@extends('layouts.admin')

@section('title', 'Importador & Scraper DDL')
@section('page-title', 'Scraper Automático')

@section('content')
<div class="space-y-6 max-w-[1720px] mx-auto w-full" x-data="scraperApp()">

    <!-- Floating Success Toast -->
    <div x-show="toastVisible" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 bg-[#161310] border border-success/40 text-success px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold backdrop-blur-md">
        <svg class="w-5 h-5 text-success flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastText"></span>
    </div>

    <!-- Header Panel macOS Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#14110E] border border-[#2B241C] p-5 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-[#EA580C] p-0.5 shadow-lg shadow-primary/20 flex-shrink-0">
                <div class="w-full h-full bg-[#181410] rounded-[10px] flex items-center justify-center text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-white tracking-tight">Scraper Inteligente DDL</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#2997FF]/15 text-[#2997FF] border border-[#2997FF]/30">v1.0 AUTO</span>
                </div>
                <p class="text-xs text-[#8C847A]">Importa programas a 1 clic, extrae enlaces limpios de descarga y automatiza actualizaciones con Cron.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap w-full sm:w-auto">
            <button type="button" @click="syncTorrentmacNow()" :disabled="torrentSyncing"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#30D158] to-[#10B981] hover:from-[#28C840] hover:to-[#059669] text-black text-xs font-black shadow-lg shadow-success/20 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50">
                <svg :class="torrentSyncing ? 'animate-spin' : ''" class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="torrentSyncing ? 'Subiendo Torrents a R2...' : '🧲 Sincronizar TorrentMac (R2)'"></span>
            </button>

            <button type="button" @click="syncUpdatesNow()" :disabled="syncingUpdates"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-lg shadow-primary/25 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50">
                <svg :class="syncingUpdates ? 'animate-spin' : ''" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="syncingUpdates ? 'Detectando Novedades...' : '⚡ Sincronizar Catálogo DDL'"></span>
            </button>

            <button type="button" @click="syncCats()" :disabled="syncingCategories"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#201C17] hover:bg-[#2A241E] border border-[#3A3025] text-[#D8CFBE] hover:text-white text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50">
                <svg :class="syncingCategories ? 'animate-spin' : ''" class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="syncingCategories ? 'Sincronizando...' : 'Sincronizar Categorías'"></span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Metric Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="p-4 bg-[#14110E] border border-[#262019] rounded-2xl shadow">
            <span class="text-[11px] font-bold text-[#8C847A] uppercase tracking-wider block mb-1">Apps en el Portal</span>
            <div class="text-2xl font-black text-white font-mono">{{ number_format($stats['total_apps']) }}</div>
        </div>
        <div class="p-4 bg-[#14110E] border border-[#262019] rounded-2xl shadow">
            <span class="text-[11px] font-bold text-[#8C847A] uppercase tracking-wider block mb-1">Torrents en R2 / Local</span>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-black text-[#30D158] font-mono">{{ number_format($stats['total_torrent_apps']) }}</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30">{{ strtoupper($stats['storage_disk'] ?? 'LOCAL') }}</span>
            </div>
        </div>
        <div class="p-4 bg-[#14110E] border border-[#262019] rounded-2xl shadow">
            <span class="text-[11px] font-bold text-[#8C847A] uppercase tracking-wider block mb-1">Categorías Activas</span>
            <div class="text-2xl font-black text-[#2997FF] font-mono">{{ number_format($stats['total_categories']) }}</div>
        </div>
        <div class="p-4 bg-[#14110E] border border-[#262019] rounded-2xl shadow">
            <span class="text-[11px] font-bold text-[#8C847A] uppercase tracking-wider block mb-1">Versiones Guardadas</span>
            <div class="text-2xl font-black text-success font-mono">{{ number_format($stats['total_versions']) }}</div>
        </div>
        <div class="col-span-2 sm:col-span-3 lg:col-span-1 p-4 bg-[#14110E] border border-[#262019] rounded-2xl shadow" x-data="{
            cronActive: {{ $stats['cron_enabled'] ? 'true' : 'false' }},
            togglingCron: false,
            async toggleCron() {
                this.togglingCron = true;
                try {
                    const res = await fetch('{{ url('/admin/scraper/toggle-cron') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.cronActive = data.cron_enabled;
                    }
                } catch (e) {
                    alert('Error al cambiar estado de cron');
                } finally {
                    this.togglingCron = false;
                }
            }
        }">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[11px] font-bold text-[#8C847A] uppercase tracking-wider block">Estado de Cron</span>
                <button type="button" @click="toggleCron()" :disabled="togglingCron"
                        class="text-[10px] font-bold px-2 py-0.5 rounded-lg border transition-all cursor-pointer"
                        :class="cronActive ? 'bg-danger/15 border-danger/30 text-danger hover:bg-danger/25' : 'bg-success/15 border-success/30 text-success hover:bg-success/25'"
                        x-text="togglingCron ? '...' : (cronActive ? 'Pausar' : 'Activar Ahora')">
                </button>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <template x-if="cronActive">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-success animate-pulse flex-shrink-0"></span>
                        <span class="text-xs font-bold text-success truncate">Activo ({{ $stats['cron_limit'] ?? 10 }} apps / cada 2h)</span>
                    </div>
                </template>
                <template x-if="!cronActive">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#6B645C] flex-shrink-0"></span>
                        <span class="text-xs font-bold text-[#8C847A]">Inactivo</span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-[#14110E] border border-[#262019] rounded-2xl flex-wrap sm:flex-nowrap">
        <button type="button" @click="tab = 'search'"
                :class="tab === 'search' ? 'bg-[#221C16] text-white border border-[#3A3025] shadow font-bold' : 'text-[#8C847A] hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>Buscador en Vivo DDL</span>
        </button>

        <button type="button" @click="tab = 'torrentmac'; if (torrentApps.length === 0) loadTorrentmac(1)"
                :class="tab === 'torrentmac' ? 'bg-[#221C16] text-[#30D158] border border-[#30D158]/40 shadow font-bold' : 'text-[#8C847A] hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer relative">
            <span class="text-base leading-none">🧲</span>
            <span>TorrentMac (R2 P2P)</span>
            <span x-show="torrentUpdatesCount > 0" x-cloak
                  class="px-1.5 py-0.2 rounded-full font-mono text-[10px] font-extrabold bg-warning text-black"
                  x-text="torrentUpdatesCount"></span>
        </button>

        <button type="button" @click="tab = 'cron'"
                :class="tab === 'cron' ? 'bg-[#221C16] text-white border border-[#3A3025] shadow font-bold' : 'text-[#8C847A] hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Automatización Cron</span>
        </button>

        <button type="button" @click="tab = 'recent'"
                :class="tab === 'recent' ? 'bg-[#221C16] text-white border border-[#3A3025] shadow font-bold' : 'text-[#8C847A] hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Últimos Importados ({{ $recentApps->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: BUSCADOR EN VIVO DE HAXMAC -->
    <div x-show="tab === 'search'" class="space-y-6">
        <!-- Search Box Card -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                Buscador en Tiempo Real DDL
            </h2>
            <p class="text-xs text-[#8C847A]">Escribe el nombre de cualquier programa (ejemplo: <em>Photoshop, Final Cut, Ableton, CleanMyMac</em>) para consultar el catálogo DDL en vivo e importarlo a 1 clic con todos sus mirrors limpios.</p>

            <form @submit.prevent="doSearch()" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <input type="text" x-model="searchQuery"
                           placeholder="Buscar programas en catálogo (ej. Office, Logic Pro, CleanMyMac)..."
                           class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl pl-11 pr-10 py-3 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors">
                    <svg class="w-5 h-5 text-[#6E675E] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" x-show="searchQuery.length > 0" @click="clearSearch()"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#8C847A] hover:text-white text-xs p-1 cursor-pointer">
                        ✕
                    </button>
                </div>

                <button type="submit" :disabled="searching || searchQuery.trim().length < 2"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-lg shadow-primary/25 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 flex-shrink-0">
                    <svg x-show="searching" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="searching ? 'Consultando Catálogo...' : 'Buscar en Catálogo'"></span>
                </button>
            </form>
        </div>

        <!-- Categories Filter Carousel / Pills (Real Categories: Games, Utilities, Media, Adobe, etc.) -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-4 shadow-xl space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Explorar Catálogo DDL por Categorías</h3>
                </div>
                <span class="text-[11px] text-[#8C847A] hidden sm:inline">Selecciona cualquier categoría para cargar directamente sus posts y juegos</span>
            </div>

            <!-- Categories Scrollable Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                <!-- Quick Filter Pill: Requieren Update -->
                <button type="button" 
                        @click="togglePendingUpdates()"
                        :class="viewingUpdatesOnly 
                            ? 'bg-warning text-black font-extrabold shadow-md shadow-warning/30 border-warning ring-2 ring-warning/20' 
                            : 'bg-[#221810] hover:bg-[#2C1F14] text-warning border-warning/35 hover:border-warning/70'"
                        class="px-3.5 py-2 rounded-xl text-xs whitespace-nowrap border transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
                    <span>⚡</span>
                    <span>Requieren Update</span>
                    <span x-show="updatesCount > 0" class="px-1.5 py-0.2 rounded-full text-[9px] font-mono font-black"
                          :class="viewingUpdatesOnly ? 'bg-black text-warning' : 'bg-warning text-black'"
                          x-text="updatesCount"></span>
                </button>

                @foreach($haxmacCategories as $cat)
                    <button type="button" 
                            @click="selectCategory('{{ $cat['slug'] }}', '{{ $cat['name'] }}')"
                            :class="activeCategory === '{{ $cat['slug'] }}' && !hasSearched && !viewingUpdatesOnly
                                ? 'bg-gradient-to-r from-primary to-[#EA580C] text-white font-bold shadow-md shadow-primary/25 border-transparent' 
                                : 'bg-[#181410] hover:bg-[#221C16] text-[#B0A79B] hover:text-white border-[#2A231A]'"
                            class="px-3.5 py-2 rounded-xl text-xs whitespace-nowrap border transition-all flex items-center gap-2 flex-shrink-0 cursor-pointer">
                        <span>{{ $cat['icon'] }}</span>
                        <span>{{ $cat['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Section Header (Dynamic: Search Results vs Categoría Activa vs Requieren Update) -->
        <div id="catalog-results-header" class="space-y-4 relative">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                <div>
                    <div class="flex items-center gap-2.5">
                        <template x-if="hasSearched">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/15 text-primary border border-primary/30">
                                <span>Búsqueda en Vivo</span>
                            </span>
                        </template>
                        <template x-if="viewingUpdatesOnly">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-warning/20 text-warning border border-warning/40 shadow-sm shadow-warning/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-warning animate-pulse"></span>
                                <span>Actualizaciones Disponibles</span>
                            </span>
                        </template>
                        <template x-if="!hasSearched && !viewingUpdatesOnly">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#201C18] text-[#D8CFBE] border border-[#382E23]">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span x-text="activeCategoryName"></span>
                            </span>
                        </template>
                        
                        <h2 class="text-base font-bold text-white" 
                            x-text="viewingUpdatesOnly ? 'Programas que Requieren Actualización' : (hasSearched ? ('Resultados para «' + searchQuery + '»') : ('Novedades en ' + activeCategoryName))">Últimos Agregados en Catálogo DDL</h2>
                    </div>
                    <p class="text-xs text-[#8C847A] mt-1" 
                       x-text="viewingUpdatesOnly 
                           ? ('Mostrando ' + pendingUpdates.length + ' programas instalados localmente que tienen versiones más recientes en el catálogo fuente. Actualízalos con un clic.') 
                           : (hasSearched 
                               ? ('Mostrando ' + searchResults.length + ' de ' + searchTotal + ' programas encontrados en el catálogo fuente') 
                               : ('Catálogo en vivo para ' + activeCategoryName + '. Puedes importar cualquier software al instante con 1 clic.'))"></p>
                </div>

                <!-- Header Action Controls & Column Switcher -->
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <!-- Column Layout Switcher (4, 5, 6 col) -->
                    <div class="flex items-center gap-1 bg-[#191511] border border-[#2B231B] p-1 rounded-xl shadow-inner">
                        <span class="text-[10px] font-bold text-[#736B63] px-1.5 uppercase font-mono hidden sm:inline">Cols:</span>
                        <button type="button" @click="gridCols = 4" 
                                :class="gridCols === 4 ? 'bg-[#29221B] text-primary border border-primary/30 shadow' : 'text-[#8C847A] hover:text-white'"
                                class="px-2 py-1 rounded-lg text-[10px] font-mono font-bold transition-all cursor-pointer" title="Vista 4 Columnas">
                            4
                        </button>
                        <button type="button" @click="gridCols = 5" 
                                :class="gridCols === 5 ? 'bg-[#29221B] text-primary border border-primary/30 shadow' : 'text-[#8C847A] hover:text-white'"
                                class="px-2 py-1 rounded-lg text-[10px] font-mono font-bold transition-all cursor-pointer" title="Vista 5 Columnas">
                            5
                        </button>
                        <button type="button" @click="gridCols = 6" 
                                :class="gridCols === 6 ? 'bg-primary text-white shadow shadow-primary/30' : 'text-[#8C847A] hover:text-white'"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold transition-all cursor-pointer" title="Vista 6 Columnas (Predeterminado)">
                            6 Cols
                        </button>
                    </div>
 
                    <!-- Active Queue Counter Pill -->
                    <div x-show="importQueue.length > 0" x-cloak class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gradient-to-r from-[#881337] to-[#4C0519] border border-amber-500/40 text-white text-xs font-bold shadow-lg shadow-[#881337]/30 animate-pulse flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                        </svg>
                        <span>Cola segura: <strong class="text-amber-300 font-mono" x-text="importQueue.length"></strong> en espera</span>
                    </div>

                    <!-- Import Entire Page / Update All Visible button -->
                    <button type="button" @click="importPageApps()" :disabled="importingAllPage || getDisplayedApps().length === 0"
                            class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#059669] to-[#047857] hover:from-[#10B981] hover:to-[#059669] text-white text-xs font-bold shadow-md shadow-[#059669]/20 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <svg x-show="importingAllPage" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!importingAllPage" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span x-text="importingAllPage ? ('Importando ' + pageImportProgress) : (viewingUpdatesOnly ? '⚡ Actualizar Esta Vista' : '⚡ Importar Esta Página')"></span>
                    </button>

                    <!-- Back to Catalog button if searched or viewing updates -->
                    <div x-show="hasSearched || viewingUpdatesOnly" class="flex-shrink-0">
                        <button type="button" @click="clearSearch()"
                                class="px-3.5 py-1.5 rounded-xl bg-[#201C18] hover:bg-[#2A241F] text-[#D4C9BC] hover:text-white border border-[#382E23] text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#8C847A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Volver a Catálogo</span>
                        </button>
                    </div>

                    <!-- Option next to Actualizar Todo: Requieren Actualización -->
                    <div x-show="!hasSearched" class="flex-shrink-0">
                        <button type="button" @click="togglePendingUpdates()" :disabled="loadingUpdates"
                                class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
                                :class="viewingUpdatesOnly 
                                    ? 'bg-warning text-black border-warning shadow-warning/30 font-black ring-2 ring-warning/30' 
                                    : 'bg-[#221C16] hover:bg-[#2C241D] text-warning border-warning/40 shadow-black/40 hover:border-warning/80'">
                            <svg x-show="!loadingUpdates" class="w-3.5 h-3.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <svg x-show="loadingUpdates" class="animate-spin w-3.5 h-3.5 text-current" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loadingUpdates ? 'Buscando...' : (viewingUpdatesOnly ? '✓ Viendo Updates' : 'Requieren Actualización')"></span>
                            <span x-show="updatesCount > 0" class="px-1.5 py-0.2 rounded-full font-mono text-[10px] font-extrabold"
                                  :class="viewingUpdatesOnly ? 'bg-black text-warning' : 'bg-warning text-black'" 
                                  x-text="updatesCount"></span>
                        </button>
                    </div>

                    <!-- Auto-Sync Updates button -->
                    <div x-show="!hasSearched" class="flex-shrink-0">
                        <button type="button" @click="syncUpdatesNow()" :disabled="syncingUpdates"
                                class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-md shadow-primary/20 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                            <svg :class="syncingUpdates ? 'animate-spin' : ''" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="syncingUpdates ? 'Sincronizando...' : (viewingUpdatesOnly ? '⚡ Actualizar Todas' : '⚡ Actualizar Todo')"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div x-show="getDisplayedApps().length === 0 && !searching && !loadingPage && !loadingUpdates" 
                 class="p-12 text-center bg-[#14110E] border border-[#2B241C] rounded-[22px] shadow-lg space-y-3">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#1C1814] border border-[#2B241C] flex items-center justify-center"
                     :class="viewingUpdatesOnly ? 'text-success' : 'text-[#8C847A]'">
                    <template x-if="viewingUpdatesOnly">
                        <svg class="w-7 h-7 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="!viewingUpdatesOnly">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </template>
                </div>
                <h3 class="text-sm font-bold text-white" 
                    x-text="viewingUpdatesOnly ? '¡Todos tus programas están al día!' : 'No se encontraron programas disponibles'"></h3>
                <p class="text-xs text-[#8C847A] max-w-md mx-auto" 
                   x-text="viewingUpdatesOnly 
                       ? 'No se detectaron nuevas versiones pendientes en el catálogo para tus programas locales en este momento.' 
                       : (hasSearched ? 'No hubo coincidencias para esa búsqueda en el catálogo. Intenta con otro término.' : 'No se pudo conectar temporalmente con el catálogo o no hay apps registradas.')"></p>
                <div class="pt-2">
                    <button type="button" @click="clearSearch()" class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover cursor-pointer">
                        Ver Catálogo Principal
                    </button>
                </div>
            </div>

            <!-- Card Grid & Loading State Wrapper -->
            <div class="relative">
                <!-- Transparent Loading Overlay during Page Transitions -->
                <div x-show="loadingPage" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 bg-[#0E0C0A]/70 backdrop-blur-[2px] z-20 rounded-[22px] flex flex-col items-center justify-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#181410] border border-primary/40 flex items-center justify-center text-primary shadow-2xl">
                        <svg class="animate-spin w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-[#D8CFBE]">Cargando página del catálogo...</span>
                </div>

                <!-- Card Grid (6 Columns Layout) -->
                <div class="grid gap-3 sm:gap-3.5 transition-all duration-300"
                     :class="{
                         'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4': gridCols === 4,
                         'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5': gridCols === 5,
                         'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-6': gridCols === 6
                     }">
                    <template x-for="item in getDisplayedApps()" :key="item.slug">
                        <div class="bg-[#14110E] border border-[#262019] hover:border-[#3E3428] rounded-[20px] p-3 flex flex-col justify-between transition-all duration-300 shadow-xl group hover:shadow-2xl hover:shadow-black/60">
                            
                            <!-- Top Image / Poster Container -->
                            <div>
                                <div class="relative w-full aspect-[4/3] rounded-[14px] bg-[#1A1612] border border-[#241E18] overflow-hidden flex items-center justify-center p-2.5 group-hover:border-[#382E24] transition-all">
                                    <template x-if="item.icon_url">
                                        <img :src="item.icon_url" :alt="item.name" 
                                             class="max-h-full max-w-full object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-300" 
                                             loading="lazy">
                                    </template>
                                    <template x-if="!item.icon_url">
                                        <div class="w-12 h-12 rounded-xl bg-[#221C16] border border-[#30271E] flex items-center justify-center text-primary font-bold text-lg">
                                            
                                        </div>
                                    </template>

                                    <!-- Top Right: Version Tag & Comparison -->
                                    <template x-if="item.version">
                                        <div class="absolute top-2 right-2 flex flex-col items-end gap-0.5">
                                            <span class="px-1.5 py-0.5 rounded-md bg-[#0E0C0A]/90 backdrop-blur-md border border-[#2E251E] text-[#D8CFBE] font-mono font-bold text-[9px] shadow" 
                                                  x-text="'v' + item.version"></span>
                                            <template x-if="item.has_update && item.local_version">
                                                <span class="px-1.5 py-0.5 rounded-md bg-warning text-black font-mono font-extrabold text-[8px] shadow flex items-center gap-0.5" title="Versión instalada vs nueva versión">
                                                    <span class="line-through opacity-75" x-text="'v' + item.local_version"></span>
                                                    <span>→</span>
                                                    <span x-text="'v' + item.version"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Top Left: Status Chip -->
                                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                                        <template x-if="item.has_update">
                                            <span class="relative flex h-5 w-5" title="Actualización Disponible">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning opacity-75"></span>
                                                <span class="relative inline-flex items-center justify-center rounded-full h-5 w-5 bg-gradient-to-tr from-warning to-amber-300 text-black shadow-lg shadow-warning/50 ring-2 ring-[#1E1914]">
                                                    <svg class="w-3 h-3 animate-spin [animation-duration:4s]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                </span>
                                            </span>
                                        </template>
                                        <template x-if="item.is_imported && !item.has_update">
                                            <span class="px-1.5 py-0.5 rounded-md bg-success/95 backdrop-blur-md text-black font-extrabold text-[8px] uppercase tracking-wider shadow">
                                                ✓ Importado
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h3 class="text-[13px] sm:text-[13.5px] font-bold text-white leading-snug line-clamp-2 mt-2.5 group-hover:text-primary transition-colors min-h-[2.35rem]" 
                                    :title="item.name" 
                                    x-text="item.name"></h3>

                                <!-- Category Pill & Downloads -->
                                <div class="mt-2 flex items-center justify-between gap-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-[#251016] border border-[#4D1C28] text-[#FB7185] text-[9px] font-black uppercase tracking-wider font-mono truncate" 
                                          x-text="item.category || 'MACOS'"></span>

                                    <template x-if="item.downloads">
                                        <span class="text-[9px] font-mono text-[#8C847A] flex items-center gap-0.5 flex-shrink-0" :title="'Descargas: ' + item.downloads">
                                            <svg class="w-2.5 h-2.5 text-[#6E675E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span x-text="item.downloads"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Divider & Action Button -->
                            <div class="mt-2">
                                <!-- Thin separator line matching image -->
                                <div class="h-px w-full bg-[#241E18] my-2.5"></div>

                                <!-- CTA Button (Queue Aware: Importing vs Queued vs Normal) -->
                                <button type="button" 
                                        @click="importSingle(item)" 
                                        :disabled="importingSlug === item.slug || isItemQueued(item.slug)"
                                        class="w-full py-2 px-2.5 rounded-xl text-xs font-black shadow-lg transition-all flex items-center justify-center gap-1.5 active:scale-[0.98] disabled:opacity-90"
                                        :class="importingSlug === item.slug 
                                            ? 'bg-gradient-to-r from-[#9F1239] via-[#881337] to-[#4C0519] text-white shadow-[#9F1239]/40 cursor-wait animate-pulse' 
                                            : (isItemQueued(item.slug)
                                                ? 'bg-gradient-to-r from-[#881337] via-[#701A28] to-[#4C0519] text-[#FCD34D] border border-amber-500/40 shadow-black/50 cursor-wait'
                                                : (item.has_update 
                                                    ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#FBBF24] hover:to-[#F59E0B] text-black shadow-[#F59E0B]/25 cursor-pointer' 
                                                    : (item.is_imported 
                                                        ? 'bg-gradient-to-r from-[#1E1914] to-[#2B231C] hover:from-[#2B231C] hover:to-[#382E24] text-[#D8CFBE] border border-[#3E3326] shadow-black/40 cursor-pointer' 
                                                        : 'bg-gradient-to-r from-[#E11D48] via-[#E11D48] to-[#BE123C] hover:from-[#F43F5E] hover:to-[#E11D48] text-white shadow-[#E11D48]/30 hover:shadow-[#E11D48]/45 cursor-pointer')))">
                                    
                                    <!-- State 1: Active Import Spinner -->
                                    <svg x-show="importingSlug === item.slug" class="animate-spin w-3.5 h-3.5 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>

                                    <!-- State 2: Queued Clock Icon -->
                                    <svg x-show="isItemQueued(item.slug)" class="w-3.5 h-3.5 text-amber-400 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                    </svg>

                                    <!-- State 3: Normal Lightning Bolt Icon -->
                                    <svg x-show="importingSlug !== item.slug && !isItemQueued(item.slug)" class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                    </svg>

                                    <span x-text="importingSlug === item.slug 
                                        ? 'Importando & Redactando IA...' 
                                        : (isItemQueued(item.slug)
                                            ? 'En cola segura...'
                                            : (item.has_update 
                                                ? '1–Clic Update' 
                                                : (item.is_imported ? '✓ Re-importar' : '1–Clic Importar')))">1–Clic Importar</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Dynamic Pagination Bar (macOS High Performance Style) -->
            <div x-show="totalPages > 1 && !viewingUpdatesOnly" class="pt-6 border-t border-[#241E18] flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Page Info / Counter -->
                <div class="flex items-center gap-2 text-xs text-[#8C847A]">
                    <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-primary" :class="loadingPage ? 'animate-ping' : ''"></span>
                    <span>Página <strong class="text-white font-mono" x-text="currentPage"></strong> de <strong class="text-[#D8CFBE] font-mono" x-text="totalPages"></strong></span>
                    <span x-show="loadingPage" class="text-primary text-[11px] font-semibold animate-pulse ml-1">Cargando catálogo...</span>
                </div>

                <!-- Page Navigation Buttons -->
                <div class="flex items-center gap-1.5 flex-wrap justify-center">
                    <!-- Prev Button -->
                    <button type="button" @click="goToPage(currentPage - 1)" :disabled="!hasPrev || loadingPage"
                            class="px-3 py-1.5 rounded-xl bg-[#1B1713] hover:bg-[#251F19] text-[#D8CFBE] hover:text-white border border-[#2F261E] text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="hidden sm:inline">Anterior</span>
                    </button>

                    <!-- Page Numbers -->
                    <template x-for="(num, idx) in getPageNumbers()" :key="idx">
                        <div>
                            <!-- Number Button -->
                            <template x-if="num !== '...'">
                                <button type="button" @click="goToPage(num)" :disabled="loadingPage"
                                        class="w-8 h-8 rounded-xl text-xs font-mono font-bold transition-all flex items-center justify-center cursor-pointer disabled:opacity-60"
                                        :class="num === currentPage 
                                            ? 'bg-gradient-to-br from-primary to-[#EA580C] text-white shadow-lg shadow-primary/25 font-black scale-105' 
                                            : 'bg-[#181410] hover:bg-[#241E18] text-[#A8A199] hover:text-white border border-[#2C231A]'">
                                    <span x-text="num"></span>
                                </button>
                            </template>

                            <!-- Ellipsis Dots -->
                            <template x-if="num === '...'">
                                <span class="w-6 text-center text-[#6E675E] font-mono text-xs select-none">…</span>
                            </template>
                        </div>
                    </template>

                    <!-- Next Button -->
                    <button type="button" @click="goToPage(currentPage + 1)" :disabled="!hasNext || loadingPage"
                            class="px-3 py-1.5 rounded-xl bg-[#1B1713] hover:bg-[#251F19] text-[#D8CFBE] hover:text-white border border-[#2F261E] text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <span class="hidden sm:inline">Siguiente</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Direct Page Jump Input -->
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-[#736B63] text-[11px] hidden md:inline">Ir a pág:</span>
                    <input type="number" min="1" :max="totalPages" 
                           @keydown.enter.prevent="goToPage(parseInt($event.target.value))"
                           :placeholder="currentPage"
                           class="w-14 bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-lg px-2 py-1 text-center font-mono text-xs text-white focus:outline-none">
                </div>
            </div>
        </div>
    </div>

    <!-- TAB: TORRENTMAC CATÁLOGO EN VIVO & R2 -->
    <div x-show="tab === 'torrentmac'" class="space-y-6" x-cloak>
        <!-- Search Box Card for TorrentMac -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#30D158]"></span>
                Buscador en Tiempo Real de TorrentMac
            </h2>
            <p class="text-xs text-[#8C847A]">Escribe el nombre de cualquier programa o juego (ejemplo: <em>Photoshop, Final Cut, Logic Pro, Ableton, Parallels, CleanMyMac</em>) para consultar el catálogo de TorrentMac en vivo e importarlo a 1 clic con su torrent y capturas.</p>

            <form @submit.prevent="doTorrentSearch()" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <input type="text" x-model="torrentSearchQuery"
                           placeholder="Buscar programas o juegos en TorrentMac (ej. Logic Pro, Final Cut, CleanMyMac)..."
                           class="w-full bg-[#12100E] border border-[#2B241C] focus:border-[#30D158] rounded-xl pl-11 pr-10 py-3 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors">
                    <svg class="w-5 h-5 text-[#6E675E] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" x-show="torrentSearchQuery.length > 0" @click="clearTorrentSearch()"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#8C847A] hover:text-white text-xs p-1 cursor-pointer">
                        ✕
                    </button>
                </div>

                <button type="submit" :disabled="torrentSearching || torrentSearchQuery.trim().length < 2"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#30D158] to-[#10B981] hover:from-[#28C840] hover:to-[#059669] text-black text-xs font-black shadow-lg shadow-success/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 flex-shrink-0">
                    <svg x-show="torrentSearching" class="animate-spin w-4 h-4 text-black" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="torrentSearching ? 'Consultando TorrentMac...' : 'Buscar en TorrentMac'"></span>
                </button>
            </form>
        </div>

        <!-- Info Banner & Cloudflare R2 Status -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#30D158] animate-pulse"></span>
                        Catálogo en Vivo de TorrentMac & Almacenamiento R2
                    </h2>
                    <p class="text-xs text-[#8C847A] mt-1">
                        Explora aplicaciones y juegos en formato Torrent. Al importar, el archivo <code class="text-[#30D158] font-mono">.torrent</code> y las capturas se transfieren a tu almacenamiento sin depender de servidores de terceros.
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                    <!-- Local vs R2 Switcher -->
                    <div class="flex items-center gap-1.5 p-1 bg-[#1A1612] border border-[#2E251C] rounded-xl text-xs">
                        <span class="text-[#8C847A] text-[11px] font-semibold pl-1.5 hidden md:inline">Destino:</span>
                        <button type="button" @click="setTorrentStorageDisk('local')"
                                :class="torrentStorageTarget === 'local' 
                                    ? 'bg-[#2A231C] text-white font-bold border border-[#44372A] shadow' 
                                    : 'text-[#8C847A] hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition-all flex items-center gap-1 cursor-pointer text-xs">
                            <span>📁 Local</span>
                        </button>
                        <button type="button" @click="setTorrentStorageDisk('r2')"
                                :class="torrentStorageTarget === 'r2' 
                                    ? 'bg-gradient-to-r from-[#30D158] to-[#10B981] text-black font-black shadow-md shadow-success/20' 
                                    : 'text-[#8C847A] hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition-all flex items-center gap-1 cursor-pointer text-xs"
                                :title="r2Configured ? 'Cloudflare R2 activo' : 'Requiere credenciales en .env'">
                            <span>☁️ Cloudflare R2</span>
                            <span x-show="!r2Configured" class="w-1.5 h-1.5 rounded-full bg-amber-400" title="Sin credenciales configuradas"></span>
                        </button>
                    </div>

                    <a href="{{ route('admin.settings', ['tab' => 'storage']) }}"
                       class="px-3 py-1.5 rounded-xl bg-[#F38020]/15 hover:bg-[#F38020]/25 text-[#F38020] border border-[#F38020]/40 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                       title="Ir a Configurar Credenciales de Cloudflare R2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Configurar R2</span>
                    </a>

                    <!-- Active Torrent Queue Counter Pill -->
                    <div x-show="torrentQueue.length > 0" x-cloak class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gradient-to-r from-[#881337] to-[#4C0519] border border-amber-500/40 text-white text-xs font-bold shadow-lg shadow-[#881337]/30 animate-pulse flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                        </svg>
                        <span>Cola Torrent: <strong class="text-amber-300 font-mono" x-text="torrentQueue.length"></strong> en espera</span>
                    </div>

                    <button type="button" @click="loadTorrentmac(torrentCurrentPage)" :disabled="torrentLoading"
                            class="px-3 py-1.5 rounded-xl bg-[#201C17] hover:bg-[#2A241E] border border-[#3A3025] text-[#D8CFBE] hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg :class="torrentLoading ? 'animate-spin' : ''" class="w-3.5 h-3.5 text-[#30D158]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Refrescar</span>
                    </button>
                </div>
            </div>

            <!-- Categories Horizontal Pills -->
            <div class="pt-2 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                <template x-for="cat in torrentCategories" :key="cat.slug">
                    <button type="button" @click="loadTorrentmac(1, cat.slug)"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 cursor-pointer"
                            :class="(!torrentHasSearched && torrentCategory === cat.slug) 
                                ? 'bg-gradient-to-r from-[#30D158] to-[#10B981] text-black font-black shadow-lg shadow-success/20' 
                                : 'bg-[#1C1814] hover:bg-[#26201A] text-[#8C847A] hover:text-white border border-[#2E251C]'">
                        <span x-text="cat.icon"></span>
                        <span x-text="cat.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Catalog Results Section -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-6">
            <div id="torrentmac-results-header" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#30D158]"></span>
                    <template x-if="torrentHasSearched">
                        <span>Búsqueda TorrentMac: "<strong class="text-[#30D158]" x-text="torrentSearchQuery"></strong>"</span>
                    </template>
                    <template x-if="viewingTorrentUpdatesOnly">
                        <span class="text-warning">⚡ Actualizaciones Pendientes TorrentMac</span>
                    </template>
                    <template x-if="!torrentHasSearched && !viewingTorrentUpdatesOnly">
                        <span>Resultados: <strong class="text-[#30D158]" x-text="torrentCategoryName"></strong></span>
                    </template>
                    <span x-show="torrentLoading || torrentSearching || loadingTorrentUpdates" class="text-xs text-[#30D158] animate-pulse ml-2 font-normal">Cargando desde TorrentMac...</span>
                </h3>
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                    <!-- Back to Catalog button if searched or viewing updates -->
                    <div x-show="torrentHasSearched || viewingTorrentUpdatesOnly" class="flex-shrink-0">
                        <button type="button" @click="clearTorrentSearch()"
                                class="px-3 py-1.5 rounded-xl bg-[#201C18] hover:bg-[#2A241F] text-[#D4C9BC] hover:text-white border border-[#382E23] text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#8C847A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Volver a Catálogo</span>
                        </button>
                    </div>

                    <!-- Option: Requieren Actualización (TorrentMac) -->
                    <div x-show="!torrentHasSearched" class="flex-shrink-0">
                        <button type="button" @click="toggleTorrentPendingUpdates()" :disabled="loadingTorrentUpdates"
                                class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
                                :class="viewingTorrentUpdatesOnly 
                                    ? 'bg-warning text-black border-warning shadow-warning/30 font-black ring-2 ring-warning/30' 
                                    : 'bg-[#221C16] hover:bg-[#2C241D] text-warning border-warning/40 shadow-black/40 hover:border-warning/80'">
                            <svg x-show="!loadingTorrentUpdates" class="w-3.5 h-3.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <svg x-show="loadingTorrentUpdates" class="animate-spin w-3.5 h-3.5 text-current" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loadingTorrentUpdates ? 'Buscando...' : (viewingTorrentUpdatesOnly ? '✓ Viendo Updates' : 'Requieren Actualización')"></span>
                            <span x-show="torrentUpdatesCount > 0" class="px-1.5 py-0.2 rounded-full font-mono text-[10px] font-extrabold"
                                  :class="viewingTorrentUpdatesOnly ? 'bg-black text-warning' : 'bg-warning text-black'" 
                                  x-text="torrentUpdatesCount"></span>
                        </button>
                    </div>

                    <!-- Auto-Sync Torrent Updates button -->
                    <div x-show="!torrentHasSearched" class="flex-shrink-0">
                        <button type="button" @click="syncTorrentUpdatesNow()" :disabled="torrentSyncing"
                                class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-warning to-[#D97706] hover:from-[#FBBF24] hover:to-warning text-black text-xs font-bold shadow-md shadow-warning/20 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                            <svg :class="torrentSyncing ? 'animate-spin' : ''" class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="torrentSyncing ? 'Actualizando...' : (viewingTorrentUpdatesOnly ? '⚡ Actualizar Todas' : '⚡ Actualizar Todo')"></span>
                        </button>
                    </div>

                    <span class="text-xs text-[#8C847A] font-mono hidden sm:inline" x-show="getDisplayedTorrentApps().length > 0">
                        Mostrando <strong class="text-white" x-text="getDisplayedTorrentApps().length"></strong> programas
                    </span>
                </div>
            </div>

            <!-- Loading State Skeleton -->
            <div x-show="(torrentLoading || torrentSearching || loadingTorrentUpdates) && getDisplayedTorrentApps().length === 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 py-8">
                <template x-for="i in 12" :key="i">
                    <div class="bg-[#1A1612] border border-[#262019] rounded-2xl p-4 animate-pulse space-y-3">
                        <div class="w-16 h-16 bg-[#262019] rounded-2xl mx-auto"></div>
                        <div class="h-4 bg-[#262019] rounded w-3/4 mx-auto"></div>
                        <div class="h-3 bg-[#262019] rounded w-1/2 mx-auto"></div>
                        <div class="h-8 bg-[#262019] rounded-xl w-full"></div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="!torrentLoading && !torrentSearching && !loadingTorrentUpdates && getDisplayedTorrentApps().length === 0" class="text-center py-12 space-y-3">
                <span class="text-4xl" x-text="viewingTorrentUpdatesOnly ? '🎉' : '🧲'"></span>
                <p class="text-sm text-[#8C847A]" x-text="viewingTorrentUpdatesOnly ? '¡Todos tus programas Torrent están al día! No hay nuevas versiones pendientes en este momento.' : (torrentHasSearched ? 'No se encontraron programas en TorrentMac que coincidan con tu búsqueda.' : 'No hay programas cargados o la categoría está vacía.')"></p>
                <button type="button" @click="torrentHasSearched || viewingTorrentUpdatesOnly ? clearTorrentSearch() : loadTorrentmac(1)" class="px-4 py-2 rounded-xl bg-[#30D158] text-black text-xs font-bold hover:bg-[#28C840] transition-colors cursor-pointer">
                    <span x-text="torrentHasSearched || viewingTorrentUpdatesOnly ? 'Volver al Catálogo Principal' : 'Cargar Catálogo Inicial'"></span>
                </button>
            </div>

            <!-- Cards Grid -->
            <div x-show="getDisplayedTorrentApps().length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" :class="(torrentLoading || torrentSearching || loadingTorrentUpdates) ? 'opacity-50 pointer-events-none' : ''">
                <template x-for="app in getDisplayedTorrentApps()" :key="app.url">
                    <div class="bg-[#181410] border rounded-2xl p-3.5 flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-xl group relative"
                         :class="app.has_update ? 'border-warning/50 hover:border-warning' : 'border-[#2B231B] hover:border-[#30D158]/50'">
                        <!-- Top Badges -->
                        <div>
                            <div class="relative flex items-center justify-center pt-2 pb-1">
                                <template x-if="app.icon_url">
                                    <img :src="app.icon_url" :alt="app.name" class="w-16 h-16 object-cover rounded-2xl shadow-md border border-white/10 group-hover:scale-105 transition-transform"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                </template>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#30D158]/20 to-primary/20 flex items-center justify-center text-lg font-bold text-[#30D158]"
                                     :style="app.icon_url ? 'display:none' : ''">
                                    <span x-text="app.clean_name ? app.clean_name.charAt(0) : 'T'"></span>
                                </div>

                                <!-- Status Pill (Top Left: Update indicator - Icon only, animated, no text) -->
                                <div class="absolute -top-1 left-0 flex items-center justify-center z-10" x-show="app.has_update" title="Actualización Disponible">
                                    <span class="relative flex h-5 w-5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning opacity-75"></span>
                                        <span class="relative inline-flex items-center justify-center rounded-full h-5 w-5 bg-gradient-to-tr from-warning to-amber-300 text-black shadow-lg shadow-warning/50 ring-2 ring-[#181410]">
                                            <svg class="w-3 h-3 animate-spin [animation-duration:4s]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </span>
                                    </span>
                                </div>

                                <!-- Status Pill (Top Right: Dual / Torrent / En Portal) -->
                                <div class="absolute -top-1 right-0 flex flex-col items-end gap-1">
                                    <template x-if="app.is_dual">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-[#2997FF]/20 text-[#2997FF] border border-[#2997FF]/30 font-mono" title="Cuenta con Descarga Directa y Torrent">
                                            ⚡ DUAL
                                        </span>
                                    </template>
                                    <template x-if="!app.is_dual && app.has_torrent">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-success/20 text-success border border-success/30 font-mono">
                                            ✓ TORRENT
                                        </span>
                                    </template>
                                    <template x-if="!app.is_dual && !app.has_torrent && app.in_database">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-[#A855F7]/20 text-[#A855F7] border border-[#A855F7]/30 font-mono">
                                            EN PORTAL
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Title & Version -->
                            <h4 class="text-[13px] font-bold text-white leading-snug line-clamp-2 mt-2.5 group-hover:text-[#30D158] transition-colors min-h-[2.35rem]"
                                :title="app.name" x-text="app.clean_name || app.name"></h4>

                            <div class="mt-1 flex items-center justify-between text-[10px] font-mono text-[#8C847A]">
                                <template x-if="app.has_update && app.local_version">
                                    <span class="px-1.5 py-0.5 rounded bg-warning/20 border border-warning/40 text-warning font-bold flex items-center gap-1" title="Versión en portal vs nueva versión torrent">
                                        <span class="line-through opacity-70" x-text="'v' + app.local_version"></span>
                                        <span>→</span>
                                        <span class="font-black text-warning" x-text="'v' + app.version"></span>
                                    </span>
                                </template>
                                <template x-if="!app.has_update || !app.local_version">
                                    <span class="text-[#30D158] font-bold" x-text="app.version ? 'v' + app.version : 'Torrent'"></span>
                                </template>
                                <span x-text="app.category || 'macOS'"></span>
                            </div>
                        </div>

                        <!-- Divider & Action Button (Queue Aware & Update Aware) -->
                        <div class="mt-3 pt-2.5 border-t border-[#262019]">
                            <button type="button" @click="importTorrentApp(app)" :disabled="torrentImportingUrl === app.url || isTorrentQueued(app.url)"
                                    class="w-full py-2 px-2 rounded-xl text-xs font-black shadow-lg transition-all flex items-center justify-center gap-1.5 active:scale-[0.98] disabled:opacity-90"
                                    :class="torrentImportingUrl === app.url 
                                        ? 'bg-gradient-to-r from-[#9F1239] via-[#881337] to-[#4C0519] text-white shadow-[#9F1239]/40 cursor-wait animate-pulse' 
                                        : (isTorrentQueued(app.url)
                                            ? 'bg-gradient-to-r from-[#881337] via-[#701A28] to-[#4C0519] text-[#FCD34D] border border-amber-500/40 shadow-black/50 cursor-wait'
                                            : (app.has_update
                                                ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#FBBF24] hover:to-[#F59E0B] text-black shadow-[#F59E0B]/25 cursor-pointer ring-1 ring-amber-400/50'
                                                : (app.has_torrent 
                                                    ? 'bg-[#221C16] hover:bg-[#2B231C] text-[#30D158] border border-[#30D158]/30 shadow-none cursor-pointer' 
                                                    : 'bg-gradient-to-r from-[#30D158] to-[#10B981] hover:from-[#28C840] hover:to-[#059669] text-black shadow-success/20 cursor-pointer')))">
                                
                                <!-- Loading Spinner -->
                                <svg x-show="torrentImportingUrl === app.url" class="animate-spin w-3.5 h-3.5 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>

                                <!-- Queued Clock Icon -->
                                <svg x-show="isTorrentQueued(app.url)" class="w-3.5 h-3.5 text-amber-400 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                </svg>

                                <!-- Magnet/Torrent Icon or Update lightning -->
                                <span x-show="torrentImportingUrl !== app.url && !isTorrentQueued(app.url)" class="text-sm" x-text="app.has_update ? '⚡' : '🧲'"></span>

                                <span x-text="torrentImportingUrl === app.url 
                                    ? 'Importando Torrent...' 
                                    : (isTorrentQueued(app.url)
                                        ? 'En cola segura...'
                                        : (app.has_update
                                            ? ('⚡ Actualizar a v' + app.version)
                                            : (app.has_torrent ? '✓ Re-importar' : (app.in_database ? '+ Vincular Torrent' : '1–Clic Torrent'))))">
                                    1–Clic Torrent
                                </span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Dynamic Pagination Bar for TorrentMac -->
            <div x-show="!viewingTorrentUpdatesOnly && torrentTotalPages > 1" class="pt-6 border-t border-[#241E18] flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Page Info / Counter -->
                <div class="flex items-center gap-2 text-xs text-[#8C847A]">
                    <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-[#30D158]" :class="(torrentLoading || torrentSearching) ? 'animate-ping' : ''"></span>
                    <span>Página <strong class="text-white font-mono" x-text="torrentCurrentPage"></strong> de <strong class="text-[#D8CFBE] font-mono" x-text="torrentTotalPages"></strong></span>
                    <span x-show="torrentLoading || torrentSearching" class="text-[#30D158] text-[11px] font-semibold animate-pulse ml-1">Cargando catálogo...</span>
                </div>

                <!-- Page Navigation Buttons -->
                <div class="flex items-center gap-1.5 flex-wrap justify-center">
                    <!-- Prev Button -->
                    <button type="button" @click="goToTorrentPage(torrentCurrentPage - 1)" :disabled="!torrentHasPrev || torrentLoading || torrentSearching"
                            class="px-3 py-1.5 rounded-xl bg-[#1B1713] hover:bg-[#251F19] text-[#D8CFBE] hover:text-white border border-[#2F261E] text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="hidden sm:inline">Anterior</span>
                    </button>

                    <!-- Page Numbers -->
                    <template x-for="(num, idx) in getTorrentPageNumbers()" :key="idx">
                        <div>
                            <!-- Number Button -->
                            <template x-if="num !== '...'">
                                <button type="button" @click="goToTorrentPage(num)" :disabled="torrentLoading || torrentSearching"
                                        class="w-8 h-8 rounded-xl text-xs font-mono font-bold transition-all flex items-center justify-center cursor-pointer disabled:opacity-60"
                                        :class="num === torrentCurrentPage 
                                            ? 'bg-gradient-to-br from-[#30D158] to-[#10B981] text-black shadow-lg shadow-success/20 font-black scale-105' 
                                            : 'bg-[#181410] hover:bg-[#241E18] text-[#A8A199] hover:text-white border border-[#2C231A]'">
                                    <span x-text="num"></span>
                                </button>
                            </template>

                            <!-- Ellipsis Dots -->
                            <template x-if="num === '...'">
                                <span class="w-6 text-center text-[#6E675E] font-mono text-xs select-none">…</span>
                            </template>
                        </div>
                    </template>

                    <!-- Next Button -->
                    <button type="button" @click="goToTorrentPage(torrentCurrentPage + 1)" :disabled="!torrentHasNext || torrentLoading || torrentSearching"
                            class="px-3 py-1.5 rounded-xl bg-[#1B1713] hover:bg-[#251F19] text-[#D8CFBE] hover:text-white border border-[#2F261E] text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <span class="hidden sm:inline">Siguiente</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Direct Page Jump Input -->
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-[#736B63] text-[11px] hidden md:inline">Ir a pág:</span>
                    <input type="number" min="1" :max="torrentTotalPages" 
                           @keydown.enter.prevent="goToTorrentPage(parseInt($event.target.value))"
                           :placeholder="torrentCurrentPage"
                           class="w-14 bg-[#12100E] border border-[#2B241C] focus:border-[#30D158] rounded-lg px-2 py-1 text-center font-mono text-xs text-white focus:outline-none">
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: AUTOMATIZACIÓN CON CRON -->
    <div x-show="tab === 'cron'" x-cloak class="space-y-6">
        <form action="{{ route('admin.scraper.cron-settings') }}" method="POST" class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-5">
            @csrf
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                Configuración del Cron de Auto-Importación
            </h2>
            <p class="text-xs text-[#8C847A]">El programador de Laravel revisará diariamente las categorías activas en segundo plano, detectará nuevas versiones o nuevos programas lanzados en el catálogo y los publicará automáticamente.</p>

            <div class="p-4 rounded-xl bg-[#171411] border border-[#2B241C] space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="cron_enabled" value="1" {{ $stats['cron_enabled'] ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-primary focus:ring-primary bg-[#12100E] border-[#382E24]">
                    <div>
                        <span class="text-xs font-bold text-white block">Activar Sincronización Automática Programada</span>
                        <span class="text-[11px] text-[#8C847A]">Ejecuta el rastreador en segundo plano para mantener la web actualizada al día.</span>
                    </div>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-[#262019]">
                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Frecuencia de Sincronización</label>
                        <select name="cron_frequency"
                                class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2 text-sm text-white focus:outline-none transition-colors">
                            <option value="2hours" {{ ($stats['cron_frequency'] ?? '2hours') === '2hours' ? 'selected' : '' }}>Cada 2 horas (Recomendado)</option>
                            <option value="hourly" {{ ($stats['cron_frequency'] ?? '') === 'hourly' ? 'selected' : '' }}>Cada 1 hora</option>
                            <option value="4hours" {{ ($stats['cron_frequency'] ?? '') === '4hours' ? 'selected' : '' }}>Cada 4 horas</option>
                        </select>
                        <p class="text-[10px] text-[#736B63] mt-1">Intervalo con el que revisa novedades.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Límite por Ejecución</label>
                        <input type="number" name="cron_limit" min="1" max="50" value="{{ $stats['cron_limit'] ?? 10 }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2 text-sm text-white focus:outline-none transition-colors">
                        <p class="text-[10px] text-[#736B63] mt-1">Máximo de apps a publicar por tanda (ej. 10).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Hora Barrido Nocturno</label>
                        <input type="time" name="cron_time" value="{{ $stats['cron_time'] }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2 text-sm text-white focus:outline-none transition-colors">
                        <p class="text-[10px] text-[#736B63] mt-1">Hora para el recorrido profundo de categorías.</p>
                    </div>
                </div>
            </div>

            <!-- Dual Auto-Sync Architecture Explanation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-[#161310] border border-[#2A231C] space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">1. Sincronización Rápida (Cada 2 horas)</span>
                    </div>
                    <p class="text-[11px] text-[#8C847A] leading-relaxed">
                        Revisa la portada del catálogo. Si detecta que ha salido una nueva versión de un programa existente o una app nueva, descarga automáticamente capturas, novedades ("Whats new?") y los enlaces limpios sin saturar el servidor.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-[#161310] border border-[#2A231C] space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">2. Rastreo Profundo Nocturno (Diario)</span>
                    </div>
                    <p class="text-[11px] text-[#8C847A] leading-relaxed">
                        A la hora seleccionada (ej. {{ $stats['cron_time'] }}), recorre el catálogo completo categoría por categoría para rescatar programas históricos o versiones alternativas.
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-[#100E0C] border border-[#201C18] space-y-2">
                <span class="text-xs font-bold text-[#A8A199] block">Instrucción para el Crontab del Servidor (o Probar en Consola):</span>
                <p class="text-xs text-[#7A736A]">Para que las tareas programadas funcionen en tu servidor VPS o hosting, añade esta línea a tu `crontab -e`:</p>
                <div class="p-2.5 rounded-lg bg-[#0A0908] border border-[#2A231C] text-xs font-mono text-primary select-all">
                    * * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1
                </div>
                <p class="text-[11px] text-[#6E675E] pt-1">O ejecuta directamente para probar la sincronización de novedades en tu terminal:</p>
                <div class="p-2 rounded-lg bg-[#0A0908] border border-[#2A231C] text-xs font-mono text-[#D4C9BC] select-all">
                    php artisan haxmac:scrape --sync-latest
                </div>
            </div>

            <div class="flex justify-end pt-3">
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-lg shadow-primary/25 transition-all cursor-pointer">
                    Guardar Configuración Cron
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 4: ÚLTIMOS PROGRAMAS IMPORTADOS -->
    <div x-show="tab === 'recent'" x-cloak class="space-y-4">
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                Programas Importados Recientemente
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#262019] text-[#8C847A] uppercase text-[10px] font-bold">
                            <th class="py-3 px-3">Programa</th>
                            <th class="py-3 px-3">Versión</th>
                            <th class="py-3 px-3">Categoría</th>
                            <th class="py-3 px-3">Tamaño</th>
                            <th class="py-3 px-3">Mirror Limpio</th>
                            <th class="py-3 px-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#201C18]">
                        @forelse($recentApps as $app)
                            <tr class="hover:bg-[#1A1713] transition-colors">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-[#1D1915] border border-[#30271F] p-0.5 overflow-hidden flex-shrink-0">
                                            @if($app->icon)
                                                <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" class="w-full h-full object-contain rounded">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-primary font-bold text-xs"></div>
                                            @endif
                                        </div>
                                        <span class="font-bold text-white truncate max-w-xs">{{ $app->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 font-mono text-[#A8A199]">v{{ $app->version }}</td>
                                <td class="py-3 px-3 text-[#A8A199]">{{ $app->category?->name ?? 'General' }}</td>
                                <td class="py-3 px-3 text-[#736B63] font-mono">{{ $app->size ?: 'N/A' }}</td>
                                <td class="py-3 px-3">
                                    @if($app->download_url_external)
                                        <a href="{{ $app->download_url_external }}" target="_blank" rel="noreferrer"
                                           class="inline-flex items-center gap-1 text-[11px] text-[#2997FF] hover:underline font-mono truncate max-w-[180px]">
                                            <span>{{ parse_url($app->download_url_external, PHP_URL_HOST) }}</span>
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-[#59524A] text-[11px]">Sin mirror</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('app', $app->slug) }}" target="_blank"
                                           class="px-2.5 py-1 rounded-lg bg-[#201C18] hover:bg-[#2A241F] text-white text-[11px] font-semibold border border-[#352C22] transition-colors">
                                            Ver en Web
                                        </a>
                                        <a href="{{ route('admin.applications.edit', $app) }}"
                                           class="px-2.5 py-1 rounded-lg bg-primary/15 hover:bg-primary/25 text-primary text-[11px] font-semibold border border-primary/30 transition-colors">
                                            Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-[#736B63]">No hay programas importados todavía. Usa el buscador o el importador de categoría arriba.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function scraperApp() {
    return {
    tab: 'search',
    gridCols: 6,
    searchQuery: '',
    searching: false,
    searchResults: [],
    searchTotal: 0,
    hasSearched: false,
    activeCategory: 'all',
    activeCategoryName: 'Todos los Posts',
    latestApps: @js($haxmacLatest ?? []),
    currentPage: {{ $haxmacPagination['current_page'] ?? 1 }},
    totalPages: {{ $haxmacPagination['total_pages'] ?? 1 }},
    hasNext: {{ ($haxmacPagination['has_next'] ?? false) ? 'true' : 'false' }},
    hasPrev: {{ ($haxmacPagination['has_prev'] ?? false) ? 'true' : 'false' }},
    loadingPage: false,
    importingSlug: null,
    importQueue: [],
    importingAllPage: false,
    pageImportProgress: '',
    importMessage: '',
    syncingCategories: false,
    pendingUpdates: [],
    loadingUpdates: false,
    viewingUpdatesOnly: false,
    updatesCount: (@js($haxmacLatest ?? [])).filter(it => it.has_update).length,

    // TorrentMac State & Methods
    torrentCategory: 'all',
    torrentCategoryName: 'Todos los Torrents',
    torrentApps: [],
    torrentSearchQuery: '',
    torrentSearching: false,
    torrentHasSearched: false,
    torrentSearchResults: [],
    torrentSearchTotal: 0,
    torrentCurrentPage: 1,
    torrentTotalPages: 1,
    torrentHasNext: false,
    torrentHasPrev: false,
    torrentLoading: false,
    torrentImportingUrl: null,
    torrentQueue: [],
    torrentSyncing: false,
    torrentUpdatesCount: 0,
    torrentPendingUpdates: [],
    viewingTorrentUpdatesOnly: false,
    loadingTorrentUpdates: false,
    torrentCategories: @js($torrentmacCategories ?? []),
    torrentStorageTarget: '{{ $stats['torrent_storage_target'] ?? 'local' }}',
    effectiveStorageDisk: '{{ $stats['storage_disk'] ?? 'local' }}',
    r2Configured: {{ ($stats['r2_configured'] ?? false) ? 'true' : 'false' }},

    init() {
        this.fetchUpdatesCountAsync();
        this.fetchTorrentUpdatesCountAsync();
    },

    async fetchUpdatesCountAsync() {
        try {
            const res = await fetch('{{ route('admin.scraper.pending-updates') }}?pages=2');
            const data = await res.json();
            if (data.success) {
                this.updatesCount = data.count;
                if (this.viewingUpdatesOnly) {
                    this.pendingUpdates = data.items;
                }
            }
        } catch(e) {
            console.warn('Could not fetch pending updates count', e);
        }
    },

    async togglePendingUpdates() {
        if (this.viewingUpdatesOnly) {
            this.viewingUpdatesOnly = false;
            this.activeCategory = 'all';
            this.activeCategoryName = 'Todos los Posts';
            await this.goToPage(1);
            return;
        }

        this.loadingUpdates = true;
        this.hasSearched = false;
        this.searchQuery = '';
        this.searchResults = [];
        this.activeCategory = 'updates';
        this.activeCategoryName = 'Actualizaciones Pendientes';

        try {
            const res = await fetch('{{ route('admin.scraper.pending-updates') }}?pages=2');
            const data = await res.json();
            if (data.success) {
                this.pendingUpdates = data.items;
                this.updatesCount = data.count;
                this.viewingUpdatesOnly = true;
                document.getElementById('catalog-results-header')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                alert(data.message || 'Error al obtener actualizaciones');
            }
        } catch(e) {
            console.error(e);
            alert('Error al conectar con el servidor para buscar actualizaciones');
        } finally {
            this.loadingUpdates = false;
        }
    },

    getDisplayedApps() {
        if (this.viewingUpdatesOnly) return this.pendingUpdates;
        if (this.hasSearched) return this.searchResults;
        return this.latestApps;
    },

    async selectCategory(slug, name) {
        this.viewingUpdatesOnly = false;
        if (this.activeCategory === slug && !this.hasSearched) return;
        this.activeCategory = slug;
        this.activeCategoryName = name;
        this.hasSearched = false;
        this.searchQuery = '';
        this.searchResults = [];
        await this.goToPage(1);
    },

    async doSearch(page = 1) {
        if (this.searchQuery.trim().length < 2) return;
        this.searching = true;
        this.hasSearched = true;
        try {
            const res = await fetch('{{ route('admin.scraper.search') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ q: this.searchQuery, page: page })
            });
            const data = await res.json();
            if (data.success) {
                this.searchResults = data.results;
                this.searchTotal = data.total;
                if (data.pagination) {
                    this.currentPage = data.pagination.current_page;
                    this.totalPages = data.pagination.total_pages;
                    this.hasNext = data.pagination.has_next;
                    this.hasPrev = data.pagination.has_prev;
                }
            }
        } catch(e) {
            console.error(e);
        } finally {
            this.searching = false;
        }
    },

    clearSearch() {
        this.searchQuery = '';
        this.hasSearched = false;
        this.searchResults = [];
        this.searchTotal = 0;
        this.goToPage(1);
    },

    async goToPage(page) {
        if (page < 1 || (this.totalPages > 1 && page > this.totalPages) || this.loadingPage) return;
        this.loadingPage = true;

        if (this.hasSearched) {
            await this.doSearch(page);
            this.loadingPage = false;
            document.getElementById('catalog-results-header')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        try {
            const res = await fetch('{{ route('admin.scraper.latest') }}?category=' + encodeURIComponent(this.activeCategory) + '&page=' + page);
            const data = await res.json();
            if (data.success) {
                this.latestApps = data.items;
                this.currentPage = data.pagination.current_page;
                this.totalPages = data.pagination.total_pages;
                this.hasNext = data.pagination.has_next;
                this.hasPrev = data.pagination.has_prev;
                document.getElementById('catalog-results-header')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch(e) {
            console.error(e);
            alert('Error al cargar la página del catálogo');
        } finally {
            this.loadingPage = false;
        }
    },

    getPageNumbers() {
        const current = this.currentPage;
        const total = this.totalPages;
        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }
        if (current <= 4) {
            return [1, 2, 3, 4, 5, '...', total];
        }
        if (current >= total - 3) {
            return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        }
        return [1, '...', current - 1, current, current + 1, '...', total];
    },

    async importPageApps() {
        const items = this.hasSearched ? this.searchResults : this.latestApps;
        const toImport = items.filter(it => !it.is_imported || it.has_update);
        if (toImport.length === 0) {
            this.showToast('✓ Todas las apps de esta página ya están importadas');
            return;
        }

        if (!confirm(`¿Deseas importar ${toImport.length} programas pendientes de esta página?`)) {
            return;
        }

        this.importingAllPage = true;
        let count = 0;

        for (const item of toImport) {
            count++;
            this.pageImportProgress = `${count}/${toImport.length}`;
            try {
                await this.executeImport(item);
                await new Promise(r => setTimeout(r, 200));
            } catch(e) {
                console.error(e);
            }
        }

        this.importingAllPage = false;
        this.pageImportProgress = '';
        this.showToast(`✓ Se han importado ${count} programas con éxito`);
        if (this.importQueue.length > 0) {
            this.processQueue();
        }
    },

    importSingle(item) {
        this.enqueueImport(item);
    },

    isItemQueued(slug) {
        return this.importQueue.some(it => it.slug === slug);
    },

    enqueueImport(item) {
        if (this.importingSlug === item.slug || this.isItemQueued(item.slug)) {
            return;
        }
        if (!this.importingSlug && !this.importingAllPage) {
            this.executeImport(item);
        } else {
            this.importQueue.push(item);
            this.showToast(`🕒 "${item.name || item.title || item.slug}" en cola segura (${this.importQueue.length} en espera)`);
        }
    },

    async processQueue() {
        if (this.importQueue.length === 0 || this.importingSlug || this.importingAllPage) return;
        const nextItem = this.importQueue.shift();
        if (nextItem) {
            await this.executeImport(nextItem);
        }
    },

    async executeImport(item) {
        this.importingSlug = item.slug;
        try {
            const res = await fetch('{{ route('admin.scraper.import-single') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url_or_slug: item.url || item.slug, download_images: true })
            });
            const data = await res.json();
            if (data.success) {
                item.is_imported = true;
                item.has_update = false;
                item.local_version = item.version;
                this.showToast('✓ ' + data.message);
                if (this.viewingUpdatesOnly) {
                    this.pendingUpdates = this.pendingUpdates.filter(it => it.slug !== item.slug);
                    this.updatesCount = this.pendingUpdates.length;
                } else {
                    this.fetchUpdatesCountAsync();
                }
            } else {
                this.showToast('⚠️ ' + (data.message || 'Error al importar'));
            }
        } catch(e) {
            this.showToast('❌ Error de conexión al importar');
        } finally {
            this.importingSlug = null;
            if (!this.importingAllPage && this.importQueue.length > 0) {
                setTimeout(() => {
                    this.processQueue();
                }, 300);
            }
        }
    },

    async syncCats() {
        this.syncingCategories = true;
        try {
            const res = await fetch('{{ route('admin.scraper.sync-categories') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.showToast('✓ ' + data.message);
                setTimeout(() => location.reload(), 1200);
            }
        } catch(e) {
            alert('Error al sincronizar categorías');
        } finally {
            this.syncingCategories = false;
        }
    },

    syncingUpdates: false,
    async syncUpdatesNow() {
        this.syncingUpdates = true;
        try {
            const res = await fetch('{{ route('admin.scraper.sync-updates') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.showToast('✓ ' + data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                alert(data.message || 'Error al sincronizar novedades');
            }
        } catch(e) {
            alert('Error de conexión al sincronizar novedades');
        } finally {
            this.syncingUpdates = false;
        }
    },

    getDisplayedTorrentApps() {
        if (this.viewingTorrentUpdatesOnly) return this.torrentPendingUpdates;
        if (this.torrentHasSearched) return this.torrentSearchResults;
        return this.torrentApps;
    },

    async fetchTorrentUpdatesCountAsync(refresh = false) {
        try {
            const url = '{{ route('admin.scraper.torrentmac.pending-updates') }}?pages=2' + (refresh ? '&refresh=1' : '');
            const res = await fetch(url);
            const data = await res.json();
            if (data.success) {
                this.torrentUpdatesCount = data.count;
                if (this.viewingTorrentUpdatesOnly) {
                    this.torrentPendingUpdates = data.items;
                }
            }
        } catch(e) {
            console.warn('Could not fetch torrent pending updates count', e);
        }
    },

    async toggleTorrentPendingUpdates() {
        if (this.viewingTorrentUpdatesOnly) {
            this.viewingTorrentUpdatesOnly = false;
            this.torrentCategory = 'all';
            this.torrentCategoryName = 'Todos los Torrents';
            await this.loadTorrentmac(1);
            return;
        }

        this.loadingTorrentUpdates = true;
        this.torrentHasSearched = false;
        this.torrentSearchQuery = '';
        this.torrentSearchResults = [];
        this.viewingTorrentUpdatesOnly = true;

        try {
            const res = await fetch('{{ route('admin.scraper.torrentmac.pending-updates') }}?pages=2&refresh=1');
            const data = await res.json();
            if (data.success) {
                this.torrentPendingUpdates = data.items;
                this.torrentUpdatesCount = data.count;
                document.getElementById('torrentmac-results-header')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                alert(data.message || 'Error al obtener actualizaciones de TorrentMac');
            }
        } catch(e) {
            console.error(e);
            alert('Error al conectar para buscar actualizaciones de TorrentMac');
        } finally {
            this.loadingTorrentUpdates = false;
        }
    },

    async syncTorrentUpdatesNow() {
        this.torrentSyncing = true;
        try {
            const res = await fetch('{{ route('admin.scraper.torrentmac.sync-latest') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ pages: 2, download_images: true })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast('✓ ' + data.message);
                await this.fetchTorrentUpdatesCountAsync(true);
                if (this.viewingTorrentUpdatesOnly) {
                    await this.toggleTorrentPendingUpdates();
                } else {
                    await this.loadTorrentmac(this.torrentCurrentPage);
                }
            } else {
                alert(data.message || 'Error en sincronización');
            }
        } catch(e) {
            alert('Error al sincronizar TorrentMac');
        } finally {
            this.torrentSyncing = false;
        }
    },

    async doTorrentSearch(page = 1) {
        if (this.torrentSearchQuery.trim().length < 2) return;
        this.torrentSearching = true;
        this.torrentHasSearched = true;
        this.viewingTorrentUpdatesOnly = false;
        try {
            const res = await fetch('{{ route('admin.scraper.torrentmac.search') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ q: this.torrentSearchQuery, page: page })
            });
            const data = await res.json();
            if (data.success) {
                this.torrentSearchResults = data.results;
                this.torrentSearchTotal = data.total;
                if (data.pagination) {
                    this.torrentCurrentPage = data.pagination.current_page;
                    this.torrentTotalPages = data.pagination.total_pages;
                    this.torrentHasNext = data.pagination.has_next;
                    this.torrentHasPrev = data.pagination.has_prev;
                }
            } else {
                this.showToast('✗ ' + (data.message || 'Error al buscar en TorrentMac'));
            }
        } catch(e) {
            console.error(e);
            this.showToast('✗ Error de conexión con TorrentMac');
        } finally {
            this.torrentSearching = false;
        }
    },

    clearTorrentSearch() {
        this.torrentSearchQuery = '';
        this.torrentHasSearched = false;
        this.torrentSearchResults = [];
        this.torrentSearchTotal = 0;
        this.viewingTorrentUpdatesOnly = false;
        this.loadTorrentmac(1);
    },

    async goToTorrentPage(page) {
        if (page < 1 || (this.torrentTotalPages > 1 && page > this.torrentTotalPages) || this.torrentLoading || this.torrentSearching) return;
        if (this.torrentHasSearched) {
            await this.doTorrentSearch(page);
        } else {
            await this.loadTorrentmac(page);
        }
        document.getElementById('torrentmac-results-header')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    getTorrentPageNumbers() {
        const current = this.torrentCurrentPage;
        const total = this.torrentTotalPages;
        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }
        if (current <= 4) {
            return [1, 2, 3, 4, 5, '...', total];
        }
        if (current >= total - 3) {
            return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        }
        return [1, '...', current - 1, current, current + 1, '...', total];
    },

    async loadTorrentmac(page = 1, category = null) {
        this.viewingTorrentUpdatesOnly = false;
        if (category !== null) {
            this.torrentCategory = category;
            const found = this.torrentCategories.find(c => c.slug === category);
            this.torrentCategoryName = found ? found.name : category;
            this.torrentHasSearched = false;
            this.torrentSearchQuery = '';
            this.torrentSearchResults = [];
        }
        this.torrentLoading = true;
        try {
            const res = await fetch(`{{ route('admin.scraper.torrentmac.latest') }}?page=${page}&category=${this.torrentCategory}`);
            const data = await res.json();
            if (data.success) {
                this.torrentApps = data.items;
                this.torrentCurrentPage = data.pagination.current_page;
                this.torrentTotalPages = data.pagination.total_pages;
                this.torrentHasNext = data.pagination.has_next;
                this.torrentHasPrev = data.pagination.has_prev;
            } else {
                this.showToast('✗ ' + (data.message || 'Error al cargar TorrentMac'));
            }
        } catch(e) {
            this.showToast('✗ Error de conexión con TorrentMac');
        } finally {
            this.torrentLoading = false;
        }
    },

    importTorrentApp(app) {
        this.enqueueTorrentImport(app);
    },

    isTorrentQueued(url) {
        return this.torrentQueue.some(it => it.url === url);
    },

    enqueueTorrentImport(app) {
        if (this.torrentImportingUrl === app.url || this.isTorrentQueued(app.url)) {
            return;
        }
        if (!this.torrentImportingUrl) {
            this.executeTorrentImport(app);
        } else {
            this.torrentQueue.push(app);
            this.showToast(`🕒 Torrent "${app.title || app.name || 'App'}" en cola segura (${this.torrentQueue.length} en espera)`);
        }
    },

    async processTorrentQueue() {
        if (this.torrentQueue.length === 0 || this.torrentImportingUrl) return;
        const nextApp = this.torrentQueue.shift();
        if (nextApp) {
            await this.executeTorrentImport(nextApp);
        }
    },

    async executeTorrentImport(app) {
        this.torrentImportingUrl = app.url;
        try {
            const res = await fetch('{{ route('admin.scraper.torrentmac.import-single') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: app.url, download_images: true })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast('✓ ' + data.message);
                app.in_database = true;
                app.has_torrent = true;
                app.is_dual = data.app?.is_dual;
                app.has_update = false;
                app.local_version = data.app?.version || app.version;
                
                if (this.viewingTorrentUpdatesOnly) {
                    this.torrentPendingUpdates = this.torrentPendingUpdates.filter(it => (it.url !== app.url && it.slug !== app.slug));
                    this.torrentUpdatesCount = this.torrentPendingUpdates.length;
                } else {
                    this.fetchTorrentUpdatesCountAsync(true);
                }
            } else {
                this.showToast('⚠️ ' + (data.message || 'Error al importar torrent'));
            }
        } catch(e) {
            this.showToast('❌ Error de conexión al importar torrent');
        } finally {
            this.torrentImportingUrl = null;
            if (this.torrentQueue.length > 0) {
                setTimeout(() => {
                    this.processTorrentQueue();
                }, 300);
            }
        }
    },

    async syncTorrentmacNow() {
        this.torrentSyncing = true;
        try {
            const res = await fetch('{{ route('admin.scraper.torrentmac.sync-latest') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ pages: 1, download_images: true })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast('✓ ' + data.message);
                await this.fetchTorrentUpdatesCountAsync();
                await this.loadTorrentmac(this.torrentCurrentPage);
            } else {
                alert(data.message || 'Error en sincronización');
            }
        } catch(e) {
            alert('Error al sincronizar TorrentMac');
        } finally {
            this.torrentSyncing = false;
        }
    },

    async setTorrentStorageDisk(disk) {
        if (this.torrentStorageTarget === disk) return;
        try {
            const res = await fetch('{{ route('admin.scraper.torrentmac.storage-settings') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ storage_disk: disk })
            });
            const data = await res.json();
            if (data.success) {
                this.torrentStorageTarget = data.storage_disk;
                this.effectiveStorageDisk = data.effective_disk;
                this.showToast('✓ ' + data.message);
            } else {
                alert(data.message || 'Error al cambiar destino de almacenamiento');
            }
        } catch(e) {
            alert('Error al conectar con el servidor');
        }
    },

    toastText: '',
    toastVisible: false,
    showToast(msg) {
        this.toastText = msg;
        this.toastVisible = true;
        setTimeout(() => this.toastVisible = false, 4000);
    }
    };
}
</script>
@endpush
@endsection
