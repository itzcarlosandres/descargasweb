<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TorrentmacClient
{
    protected string $baseUrl = 'https://www.torrentmac.net';

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
        $cacheKey = 'torrentmac_html_'.md5($url);

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
                ->timeout(25)
                ->retry(2, 500)
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                if ($useCache && ! empty($html)) {
                    Cache::put($cacheKey, $html, $ttlSeconds);
                }

                return $html;
            }

            Log::warning("TorrentmacClient: Failed to fetch {$url} with status {$response->status()}");

            return null;
        } catch (\Throwable $e) {
            Log::error("TorrentmacClient exception on {$url}: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Clear cached HTML for a specific URL
     */
    public function clearUrlCache(string $url): void
    {
        Cache::forget('torrentmac_html_'.md5($url));
    }

    /**
     * Fetch home page / latest additions containing the main catalog
     */
    public function getHomePageHtml(int $page = 1): ?string
    {
        if ($page > 1) {
            return $this->fetchHtml($this->baseUrl.'/page/'.$page.'/');
        }

        return $this->fetchHtml($this->baseUrl.'/');
    }

    /**
     * Search TorrentMac in real-time with optional pagination
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
     * Resolve exact TorrentMac URL for category
     */
    public function getCategoryUrl(string $categorySlug, int $page = 1): string
    {
        $map = [
            'all' => $this->baseUrl.'/',
            'apps' => $this->baseUrl.'/category/apps/',
            'games' => $this->baseUrl.'/category/games/',
            'utilities-tools' => $this->baseUrl.'/category/utilities-tools/',
            'audio-editors' => $this->baseUrl.'/category/audio-editors/',
            'developer-tools' => $this->baseUrl.'/category/developer-tools/',
            'video' => $this->baseUrl.'/category/video/',
            'productivity' => $this->baseUrl.'/category/productivity/',
            'graphics-design' => $this->baseUrl.'/category/graphics-design/',
            'photography' => $this->baseUrl.'/category/photography/',
            'plugins' => $this->baseUrl.'/category/plugins/',
        ];

        $baseUrl = $map[$categorySlug] ?? ($this->baseUrl.'/category/'.$categorySlug.'/');
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
     * Download binary data (e.g. images, icons or torrent files)
     */
    public function downloadBinary(string $url): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Referer' => $this->baseUrl.'/',
                    'Accept' => '*/*',
                ])
                ->timeout(30)
                ->retry(2, 500)
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            Log::warning("TorrentmacClient downloadBinary failed with HTTP {$response->status()} for {$url}");

            return null;
        } catch (\Throwable $e) {
            Log::warning("TorrentmacClient downloadBinary failed for {$url}: {$e->getMessage()}");

            return null;
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
