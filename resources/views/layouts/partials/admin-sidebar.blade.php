<!-- macOS Traffic Lights & Brand -->
<div class="p-5 border-b border-[#26211B]">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#FF5F57] border border-[#FF5F57]/40 inline-block shadow-sm"></span>
            <span class="w-3 h-3 rounded-full bg-[#FEBC2E] border border-[#FEBC2E]/40 inline-block shadow-sm"></span>
            <span class="w-3 h-3 rounded-full bg-[#28C840] border border-[#28C840]/40 inline-block shadow-sm"></span>
            <span class="ml-2 text-[10px] uppercase font-bold tracking-wider text-[#736B63]">macOS Console</span>
        </div>

        @if(!empty($isMobile))
        <!-- Mobile Close Drawer Button -->
        <button type="button" @click="sidebarOpen = false"
                class="lg:hidden p-1.5 rounded-lg text-[#8C847A] hover:text-white hover:bg-[#1E1914] transition-colors focus:outline-none cursor-pointer"
                aria-label="Cerrar menú">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        @endif
    </div>

    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
        <div class="w-9 h-9 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
            @if(setting('logo_type') === 'image' && setting('logo_image'))
                <img src="{{ setting('logo_image') }}" alt="Logo" class="w-full h-full object-contain rounded-lg">
            @else
                <x-logo-icon :icon="setting('logo_icon', 'finder')" class="w-full h-full" />
            @endif
        </div>
        <div>
            <div class="flex items-center gap-1.5">
                <span class="text-white font-extrabold text-base tracking-tight">{{ setting('site_name', 'HackMac') }}<span class="text-primary font-bold">{{ setting('site_name_highlight', '.cc') }}</span></span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary/20 text-primary border border-primary/30">PRO</span>
            </div>
            <span class="text-[#8C847A] text-xs">Gestor de Aplicaciones</span>
        </div>
    </a>

    <!-- Quick Action: Publicar Programa -->
    <div class="mt-4">
        <a href="{{ route('admin.applications.create') }}"
           class="w-full py-2.5 px-3.5 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-md shadow-primary/20 flex items-center justify-center gap-2 transition-all group">
            <svg class="w-4 h-4 transition-transform group-hover:rotate-90 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Publicar Programa</span>
        </a>
    </div>
</div>

<!-- Navigation Links -->
<nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
    <p class="px-3 text-[10px] font-bold text-[#6B635A] uppercase tracking-wider mb-2">Panel Principal</p>

    <a href="{{ route('admin.dashboard') }}"
       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all mb-1 {{ request()->routeIs('admin.dashboard') ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-semibold' : 'text-[#A39B91] hover:text-white hover:bg-[#1A1612]' }}">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white' : 'bg-[#1C1814] text-[#A39B91]' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <span>Dashboard</span>
        </div>
    </a>

    <!-- Scraper en Panel Principal -->
    <a href="{{ route('admin.scraper') }}"
       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.scraper*') ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-semibold' : 'text-[#A39B91] hover:text-white hover:bg-[#1A1612]' }}">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.scraper*') ? 'bg-primary text-white' : 'bg-[#1C1814] text-[#A39B91]' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </div>
            <span>Scraper Automático</span>
        </div>
        <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold bg-[#0071E3]/20 text-[#0071E3] border border-[#0071E3]/30">
            AUTO
        </span>
    </a>

    <div class="pt-4">
        <p class="px-3 text-[10px] font-bold text-[#6B635A] uppercase tracking-wider mb-2">Contenido</p>

        <!-- Applications -->
        <a href="{{ route('admin.applications') }}"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all mb-1 {{ request()->routeIs('admin.applications*') ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-semibold' : 'text-[#A39B91] hover:text-white hover:bg-[#1A1612]' }}">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.applications*') ? 'bg-primary text-white' : 'bg-[#1C1814] text-[#A39B91]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span>Aplicaciones</span>
            </div>
            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ request()->routeIs('admin.applications*') ? 'bg-primary/20 text-primary' : 'bg-[#221C16] text-[#7E776F]' }}">
                {{ \App\Models\Application::count() }}
            </span>
        </a>

        <!-- Categories -->
        <a href="{{ route('admin.categories') }}"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all mb-1 {{ request()->routeIs('admin.categories*') ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-semibold' : 'text-[#A39B91] hover:text-white hover:bg-[#1A1612]' }}">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.categories*') ? 'bg-primary text-white' : 'bg-[#1C1814] text-[#A39B91]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span>Categorías</span>
            </div>
            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ request()->routeIs('admin.categories*') ? 'bg-primary/20 text-primary' : 'bg-[#221C16] text-[#7E776F]' }}">
                {{ \App\Models\Category::count() }}
            </span>
        </a>

        <!-- Broken Links Reports -->
        @php
            $pendingReportsCount = \Illuminate\Support\Facades\Schema::hasTable('broken_link_reports') 
                ? \App\Models\BrokenLinkReport::where('status', 'pending')->count() 
                : 0;
        @endphp
        <a href="{{ route('admin.reports') }}"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all mb-1 {{ request()->routeIs('admin.reports*') ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-semibold' : 'text-[#A39B91] hover:text-white hover:bg-[#1A1612]' }}">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.reports*') ? 'bg-danger text-white' : 'bg-[#1C1814] text-[#A39B91]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <span>Enlaces Caídos</span>
            </div>
            @if($pendingReportsCount > 0)
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-danger/20 text-danger border border-danger/30 animate-pulse">
                    {{ $pendingReportsCount }}
                </span>
            @endif
        </a>

        <!-- Settings -->
        <a href="{{ route('admin.settings') }}"
           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.settings*') ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-semibold' : 'text-[#A39B91] hover:text-white hover:bg-[#1A1612]' }}">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.settings*') ? 'bg-primary text-white' : 'bg-[#1C1814] text-[#A39B91]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span>Configuración</span>
            </div>
        </a>
    </div>

    <div class="pt-4">
        <p class="px-3 text-[10px] font-bold text-[#6B635A] uppercase tracking-wider mb-2">Accesos Directos</p>
        <a href="{{ route('home') }}" target="_blank"
           class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs text-[#8C847A] hover:text-white hover:bg-[#1A1612] transition-colors">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-[#8C847A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>Ver Portal Web</span>
            </div>
            <svg class="w-3.5 h-3.5 text-[#5C554D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</nav>

<!-- User Footer Card -->
<div class="p-3.5 border-t border-[#26211B] bg-[#0E0D0B]/60 mt-auto">
    <div class="flex items-center justify-between p-2 rounded-xl bg-[#181410] border border-[#2B241C]">
        <div class="flex items-center gap-2.5 overflow-hidden">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary to-amber-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-white truncate leading-tight">{{ auth()->user()->name ?? 'Admin' }}</p>
                <span class="inline-block text-[10px] text-primary font-mono leading-tight">Super Admin</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Cerrar sesión"
                    class="p-1.5 text-[#8C847A] hover:text-danger hover:bg-danger/10 rounded-lg transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</div>
