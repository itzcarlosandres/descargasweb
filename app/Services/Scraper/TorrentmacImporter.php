<?php

namespace App\Services\Scraper;

use App\Models\Application;
use App\Models\ApplicationImage;
use App\Models\Category;
use App\Models\Setting;
use App\Services\AI\GeminiService;
use App\Services\Scraper\Traits\AppResolverTrait;
use App\Services\Storage\CloudflareR2Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TorrentmacImporter
{
    use AppResolverTrait;

    public function __construct(
        protected TorrentmacClient $client,
        protected TorrentmacParser $parser,
        protected GeminiService $geminiService,
        protected ?CloudflareR2Service $r2Service = null
    ) {
        $this->r2Service = $this->r2Service ?? app(CloudflareR2Service::class);
    }

    protected ?string $explicitDisk = null;

    /**
     * Set explicit storage disk for this import run (e.g. from CLI option)
     */
    public function setExplicitDisk(?string $disk): static
    {
        $this->explicitDisk = $disk;

        return $this;
    }

    /**
     * Resolve which storage disk to use for TorrentMac based on user setting ('local' vs 'r2')
     */
    public function getStorageDisk(): string
    {
        $target = $this->explicitDisk ?? Setting::get('torrentmac_storage_disk', 'local');

        if ($target === 'r2') {
            if ($this->r2Service->isConfigured()) {
                return 'r2';
            }

            Log::warning('TorrentmacImporter: Target disk is R2 but credentials are not configured in Admin/ENV. Falling back to public.');
        }

        return 'public';
    }

    /**
     * Get configured target ('local' or 'r2')
     */
    public function getConfiguredStorageTarget(): string
    {
        return $this->explicitDisk ?? Setting::get('torrentmac_storage_disk', 'local');
    }

    /**
     * Get programs from TorrentMac by category and page, annotated with local database status
     *
     * @return array{items: array<int, array>, pagination: array, category: string}
     */
    public function getTorrentmacApps(int $page = 1, bool $withPagination = false, string $category = 'all'): array
    {
        $html = $category === 'all'
            ? $this->client->getHomePageHtml($page)
            : $this->client->getCategoryHtmlBySlug($category, $page);

        if (! $html) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 1,
                    'has_next' => false,
                    'has_prev' => false,
                ],
                'category' => $category,
            ];
        }

        $cards = $this->parser->parseAppCards($html);
        $annotated = $this->annotateCards($cards);
        $pagination = $this->parser->parsePagination($html, $page);

        $result = [
            'items' => $annotated,
            'pagination' => $pagination,
            'category' => $category,
        ];

        if ($withPagination) {
            return $result;
        }

        return $annotated;
    }

    /**
     * Search apps in real-time on TorrentMac and annotate with database status
     *
     * @return array<int, array>|array{items: array<int, array>, pagination: array}
     */
    public function searchTorrentmac(string $query, int $page = 1, bool $withPagination = false): array
    {
        $html = $this->client->searchHtml($query, $page);
        if (! $html) {
            return $withPagination
                ? ['items' => [], 'pagination' => ['current_page' => $page, 'total_pages' => 1, 'has_next' => false, 'has_prev' => false]]
                : [];
        }

        $cards = $this->parser->parseAppCards($html);
        $annotated = $this->annotateCards($cards);

        if ($withPagination) {
            return [
                'items' => $annotated,
                'pagination' => $this->parser->parsePagination($html, $page),
            ];
        }

        return $annotated;
    }

    /**
     * Annotate parsed TorrentMac cards with local database status (already imported, dual download, etc.)
     *
     * @param  array<int, array>  $cards
     * @return array<int, array>
     */
    public function annotateCards(array $cards): array
    {
        if (empty($cards)) {
            return [];
        }

        $slugs = array_column($cards, 'slug');

        return array_map(function ($card) {
            $existing = $this->resolveExistingApplication($card);

            $card['in_database'] = (bool) $existing;
            $card['has_torrent'] = $existing ? (bool) $existing->has_torrent : false;
            $card['has_ddl'] = $existing ? ! empty($existing->download_url_external) : false;
            $card['is_dual'] = $existing ? ($existing->has_torrent && ! empty($existing->download_url_external)) : false;
            $card['local_app_id'] = $existing ? $existing->id : null;
            $card['local_version'] = $existing ? $existing->version : null;
            $card['has_update'] = $existing && ! empty($card['version']) && ! empty($existing->version) && ($card['version'] !== $existing->version);

            return $card;
        }, $cards);
    }

    /**
     * Import or update an application from its TorrentMac detail URL
     */
    public function importAppByUrl(string $url, bool $downloadImages = true): ?Application
    {
        $html = $this->client->fetchHtml($url, false);
        if (! $html) {
            Log::error("TorrentmacImporter: Could not fetch HTML for {$url}");

            return null;
        }

        $appData = $this->parser->parseAppDetail($html, $url);
        if (! $appData || empty($appData['name'])) {
            Log::warning("TorrentmacImporter: Failed to parse valid app data from {$url}");

            return null;
        }

        // 1. Resolve Application in Local DB via 4-tier deduplication engine
        $existing = $this->resolveExistingApplication($appData);

        // 2. Download and Store .torrent file in Cloudflare R2 / Public disk
        $torrentStorageData = $this->storeTorrentFile($appData['torrent_url'], $appData['slug'], $appData['version']);

        // 3. Resolve Category
        $category = $this->resolveCategory($appData['category']);

        // 4. Handle Icon (Reuse existing or download)
        $disk = $this->getStorageDisk();
        $iconLocalPath = null;
        if ($existing && ! empty($existing->icon) && Storage::disk($disk)->exists($existing->icon)) {
            $iconLocalPath = $existing->icon;
        } elseif ($downloadImages && ! empty($appData['icon_url'])) {
            $iconLocalPath = $this->downloadAndStoreIcon($appData['icon_url'], $appData['slug']);
        }

        // 5. Content Enrichment with Gemini AI (if needed)
        $description = $appData['description'];
        $features = null;

        $hasExistingEnrichedContent = $existing
            && ! empty($existing->description)
            && ! empty($existing->features)
            && ($existing->version === $appData['version'] || empty($appData['version']));

        if ($hasExistingEnrichedContent) {
            $description = $existing->description;
            $features = $existing->features;
        } elseif ($this->geminiService->isEnabled()) {
            try {
                $aiContent = $this->geminiService->generateAppContent(
                    name: $appData['clean_name'] ?: $appData['name'],
                    category: $category->name,
                    version: $appData['version'] ?? null,
                    shortDesc: null,
                    scrapedDesc: $appData['raw_description'] ?? null,
                    categorySlug: $category->slug
                );

                if (! empty($aiContent['description'])) {
                    $description = $aiContent['description'];
                }
                if (! empty($aiContent['features'])) {
                    $features = $aiContent['features'];
                }
            } catch (\Throwable $e) {
                Log::warning("TorrentmacImporter: Gemini AI enrichment failed for '{$appData['name']}': {$e->getMessage()}");
            }
        }

        // 6. Update or Create Application
        $application = $existing;

        $payload = [
            'category_id' => $category->id,
            'name' => $existing ? $existing->name : ($appData['clean_name'] ?: $appData['name']),
            'description' => $description,
            'features' => $features,
            'version' => $appData['version'] ?? ($existing ? $existing->version : '1.0'),
            'size' => $appData['size'] ?? ($existing ? $existing->size : 'Universal'),
            'platform' => $appData['platform'],
            'has_torrent' => true,
            'published' => true,
            'released_at' => now(),
        ];

        // Store torrent URL and internal storage path
        if (! empty($torrentStorageData['public_url'])) {
            $payload['torrent_url'] = $torrentStorageData['public_url'];
        } elseif (! empty($appData['torrent_url'])) {
            $payload['torrent_url'] = $appData['torrent_url'];
        }

        if (! empty($torrentStorageData['path'])) {
            $payload['torrent_file_path'] = $torrentStorageData['path'];
        }

        if (! empty($appData['magnet_link'])) {
            $payload['magnet_link'] = $appData['magnet_link'];
        }

        if ($iconLocalPath) {
            $payload['icon'] = $iconLocalPath;
        }

        if ($appData['rating'] && (! $existing || empty($existing->rating))) {
            $payload['rating'] = $appData['rating'];
            $payload['reviews_count'] = $appData['reviews_count'] ?? 15;
        }

        // Check if this is a new version upgrade
        $incomingVersion = $appData['version'] ?? null;
        $isVersionUpgrade = $existing && $incomingVersion && ! empty($existing->version) && ($incomingVersion !== $existing->version);

        if ($isVersionUpgrade) {
            // Archive all previous versions of this application
            $existing->versions()->update(['is_current' => false]);
            $payload['version'] = $incomingVersion;
            $payload['size'] = $appData['size'] ?? $existing->size;
        }

        if ($application) {
            $application->update($payload);
            if ($isVersionUpgrade) {
                $application->touch(); // Bump to top of Latest Releases
            }
        } else {
            $payload['slug'] = $appData['slug'];
            $payload['short_description'] = Str::limit(strip_tags($description), 160);
            $application = Application::create($payload);
        }

        // 7. Screenshots
        $needsScreenshots = $downloadImages
            && ! empty($appData['screenshots'])
            && (! $existing || $existing->images->isEmpty());

        if ($needsScreenshots) {
            $firstScreenshot = null;
            foreach (array_slice($appData['screenshots'], 0, 6) as $index => $screenshotUrl) {
                $screenPath = $this->downloadAndStoreImage($screenshotUrl, "{$application->slug}-torrent-{$index}");
                if ($screenPath) {
                    ApplicationImage::create([
                        'application_id' => $application->id,
                        'path' => $screenPath,
                        'alt' => "{$application->name} Captura ".($index + 1),
                        'sort_order' => $index,
                    ]);

                    if ($firstScreenshot === null) {
                        $firstScreenshot = $screenPath;
                    }
                }
            }

            if ($firstScreenshot && empty($application->screenshot)) {
                $application->update(['screenshot' => $firstScreenshot]);
            }
        }

        // 8. Application Version Record (Archived vs Current)
        if (! empty($appData['version'])) {
            if ($isVersionUpgrade) {
                $application->versions()->create([
                    'version' => $appData['version'],
                    'torrent_url' => $payload['torrent_url'] ?? null,
                    'torrent_file_path' => $payload['torrent_file_path'] ?? null,
                    'size' => $appData['size'] ?? $application->size,
                    'is_current' => true,
                    'released_at' => now(),
                ]);
            } else {
                $currentVer = $application->currentVersion;
                if ($currentVer) {
                    $currentVer->update([
                        'torrent_url' => $payload['torrent_url'] ?? $currentVer->torrent_url,
                        'torrent_file_path' => $payload['torrent_file_path'] ?? $currentVer->torrent_file_path,
                        'size' => $appData['size'] ?? $currentVer->size,
                    ]);
                } else {
                    $application->versions()->create([
                        'version' => $appData['version'],
                        'torrent_url' => $payload['torrent_url'] ?? null,
                        'torrent_file_path' => $payload['torrent_file_path'] ?? null,
                        'size' => $appData['size'] ?? $application->size,
                        'is_current' => true,
                        'released_at' => now(),
                    ]);
                }
            }
        }

        return $application;
    }

    /**
     * Download the .torrent file and upload it to Cloudflare R2 / Public storage
     *
     * @return array{path: ?string, public_url: ?string}
     */
    public function storeTorrentFile(?string $torrentUrl, string $slug, ?string $version = null): array
    {
        if (empty($torrentUrl)) {
            return ['path' => null, 'public_url' => null];
        }

        $binary = $this->client->downloadBinary($torrentUrl);
        if (! $binary) {
            Log::warning("TorrentmacImporter: Could not download torrent binary from {$torrentUrl}");

            return ['path' => null, 'public_url' => $torrentUrl];
        }

        $disk = $this->getStorageDisk();
        $safeVersion = $version ? '-'.Str::slug($version) : '';
        $relativePath = "torrents/{$slug}{$safeVersion}.torrent";

        if ($disk === 'r2') {
            $r2PublicUrl = $this->r2Service->putObject($relativePath, $binary, 'application/x-bittorrent');
            if ($r2PublicUrl) {
                return [
                    'path' => $relativePath,
                    'public_url' => $r2PublicUrl,
                ];
            }
            Log::warning("TorrentmacImporter: Cloudflare R2 upload failed, falling back to local public disk for {$relativePath}");
            $disk = 'public';
        }

        try {
            Storage::disk('public')->put($relativePath, $binary, 'public');

            $publicUrl = Storage::disk('public')->url($relativePath);

            return [
                'path' => $relativePath,
                'public_url' => $publicUrl,
            ];
        } catch (\Throwable $e) {
            Log::error("TorrentmacImporter: Failed to store torrent on disk 'public': {$e->getMessage()}");

            return ['path' => null, 'public_url' => $torrentUrl];
        }
    }

    /**
     * Download and store app icon
     */
    protected function downloadAndStoreIcon(string $url, string $slug): ?string
    {
        $binary = $this->client->downloadBinary($url);
        if (! $binary) {
            return null;
        }

        $disk = $this->getStorageDisk();
        $extension = 'png';
        if (preg_match('#\.(jpe?g|webp|png|gif)#i', $url, $m)) {
            $extension = strtolower($m[1]);
        }

        $path = "apps/{$slug}/icon.{$extension}";

        if ($disk === 'r2') {
            $r2Url = $this->r2Service->putObject($path, $binary, "image/{$extension}");
            if ($r2Url) {
                return $r2Url;
            }
        }

        try {
            Storage::disk('public')->put($path, $binary, 'public');

            return $path;
        } catch (\Throwable $e) {
            Log::warning("TorrentmacImporter: Failed to save icon to disk 'public': {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Download and store screenshot image
     */
    protected function downloadAndStoreImage(string $url, string $filename): ?string
    {
        $binary = $this->client->downloadBinary($url);
        if (! $binary) {
            return null;
        }

        $disk = $this->getStorageDisk();
        $extension = 'jpg';
        if (preg_match('#\.(jpe?g|webp|png)#i', $url, $m)) {
            $extension = strtolower($m[1]);
        }

        $path = "screenshots/{$filename}.{$extension}";

        if ($disk === 'r2') {
            $r2Url = $this->r2Service->putObject($path, $binary, "image/{$extension}");
            if ($r2Url) {
                return $r2Url;
            }
        }

        try {
            Storage::disk('public')->put($path, $binary, 'public');

            return $path;
        } catch (\Throwable $e) {
            Log::warning("TorrentmacImporter: Failed to save screenshot to disk 'public': {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Resolve category model by slug or fallback to system-utilities
     */
    protected function resolveCategory(string $slug): Category
    {
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $category;
        }

        $category = Category::where('slug', 'system-utilities')->first();
        if ($category) {
            return $category;
        }

        return Category::firstOrCreate(
            ['slug' => 'system-utilities'],
            [
                'name' => 'Utilidades del Sistema',
                'description' => 'Herramientas esenciales y utilidades avanzadas para macOS.',
                'icon' => 'cog',
                'color' => '#0071E3',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    /**
     * Sync latest programs from TorrentMac homepage
     *
     * @return array{total_checked: int, new_imported: int, updated: int, items: array}
     */
    public function syncLatestUpdates(int $pages = 1, bool $downloadImages = true): array
    {
        $totalChecked = 0;
        $newImported = 0;
        $updated = 0;
        $items = [];

        for ($p = 1; $p <= $pages; $p++) {
            $cards = $this->getTorrentmacApps($p, false, 'all');
            foreach ($cards as $card) {
                $totalChecked++;
                try {
                    $existing = $this->resolveExistingApplication($card);
                    $hasUpdate = $existing && ! empty($card['version']) && ! empty($existing->version) && ($card['version'] !== $existing->version);
                    $needsImport = ! $existing || ! $existing->has_torrent || $hasUpdate;

                    if (! $needsImport) {
                        continue;
                    }

                    $app = $this->importAppByUrl($card['url'], $downloadImages);

                    if ($app) {
                        if ($existing) {
                            $updated++;
                            $items[] = ['name' => $app->name, 'status' => 'updated', 'slug' => $app->slug];
                        } else {
                            $newImported++;
                            $items[] = ['name' => $app->name, 'status' => 'imported', 'slug' => $app->slug];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("TorrentmacImporter sync error on {$card['slug']}: {$e->getMessage()}");
                }
            }
        }

        Setting::set('torrentmac_last_sync', now()->toDateTimeString(), 'scraper');

        return [
            'total_checked' => $totalChecked,
            'new_imported' => $newImported,
            'updated' => $updated,
            'items' => $items,
        ];
    }
}
