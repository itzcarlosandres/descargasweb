<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('HackMac.cc Admin Console');
        $response->assertSee('Publicar Programa');
        $response->assertSee('Top 5 Más Descargados');
        $response->assertSee('Distribución por Categorías');
        $response->assertSee('Centro de Control Rápido');
        $response->assertSee('Estado del Servidor');
    }

    public function test_admin_can_view_applications_index(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilidades',
            'is_active' => true,
        ]);
        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'CleanMyMac X',
            'slug' => 'cleanmymac-x',
            'version' => 'v4.15.2',
            'size' => '120 MB',
            'published' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applications'));

        $response->assertStatus(200);
        $response->assertSee('CleanMyMac X');
        $response->assertSee('v4.15.2');
        $response->assertSee('120 MB');
    }

    public function test_admin_can_view_create_application_form(): void
    {
        $admin = $this->createAdminUser();
        Category::create([
            'name' => 'Diseño Gráfico',
            'slug' => 'diseno-grafico',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applications.create'));

        $response->assertStatus(200);
        $response->assertSee('Publicar Nuevo Programa macOS');
        $response->assertSee('Diseño Gráfico');
        $response->assertSee('Simulador en Tiempo Real');
    }

    public function test_admin_can_store_new_application_with_version_and_size(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::create([
            'name' => 'Video & Cine',
            'slug' => 'video-cine',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applications.store'), [
            'name' => 'Final Cut Pro X',
            'slug' => 'final-cut-pro-x',
            'category_id' => $category->id,
            'version' => 'v10.8.1',
            'size' => '3.8 GB',
            'developer' => 'Apple Inc.',
            'license' => 'Full Pre-activado',
            'platform' => 'macOS Universal (Apple Silicon & Intel)',
            'download_url' => 'https://example.com/fcp.dmg',
            'short_description' => 'Edición de video profesional revolucionaria.',
            'description' => 'Instrucciones completas y notas.',
            'changelog' => 'Mejoras de rendimiento en M3 y M4.',
            'published' => '1',
            'featured' => '1',
        ]);

        $response->assertRedirect(route('admin.applications'));
        $this->assertDatabaseHas('applications', [
            'name' => 'Final Cut Pro X',
            'slug' => 'final-cut-pro-x',
            'version' => 'v10.8.1',
            'size' => '3.8 GB',
            'featured' => true,
            'published' => true,
        ]);

        $app = Application::where('slug', 'final-cut-pro-x')->first();
        $this->assertNotNull($app);
        $this->assertDatabaseHas('application_versions', [
            'application_id' => $app->id,
            'version' => 'v10.8.1',
            'size' => '3.8 GB',
            'is_current' => true,
        ]);
    }

    public function test_admin_can_view_edit_application_form(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::create([
            'name' => 'Audio',
            'slug' => 'audio',
            'is_active' => true,
        ]);
        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Logic Pro',
            'slug' => 'logic-pro',
            'version' => 'v11.0',
            'size' => '1.2 GB',
            'published' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applications.edit', $app));

        $response->assertStatus(200);
        $response->assertSee('Editar: Logic Pro');
    }

    public function test_admin_can_update_application(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::create([
            'name' => 'Productividad',
            'slug' => 'productividad',
            'is_active' => true,
        ]);
        $app = Application::create([
            'category_id' => $category->id,
            'name' => 'Notion Mac',
            'slug' => 'notion-mac',
            'version' => 'v2.1',
            'size' => '85 MB',
            'published' => false,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.applications.update', $app), [
            'name' => 'Notion Mac Enhanced',
            'slug' => 'notion-mac',
            'category_id' => $category->id,
            'version' => 'v2.2',
            'size' => '90 MB',
            'published' => '1',
            'featured' => '0',
            'popular' => '1',
            'changelog' => 'Corrección de inicio rápido.',
        ]);

        $response->assertRedirect(route('admin.applications'));
        $this->assertDatabaseHas('applications', [
            'id' => $app->id,
            'name' => 'Notion Mac Enhanced',
            'version' => 'v2.2',
            'size' => '90 MB',
            'published' => true,
            'popular' => true,
        ]);
    }

    public function test_admin_can_view_categories_index(): void
    {
        $admin = $this->createAdminUser();
        Category::create([
            'name' => 'Desarrollo Web',
            'slug' => 'desarrollo-web',
            'icon' => '💻',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categories'));

        $response->assertStatus(200);
        $response->assertSee('Desarrollo Web');
        $response->assertSee('💻');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Seguridad y Privacidad',
            'slug' => 'seguridad-privacidad',
            'description' => 'Antivirus y herramientas de seguridad para Mac',
            'icon' => '🛡️',
            'sort_order' => 3,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Seguridad y Privacidad',
            'slug' => 'seguridad-privacidad',
            'icon' => '🛡️',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_create_category_form(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertSee('Crear Nueva Categoría');
        $response->assertSee('Vista Previa');
        $response->assertDontSee('$(val)');
    }

    public function test_admin_can_view_edit_category_form(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilidades',
            'icon' => 'wrench',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertSee('Editar Categoría: Utilidades');
        $response->assertDontSee('$(val)');
    }

    public function test_lucide_icons_render_properly(): void
    {
        $rendered = Blade::render('<x-lucide-palette class="w-5 h-5 text-primary" />');
        $this->assertStringContainsString('<svg', $rendered);
        $this->assertStringContainsString('w-5 h-5 text-primary', $rendered);

        $genericRendered = Blade::render('<x-icon name="lucide-code" class="w-4 h-4" />');
        $this->assertStringContainsString('<svg', $genericRendered);
    }

    public function test_admin_can_update_logo_size_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'active_tab' => 'logo',
            'logo_type' => 'text_icon',
            'site_name' => 'Test',
            'site_name_highlight' => 'App',
            'logo_icon' => 'finder',
            'logo_size' => 18,
            'gemini_target_words' => 60,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(18, Setting::get('logo_size'));
    }
}
