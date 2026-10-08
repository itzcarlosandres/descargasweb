<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Category;
use App\Models\Setting;
use App\Services\Scraper\HaxmacImporter;
use App\Services\Scraper\TorrentmacImporter;
use App\Services\Storage\CloudflareR2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScraperController extends Controller
{
    public function __construct(
        protected HaxmacImporter $importer,
        protected TorrentmacImporter $torrentmacImporter
    ) {}

    /**
     * Show scraper management dashboard
     */
    public function index()
    {
        $stats = [
            'total_apps' => Application::count(),
            'total_torrent_apps' => Application::where('has_torrent', true)->count(),
            'total_categories' => Category::count(),
            'total_versions' => ApplicationVersion::count(),
            'last_sync' => Setting::get('scraper_last_sync', 'Nunca'),
            'last_torrent_sync' => Setting::get('torrentmac_last_sync', 'Nunca'),
            'cron_enabled' => (bool) Setting::get('scraper_cron_enabled', false),
            'cron_time' => Setting::get('scraper_cron_time', '03:00'),
            'cron_pages' => (int) Setting::get('scraper_cron_pages', 2),
            'cron_limit' => (int) Setting::get('scraper_cron_limit', 10),
            'cron_frequency' => Setting::get('scraper_cron_frequency', '2hours'),
            'draft_apps' => Application::where('published', false)->count(),
            'drip_mode' => (bool) Setting::get('scraper_drip_feed_mode', false),
            'storage_disk' => $this->torrentmacImporter->getStorageDisk(),
            'torrent_storage_target' => $this->torrentmacImporter->getConfiguredStorageTarget(),
            'r2_configured' => app(CloudflareR2Service::class)->isConfigured(),
        ];

        $categories = Category::withCount('applications')->ordered()->get();
        $recentApps = Application::with('category')->latest()->take(15)->get();

        $latestData = [
            'items' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 1,
                'has_next' => false,
                'has_prev' => false,
            ],
        ];

        try {
            $latestData = $this->importer->getLatestHaxmacApps(1, true);
        } catch (\Throwable $e) {
            Log::warning("Could not fetch latest HaxMac apps: {$e->getMessage()}");
        }

        $haxmacLatest = $latestData['items'];
        $haxmacPagination = $latestData['pagination'];

        $haxmacCategories = [
            ['slug' => 'all', 'name' => 'Todos los Posts', 'icon' => '🌐'],
            ['slug' => 'mac-games', 'name' => 'Juegos (Games)', 'icon' => '🎮'],
            ['slug' => 'apple-arcade', 'name' => 'Apple Arcade', 'icon' => '🕹️'],
            ['slug' => 'system-utilities', 'name' => 'System Utilities', 'icon' => '⚙️'],
            ['slug' => 'productivity-business', 'name' => 'Productivity & Business', 'icon' => '💼'],
            ['slug' => 'media-design', 'name' => 'Media & Design', 'icon' => '🎨'],
            ['slug' => 'developer-tools', 'name' => 'Developer Tools', 'icon' => '💻'],
            ['slug' => 'mobile-tools', 'name' => 'Mobile Tools', 'icon' => '📱'],
            ['slug' => 'lifestyle-everyday', 'name' => 'Lifestyle & Everyday', 'icon' => '☕'],
            ['slug' => 'adobe', 'name' => 'Adobe Collection', 'icon' => '🅰️'],
            ['slug' => 'editors-choice', 'name' => 'Editor’s Choice', 'icon' => '⭐'],
            ['slug' => 'assassins-creed', 'name' => 'Assassin’s Creed', 'icon' => '🗡️'],
            ['slug' => 'gta-grand-theft-auto', 'name' => 'GTA: Grand Theft Auto', 'icon' => '🚗'],
            ['slug' => 'the-trilogy', 'name' => 'GTA: The Trilogy', 'icon' => '🔥'],
        ];

        $torrentmacCategories = [
            ['slug' => 'all', 'name' => 'Todos los Torrents', 'icon' => '🧲'],
            ['slug' => 'apps', 'name' => 'Todas las Apps', 'icon' => '💻'],
            ['slug' => 'games', 'name' => 'Juegos (Mac Games)', 'icon' => '🎮'],
            ['slug' => 'utilities-tools', 'name' => 'Utilidades y Herramientas', 'icon' => '⚙️'],
            ['slug' => 'audio-editors', 'name' => 'Audio y Música', 'icon' => '🎵'],
            ['slug' => 'developer-tools', 'name' => 'Developer Tools', 'icon' => '🧑‍💻'],
            ['slug' => 'productivity', 'name' => 'Productividad', 'icon' => '💼'],
            ['slug' => 'video', 'name' => 'Video y Edición', 'icon' => '🎬'],
            ['slug' => 'graphics-design', 'name' => 'Diseño Gráfico', 'icon' => '🎨'],
            ['slug' => 'photography', 'name' => 'Fotografía', 'icon' => '📷'],
            ['slug' => 'plugins', 'name' => 'Plugins', 'icon' => '🔌'],
        ];

        return view('admin.scraper.index', compact(
            'stats',
            'categories',
            'recentApps',
            'haxmacLatest',
            'haxmacPagination',
            'haxmacCategories',
            'torrentmacCategories'
        ));
    }

    /**
     * Get paginated latest programs from HaxMac (with category filter support)
     */
    public function getLatest(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $category = (string) $request->input('category', 'all');

        try {
            $data = $this->importer->getLatestHaxmacApps($page, true, $category);

            return response()->json([
                'success' => true,
                'items' => $data['items'],
                'pagination' => $data['pagination'],
                'category' => $category,
            ]);
        } catch (\Throwable $e) {
            Log::error("ScraperController getLatest error on page {$page} cat {$category}: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'No se pudo cargar la página '.$page.' de HaxMac',
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 1,
                    'has_next' => false,
                    'has_prev' => false,
                ],
            ], 500);
        }
    }

    /**
     * Clear cached pending updates
     */
    protected function clearPendingUpdatesCache(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("scraper_pending_updates_{$i}");
        }
    }

    /**
     * Get list of applications that exist locally and have newer versions available on HaxMac
     */
    public function getPendingUpdates(Request $request): JsonResponse
    {
        $pages = max(1, min(5, (int) $request->input('pages', 2)));
        $forceRefresh = $request->boolean('refresh', false);
        $cacheKey = "scraper_pending_updates_{$pages}";

        try {
            if ($forceRefresh) {
                Cache::forget($cacheKey);
            }

            $updates = Cache::remember($cacheKey, 90, function () use ($pages) {
                return $this->importer->getPendingUpdates($pages);
            });

            return response()->json([
                'success' => true,
                'count' => count($updates),
                'items' => $updates,
            ]);
        } catch (\Throwable $e) {
            Log::error("ScraperController getPendingUpdates error: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Error al escanear actualizaciones: '.$e->getMessage(),
                'count' => 0,
                'items' => [],
            ], 500);
        }
    }

    /**
     * Search HaxMac in real-time with pagination
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'page' => 'nullable|integer|min:1',
        ]);

        $page = max(1, (int) $request->input('page', 1));
        $data = $this->importer->searchHaxmac($request->q, $page, true);

        return response()->json([
            'success' => true,
            'query' => $request->q,
            'total' => count($data['items']),
            'results' => $data['items'],
            'pagination' => $data['pagination'],
        ]);
    }

    /**
     * Import a single application with 1-click
     */
    public function importSingle(Request $request): JsonResponse
    {
        $request->validate([
            'url_or_slug' => 'required|string',
            'download_images' => 'nullable|boolean',
        ]);

        $downloadImages = $request->boolean('download_images', true);
        $app = $this->importer->importApp($request->url_or_slug, $downloadImages);

        if ($app) {
            Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
            $this->clearPendingUpdatesCache();

            return response()->json([
                'success' => true,
                'message' => "Aplicación {$app->name} importada con éxito.",
                'app' => [
                    'id' => $app->id,
                    'name' => $app->name,
                    'version' => $app->version,
                    'category' => $app->category?->name,
                    'size' => $app->size,
                    'download_url' => $app->download_url_external,
                    'public_url' => route('app', $app->slug),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo importar la aplicación desde HaxMac.',
        ], 422);
    }

    /**
     * Import an entire category with configurable pages
     */
    public function importCategory(Request $request): JsonResponse
    {
        $request->validate([
            'category_slug' => 'required|string',
            'pages' => 'nullable|integer|min:1|max:10',
            'download_images' => 'nullable|boolean',
        ]);

        $pages = $request->integer('pages', 1);
        $downloadImages = $request->boolean('download_images', true);

        $summary = $this->importer->importCategory($request->category_slug, $pages, $downloadImages);
        Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        $this->clearPendingUpdatesCache();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'message' => "Importación completada: {$summary['imported']} nuevas, {$summary['updated']} actualizadas.",
        ]);
    }

    /**
     * Synchronize categories from HaxMac main menu
     */
    public function syncCategories(): JsonResponse
    {
        $result = $this->importer->syncCategories();

        return response()->json([
            'success' => true,
            'created' => $result['created'],
            'updated' => $result['updated'],
            'categories' => $result['categories'],
            'message' => "Categorías sincronizadas: {$result['created']} creadas, {$result['updated']} actualizadas.",
        ]);
    }

    /**
     * Scan and automatically import new additions and updates from HaxMac
     */
    public function syncUpdates(): JsonResponse
    {
        $result = $this->importer->syncLatestUpdates(true);
        Setting::set('scraper_last_sync', now()->format('Y-m-d H:i:s'), 'scraper');
        $this->clearPendingUpdatesCache();

        return response()->json([
            'success' => true,
            'new_imported' => $result['new_imported'],
            'updated' => $result['updated'],
            'total_checked' => $result['total_checked'],
            'items' => $result['items'],
            'message' => "Sincronización finalizada: {$result['new_imported']} programas nuevos importados, {$result['updated']} actualizados a su última versión.",
        ]);
    }

    /**
     * Update automated Cron configuration
     */
    public function updateCronSettings(Request $request)
    {
        $request->validate([
            'cron_enabled' => 'nullable|boolean',
            'cron_time' => 'required|string',
            'cron_pages' => 'required|integer|min:1|max:5',
            'cron_limit' => 'nullable|integer|min:1|max:50',
            'cron_frequency' => 'nullable|string',
            'drip_mode' => 'nullable|boolean',
        ]);

        Setting::set('scraper_cron_enabled', $request->boolean('cron_enabled'), 'scraper');
        Setting::set('scraper_cron_time', $request->cron_time, 'scraper');
        Setting::set('scraper_cron_pages', $request->integer('cron_pages'), 'scraper');
        Setting::set('scraper_cron_limit', $request->integer('cron_limit', 10), 'scraper');
        Setting::set('scraper_cron_frequency', $request->input('cron_frequency', '2hours'), 'scraper');
        Setting::set('scraper_drip_feed_mode', $request->boolean('drip_mode'), 'scraper');
        Setting::clearCache();

        return redirect()->route('admin.scraper')->with('success', 'Configuración de automatización guardada correctamente.');
    }

    /**
     * Instant toggle of automated Cron status
     */
    public function toggleCron(Request $request): JsonResponse
    {
        $current = (bool) Setting::get('scraper_cron_enabled', false);
        $new = ! $current;
        Setting::set('scraper_cron_enabled', $new ? 1 : 0, 'scraper');
        Setting::clearCache();

        return response()->json([
            'success' => true,
            'cron_enabled' => $new,
            'message' => $new ? 'Cron activado exitosamente.' : 'Cron pausado.',
        ]);
    }

    /**
     * Release a batch of draft applications to published status (Drip Feed / Goteo)
     */
    public function releaseDripBatch(Request $request): JsonResponse
    {
        $limit = max(1, min(50, (int) $request->input('limit', Setting::get('scraper_cron_limit', 10))));

        $drafts = Application::where('published', false)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        $count = $drafts->count();
        if ($count > 0) {
            foreach ($drafts as $app) {
                $app->update([
                    'published' => true,
                    'released_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $remaining = Application::where('published', false)->count();
        $totalPublished = Application::where('published', true)->count();

        return response()->json([
            'success' => true,
            'published_count' => $count,
            'remaining_drafts' => $remaining,
            'total_published' => $totalPublished,
            'message' => $count > 0
                ? "Se han publicado {$count} programas con éxito. Quedan {$remaining} en Cola Draft."
                : 'No hay programas en Cola Draft para publicar.',
        ]);
    }

    /**
     * Get paginated latest programs from TorrentMac (with category filter support)
     */
    public function getTorrentmacLatest(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $category = (string) $request->input('category', 'all');

        try {
            $data = $this->torrentmacImporter->getTorrentmacApps($page, true, $category);

            return response()->json([
                'success' => true,
                'items' => $data['items'],
                'pagination' => $data['pagination'],
                'category' => $category,
            ]);
        } catch (\Throwable $e) {
            Log::error("ScraperController getTorrentmacLatest error on page {$page} cat {$category}: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'No se pudo cargar la página '.$page.' de TorrentMac: '.$e->getMessage(),
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 1,
                    'has_next' => false,
                    'has_prev' => false,
                ],
            ], 500);
        }
    }

    /**
     * Clear cached TorrentMac pending updates
     */
    protected function clearTorrentmacPendingUpdatesCache(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("scraper_torrentmac_pending_updates_{$i}");
        }
    }

    /**
     * Get list of applications that exist locally and have newer versions available on TorrentMac
     */
    public function getTorrentmacPendingUpdates(Request $request): JsonResponse
    {
        $pages = max(1, min(5, (int) $request->input('pages', 2)));
        $forceRefresh = $request->boolean('refresh', false);
        $cacheKey = "scraper_torrentmac_pending_updates_{$pages}";

        try {
            if ($forceRefresh) {
                Cache::forget($cacheKey);
            }

            $updates = Cache::remember($cacheKey, 90, function () use ($pages) {
                return $this->torrentmacImporter->getPendingUpdates($pages);
            });

            return response()->json([
                'success' => true,
                'count' => count($updates),
                'items' => $updates,
            ]);
        } catch (\Throwable $e) {
            Log::error("ScraperController getTorrentmacPendingUpdates error: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Error al escanear actualizaciones de TorrentMac: '.$e->getMessage(),
                'count' => 0,
                'items' => [],
            ], 500);
        }
    }

    /**
     * Search TorrentMac in real-time with pagination
     */
    public function searchTorrentmac(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'page' => 'nullable|integer|min:1',
        ]);

        $page = max(1, (int) $request->input('page', 1));

        try {
            $data = $this->torrentmacImporter->searchTorrentmac($request->q, $page, true);

            return response()->json([
                'success' => true,
                'query' => $request->q,
                'total' => count($data['items']),
                'results' => $data['items'],
                'pagination' => $data['pagination'],
            ]);
        } catch (\Throwable $e) {
            Log::error("ScraperController searchTorrentmac error: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar en TorrentMac: '.$e->getMessage(),
                'results' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 1,
                    'has_next' => false,
                    'has_prev' => false,
                ],
            ], 500);
        }
    }

    /**
     * Import a single application from TorrentMac with 1-click
     */
    public function importTorrentmacSingle(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'download_images' => 'nullable|boolean',
        ]);

        $downloadImages = $request->boolean('download_images', true);
        $app = $this->torrentmacImporter->importAppByUrl($request->url, $downloadImages);

        if ($app) {
            $this->clearTorrentmacPendingUpdatesCache();

            return response()->json([
                'success' => true,
                'message' => "Aplicación {$app->name} importada con torrent con éxito.",
                'app' => [
                    'id' => $app->id,
                    'name' => $app->name,
                    'version' => $app->version,
                    'category' => $app->category?->name,
                    'size' => $app->size,
                    'has_torrent' => $app->has_torrent,
                    'torrent_url' => $app->torrent_url,
                    'is_dual' => ($app->has_torrent && ! empty($app->download_url_external)),
                    'public_url' => route('app', $app->slug),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo importar la aplicación desde TorrentMac.',
        ], 422);
    }

    /**
     * Scan and automatically import new additions from TorrentMac
     */
    public function syncTorrentmacLatest(Request $request): JsonResponse
    {
        $pages = max(1, min(5, (int) $request->input('pages', 1)));
        $downloadImages = $request->boolean('download_images', true);

        $result = $this->torrentmacImporter->syncLatestUpdates($pages, $downloadImages);
        $this->clearTorrentmacPendingUpdatesCache();

        return response()->json([
            'success' => true,
            'new_imported' => $result['new_imported'],
            'updated' => $result['updated'],
            'total_checked' => $result['total_checked'],
            'items' => $result['items'],
            'message' => "Sincronización TorrentMac finalizada: {$result['new_imported']} programas nuevos importados, {$result['updated']} actualizados.",
        ]);
    }

    /**
     * Update TorrentMac destination storage driver ('local' or 'r2')
     */
    public function updateTorrentStorageSettings(Request $request): JsonResponse
    {
        $request->validate([
            'storage_disk' => 'required|in:local,r2',
        ]);

        $disk = $request->input('storage_disk');
        Setting::set('torrentmac_storage_disk', $disk, 'scraper');
        Setting::clearCache();

        $r2Configured = ! empty(config('filesystems.disks.r2.key')) && ! empty(config('filesystems.disks.r2.bucket'));

        return response()->json([
            'success' => true,
            'storage_disk' => $disk,
            'effective_disk' => ($disk === 'r2' && $r2Configured) ? 'r2' : 'public',
            'r2_configured' => $r2Configured,
            'message' => $disk === 'r2'
                ? ($r2Configured ? 'Almacenamiento de TorrentMac configurado en Cloudflare R2.' : 'R2 seleccionado, pero faltan credenciales en .env; se usará almacenamiento local como respaldo.')
                : 'Almacenamiento de TorrentMac configurado en Servidor Local (public).',
        ]);
    }
}
