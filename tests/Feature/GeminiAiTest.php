<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use App\Services\AI\GeminiService;
use App\Services\Scraper\HaxmacClient;
use App\Services\Scraper\HaxmacImporter;
use App\Services\Scraper\HaxmacParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiAiTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_admin_can_update_gemini_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'gemini_api_key' => 'AIzaSyTestApiKey123456789',
            'gemini_model' => 'gemini-2.5-flash',
            'gemini_auto_generate' => '1',
            'gemini_target_words' => 412,
            'gemini_features_count' => 7,
        ]);

        $response->assertRedirect(route('admin.settings'));
        $this->assertEquals('AIzaSyTestApiKey123456789', Setting::get('gemini_api_key'));
        $this->assertEquals('gemini-2.5-flash', Setting::get('gemini_model'));
        $this->assertEquals('1', Setting::get('gemini_auto_generate'));
        $this->assertEquals('412', Setting::get('gemini_target_words'));
        $this->assertEquals('7', Setting::get('gemini_features_count'));
    }

    public function test_admin_can_test_gemini_connection_success(): void
    {
        $admin = $this->createAdminUser();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'CONNECTED'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.settings.test-gemini'), [
            'api_key' => 'AIzaSyValidKey',
            'model' => 'gemini-2.5-flash',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertStringContainsString('CONNECTED', $response->json('message'));
    }

    public function test_admin_can_generate_ai_content_for_application(): void
    {
        $admin = $this->createAdminUser();
        Setting::set('gemini_api_key', 'AIzaSyValidKey', 'ai');
        Setting::set('gemini_model', 'gemini-2.5-flash', 'ai');
        Setting::set('gemini_target_words', '60', 'ai');
        Setting::set('gemini_features_count', '7', 'ai');

        $category = Category::create([
            'name' => 'Design & Graphics',
            'slug' => 'design-graphics',
            'is_active' => true,
        ]);
        $app = Application::create([
            'name' => 'Pixelmator Pro',
            'slug' => 'pixelmator-pro',
            'category_id' => $category->id,
            'version' => '3.6.5',
            'description' => 'Original basic description.',
            'features' => null,
            'platform' => 'Universal',
            'published' => true,
        ]);

        $fakeJson = json_encode([
            'description' => '<p>Pixelmator Pro is an ultra-fast, professional image editor built natively for macOS and Apple Silicon. Ranked among the best <a href="/category/design-graphics">macOS Design & Graphics</a> software, it empowers creators with non-destructive layers, automated AI adjustments, and full Sequoia integration in a streamlined interface.</p>',
            'features' => [
                ['title' => 'Apple Silicon Native', 'description' => 'Harnesses M1/M2/M3/M4 unified memory for lightning-speed filters.'],
                ['title' => 'ML Super Resolution', 'description' => 'Enhance image clarity and upscale photos automatically with Core ML.'],
                ['title' => 'Non-Destructive Layers', 'description' => 'Combine photos, vector graphics, and text without losing raw fidelity.'],
                ['title' => 'Color Adjustments', 'description' => 'Professional HDR and RAW grading tools with native curves.'],
                ['title' => 'Auto Background Removal', 'description' => 'Isolate subjects in a single click using on-device machine intelligence.'],
                ['title' => 'Sequoia Dark Mode', 'description' => 'Seamless UI integration following Apple human interface guidelines.'],
                ['title' => 'Direct Export Formats', 'description' => 'Optimized output for WebP, HEIC, TIFF, PNG, and PSD compositions.'],
            ],
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $fakeJson],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.applications.generate-ai', $app));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $app->refresh();
        $this->assertStringContainsString('Pixelmator Pro is an ultra-fast', $app->description);
        $this->assertStringContainsString('/category/design-graphics', $app->description);
        $this->assertEquals(1, substr_count($app->description, '<p>'));
        $this->assertStringContainsString('Apple Silicon Native', $app->features);
        $this->assertStringContainsString('Direct Export Formats', $app->features);
        // Verify 7 list items exist in features
        $this->assertEquals(7, substr_count($app->features, '<li>'));
    }

    public function test_scraper_importer_uses_gemini_when_enabled(): void
    {
        Setting::set('gemini_api_key', 'AIzaSyKeyForScraper', 'ai');
        Setting::set('gemini_auto_generate', '1', 'ai');
        Setting::set('gemini_target_words', '412', 'ai');
        Setting::set('gemini_features_count', '7', 'ai');

        $fakeJson = json_encode([
            'description' => '<p>Enriched AI generated English description for Raycast Mac app with 412 targeted words and deep system integration.</p>',
            'features' => [
                ['title' => 'Extensible Launcher', 'description' => 'Run scripts and extensions seamlessly.'],
                ['title' => 'Clipboard History', 'description' => 'Instant search through copied items.'],
                ['title' => 'Snippet Expansion', 'description' => 'Speed up typing with smart shortcuts.'],
                ['title' => 'Window Management', 'description' => 'Snap windows effortlessly with keyboard commands.'],
                ['title' => 'Quick Calculations', 'description' => 'Evaluate currency and math in the search bar.'],
                ['title' => 'Apple Silicon Fast', 'description' => 'Zero latency startup under 10 milliseconds.'],
                ['title' => 'Custom Themes', 'description' => 'Matches macOS light and dark aesthetics perfectly.'],
            ],
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $fakeJson],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $client = $this->createMock(HaxmacClient::class);
        $client->method('getBaseUrl')->willReturn('https://haxmac.cc');
        $client->method('fetchHtml')->willReturn('<html>app content</html>');

        $parser = $this->createMock(HaxmacParser::class);
        $parser->method('parseAppDetail')->willReturn([
            'name' => 'Raycast AI Launcher',
            'slug' => 'raycast-ai-launcher',
            'category_name' => 'Utilities',
            'short_description' => 'Fast keyboard launcher for Mac',
            'description' => 'Original scraped description from HaxMac',
            'version' => '1.75.0',
            'size' => '85 MB',
            'platform' => 'Universal',
            'screenshots' => [],
            'rating' => 4.9,
            'downloads' => 1500,
        ]);
        $parser->method('parseDownloadMirrors')->willReturn([
            'mirrors' => [['name' => 'Mirror 1', 'url' => 'https://example.com/raycast.dmg']],
            'older_versions' => [],
        ]);

        $gemini = app(GeminiService::class);
        $importer = new HaxmacImporter($client, $parser, $gemini);

        $app = $importer->importApp('raycast-ai-launcher', false);

        $this->assertNotNull($app);
        $this->assertStringContainsString('Enriched AI generated English description', $app->description);
        $this->assertStringContainsString('Extensible Launcher', $app->features);
        $this->assertEquals(7, substr_count($app->features, '<li>'));
    }
}
