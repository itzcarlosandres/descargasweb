<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\User;
use App\Services\Scraper\TorrentmacImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private function createCategoryAndApp(array $attributes = []): array
    {
        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilidades',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $app = Application::create(array_merge([
            'category_id' => $category->id,
            'name' => 'CleanMyMac X',
            'slug' => 'cleanmymac-x',
            'short_description' => 'Herramienta de limpieza para macOS',
            'description' => 'Optimiza y limpia tu Mac en minutos.',
            'version' => '4.15.5',
            'size' => '120 MB',
            'platform' => 'macOS',
            'has_torrent' => true,
            'magnet_link' => 'magnet:?xt=urn:btih:example1234567890',
            'download_url_external' => 'https://example.com/dl/cleanmymac.dmg',
            'published' => true,
            'featured' => false,
            'downloads' => 500,
            'rating' => 4.8,
            'reviews_count' => 12,
            'released_at' => now(),
        ], $attributes));

        return [$category, $app];
    }

    public function test_download_page_renders_magnet_copy_and_terminal_helper(): void
    {
        [, $app] = $this->createCategoryAndApp();

        $response = $this->get(route('download', $app->slug));

        $response->assertStatus(200);
        $response->assertSee('Copy Magnet Link');
        $response->assertSee('Open in Client');
        $response->assertSee('Fix Damaged App (xattr)');
        $response->assertSee('Ad-hoc Signature (M1/M2/M3/M4)');
        $response->assertSee('Allow Anywhere (spctl)');
    }

    public function test_user_can_report_broken_download_link(): void
    {
        [, $app] = $this->createCategoryAndApp();

        $response = $this->postJson(route('app.report', $app->slug), [
            'type' => 'ddl',
            'notes' => 'El enlace externo redirige a un error 404',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('broken_link_reports', [
            'application_id' => $app->id,
            'type' => 'ddl',
            'notes' => 'El enlace externo redirige a un error 404',
            'status' => 'pending',
        ]);
    }

    public function test_search_logs_queries_for_analytics(): void
    {
        $this->createCategoryAndApp();

        $response = $this->get(route('search', ['q' => 'Photoshop']));
        $response->assertStatus(200);

        $this->assertDatabaseHas('search_logs', [
            'query' => 'Photoshop',
            'results_count' => 0,
        ]);
    }

    public function test_admin_can_perform_bulk_actions_on_applications(): void
    {
        $admin = User::factory()->create();
        [$category, $app1] = $this->createCategoryAndApp(['name' => 'App One', 'slug' => 'app-one', 'published' => false]);
        $app2 = Application::create([
            'category_id' => $category->id,
            'name' => 'App Two',
            'slug' => 'app-two',
            'published' => false,
        ]);

        $this->assertFalse((bool) $app1->fresh()->published);
        $this->assertFalse((bool) $app2->fresh()->published);

        $response = $this->actingAs($admin)->post(route('admin.applications.bulk-action'), [
            'action' => 'publish',
            'selected_ids' => [$app1->id, $app2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue((bool) $app1->fresh()->published);
        $this->assertTrue((bool) $app2->fresh()->published);
    }

    public function test_admin_can_resolve_broken_link_report(): void
    {
        $admin = User::factory()->create();
        [, $app] = $this->createCategoryAndApp();

        $report = BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'mirror',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.resolve', $report->id));

        $response->assertRedirect();
        $this->assertEquals('resolved', $report->fresh()->status);
    }

    public function test_torrent_importer_extracts_magnet_from_torrent_binary(): void
    {
        $importer = app(TorrentmacImporter::class);

        $infoDict = 'd4:name8:test.app12:piece lengthi16384e6:pieces20:12345678901234567890e';
        $torrentBinary = 'd8:announce27:http://tracker.example.com/4:info'.$infoDict.'e';

        $expectedHash = sha1($infoDict);
        $magnet = $importer->extractMagnetFromTorrent($torrentBinary, 'Test App');

        $this->assertNotNull($magnet);
        $this->assertStringStartsWith("magnet:?xt=urn:btih:{$expectedHash}", $magnet);
        $this->assertStringContainsString('dn=Test%20App', $magnet);
        $this->assertStringContainsString('tr=', $magnet);
    }
}
