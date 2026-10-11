<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use App\Services\Scraper\HaxmacParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScraperTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_guest_cannot_access_scraper(): void
    {
        $response = $this->get(route('admin.scraper'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_scraper_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.scraper'));

        $response->assertStatus(200);
        $response->assertSee('Scraper Inteligente DDL');
        $response->assertSee('Buscador en Vivo DDL');
        $response->assertSee('TorrentMac (R2 P2P)');
        $response->assertSee('Buscador en Tiempo Real de TorrentMac');
        $response->assertSee('Automatización Cron');
    }

    public function test_admin_can_update_cron_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.scraper.cron-settings'), [
            'cron_enabled' => '1',
            'cron_time' => '04:30',
            'cron_pages' => 3,
        ]);

        $response->assertRedirect(route('admin.scraper'));
        $response->assertSessionHas('success');

        $this->assertTrue((bool) Setting::get('scraper_cron_enabled'));
        $this->assertEquals('04:30', Setting::get('scraper_cron_time'));
        $this->assertEquals(3, (int) Setting::get('scraper_cron_pages'));
    }

    public function test_admin_can_toggle_drip_feed_mode(): void
    {
        $admin = $this->createAdminUser();

        // Toggle on
        $response = $this->actingAs($admin)->postJson(route('admin.scraper.toggle-drip'), [
            'drip_mode' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'drip_mode' => true,
            ]);

        $this->assertTrue((bool) Setting::get('scraper_drip_feed_mode'));

        // Toggle off
        $response2 = $this->actingAs($admin)->postJson(route('admin.scraper.toggle-drip'), [
            'drip_mode' => false,
        ]);

        $response2->assertOk()
            ->assertJson([
                'success' => true,
                'drip_mode' => false,
            ]);

        $this->assertFalse((bool) Setting::get('scraper_drip_feed_mode'));
    }

    public function test_admin_can_save_drip_mode_via_cron_settings_without_cron_pages(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.scraper.cron-settings'), [
            'cron_enabled' => '1',
            'cron_frequency' => '2hours',
            'cron_limit' => 15,
            'cron_time' => '02:00',
            'drip_mode' => '1',
            'tab' => 'cron',
        ]);

        $response->assertRedirect(route('admin.scraper', ['tab' => 'cron']));
        $response->assertSessionHas('success');

        $this->assertTrue((bool) Setting::get('scraper_drip_feed_mode'));
        $this->assertEquals(15, (int) Setting::get('scraper_cron_limit'));
        $this->assertEquals('02:00', Setting::get('scraper_cron_time'));
    }

    public function test_haxmac_parser_filters_ad_links_and_keeps_clean_mirrors(): void
    {
        $parser = new HaxmacParser;

        // Valid trusted hosts
        $this->assertTrue($parser->isCleanDownloadUrl('https://theuser.cloud/l3jz0e22rsbr.html'));
        $this->assertTrue($parser->isCleanDownloadUrl('https://usersdrive.com/1k5los084z74.html'));
        $this->assertTrue($parser->isCleanDownloadUrl('https://send.now/bazoiwepu9vx'));
        $this->assertTrue($parser->isCleanDownloadUrl('https://mega.nz/file/sample#abc'));

        // Discarded ads and tracker domains
        $this->assertFalse($parser->isCleanDownloadUrl('//fb10o8tc2208264q.cfd/?s=58&g=51&q=App'));
        $this->assertFalse($parser->isCleanDownloadUrl('https://ads.redirector.click/download'));
        $this->assertFalse($parser->isCleanDownloadUrl('https://haxmac.to/wp-content/uploads/banner.jpg'));
        $this->assertFalse($parser->isCleanDownloadUrl(''));
        $this->assertFalse($parser->isCleanDownloadUrl(null));
    }

    public function test_admin_can_sync_categories_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->postJson(route('admin.scraper.sync-categories'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'created',
            'updated',
            'categories',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_admin_scraper_view_renders_cards_and_latest_added_section(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.scraper'));

        $response->assertStatus(200);
        $response->assertSee('Últimos Agregados en Catálogo DDL');
        $response->assertSee('1–Clic Importar');
        $response->assertViewHas('haxmacLatest');
    }

    public function test_haxmac_parser_extracts_screenshots_and_whats_new(): void
    {
        $parser = new HaxmacParser;

        $sampleHtml = <<<'HTML'
        <div class="single-app-header">
            <h1 class="entry-title">Sample App <span class="sah-ver">2.5.0</span></h1>
        </div>
        <div class="entry-content">
            <p>Sample App is a modern utility for macOS.</p>
            <div class="wps-tabs-wrapper">
                <ul class="wps-tabs-list">
                    <li class="wps-tabs-item">Features</li>
                    <li class="wps-tabs-item">Screenshots</li>
                    <li class="wps-tabs-item">Whats new?</li>
                </ul>
                <div class="wps-tabs-content">
                    <div class="wps-tab-text"><ul><li>Feature 1</li></ul></div>
                    <div class="wps-tab-text">
                        <p><a href="https://haxmac.to/uploads/screen1.avif"><img src="https://haxmac.to/uploads/screen1.avif"></a></p>
                        <p><a href="https://haxmac.to/uploads/screen2.webp"><img src="https://haxmac.to/uploads/screen2.webp"></a></p>
                    </div>
                    <div class="wps-tab-text">
                        <ul><li>Version 2.5: Security update and speed enhancements.</li></ul>
                    </div>
                </div>
            </div>
        </div>
HTML;

        $parsed = $parser->parseAppDetail($sampleHtml, 'https://haxmac.to/sample-app/');

        $this->assertEquals('Sample App', $parsed['name']);
        $this->assertEquals('2.5.0', $parsed['version']);
        $this->assertCount(2, $parsed['screenshots']);
        $this->assertEquals('https://haxmac.to/uploads/screen1.avif', $parsed['screenshots'][0]);
        $this->assertEquals('https://haxmac.to/uploads/screen2.webp', $parsed['screenshots'][1]);
        $this->assertStringContainsString('Version 2.5: Security update', $parsed['whats_new']);
    }

    public function test_admin_can_call_sync_updates_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->postJson(route('admin.scraper.sync-updates'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'new_imported',
            'updated',
            'total_checked',
            'items',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_admin_can_fetch_pending_updates_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->getJson(route('admin.scraper.pending-updates'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'count',
            'items',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_admin_can_release_drip_batch_from_draft_queue(): void
    {
        $admin = $this->createAdminUser();

        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilities',
            'is_active' => true,
        ]);

        // Create 3 draft applications
        for ($i = 1; $i <= 3; $i++) {
            Application::create([
                'category_id' => $category->id,
                'name' => "Draft App {$i}",
                'slug' => "draft-app-{$i}",
                'version' => "1.{$i}",
                'published' => false,
            ]);
        }

        $this->assertEquals(3, Application::where('published', false)->count());

        // Release batch with limit 2
        $response = $this->actingAs($admin)->postJson(route('admin.scraper.drip.release-now'), [
            'limit' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'published_count' => 2,
            'remaining_drafts' => 1,
        ]);

        $this->assertEquals(1, Application::where('published', false)->count());
        $this->assertEquals(2, Application::where('published', true)->count());
    }

    public function test_release_drip_batch_balances_torrent_and_ddl_and_fills_with_available_apps(): void
    {
        $category = Category::create([
            'name' => 'Utilidades Test',
            'slug' => 'utilities-test',
            'is_active' => true,
        ]);

        // Create 2 torrent drafts and 8 DDL drafts (total 10)
        for ($i = 1; $i <= 2; $i++) {
            Application::create([
                'category_id' => $category->id,
                'name' => "Torrent Draft {$i}",
                'slug' => "torrent-draft-{$i}",
                'version' => "1.{$i}",
                'has_torrent' => true,
                'published' => false,
            ]);
        }

        for ($i = 1; $i <= 8; $i++) {
            Application::create([
                'category_id' => $category->id,
                'name' => "DDL Draft {$i}",
                'slug' => "ddl-draft-{$i}",
                'version' => "1.{$i}",
                'has_torrent' => false,
                'published' => false,
            ]);
        }

        // Release batch of 6: target is 3 Torrent + 3 DDL.
        // Since only 2 Torrent drafts exist, it takes 2 Torrent + 4 DDL = 6 total!
        $result = Application::releaseDripBatch(6);

        $this->assertEquals(6, $result['published_count']);
        $this->assertEquals(2, $result['torrent_count']);
        $this->assertEquals(4, $result['ddl_count']);
        $this->assertEquals(4, Application::where('published', false)->count());
    }
}
