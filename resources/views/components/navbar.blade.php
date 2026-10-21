<!-- Uptodown Clone Sticky Header -->
<header class="utd-header-component is-sticky" role="banner" x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="utd-header-inner flex items-center justify-between h-[52px] px-4 bg-[#0f292f] border-b border-[#163e47] text-white select-none">
        <!-- Left Section: Hamburger Menu -->
        <div class="flex items-center gap-3">
            <button @click="mobileOpen = !mobileOpen" 
                    class="w-9 h-9 flex items-center justify-center text-[#cddce0] hover:text-[#00aad5] rounded-lg transition-colors cursor-pointer"
                    aria-label="Abrir menú" 
                    title="Menú">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Center Section: Inverted Chevron Badge + Brand Logo -->
        <div class="site-branding flex items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-white font-extrabold text-xl tracking-tight hover:opacity-95 transition-opacity">
                <div class="w-[26px] h-[26px] bg-[#00aad5] rounded-[5px] flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </div>
                <span class="text-white font-black tracking-[-0.03em] font-sans text-[20px]">
                    {{ setting('site_name', 'uptodown') }}<span class="text-[#00aad5] font-black">{{ setting('site_name_highlight', '') }}</span>
                </span>
            </a>
        </div>

        <!-- Right Section: Turbo Pill + Globe + Search + User Avatar -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Turbo VIP Avatar Pill -->
            <a href="#" class="w-[30px] h-[30px] rounded-full border-2 border-[#e06290] bg-gradient-to-tr from-[#e06290] to-[#9333ea] text-white flex items-center justify-center font-extrabold text-[11px] shadow-sm hover:scale-105 transition-transform" title="Turbo VIP">
                <span>U</span>
            </a>

            <!-- Language Switcher / Globe -->
            <button class="w-9 h-9 flex items-center justify-center text-[#cddce0] hover:text-[#00aad5] rounded-lg transition-colors cursor-pointer" title="Idioma">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </button>

            <!-- Search Toggle -->
            <button @click="searchOpen = !searchOpen" 
                    class="w-9 h-9 flex items-center justify-center text-[#cddce0] hover:text-[#00aad5] rounded-lg transition-colors cursor-pointer" 
                    title="Buscar">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>

            <!-- User Login / Account -->
            @auth
                <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 flex items-center justify-center text-[#cddce0] hover:text-[#00aad5] rounded-lg transition-colors" title="Panel de Administración">
                    <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}" class="w-9 h-9 flex items-center justify-center text-[#cddce0] hover:text-[#00aad5] rounded-lg transition-colors" title="Iniciar Sesión">
                    <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
            @endauth
        </div>
    </div>

    <!-- Dropdown Quick Search Bar -->
    <div x-show="searchOpen" x-cloak 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-[#0c2328] border-b-2 border-[#00aad5] px-4 py-2 flex items-center gap-3">
        <form action="{{ route('search') }}" method="GET" class="flex-1 flex items-center gap-2">
            <input type="text" 
                   name="q" 
                   value="{{ request('q', request('s')) }}"
                   placeholder="Buscar programas, juegos PC, APKs de Android, torrents..." 
                   class="w-full bg-[#13353d] border border-[#163e47] text-white px-3.5 py-1.5 rounded-lg text-sm focus:outline-none focus:border-[#00aad5]">
            <button type="submit" class="bg-[#00aad5] text-white font-bold text-xs px-4 py-2 rounded-lg hover:bg-[#00beed] transition-colors">
                Buscar
            </button>
        </form>
        <button @click="searchOpen = false" class="text-xs font-bold text-[#7f949a] hover:text-white px-2 py-1">
            Cerrar
        </button>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-full"
         class="fixed inset-y-0 left-0 w-72 bg-[#0f292f] border-r border-[#163e47] z-50 p-5 shadow-2xl overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-[#163e47] mb-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-[#00aad5] rounded flex items-center justify-center text-white">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </div>
                <span class="font-extrabold text-white text-lg">Uptodown</span>
            </div>
            <button @click="mobileOpen = false" class="text-[#7f949a] hover:text-white">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <ul class="space-y-2 text-sm font-bold text-[#cddce0]">
            <li><a href="{{ route('home') }}" class="block px-3 py-2 rounded hover:bg-[#13353d] hover:text-[#00aad5]">Inicio</a></li>
            <li><a href="{{ route('categories') }}" class="block px-3 py-2 rounded hover:bg-[#13353d] hover:text-[#00aad5]">Categorías</a></li>
            <li><a href="{{ route('popular') }}" class="block px-3 py-2 rounded hover:bg-[#13353d] hover:text-[#00aad5]">Top Descargas</a></li>
            <li><a href="{{ route('new') }}" class="block px-3 py-2 rounded hover:bg-[#13353d] hover:text-[#00aad5]">Novedades</a></li>
            <li><a href="{{ route('updated') }}" class="block px-3 py-2 rounded hover:bg-[#13353d] hover:text-[#00aad5]">Últimas Actualizaciones</a></li>
        </ul>
    </div>
</header>
