<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Services\Storage\TorrentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TorrentLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_branded_filename_generation(): void
    {
        $service = app(TorrentStorageService::class);

        $url = 'https://www.torrentmac.net/wp-content/uploads/tr_files/2022/Claris.FileMaker.Pro.19.5.4.401.macOS_Torrentmac.net.zip.torrent';
        $filename = $service->generateBrandedFilename('Claris FileMaker Pro', '19.5.4.401', $url);

        $this->assertEquals('Claris.FileMaker.Pro.19.5.4.401.macOS_HackMac.cc.torrent', $filename);
        $this->assertStringNotContainsString('torrentmac', strtolower($filename));
        $this->assertStringNotContainsString('zip', strtolower($filename));
    }

    public function test_torrent_download_falls_back_to_magnet_when_remote_link_is_dead(): void
    {
        Storage::fake('public');

        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilidades',
            'color' => '#0071E3',
            'is_active' => true,
        ]);

        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Dead Link App',
            'slug' => 'dead-link-app',
            'version' => '1.0.0',
            'has_torrent' => true,
            'torrent_url' => 'https://www.torrentmac.net/wp-content/uploads/tr_files/Dead_Link_1.0.0_Torrentmac.net.zip.torrent',
            'magnet_link' => 'magnet:?xt=urn:btih:deadlink1234567890&dn=DeadLinkApp',
            'published' => true,
        ]);

        // Fake remote failure (e.g. 404 dead link)
        Http::fake([
            'https://www.torrentmac.net/*' => Http::response(null, 404),
        ]);

        // When external torrent is dead, download redirects directly to fallback magnet
        $response = $this->get(route('download.file', ['application' => $app->slug, 'type' => 'torrent']));
        $response->assertRedirect($app->magnet_link);
    }

    public function test_torrent_download_serves_branded_file_when_remote_succeeds(): void
    {
        Storage::fake('public');

        $category = Category::create([
            'name' => 'Utilidades 2',
            'slug' => 'utilidades-2',
            'color' => '#0071E3',
            'is_active' => true,
        ]);

        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Active App',
            'slug' => 'active-app',
            'version' => '2.5.0',
            'has_torrent' => true,
            'torrent_url' => 'https://www.torrentmac.net/wp-content/uploads/tr_files/Active_App_2.5.0_Torrentmac.net.zip.torrent',
            'published' => true,
        ]);

        $fakeTorrentBinary = 'd8:announce39:udp://tracker.opentrackr.org:1337/announce4:infod4:name9:ActiveAppee';
        Http::fake([
            'https://www.torrentmac.net/*' => Http::response($fakeTorrentBinary, 200),
        ]);

        $response = $this->get(route('download.file', ['application' => $app->slug, 'type' => 'torrent']));
        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('HackMac.cc.torrent', $response->headers->get('content-disposition'));
    }

    public function test_localize_torrents_artisan_command(): void
    {
        Storage::fake('public');

        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilidades',
            'color' => '#0071E3',
            'is_active' => true,
        ]);

        $fakeTorrentBinary = 'd8:announce39:udp://tracker.opentrackr.org:1337/announce4:infod4:name7:TestAppee';
        Http::fake([
            'https://www.torrentmac.net/*' => Http::response($fakeTorrentBinary, 200),
        ]);

        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Photoshop Demo',
            'slug' => 'photoshop-demo',
            'version' => '2025',
            'has_torrent' => true,
            'torrent_url' => 'https://www.torrentmac.net/wp-content/uploads/tr_files/Photoshop_2025_Torrentmac.net.torrent',
            'published' => true,
        ]);

        $this->artisan('torrents:localize', ['--limit' => 1])
            ->assertExitCode(0);

        $app->refresh();
        $this->assertNotNull($app->torrent_file_path);
        $this->assertStringContainsString('HackMac.cc.torrent', $app->torrent_file_path);
        Storage::disk('public')->assertExists($app->torrent_file_path);
    }
}
