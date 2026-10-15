<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate physical public/sitemap.xml file for search engines';

    public function handle(): int
    {
        $this->info('Generando sitemaps XML modulares...');

        $appUrl = config('app.url');
        if (empty($appUrl) || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $appUrl = 'https://hackmac.cc';
        }

        URL::forceRootUrl($appUrl);
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        URL::forceScheme($scheme);

        $categories = Category::active()->ordered()
            ->select(['id', 'name', 'slug', 'updated_at'])
            ->get();

        $applications = Application::published()
            ->select(['id', 'category_id', 'name', 'slug', 'updated_at', 'featured', 'icon', 'screenshot'])
            ->latest('updated_at')
            ->get();

        $latestAppDate = $applications->first()?->updated_at?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();
        $latestCategoryDate = $categories->sortByDesc('updated_at')->first()?->updated_at?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();
        $latestPagesDate = $latestAppDate;

        // 1. sitemap-apps.xml
        $appsXml = view('sitemap.apps', compact('applications'))->render();
        file_put_contents(public_path('sitemap-apps.xml'), $appsXml);
        Cache::put('sitemap_apps_xml', $appsXml, 86400);

        // 2. sitemap-categories.xml
        $catsXml = view('sitemap.categories', compact('categories'))->render();
        file_put_contents(public_path('sitemap-categories.xml'), $catsXml);
        Cache::put('sitemap_categories_xml', $catsXml, 86400);

        // 3. sitemap-pages.xml
        $pagesXml = view('sitemap.pages', compact('latestAppDate'))->render();
        file_put_contents(public_path('sitemap-pages.xml'), $pagesXml);
        Cache::put('sitemap_pages_xml', $pagesXml, 86400);

        // 4. sitemap.xml (Index)
        $indexXml = view('sitemap.index', compact('latestAppDate', 'latestCategoryDate', 'latestPagesDate'))->render();
        file_put_contents(public_path('sitemap.xml'), $indexXml);
        Cache::put('sitemap_index_xml', $indexXml, 86400);
        Cache::put('sitemap_xml', $indexXml, 86400);

        $this->info('✓ Sitemap Index generado en '.public_path('sitemap.xml'));
        $this->info('✓ Sub-sitemaps generados: sitemap-apps.xml, sitemap-categories.xml, sitemap-pages.xml');

        return self::SUCCESS;
    }
}
