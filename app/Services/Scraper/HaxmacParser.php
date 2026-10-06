<?php

namespace App\Services\Scraper;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class HaxmacParser
{
    /**
     * Allowed and trusted download host keywords
     */
    protected array $trustedHostKeywords = [
        'theuser.cloud',
        'usersdrive.com',
        'send.cm',
        'send.now',
        'mega.nz',
        'drive.google.com',
        'mediafire.com',
        'pixeldrain.com',
        '1fichier.com',
        'rapidgator.net',
        'dropbox.com',
        'qiwi.gg',
        'krakenfiles.com',
        'gofile.io',
    ];

    /**
     * Known adware / tracking domains to discard immediately
     */
    protected array $blockedDomainKeywords = [
        '.cfd',
        'ad.',
        'ads.',
        'click',
        'track',
        'sponsor',
        'traffic',
        'redirect',
        'interstitial',
    ];

    /**
     * Parse categories from main menu
     *
     * @return array<int, array{name: string, slug: string, url: string, parent: ?string}>
     */
    public function parseMenuCategories(string $html): array
    {
        $crawler = new Crawler($html);
        $categories = [];
        $seenSlugs = [];

        // Traverse main-nav items
        $crawler->filter('nav.main-nav ul#menu-main-menu > li')->each(function (Crawler $li) use (&$categories, &$seenSlugs) {
            $mainLink = $li->children('a');
            if ($mainLink->count() === 0) {
                return;
            }

            $mainName = trim($mainLink->text());
            $mainUrl = $mainLink->attr('href');

            // Skip informational/guide pages in menu
            if (in_array(strtolower($mainName), ['disable sip', 'fix damaged apps', 'how to disable system integrity protection in macos'])) {
                return;
            }

            $mainSlug = $this->extractSlugFromUrl($mainUrl);
            if ($mainSlug && ! isset($seenSlugs[$mainSlug])) {
                $seenSlugs[$mainSlug] = true;
                $categories[] = [
                    'name' => $mainName,
                    'slug' => $mainSlug,
                    'url' => $mainUrl,
                    'parent' => null,
                ];
            }

            // Submenu categories
            $li->filter('ul.sub-menu > li')->each(function (Crawler $subLi) use (&$categories, &$seenSlugs, $mainSlug) {
                $subLink = $subLi->children('a');
                if ($subLink->count() === 0) {
                    return;
                }

                $subName = trim($subLink->text());
                $subUrl = $subLink->attr('href');
                $subSlug = $this->extractSlugFromUrl($subUrl);

                if ($subSlug && ! isset($seenSlugs[$subSlug])) {
                    $seenSlugs[$subSlug] = true;
                    $categories[] = [
                        'name' => $subName,
                        'slug' => $subSlug,
                        'url' => $subUrl,
                        'parent' => $mainSlug,
                    ];
                }
            });
        });

        return $categories;
    }

    /**
     * Parse app cards from listing or search results
     *
     * @return array<int, array{name: string, slug: string, url: string, version: ?string, icon_url: ?string, category: ?string, downloads: ?string, rating: ?string}>
     */
    public function parseAppCards(string $html): array
    {
        $crawler = new Crawler($html);
        $apps = [];
        $seenUrls = [];

        // Check both .post-card, .feat-card, or articles
        $crawler->filter('.post-card, .feat-card, article.hentry')->each(function (Crawler $node) use (&$apps, &$seenUrls) {
            // Extract URL
            $url = null;
            if ($node->nodeName() === 'a' && $node->attr('href')) {
                $url = $node->attr('href');
            } elseif ($node->filter('.entry-title a, h2 a, a')->count() > 0) {
                $url = $node->filter('.entry-title a, h2 a, a')->first()->attr('href');
            }

            if (! $url || isset($seenUrls[$url])) {
                return;
            }

            // Skip download routes directly in listing
            if (str_contains($url, '/download/')) {
                return;
            }

            $seenUrls[$url] = true;

            // Extract Name cleanly
            $name = '';
            if ($node->filter('.feat-name')->count() > 0) {
                $name = trim($node->filter('.feat-name')->first()->text());
            } elseif ($node->filter('.entry-title a')->count() > 0) {
                $name = trim($node->filter('.entry-title a')->first()->text());
            } elseif ($node->filter('h2 a')->count() > 0) {
                $name = trim($node->filter('h2 a')->first()->text());
            } elseif ($node->filter('.entry-title, h2')->count() > 0) {
                $name = trim($node->filter('.entry-title, h2')->first()->text());
            } else {
                $name = trim($node->text());
            }

            // Clean version if included in name or meta
            $version = null;
            if ($node->filter('.meta-ver, .sah-ver')->count() > 0) {
                $version = trim($node->filter('.meta-ver, .sah-ver')->first()->text());
            } elseif (preg_match('/\b(v?\d+(\.\d+)+[a-z0-9_-]*)\b/i', $name, $matches)) {
                $version = $matches[1];
            }

            // Icon image
            $iconUrl = null;
            $imgNode = $node->filter('img.card-icon, img.feat-icon, img.wp-post-image, img');
            if ($imgNode->count() > 0) {
                $iconUrl = $imgNode->attr('src');
                if (! $iconUrl || str_starts_with($iconUrl, 'data:')) {
                    $iconUrl = $imgNode->attr('data-src') ?? $imgNode->attr('data-lazy-src');
                }
            }

            // Category
            $category = null;
            if ($node->filter('.cat-pill, .feat-cat, .sah-cat')->count() > 0) {
                $category = trim($node->filter('.cat-pill, .feat-cat, .sah-cat')->first()->text());
            }

            // Downloads
            $downloads = null;
            if ($node->filter('.card-dl, .feat-dl, .sah-dls')->count() > 0) {
                $downloads = trim($node->filter('.card-dl, .feat-dl, .sah-dls')->text());
            }

            // Rating
            $rating = null;
            if ($node->filter('.card-rating strong')->count() > 0) {
                $rating = trim($node->filter('.card-rating strong')->text());
            }

            $slug = $this->extractSlugFromUrl($url);

            $apps[] = [
                'name' => $this->cleanAppName($name, $version),
                'slug' => $slug,
                'url' => $url,
                'version' => $version,
                'icon_url' => $iconUrl,
                'category' => $category,
                'downloads' => $downloads,
                'rating' => $rating,
            ];
        });

        return $apps;
    }

    /**
     * Parse full app detail page
     */
    public function parseAppDetail(string $html, string $pageUrl): array
    {
        $crawler = new Crawler($html);

        // App Name
        $name = '';
        if ($crawler->filter('.single-app-header .entry-title')->count() > 0) {
            $nameNode = $crawler->filter('.single-app-header .entry-title');
            // Remove .sah-ver if inside entry-title
            $rawHtml = $nameNode->html();
            $rawHtml = preg_replace('/<span class="sah-ver"[^>]*>.*?<\/span>/i', '', $rawHtml);
            $name = trim(strip_tags($rawHtml));
        } elseif ($crawler->filter('h1.entry-title')->count() > 0) {
            $name = trim($crawler->filter('h1.entry-title')->text());
        }

        // Version
        $version = null;
        if ($crawler->filter('.sah-ver')->count() > 0) {
            $version = trim($crawler->filter('.sah-ver')->text());
        } elseif ($crawler->filter('.sah-cta-s')->count() > 0) {
            $ctaSub = $crawler->filter('.sah-cta-s')->text();
            if (preg_match('/([0-9]+(?:\.[0-9]+)+[a-z0-9_-]*)/i', $ctaSub, $matches)) {
                $version = $matches[1];
            }
        }

        // Icon
        $iconUrl = null;
        if ($crawler->filter('.sah-icon img')->count() > 0) {
            $iconUrl = $crawler->filter('.sah-icon img')->attr('src');
        } elseif ($crawler->filter('img.wp-post-image')->count() > 0) {
            $iconUrl = $crawler->filter('img.wp-post-image')->first()->attr('src');
        }

        // Category
        $categoryName = null;
        if ($crawler->filter('.sah-cat')->count() > 0) {
            $categoryName = trim($crawler->filter('.sah-cat')->text());
        } elseif ($crawler->filter('.cp-crumbs a')->count() >= 2) {
            $categoryName = trim($crawler->filter('.cp-crumbs a')->last()->text());
        }

        // Technical Specs
        $size = null;
        $requires = null;
        $architecture = null;

        $crawler->filter('.sah-specs .sah-spec')->each(function (Crawler $spec) use (&$size, &$requires, &$architecture) {
            if ($spec->filter('dt')->count() === 0 || $spec->filter('dd')->count() === 0) {
                return;
            }
            $label = strtolower(trim($spec->filter('dt')->text()));
            $value = trim($spec->filter('dd')->text());

            if (str_contains($label, 'size')) {
                $size = $value;
            } elseif (str_contains($label, 'requires')) {
                $requires = $value;
            } elseif (str_contains($label, 'architecture')) {
                $architecture = $value;
            }
        });

        // Content / Description
        $descriptionHtml = '';
        if ($crawler->filter('.entry-content')->count() > 0) {
            $descriptionHtml = $this->sanitizeContentHtml($crawler->filter('.entry-content'));
        }

        // Short Description (first clean paragraph)
        $shortDesc = '';
        if ($crawler->filter('.entry-content > p')->count() > 0) {
            $shortDesc = trim($crawler->filter('.entry-content > p')->first()->text());
        }

        // Download Page URL
        $downloadUrl = null;
        if ($crawler->filter('a.cp-download, a.sah-cta')->count() > 0) {
            $downloadUrl = $crawler->filter('a.cp-download, a.sah-cta')->attr('href');
        }

        // Rating & Downloads count
        $rating = null;
        if ($crawler->filter('.card-rating strong')->count() > 0) {
            $rating = (float) trim($crawler->filter('.card-rating strong')->text());
        }

        $downloads = 0;
        if ($crawler->filter('.sah-dls strong')->count() > 0) {
            $dlsText = str_replace([',', '.'], '', trim($crawler->filter('.sah-dls strong')->text()));
            $downloads = (int) $dlsText;
        }

        // Interactive Tabs extraction (Features, Screenshots, Whats new?)
        $tabTitles = [];
        $crawler->filter('.wps-tabs-item')->each(function (Crawler $tabNode, $i) use (&$tabTitles) {
            $tabTitles[$i] = strtolower(trim($tabNode->text()));
        });

        $tabPanels = [];
        $crawler->filter('.wps-tab-text, .wps-tabs-content > div')->each(function (Crawler $panelNode, $i) use (&$tabPanels) {
            $tabPanels[$i] = $panelNode;
        });

        $screenshots = [];
        $whatsNew = null;
        $features = null;

        foreach ($tabTitles as $i => $title) {
            if (! isset($tabPanels[$i])) {
                continue;
            }
            $panel = $tabPanels[$i];

            if (str_contains($title, 'screenshot') || str_contains($title, 'captura')) {
                $panel->filter('a, img')->each(function (Crawler $node) use (&$screenshots) {
                    $imgUrl = null;
                    if ($node->nodeName() === 'a' && preg_match('/\.(avif|webp|png|jpe?g)$/i', $node->attr('href') ?? '')) {
                        $imgUrl = $node->attr('href');
                    } elseif ($node->nodeName() === 'img') {
                        $imgUrl = $node->attr('src') ?? $node->attr('data-src');
                    }
                    if ($imgUrl && ! in_array($imgUrl, $screenshots)) {
                        $screenshots[] = $imgUrl;
                    }
                });
            } elseif (str_contains($title, 'what') || str_contains($title, 'novedad') || str_contains($title, 'changelog')) {
                $whatsNew = $this->sanitizeTabContentHtml($panel);
            } elseif (str_contains($title, 'feature') || str_contains($title, 'caracter')) {
                $features = $this->sanitizeTabContentHtml($panel);
            }
        }

        // Fallback for screenshots if tabs were empty
        if (empty($screenshots)) {
            $crawler->filter('.entry-content img')->each(function (Crawler $img) use (&$screenshots, $iconUrl) {
                $src = $img->attr('src') ?? $img->attr('data-src');
                if (! $src || $src === $iconUrl) {
                    return;
                }
                $width = (int) ($img->attr('width') ?? 0);
                if ($width > 0 && $width < 200) {
                    return;
                }
                if (! in_array($src, $screenshots)) {
                    $screenshots[] = $src;
                }
            });
        }

        return [
            'name' => $this->cleanAppName($name, $version),
            'slug' => $this->extractSlugFromUrl($pageUrl),
            'version' => $version,
            'icon_url' => $iconUrl,
            'category_name' => $categoryName,
            'size' => $size,
            'requires' => $requires,
            'platform' => $architecture ?? 'Universal',
            'description' => $descriptionHtml,
            'short_description' => Str::limit($shortDesc, 255),
            'download_page_url' => $downloadUrl,
            'rating' => $rating,
            'downloads' => $downloads,
            'screenshots' => $screenshots,
            'whats_new' => $whatsNew,
            'features' => $features,
        ];
    }

    /**
     * Parse download page for clean direct file-host mirrors
     *
     * @return array{mirrors: array<int, array{host: string, url: string}>, older_versions: array<int, array{version: string, meta: string, mirrors: array}>}
     */
    public function parseDownloadMirrors(string $html): array
    {
        $crawler = new Crawler($html);
        $cleanMirrors = [];
        $seenUrls = [];

        // 1. Current Version Mirrors
        $crawler->filter('ul.dl-mirror-list li')->each(function (Crawler $li) use (&$cleanMirrors, &$seenUrls) {
            // Discard any ad rows or sponsored containers
            if (
                $li->filter('.is-sponsored, [data-dl-ad], .dl-ad-row, .dl-ad-payload')->count() > 0
                || str_contains($li->attr('class') ?? '', 'ad')
            ) {
                return;
            }

            $a = $li->filter('a.dl-mirror, a');
            if ($a->count() === 0) {
                return;
            }

            $url = trim($a->attr('href'));
            $hostName = '';

            if ($a->filter('.dl-mirror-name')->count() > 0) {
                $hostName = trim($a->filter('.dl-mirror-name')->text());
            } elseif ($a->filter('.dl-mirror-host')->count() > 0) {
                $hostName = trim($a->filter('.dl-mirror-host')->text());
            }

            if ($this->isCleanDownloadUrl($url)) {
                if (! isset($seenUrls[$url])) {
                    $seenUrls[$url] = true;
                    $cleanMirrors[] = [
                        'host' => $hostName ?: parse_url($url, PHP_URL_HOST) ?: 'Mirror',
                        'url' => $url,
                    ];
                }
            }
        });

        // 2. Version History Older Builds
        $olderVersions = [];
        if ($crawler->filter('#version-history .dl-old-list li.dl-old')->count() > 0) {
            $crawler->filter('#version-history .dl-old-list li.dl-old')->each(function (Crawler $oldLi) use (&$olderVersions) {
                $ver = '';
                if ($oldLi->filter('.dl-old-ver')->count() > 0) {
                    $ver = trim($oldLi->filter('.dl-old-ver')->text());
                }

                $meta = '';
                if ($oldLi->filter('.dl-old-meta')->count() > 0) {
                    $meta = trim($oldLi->filter('.dl-old-meta')->text());
                }

                $versionMirrors = [];
                $oldLi->filter('.dl-old-files a')->each(function (Crawler $oldA) use (&$versionMirrors) {
                    $fileUrl = trim($oldA->attr('href'));
                    if ($this->isCleanDownloadUrl($fileUrl)) {
                        $versionMirrors[] = [
                            'host' => trim($oldA->text()) ?: 'Mirror',
                            'url' => $fileUrl,
                        ];
                    }
                });

                if ($ver && count($versionMirrors) > 0) {
                    $olderVersions[] = [
                        'version' => $ver,
                        'meta' => $meta,
                        'mirrors' => $versionMirrors,
                    ];
                }
            });
        }

        return [
            'mirrors' => $cleanMirrors,
            'older_versions' => $olderVersions,
        ];
    }

    /**
     * Validate that a download link is a clean file-host link and not an ad/redirect
     */
    public function isCleanDownloadUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return false;
        }

        $lowerUrl = strtolower($url);

        // Check for ad/tracker blacklisted keywords
        foreach ($this->blockedDomainKeywords as $badKeyword) {
            if (str_contains($lowerUrl, $badKeyword)) {
                return false;
            }
        }

        // Verify it belongs to known trusted hosts OR does not look like an ad query redirect
        foreach ($this->trustedHostKeywords as $trustedHost) {
            if (str_contains($lowerUrl, $trustedHost)) {
                return true;
            }
        }

        // If not in trusted list, ensure it has a valid domain without suspicious query redirection
        $parsed = parse_url($url);
        if (empty($parsed['host'])) {
            return false;
        }

        // Discard root haxmac internal links that aren't downloads
        if (str_contains($parsed['host'], 'haxmac.to') || str_contains($parsed['host'], 'haxmac.cc')) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize description HTML, removing tabs wrappers, ads, scripts, and keeping only clean prose
     */
    protected function sanitizeContentHtml(Crawler $contentNode): string
    {
        $rawHtml = $contentNode->html();
        if (trim($rawHtml) === '') {
            return '';
        }

        // 1. Strip scripts, styles, iframes, and guard containers
        $rawHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $rawHtml);
        $rawHtml = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $rawHtml);
        $rawHtml = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $rawHtml);
        $rawHtml = preg_replace('/<div class="hx-guard"[^>]*>.*?<\/div>/is', '', $rawHtml);

        // 2. Load into DOM to cleanly remove tabs containers and widgets
        $doc = new \DOMDocument;
        $prevErrors = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$rawHtml.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $xpath = new \DOMXPath($doc);
        $unwantedNodes = $xpath->query('//*[contains(@class, "wps-tabs") or contains(@class, "wps-shortcode") or contains(@class, "sah-cta") or contains(@class, "download-") or contains(@class, "is-sponsored")]');
        if ($unwantedNodes) {
            foreach ($unwantedNodes as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $html = '';
        $root = $doc->getElementsByTagName('div')->item(0);
        if ($root) {
            foreach ($root->childNodes as $child) {
                $html .= $doc->saveHTML($child);
            }
        } else {
            $html = $doc->saveHTML();
        }

        // 3. Fallback regex to ensure no tab residue survives
        $html = preg_replace('/<div[^>]*class="[^"]*(?:wps-shortcode|wps-tabs)[^"]*"[^>]*>[\s\S]*$/i', '', $html);

        // 4. Strip unsafe tags, retaining clean prose
        $cleanHtml = strip_tags(trim($html), '<p><br><strong><b><em><i><u><ul><ol><li><h3><h4><blockquote><a>');

        // 5. Clean out empty paragraphs
        $cleanHtml = preg_replace('/<p>\s*(?:&nbsp;)?\s*<\/p>/i', '', $cleanHtml);

        return trim($cleanHtml);
    }

    /**
     * Sanitize tab HTML fragment (strip unsafe tags and external tracking links)
     */
    protected function sanitizeTabContentHtml(Crawler $panelNode): string
    {
        $html = $panelNode->html();
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        $html = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $html);

        return trim(strip_tags($html, '<p><br><ul><ol><li><strong><b><em><i>'));
    }

    /**
     * Clean App name removing redundant version suffixes if present
     */
    protected function cleanAppName(string $name, ?string $version = null): string
    {
        $name = trim($name);
        if ($version && str_ends_with($name, $version)) {
            $name = trim(substr($name, 0, -strlen($version)));
        }

        return preg_replace('/\s+/', ' ', $name);
    }

    /**
     * Extract slug from a URL
     */
    protected function extractSlugFromUrl(string $url): string
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $parts = explode('/', $path);

        return end($parts) ?: '';
    }

    /**
     * Parse pagination metadata from page HTML
     *
     * @return array{current_page: int, total_pages: int, has_next: bool, has_prev: bool}
     */
    public function parsePagination(string $html, int $fallbackCurrentPage = 1): array
    {
        $crawler = new Crawler($html);
        $currentPage = $fallbackCurrentPage;
        $totalPages = 1;
        $hasNext = false;
        $hasPrev = false;

        // Current Page
        if ($crawler->filter('.page-numbers.current, span.page-numbers.current')->count() > 0) {
            $currentText = trim($crawler->filter('.page-numbers.current, span.page-numbers.current')->text());
            if (is_numeric($currentText)) {
                $currentPage = (int) $currentText;
            }
        }

        // Total Pages: find all numeric page links
        $crawler->filter('.page-numbers')->each(function (Crawler $node) use (&$totalPages, &$hasNext, &$hasPrev) {
            $class = $node->attr('class') ?? '';
            $text = trim($node->text());

            if (str_contains($class, 'next')) {
                $hasNext = true;
            } elseif (str_contains($class, 'prev')) {
                $hasPrev = true;
            } elseif (is_numeric($text)) {
                $pageNum = (int) $text;
                if ($pageNum > $totalPages) {
                    $totalPages = $pageNum;
                }
            }
        });

        // Ensure total_pages is at least current_page
        if ($totalPages < $currentPage) {
            $totalPages = $currentPage;
        }

        // If next link exists but total_pages didn't catch a higher number, bump total_pages
        if ($hasNext && $totalPages <= $currentPage) {
            $totalPages = $currentPage + 1;
        }

        if ($currentPage > 1) {
            $hasPrev = true;
        }

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev,
        ];
    }
}
