<header class="site-header is-sticky" role="banner" x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="container-app header-inner flex items-center justify-between h-[65px]">
        <!-- Left Section: Traffic Lights & Branding -->
        <div class="flex items-center gap-4 sm:gap-5">
            <!-- macOS Window Controls (Traffic Lights) -->
            <span class="traffic-lights flex items-center gap-[7px] select-none" aria-hidden="true">
                <span class="tl-red w-[11px] h-[11px] rounded-full bg-[#FF5F57] inline-block shadow-sm"></span>
                <span class="tl-yellow w-[11px] h-[11px] rounded-full bg-[#FEBC2E] inline-block shadow-sm"></span>
                <span class="tl-green w-[11px] h-[11px] rounded-full bg-[#28C840] inline-block shadow-sm"></span>
            </span>

            <!-- Site Branding: Finder Icon + HackMac.cc Logo -->
            <div class="site-branding">
                @php
                    $navLogoSize = (int) setting('logo_size', 24);
                    $navIconSize = max(20, $navLogoSize + 8);
                @endphp
                <a class="custom-logo-link logo-dual flex items-center gap-2.5 text-[#1C1814] dark:text-white font-bold tracking-tight hover:opacity-95 transition-opacity"
                   href="{{ route('home') }}"
                   rel="home">
                    @if(setting('logo_type') === 'image' && setting('logo_image'))
                        <img src="{{ setting('logo_image') }}" alt="{{ setting('site_name', 'HackMac') }}{{ setting('site_name_highlight', '.cc') }}" style="height: {{ $navLogoSize + 10 }}px;" class="max-h-[52px] max-w-[220px] object-contain flex-shrink-0">
                    @else
                        <div style="width: {{ $navIconSize }}px; height: {{ $navIconSize }}px;" class="flex-shrink-0 flex items-center justify-center">
                            <x-logo-icon :icon="setting('logo_icon', 'finder')" class="w-full h-full" />
                        </div>

                        <span style="font-size: {{ $navLogoSize }}px; line-height: 1;" class="text-[#1C1814] dark:text-white font-black tracking-[-0.03em] font-sans inline-flex items-center">
                            {{ setting('site_name', 'HackMac') }}<span class="text-primary font-black">{{ setting('site_name_highlight', '.cc') }}</span>
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Center Navigation Menu -->
        <nav class="main-nav hidden lg:flex items-center" aria-label="Top menu">
            <ul id="menu-main-menu" class="menu flex items-center gap-6 xl:gap-7 text-[14.5px] font-medium text-[#38322B] dark:text-[#F6F3EC]">
                <!-- Editor's Choice -->
                <li id="menu-item-6907" class="menu-item">
                    <a href="{{ route('popular') }}"
                       class="hover:text-[#0071E3] dark:hover:text-white transition-colors cursor-pointer {{ request()->routeIs('popular') ? 'text-[#0071E3] dark:text-white font-semibold' : 'text-[#38322B]/90 dark:text-[#F6F3EC]/90' }}">
                        Editor’s Choice
                    </a>
                </li>

                <!-- Application Dropdown -->
                <li id="menu-item-33" class="menu-item menu-item-has-children relative group" x-data="{ open: false }" @mouseleave="open = false">
                    <a href="{{ route('categories') }}"
                       @mouseenter="open = true"
                       @click.prevent="open = !open"
                       class="hover:text-[#0071E3] dark:hover:text-white transition-colors flex items-center gap-1.5 py-2 cursor-pointer focus:outline-none {{ request()->routeIs('categories') || request()->routeIs('category') ? 'text-[#0071E3] dark:text-white font-semibold' : 'text-[#38322B]/90 dark:text-[#F6F3EC]/90' }}"
                       aria-expanded="false"
                       :aria-expanded="open">
                        <span>Application</span>
                        <svg class="w-3 h-3 text-[#38322B]/60 dark:text-[#F6F3EC]/60 group-hover:text-[#0071E3] dark:group-hover:text-white transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>

                    <!-- Sub Menu -->
                    <ul class="sub-menu absolute left-0 top-full w-64 bg-white dark:bg-[#16171A] backdrop-blur-xl border border-[#E5E7EB] dark:border-[#27272A] rounded-2xl shadow-2xl p-1.5 z-50 space-y-0.5"
                        x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1">

                        <li id="menu-item-3070">
                            <a href="{{ route('category', 'developer-tools') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors group/item">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-[#0071E3] flex-shrink-0">
                                    <x-category-icon icon="code" class="w-3.5 h-3.5" />
                                </div>
                                <span class="flex-1 truncate">Developer Tools</span>
                            </a>
                        </li>
                        <li id="menu-item-34">
                            <a href="{{ route('category', 'media-design') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors group/item">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-[#0071E3] flex-shrink-0">
                                    <x-category-icon icon="palette" class="w-3.5 h-3.5" />
                                </div>
                                <span class="flex-1 truncate">Media &amp; Design</span>
                            </a>
                        </li>
                        <li id="menu-item-7860">
                            <a href="{{ route('category', 'lifestyle-everyday') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors group/item">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-[#0071E3] flex-shrink-0">
                                    <x-category-icon icon="app" class="w-3.5 h-3.5" />
                                </div>
                                <span class="flex-1 truncate">Lifestyle &amp; Everyday</span>
                            </a>
                        </li>
                        <li id="menu-item-35">
                            <a href="{{ route('category', 'mobile-tools') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors group/item">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-[#0071E3] flex-shrink-0">
                                    <x-category-icon icon="globe" class="w-3.5 h-3.5" />
                                </div>
                                <span class="flex-1 truncate">Mobile Tools</span>
                            </a>
                        </li>
                        <li id="menu-item-2722">
                            <a href="{{ route('category', 'productivity-business') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors group/item">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-[#0071E3] flex-shrink-0">
                                    <x-category-icon icon="briefcase" class="w-3.5 h-3.5" />
                                </div>
                                <span class="flex-1 truncate">Productivity &amp; Business</span>
                            </a>
                        </li>
                        <li id="menu-item-38">
                            <a href="{{ route('category', 'system-utilities') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors group/item">
                                <div class="w-5 h-5 rounded flex items-center justify-center text-[#0071E3] flex-shrink-0">
                                    <x-category-icon icon="wrench" class="w-3.5 h-3.5" />
                                </div>
                                <span class="flex-1 truncate">System Utilities</span>
                            </a>
                        </li>
                        <li class="border-t border-[#E5E7EB] dark:border-[#27272A] my-1"></li>
                        <li>
                            <a href="{{ route('categories') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-[13px] text-[#0071E3] dark:text-[#38BDF8] font-semibold hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] transition-colors">
                                <span>All Applications &rarr;</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Adobe Collection Dropdown -->
                <li id="menu-item-7250" class="menu-item menu-item-has-children relative group" x-data="{ open: false }" @mouseleave="open = false">
                    <a href="{{ route('search', ['q' => 'Adobe']) }}"
                       @mouseenter="open = true"
                       @click.prevent="open = !open"
                       class="hover:text-[#0071E3] dark:hover:text-white transition-colors flex items-center gap-1.5 py-2 cursor-pointer focus:outline-none"
                       :class="open ? 'text-[#0071E3] dark:text-white' : 'text-[#38322B]/90 dark:text-[#F6F3EC]/90'"
                       aria-expanded="false"
                       :aria-expanded="open">
                        <span>Adobe Collection</span>
                        <svg class="w-3 h-3 text-[#38322B]/60 dark:text-[#F6F3EC]/60 group-hover:text-[#0071E3] dark:group-hover:text-white transition-transform" :class="open ? 'rotate-180 text-[#0071E3] dark:text-white' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>

                    <!-- Sub Menu -->
                    <ul class="sub-menu absolute left-0 top-full w-60 bg-white dark:bg-[#16171A] backdrop-blur-xl border border-[#E5E7EB] dark:border-[#27272A] rounded-2xl shadow-2xl p-1.5 z-50 space-y-0.5"
                        x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1">
                        <li id="menu-item-31084">
                            <a href="{{ route('search', ['q' => 'Photoshop']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>Adobe Photoshop</span>
                            </a>
                        </li>
                        <li id="menu-item-31085">
                            <a href="{{ route('search', ['q' => 'Lightroom']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>Adobe Lightroom</span>
                            </a>
                        </li>
                        <li id="menu-item-31086">
                            <a href="{{ route('search', ['q' => 'Premiere']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>Adobe Premiere Pro</span>
                            </a>
                        </li>
                        <li id="menu-item-31087">
                            <a href="{{ route('search', ['q' => 'Illustrator']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>Adobe Illustrator</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Games Dropdown -->
                <li id="menu-item-39" class="menu-item menu-item-has-children relative group" x-data="{ open: false }" @mouseleave="open = false">
                    <a href="{{ route('category', 'games') }}"
                       @mouseenter="open = true"
                       @click.prevent="open = !open"
                       class="hover:text-[#0071E3] dark:hover:text-white transition-colors flex items-center gap-1.5 py-2 cursor-pointer focus:outline-none {{ request()->is('category/games') ? 'text-[#0071E3] dark:text-white font-semibold' : 'text-[#38322B]/90 dark:text-[#F6F3EC]/90' }}"
                       :class="open ? 'text-[#0071E3] dark:text-white' : ''"
                       aria-expanded="false"
                       :aria-expanded="open">
                        <span>Games</span>
                        <svg class="w-3 h-3 text-[#38322B]/60 dark:text-[#F6F3EC]/60 group-hover:text-[#0071E3] dark:group-hover:text-white transition-transform" :class="open ? 'rotate-180 text-[#0071E3] dark:text-white' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>

                    <!-- Sub Menu -->
                    <ul class="sub-menu absolute left-0 top-full w-60 bg-white dark:bg-[#16171A] backdrop-blur-xl border border-[#E5E7EB] dark:border-[#27272A] rounded-2xl shadow-2xl p-1.5 z-50 space-y-0.5"
                        x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1">
                        <li id="menu-item-22102">
                            <a href="{{ route('search', ['q' => 'Apple Arcade']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>Apple Arcade</span>
                            </a>
                        </li>
                        <li id="menu-item-47013">
                            <a href="{{ route('search', ['q' => 'Assassin']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>Assassin’s Creed</span>
                            </a>
                        </li>
                        <li id="menu-item-48402">
                            <a href="{{ route('search', ['q' => 'GTA']) }}" class="flex items-center justify-between px-3.5 py-2 rounded-lg text-[13.5px] font-medium hover:bg-[#F3F4F6] dark:hover:bg-[#1E293B] text-[#374151] dark:text-[#E4E4E7] hover:text-[#0071E3] dark:hover:text-white transition-colors">
                                <span>GTA: Grand Theft Auto</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Disable SIP -->
                <li id="menu-item-7167" class="menu-item">
                    <a href="{{ route('guide.sip') }}"
                       class="hover:text-[#0071E3] dark:hover:text-white transition-colors cursor-pointer {{ request()->routeIs('guide.sip') ? 'text-[#0071E3] dark:text-white font-semibold' : 'text-[#38322B]/90 dark:text-[#F6F3EC]/90' }}">
                        Disable SIP
                    </a>
                </li>

                <!-- Fix Damaged Apps -->
                <li id="menu-item-7168" class="menu-item">
                    <a href="{{ route('guide.fix') }}"
                       class="hover:text-[#0071E3] dark:hover:text-white transition-colors cursor-pointer {{ request()->routeIs('guide.fix') ? 'text-[#0071E3] dark:text-white font-semibold' : 'text-[#38322B]/90 dark:text-[#F6F3EC]/90' }}">
                        Fix Damaged Apps
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Right Controls: Search, Theme Toggle, Admin Profile & Mobile Toggle -->
        <div class="header-actions flex items-center gap-1.5 sm:gap-2">
            <!-- Search Button -->
            <button type="button"
                    class="icon-btn search-open w-9 h-9 rounded-lg text-[#4A433B] dark:text-[#F6F3EC]/80 hover:text-[#1C1814] dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition-colors cursor-pointer"
                    @click="searchOpen = !searchOpen"
                    aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
            </button>

            <!-- Theme Toggle Button (Light / Dark) -->
            <button type="button"
                    onclick="toggleTheme()"
                    class="icon-btn theme-toggle w-9 h-9 rounded-lg text-[#4A433B] dark:text-[#F6F3EC]/80 hover:text-[#0071E3] dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition-colors cursor-pointer"
                    aria-label="Toggle dark/light mode"
                    title="Cambiar Modo Claro / Oscuro">
                <!-- Moon icon shown when in light mode (to switch to dark) -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px] dark:hidden block" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                <!-- Sun icon shown when in dark mode (to switch to light) -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px] hidden dark:block" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"></circle>
                    <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                </svg>
            </button>

            @auth
                <!-- Admin quick indicator -->
                <a href="{{ route('admin.dashboard') }}"
                   class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#F5F5F7] dark:bg-[#242426] border border-[#E5E7EB] dark:border-[#333336] text-[#1D1D1F] dark:text-white text-xs hover:border-[#0071E3] transition-colors ml-1"
                   title="Ir al Panel de Administración">
                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                    <span class="font-medium text-[11px]">{{ auth()->user()->name }}</span>
                </a>
            @endauth

            <!-- Mobile Menu Toggle Button -->
            <button type="button"
                    class="icon-btn menu-toggle w-9 h-9 rounded-lg text-[#4A433B] dark:text-[#F6F3EC]/80 hover:text-[#1C1814] dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition-colors lg:hidden cursor-pointer"
                    @click="mobileOpen = !mobileOpen"
                    aria-label="Menu"
                    aria-expanded="false"
                    :aria-expanded="mobileOpen">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18"></path>
                </svg>
            </button>
        </div>
    </div>


    <!-- Quick Live Search Dropdown Drawer -->
    <div x-show="searchOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-data="{
            query: '',
            results: [],
            loading: false,
            timeout: null,
            search() {
                clearTimeout(this.timeout);
                if (this.query.length < 2) {
                    this.results = [];
                    this.loading = false;
                    return;
                }
                this.loading = true;
                this.timeout = setTimeout(async () => {
                    try {
                        const res = await fetch('{{ route('api.search.live') }}?q=' + encodeURIComponent(this.query));
                        if (res.ok) {
                            this.results = await res.json();
                        }
                    } catch(e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                }, 250);
            }
         }"
         class="py-3 border-t border-[#E5DFD7] dark:border-[#222938] bg-[#F5F5F7]/95 dark:bg-[#131822]/95 backdrop-blur-xl relative">
        <div class="container-app relative">
            <form action="{{ route('search') }}" method="GET" class="relative">
            <input type="text" name="q" x-model="query" @input="search()"
                    placeholder="Buscar programas, juegos y utilidades en tiempo real..."
                    class="w-full bg-white dark:bg-[#10141C] border border-[#E0D8CE] dark:border-[#222938] rounded-xl px-10 py-2.5 text-xs text-[#1C1814] dark:text-white placeholder-[#8C847A] focus:outline-none focus:border-[#0071E3]">
            <svg class="w-4 h-4 text-[#8C847A] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin w-4 h-4 text-[#0071E3]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </form>

        <!-- Real-Time Autocomplete Results Dropdown -->
        <div x-show="results.length > 0" x-cloak
             class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#131822] border border-[#E5DFD7] dark:border-[#222938] rounded-2xl p-2 shadow-2xl z-50 max-h-80 overflow-y-auto space-y-1">
            <template x-for="item in results" :key="item.id">
                <a :href="item.url" class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#F5F5F7] dark:hover:bg-[#1B222E] transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-[#F8F4EE] dark:bg-[#10141C] border border-[#E8E1D7] dark:border-[#222938] p-0.5 flex-shrink-0 overflow-hidden">
                        <img :src="item.icon_url" :alt="item.name" class="w-full h-full object-cover rounded-md">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-[#1C1814] dark:text-white group-hover:text-[#0071E3] transition-colors truncate" x-text="item.name"></p>
                        <p class="text-[10px] text-[#7E776F]" x-text="item.category + ' • v' + item.version + ' • ' + item.size"></p>
                    </div>
                    <span class="text-[10px] text-[#0071E3] font-semibold flex items-center gap-0.5">
                        <span x-text="item.downloads"></span>
                    </span>
                </a>
            </template>
        </div>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-t border-[#E5DFD7] dark:border-[#222938] bg-[#F5F5F7]/95 dark:bg-[#131822]/95 backdrop-blur-xl">
        <div class="container-app py-4 space-y-3">
            <form action="{{ route('search') }}" method="GET">
                <input type="text" name="q" placeholder="Buscar aplicaciones y juegos..." value="{{ request('q') }}" class="input w-full text-xs">
            </form>
            <nav class="flex flex-col gap-1 text-sm text-[#38322B] dark:text-[#F6F3EC]/90">
                <a href="{{ route('popular') }}" class="py-2 hover:text-[#0071E3] dark:hover:text-white border-b border-[#E5DFD7] dark:border-[#222938]">Editor’s Choice</a>
                <a href="{{ route('categories') }}" class="py-2 hover:text-[#0071E3] dark:hover:text-white border-b border-[#E5DFD7] dark:border-[#222938]">Application</a>
                <a href="{{ route('search', ['q' => 'Adobe']) }}" class="py-2 hover:text-[#0071E3] dark:hover:text-white border-b border-[#E5DFD7] dark:border-[#222938]">Adobe Collection</a>
                <a href="{{ route('category', 'games') }}" class="py-2 hover:text-[#0071E3] dark:hover:text-white border-b border-[#E5DFD7] dark:border-[#222938]">Games</a>
                <a href="{{ route('guide.sip') }}" class="py-2 hover:text-[#0071E3] dark:hover:text-white border-b border-[#E5DFD7] dark:border-[#222938] text-[#0071E3]">Disable SIP</a>
                <a href="{{ route('guide.fix') }}" class="py-2 hover:text-[#0071E3] dark:hover:text-white border-b border-[#E5DFD7] dark:border-[#222938] text-[#0071E3]">Fix Damaged Apps</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="py-2 text-[#0071E3] font-medium">Panel Admin</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="py-2 text-xs text-danger text-left">Cerrar Sesión</button>
                    </form>
                @endauth
            </nav>
        </div>
    </div>
</header>
