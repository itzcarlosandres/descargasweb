{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($applications as $app)
    <url>
        <loc>{{ route('app', $app->slug) }}</loc>
        <lastmod>{{ $app->updated_at ? $app->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString() }}</lastmod>
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
