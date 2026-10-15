{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ route('sitemap.apps') }}</loc>
        <lastmod>{{ $latestAppDate }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('sitemap.categories') }}</loc>
        <lastmod>{{ $latestCategoryDate }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('sitemap.pages') }}</loc>
        <lastmod>{{ $latestPagesDate }}</lastmod>
    </sitemap>
</sitemapindex>
