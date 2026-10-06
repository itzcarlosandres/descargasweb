<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Tests\TestCase;

class PortalTest extends TestCase
{
    private function createCategoryAndApp(array $appAttributes = []): array
    {
        $category = Category::create([
            'name' => 'Utilidades',
            'slug' => 'utilidades',
            'description' => 'Herramientas útiles',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $app = Application::create(array_merge([
            'category_id' => $category->id,
            'name' => '7-Zip',
            'slug' => '7-zip',
            'short_description' => 'Compresor de archivos de alta tasa de compresión',
            'description' => '7-Zip es un archivador de ficheros de código abierto.',
            'version' => '24.09',
            'size' => '1.5 MB',
            'developer' => 'Igor Pavlov',
            'license' => 'Open Source',
            'platform' => 'Windows',
            'download_url' => 'https://example.com/download/7z.exe',
            'download_url_external' => 'https://7-zip.org',
            'published' => true,
            'featured' => true,
            'popular' => true,
            'downloads' => 1500,
            'rating' => 4.5,
            'reviews_count' => 10,
            'released_at' => now(),
        ], $appAttributes));

        return [$category, $app];
    }

    public function test_home_page_renders_successfully(): void
    {
        $this->createCategoryAndApp();

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('7-Zip');
        $response->assertSee('Discover the best free Mac apps & games');
    }

    public function test_categories_page_renders_successfully(): void
    {
        $this->createCategoryAndApp();

        $response = $this->get(route('categories'));

        $response->assertStatus(200);
        $response->assertSee('Utilidades');
        $response->assertSee('1 apps');
    }

    public function test_category_detail_page_renders(): void
    {
        [$category] = $this->createCategoryAndApp();

        $response = $this->get(route('category', $category->slug));

        $response->assertStatus(200);
        $response->assertSee('Utilidades');
        $response->assertSee('7-Zip');
    }

    public function test_app_detail_page_renders(): void
    {
        [, $app] = $this->createCategoryAndApp();

        $response = $this->get(route('app', $app->slug));

        $response->assertStatus(200);
        $response->assertSee('7-Zip');
        $response->assertSee('Descargar Ahora');
        $response->assertSee('Sitio Web Oficial');
    }

    public function test_search_works_with_query_and_filters(): void
    {
        $this->createCategoryAndApp();

        $response = $this->get(route('search', ['q' => '7-Zip']));

        $response->assertStatus(200);
        $response->assertSee('7-Zip');

        $emptyResponse = $this->get(route('search', ['q' => 'InexistenteXYZ']));
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('No se encontraron resultados');
    }

    public function test_download_increments_counter_and_records_download(): void
    {
        [, $app] = $this->createCategoryAndApp();
        $initialDownloads = $app->downloads;

        // Dedicated download countdown page with obfuscated token
        $pageResponse = $this->get(route('download', $app->slug));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee($app->name);
        $downloadToken = $pageResponse->viewData('downloadToken');
        $this->assertNotEmpty($downloadToken);

        // Actual download action via obfuscated token
        $response = $this->get(route('download.token', ['token' => $downloadToken]));

        $response->assertRedirect($app->download_url_external);
        $app->refresh();
        $this->assertEquals($initialDownloads + 1, $app->downloads);

        $this->assertDatabaseHas('downloads', [
            'application_id' => $app->id,
        ]);
    }

    public function test_user_can_submit_review(): void
    {
        [, $app] = $this->createCategoryAndApp();

        $response = $this->post(route('app.review', $app->slug), [
            'author_name' => 'Carlos',
            'rating' => 5,
            'comment' => 'Excelente aplicación, muy rápida.',
        ]);

        $response->assertSessionHas('review_success');
        $this->assertDatabaseHas('reviews', [
            'application_id' => $app->id,
            'author_name' => 'Carlos',
            'rating' => 5,
            'approved' => true,
        ]);

        $app->refresh();
        $this->assertEquals(5.0, $app->rating);
        $this->assertEquals(1, $app->reviews_count);
    }

    public function test_login_flow(): void
    {
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('secret123'),
        ]);

        $failResponse = $this->post(route('login.post'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);
        $failResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        $successResponse = $this->post(route('login.post'), [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);
        $successResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_admin_requires_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('secret123'),
        ]);

        $authResponse = $this->actingAs($user)->get(route('admin.dashboard'));
        $authResponse->assertStatus(200);
    }

    public function test_admin_can_create_and_update_application(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@domain.com',
            'password' => bcrypt('password'),
        ]);

        [$category] = $this->createCategoryAndApp();

        $storeResponse = $this->actingAs($user)->post(route('admin.applications.store'), [
            'name' => 'VLC Media Player',
            'category_id' => $category->id,
            'version' => '3.0.21',
            'developer' => 'VideoLAN',
            'download_url' => 'https://videolan.org/vlc.exe',
            'download_url_external' => 'https://videolan.org',
            'published' => 1,
        ]);

        $storeResponse->assertRedirect(route('admin.applications'));
        $this->assertDatabaseHas('applications', [
            'name' => 'VLC Media Player',
            'slug' => 'vlc-media-player',
            'download_url_external' => 'https://videolan.org',
        ]);

        $vlc = Application::where('slug', 'vlc-media-player')->first();

        $updateResponse = $this->actingAs($user)->put(route('admin.applications.update', $vlc), [
            'name' => 'VLC Media Player Pro',
            'category_id' => $category->id,
            'version' => '3.0.22',
            'developer' => 'VideoLAN Community',
            'download_url' => 'https://videolan.org/vlc-pro.exe',
            'download_url_external' => 'https://videolan.org/pro',
            'published' => 1,
        ]);

        $updateResponse->assertRedirect(route('admin.applications'));
        $this->assertDatabaseHas('applications', [
            'id' => $vlc->id,
            'name' => 'VLC Media Player Pro',
            'version' => '3.0.22',
        ]);
    }

    public function test_user_can_reply_and_vote_on_comments(): void
    {
        [, $app] = $this->createCategoryAndApp();

        $comment = Review::create([
            'application_id' => $app->id,
            'author_name' => 'María',
            'rating' => 5,
            'comment' => 'Funciona perfecto en macOS Sonoma M2',
            'approved' => true,
        ]);

        // Post a reply
        $replyResponse = $this->post(route('app.review', $app->slug), [
            'author_name' => 'David',
            'comment' => 'Confirmado, en M3 Pro también va excelente.',
            'parent_id' => $comment->id,
        ]);
        $replyResponse->assertSessionHas('review_success');

        $this->assertDatabaseHas('reviews', [
            'parent_id' => $comment->id,
            'author_name' => 'David',
        ]);

        // Upvote comment via JSON
        $voteResponse = $this->postJson(route('review.vote', $comment), [
            'type' => 'up',
        ]);
        $voteResponse->assertStatus(200);
        $voteResponse->assertJson([
            'success' => true,
            'upvotes' => 1,
        ]);

        $comment->refresh();
        $this->assertEquals(1, $comment->upvotes);
    }

    public function test_guides_and_institutional_pages_render_and_live_search_works(): void
    {
        $this->get(route('guide.sip'))->assertStatus(200)->assertSee('Disable System Integrity Protection');
        $this->get(route('guide.fix'))->assertStatus(200)->assertSee('Fix Damaged Apps');
        $this->get(route('dmca'))->assertStatus(200)->assertSee('DMCA Copyright Policy');
        $this->get(route('privacy'))->assertStatus(200)->assertSee('Privacy Policy');
        $this->get(route('terms'))->assertStatus(200)->assertSee('Terms and Conditions');
        $this->get(route('about'))->assertStatus(200)->assertSee('About HackMac.cc');
        $this->get(route('contact'))->assertStatus(200)->assertSee('Get in Touch');

        // Test Contact Submission
        $contactResponse = $this->post(route('contact.submit'), [
            'name' => 'Tester',
            'email' => 'test@example.com',
            'subject' => 'General Inquiry',
            'message' => 'Hello team, great job with the site!',
        ]);
        $contactResponse->assertSessionHas('contact_success');

        // Test Live Search API
        [, $app] = $this->createCategoryAndApp();
        $apiResponse = $this->getJson(route('api.search.live', ['q' => '7-Zip']));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment([
            'name' => '7-Zip',
        ]);
    }

    public function test_admin_can_toggle_featured_status(): void
    {
        $admin = User::factory()->create();
        [, $app] = $this->createCategoryAndApp(['featured' => false]);
        $this->assertFalse($app->featured);

        // Toggle ON via JSON
        $response = $this->actingAs($admin)->postJson(route('admin.applications.toggle-featured', $app));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'featured' => true,
        ]);

        $app->refresh();
        $this->assertTrue($app->featured);

        // Toggle OFF via redirect
        $response2 = $this->actingAs($admin)->post(route('admin.applications.toggle-featured', $app));
        $response2->assertRedirect();
        $app->refresh();
        $this->assertFalse($app->featured);
    }

    public function test_seo_robots_sitemap_and_open_graph_tags(): void
    {
        // 1. Robots.txt
        $robots = $this->get('/robots.txt');
        $robots->assertStatus(200);
        $robots->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $robots->assertSee('User-agent: *');
        $robots->assertSee('Disallow: /admin/');
        $robots->assertSee('Sitemap: '.url('sitemap.xml'), false);

        // 2. Sitemap.xml
        [$category, $app] = $this->createCategoryAndApp(['published' => true]);
        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertStatus(200);
        $sitemap->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $sitemap->assertSee('urlset');
        $sitemap->assertSee(route('app', $app->slug), false);
        $sitemap->assertSee(route('category', $category->slug), false);

        // 3. Open Graph and Schema.org on App Detail
        $detail = $this->get(route('app', $app->slug));
        $detail->assertStatus(200);
        $detail->assertSee('property="og:title"', false);
        $detail->assertSee('property="og:image"', false);
        $detail->assertSee('name="twitter:card"', false);
        $detail->assertSee('SoftwareApplication', false);
    }
}
