<x-app-layout>
    @php
        $appTitle = "Download {$application->name} {$application->version} for Mac - " . config('app.name');
        $appDesc = Str::limit(strip_tags($application->short_description ?: $application->description), 160) ?: "Download {$application->name} {$application->version} for macOS. Safe and verified software.";
        $shareImage = $application->screenshot_url 
            ?: ($application->images->first()?->image_url 
            ?: ($application->icon_url ?: asset('images/og-share.jpg')));
    @endphp

    @section('title', $appTitle)
    @section('description', $appDesc)
    @section('og_image', $shareImage)
    @section('og_type', 'article')
    @section('canonical', route('app', $application->slug))

    @section('schema')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "{{ addslashes($application->name) }}",
        "operatingSystem": "macOS",
        "applicationCategory": "{{ addslashes($application->category->name ?? 'Utilities') }}",
        "softwareVersion": "{{ addslashes($application->version ?? '1.0') }}",
        "fileSize": "{{ addslashes($application->formatted_size ?? '') }}",
        "description": "{{ addslashes($appDesc) }}",
        "image": "{{ $shareImage }}",
        "url": "{{ route('app', $application->slug) }}",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }@if($application->rating && $application->rating > 0),
        "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "{{ number_format($application->rating, 1) }}",
            "reviewCount": "{{ max($application->reviews_count, 1) }}",
            "bestRating": "5",
            "worstRating": "1"
        }
        @endif
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "{{ route('home') }}"
            },
            {
                "@@type": "ListItem",
                "position": 2,
                "name": "{{ addslashes($application->category->name ?? 'Category') }}",
                "item": "{{ route('category', $application->category->slug ?? 'all') }}"
            },
            {
                "@@type": "ListItem",
                "position": 3,
                "name": "{{ addslashes($application->name) }}",
                "item": "{{ route('app', $application->slug) }}"
            }
        ]
    }
    </script>
    @endsection

    @php
        $avatarColors = [
            ['#0071E3', '#004C99'],
            ['#4D9BE9', '#005FBF'],
            ['#2563EB', '#1D4ED8'],
            ['#0284C7', '#0369A1'],
            ['#3B82F6', '#1E40AF'],
            ['#64748B', '#334155'],
        ];
    @endphp

    <div class="site-container layout-with-sidebar py-6 sm:py-8">
        <!-- Main Article Column (864px equivalent in desktop grid) -->
        <article id="post-{{ $application->id }}" class="post-{{ $application->id }} post type-post status-publish format-standard has-post-thumbnail hentry category-{{ $application->category->slug }} min-w-0">
            <div class="single-shell has-sidebar">
                <!-- macOS Window Shell -->
                <div class="single-window">
                    <!-- Titlebar with Traffic Lights & Breadcrumbs -->
                    <div class="window-titlebar has-crumbs">
                        <span class="traffic-lights" aria-hidden="true">
                            <span class="tl-red"></span>
                            <span class="tl-yellow"></span>
                            <span class="tl-green"></span>
                        </span>
                        <nav class="cp-crumbs cp-crumbs-yoast" aria-label="Breadcrumb">
                            <span>
                                <span><a href="{{ route('home') }}">Home</a></span>
                                <span class="mx-1 text-[#A1A1A6]">»</span>
                                <span><a href="{{ route('categories') }}">Application</a></span>
                                <span class="mx-1 text-[#A1A1A6]">»</span>
                                <span><a href="{{ route('category', $application->category->slug) }}">{{ $application->category->name }}</a></span>
                                <span class="mx-1 text-[#A1A1A6]">»</span>
                                <span class="breadcrumb_last" aria-current="page">{{ $application->name }}</span>
                            </span>
                        </nav>
                    </div>

                    <!-- Single Content Area -->
                    <div class="single-content">
                        <!-- Application Header (sah-main, sah-side, sah-specs) -->
                        <header class="single-app-header">
                            <!-- sah-main -->
                            <div class="sah-main">
                                <!-- sah-icon: squircle icon -->
                                <div class="sah-icon">
                                    <img width="200" height="200" src="{{ $application->icon_url }}" class="sah-img wp-post-image" alt="{{ $application->name }} logo" loading="eager" decoding="async"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full bg-gradient-to-tr from-primary/30 to-primary/10 flex items-center justify-center text-primary font-bold text-3xl sm:text-4xl" style="display: none;">
                                        {{ strtoupper(substr($application->name, 0, 1)) }}
                                    </div>
                                </div>

                                <!-- sah-info -->
                                <div class="sah-info min-w-0 flex-1">
                                    <h1 class="entry-title font-heading text-xl sm:text-2xl lg:text-3xl font-extrabold text-white tracking-tight !mb-1.5 leading-tight break-words">
                                        {{ $application->name }}
                                    </h1>

                                    <!-- Badges Row: Version & Category -->
                                    <div class="flex items-center gap-2 flex-wrap mb-2">
                                        <span class="sah-ver font-mono font-bold">v{{ $application->version }}</span>
                                        <a class="sah-cat" href="{{ route('category', $application->category->slug) }}">{{ $application->category->name }}</a>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#262019] text-[#A8A199] border border-[#3D3224]">
                                            {{ strtoupper($application->license ?? 'Free') }}
                                        </span>
                                    </div>

                                    <!-- Rating, Downloads & Updated Date -->
                                    <div class="sah-ident flex items-center gap-2 sm:gap-3 flex-wrap text-xs text-[#A1A1A6]">
                                        <span class="card-rating inline-flex items-center gap-1 text-[#FEBC2E] font-bold" title="Rated {{ number_format($application->rating > 0 ? $application->rating : 4.4, 1) }} out of 5 by {{ $application->reviews_count > 0 ? $application->reviews_count : 25 }} people">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="inline-block">
                                                <path d="M12 2.6l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.4l-5.8 3.06 1.1-6.46-4.7-4.58 6.5-.94z"></path>
                                            </svg>
                                            <strong>{{ number_format($application->rating > 0 ? $application->rating : 4.4, 1) }}</strong>
                                            <span class="card-rating-n text-[11px] text-[#8C847A]">({{ $application->reviews_count > 0 ? $application->reviews_count : 25 }})</span>
                                        </span>
                                        <span class="text-[#3D3224]">&middot;</span>
                                        <span class="sah-dls">
                                            <strong class="text-white">{{ $application->formatted_downloads }}</strong> downloads
                                        </span>
                                        <span class="text-[#3D3224] hidden sm:inline">&middot;</span>
                                        <span class="sah-when block sm:inline w-full sm:w-auto text-[11px] text-[#8C847A]">
                                            Updated {{ $application->updated_at->format('M j, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- sah-side: Download CTA Button (Exact User Reference Match) -->
                            <div class="sah-side flex flex-col items-center sm:items-end w-full sm:w-auto mt-2 sm:mt-0">
                                @if($application->has_torrent && empty($application->download_url_external) && empty($application->download_url))
                                    <!-- Only Torrent Available -->
                                    <a class="btn-download-banner !bg-gradient-to-r !from-[#30D158] !to-[#10B981] !text-black !shadow-[#30D158]/25 cursor-pointer w-full sm:w-[260px]"
                                       href="{{ route('download', ['application' => $application->slug, 'type' => 'torrent']) }}"
                                       title="Download {{ $application->name }} Torrent">
                                        <span class="btn-download-label !text-black flex items-center gap-1.5">
                                            <span>Download Torrent</span>
                                        </span>
                                        <span class="btn-download-sub !text-black/80">
                                            .torrent &middot; {{ strtoupper($application->license ?? 'Free') }}
                                        </span>
                                        <span class="sr-only">Download Torrent</span>
                                    </a>
                                @else
                                    <!-- Direct Download (Primary) -->
                                    <a class="btn-download-banner cursor-pointer w-full sm:w-[260px]"
                                       href="{{ route('download', $application->slug) }}"
                                       title="Download {{ $application->name }}">
                                        <span class="btn-download-label">Download</span>
                                        <span class="btn-download-sub">
                                            @if($application->versions->count() > 1)
                                                {{ $application->versions->count() }} versions
                                            @else
                                                {{ $application->version }} &middot; {{ strtoupper($application->license ?? 'Free') }}
                                            @endif
                                        </span>
                                        <span class="sr-only">Download Now</span>
                                    </a>
                                @endif

                                <div class="mt-2 text-center sm:text-right w-full sm:w-auto" x-data>
                                    <span class="text-xs text-[#8C847A] font-medium block">
                                        {{ $application->version }} · {{ strtoupper($application->license ?? 'Free') }} · Universal
                                    </span>
                                    <div class="flex items-center justify-center sm:justify-end gap-2.5 mt-1">
                                        <a class="sah-older text-xs text-[#8C847A] hover:text-primary transition-colors" href="#older-versions">
                                            All older versions
                                        </a>
                                        <span class="text-zinc-600 text-xs">·</span>
                                        <button type="button"
                                                @click="$dispatch('open-report-modal')"
                                                onclick="window.dispatchEvent(new CustomEvent('open-report-modal'))"
                                                class="text-xs text-amber-500 hover:text-amber-400 font-medium inline-flex items-center gap-1 py-0.5 px-1.5 rounded-md hover:bg-amber-500/10 transition-colors cursor-pointer"
                                                title="Reportar si el enlace no funciona">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span>Reportar enlace</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- sah-specs: 4 columns grid -->
                            <dl class="sah-specs" style="--sah-cols: 4">
                                <div class="sah-spec">
                                    <dt>Size</dt>
                                    <dd>{{ $application->formatted_size }}</dd>
                                </div>
                                <div class="sah-spec">
                                    <dt>Requires</dt>
                                    <dd>{{ $application->platform ?? 'macOS 14' }}</dd>
                                </div>
                                <div class="sah-spec">
                                    <dt>Architecture</dt>
                                    <dd>Universal</dd>
                                </div>
                                <div class="sah-spec sah-spec-rate"
                                     x-data="{ 
                                         currentRating: {{ $userRating ?? (session('rated_app_' . $application->id) ?? 0) }},
                                         hoverRating: 0 
                                     }"
                                     @rating-updated.window="currentRating = $event.detail">
                                    <dt>Your rating</dt>
                                    <dd>
                                        <a href="#respond" class="cp-rate-stars" title="Rate this app" @mouseleave="hoverRating = 0">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="cp-star" aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}"
                                                      @mouseenter="hoverRating = {{ $i }}"
                                                      @click="window.dispatchEvent(new CustomEvent('select-form-rating', { detail: {{ $i }} }))"
                                                      :class="(hoverRating > 0 ? {{ $i }} <= hoverRating : (currentRating > 0 && {{ $i }} <= currentRating)) ? '!text-[#FBBF24] is-active' : '!text-[#484036] dark:!text-[#484036] text-[#D1C9BE]'">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M12 2.6l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.4l-5.8 3.06 1.1-6.46-4.7-4.58 6.5-.94z"/>
                                                    </svg>
                                                </span>
                                            @endfor
                                        </a>
                                    </dd>
                                </div>
                            </dl>
                        </header>

                        <!-- Entry Content -->
                        <div class="entry-content mb-8">
                            @if(!empty($application->description))
                                <div class="prose max-w-none text-[#3E3933] dark:text-[#C4BDB5] text-sm sm:text-base leading-relaxed space-y-4 [&>p]:text-[#3E3933] dark:[&>p]:text-[#C4BDB5] [&>p]:leading-relaxed [&>strong]:text-[#1C1814] dark:[&>strong]:text-white [&>a]:text-primary [&>a]:underline">
                                    {!! $application->description !!}
                                </div>
                            @else
                                <p class="text-[#3E3933] dark:text-[#C4BDB5] text-sm sm:text-base leading-relaxed">
                                    {{ $application->name }} is a professional solution for macOS designed with the highest standards of performance and reliability.
                                </p>
                            @endif
                        </div>

                        <!-- Interactive Tab Component (Features, Screenshots, Whats new?) -->
                        <div x-data="{ tab: 'features', zoomModal: null }" class="wps-shortcode-wrapper wps-tabs-wrapper">
                            <ul class="wps-tabs-list" role="tablist">
                                <li class="wps-tabs-item" :class="{ 'wps-active': tab === 'features' }" @click="tab = 'features'" role="tab" :aria-selected="tab === 'features'">
                                    Features
                                </li>
                                <li class="wps-tabs-item" :class="{ 'wps-active': tab === 'screenshots' }" @click="tab = 'screenshots'" role="tab" :aria-selected="tab === 'screenshots'">
                                    <span>Screenshots</span>
                                    @if($application->images && $application->images->count() > 0)
                                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-primary/20 text-primary font-bold">{{ $application->images->count() }}</span>
                                    @endif
                                </li>
                                <li class="wps-tabs-item" :class="{ 'wps-active': tab === 'whatsnew' }" @click="tab = 'whatsnew'" role="tab" :aria-selected="tab === 'whatsnew'">
                                    Whats new?
                                </li>
                            </ul>

                            <div class="wps-tabs-content">
                                <!-- Tab 1: Features -->
                                <div x-show="tab === 'features'" class="wps-tab-text" role="tabpanel">
                                    @if(!empty($application->features))
                                        <div class="prose dark:prose-invert max-w-none text-[#3E3933] dark:text-[#C4BDB5] text-xs sm:text-sm leading-relaxed [&>ul]:space-y-2.5 [&>ul>li>strong]:text-[#1C1814] dark:[&>ul>li>strong]:text-white [&>p]:text-[#3E3933] dark:[&>p]:text-[#C4BDB5] [&>p>strong]:text-[#1C1814] dark:[&>p>strong]:text-white [&>h1]:text-[#1C1814] dark:[&>h1]:text-white [&>h2]:text-[#1C1814] dark:[&>h2]:text-white [&>h3]:text-[#1C1814] dark:[&>h3]:text-white [&>h4]:text-[#1C1814] dark:[&>h4]:text-white">
                                            {!! $application->features !!}
                                        </div>
                                    @else
                                        <ul class="space-y-2.5 text-xs sm:text-sm text-[#3E3933] dark:text-[#C4BDB5]">
                                            <li>
                                                <strong class="text-[#1C1814] dark:text-white">High-performance optimization:</strong> Specifically engineered to take full advantage of Apple Silicon and Intel architecture.
                                            </li>
                                            <li>
                                                <strong class="text-[#1C1814] dark:text-white">Fluid and modern interface:</strong> Deeply integrated with macOS visual aesthetics and native controls.
                                            </li>
                                            <li>
                                                <strong class="text-[#1C1814] dark:text-white">Security & Privacy:</strong> Thoroughly verified and optimized to safeguard your files and system data.
                                            </li>
                                            <li>
                                                <strong class="text-[#1C1814] dark:text-white">Rock-solid stability:</strong> Optimized resource usage to keep your Mac fast and responsive at all times.
                                            </li>
                                        </ul>
                                    @endif
                                </div>

                                    <!-- Tab 2: Screenshots -->
                                    <div x-show="tab === 'screenshots'" x-cloak class="wps-tab-text space-y-4" role="tabpanel">
                                        @if($application->images && $application->images->count() > 0)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                @foreach($application->images as $img)
                                                    <div class="group relative rounded-2xl overflow-hidden bg-[#14110E] border border-[#2D251D] hover:border-primary/50 shadow-xl transition-all duration-300 cursor-pointer"
                                                         @click="zoomModal = '{{ $img->url }}'">
                                                        <img src="{{ $img->url }}" alt="{{ $img->alt ?? ($application->name . ' Screenshot') }}" 
                                                             class="w-full h-auto object-cover group-hover:scale-[1.02] transition-transform duration-300"
                                                             loading="lazy">
                                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                            <span class="p-2.5 rounded-full bg-black/70 border border-white/20 text-white shadow-lg">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($application->screenshot_url)
                                            <div class="group relative rounded-2xl overflow-hidden bg-[#14110E] border border-[#2D251D] hover:border-primary/50 shadow-xl transition-all cursor-pointer"
                                                 @click="zoomModal = '{{ $application->screenshot_url }}'">
                                                <img src="{{ $application->screenshot_url }}" alt="{{ $application->name }} screenshot" 
                                                     class="w-full h-auto object-cover group-hover:scale-[1.02] transition-transform duration-300">
                                            </div>
                                        @else
                                            <div class="p-8 rounded-2xl bg-[#14110E] border border-[#2D251D] text-center space-y-2">
                                                <div class="w-10 h-10 mx-auto rounded-xl bg-[#1C1814] flex items-center justify-center text-primary font-bold"></div>
                                                <p class="text-xs text-[#8C847A]">High-resolution screenshots available directly in the DMG installer.</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Tab 3: Whats new? -->
                                    <div x-show="tab === 'whatsnew'" x-cloak class="wps-tab-text" role="tabpanel">
                                        @if($application->whats_new)
                                            <div class="p-5 rounded-2xl bg-[#FAF7F3] dark:bg-[#14110E] border border-[#EBE4DB] dark:border-[#2D251D] space-y-3">
                                                <div class="flex items-center gap-2 pb-2 border-b border-[#EBE4DB] dark:border-[#251E18]">
                                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                                    <h3 class="text-xs font-bold text-[#1C1814] dark:text-white uppercase tracking-wider">
                                                        What's New in Version {{ $application->version }}
                                                    </h3>
                                                </div>
                                                <div class="prose max-w-none text-[#3E3933] dark:text-[#C4BDB5] text-xs sm:text-sm leading-relaxed [&>ul]:space-y-2 [&>p]:text-[#3E3933] dark:[&>p]:text-[#C4BDB5] [&>strong]:text-[#1C1814] dark:[&>strong]:text-white">
                                                    {!! $application->whats_new !!}
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-5 rounded-2xl bg-[#FAF7F3] dark:bg-[#14110E] border border-[#EBE4DB] dark:border-[#2D251D] space-y-3">
                                                <div class="flex items-center gap-2 pb-2 border-b border-[#EBE4DB] dark:border-[#251E18]">
                                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                                    <h3 class="text-xs font-bold text-[#1C1814] dark:text-white uppercase tracking-wider">
                                                        What's New in Version {{ $application->version }}
                                                    </h3>
                                                </div>
                                                <ul class="text-xs sm:text-sm text-[#3E3933] dark:text-[#C4BDB5] space-y-2">
                                                    <li>Security and stability engine updated to version {{ $application->version }}.</li>
                                                    <li>Full native compatibility with macOS Sequoia and Apple Silicon (M1/M2/M3/M4).</li>
                                                    <li>Performance optimizations and minor bug fixes.</li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Lightbox Zoom Modal for Screenshots -->
                                <div x-show="zoomModal" x-cloak
                                     @keydown.escape.window="zoomModal = null"
                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0">
                                    <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center"
                                         @click.away="zoomModal = null">
                                        <button type="button" @click="zoomModal = null"
                                                class="absolute -top-10 right-0 text-white/80 hover:text-white text-sm font-bold flex items-center gap-1 cursor-pointer bg-white/10 px-3 py-1 rounded-full">
                                            <span>Close</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        <img :src="zoomModal" alt="Screenshot" 
                                             class="max-w-full max-h-[85vh] object-contain rounded-2xl border border-white/10 shadow-2xl">
                                    </div>
                                </div>
                            </div>

                        <!-- Older Versions & Compatibility Section (Estilo Leer más) -->
                        <section class="cp-versions" id="older-versions" x-data="{ expanded: false }">
                            <div class="cp-versions-head flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2">
                                <div>
                                    <h2 class="font-heading font-bricolage text-sm font-bold text-[#1D1D1F] dark:text-white uppercase tracking-wider">
                                        Older versions & Compatibility
                                    </h2>
                                    <p class="text-[12px] text-[#86868B] dark:text-[#A1A1A6] mt-0.5">
                                        Running macOS Catalina, Big Sur, Monterey, or Ventura? Download tested builds for Intel and Apple Silicon.
                                    </p>
                                </div>
                                <span class="text-[11px] font-semibold text-primary/90 bg-primary/10 px-2.5 py-1 rounded-full w-fit">
                                    {{ $application->versions->count() > 0 ? $application->versions->count() : 1 }} {{ Str::plural('build', $application->versions->count() > 0 ? $application->versions->count() : 1) }}
                                </span>
                            </div>

                            <ul class="cp-versions-list mt-3 space-y-2">
                                @if($application->versions && $application->versions->count() > 0)
                                    @foreach($application->versions as $ver)
                                        <li x-show="expanded || {{ $loop->index }} < 2"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 -translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl bg-[#F5F5F7] dark:bg-[#161617] border border-[#E5E7EB] dark:border-[#2D251D] hover:border-primary/40 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#25201A] border border-[#E5E7EB] dark:border-[#3D352B] flex items-center justify-center p-0.5 overflow-hidden flex-shrink-0">
                                                    <img src="{{ $application->icon_url }}"
                                                         alt="{{ $application->name }}"
                                                         class="w-full h-full object-contain rounded"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                    <svg class="w-4 h-4 fill-current text-[#1D1D1F] dark:text-white" style="display: none;" viewBox="0 0 170 170" aria-hidden="true">
                                                        <path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.7-3.04-7.58-7.7-11.64-13.98-6.19-9.56-11.02-20.73-14.5-33.51-3.48-12.79-5.22-24.96-5.22-36.52 0-16.1 4.19-29.41 12.56-39.92 8.37-10.51 18.91-15.87 31.62-16.08 4.79 0 10.15 1.34 16.08 4.02 5.93 2.68 9.77 4.07 11.52 4.17 1.48 0 5.48-1.48 12.01-4.44 6.53-2.96 12.19-4.33 16.98-4.12 12.63.63 22.84 5.37 30.63 14.21-11.09 6.74-16.53 16.14-16.32 28.19.21 9.4 3.87 17.26 10.98 23.57 7.11 6.31 15.53 9.94 25.26 10.89-2.54 7.6-5.46 15.17-8.75 22.7zM119.22 31.84c0-7.38 2.66-14.37 7.97-20.97 5.31-6.6 11.83-10.59 19.56-11.97.21 1.05.32 2.05.32 3 0 7.39-2.78 14.53-8.34 21.42-5.56 6.89-12.22 10.74-19.98 11.55-.21-1.05-.32-2.05-.32-3.03z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-xs text-[#1D1D1F] dark:text-white font-mono">{{ $ver->version }}</span>
                                                        @if($loop->first)
                                                            <span class="text-[10px] font-bold text-success bg-success/15 px-2 py-0.5 rounded-md border border-success/30">Latest</span>
                                                        @else
                                                            <span class="text-[10px] font-medium text-[#86868B] dark:text-[#A1A1A6] bg-black/5 dark:bg-white/5 px-1.5 py-0.5 rounded">Archived</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[11px] text-[#86868B] dark:text-[#A1A1A6] mt-0.5">
                                                        <span>{{ $ver->released_at ? $ver->released_at->format('M d, Y') : $application->updated_at->format('M d, Y') }}</span>
                                                        <span>&middot;</span>
                                                        <span>{{ $ver->size ?? $application->formatted_size }}</span>
                                                        <span>&middot;</span>
                                                        <span>Universal</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 self-end sm:self-center">
                                                @if($ver->download_url || $ver->is_current)
                                                    <a href="{{ route('download', ['application' => $application->slug, 'version' => $ver->id]) }}"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-primary hover:bg-primary-hover shadow-sm transition-all cursor-pointer"
                                                        title="Download {{ $application->name }} {{ $ver->version }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        <span>Download</span>
                                                    </a>
                                                @endif

                                                @if($ver->torrent_url || $ver->torrent_file_path || ($ver->is_current && $application->has_torrent))
                                                    <a href="{{ route('download', ['application' => $application->slug, 'version' => $ver->id, 'type' => 'torrent']) }}"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#30D158] bg-[#30D158]/10 hover:bg-[#30D158]/20 border border-[#30D158]/30 transition-all cursor-pointer"
                                                        title="Download Torrent {{ $application->name }} {{ $ver->version }}">
                                                        <span>🧲</span>
                                                        <span>Torrent</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl bg-[#F5F5F7] dark:bg-[#161617] border border-[#E5E7EB] dark:border-[#2D251D]">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#25201A] border border-[#E5E7EB] dark:border-[#3D352B] flex items-center justify-center p-0.5 overflow-hidden flex-shrink-0">
                                                <img src="{{ $application->icon_url }}"
                                                     alt="{{ $application->name }}"
                                                     class="w-full h-full object-contain rounded"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <svg class="w-4 h-4 fill-current text-[#1D1D1F] dark:text-white" style="display: none;" viewBox="0 0 170 170" aria-hidden="true">
                                                    <path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.7-3.04-7.58-7.7-11.64-13.98-6.19-9.56-11.02-20.73-14.5-33.51-3.48-12.79-5.22-24.96-5.22-36.52 0-16.1 4.19-29.41 12.56-39.92 8.37-10.51 18.91-15.87 31.62-16.08 4.79 0 10.15 1.34 16.08 4.02 5.93 2.68 9.77 4.07 11.52 4.17 1.48 0 5.48-1.48 12.01-4.44 6.53-2.96 12.19-4.33 16.98-4.12 12.63.63 22.84 5.37 30.63 14.21-11.09 6.74-16.53 16.14-16.32 28.19.21 9.4 3.87 17.26 10.98 23.57 7.11 6.31 15.53 9.94 25.26 10.89-2.54 7.6-5.46 15.17-8.75 22.7zM119.22 31.84c0-7.38 2.66-14.37 7.97-20.97 5.31-6.6 11.83-10.59 19.56-11.97.21 1.05.32 2.05.32 3 0 7.39-2.78 14.53-8.34 21.42-5.56 6.89-12.22 10.74-19.98 11.55-.21-1.05-.32-2.05-.32-3.03z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-xs text-[#1D1D1F] dark:text-white font-mono">{{ $application->version }}</span>
                                                    <span class="text-[10px] font-bold text-success bg-success/15 px-2 py-0.5 rounded-md border border-success/30">Latest</span>
                                                </div>
                                                <div class="flex items-center gap-2 text-[11px] text-[#86868B] dark:text-[#A1A1A6] mt-0.5">
                                                    <span>{{ $application->updated_at->format('M d, Y') }}</span>
                                                    <span>&middot;</span>
                                                    <span>{{ $application->formatted_size }}</span>
                                                    <span>&middot;</span>
                                                    <span>Universal</span>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('download', $application->slug) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-primary hover:bg-primary-hover shadow-sm transition-all self-end sm:self-center cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>Download</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>

                            @if($application->versions && $application->versions->count() > 2)
                                <div class="mt-3 text-center">
                                    <button type="button"
                                            @click="expanded = !expanded"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-primary hover:text-primary-hover bg-primary/10 hover:bg-primary/15 border border-primary/20 transition-all cursor-pointer shadow-sm">
                                        <span x-text="expanded ? 'Show fewer versions' : 'Show {{ $application->versions->count() - 2 }} more older versions'"></span>
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                             :class="expanded ? 'rotate-180' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </section>

                        <!-- Verified Mirror & Official Website Link -->
                        @if($application->download_url_external)
                        <div class="mt-4 pt-3 flex items-center justify-between text-xs text-[#8C847A]">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-success" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Verified secure servers
                            </span>
                            <a href="{{ $application->download_url_external }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline flex items-center gap-1 font-semibold">
                                <span>Official Website</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                        @endif

                        <!-- Entry Foot: Tags & Share -->
                        <div class="entry-foot">
                            <div class="entry-tags">
                                @if($application->tags && $application->tags->count() > 0)
                                    @foreach($application->tags as $t)
                                        <a href="{{ route('search', ['q' => $t->name]) }}" rel="tag">{{ $t->name }}</a>
                                    @endforeach
                                @else
                                    <a href="{{ route('search', ['q' => 'Apple Silicon']) }}" rel="tag">Apple Silicon</a>
                                    <a href="{{ route('search', ['q' => 'macOS']) }}" rel="tag">macOS</a>
                                @endif
                            </div>

                            <div class="cp-share">
                                <span class="share-label">Follow us:</span>
                                @if(\App\Models\Setting::get('facebook_url'))
                                <a class="share-facebook hover:!text-[#1877F2] hover:!border-[#1877F2]/50" 
                                   href="{{ \App\Models\Setting::get('facebook_url', 'https://facebook.com') }}" 
                                   target="_blank" 
                                   rel="noopener nofollow" 
                                   aria-label="Facebook">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.32l-.53 3.5h-2.8V24C19.62 23.1 24 18.1 24 12.07"></path></svg>
                                    <span>Facebook</span>
                                </a>
                                @endif

                                @if(\App\Models\Setting::get('twitter_url'))
                                <a class="share-x hover:!text-white hover:!border-white/50" 
                                   href="{{ \App\Models\Setting::get('twitter_url', 'https://x.com') }}" 
                                   target="_blank" 
                                   rel="noopener nofollow" 
                                   aria-label="X.com">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.4l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.6l5.24 6.93zM17.6 20.64h2.04L6.49 3.24H4.3z"></path></svg>
                                    <span>X.com</span>
                                </a>
                                @endif

                                @if(\App\Models\Setting::get('instagram_url'))
                                <a class="share-instagram hover:!text-[#E1306C] hover:!border-[#E1306C]/50" 
                                   href="{{ \App\Models\Setting::get('instagram_url', 'https://instagram.com') }}" 
                                   target="_blank" 
                                   rel="noopener nofollow" 
                                   aria-label="Instagram">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    <span>Instagram</span>
                                </a>
                                @endif

                                @if(\App\Models\Setting::get('telegram_channel'))
                                <a class="share-telegram hover:!text-[#229ED9] hover:!border-[#229ED9]/50" 
                                   href="{{ \App\Models\Setting::get('telegram_channel', 'https://t.me/hackmac') }}" 
                                   target="_blank" 
                                   rel="noopener nofollow" 
                                   aria-label="Telegram">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0m5.57 8.16-1.97 9.28c-.15.66-.54.82-1.09.51l-3-2.21-1.45 1.39c-.16.16-.3.3-.6.3l.21-3.05 5.56-5.02c.24-.21-.05-.33-.37-.12l-6.87 4.33-2.96-.93c-.64-.2-.66-.64.14-.95l11.57-4.46c.54-.2 1.01.12.83.93"></path></svg>
                                    <span>Telegram</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- "More apps for you" Section -->
            <section class="related-posts" aria-label="Related posts">
                <div class="flex items-center gap-2 mb-3.5">
                    <span class="w-2.5 h-2.5 rounded-[3px] bg-primary inline-block"></span>
                    <h2 class="font-heading text-xs font-bold text-[#1C1814] dark:text-white tracking-wider uppercase">More apps for you</h2>
                </div>
                <div class="related-grid">
                    @foreach($moreApps as $relatedApp)
                    <a class="related-card" href="{{ route('app', $relatedApp->slug) }}">
                        <img width="104" height="104" src="{{ $relatedApp->icon_url }}" class="attachment-cupertino-related size-cupertino-related wp-post-image" alt="{{ $relatedApp->name }} Logo" loading="lazy" decoding="async"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-12 h-12 rounded-xl bg-primary/20 text-primary font-bold text-sm flex items-center justify-center flex-shrink-0" style="display: none;">
                            {{ strtoupper(substr($relatedApp->name, 0, 1)) }}
                        </div>
                        <span>
                            <span class="rel-title">{{ $relatedApp->name }}</span>
                            <span class="rel-meta">
                                <span class="rel-ver">{{ $relatedApp->version }}</span>
                                <span class="text-[#554D43]">&bull;</span>
                                <span class="rel-date">{{ $relatedApp->updated_at->format('M j, Y') }}</span>
                            </span>
                        </span>
                    </a>
                    @endforeach
                </div>
            </section>

            <!-- Comments Area (100% Real, Dynamic, Filterable & Threaded) -->
            <div id="comments" class="comments-area">
                <div class="comments-header">
                    <h2 class="comments-title font-heading">
                        {{ $application->approvedReviews->count() }} {{ \Illuminate\Support\Str::plural('Comment', $application->approvedReviews->count()) }}
                    </h2>
                    <div class="comment-sort" role="group" aria-label="Sort comments">
                        @php $sort = request('sort_comments', 'top'); @endphp
                        <a href="{{ request()->fullUrlWithQuery(['sort_comments' => 'top']) }}#comments" class="{{ $sort === 'top' ? 'active-sort' : '' }}">
                            Top
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['sort_comments' => 'new']) }}#comments" class="{{ $sort === 'new' ? 'active-sort' : '' }}">
                            New
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['sort_comments' => 'trending']) }}#comments" class="{{ $sort === 'trending' ? 'active-sort' : '' }}">
                            Trending
                        </a>
                    </div>
                </div>

                @if(session('review_success'))
                <div class="p-3.5 mb-5 rounded-xl bg-success/15 border border-success/30 text-success text-xs flex items-center gap-2 shadow-lg">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('review_success') }}</span>
                </div>
                @endif

                <!-- Leave a Reply Form -->
                <div id="respond" class="comment-respond">
                    <h3 id="reply-title" class="comment-reply-title font-heading">
                        Leave a reply
                    </h3>
                    <form action="{{ route('app.review', $application->slug) }}" method="post" id="commentform" class="comment-form"
                          x-data="{ 
                              ratingVal: {{ $userRating ?? (session('rated_app_' . $application->id) ?? 0) }},
                              hoverVal: 0 
                          }"
                          @select-form-rating.window="ratingVal = $event.detail; window.dispatchEvent(new CustomEvent('rating-updated', { detail: ratingVal }))">
                        @csrf
                        <input type="hidden" name="rating" :value="ratingVal > 0 ? ratingVal : 5">
                        <p class="comment-notes">
                            <span id="email-notes">Your email address will not be published.</span>
                            <span class="required-field-message">Required fields are marked <span class="text-primary font-bold">*</span></span>
                        </p>
                        <p class="comment-form-comment">
                            <label for="comment">Comment <span class="text-primary">*</span></label>
                            <textarea id="comment" name="comment" rows="3" maxlength="65525" required="" placeholder="Share whether the build worked, on which Mac, and on which macOS.">{{ old('comment') }}</textarea>
                            @error('comment')<span class="text-danger text-xs mt-1 block">{{ $message }}</span>@enderror
                        </p>
                        <div class="cp-field-row">
                            <p class="comment-form-author">
                                <label for="author_name">Name <span class="text-primary">*</span></label>
                                <input id="author_name" name="author_name" type="text" value="{{ old('author_name', auth()->user()->name ?? '') }}" size="30" maxlength="245" autocomplete="name" required="">
                                @error('author_name')<span class="text-danger text-xs mt-1 block">{{ $message }}</span>@enderror
                            </p>
                            <p class="comment-form-email">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email ?? '') }}" size="30" maxlength="100" autocomplete="email">
                            </p>
                        </div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs text-[#8C847A] font-semibold">Your rating:</span>
                            <div class="flex items-center gap-1 text-base" @mouseleave="hoverVal = 0">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button" 
                                            @click="ratingVal = star; window.dispatchEvent(new CustomEvent('rating-updated', { detail: star }))" 
                                            @mouseenter="hoverVal = star"
                                            class="hover:scale-125 transition-transform cursor-pointer focus:outline-none text-lg px-0.5"
                                            :class="(hoverVal > 0 ? star <= hoverVal : (ratingVal > 0 && star <= ratingVal)) ? 'text-warning !text-[#FBBF24]' : 'text-[#5A5044] dark:text-[#484036] text-[#D1C9BE]'">
                                        ★
                                    </button>
                                </template>
                                <span class="text-xs text-[#8C847A] ml-1.5" x-text="ratingVal > 0 ? (ratingVal + ' out of 5 stars') : 'Select a rating'"></span>
                            </div>
                        </div>
                        <p class="form-submit">
                            <input name="submit" type="submit" id="submit" class="submit" value="Post comment">
                            <span class="cp-form-note">Comments are moderated before they appear.</span>
                        </p>
                    </form>
                </div>

                <!-- Comment List -->
                <ol class="comment-list">
                    @forelse($application->rootApprovedReviews as $review)
                    @php
                        $colorIndex = abs(crc32($review->author_name)) % count($avatarColors);
                        $avColor = $avatarColors[$colorIndex];
                    @endphp
                    <li id="comment-{{ $review->id }}" class="comment depth-1"
                        x-data="{
                            replyOpen: false,
                            upvotes: {{ $review->upvotes ?? 0 }},
                            downvotes: {{ $review->downvotes ?? 0 }},
                            userVote: '{{ session('voted_review_' . $review->id) }}',
                            voting: false,
                            async vote(type) {
                                if (this.voting) return;
                                this.voting = true;
                                try {
                                    const res = await fetch('{{ route('review.vote', $review->id) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ type })
                                    });
                                    if (res.ok) {
                                        const data = await res.json();
                                        this.upvotes = data.upvotes;
                                        this.downvotes = data.downvotes;
                                        this.userVote = data.user_vote;
                                    }
                                } catch (e) {
                                    console.error(e);
                                } finally {
                                    this.voting = false;
                                }
                            }
                        }">
                        <article class="comment-body" id="div-comment-{{ $review->id }}">
                            <header class="comment-meta">
                                <span class="comment-author vcard">
                                    <span class="avatar cp-avatar" aria-hidden="true" style="--av-a:{{ $avColor[0] }};--av-b:{{ $avColor[1] }};width:36px;height:36px;font-size:12px">
                                        {{ strtoupper(substr($review->author_name, 0, 2)) }}
                                    </span>
                                    <span class="fn">{{ $review->author_name }}</span>
                                    @if($review->rating > 0)
                                        <span class="text-xs text-warning ml-2 font-bold">★ {{ $review->rating }}</span>
                                    @endif
                                </span>
                                <span class="comment-metadata">
                                    <time datetime="{{ $review->created_at->toIso8601String() }}" title="{{ $review->created_at->format('M j, Y') }}">
                                        {{ $review->created_at->diffForHumans() }}
                                    </time>
                                </span>
                            </header>
                            <div class="comment-content">
                                <p>{{ $review->comment }}</p>
                            </div>
                            <div class="comment-actions">
                                <button type="button" class="cp-vote cp-vote-like" @click="vote('up')" :class="{ 'text-primary font-bold': userVote === 'up' }">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M7 22V11M3 13v7a2 2 0 0 0 2 2h11.3a2 2 0 0 0 2-1.7l1.4-7a2 2 0 0 0-2-2.3H14V5a3 3 0 0 0-3-3l-4 9"></path>
                                    </svg>
                                    <span class="cp-like-count" x-text="upvotes"></span>
                                </button>
                                <button type="button" class="cp-vote cp-vote-dislike" @click="vote('down')" :class="{ 'text-danger font-bold': userVote === 'down' }">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M17 2v11m4-9v7a2 2 0 0 1-2 2h-3v6a3 3 0 0 1-3 3l-4-9H5a2 2 0 0 1-2-2.3l1.4-7a2 2 0 0 1 2-1.7H17"></path>
                                    </svg>
                                    <span class="cp-dislike-count" x-text="downvotes"></span>
                                </button>
                                <span class="reply">
                                    <a class="comment-reply-link cursor-pointer" @click="replyOpen = !replyOpen">Reply</a>
                                </span>
                            </div>

                            <!-- Inline Reply Box -->
                            <div x-show="replyOpen" x-cloak class="mt-3 pt-3 border-t border-[rgba(255,244,224,0.08)]">
                                <form action="{{ route('app.review', $application->slug) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $review->id }}">
                                    <input type="hidden" name="rating" value="5">
                                    <textarea name="comment" rows="2" required placeholder="Reply to {{ $review->author_name }}..."
                                              class="w-full bg-[#13110E] border border-[rgba(255,244,224,0.09)] rounded-xl p-2.5 text-xs text-white placeholder-[#6B645C] focus:outline-none resize-none"></textarea>
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="author_name" value="{{ auth()->user()->name ?? '' }}" required placeholder="Your name"
                                               class="bg-[#13110E] border border-[rgba(255,244,224,0.09)] rounded-xl px-3 py-1.5 text-xs text-white placeholder-[#6B645C] focus:outline-none">
                                        <button type="submit" class="bg-primary hover:bg-[#ea580c] text-white text-xs font-bold px-4 py-1.5 rounded-full transition-colors cursor-pointer">
                                            Send reply
                                        </button>
                                        <button type="button" @click="replyOpen = false" class="text-xs text-[#7E776F] hover:text-white transition-colors cursor-pointer px-2">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </article>

                        <!-- Nested Replies -->
                        @if($review->approvedReplies->count() > 0)
                        <ol class="children">
                            @foreach($review->approvedReplies as $reply)
                            @php
                                $rColorIndex = abs(crc32($reply->author_name)) % count($avatarColors);
                                $rColor = $avatarColors[$rColorIndex];
                            @endphp
                            <li id="comment-{{ $reply->id }}" class="comment depth-2"
                                x-data="{
                                    upvotes: {{ $reply->upvotes ?? 0 }},
                                    downvotes: {{ $reply->downvotes ?? 0 }},
                                    userVote: '{{ session('voted_review_' . $reply->id) }}',
                                    voting: false,
                                    async vote(type) {
                                        if (this.voting) return;
                                        this.voting = true;
                                        try {
                                            const res = await fetch('{{ route('review.vote', $reply->id) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ type })
                                            });
                                            if (res.ok) {
                                                const data = await res.json();
                                                this.upvotes = data.upvotes;
                                                this.downvotes = data.downvotes;
                                                this.userVote = data.user_vote;
                                            }
                                        } catch (e) {
                                            console.error(e);
                                        } finally {
                                            this.voting = false;
                                        }
                                    }
                                }">
                                <article class="comment-body" id="div-comment-{{ $reply->id }}">
                                    <header class="comment-meta">
                                        <span class="comment-author vcard">
                                            <span class="avatar cp-avatar" aria-hidden="true" style="--av-a:{{ $rColor[0] }};--av-b:{{ $rColor[1] }};width:32px;height:32px;font-size:11px">
                                                {{ strtoupper(substr($reply->author_name, 0, 2)) }}
                                            </span>
                                            <span class="fn">{{ $reply->author_name }}</span>
                                        </span>
                                        <span class="comment-metadata">
                                            <time datetime="{{ $reply->created_at->toIso8601String() }}">
                                                {{ $reply->created_at->diffForHumans() }}
                                            </time>
                                        </span>
                                    </header>
                                    <div class="comment-content">
                                        <p>{{ $reply->comment }}</p>
                                    </div>
                                    <div class="comment-actions">
                                        <button type="button" class="cp-vote cp-vote-like" @click="vote('up')" :class="{ 'text-primary font-bold': userVote === 'up' }">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M7 22V11M3 13v7a2 2 0 0 0 2 2h11.3a2 2 0 0 0 2-1.7l1.4-7a2 2 0 0 0-2-2.3H14V5a3 3 0 0 0-3-3l-4 9"></path>
                                            </svg>
                                            <span class="cp-like-count" x-text="upvotes"></span>
                                        </button>
                                        <button type="button" class="cp-vote cp-vote-dislike" @click="vote('down')" :class="{ 'text-danger font-bold': userVote === 'down' }">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M17 2v11m4-9v7a2 2 0 0 1-2 2h-3v6a3 3 0 0 1-3 3l-4-9H5a2 2 0 0 1-2-2.3l1.4-7a2 2 0 0 1 2-1.7H17"></path>
                                            </svg>
                                            <span class="cp-dislike-count" x-text="downvotes"></span>
                                        </button>
                                    </div>
                                </article>
                            </li>
                            @endforeach
                        </ol>
                        @endif
                    </li>
                    @empty
                    <li class="p-8 rounded-2xl bg-[#181512] border border-dashed border-[rgba(255,244,224,0.08)] text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#221E19] text-[#8C847A] flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-white">No comments yet</h4>
                        <p class="text-xs text-[#8C847A] max-w-sm mx-auto leading-relaxed">
                            Share whether this build worked on your Mac, on which macOS version, and your tips.
                        </p>
                    </li>
                    @endforelse
                </ol>
            </div>

            <!-- Similar Alternatives / Recommended Apps Section (SEO & Dwell Time Booster) -->
            @if(isset($moreApps) && $moreApps->count() > 0)
            <div class="single-window mt-6">
                <div class="window-titlebar flex items-center justify-between px-4 py-3 border-b border-[#2D251D] dark:border-[#2D251D]">
                    <div class="flex items-center gap-2">
                        <span class="traffic-lights" aria-hidden="true">
                            <span class="tl-red"></span>
                            <span class="tl-yellow"></span>
                            <span class="tl-green"></span>
                        </span>
                        <h3 class="font-heading text-xs font-bold text-white tracking-wider uppercase ml-1">
                            Similar Alternatives to {{ $application->name }}
                        </h3>
                    </div>
                    <a href="{{ route('category', $application->category->slug) }}" class="text-[11px] text-primary hover:underline font-semibold">
                        View All in {{ $application->category->name }} →
                    </a>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($moreApps->take(6) as $altApp)
                        <a href="{{ route('app', $altApp->slug) }}" class="p-3 rounded-2xl bg-[#14110E] hover:bg-[#1C1814] border border-[#2D251D] hover:border-primary/40 transition-all flex items-center gap-3 group shadow-sm">
                            <div class="w-12 h-12 rounded-xl bg-[#1C1814] border border-[#2D251D] p-1 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                <img src="{{ $altApp->icon_url }}" alt="{{ $altApp->name }}" class="w-full h-full object-contain rounded-lg" loading="lazy"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full bg-primary/20 rounded-lg flex items-center justify-center text-primary font-bold text-sm" style="display: none;">
                                    {{ strtoupper(substr($altApp->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-heading text-xs font-bold text-white truncate group-hover:text-primary transition-colors">
                                    {{ $altApp->name }}
                                </h4>
                                <div class="flex items-center gap-1.5 mt-1 text-[10px] text-[#8C847A]">
                                    <span class="font-mono text-primary font-semibold">v{{ $altApp->version }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $altApp->formatted_downloads }} dl</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </article>

        <!-- Sidebar Column (320px in desktop grid) -->
        <aside class="sidebar min-w-0">
            <!-- Top Posts Widget -->
            <div class="single-window">
                <div class="window-titlebar flex items-center gap-3 px-4 py-3 border-b border-[#E5E7EB] dark:border-[#333336]">
                    <span class="traffic-lights" aria-hidden="true">
                        <span class="tl-red"></span>
                        <span class="tl-yellow"></span>
                        <span class="tl-green"></span>
                    </span>
                    <h3 class="font-heading text-xs font-semibold text-[#1D1D1F] dark:text-[#A1A1A6] tracking-wide">Top Posts</h3>
                </div>
                <div class="divide-y divide-[#E5E7EB] dark:divide-[#333336] px-3">
                    @foreach($topPosts as $topApp)
                    <a href="{{ route('app', $topApp->slug) }}" class="flex items-center gap-3.5 py-3 px-1 rounded-xl hover:bg-[#F5F5F7] dark:hover:bg-[#2C2C2F] transition-colors group">
                        <!-- Number Rank Badge -->
                        <span class="w-6 h-6 rounded-md {{ $loop->iteration <= 3 ? 'bg-[#0071E3] text-white font-extrabold shadow-sm' : 'bg-[#F5F5F7] dark:bg-[#242426] text-[#6B7280] dark:text-[#A1A1A6] font-bold border border-[#E5E7EB] dark:border-[#333336]' }} text-[11px] flex items-center justify-center flex-shrink-0">
                            {{ $loop->iteration }}
                        </span>

                        <!-- App Icon (48px squircle) -->
                        <div class="w-12 h-12 rounded-xl bg-[#F5F5F7] dark:bg-[#161617] border border-[#E5E7EB] dark:border-[#333336] p-1 flex-shrink-0 overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
                            <img src="{{ $topApp->icon_url }}" alt="{{ $topApp->name }}" class="w-full h-full object-cover rounded-lg"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full bg-[#0071E3]/20 rounded-lg flex items-center justify-center text-[#0071E3] font-bold text-sm" style="display: none;">
                                {{ strtoupper(substr($topApp->name, 0, 1)) }}
                            </div>
                        </div>

                        <!-- App Details -->
                        <div class="min-w-0 flex-1">
                            <p class="text-[13.5px] font-bold text-[#1D1D1F] dark:text-white truncate leading-tight group-hover:text-[#0071E3] transition-colors">
                                {{ $topApp->name }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded-full bg-[#0071E3]/10 text-[#4D9BE9] border border-[#0071E3]/30 text-[10.5px] font-mono font-medium leading-none">
                                    {{ $topApp->version }}
                                </span>
                                <span class="text-[11px] text-[#6B7280] dark:text-[#A1A1A6]">
                                    {{ $topApp->updated_at->format('M j, Y') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-[#FEBC2E] mt-1 font-semibold">
                                <span>★</span>
                                <span class="font-bold">{{ number_format($topApp->rating > 0 ? $topApp->rating : 4.4, 1) }}</span>
                                <span class="text-[#6B7280] dark:text-[#A1A1A6] font-normal">({{ $topApp->reviews_count > 0 ? $topApp->reviews_count : 25 }})</span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Recent Posts Widget -->
            <div class="single-window">
                <div class="window-titlebar flex items-center gap-3 px-4 py-3 border-b border-[#E5E7EB] dark:border-[#333336]">
                    <span class="traffic-lights" aria-hidden="true">
                        <span class="tl-red"></span>
                        <span class="tl-yellow"></span>
                        <span class="tl-green"></span>
                    </span>
                    <h3 class="font-heading text-xs font-semibold text-[#1D1D1F] dark:text-[#A1A1A6] tracking-wide">Recent Posts</h3>
                </div>
                <div class="divide-y divide-[#E5E7EB] dark:divide-[#333336] px-3">
                    @foreach($recentPosts as $recentApp)
                    <a href="{{ route('app', $recentApp->slug) }}" class="flex items-center gap-3.5 py-3 px-1 rounded-xl hover:bg-[#F5F5F7] dark:hover:bg-[#2C2C2F] transition-colors group">
                        <!-- App Icon (48px squircle) -->
                        <div class="w-12 h-12 rounded-xl bg-[#F5F5F7] dark:bg-[#161617] border border-[#E5E7EB] dark:border-[#333336] p-1 flex-shrink-0 overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
                            <img src="{{ $recentApp->icon_url }}" alt="{{ $recentApp->name }}" class="w-full h-full object-cover rounded-lg"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full bg-[#0071E3]/20 rounded-lg flex items-center justify-center text-[#0071E3] font-bold text-sm" style="display: none;">
                                {{ strtoupper(substr($recentApp->name, 0, 1)) }}
                            </div>
                        </div>

                        <!-- App Details -->
                        <div class="min-w-0 flex-1">
                            <p class="text-[13.5px] font-bold text-[#1D1D1F] dark:text-white truncate leading-tight group-hover:text-[#0071E3] transition-colors">
                                {{ $recentApp->name }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded-full bg-[#0071E3]/10 text-[#4D9BE9] border border-[#0071E3]/30 text-[10.5px] font-mono font-medium leading-none">
                                    {{ $recentApp->version }}
                                </span>
                                <span class="text-[11px] text-[#6B7280] dark:text-[#A1A1A6]">
                                    {{ $recentApp->updated_at->format('M j, Y') }}
                                </span>
                            </div>
                            @if($recentApp->rating > 0)
                            <div class="flex items-center gap-1 text-[11px] text-[#FEBC2E] mt-1 font-semibold">
                                <span>★</span>
                                <span class="font-bold">{{ number_format($recentApp->rating, 1) }}</span>
                            </div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

        </aside>

    </div>

    <!-- Modal for Reporting Broken Link -->
    <x-report-modal :application="$application" />
</x-app-layout>
