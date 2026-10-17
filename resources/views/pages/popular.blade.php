<x-app-layout>
    @section('title', 'Top 25 Most Downloaded Apps - ' . config('app.name'))
    @section('description', 'Discover the 25 most downloaded Mac applications, developer tools, and games. Curated and updated for macOS.')

    <div class="container-app py-8 lg:py-12">
        <!-- Hero Header -->
        <div class="mb-10 text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold mb-3 border border-primary/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>COMMUNITY LEADERBOARD</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-heading text-[#1D1D1F] dark:text-white tracking-tight mb-3">
                Top 25 Most Downloaded
            </h1>
            <p class="text-[#515154] dark:text-[#A1A1A6] text-sm sm:text-base leading-relaxed">
                The most popular and essential macOS applications, updated dynamically according to total community downloads.
            </p>
        </div>

        @if($topApplications->isEmpty())
            <div class="p-12 text-center max-w-md mx-auto bg-white dark:bg-[#141720] border border-[#E5E7EB] dark:border-[#232734] rounded-2xl shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-white mb-1">No applications found</h3>
                <p class="text-[#86868B] dark:text-[#A1A1A6] text-sm mb-4">No published applications are available at the moment.</p>
                <a href="{{ route('home') }}" class="btn-primary inline-flex text-xs">Return Home</a>
            </div>
        @else
            <!-- TOP 3 PODIUM CARDS -->
            @if($topThree->isNotEmpty())
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <h2 class="text-lg sm:text-xl font-bold font-heading text-[#1D1D1F] dark:text-white">Top 3 Podium</h2>
                        </div>
                        <span class="text-xs text-[#86868B] dark:text-[#A1A1A6] font-medium">Rankings #1 to #3</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6">
                        @foreach($topThree as $index => $app)
                            @php
                                $rank = $loop->iteration;
                                $isFirst = $rank === 1;
                                $isSecond = $rank === 2;
                                $isThird = $rank === 3;
                            @endphp
                            <article class="relative flex flex-col justify-between rounded-2xl p-5 sm:p-6 transition-all duration-300 group bg-white dark:bg-[#141720]
                                @if($isFirst)
                                    border-2 border-amber-400 dark:border-amber-400/60 shadow-xl shadow-amber-500/10 hover:shadow-2xl hover:shadow-amber-500/20 hover:-translate-y-1
                                @elseif($isSecond)
                                    border border-slate-300 dark:border-slate-700 shadow-lg shadow-slate-500/5 hover:shadow-xl hover:-translate-y-1
                                @else
                                    border border-amber-700/30 dark:border-amber-700/50 shadow-lg shadow-amber-900/5 hover:shadow-xl hover:-translate-y-1
                                @endif">

                                <!-- Top Badges Row -->
                                <div class="flex items-center justify-between gap-2 mb-5">
                                    @if($isFirst)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-gradient-to-r from-amber-500 to-yellow-400 text-slate-950 shadow-md shadow-amber-500/30">
                                            <span>🥇</span> #1 Most Downloaded
                                        </span>
                                    @elseif($isSecond)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700">
                                            <span>🥈</span> #2 Popular
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                            <span>🥉</span> #3 Popular
                                        </span>
                                    @endif

                                    <!-- Downloads Pill -->
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-[#1D1D1F] dark:text-[#E4E4E7] bg-[#F5F5F7] dark:bg-[#1E222D] px-2.5 py-1 rounded-lg border border-[#E5E7EB] dark:border-[#2A303F]">
                                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        {{ $app->formatted_downloads }}
                                    </span>
                                </div>

                                <!-- Card Center Content -->
                                <div class="flex flex-col items-center text-center flex-1">
                                    <!-- App Icon -->
                                    <a href="{{ route('app', $app->slug) }}" class="relative w-20 h-20 sm:w-22 sm:h-22 rounded-2xl p-1 bg-[#F5F5F7] dark:bg-[#1B1F2C] border border-[#E5E7EB] dark:border-[#2B3140] shadow-md mb-4 group-hover:scale-105 transition-transform duration-300 flex items-center justify-center shrink-0">
                                        <img src="{{ $app->icon_url }}" alt="{{ $app->name }}" class="w-full h-full object-cover rounded-xl" loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full bg-primary/20 rounded-xl items-center justify-center text-primary font-bold text-2xl" style="display: none;">
                                            {{ strtoupper(substr($app->name, 0, 1)) }}
                                        </div>
                                    </a>

                                    <!-- App Title -->
                                    <h3 class="font-heading font-extrabold text-base sm:text-lg text-[#1D1D1F] dark:text-white group-hover:text-primary transition-colors line-clamp-1 w-full mb-1">
                                        <a href="{{ route('app', $app->slug) }}">{{ $app->name }}</a>
                                    </h3>

                                    <!-- Meta pills -->
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap text-xs mb-3">
                                        @if($app->category)
                                            <a href="{{ route('category', $app->category->slug) }}" class="px-2 py-0.5 rounded-md bg-[#F5F5F7] dark:bg-[#1E222D] text-[#4B5563] dark:text-[#CBD5E1] hover:text-primary transition-colors border border-[#E5E7EB]/70 dark:border-[#2A303F]">
                                                {{ $app->category->name }}
                                            </a>
                                        @endif
                                        @if($app->version)
                                            <span class="px-2 py-0.5 rounded-md bg-[#F5F5F7] dark:bg-[#1E222D] text-[#6B7280] dark:text-[#94A3B8] font-mono text-[11px] border border-[#E5E7EB]/70 dark:border-[#2A303F]">
                                                v{{ $app->version }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Size & Torrent badges -->
                                    <div class="flex items-center justify-center gap-2 text-xs text-[#6B7280] dark:text-[#94A3B8] mb-5">
                                        @if($app->formatted_size && $app->formatted_size !== 'N/A')
                                            <span class="flex items-center gap-1 font-mono text-[11.5px]">
                                                <svg class="w-3.5 h-3.5 text-[#86868B] dark:text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                                {{ $app->formatted_size }}
                                            </span>
                                        @endif

                                        @if($app->has_torrent && $app->download_url_external)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/30">DUAL</span>
                                        @elseif($app->has_torrent)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#30D158]/15 text-emerald-600 dark:text-[#30D158] border border-[#30D158]/30">TORRENT</span>
                                        @elseif($app->download_url_external || $app->download_url)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-primary/15 text-primary border border-primary/30">DDL</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <a href="{{ route('app', $app->slug) }}" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm text-white bg-primary hover:bg-primary-hover shadow-md shadow-primary/20 transition-all duration-200">
                                    <span>View & Download</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- NUMBERED LIST: #4 TO #25 -->
            @if($remainingApps->isNotEmpty())
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#86868B] dark:text-[#A1A1A6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <h2 class="text-lg sm:text-xl font-bold font-heading text-[#1D1D1F] dark:text-white">Leaderboard #4 – #25</h2>
                        </div>
                        <span class="text-xs text-[#86868B] dark:text-[#A1A1A6] font-medium">{{ $remainingApps->count() }} applications</span>
                    </div>

                    <!-- List Container -->
                    <div class="bg-white dark:bg-[#141720] border border-[#E5E7EB] dark:border-[#232734] rounded-2xl shadow-sm divide-y divide-[#E5E7EB] dark:divide-[#232734] overflow-hidden">
                        @foreach($remainingApps as $app)
                            @php
                                $rankNumber = $loop->iteration + 3;
                            @endphp
                            <div class="p-3.5 sm:p-4.5 flex items-center gap-3 sm:gap-4 hover:bg-[#F8FAFC] dark:hover:bg-[#1B1F2C] transition-colors group">
                                <!-- Rank Position -->
                                <div class="w-8 sm:w-10 flex-shrink-0 text-center">
                                    <span class="font-mono font-black text-sm sm:text-base text-[#6B7280] dark:text-[#94A3B8] group-hover:text-primary transition-colors">
                                        #{{ $rankNumber }}
                                    </span>
                                </div>

                                <!-- App Icon -->
                                <a href="{{ route('app', $app->slug) }}" class="relative w-12 h-12 sm:w-14 sm:h-14 rounded-xl p-1 bg-[#F5F5F7] dark:bg-[#161922] border border-[#E5E7EB] dark:border-[#282E3E] shadow-xs flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <img src="{{ $app->icon_url }}" alt="{{ $app->name }}" class="w-full h-full object-cover rounded-lg" loading="lazy"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full bg-primary/20 rounded-lg items-center justify-center text-primary font-bold text-lg" style="display: none;">
                                        {{ strtoupper(substr($app->name, 0, 1)) }}
                                    </div>
                                </a>

                                <!-- App Details -->
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-heading font-bold text-sm sm:text-[15px] text-[#1D1D1F] dark:text-white group-hover:text-primary transition-colors truncate">
                                            <a href="{{ route('app', $app->slug) }}">{{ $app->name }}</a>
                                        </h3>
                                        @if($app->version)
                                            <span class="text-[11px] font-mono text-[#6B7280] dark:text-[#94A3B8] bg-[#F5F5F7] dark:bg-[#1E222D] border border-[#E5E7EB]/70 dark:border-[#2A303F] px-1.5 py-0.5 rounded">
                                                v{{ $app->version }}
                                            </span>
                                        @endif
                                        @if($app->has_torrent && $app->download_url_external)
                                            <span class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-mono font-bold bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/30">DUAL</span>
                                        @elseif($app->has_torrent)
                                            <span class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-mono font-bold bg-[#30D158]/15 text-emerald-600 dark:text-[#30D158] border border-[#30D158]/30">TORRENT</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3 text-xs text-[#6B7280] dark:text-[#94A3B8] mt-1">
                                        @if($app->category)
                                            <a href="{{ route('category', $app->category->slug) }}" class="hover:text-primary transition-colors text-[#4B5563] dark:text-[#CBD5E1] truncate max-w-[140px] sm:max-w-none">
                                                {{ $app->category->name }}
                                            </a>
                                        @endif

                                        @if($app->formatted_size && $app->formatted_size !== 'N/A')
                                            <span class="hidden sm:inline-flex items-center gap-1 font-mono text-[11px]">
                                                <span>•</span> {{ $app->formatted_size }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Downloads Stat -->
                                <div class="hidden sm:flex flex-col items-end text-right px-2 shrink-0">
                                    <span class="flex items-center gap-1 text-xs font-bold text-[#1D1D1F] dark:text-[#E4E4E7]">
                                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        {{ $app->formatted_downloads }}
                                    </span>
                                    <span class="text-[10px] text-[#86868B] dark:text-[#94A3B8] uppercase tracking-wider font-semibold">downloads</span>
                                </div>

                                <!-- Action Button -->
                                <div class="shrink-0 pl-1">
                                    <a href="{{ route('app', $app->slug) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-semibold bg-[#F5F5F7] dark:bg-[#1E222D] hover:bg-primary hover:text-white dark:hover:bg-primary text-[#1D1D1F] dark:text-[#F6F3EC] transition-all duration-200 group/btn border border-[#E5E7EB] dark:border-[#2C3347] hover:border-primary">
                                        <span>Get</span>
                                        <svg class="w-3 h-3 transition-transform group-hover/btn:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
