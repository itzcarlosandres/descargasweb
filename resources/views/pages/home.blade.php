<x-app-layout>
    @section('title', setting('seo_meta_title', 'Discover the best free Mac apps & games - ' . config('app.name')))
    @section('description', setting('seo_meta_description', 'Curated, fast and safe downloads for macOS — updated daily.'))
    @section('og_image', asset('images/og-share.jpg'))
    @section('canonical', route('home'))

    @section('schema')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "{{ setting('site_name', 'HackMac') }}{{ setting('site_name_highlight', '.cc') }}",
        "url": "{{ route('home') }}",
        "description": "{{ addslashes(setting('seo_meta_description', 'Curated, fast and safe downloads for macOS — updated daily.')) }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ route('search') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "{{ setting('site_name', 'HackMac') }}{{ setting('site_name_highlight', '.cc') }}",
        "url": "{{ route('home') }}",
        "logo": "{{ asset('favicon.svg') }}",
        "sameAs": {!! json_encode(array_values(array_filter([setting('twitter_url'), setting('telegram_channel')]))) !!}
    }
    </script>
    @endsection

    <div class="container-app pt-3 sm:pt-4 pb-12">
        <section class="home-hero has-stats grid relative items-center gap-6 lg:gap-10 grid-cols-1 lg:grid-cols-[minmax(0px,_1.25fr)_minmax(0px,_1fr)] p-5 sm:p-7 lg:p-[42px] mt-0.5 mb-7 text-[16.5px] leading-relaxed text-[#ffffff] bg-[#0b0b0c] rounded-[20px] shadow-[rgba(0,_0,_0,_0.5)_0px_6px_18px_0px,_rgba(0,_0,_0,_0.46)_0px_20px_44px_0px] overflow-hidden cursor-default" role="region" aria-label="Discover the best free Mac apps &amp; games">
            <div class="hero-main">
                <h1 class="font-heading font-bricolage text-2xl sm:text-3xl lg:text-[2.25rem] font-extrabold tracking-tight" style="font-family: 'Bricolage Grotesque', sans-serif !important;">
                    {{ setting('site_tagline', 'Discover the best free Mac apps & games') }}
                </h1>
                <p class="text-sm sm:text-[15px] text-[#A1A1A6] mb-5 sm:mb-6 max-w-xl">
                    {{ setting('site_description', 'Curated, fast and safe downloads for macOS — updated daily.') }}
                </p>
                <form class="hero-search w-full max-w-full lg:max-w-[480px]" role="search" method="get" action="{{ route('search') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="search" name="s" aria-label="Search apps and games" placeholder="Search apps &amp; games…" value="{{ request('s', request('q', '')) }}">
                    <button type="submit">Search</button>
                </form>
                <div class="hero-chips flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs">
                    <span class="hero-chips-label">Popular</span>
                    <a href="{{ route('category', 'media-design') }}">Media &amp; Design</a>
                    <a href="{{ route('category', 'system-utilities') }}">System Utilities</a>
                    <a href="{{ route('category', 'games') }}">Games</a>
                    <a href="{{ route('category', 'productivity-business') }}">Productivity &amp; Business</a>
                    <a href="{{ route('popular') }}">Editor's Choice</a>
                    <a href="{{ route('category', 'mobile-tools') }}">Mobile Tools</a>
                </div>
            </div>

            @php
                $totDownloads = $stats['total_downloads'] ?? 0;
                if ($totDownloads > 0) {
                    $formattedTotal = $totDownloads >= 1000000 
                        ? number_format($totDownloads / 1000000, 1) . 'M' 
                        : ($totDownloads >= 1000 
                            ? number_format($totDownloads / 1000, 1) . 'K' 
                            : number_format($totDownloads));
                } else {
                    $formattedTotal = '533.4K';
                }
                $totalAppsReal = $stats['total_apps'] ?? \App\Models\Application::published()->count();
                $updatedWeekReal = $stats['updated_this_week'] ?? \App\Models\Application::published()->where('updated_at', '>=', now()->subWeek())->count();
            @endphp
            <div class="hero-stats grid grid-cols-3 lg:flex lg:flex-col relative z-[1] py-4 px-3 sm:py-5 sm:px-6 font-sans text-center lg:text-left text-[#ffffff] bg-[#ffffff]/[0.06] border border-[#ffffff]/10 rounded-2xl backdrop-blur-md shadow-[0_8px_32px_rgba(0,0,0,0.25)] gap-1 sm:gap-2 lg:gap-0">
                <div class="hero-stat pr-1.5 sm:pr-3 lg:pr-0 border-r border-white/10 lg:border-r-0 lg:border-b lg:border-white/10 pb-0 lg:pb-4 lg:mb-4">
                    <span class="hero-stat-v block font-mono text-lg sm:text-2xl lg:text-[28.8px] font-semibold leading-tight lg:leading-relaxed tracking-tight text-[#ffffff]">{{ number_format($totalAppsReal) }}</span>
                    <span class="hero-stat-l text-[10px] sm:text-xs lg:text-[13px] text-[#A1A1A6] uppercase tracking-wider block mt-0.5 sm:mt-1">Apps &amp; games</span>
                </div>
                <div class="hero-stat px-1.5 sm:px-3 lg:px-0 border-r border-white/10 lg:border-r-0 lg:border-b lg:border-white/10 pb-0 lg:pb-4 lg:mb-4">
                    <span class="hero-stat-v block font-mono text-lg sm:text-2xl lg:text-[28.8px] font-semibold leading-tight lg:leading-relaxed tracking-tight text-[#ffffff]">{{ $formattedTotal }}</span>
                    <span class="hero-stat-l text-[10px] sm:text-xs lg:text-[13px] text-[#A1A1A6] uppercase tracking-wider block mt-0.5 sm:mt-1">Downloads</span>
                </div>
                <div class="hero-stat pl-1.5 sm:pl-3 lg:pl-0">
                    <span class="hero-stat-v block font-mono text-lg sm:text-2xl lg:text-[28.8px] font-semibold leading-tight lg:leading-relaxed tracking-tight text-[#ffffff]">{{ number_format($updatedWeekReal) }}</span>
                    <span class="hero-stat-l text-[10px] sm:text-xs lg:text-[13px] text-[#A1A1A6] uppercase tracking-wider block mt-0.5 sm:mt-1">Updated weekly</span>
                </div>
            </div>
        </section>

        <!-- Featured Section Header & 4 Cards Grid -->
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3.5">
                <span class="w-2.5 h-2.5 rounded-[3px] bg-primary inline-block"></span>
                <h2 class="font-heading text-xs font-bold text-[#1C1814] dark:text-white tracking-wider uppercase">Featured</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4">
                @foreach($featured as $index => $app)
                    <x-featured-card :app="$app" :position="$index + 1" />
                @endforeach
            </div>
        </div>

        <!-- Latest Releases / Recently Added Section -->
        <div class="space-y-4">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-[3px] bg-primary inline-block"></span>
                    <h2 class="font-heading text-xs font-bold text-[#1C1814] dark:text-white tracking-wider uppercase">Latest Releases</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[11px] text-[#7E776F] dark:text-[#8C847A] font-medium hidden sm:inline-block">
                        Recently added &amp; updated apps
                    </span>
                    <a href="{{ route('new') }}" class="text-[11px] font-semibold text-[#7E776F] dark:text-[#8C847A] hover:text-primary transition-colors flex items-center gap-1">
                        <span>View All</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-3.5 sm:gap-4">
                @foreach($applications as $app)
                    <x-app-card :app="$app" />
                @endforeach
            </div>

            <!-- Numbered Pagination matching screenshot -->
            <x-pagination :paginator="$applications" />
        </div>
    </div>
</x-app-layout>
