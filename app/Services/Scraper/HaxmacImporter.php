<?php

namespace App\Services\Scraper;

use App\Models\Application;
use App\Models\ApplicationImage;
use App\Models\ApplicationVersion;
use App\Models\Category;
use App\Models\Setting;
use App\Services\AI\GeminiService;
use App\Services\Scraper\Traits\AppResolverTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HaxmacImporter
{
    use AppResolverTrait;

    protected GeminiService $geminiService;

    public function __construct(
        protected HaxmacClient $client,
        protected HaxmacParser $parser,
        ?GeminiService $geminiService = null
    ) {
        $this->geminiService = $geminiService ?? app(GeminiService::class);
    }

    /**
     * Synchronize all categories from HaxMac main menu
     *
     * @return array{created: int, updated: int, categories: array}
     */
    public function syncCategories(): array
    {
        $html = $this->client->getHomePageHtml();
        if (! $html) {
            return ['created' => 0, 'updated' => 0, 'categories' => []];
        }

        $parsedCategories = $this->parser->parseMenuCategories($html);
        $created = 0;
        $updated = 0;
        $synced = [];

        foreach ($parsedCategories as $index => $catData) {
            $slug = $catData['slug'];
            $name = $catData['name'];

            $category = Category::where('slug', $slug)->first();

            if ($category) {
                $category->update([
                    'name' => $name,
                    'is_active' => true,
                ]);
                $updated++;
            } else {
                $category = Category::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => "Aplicaciones y descargas de {$name} para macOS",
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]);
                $created++;
            }

            $synced[] = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => $catData['url'],
            ];
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'categories' => $synced,
        ];
    }

    /**
     * Get programs from HaxMac by category and page, annotated with local database status
     *
     * @return array{items: array<int, array>, pagination: array, category: string}
     */
    public function getHaxmacAppsByCategory(string $categorySlug = 'all', int $page = 1): array
    {
        $html = $categorySlug === 'all'
            ? $this->client->getHomePageHtml($page)
            : $this->client->getCategoryHtmlBySlug($categorySlug, $page);

        if (! $html) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 1,
                    'has_next' => false,
                    'has_prev' => false,
                ],
                'category' => $categorySlug,
            ];
        }

        $cards = $this->parser->parseAppCards($html);
        $annotated = $this->annotateCards($cards);
        $pagination = $this->parser->parsePagination($html, $page);

        return [
            'items' => $annotated,
            'pagination' => $pagination,
            'category' => $categorySlug,
        ];
    }

    /**
     * Get the latest programs from HaxMac homepage or category annotated with database status
     *
     * @return array<int, array>|array{items: array<int, array>, pagination: array, category: string}
     */
    public function getLatestHaxmacApps(int $page = 1, bool $withPagination = false, string $category = 'all'): array
    {
        $result = $this->getHaxmacAppsByCategory($category, $page);

        if ($withPagination) {
            return $result;
        }

        return $result['items'];
    }

    /**
     * Synchronize latest programs from HaxMac homepage (imports new apps and updates existing ones)
     *
     * @return array{total_checked: int, new_imported: int, updated: int, items: array}
     */
    /**
     * Sync latest programs from HaxMac homepage.
     * If page 1 has no new apps, it automatically scans subsequent older pages to bring backlog apps.
     *
     * @return array{total_checked: int, new_imported: int, updated: int, items: array}
     */
    public function syncLatestUpdates(bool $downloadImages = true, int $limit = 0, int $maxPages = 3): array
    {
        $newImported = 0;
        $updated = 0;
        $totalChecked = 0;
        $processedItems = [];

        for ($p = 1; $p <= $maxPages; $p++) {
            $latest = $this->getLatestHaxmacApps($p, false);
            if (empty($latest)) {
                break;
            }

            foreach ($latest as $card) {
                if ($limit > 0 && ($newImported + $updated) >= $limit) {
                    break 2;
                }

                $totalChecked++;

                // Only process if it is NOT yet imported, OR if it has a newer version available
                if (! $card['is_imported'] || $card['has_update']) {
                    try {
                        $url = $card['url'] ?? $card['slug'];
                        $wasExisting = $card['is_imported'];
                        $app = $this->importApp($url, $downloadImages);

                        if ($app) {
                            if ($wasExisting) {
                                $updated++;
                            } else {
                                $newImported++;
                            }

                            $processedItems[] = [
                                'name' => $app->name,
                                'version' => $app->version,
                                'type' => $wasExisting ? 'updated' : 'new',
                            ];
                        }

                        // Respectful pause to avoid server rate-limiting (250ms)
                        usleep(250000);
                    } catch (\Throwable $e) {
                        Log::error("HaxmacImporter sync error on {$card['slug']}: {$e->getMessage()}");
                    } finally {
                        gc_collect_cycles();
                    }
                }
            }

            // If we found any new apps on this page or satisfied the limit, no need to keep digging
            if (($limit > 0 && ($newImported + $updated) >= $limit) || ($limit === 0 && ($newImported + $updated) > 0)) {
                break;
            }
        }

        return [
            'total_checked' => $totalChecked,
            'new_imported' => $newImported,
            'updated' => $updated,
            'items' => $processedItems,
        ];
    }

    /**
     * Scan recent HaxMac pages and return only applications that exist locally with a newer version available
     *
     * @return array<int, array>
     */
    public function getPendingUpdates(int $maxPages = 2): array
    {
        $pending = [];
        $seenSlugs = [];

        for ($p = 1; $p <= $maxPages; $p++) {
            $data = $this->getHaxmacAppsByCategory('all', $p);
            $cards = $data['items'] ?? [];

            foreach ($cards as $card) {
                if (! empty($card['has_update']) && ! in_array($card['slug'], $seenSlugs)) {
                    $seenSlugs[] = $card['slug'];
                    $pending[] = $card;
                }
            }
        }

        return $pending;
    }

    /**
     * Search apps in real-time on HaxMac and annotate with database status
     *
     * @return array<int, array>|array{items: array<int, array>, pagination: array}
     */
    public function searchHaxmac(string $query, int $page = 1, bool $withPagination = false): array
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
     * Annotate parsed cards with local database status (imported, version, has_update)
     *
     * @param  array<int, array>  $cards
     * @return array<int, array>
     */
    protected function annotateCards(array $cards): array
    {
        if (empty($cards)) {
            return [];
        }

        $results = [];

        // Optimize: Bulk query existing applications to eliminate N+1 overhead
        $slugs = array_values(array_filter(array_column($cards, 'slug')));
        $names = array_values(array_filter(array_column($cards, 'name')));

        $existingBySlug = Application::whereIn('slug', $slugs)->get(['id', 'slug', 'name', 'version'])->keyBy('slug');
        $existingByName = Application::whereIn('name', $names)->get(['id', 'slug', 'name', 'version'])->keyBy('name');

        foreach ($cards as $card) {
            $existing = $existingBySlug->get($card['slug']) ?? $existingByName->get($card['name'] ?? '');
            $hasUpdate = false;

            if ($existing && $card['version'] && $existing->version) {
                // If versions differ
                $hasUpdate = version_compare($card['version'], $existing->version, '>');
            }

            $results[] = array_merge($card, [
                'is_imported' => (bool) $existing,
                'local_id' => $existing?->id,
                'local_version' => $existing?->version,
                'has_update' => $hasUpdate,
            ]);
        }

        return $results;
    }

    /**
     * Import or update a single application by its HaxMac page URL or slug
     */
    public function importApp(string $urlOrSlug, bool $downloadImages = true): ?Application
    {
        $url = str_starts_with($urlOrSlug, 'http')
            ? $urlOrSlug
            : rtrim($this->client->getBaseUrl(), '/').'/'.ltrim($urlOrSlug, '/').'/';

        $detailHtml = $this->client->fetchHtml($url);
        if (! $detailHtml) {
            Log::error("HaxmacImporter: Could not fetch detail HTML for {$url}");

            return null;
        }

        $appData = $this->parser->parseAppDetail($detailHtml, $url);
        if (empty($appData['name']) || empty($appData['slug'])) {
            Log::warning("HaxmacImporter: Failed to parse valid app data from {$url}");

            return null;
        }

        // Check if application already exists in local database via 4-tier deduplication engine
        $existing = $this->resolveExistingApplication($appData);

        // 1. Fetch clean download mirrors if download page exists
        $cleanMirrors = [];
        $olderVersions = [];

        $downloadUrl = $appData['download_page_url'] ?? rtrim($this->client->getBaseUrl(), '/').'/download/'.$appData['slug'].'/';
        $downloadHtml = $this->client->fetchHtml($downloadUrl);

        if ($downloadHtml) {
            $mirrorData = $this->parser->parseDownloadMirrors($downloadHtml);
            $cleanMirrors = $mirrorData['mirrors'];
            $olderVersions = $mirrorData['older_versions'];
        }

        // Pick primary clean download URL
        $primaryDownloadUrl = ! empty($cleanMirrors) ? $cleanMirrors[0]['url'] : null;

        // 2. Resolve Category
        $category = $this->resolveCategory($appData['category_name']);

        // 3. Handle Icon Download (Reuse existing local icon if present on disk to save bandwidth and disk space)
        $iconLocalPath = null;
        if ($existing && ! empty($existing->icon) && Storage::disk('public')->exists($existing->icon)) {
            $iconLocalPath = $existing->icon;
        } elseif ($downloadImages && ! empty($appData['icon_url'])) {
            $iconLocalPath = $this->downloadAndStoreIcon($appData['icon_url'], $appData['slug']);
        }

        // 4. Content Enrichment with Gemini AI (if enabled)
        // Optimize: Skip costly Gemini AI calls if existing app is already up-to-date and has rich description & features
        $description = $appData['description'];
        $features = $appData['features'] ?? null;

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
                    name: $appData['name'],
                    category: $category->name ?? $appData['category_name'],
                    version: $appData['version'] ?? null,
                    shortDesc: $appData['short_description'] ?? null,
                    scrapedDesc: $appData['description'] ?? null,
                    categorySlug: $category->slug ?? null
                );

                if (! empty($aiContent['description'])) {
                    $description = $aiContent['description'];
                }
                if (! empty($aiContent['features'])) {
                    $features = $aiContent['features'];
                }
            } catch (\Throwable $e) {
                Log::warning("HaxmacImporter: Gemini AI enrichment failed for '{$appData['name']}', keeping original content: {$e->getMessage()}");
            }
        }

        // 5. Update or Create Application
        $application = $existing ?? Application::where('slug', $appData['slug'])->first();

        $payload = [
            'category_id' => $category->id,
            'name' => $appData['name'],
            'short_description' => $appData['short_description'],
            'description' => $description,
            'changelog' => $appData['whats_new'] ?? null,
            'features' => $features,
            'version' => $appData['version'],
            'size' => $appData['size'],
            'platform' => $appData['platform'] ?? 'Universal',
            'download_url_external' => $primaryDownloadUrl,
            'published' => $existing ? (bool) $existing->published : (! (bool) Setting::get('scraper_drip_feed_mode', false)),
            'released_at' => now(),
        ];

        if ($iconLocalPath) {
            $payload['icon'] = $iconLocalPath;
        }

        if ($appData['rating']) {
            $payload['rating'] = $appData['rating'];
        }

        $isVersionUpgrade = $existing && ! empty($appData['version']) && ! empty($existing->version) && ($appData['version'] !== $existing->version);

        if ($application) {
            $application->update($payload);
            if ($isVersionUpgrade) {
                $application->touch(); // Bump to top of Latest Releases
            }
        } else {
            $payload['slug'] = $appData['slug'];
            $application = Application::create($payload);
        }

        // 4b. Download and Store Screenshots
        // Optimize: Only re-download screenshots if needed (new app, version updated, or screenshots missing)
        $needsScreenshots = $downloadImages
            && ! empty($appData['screenshots'])
            && (! $existing || $existing->version !== $appData['version'] || $existing->images->isEmpty());

        if ($needsScreenshots) {
            // Delete old physical screenshot files from storage to prevent disk bloating
            if ($existing && $existing->images->isNotEmpty()) {
                foreach ($existing->images as $oldImg) {
                    if (! empty($oldImg->path) && Storage::disk('public')->exists($oldImg->path)) {
                        Storage::disk('public')->delete($oldImg->path);
                    }
                }
            }

            $application->images()->delete();
            $firstScreenshot = null;

            foreach (array_slice($appData['screenshots'], 0, 8) as $index => $screenshotUrl) {
                $screenPath = $this->downloadAndStoreImage($screenshotUrl, "{$application->slug}-screen-{$index}");
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

            if ($firstScreenshot) {
                $application->update(['screenshot' => $firstScreenshot]);
            }
        }

        // 5. Store Application Versions
        if ($appData['version'] && $primaryDownloadUrl) {
            $application->versions()->where('version', '!=', $appData['version'])->update(['is_current' => false]);

            ApplicationVersion::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'version' => $appData['version'],
                ],
                [
                    'download_url' => $primaryDownloadUrl,
                    'size' => $appData['size'],
                    'changelog' => $appData['whats_new'] ?? null,
                    'is_current' => true,
                    'released_at' => now(),
                ]
            );
        }

        // Store older versions if present
        foreach ($olderVersions as $oldVer) {
            $oldUrl = ! empty($oldVer['mirrors']) ? $oldVer['mirrors'][0]['url'] : null;
            if ($oldUrl) {
                ApplicationVersion::updateOrCreate(
                    [
                        'application_id' => $application->id,
                        'version' => $oldVer['version'],
                    ],
                    [
                        'download_url' => $oldUrl,
                        'changelog' => $oldVer['meta'] ?? null,
                        'is_current' => false,
                    ]
                );
            }
        }

        return $application->fresh();
    }

    /**
     * Import apps from a specific category URL across multiple pages
     *
     * @return array{imported: int, updated: int, failed: int, items: array}
     */
    public function importCategory(string $categorySlugOrUrl, int $maxPages = 1, bool $downloadImages = true): array
    {
        $imported = 0;
        $updated = 0;
        $failed = 0;
        $items = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            $pageHtml = $this->client->getCategoryHtml($categorySlugOrUrl, $page);
            if (! $pageHtml) {
                break;
            }

            $appCards = $this->parser->parseAppCards($pageHtml);
            if (empty($appCards)) {
                break;
            }

            foreach ($appCards as $card) {
                try {
                    $wasExisting = Application::where('slug', $card['slug'])->exists();
                    $app = $this->importApp($card['url'], $downloadImages);

                    if ($app) {
                        if ($wasExisting) {
                            $updated++;
                        } else {
                            $imported++;
                        }
                        $items[] = [
                            'name' => $app->name,
                            'version' => $app->version,
                            'status' => $wasExisting ? 'updated' : 'created',
                        ];
                    } else {
                        $failed++;
                    }

                    // Respectful pause between items (250ms) to avoid rate limits
                    usleep(250000);
                } catch (\Throwable $e) {
                    Log::error("HaxmacImporter category error on {$card['slug']}: {$e->getMessage()}");
                    $failed++;
                } finally {
                    gc_collect_cycles();
                }
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'failed' => $failed,
            'items' => $items,
        ];
    }

    /**
     * Resolve or create a Category model by name
     */
    protected function resolveCategory(?string $name): Category
    {
        if (empty($name)) {
            $default = Category::first();
            if ($default) {
                return $default;
            }

            return Category::create([
                'name' => 'Utilidades',
                'slug' => 'utilidades',
                'is_active' => true,
            ]);
        }

        $slug = Str::slug($name);

        return Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => "Aplicaciones de {$name}",
                'is_active' => true,
            ]
        );
    }

    /**
     * Download icon image and store in storage/app/public/apps
     */
    protected function downloadAndStoreIcon(string $imageUrl, string $slug): ?string
    {
        return $this->downloadAndStoreImage($imageUrl, $slug);
    }

    /**
     * Download any image (icon or screenshot) and store in public storage
     */
    protected function downloadAndStoreImage(string $imageUrl, string $filenamePrefix): ?string
    {
        $binary = $this->client->downloadBinary($imageUrl);
        if (! $binary) {
            return null;
        }

        // Determine extension from url path
        $cleanPath = parse_url($imageUrl, PHP_URL_PATH) ?? '';
        $extension = 'png';
        if (str_ends_with(strtolower($cleanPath), '.webp')) {
            $extension = 'webp';
        } elseif (str_ends_with(strtolower($cleanPath), '.avif')) {
            $extension = 'avif';
        } elseif (str_ends_with(strtolower($cleanPath), '.jpg') || str_ends_with(strtolower($cleanPath), '.jpeg')) {
            $extension = 'jpg';
        }

        $filename = "apps/{$filenamePrefix}-".time().".{$extension}";
        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }
}
