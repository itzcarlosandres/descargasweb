<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Category;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\AI\GeminiService;
use App\Services\Storage\CloudflareR2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalDownloads = (int) Application::sum('downloads');
        $formattedDownloads = $totalDownloads >= 1000000
            ? number_format($totalDownloads / 1000000, 1).'M'
            : ($totalDownloads >= 1000
                ? number_format($totalDownloads / 1000, 1).'K'
                : number_format($totalDownloads));

        $stats = [
            'total_apps' => Application::count(),
            'published_apps' => Application::where('published', true)->count(),
            'pending_apps' => Application::where('published', false)->count(),
            'featured_apps' => Application::where('featured', true)->count(),
            'total_downloads' => $totalDownloads,
            'formatted_downloads' => $formattedDownloads,
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'updates_this_week' => Application::where('updated_at', '>=', now()->subWeek())->count(),
            'total_reviews' => Review::count(),
            'pending_reviews' => Review::where('approved', false)->count(),
        ];

        $recentApps = Application::with('category')->latest('updated_at')->limit(6)->get();
        $topDownloaded = Application::with('category')->orderByDesc('downloads')->limit(5)->get();
        $categoriesStats = Category::withCount('applications')->orderByDesc('applications_count')->limit(6)->get();
        $recentReviews = Review::with('application')->latest()->limit(4)->get();
        $featuredApps = Application::with('category')->where('featured', true)->latest()->limit(4)->get();

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'gemini_configured' => ! empty(setting('gemini_api_key')),
            'db_driver' => config('database.default'),
            'storage_symlink' => file_exists(public_path('storage')),
            'cache_driver' => config('cache.default'),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentApps',
            'topDownloaded',
            'categoriesStats',
            'recentReviews',
            'featuredApps',
            'systemInfo'
        ));
    }

    public function applications(Request $request)
    {
        $query = Application::with('category')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('published', true);
            } elseif ($request->status === 'draft') {
                $query->where('published', false);
            } elseif ($request->status === 'featured') {
                $query->where('featured', true);
            }
        }

        $applications = $query->paginate(15)->withQueryString();
        $categories = Category::ordered()->get();
        $totalApps = Application::count();
        $publishedApps = Application::where('published', true)->count();
        $featuredApps = Application::where('featured', true)->count();

        return view('admin.applications.index', compact('applications', 'categories', 'totalApps', 'publishedApps', 'featuredApps'));
    }

    public function createApplication()
    {
        $categories = Category::active()->ordered()->get();
        $application = new Application;
        $app = $application;

        return view('admin.applications.form', compact('application', 'app', 'categories'));
    }

    public function storeApplication(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:applications,slug',
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'developer' => 'nullable|string|max:255',
            'license' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:255',
            'download_url' => 'nullable|url|max:500',
            'download_url_external' => 'nullable|url|max:500',
            'download_mirrors' => 'nullable|array',
            'download_mirrors.*.name' => 'nullable|string|max:100',
            'download_mirrors.*.url' => 'nullable|string|max:1000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:6144',
            'changelog' => 'nullable|string',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['featured'] = $request->boolean('featured');
        $validated['popular'] = $request->boolean('popular');
        $validated['published'] = $request->boolean('published', true);

        // Parse custom download mirrors
        $mirrors = [];
        if ($request->has('download_mirrors') && is_array($request->input('download_mirrors'))) {
            foreach ($request->input('download_mirrors') as $mirror) {
                $url = trim($mirror['url'] ?? '');
                $name = trim($mirror['name'] ?? '');
                if (! empty($url)) {
                    $mirrors[] = [
                        'name' => ! empty($name) ? $name : 'Servidor Alternativo',
                        'url' => $url,
                    ];
                }
            }
        }
        $validated['download_mirrors'] = ! empty($mirrors) ? $mirrors : null;

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('icons', 'public');
        }

        if ($request->hasFile('screenshot')) {
            $validated['screenshot'] = $request->file('screenshot')->store('screenshots', 'public');
        }

        $app = Application::create($validated);

        if (! empty($validated['version'])) {
            $app->versions()->create([
                'version' => $validated['version'],
                'changelog' => $request->input('changelog'),
                'download_url' => $validated['download_url'] ?? null,
                'download_mirrors' => $validated['download_mirrors'] ?? null,
                'size' => $validated['size'] ?? null,
                'is_current' => true,
                'released_at' => now(),
            ]);
        }

        return redirect()->route('admin.applications')->with('success', '¡Programa publicado exitosamente!');
    }

    public function editApplication(Application $application)
    {
        $categories = Category::active()->ordered()->get();
        $application->load(['category', 'currentVersion', 'versions']);
        $app = $application;

        return view('admin.applications.form', compact('application', 'app', 'categories'));
    }

    public function updateApplication(Request $request, Application $application)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:applications,slug,'.$application->id,
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'developer' => 'nullable|string|max:255',
            'license' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:255',
            'download_url' => 'nullable|url|max:500',
            'download_url_external' => 'nullable|url|max:500',
            'download_mirrors' => 'nullable|array',
            'download_mirrors.*.name' => 'nullable|string|max:100',
            'download_mirrors.*.url' => 'nullable|string|max:1000',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:6144',
            'changelog' => 'nullable|string',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['featured'] = $request->boolean('featured');
        $validated['popular'] = $request->boolean('popular');
        $validated['published'] = $request->boolean('published');

        // Parse custom download mirrors
        $mirrors = [];
        if ($request->has('download_mirrors') && is_array($request->input('download_mirrors'))) {
            foreach ($request->input('download_mirrors') as $mirror) {
                $url = trim($mirror['url'] ?? '');
                $name = trim($mirror['name'] ?? '');
                if (! empty($url)) {
                    $mirrors[] = [
                        'name' => ! empty($name) ? $name : 'Servidor Alternativo',
                        'url' => $url,
                    ];
                }
            }
        }
        $validated['download_mirrors'] = ! empty($mirrors) ? $mirrors : null;

        if ($request->hasFile('icon')) {
            if ($application->icon) {
                Storage::disk('public')->delete($application->icon);
            }
            $validated['icon'] = $request->file('icon')->store('icons', 'public');
        }

        if ($request->hasFile('screenshot')) {
            if ($application->screenshot) {
                Storage::disk('public')->delete($application->screenshot);
            }
            $validated['screenshot'] = $request->file('screenshot')->store('screenshots', 'public');
        }

        $previousVersion = $application->version;
        $application->update($validated);

        if (! empty($validated['version'])) {
            $isNewVersion = ! empty($previousVersion) && $validated['version'] !== $previousVersion;

            if ($isNewVersion) {
                // Archive previous versions
                $application->versions()->update(['is_current' => false]);

                // Create new active version
                $application->versions()->create([
                    'version' => $validated['version'],
                    'changelog' => $request->input('changelog'),
                    'download_url' => $validated['download_url'] ?? null,
                    'download_mirrors' => $validated['download_mirrors'] ?? null,
                    'size' => $validated['size'] ?? null,
                    'is_current' => true,
                    'released_at' => now(),
                ]);
            } else {
                $currentVersion = $application->currentVersion;
                if ($currentVersion) {
                    $currentVersion->update([
                        'version' => $validated['version'],
                        'changelog' => $request->input('changelog', $currentVersion->changelog),
                        'download_url' => $validated['download_url'] ?? $currentVersion->download_url,
                        'download_mirrors' => $validated['download_mirrors'] ?? $currentVersion->download_mirrors,
                        'size' => $validated['size'] ?? $currentVersion->size,
                    ]);
                } else {
                    $application->versions()->create([
                        'version' => $validated['version'],
                        'changelog' => $request->input('changelog'),
                        'download_url' => $validated['download_url'] ?? null,
                        'download_mirrors' => $validated['download_mirrors'] ?? null,
                        'size' => $validated['size'] ?? null,
                        'is_current' => true,
                        'released_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.applications')->with('success', '¡Programa actualizado correctamente!');
    }

    public function destroyApplication(Application $application)
    {
        if ($application->icon) {
            Storage::disk('public')->delete($application->icon);
        }
        if ($application->screenshot) {
            Storage::disk('public')->delete($application->screenshot);
        }
        $application->delete();

        return redirect()->route('admin.applications')->with('success', 'Programa eliminado correctamente.');
    }

    public function toggleFeatured(Application $application): JsonResponse|RedirectResponse
    {
        $newFeatured = ! $application->featured;
        $application->update([
            'featured' => $newFeatured,
        ]);

        $featuredCount = Application::where('featured', true)->count();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'featured' => (bool) $application->featured,
                'featuredCount' => $featuredCount,
                'message' => $application->featured
                    ? "«{$application->name}» ahora está en Destacados (Featured)."
                    : "«{$application->name}» se quitó de Destacados (Featured).",
            ]);
        }

        return back()->with('success', $application->featured
            ? "«{$application->name}» se agregó a Destacados (Featured)."
            : "«{$application->name}» se quitó de Destacados (Featured).");
    }

    public function resetDownloads(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'zero');

        if ($mode === 'real') {
            $apps = Application::all();
            foreach ($apps as $app) {
                $realCount = $app->downloads()->count();
                $app->update(['downloads' => $realCount]);
            }
            $message = 'Contadores de descargas sincronizados con las descargas reales del portal.';
        } else {
            Application::query()->update(['downloads' => 0]);
            $message = 'Todos los contadores de descargas han sido reiniciados a 0 exitosamente.';
        }

        Cache::forget('home_stats');

        return back()->with('success', $message);
    }

    public function categories(Request $request)
    {
        $query = Category::withCount('applications')->ordered();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%");
            });
        }

        $categories = $query->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.form');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        Category::create($validated);

        return redirect()->route('admin.categories')->with('success', 'Category created successfully.');
    }

    public function editCategory(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()->route('admin.categories')->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->applications()->count() > 0) {
            return redirect()->route('admin.categories')->with('error', 'Cannot delete category with applications.');
        }
        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully.');
    }

    public function settings()
    {
        $settings = Setting::getAll();

        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'logo_type' => 'nullable|in:text_icon,image',
            'site_name' => 'nullable|string|max:100',
            'site_name_highlight' => 'nullable|string|max:100',
            'logo_icon' => 'nullable|string|max:50',
            'logo_size' => 'nullable|integer|min:14|max:48',
            'logo_image' => 'nullable|image|max:2048',
            'favicon_image' => 'nullable|mimes:ico,png,svg|max:1024',
            'seo_meta_title' => 'nullable|string|max:255',
            'seo_meta_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_og_image' => 'nullable|image|max:3072',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'footer_text' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'telegram_channel' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'gemini_api_key' => 'nullable|string|max:255',
            'gemini_model' => 'nullable|string|in:gemini-2.5-flash,gemini-2.5-pro,gemini-2.0-flash',
            'gemini_auto_generate' => 'nullable|string|in:0,1',
            'gemini_target_words' => 'nullable|integer|min:30|max:1000',
            'gemini_features_count' => 'nullable|integer|min:3|max:15',
            'r2_account_id' => 'nullable|string|max:255',
            'r2_access_key_id' => 'nullable|string|max:255',
            'r2_secret_access_key' => 'nullable|string|max:255',
            'r2_bucket' => 'nullable|string|max:255',
            'r2_url' => 'nullable|string|max:255',
            'torrentmac_storage_disk' => 'nullable|string|in:local,r2',
        ]);

        // Text & Branding settings
        if ($request->has('logo_type')) {
            Setting::set('logo_type', $validated['logo_type'] ?? 'text_icon', 'branding');
        }
        if ($request->has('site_name')) {
            Setting::set('site_name', $validated['site_name'] ?? 'HackMac', 'branding');
        }
        if ($request->has('site_name_highlight')) {
            Setting::set('site_name_highlight', $validated['site_name_highlight'] ?? '.cc', 'branding');
        }
        if ($request->has('logo_icon')) {
            Setting::set('logo_icon', $validated['logo_icon'] ?? 'finder', 'branding');
        }
        if ($request->has('logo_size')) {
            Setting::set('logo_size', (int) ($validated['logo_size'] ?? 24), 'branding');
        }

        // Logo Image upload / removal
        if ($request->hasFile('logo_image')) {
            $path = $request->file('logo_image')->store('branding', 'public');
            Setting::set('logo_image', Storage::url($path), 'branding');
        } elseif ($request->boolean('remove_logo_image')) {
            Setting::set('logo_image', null, 'branding');
        }

        // Favicon upload / removal
        if ($request->hasFile('favicon_image')) {
            $path = $request->file('favicon_image')->store('branding', 'public');
            Setting::set('favicon_image', Storage::url($path), 'branding');
        } elseif ($request->boolean('remove_favicon_image')) {
            Setting::set('favicon_image', null, 'branding');
        }

        // SEO OpenGraph upload / removal
        if ($request->hasFile('seo_og_image')) {
            $path = $request->file('seo_og_image')->store('branding', 'public');
            Setting::set('seo_og_image', Storage::url($path), 'seo');
        } elseif ($request->boolean('remove_seo_og_image')) {
            Setting::set('seo_og_image', null, 'seo');
        }

        // SEO Fields
        if ($request->has('seo_meta_title')) {
            Setting::set('seo_meta_title', $validated['seo_meta_title'] ?? null, 'seo');
        }
        if ($request->has('seo_meta_description')) {
            Setting::set('seo_meta_description', $validated['seo_meta_description'] ?? null, 'seo');
        }
        if ($request->has('seo_keywords')) {
            Setting::set('seo_keywords', $validated['seo_keywords'] ?? null, 'seo');
        }

        // General Info & Descriptions
        if ($request->has('site_tagline')) {
            Setting::set('site_tagline', $validated['site_tagline'] ?? null, 'general');
        }
        if ($request->has('site_description')) {
            Setting::set('site_description', $validated['site_description'] ?? null, 'general');
        }
        if ($request->has('footer_text')) {
            Setting::set('footer_text', $validated['footer_text'] ?? null, 'general');
        }
        if ($request->has('contact_email')) {
            Setting::set('contact_email', $validated['contact_email'] ?? null, 'general');
        }
        if ($request->has('telegram_channel')) {
            Setting::set('telegram_channel', $validated['telegram_channel'] ?? null, 'general');
        }
        if ($request->has('twitter_url')) {
            Setting::set('twitter_url', $validated['twitter_url'] ?? null, 'general');
        }

        // Gemini AI Settings
        if ($request->has('gemini_api_key')) {
            Setting::set('gemini_api_key', $validated['gemini_api_key'] ?? '', 'ai');
        }
        if ($request->has('gemini_model')) {
            Setting::set('gemini_model', $validated['gemini_model'] ?? 'gemini-2.5-flash', 'ai');
        }
        if ($request->has('gemini_auto_generate')) {
            Setting::set('gemini_auto_generate', $validated['gemini_auto_generate'] ?? '1', 'ai');
        }
        if ($request->has('gemini_target_words')) {
            Setting::set('gemini_target_words', (string) ($validated['gemini_target_words'] ?? 60), 'ai');
        }
        if ($request->has('gemini_features_count')) {
            Setting::set('gemini_features_count', (string) ($validated['gemini_features_count'] ?? 7), 'ai');
        }

        // Cloudflare R2 & Storage Settings
        if ($request->has('r2_account_id')) {
            Setting::set('r2_account_id', trim((string) ($validated['r2_account_id'] ?? '')), 'storage');
        }
        if ($request->has('r2_access_key_id')) {
            Setting::set('r2_access_key_id', trim((string) ($validated['r2_access_key_id'] ?? '')), 'storage');
        }
        if ($request->has('r2_secret_access_key')) {
            Setting::set('r2_secret_access_key', trim((string) ($validated['r2_secret_access_key'] ?? '')), 'storage');
        }
        if ($request->has('r2_bucket')) {
            Setting::set('r2_bucket', trim((string) ($validated['r2_bucket'] ?? '')), 'storage');
        }
        if ($request->has('r2_url')) {
            Setting::set('r2_url', trim((string) ($validated['r2_url'] ?? '')), 'storage');
        }
        if ($request->has('torrentmac_storage_disk')) {
            Setting::set('torrentmac_storage_disk', $validated['torrentmac_storage_disk'] ?? 'local', 'storage');
        }

        Setting::clearCache();

        $redirectUrl = $request->filled('active_tab')
            ? route('admin.settings', ['tab' => $request->input('active_tab')])
            : route('admin.settings');

        return redirect($redirectUrl)->with('success', 'Configuración guardada exitosamente.');
    }

    /**
     * Test Google Gemini API connection
     */
    public function testGemini(Request $request, GeminiService $geminiService): JsonResponse
    {
        $apiKey = $request->input('api_key');
        $model = $request->input('model');

        $result = $geminiService->testConnection($apiKey, $model);

        return response()->json($result);
    }

    /**
     * Test Cloudflare R2 Storage connection
     */
    public function testR2(Request $request, CloudflareR2Service $r2Service): JsonResponse
    {
        $accountId = $request->input('account_id');
        $accessKey = $request->input('access_key_id');
        $secretKey = $request->input('secret_access_key');
        $bucket = $request->input('bucket');

        $result = $r2Service->testConnection($accountId, $accessKey, $secretKey, $bucket);

        return response()->json($result);
    }

    /**
     * Regenerate Application description and 7 features using Gemini 2.5 AI
     */
    public function generateAiContent(Application $application, GeminiService $geminiService): JsonResponse
    {
        $content = $geminiService->generateAppContent(
            name: $application->name,
            category: $application->category?->name,
            version: $application->version,
            shortDesc: $application->short_description,
            scrapedDesc: $application->description,
            categorySlug: $application->category?->slug
        );

        if (! $content) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el contenido con Gemini AI. Verifica tu clave API y cuota en Configuración.',
            ], 422);
        }

        $application->update([
            'description' => $content['description'],
            'features' => $content['features'],
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Contenido generado exitosamente con Gemini 2.5!',
            'description' => $content['description'],
            'features' => $content['features'],
        ]);
    }
}
