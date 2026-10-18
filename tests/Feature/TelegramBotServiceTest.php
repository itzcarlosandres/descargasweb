<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Setting;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TelegramBotServiceTest extends TestCase
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
            'name' => 'Pixelmator Pro',
            'slug' => 'pixelmator-pro',
            'short_description' => 'Image editing powerhouse for macOS.',
            'description' => 'Detailed description of Pixelmator Pro.',
            'version' => '3.6.5',
            'size' => '450 MB',
            'platform' => 'macOS',
            'published' => true,
            'has_torrent' => true,
            'download_url_external' => 'https://example.com/pixelmator.dmg',
            'released_at' => now(),
        ], $attributes));
    }

    public function test_format_message_replaces_all_placeholders_including_image_and_cover(): void
    {
        $app = $this->createTestApp();
        $service = new TelegramBotService;

        $template = 'Title: {title} | Cat: {category} | Version: {version} | Image: {image} | Cover: {cover}';
        $formatted = $service->formatMessage($app, $template);

        $this->assertStringContainsString('Pixelmator Pro', $formatted);
        $this->assertStringContainsString('Design &amp; Graphics', $formatted);
        $this->assertStringContainsString('3.6.5', $formatted);
        $this->assertStringContainsString($app->cover_image_url, $formatted);
    }

    public function test_send_application_uploads_photo_via_multipart_to_telegram(): void
    {
        Setting::set('telegram_bot_token', '123456:ABC-TEST-TOKEN', 'telegram');
        Setting::set('telegram_channel_id', '@hackmactest', 'telegram');

        Storage::fake('public');
        // Create dummy screenshot
        Storage::disk('public')->put('apps/test-screen.png', 'fake-image-binary-data');

        $app = $this->createTestApp([
            'screenshot' => 'apps/test-screen.png',
        ]);

        Http::fake([
            'https://api.telegram.org/bot123456:ABC-TEST-TOKEN/sendPhoto' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 999],
            ], 200),
        ]);

        $service = new TelegramBotService;
        $result = $service->sendApplication($app);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Publicado con éxito', $result['message']);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'sendPhoto') &&
                str_contains($request->body(), '@hackmactest') &&
                str_contains($request->body(), 'Pixelmator Pro') &&
                str_contains($request->body(), 'fake-image-binary-data');
        });
    }

    public function test_send_application_falls_back_to_send_message_if_send_photo_fails(): void
    {
        Setting::set('telegram_bot_token', '123456:ABC-TEST-TOKEN', 'telegram');
        Setting::set('telegram_channel_id', '@hackmactest', 'telegram');

        $app = $this->createTestApp();

        Http::fake([
            'https://api.telegram.org/bot123456:ABC-TEST-TOKEN/sendPhoto' => Http::response([
                'ok' => false,
                'description' => 'Bad Request: photo error',
            ], 400),
            'https://api.telegram.org/bot123456:ABC-TEST-TOKEN/sendMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 1000],
            ], 200),
        ]);

        $service = new TelegramBotService;
        $result = $service->sendApplication($app);

        $this->assertTrue($result['success']);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'sendMessage');
        });
    }

    public function test_resolve_cover_image_falls_back_to_default_og_share_when_app_has_no_images(): void
    {
        $app = $this->createTestApp();
        $service = new TelegramBotService;

        $cover = $service->resolveCoverImage($app);

        $this->assertNotNull($cover);
        $this->assertArrayHasKey('bytes', $cover);
        $this->assertEquals('cover.jpg', $cover['filename']);
        $this->assertEquals('image/jpeg', $cover['mime']);
    }

    public function test_resolve_cover_image_handles_avif_screenshot(): void
    {
        if (! function_exists('imagecreatefromavif')) {
            $this->markTestSkipped('AVIF support not available in GD');
        }

        Storage::fake('public');

        // Create a real 10x10 AVIF image if imageavif exists, or mock file
        if (function_exists('imageavif')) {
            $im = imagecreatetruecolor(10, 10);
            ob_start();
            imageavif($im);
            $avifData = ob_get_clean();
            imagedestroy($im);

            Storage::disk('public')->put('apps/test-app-screen.avif', $avifData);

            $app = $this->createTestApp([
                'screenshot' => 'apps/test-app-screen.avif',
            ]);

            $service = new TelegramBotService;
            $cover = $service->resolveCoverImage($app);

            $this->assertNotNull($cover);
            $this->assertEquals('cover.jpg', $cover['filename']);
            $this->assertEquals('image/jpeg', $cover['mime']);
            $this->assertNotEmpty($cover['bytes']);
        }
    }

    public function test_app_og_image_route_serves_jpeg_image(): void
    {
        $app = $this->createTestApp();

        $response = $this->get(route('app.og-image', $app->slug));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }
}
