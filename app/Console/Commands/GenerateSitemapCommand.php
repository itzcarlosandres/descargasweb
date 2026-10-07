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
        $this->info('Generando sitemap XML estático...');

        $appUrl = config('app.url');
        if ($appUrl) {
            URL::forceRootUrl($appUrl);
            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            if ($scheme) {
                URL::forceScheme($scheme);
            }
        }

        $categories = Category::active()->ordered()
            ->select(['id', 'name', 'slug', 'updated_at'])
            ->get();

        $applications = Application::published()
            ->select(['id', 'category_id', 'name', 'slug', 'updated_at', 'featured', 'icon', 'screenshot'])
            ->latest('updated_at')
            ->get();

        $latestAppDate = $applications->first()?->updated_at?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString();

        $xml = view('sitemap', compact('categories', 'applications', 'latestAppDate'))->render();

        file_put_contents(public_path('sitemap.xml'), $xml);
        Cache::put('sitemap_xml', $xml, 86400);

        $this->info('✓ Sitemap generado exitosamente en '.public_path('sitemap.xml'));

        return self::SUCCESS;
    }
}
