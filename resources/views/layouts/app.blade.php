<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>

    @php
        $siteTitle = setting('site_name', 'HackMac') . setting('site_name_highlight', '.cc');
        $seoTitle = View::hasSection('title') ? trim(View::getSection('title')) : setting('seo_meta_title', $siteTitle . ' - Descarga Aplicaciones para macOS');
        $seoDescription = View::hasSection('description') ? trim(View::getSection('description')) : setting('seo_meta_description', 'Descubre y descarga las mejores aplicaciones y utilidades verificadas para macOS.');
        $seoImage = View::hasSection('og_image') 
            ? trim(View::getSection('og_image')) 
            : (setting('seo_og_image') ? asset(setting('seo_og_image')) : asset('images/og-share.jpg'));
        $seoType = View::hasSection('og_type') ? trim(View::getSection('og_type')) : 'website';
        $canonicalUrl = View::hasSection('canonical') ? trim(View::getSection('canonical')) : url()->current();
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ setting('seo_keywords', 'mac apps, macos, apple silicon, software mac, dmg macos, hackmac, hackmac.cc') }}">
    <meta name="author" content="{{ $siteTitle }}">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <meta name="googlebot" content="@yield('googlebot', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Open Graph / Facebook / WhatsApp / Telegram / Discord -->
    <meta property="og:site_name" content="{{ $siteTitle }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:secure_url" content="{{ $seoImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $seoTitle }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter / X Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="twitter:image:alt" content="{{ $seoTitle }}">
    @if(setting('twitter_url'))
    <meta name="twitter:site" content="{{ '@' . ltrim(parse_url(setting('twitter_url'), PHP_URL_PATH) ?? '', '/') }}">
    @endif

    <!-- Schema.org JSON-LD Structured Data -->
    @yield('schema')

    @if(setting('favicon_image'))
    <link rel="icon" href="{{ setting('favicon_image') }}">
    <link rel="shortcut icon" href="{{ setting('favicon_image') }}">
    <link rel="apple-touch-icon" href="{{ setting('favicon_image') }}">
    @else
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-background text-text antialiased selection:bg-primary/30">
    <x-navbar />

    <main class="flex-1">
        @if(session('success'))
            <div class="container-app pt-6">
                <div class="p-4 rounded-xl bg-success/15 border border-success/30 text-success text-sm flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="container-app pt-6">
                <div class="p-4 rounded-xl bg-danger/15 border border-danger/30 text-danger text-sm flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <x-footer />

    @livewireScripts
    @stack('scripts')
</body>
</html>
