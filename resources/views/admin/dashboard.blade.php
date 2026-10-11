<x-admin-layout>
    @section('title', 'Dashboard')
    @section('header', 'Dashboard')

    <div class="space-y-8" x-data="{ showResetModal: false }">
        <!-- Hero Banner with Warm Ambient Glow -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1E1914] via-[#1A1511] to-[#141210] border border-[#2E2820] p-6 md:p-8 shadow-2xl">
            <!-- Ambient Radial Glow in Top Right -->
            <div class="absolute -right-16 -top-16 w-80 h-80 pointer-events-none rounded-full"
                 style="background: radial-gradient(circle at 75% 25%, rgba(217, 77, 11, 0.22) 0%, rgba(217, 77, 11, 0.04) 50%, transparent 75%); filter: blur(35px);"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-primary/20 text-primary border border-primary/30 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                            <span>HackMac.cc Admin Console</span>
                        </span>
                        <span class="text-xs text-[#8C847A] font-mono">&bull; macOS Software Portal v2.0</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight">
                        ¡Bienvenido, {{ auth()->user()->name ?? 'Administrador' }}!
                    </h1>

                    <p class="text-xs sm:text-sm text-[#A39B91] mt-1.5 max-w-2xl leading-relaxed">
                        Gestiona y supervisa el catálogo de software para Mac, contadores de descargas, sincronización automatizada y optimizaciones SEO.
                    </p>
                </div>

                <!-- Action Buttons Header -->
                <div class="flex items-center gap-2.5 flex-wrap flex-shrink-0">
                    <a href="{{ route('admin.applications.create') }}"
                       class="btn-primary text-xs px-4 py-2.5 gap-2 shadow-lg shadow-primary/25 hover:scale-[1.02] transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Publicar Programa</span>
                    </a>

                    <a href="{{ route('admin.scraper') }}"
                       class="px-3.5 py-2.5 rounded-xl bg-[#1A2530] hover:bg-[#203040] text-[#38BDF8] border border-[#38BDF8]/30 text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-all hover:scale-[1.02]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Scraper Auto</span>
                    </a>

                    <a href="{{ route('home') }}" target="_blank"
                       class="btn-secondary text-xs px-3.5 py-2.5 gap-1.5 hover:text-white transition-colors"
                       title="Ver el sitio público en una nueva pestaña">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Ver Web</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 6 Executive KPI Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5 sm:gap-4">
            <!-- 1. Total Apps -->
            <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col justify-between hover:border-primary/50 transition-all hover:shadow-lg group">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider">Catálogo Total</span>
                    <div class="w-7 h-7 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-black text-white font-mono tracking-tight">{{ number_format($stats['total_apps']) }}</p>
                    <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                        <span class="text-success font-semibold">{{ $stats['published_apps'] }} activas</span>
                        <span class="text-[#5C554D]">&bull;</span>
                        <span class="text-warning">{{ $stats['pending_apps'] }} borr.</span>
                    </div>
                </div>
            </div>

            <!-- 2. Downloads -->
            <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col justify-between hover:border-emerald-500/50 transition-all hover:shadow-lg group">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider">Descargas</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-black text-emerald-400 font-mono tracking-tight">{{ $stats['formatted_downloads'] }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-[10px] text-[#8C847A]">{{ number_format($stats['total_downloads']) }} exactas</span>
                        <button type="button" @click="showResetModal = true"
                                class="text-[9px] text-[#A39B91] hover:text-warning underline transition-colors"
                                title="Gestionar o reiniciar contadores">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Featured Apps -->
            <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col justify-between hover:border-amber-500/50 transition-all hover:shadow-lg group">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider">Destacados</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-black text-amber-400 font-mono tracking-tight">{{ number_format($stats['featured_apps']) }}</p>
                    <a href="{{ route('admin.applications', ['status' => 'featured']) }}" class="text-[10px] text-amber-300/80 hover:text-amber-300 mt-1 block font-medium transition-colors">
                        En carrusel portada &rarr;
                    </a>
                </div>
            </div>

            <!-- 4. Categories -->
            <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col justify-between hover:border-blue-500/50 transition-all hover:shadow-lg group">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider">Categorías</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-black text-white font-mono tracking-tight">{{ number_format($stats['total_categories']) }}</p>
                    <a href="{{ route('admin.categories') }}" class="text-[10px] text-blue-400 hover:text-blue-300 mt-1 block font-medium transition-colors">
                        Gestionar secciones &rarr;
                    </a>
                </div>
            </div>

            <!-- 5. Updates this week -->
            <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col justify-between hover:border-purple-500/50 transition-all hover:shadow-lg group">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider">Esta Semana</span>
                    <div class="w-7 h-7 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-black text-purple-300 font-mono tracking-tight">{{ number_format($stats['updates_this_week']) }}</p>
                    <span class="text-[10px] text-[#8C847A] mt-1 block">Nuevas versiones</span>
                </div>
            </div>

            <!-- 6. Gemini AI Status -->
            <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col justify-between hover:border-sky-500/50 transition-all hover:shadow-lg group">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider">Motor AI</span>
                    <div class="w-7 h-7 rounded-lg {{ $systemInfo['gemini_configured'] ? 'bg-sky-500/10 border-sky-500/20 text-sky-400' : 'bg-warning/10 border-warning/20 text-warning' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-lg font-black font-mono tracking-tight {{ $systemInfo['gemini_configured'] ? 'text-sky-400' : 'text-warning' }}">
                        {{ $systemInfo['gemini_configured'] ? 'Gemini Activo' : 'Sin Clave' }}
                    </p>
                    <a href="{{ route('admin.settings') }}" class="text-[10px] text-[#8C847A] hover:text-white mt-1 block transition-colors">
                        {{ $systemInfo['gemini_configured'] ? 'Listo para redacción &rarr;' : 'Configurar API Key &rarr;' }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Dual Analytical Columns (8 Cols + 4 Cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: Top Apps & Recent Activity (8 Cols) -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Top 5 Most Downloaded Applications -->
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between pb-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-[3px] bg-primary inline-block"></span>
                            <h2 class="text-xs font-bold text-white uppercase tracking-wider">Top 5 Más Descargados (Líderes)</h2>
                        </div>
                        <span class="text-[11px] text-[#8C847A]">Basado en descargas acumuladas</span>
                    </div>

                    <div class="card overflow-hidden bg-[#141210] border-[#26211B] shadow-xl">
                        @php
                            $maxDownload = max($topDownloaded->max('downloads') ?? 1, 1);
                        @endphp

                        <div class="divide-y divide-[#241F1A]">
                            @forelse($topDownloaded as $index => $app)
                                @php
                                    $rank = $loop->iteration;
                                    $rankColors = [
                                        1 => 'from-[#F59E0B] to-[#D97706] text-black border-[#FBBF24]',
                                        2 => 'from-[#94A3B8] to-[#64748B] text-black border-[#CBD5E1]',
                                        3 => 'from-[#B45309] to-[#78350F] text-white border-[#D97706]',
                                    ];
                                    $rankBadge = $rankColors[$rank] ?? 'bg-[#221C16] text-[#A39B91] border-[#332A22]';
                                    $percent = min(100, max(8, round(($app->downloads / $maxDownload) * 100)));
                                @endphp

                                <div class="p-4 flex items-center justify-between hover:bg-[#181410] transition-colors gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                        <!-- Rank Medal -->
                                        <div class="w-6 h-6 rounded-md font-mono text-[11px] font-black flex items-center justify-center flex-shrink-0 shadow-sm border {{ $rank <= 3 ? 'bg-gradient-to-br ' . $rankBadge : $rankBadge }}">
                                            #{{ $rank }}
                                        </div>

                                        <!-- App Squircle Icon -->
                                        <div class="w-11 h-11 rounded-[13px] bg-gradient-to-b from-[#2B241C] to-[#181410] border border-[#3E342A] p-0.5 shadow-md flex-shrink-0 flex items-center justify-center overflow-hidden">
                                            <img src="{{ $app->icon_url }}" alt="{{ $app->name }}"
                                                 class="w-full h-full object-cover rounded-[11px]"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-full h-full bg-primary/20 text-primary font-bold text-sm rounded-[11px] items-center justify-center" style="display: none;">
                                                {{ strtoupper(substr($app->name, 0, 1)) }}
                                            </div>
                                        </div>

                                        <!-- Name & Category & Relative Volume -->
                                        <div class="min-w-0 flex-1 pr-2">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.applications.edit', $app) }}"
                                                   class="text-xs font-bold text-white hover:text-primary transition-colors truncate">
                                                    {{ $app->name }}
                                                </a>
                                                <span class="text-[10px] font-mono text-[#8C847A] hidden sm:inline-block">
                                                    {{ $app->version ?? 'v1.0' }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-3 mt-1.5">
                                                <!-- Visual Bar of relative volume -->
                                                <div class="w-28 sm:w-36 h-1.5 rounded-full bg-[#201B16] overflow-hidden flex-shrink-0">
                                                    <div class="h-full bg-gradient-to-r from-primary to-amber-500 rounded-full"
                                                         style="width: {{ $percent }}%;"></div>
                                                </div>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#1F1B16] text-[#A39B91] border border-[#2D261E] truncate max-w-[120px]">
                                                    {{ $app->category->name ?? 'General' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Downloads & Edit Action -->
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <div class="text-right">
                                            <span class="text-xs font-extrabold text-emerald-400 font-mono block">
                                                {{ number_format($app->downloads) }}
                                            </span>
                                            <span class="text-[9px] uppercase tracking-wider text-[#736B63] block">
                                                Descargas
                                            </span>
                                        </div>

                                        <a href="{{ route('admin.applications.edit', $app) }}"
                                           class="btn-secondary text-xs px-2.5 py-1.5 gap-1 hover:text-white"
                                           title="Editar programa">
                                            <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-[#8C847A]">
                                    No hay programas registrados todavía.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Latest Catalog Updates Table -->
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between pb-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-[3px] bg-primary inline-block"></span>
                            <h2 class="text-xs font-bold text-white uppercase tracking-wider">Últimos Programas Actualizados</h2>
                        </div>
                        <a href="{{ route('admin.applications') }}"
                           class="text-xs text-primary hover:text-primary-hover font-semibold transition-colors flex items-center gap-1">
                            <span>Ver catálogo completo ({{ number_format($stats['total_apps']) }})</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="card overflow-hidden bg-[#141210] border-[#26211B] shadow-xl">
                        <div class="divide-y divide-[#241F1A]">
                            @forelse($recentApps as $app)
                                <div class="p-4 flex items-center justify-between hover:bg-[#181410] transition-colors gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-11 h-11 rounded-[13px] bg-gradient-to-b from-[#2B241C] to-[#181410] border border-[#3E342A] p-0.5 shadow-md flex-shrink-0 flex items-center justify-center overflow-hidden">
                                            <img src="{{ $app->icon_url }}" alt="{{ $app->name }}"
                                                 class="w-full h-full object-cover rounded-[11px]"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-full h-full bg-primary/20 text-primary font-bold text-sm rounded-[11px] items-center justify-center" style="display: none;">
                                                {{ strtoupper(substr($app->name, 0, 1)) }}
                                            </div>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.applications.edit', $app) }}"
                                                   class="text-xs font-bold text-white hover:text-primary transition-colors truncate">
                                                    {{ $app->name }}
                                                </a>
                                                @if($app->featured)
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-0.5">
                                                        ★ Featured
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#1F1B16] text-[#A39B91] border border-[#2D261E]">
                                                    {{ $app->category->name ?? 'General' }}
                                                </span>
                                                <span class="text-[10px] font-mono text-[#8C847A]">
                                                    {{ $app->version ?? 'v1.0' }} &bull; {{ $app->formatted_size }}
                                                </span>
                                                <span class="text-[10px] text-[#736B63] hidden md:inline-block">
                                                    &bull; {{ $app->updated_at?->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <!-- Status Badge -->
                                        @if($app->published)
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                Publicado
                                            </span>
                                        @else
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                                Borrador
                                            </span>
                                        @endif

                                        <a href="{{ route('admin.applications.edit', $app) }}"
                                           class="btn-secondary text-xs px-3 py-1.5 gap-1 hover:text-white">
                                            <span>Editar</span>
                                            <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-[#8C847A]">
                                    No hay programas registrados todavía.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Distribution, Quick Tools & System Health (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- 1. Category Distribution -->
                <div class="card p-5 bg-[#141210] border-[#26211B] shadow-xl">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#26211B]">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Distribución por Categorías</span>
                        </h3>
                        <a href="{{ route('admin.categories') }}" class="text-[11px] text-primary hover:underline">
                            Ver todas
                        </a>
                    </div>

                    <div class="space-y-3.5">
                        @php
                            $totalAppsCount = max($stats['total_apps'], 1);
                        @endphp

                        @foreach($categoriesStats as $cat)
                            @php
                                $pct = round(($cat->applications_count / $totalAppsCount) * 100);
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="font-medium text-white truncate max-w-[160px]">{{ $cat->name }}</span>
                                    <span class="font-mono text-[#8C847A] text-[11px]">
                                        {{ number_format($cat->applications_count) }} ({{ $pct }}%)
                                    </span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-[#201B16] overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-primary to-amber-500 rounded-full"
                                         style="width: {{ max($pct, 2) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Quick Command & Maintenance Hub -->
                <div class="card p-5 bg-[#141210] border-[#26211B] shadow-xl">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4 pb-2 border-b border-[#26211B]">
                        Centro de Control Rápido
                    </h3>

                    <div class="space-y-2.5">
                        <!-- Action: New App -->
                        <a href="{{ route('admin.applications.create') }}"
                           class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] hover:bg-[#221C16] border border-[#2B241C] hover:border-primary/40 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">Publicar Software</p>
                                    <p class="text-[10px] text-[#8C847A]">Nuevo instalador DMG o ZIP</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-[#6B635A] group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Action: Scraper -->
                        <a href="{{ route('admin.scraper') }}"
                           class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] hover:bg-[#221C16] border border-[#2B241C] hover:border-[#38BDF8]/40 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#38BDF8]/20 text-[#38BDF8] flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">Scraper Automático</p>
                                    <p class="text-[10px] text-[#8C847A]">Comprobar e importar apps</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-[#6B635A] group-hover:text-[#38BDF8] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Action: Reset Downloads -->
                        <button type="button" @click="showResetModal = true"
                                class="w-full flex items-center justify-between p-3 rounded-xl bg-[#1A1612] hover:bg-[#221C16] border border-[#2B241C] hover:border-warning/40 transition-all group text-left cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-warning/20 text-warning flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">Resetear Descargas</p>
                                    <p class="text-[10px] text-[#8C847A]">Limpiar contadores para producción</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-[#6B635A] group-hover:text-warning transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Action: SEO & Settings -->
                        <a href="{{ route('admin.settings') }}"
                           class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] hover:bg-[#221C16] border border-[#2B241C] hover:border-emerald-500/40 transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">Ajustes SEO & AI</p>
                                    <p class="text-[10px] text-[#8C847A]">Metadatos, API Keys y Redes</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-[#6B635A] group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- 3. Server & Engine Diagnostics -->
                <div class="card p-5 bg-[#141210] border-[#26211B] shadow-xl">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4 pb-2 border-b border-[#26211B] flex items-center justify-between">
                        <span>Estado del Servidor</span>
                        <span class="inline-flex items-center gap-1.5 text-[10px] text-success font-mono font-bold">
                            <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                            ONLINE
                        </span>
                    </h3>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-[#221C16]">
                            <span class="text-[#8C847A]">Entorno:</span>
                            <span class="font-mono text-white px-2 py-0.5 rounded bg-[#1C1814] border border-[#2D251D]">
                                {{ $systemInfo['environment'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-[#221C16]">
                            <span class="text-[#8C847A]">PHP Runtime:</span>
                            <span class="font-mono text-white font-bold">v{{ $systemInfo['php_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-[#221C16]">
                            <span class="text-[#8C847A]">Laravel Framework:</span>
                            <span class="font-mono text-white font-bold">v{{ $systemInfo['laravel_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-[#221C16]">
                            <span class="text-[#8C847A]">Base de Datos:</span>
                            <span class="font-mono text-primary font-bold uppercase">{{ $systemInfo['db_driver'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-[#221C16]">
                            <span class="text-[#8C847A]">Storage Enlazado:</span>
                            <span class="font-mono {{ $systemInfo['storage_symlink'] ? 'text-success' : 'text-danger' }} font-bold">
                                {{ $systemInfo['storage_symlink'] ? 'Conectado (OK)' : 'Falta symlink' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-[#8C847A]">Sitemap & Robots:</span>
                            <a href="{{ url('sitemap.xml') }}" target="_blank" class="font-mono text-primary hover:underline text-[11px]">
                                sitemap.xml &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Insights & User Demand (Missing Searches + Broken Links) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 1. Missing Searches -->
            <div class="card p-5 bg-[#141210] border-[#26211B] shadow-xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">
                            Búsquedas Sin Resultados (Demanda de Usuarios)
                        </h3>
                    </div>
                    <span class="text-[10px] text-[#8C847A] font-semibold">Oportunidades de catálogo</span>
                </div>

                @if($topMissingSearches->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($topMissingSearches as $search)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#1A1612] border border-[#2B241C] text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-amber-400 font-mono">🔍</span>
                                    <span class="font-bold text-white truncate max-w-xs">{{ $search->query }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-400/10 text-amber-400 border border-amber-400/20 text-[10px] font-mono font-bold">
                                        {{ $search->total }} {{ $search->total === 1 ? 'búsqueda' : 'búsquedas' }}
                                    </span>
                                    <a href="{{ route('admin.scraper') }}" class="btn-secondary text-[10px] py-1 px-2 text-primary hover:text-white" title="Importar con Scraper">
                                        Buscar en Scraper &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-[#8C847A]">
                        <span>No hay búsquedas fallidas registradas. ¡Tu catálogo está cubriendo la demanda!</span>
                    </div>
                @endif
            </div>

            <!-- 2. Broken Link Reports -->
            <div class="card p-5 bg-[#141210] border-[#26211B] shadow-xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">
                            Reportes de Enlaces ({{ $unresolvedReportsCount }} Pendientes)
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($unresolvedReportsCount > 0)
                            <span class="px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold">
                                Requiere Atención
                            </span>
                        @endif
                        <a href="{{ route('admin.reports') }}" class="text-[11px] text-primary hover:underline font-semibold">
                            Ver todos &rarr;
                        </a>
                    </div>
                </div>

                @if($recentBrokenReports->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($recentBrokenReports as $report)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#1A1612] border border-[#2B241C] text-xs">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.applications.edit', $report->application_id) }}" class="font-bold text-white hover:text-primary transition-colors truncate max-w-xs">
                                            {{ $report->application?->name ?? 'App #' . $report->application_id }}
                                        </a>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold uppercase {{ $report->status === 'resolved' ? 'bg-success/20 text-success' : 'bg-rose-500/20 text-rose-400' }}">
                                            {{ $report->type }}
                                        </span>
                                    </div>
                                    @if($report->notes)
                                        <p class="text-[11px] text-[#A39B91] truncate mt-0.5">{{ $report->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($report->status !== 'resolved')
                                        <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                            @csrf
                                            <button type="submit" class="btn-primary text-[10px] py-1 px-2.5 cursor-pointer">
                                                Resolver
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-success font-semibold">✓ Resuelto</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-xs text-[#8C847A]">
                        <span>✓ No hay reportes de enlaces pendientes. ¡Todos los enlaces funcionan bien!</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reset Downloads Confirmation Modal (Alpine.js) -->
        <div x-show="showResetModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @keydown.escape.window="showResetModal = false">
            <div class="card max-w-md w-full bg-[#181410] border-[#382E24] p-6 shadow-2xl space-y-4"
                 @click.away="showResetModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-[#2D251D]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-warning/20 text-warning flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white">Gestionar Contadores de Descarga</h3>
                    </div>
                    <button type="button" @click="showResetModal = false" class="text-[#8C847A] hover:text-white text-lg font-bold">
                        &times;
                    </button>
                </div>

                <p class="text-xs text-[#A39B91] leading-relaxed">
                    Al importar aplicaciones al catálogo se configuran cifras de descarga iniciales. Elige cómo deseas gestionarlas para tu portal HackMac.cc:
                </p>

                <div class="space-y-3 pt-2">
                    <!-- Option 1: Reset to 0 (Production Clean) -->
                    <form action="{{ route('admin.applications.reset-downloads') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="zero">
                        <button type="submit"
                                onclick="return confirm('¿Confirmas reiniciar a 0 las descargas de TODOS los programas?');"
                                class="w-full p-3 rounded-xl bg-danger/10 hover:bg-danger/20 border border-danger/30 text-left transition-colors group">
                            <p class="text-xs font-bold text-danger group-hover:underline">Reiniciar todo a 0 (Recomendado para Producción)</p>
                            <p class="text-[11px] text-[#A39B91] mt-0.5">Todas las aplicaciones comenzarán con 0 descargas reales.</p>
                        </button>
                    </form>

                    <!-- Option 2: Randomize realistic numbers -->
                    <form action="{{ route('admin.applications.reset-downloads') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="randomize">
                        <button type="submit"
                                onclick="return confirm('¿Deseas recalibrar valores moderados aleatorios (50 a 1,200 por app)?');"
                                class="w-full p-3 rounded-xl bg-[#221C16] hover:bg-[#2C241D] border border-[#3A3025] text-left transition-colors group">
                            <p class="text-xs font-bold text-warning group-hover:underline">Recalibrar valores moderados (50 - 1,200)</p>
                            <p class="text-[11px] text-[#A39B91] mt-0.5">Genera métricas moderadas para dar sensación de actividad orgánica.</p>
                        </button>
                    </form>
                </div>

                <div class="pt-2 text-right">
                    <button type="button" @click="showResetModal = false" class="btn-secondary text-xs px-4 py-2">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
