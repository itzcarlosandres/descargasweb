<?php

namespace App\Services\Scraper;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class TorrentmacParser
{
    /**
     * Parse list of application cards from TorrentMac homepage or category page
     *
     * @return array<int, array{
     *     name: string,
     *     clean_name: string,
     *     slug: string,
     *     url: string,
     *     version: ?string,
     *     icon_url: ?string,
     *     category: ?string,
     *     date: ?string,
     *     source: string
     * }>
     */
    public function parseAppCards(string $html): array
    {
        $crawler = new Crawler($html);
        $apps = [];
        $seenUrls = [];

        $crawler->filter('article.post, article.hentry, .default-post')->each(function (Crawler $node) use (&$apps, &$seenUrls) {
            $linkNode = $node->filter('h2.entry-title a, header h2 a, a.home-thumb');
            if ($linkNode->count() === 0) {
                return;
            }

            $url = $linkNode->first()->attr('href');
            if (! $url || isset($seenUrls[$url])) {
                return;
            }

            $seenUrls[$url] = true;

            // Extract Title
            $rawTitle = '';
            if ($node->filter('h2.entry-title a, header h2 a')->count() > 0) {
                $rawTitle = trim($node->filter('h2.entry-title a, header h2 a')->first()->text());
            } elseif ($linkNode->first()->attr('title')) {
                $rawTitle = trim($linkNode->first()->attr('title'));
            }

            // Extract Version
            $version = $this->extractVersion($rawTitle);

            // Clean Name
            $cleanName = $this->cleanAppName($rawTitle, $version);

            // Extract Thumbnail / Icon
            $iconUrl = null;
            $imgNode = $node->filter('img.attachment-thumbnail, img.wp-post-image, .home-thumb img');
            if ($imgNode->count() > 0) {
                $iconUrl = $imgNode->attr('src');
                // Prefer high-res image if srcset exists
                if ($imgNode->attr('srcset')) {
                    $srcset = $imgNode->attr('srcset');
                    $parts = explode(',', $srcset);
                    $lastPart = trim(end($parts));
                    if (preg_match('#^(https?://\S+)#i', $lastPart, $m)) {
                        $iconUrl = $m[1];
                    }
                }
            }

            // Category from classes (e.g. category-apps, category-utilities-tools)
            $category = $this->extractCategoryFromClasses($node->attr('class') ?? '');

            // Date
            $date = null;
            $timeNode = $node->filter('time.entry-date, time.published');
            if ($timeNode->count() > 0) {
                $date = trim($timeNode->first()->text());
            }

            $slug = $this->extractSlugFromUrl($url);

            $apps[] = [
                'name' => $rawTitle,
                'clean_name' => $cleanName,
                'slug' => $slug,
                'url' => $url,
                'version' => $version,
                'icon_url' => $iconUrl,
                'category' => $category,
                'date' => $date,
                'source' => 'torrentmac',
            ];
        });

        return $apps;
    }

    /**
     * Parse full application detail page
     *
     * @return array{
     *     name: string,
     *     clean_name: string,
     *     slug: string,
     *     url: string,
     *     version: ?string,
     *     size: ?string,
     *     category: ?string,
     *     rating: ?float,
     *     reviews_count: ?int,
     *     icon_url: ?string,
     *     torrent_url: ?string,
     *     magnet_link: ?string,
     *     screenshots: array<int, string>,
     *     description: ?string,
     *     raw_description: ?string,
     *     platform: string,
     *     released_at: ?string
     * }|null
     */
    public function parseAppDetail(string $html, string $sourceUrl): ?array
    {
        $crawler = new Crawler($html);

        // 1. Extract JSON-LD Schema.org data if available
        $jsonLd = $this->extractJsonLd($html);

        // 2. Extract Raw Title
        $title = '';
        if ($crawler->filter('h1.entry-title, h1')->count() > 0) {
            $title = trim($crawler->filter('h1.entry-title, h1')->first()->text());
        } elseif (! empty($jsonLd['name'])) {
            $title = $jsonLd['name'];
        }

        if (empty($title)) {
            return null;
        }

        // 3. Extract Version
        $version = $jsonLd['softwareVersion'] ?? $this->extractVersion($title);

        // 4. Extract Size
        $size = $jsonLd['fileSize'] ?? null;
        if (! $size) {
            if (preg_match('#(?:File Size|Size)[:\s]+([0-9.]+\s*(?:MB|GB|KB|M|G))#i', $html, $sm)) {
                $size = trim($sm[1]);
            }
        }

        // Clean size formatting (e.g. 12M -> 12 MB)
        if ($size && preg_match('#^([0-9.]+)\s*([M|G|K])B?$#i', $size, $szm)) {
            $size = $szm[1].' '.strtoupper($szm[2]).'B';
        }

        // 5. Extract Category
        $rawCategory = $jsonLd['applicationCategory'] ?? null;
        if (! $rawCategory && $crawler->filter('article')->count() > 0) {
            $rawCategory = $this->extractCategoryFromClasses($crawler->filter('article')->first()->attr('class') ?? '');
        }

        $category = $this->mapCategoryToSlug($rawCategory);

        // 6. Extract Direct Torrent File URL
        $torrentUrl = null;
        if (preg_match('#<a[^>]+href=["\']([^"\']+\.torrent)["\'][^>]*>#i', $html, $tm)) {
            $torrentUrl = html_entity_decode($tm[1]);
        } elseif ($crawler->filter('a.download-btn, a.dl-button-large')->count() > 0) {
            $dlHref = $crawler->filter('a.download-btn, a.dl-button-large')->first()->attr('href');
            if (str_contains($dlHref, '.torrent')) {
                $torrentUrl = html_entity_decode($dlHref);
            }
        }

        // 7. Extract Magnet Link
        $magnetLink = null;
        if (preg_match('#["\'](magnet:\?[^"\']+)["\']#i', $html, $mm)) {
            $magnetLink = html_entity_decode($mm[1]);
        }

        // 8. Rating and Reviews
        $rating = null;
        $reviewsCount = null;
        if (! empty($jsonLd['aggregateRating'])) {
            $rating = isset($jsonLd['aggregateRating']['ratingValue']) ? (float) $jsonLd['aggregateRating']['ratingValue'] : null;
            $reviewsCount = isset($jsonLd['aggregateRating']['ratingCount']) ? (int) $jsonLd['aggregateRating']['ratingCount'] : null;
        }

        // 9. Icon / Featured Image
        $iconUrl = null;
        if ($crawler->filter('img.wp-post-image, .home-thumb img, .entry-content img')->count() > 0) {
            $iconCandidate = $crawler->filter('img.wp-post-image, .home-thumb img')->first();
            if ($iconCandidate->count() > 0) {
                $iconUrl = $iconCandidate->attr('src');
            }
        }
        if (! $iconUrl && ! empty($jsonLd['image'])) {
            $iconUrl = is_array($jsonLd['image']) ? ($jsonLd['image'][0] ?? null) : $jsonLd['image'];
        }

        // 10. Extract Screenshots
        $screenshots = $this->extractScreenshots($html);

        // 11. Extract Description / Content
        $rawDescription = '';
        if ($crawler->filter('.entry-content, section.entry-content')->count() > 0) {
            $contentNode = $crawler->filter('.entry-content, section.entry-content')->first();
            $rawDescription = $this->cleanHtmlContent($contentNode->html());
        }

        $slug = $this->extractSlugFromUrl($sourceUrl);
        $cleanName = $this->cleanAppName($title, $version);

        return [
            'name' => $title,
            'clean_name' => $cleanName,
            'slug' => $slug,
            'url' => $sourceUrl,
            'version' => $version,
            'size' => $size ?? 'Universal',
            'category' => $category,
            'rating' => $rating,
            'reviews_count' => $reviewsCount,
            'icon_url' => $iconUrl,
            'torrent_url' => $torrentUrl,
            'magnet_link' => $magnetLink,
            'screenshots' => $screenshots,
            'description' => $rawDescription,
            'raw_description' => strip_tags($rawDescription),
            'platform' => 'macOS 12.0 or later (Apple Silicon & Intel)',
            'released_at' => $jsonLd['datePublished'] ?? now()->toIso8601String(),
        ];
    }

    /**
     * Extract JSON-LD block with SoftwareApplication
     */
    protected function extractJsonLd(string $html): ?array
    {
        if (! preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $block) {
            $data = json_decode(trim($block), true);
            if (! is_array($data)) {
                continue;
            }

            if (isset($data['@type']) && $data['@type'] === 'SoftwareApplication') {
                return $data;
            }

            if (isset($data['@graph']) && is_array($data['@graph'])) {
                foreach ($data['@graph'] as $node) {
                    if (isset($node['@type']) && $node['@type'] === 'SoftwareApplication') {
                        return $node;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract clean screenshots from article content
     *
     * @return array<int, string>
     */
    protected function extractScreenshots(string $html): array
    {
        $screenshots = [];
        if (! preg_match_all('#<img[^>]+src=["\']([^"\']+)["\'][^>]*>#i', $html, $imgs)) {
            return [];
        }

        foreach ($imgs[1] as $img) {
            // Must belong to wp-content/uploads/
            if (! str_contains($img, '/wp-content/uploads/')) {
                continue;
            }

            // Exclude logos, icons, avatars, tiny thumbnails
            if (preg_match('#(logo|avatar|icon|banner|ads|-(?:100x100|150x150|120x|64x64)\.)#i', $img)) {
                continue;
            }

            // Clean up 300x dimensions to get the full-res version if applicable
            $cleanUrl = preg_replace('#-[0-9]+x[0-9]+(\.[a-z0-9]+)$#i', '$1', $img);
            $screenshots[] = $cleanUrl;
        }

        return array_values(array_unique(array_slice($screenshots, 0, 8)));
    }

    /**
     * Clean and strip ads / unnecessary wrappers from content HTML
     */
    protected function cleanHtmlContent(string $html): string
    {
        // Remove scripts, styles, iframes, ads
        $cleaned = preg_replace('#<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>#is', '', $html);
        $cleaned = preg_replace('#<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>#is', '', $cleaned);
        $cleaned = preg_replace('#<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>#is', '', $cleaned);
        $cleaned = preg_replace('#<div class=["\'](?:torrent_info|dl-buttons|ad-|banner)[^"\']*["\'].*?<\/div>#is', '', $cleaned);

        return trim($cleaned);
    }

    /**
     * Parse pagination data from page HTML
     *
     * @return array{current_page: int, total_pages: int, has_next: bool, has_prev: bool}
     */
    public function parsePagination(string $html, int $currentPage = 1): array
    {
        $totalPages = $currentPage;

        if (preg_match_all('#page/(\d+)/#i', $html, $matches)) {
            $pages = array_map('intval', $matches[1]);
            if (! empty($pages)) {
                $totalPages = max(max($pages), $currentPage);
            }
        }

        $hasNext = str_contains($html, 'class="next page-numbers"') || str_contains($html, 'rel="next"') || ($totalPages > $currentPage);
        $hasPrev = $currentPage > 1;

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev,
        ];
    }

    /**
     * Extract software version from title string
     */
    public function extractVersion(string $title): ?string
    {
        // Match patterns like 2.104, 2026.1, 14.5.1, v3.2.0, 4.0 b2
        if (preg_match('/\b(?:v|ver\.?|version)?\s*(\d+(\.\d+)+(?:\s*(?:b|beta|rc|build|u)\d+)?)\b/i', $title, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/\b(20\d{2}(?:\.\d+)?)\b/', $title, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Clean application name by removing version, architecture tags, and TorrentMac markers
     */
    public function cleanAppName(string $title, ?string $version = null): string
    {
        $name = $title;

        // Remove [TNT], [HCiSO], [U2B], etc.
        $name = preg_replace('/\[[^\]]*\]/', '', $name);
        $name = preg_replace('/\{[^\}]*\}/', '', $name);
        $name = preg_replace('/\([^)]*(?:dmg|torrent|tnt|crack|mac|apple silicon)[^)]*\)/i', '', $name);

        // Remove "for Mac", "macOS", "Universal"
        $name = preg_replace('/\b(?:for\s+Mac|macOS|Mac|Universal|Multilingual)\b/i', '', $name);

        // Remove version if known
        if ($version) {
            $name = str_ireplace($version, '', $name);
        }

        // Clean extra punctuation and double spaces
        $name = preg_replace('/[–—\-_:|]+$/', '', trim($name));
        $name = preg_replace('/^[–—\-_:|]+/', '', trim($name));
        $name = preg_replace('/\s+/', ' ', trim($name));

        return $name ?: $title;
    }

    /**
     * Extract slug from a TorrentMac URL
     */
    public function extractSlugFromUrl(string $url): string
    {
        $path = trim(parse_url($url, PHP_URL_PATH), '/');
        $segments = explode('/', $path);
        $slug = end($segments);

        // Remove .torrent extension if present
        $slug = preg_replace('/\.torrent$/i', '', $slug);

        return Str::slug($slug);
    }

    /**
     * Extract category name or slug from CSS classes
     */
    protected function extractCategoryFromClasses(string $classAttr): ?string
    {
        if (preg_match_all('/category-([a-z0-9-]+)/i', $classAttr, $matches)) {
            foreach ($matches[1] as $c) {
                if (! in_array($c, ['apps', 'post', 'uncategorized'])) {
                    return $c;
                }
            }
        }

        return 'apps';
    }

    /**
     * Map TorrentMac category to DescargasWeb category slug
     */
    public function mapCategoryToSlug(?string $torrentCategory): string
    {
        if (! $torrentCategory) {
            return 'system-utilities';
        }

        $c = strtolower(trim($torrentCategory));

        $map = [
            'games' => 'mac-games',
            'mac-games' => 'mac-games',
            'gameapplication' => 'mac-games',
            'utilities-tools' => 'system-utilities',
            'utilitiesapplication' => 'system-utilities',
            'productivity' => 'productivity-business',
            'businessapplication' => 'productivity-business',
            'audio-editors' => 'media-design',
            'video' => 'media-design',
            'multimediaapplication' => 'media-design',
            'graphics-design' => 'media-design',
            'designapplication' => 'media-design',
            'photography' => 'media-design',
            'developer-tools' => 'developer-tools',
            'developerapplication' => 'developer-tools',
            'plugins' => 'system-utilities',
        ];

        return $map[$c] ?? 'system-utilities';
    }
}
