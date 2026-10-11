<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReportsAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    private function makeApp(string $name = 'Photoshop Mac', string $slug = 'photoshop-mac'): Application
    {
        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'is_active' => true,
        ]);

        return Application::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'version' => '1.0.0',
            'size' => '100 MB',
            'published' => true,
            'download_url' => 'https://example.com/file.dmg',
        ]);
    }

    public function test_user_can_report_broken_link(): void
    {
        $app = $this->makeApp('Photoshop Mac', 'photoshop-mac');

        $response = $this->postJson(route('app.report', $app->slug), [
            'type' => 'ddl',
            'notes' => 'El enlace descarga con error 404',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $report = BrokenLinkReport::first();
        $this->assertNotNull($report);
        $this->assertEquals($app->id, $report->application_id);
        $this->assertEquals('ddl', $report->type);
        $this->assertEquals('El enlace descarga con error 404', $report->notes);
        $this->assertFalse((bool) $report->resolved);
    }

    public function test_admin_can_view_reports_page(): void
    {
        $admin = $this->createAdminUser();
        $app = $this->makeApp('Figma', 'figma');
        BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'torrent',
            'notes' => 'Sin semillas',
            'resolved' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports'));

        $response->assertStatus(200);
        $response->assertSee('Reportes de Enlaces Caídos');
        $response->assertSee('Figma');
        $response->assertSee('Sin semillas');
    }

    public function test_admin_can_resolve_report(): void
    {
        $admin = $this->createAdminUser();
        $app = $this->makeApp('Final Cut', 'final-cut');
        $report = BrokenLinkReport::create([
            'application_id' => $app->id,
            'type' => 'ddl',
            'notes' => 'Mirror 1 roto',
            'resolved' => false,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.reports'))
            ->post(route('admin.reports.resolve', $report));

        $response->assertRedirect(route('admin.reports'));
        $this->assertTrue((bool) $report->fresh()->resolved);
    }

    public function test_admin_can_save_notification_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'notifications_settings' => '1',
            'telegram_enabled' => '1',
            'telegram_bot_token' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
            'telegram_channel_id' => '-100123456789',
            'discord_enabled' => '1',
            'discord_webhook_url' => 'https://discord.com/api/webhooks/123/xyz',
            'notify_on_new_app' => '1',
            'notify_on_update' => '1',
            'notify_on_broken_link' => '1',
        ]);

        $response->assertRedirect(route('admin.settings'));
        $this->assertEquals('1', Setting::get('telegram_enabled'));
        $this->assertEquals('-100123456789', Setting::get('telegram_channel_id'));
        $this->assertEquals('https://discord.com/api/webhooks/123/xyz', Setting::get('discord_webhook_url'));
    }

    public function test_admin_can_test_telegram_connection(): void
    {
        $admin = $this->createAdminUser();

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 999],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.settings.test-telegram'), [
            'bot_token' => '123456:fake_token',
            'channel_id' => '-100123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
