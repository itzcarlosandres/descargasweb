<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use App\Services\AI\GeminiService;
use App\Services\Scraper\TorrentmacClient;
use App\Services\Scraper\TorrentmacImporter;
use App\Services\Scraper\TorrentmacParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TorrentmacScraperTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_torrentmac_parser_extracts_cards(): void
    {
        $parser = new TorrentmacParser;

        $sampleHtml = <<<'HTML'
        <div class="archive-posts">
            <article id="post-101" class="post hentry category-apps category-utilities-tools">
                <a href="https://www.torrentmac.net/pixelmator-pro-4-2/" class="home-thumb">
                    <img src="https://www.torrentmac.net/wp-content/uploads/pixelmator.jpg" class="wp-post-image">
                </a>
                <header class="entry-header">
                    <h2 class="entry-title">
                        <a href="https://www.torrentmac.net/pixelmator-pro-4-2/">Pixelmator Pro 4.2 [TNT] for Mac</a>
                    </h2>
                </header>
            </article>
        </div>
HTML;

        $cards = $parser->parseAppCards($sampleHtml);

        $this->assertCount(1, $cards);
        $this->assertEquals('pixelmator-pro-4-2', $cards[0]['slug']);
        $this->assertEquals('Pixelmator Pro', $cards[0]['clean_name']);
        $this->assertEquals('4.2', $cards[0]['version']);
        $this->assertEquals('utilities-tools', $cards[0]['category']);
    }

    public function test_torrentmac_parser_extracts_json_ld_and_torrent_link(): void
    {
        $parser = new TorrentmacParser;

        $sampleHtml = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head>
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "SoftwareApplication",
                "name": "CleanMyMac X",
                "softwareVersion": "5.1.0",
                "fileSize": "145M",
                "applicationCategory": "UtilitiesApplication",
                "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "4.8",
                    "ratingCount": "120"
                }
            }
            </script>
        </head>
        <body>
            <h1 class="entry-title">CleanMyMac X 5.1.0 for Mac</h1>
            <div class="entry-content">
                <p>CleanMyMac cleans and optimizes macOS systems.</p>
                <div class="torrent_info">
                    <a href="https://www.torrentmac.net/wp-content/uploads/CleanMyMac_X_5.1.0.dmg.torrent" class="btn download-btn">Download Torrent</a>
                </div>
            </div>
        </body>
        </html>
HTML;

        $parsed = $parser->parseAppDetail($sampleHtml, 'https://www.torrentmac.net/cleanmymac-x-5-1-0/');

        $this->assertNotNull($parsed);
        $this->assertEquals('CleanMyMac X', $parsed['clean_name']);
        $this->assertEquals('5.1.0', $parsed['version']);
        $this->assertEquals('145 MB', $parsed['size']);
        $this->assertEquals('system-utilities', $parsed['category']);
        $this->assertEquals('https://www.torrentmac.net/wp-content/uploads/CleanMyMac_X_5.1.0.dmg.torrent', $parsed['torrent_url']);
        $this->assertEquals(4.8, $parsed['rating']);
    }

    public function test_torrentmac_importer_merges_with_existing_haxmac_app(): void
    {
        Storage::fake('public');

        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'system-utilities',
            'is_active' => true,
        ]);

        // Existing app from HaxMac with Direct Download
        $existing = Application::create([
            'category_id' => $category->id,
            'name' => 'Pixelmator Pro',
            'slug' => 'pixelmator-pro-3-6',
            'version' => '3.6.0',
            'size' => '600 MB',
            'download_url_external' => 'https://usersdrive.com/sample.html',
            'has_torrent' => false,
            'published' => true,
        ]);

        $mockClient = $this->createMock(TorrentmacClient::class);
        $mockClient->method('fetchHtml')->willReturn(<<<'HTML'
            <h1>Pixelmator Pro 3.6.0 [TNT]</h1>
            <div class="entry-content">
                <p>Professional image editing on macOS.</p>
                <a href="https://www.torrentmac.net/wp-content/uploads/Pixelmator.dmg.torrent" class="btn download-btn">Download</a>
            </div>
HTML
        );
        $mockClient->method('downloadBinary')->willReturn('d8:announce...test-torrent-content-e');

        $parser = new TorrentmacParser;
        $mockGemini = $this->createMock(GeminiService::class);
        $mockGemini->method('isEnabled')->willReturn(false);

        $importer = new TorrentmacImporter($mockClient, $parser, $mockGemini);

        $app = $importer->importAppByUrl('https://www.torrentmac.net/pixelmator-pro-3-6/', false);

        $this->assertNotNull($app);
        $this->assertEquals($existing->id, $app->id);
        $this->assertTrue((bool) $app->has_torrent);
        $this->assertEquals('https://usersdrive.com/sample.html', $app->download_url_external);
        $this->assertNotEmpty($app->torrent_url);
    }

    public function test_admin_can_access_torrentmac_latest_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->getJson(route('admin.scraper.torrentmac.latest', ['page' => 1]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'items',
            'pagination',
            'category',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_home_download_route_handles_torrent_download(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('torrents/test-app.torrent', 'binary-torrent-data');

        $category = Category::create([
            'name' => 'General',
            'slug' => 'general',
            'is_active' => true,
        ]);
        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Sample App',
            'slug' => 'sample-torrent-app',
            'version' => '1.0.0',
            'torrent_url' => 'https://cdn.example.com/torrents/sample.torrent',
            'torrent_file_path' => 'torrents/test-app.torrent',
            'has_torrent' => true,
            'published' => true,
            'downloads' => 0,
        ]);

        // Dedicated download page
        $pageResponse = $this->get(route('download', ['application' => $app->slug, 'type' => 'torrent']));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee($app->name);

        // Actual torrent download action
        $response = $this->get(route('download.file', ['application' => $app->slug, 'type' => 'torrent']));

        $response->assertRedirect('https://cdn.example.com/torrents/sample.torrent');
        $this->assertEquals(1, $app->fresh()->downloads);
    }

    public function test_admin_can_update_torrent_storage_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->postJson(route('admin.scraper.torrentmac.storage-settings'), [
            'storage_disk' => 'r2',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'storage_disk' => 'r2',
        ]);

        $this->assertEquals('r2', Setting::get('torrentmac_storage_disk'));
    }

    public function test_admin_settings_page_renders_cloudflare_r2_tab(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.settings', ['tab' => 'storage']));

        $response->assertStatus(200);
        $response->assertSee('Cloudflare R2 Storage');
        $response->assertSee('Cloudflare Account ID');
        $response->assertSee('Nombre del Bucket');
        $response->assertSee('Probar Conexión R2');
    }

    public function test_admin_can_save_r2_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'active_tab' => 'storage',
            'r2_account_id' => 'test_account_12345',
            'r2_access_key_id' => 'test_access_key',
            'r2_secret_access_key' => 'test_secret_key',
            'r2_bucket' => 'my-mac-torrents',
            'r2_url' => 'https://pub-xyz.r2.dev',
            'torrentmac_storage_disk' => 'r2',
        ]);

        $response->assertRedirect(route('admin.settings', ['tab' => 'storage']));
        $this->assertEquals('test_account_12345', Setting::get('r2_account_id'));
        $this->assertEquals('test_access_key', Setting::get('r2_access_key_id'));
        $this->assertEquals('my-mac-torrents', Setting::get('r2_bucket'));
        $this->assertEquals('https://pub-xyz.r2.dev', Setting::get('r2_url'));
        $this->assertEquals('r2', Setting::get('torrentmac_storage_disk'));
    }

    public function test_admin_test_r2_endpoint_validates_required_fields(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->postJson(route('admin.settings.test-r2'), [
            'account_id' => '',
            'access_key_id' => '',
            'secret_access_key' => '',
            'bucket' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_admin_can_search_torrentmac_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->postJson(route('admin.scraper.torrentmac.search'), [
            'q' => 'Logic Pro',
            'page' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'query',
            'total',
            'results',
            'pagination',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Logic Pro', $response->json('query'));
    }

    public function test_admin_can_access_torrentmac_pending_updates_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->getJson(route('admin.scraper.torrentmac.pending-updates', ['pages' => 1]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'count',
            'items',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertIsInt($response->json('count'));
        $this->assertIsArray($response->json('items'));
    }

    public function test_torrentmac_version_comparison_and_upgrade_history(): void
    {
        $mockClient = $this->createMock(TorrentmacClient::class);
        $mockGemini = $this->createMock(GeminiService::class);
        $parser = new TorrentmacParser;
        $importer = new TorrentmacImporter($mockClient, $parser, $mockGemini);

        $this->assertTrue($importer->isHigherVersion('4.2.1', '4.2.0'));
        $this->assertTrue($importer->isHigherVersion('v5.0', '4.9'));
        $this->assertTrue($importer->isHigherVersion('2026.1', '2025.4'));
        $this->assertFalse($importer->isHigherVersion('3.0', '3.0'));
        $this->assertFalse($importer->isHigherVersion('2.1', '2.2'));
        // False positive fixes (same base version with/without fix/hotfix/tags)
        $this->assertFalse($importer->isHigherVersion('7.7.2', '7.7.2 fix'));
        $this->assertFalse($importer->isHigherVersion('7.2.4', '7.2.4 fix'));
        $this->assertFalse($importer->isHigherVersion('2026.2.4', '2026.2.4'));
        $this->assertFalse($importer->isHigherVersion('2026.2.4', '2026 2.4'));
        $this->assertFalse($importer->isHigherVersion('7.50', '7.50'));
        $this->assertFalse($importer->isHigherVersion('7.50', '7 50'));
        $this->assertFalse($importer->isHigherVersion('7.50', '7 7 50'));
        $this->assertFalse($importer->isHigherVersion('7.50', '7.7.50'));

        $category = Category::create([
            'name' => 'Diseño',
            'slug' => 'media-design',
            'is_active' => true,
        ]);

        // Create an application with v3.0 that was not registered in application_versions yet
        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Pixelmator Pro',
            'slug' => 'pixelmator-pro',
            'version' => '3.0',
            'size' => '200 MB',
            'download_url_external' => 'https://mega.nz/file/v3',
            'has_torrent' => false,
            'published' => true,
        ]);

        $sampleDetailHtml = <<<'HTML'
        <h1 class="entry-title">Pixelmator Pro 4.2 for Mac</h1>
        <div class="entry-content">
            <p>New version with advanced AI editing.</p>
            <a href="https://www.torrentmac.net/wp-content/uploads/Pixelmator_4.2.dmg.torrent" class="btn download-btn">Download</a>
        </div>
HTML;

        $mockClient->method('fetchHtml')->willReturn($sampleDetailHtml);
        $mockClient->method('downloadBinary')->willReturn('dummy-torrent-bytes');

        $updatedApp = $importer->importAppByUrl('https://www.torrentmac.net/pixelmator-pro-4-2/', false);

        $this->assertNotNull($updatedApp);
        $this->assertEquals($app->id, $updatedApp->id);
        $this->assertEquals('4.2', $updatedApp->version);
        $this->assertTrue($updatedApp->has_torrent);

        // Verify history: v3.0 archived, v4.2 current
        $versions = $updatedApp->versions()->orderBy('version', 'asc')->get();
        $this->assertCount(2, $versions);

        $v3 = $versions->where('version', '3.0')->first();
        $this->assertNotNull($v3);
        $this->assertFalse((bool) $v3->is_current);

        $v42 = $versions->where('version', '4.2')->first();
        $this->assertNotNull($v42);
        $this->assertTrue((bool) $v42->is_current);
    }
}
