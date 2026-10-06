{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Homepage -->
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ $latestAppDate ?? now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Main Navigation Hubs -->
    <url>
        <loc>{{ route('categories') }}</loc>
        <lastmod>{{ $latestAppDate ?? now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('popular') }}</loc>
        <lastmod>{{ $latestAppDate ?? now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('new') }}</loc>
        <lastmod>{{ $latestAppDate ?? now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- macOS Guides -->
    <url>
        <loc>{{ route('guide.sip') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>{{ route('guide.fix') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <!-- Institutional & Legal Pages -->
    <url>
        <loc>{{ route('about') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ route('contact') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ route('dmca') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>{{ route('privacy') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>{{ route('terms') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>

    <!-- Categories -->
    @foreach($categories as $category)
    <url>
        <loc>{{ route('category', $category->slug) }}</loc>
        @if($category->updated_at)
        <lastmod>{{ $category->updated_at->tz('UTC')->toAtomString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    <!-- Applications -->
    @foreach($applications as $app)
    <url>
        <loc>{{ route('app', $app->slug) }}</loc>
        <lastmod>{{ $app->updated_at->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>{{ $app->featured ? '0.9' : '0.8' }}</priority>
        @if($app->icon_url)
        <image:image>
            <image:loc>{{ $app->icon_url }}</image:loc>
            <image:title>{{ $app->name }}</image:title>
        </image:image>
        @endif
        @if($app->screenshot_url)
        <image:image>
            <image:loc>{{ $app->screenshot_url }}</image:loc>
            <image:title>{{ $app->name }}</image:title>
        </image:image>
        @endif
    </url>
    @endforeach
</urlset>
