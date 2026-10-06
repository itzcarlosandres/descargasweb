<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_guest_cannot_access_settings(): void
    {
        $response = $this->get(route('admin.settings'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_settings_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Configuración del Portal');
        $response->assertSee('Identidad & Logo', false);
        $response->assertSee('SEO & Metadatos', false);
        $response->assertSee('Información & Textos', false);
        $response->assertSee('Guardar Cambios');
    }

    public function test_admin_can_update_logo_and_general_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'logo_type' => 'text_icon',
            'site_name' => 'Nova',
            'site_name_highlight' => 'Apps',
            'logo_icon' => 'command',
            'site_tagline' => 'La mejor tienda de aplicaciones',
            'site_description' => 'Descarga software garantizado y seguro para tu equipo.',
            'footer_text' => '© 2026 NovaApps Inc. Todos los derechos reservados.',
        ]);

        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');

        $this->assertEquals('Nova', Setting::get('site_name'));
        $this->assertEquals('Apps', Setting::get('site_name_highlight'));
        $this->assertEquals('command', Setting::get('logo_icon'));
        $this->assertEquals('La mejor tienda de aplicaciones', Setting::get('site_tagline'));

        // Verify public home page reflects updated branding
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Nova');
        $homeResponse->assertSee('Apps');
    }

    public function test_admin_can_update_seo_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'seo_meta_title' => 'Portal de Descargas Mac Pro - 2026',
            'seo_meta_description' => 'Encuentra las mejores aplicaciones de diseño, utilidades y productividad.',
            'seo_keywords' => 'mac apps, descargar mac, software mac, utilidades',
        ]);

        $response->assertRedirect(route('admin.settings'));

        $this->assertEquals('Portal de Descargas Mac Pro - 2026', Setting::get('seo_meta_title'));
        $this->assertEquals('Encuentra las mejores aplicaciones de diseño, utilidades y productividad.', Setting::get('seo_meta_description'));
        $this->assertEquals('mac apps, descargar mac, software mac, utilidades', Setting::get('seo_keywords'));

        // Verify public home page reflects updated SEO meta
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Portal de Descargas Mac Pro - 2026');
        $homeResponse->assertSee('Encuentra las mejores aplicaciones de diseño, utilidades y productividad.');
    }

    public function test_admin_can_upload_custom_logo_image(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminUser();
        $file = UploadedFile::fake()->image('custom_logo.png', 200, 50);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'logo_type' => 'image',
            'logo_image' => $file,
        ]);

        $response->assertRedirect(route('admin.settings'));

        $savedPath = Setting::get('logo_image');
        $this->assertNotEmpty($savedPath);
        $this->assertEquals('image', Setting::get('logo_type'));

        // Verify file stored
        $relativeDiskPath = str_replace('/storage/', '', $savedPath);
        Storage::disk('public')->assertExists($relativeDiskPath);
    }
}
