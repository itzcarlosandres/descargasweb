<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\User;
use App\Services\LinkHealth\AutoLinkHealerService;
use App\Services\LinkHealth\LinkHealthCheckerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkHealthAndAutoRepairTest extends TestCase
{
    use RefreshDatabase;

    private function createTestApp(array $attributes = []): Application
    {
        $category = Category::create([
            'name' => 'Design & Graphics',
            'slug' => 'design-graphics',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Application::create(array_merge([
            'category_id' => $category->id,
            'name' => 'CleanMyMac X',
            'slug' => 'cleanmymac-x',
            'version' => '4.15.0',
            'download_url' => 'https://example.com/dead-link.dmg',
            'published' => true,
        ], $attributes));
    }

    public function test_link_health_checker_identifies_alive_and_dead_links(): void
    {
        Http::fake([
            'https://example.com/alive.dmg' => Http::response('file content', 200),
            'https://example.com/dead.dmg' => Http::response('not found', 404),
        ]);

        $checker = app(LinkHealthCheckerService::class);

        $aliveCheck = $checker->checkUrl('https://example.com/alive.dmg');
        $this->assertTrue($aliveCheck['alive']);
        $this->assertEquals(200, $aliveCheck['status']);

        $deadCheck = $checker->checkUrl('https://example.com/dead.dmg');
        $this->assertFalse($deadCheck['alive']);
        $this->assertEquals(404, $deadCheck['status']);

        $magnetCheck = $checker->checkUrl('magnet:?xt=urn:btih:d6b055375c3db1184a4413e1176b5d9202167d4a&dn=CleanMyMac');
        $this->assertTrue($magnetCheck['alive']);
    }

    public function test_auto_link_healer_applies_candidate_and_resolves_report(): void
    {
        $app = $this->createTestApp();
        $report = BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'broken_link',
            'notes' => 'El enlace ya no descarga.',
            'status' => 'pending',
        ]);

        $healer = app(AutoLinkHealerService::class);

        $candidate = [
            'source' => 'Torrentmac',
            'title' => 'CleanMyMac X 4.15.1',
            'clean_name' => 'CleanMyMac X',
            'version' => '4.15.1',
            'url' => 'https://torrentmac.net/cleanmymac-x-4-15-1/',
            'torrent_url' => 'https://torrentmac.net/dl/cleanmymac.torrent',
            'magnet_link' => 'magnet:?xt=urn:btih:d6b055375c3db1184a4413e1176b5d9202167d4a&dn=CleanMyMac',
            'download_url' => null,
            'mirrors' => [],
            'score' => 95,
        ];

        $applied = $healer->applyCandidate($app, $candidate, $report);

        $this->assertTrue($applied);

        $freshApp = $app->fresh();
        $this->assertEquals('4.15.1', $freshApp->version);
        $this->assertTrue($freshApp->has_torrent);
        $this->assertEquals($candidate['magnet_link'], $freshApp->magnet_link);
        $this->assertEquals($candidate['torrent_url'], $freshApp->torrent_url);

        $freshReport = $report->fresh();
        $this->assertEquals('resolved', $freshReport->status);
        $this->assertStringContainsString('Auto-reparado', $freshReport->notes);
    }

    public function test_admin_can_search_replacements_for_reported_application(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();
        $report = BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'broken_link',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://torrentmac.net/?s=*' => Http::response('<html><body><article class="post"><h2 class="entry-title"><a href="https://torrentmac.net/cleanmymac-x-4-15-2/">CleanMyMac X 4.15.2</a></h2></article></body></html>', 200),
            'https://torrentmac.net/cleanmymac-x-4-15-2/' => Http::response('<html><body><h1 class="entry-title">CleanMyMac X 4.15.2</h1><a href="magnet:?xt=urn:btih:d6b055375c3db1184a4413e1176b5d9202167d4a&dn=CleanMyMac">Magnet</a></body></html>', 200),
            'https://haxmac.to/?s=*' => Http::response('<html><body></body></html>', 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.reports.search-replacement', $report->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertArrayHasKey('candidates', $response->json());
    }

    public function test_admin_can_apply_manual_replacement_and_resolve_report(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp();
        $report = BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'broken_link',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.reports.apply-replacement', $report->id), [
            'download_url' => 'https://mega.nz/file/fresh-working-link',
            'version' => '4.15.2',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('https://mega.nz/file/fresh-working-link', $app->fresh()->download_url);
        $this->assertEquals('4.15.2', $app->fresh()->version);
        $this->assertEquals('resolved', $report->fresh()->status);
    }

    public function test_admin_can_check_application_health(): void
    {
        $user = User::factory()->create();
        $app = $this->createTestApp([
            'download_url' => 'https://example.com/app.dmg',
        ]);
        $report = BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'broken_link',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://example.com/app.dmg' => Http::response('ok', 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.reports.check-health', $report->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'health' => [
                'has_working_link' => true,
            ],
        ]);
    }
}
