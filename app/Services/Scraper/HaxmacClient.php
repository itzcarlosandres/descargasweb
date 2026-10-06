<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HaxmacClient
{
    protected string $baseUrl = 'https://haxmac.to';

    protected array $userAgents = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Safari/605.1.15',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14.7; rv:130.0) Gecko/20100101 Firefox/130.0',
    ];

    /**
     * Get a random modern browser User-Agent
     */
    protected function getRandomUserAgent(): string
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    /**
     * Perform HTTP GET with headers, retries and intelligent short-term caching
     */
    public function fetchHtml(string $url, bool $useCache = true, int $ttlSeconds = 300): ?string
    {
        $cacheKey = 'haxmac_html_'.md5($url);

        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9,es;q=0.8',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                ])
                ->timeout(20)
                ->retry(2, 400)
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                if ($useCache && ! empty($html)) {
                    Cache::put($cacheKey, $html, $ttlSeconds);
                }

                return $html;
            }

            Log::warning("HaxmacClient: Failed to fetch {$url} with status {$response->status()}");

            return null;
        } catch (\Throwable $e) {
            Log::error("HaxmacClient exception on {$url}: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Clear cached HTML for a specific URL
     */
    public function clearUrlCache(string $url): void
    {
        Cache::forget('haxmac_html_'.md5($url));
    }

    /**
     * Fetch home page / latest additions containing the main navigation and catalog
     */
    public function getHomePageHtml(int $page = 1): ?string
    {
        if ($page > 1) {
            return $this->fetchHtml($this->baseUrl.'/page/'.$page.'/');
        }

        return $this->fetchHtml($this->baseUrl.'/');
    }

    /**
     * Search HaxMac in real-time with optional pagination
     */
    public function searchHtml(string $query, int $page = 1): ?string
    {
        $q = urlencode(trim($query));
        if ($page > 1) {
            return $this->fetchHtml($this->baseUrl.'/page/'.$page.'/?s='.$q);
        }

        return $this->fetchHtml($this->baseUrl.'/?s='.$q);
    }

    /**
     * Resolve exact HaxMac URL for any known category slug with pagination
     */
    public function getCategoryUrl(string $categorySlug, int $page = 1): string
    {
        $map = [
            'all' => $this->baseUrl.'/',
            'games' => $this->baseUrl.'/mac-games/',
            'mac-games' => $this->baseUrl.'/mac-games/',
            'apple-arcade' => $this->baseUrl.'/tag/apple-arcade/',
            'assassins-creed' => $this->baseUrl.'/tag/assassins-creed/',
            'gta-grand-theft-auto' => $this->baseUrl.'/tag/gta-grand-theft-auto/',
            'the-trilogy' => $this->baseUrl.'/tag/the-trilogy/',
            'system-utilities' => $this->baseUrl.'/application/system-utilities/',
            'productivity-business' => $this->baseUrl.'/application/productivity-business/',
            'media-design' => $this->baseUrl.'/application/media-design/',
            'developer-tools' => $this->baseUrl.'/application/developer-tools/',
            'mobile-tools' => $this->baseUrl.'/application/mobile-tools/',
            'lifestyle-everyday' => $this->baseUrl.'/application/lifestyle-everyday/',
            'adobe' => $this->baseUrl.'/tag/adobe/',
            'adobe-photoshop' => $this->baseUrl.'/adobe-photoshop/',
            'adobe-lightroom-classic' => $this->baseUrl.'/adobe-lightroom-classic/',
            'adobe-premiere-pro' => $this->baseUrl.'/adobe-premiere-pro/',
            'adobe-illustrator' => $this->baseUrl.'/adobe-illustrator/',
            'editors-choice' => $this->baseUrl.'/editors-choice/',
            'application' => $this->baseUrl.'/application/',
        ];

        $baseUrl = $map[$categorySlug] ?? ($this->baseUrl.'/application/'.$categorySlug.'/');
        $baseUrl = rtrim($baseUrl, '/');

        if ($page > 1) {
            return $baseUrl.'/page/'.$page.'/';
        }

        return $baseUrl.'/';
    }

    /**
     * Fetch HTML for a category by slug with pagination
     */
    public function getCategoryHtmlBySlug(string $categorySlug, int $page = 1): ?string
    {
        $url = $this->getCategoryUrl($categorySlug, $page);

        return $this->fetchHtml($url);
    }

    /**
     * Fetch category page with optional pagination
     */
    public function getCategoryHtml(string $categoryUrlOrPath, int $page = 1): ?string
    {
        $url = str_starts_with($categoryUrlOrPath, 'http')
            ? rtrim($categoryUrlOrPath, '/')
            : rtrim($this->baseUrl, '/').'/'.ltrim($categoryUrlOrPath, '/');

        if ($page > 1) {
            $url .= '/page/'.$page.'/';
        } else {
            $url .= '/';
        }

        return $this->fetchHtml($url);
    }

    /**
     * Download binary data (e.g. images)
     */
    public function downloadBinary(string $url): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Referer' => $this->baseUrl.'/',
                ])
                ->timeout(20)
                ->retry(2, 500)
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning("HaxmacClient downloadBinary failed for {$url}: {$e->getMessage()}");

            return null;
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
