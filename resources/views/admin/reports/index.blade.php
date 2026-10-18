@extends('layouts.admin')

@section('title', 'Monitor de Enlaces & Auto-Reparación')
@section('page-title', 'Monitor de Enlaces Caídos')

@section('content')
<div x-data="reportsManager()" class="space-y-6 max-w-[1600px] mx-auto w-full">

    <!-- Top View Switcher (Reportes de Usuarios VS Escáner en Vivo Automático) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[#14110E] border border-[#2B241C] p-4 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-amber-600 flex items-center justify-center text-white shadow-lg shadow-primary/20 flex-shrink-0">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-white tracking-tight flex items-center gap-2">
                    <span>Centro de Salud de Enlaces</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Sistema en línea"></span>
                </h1>
                <p class="text-xs text-[#8C847A]">Monitorea enlaces en vivo o revisa los reportes generados por usuarios.</p>
            </div>
        </div>

        <!-- View Switcher Tabs -->
        <div class="flex items-center gap-2 bg-[#0E0D0B] p-1.5 rounded-xl border border-[#2B241C]">
            <button type="button"
                    @click="mainTab = 'reports'"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                    :class="mainTab === 'reports' ? 'bg-[#262019] text-white shadow-sm border border-[#3D3224]' : 'text-[#8C847A] hover:text-white'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Reportes de Usuarios</span>
                @if($totalPending > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-danger/20 text-danger border border-danger/30">
                        {{ $totalPending }}
                    </span>
                @endif
            </button>

            <button type="button"
                    @click="mainTab = 'scanner'"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
                    :class="mainTab === 'scanner' ? 'bg-gradient-to-r from-primary/30 to-amber-500/20 text-primary border border-primary/40 shadow-sm' : 'text-[#8C847A] hover:text-white'">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                </span>
                <span>📡 Escáner en Vivo Automático</span>
                <span x-show="liveDeadCount > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-danger text-white" x-text="liveDeadCount"></span>
            </button>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="bg-success/15 border border-success/30 text-success px-4 py-3 rounded-2xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- VIEW 1: USER REPORTS LIST                                                 -->
    <!-- ========================================================================= -->
    <div x-show="mainTab === 'reports'" class="space-y-4">
        <!-- Sub-filter header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#14110E] border border-[#2B241C] p-4 rounded-2xl">
            <div>
                <h2 class="text-xs font-bold text-white uppercase tracking-wider">Reportes Enviados por Visitantes</h2>
                <p class="text-[11px] text-[#8C847A]">Reportes generados cuando un usuario hace clic en "¿Enlace caído?" en el portal.</p>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 bg-[#0F0E0C] p-1.5 rounded-xl border border-[#2B241C]">
                <a href="{{ route('admin.reports', ['status' => 'all']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $status === 'all' ? 'bg-[#262019] text-white shadow-sm' : 'text-[#8C847A] hover:text-white' }}">
                    Todos ({{ $totalReports }})
                </a>
                <a href="{{ route('admin.reports', ['status' => 'pending']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $status === 'pending' ? 'bg-danger/20 text-danger border border-danger/30 font-bold' : 'text-[#8C847A] hover:text-danger' }}">
                    Pendientes ({{ $totalPending }})
                </a>
                <a href="{{ route('admin.reports', ['status' => 'resolved']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $status === 'resolved' ? 'bg-success/20 text-success border border-success/30 font-bold' : 'text-[#8C847A] hover:text-success' }}">
                    Resueltos ({{ $totalResolved }})
                </a>
            </div>
        </div>

        <!-- Reports Table Card -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#181410] border-b border-[#262019] text-[#8C847A] uppercase text-[10px] tracking-wider font-bold">
                            <th class="py-3 px-4">Programa &amp; Enlace Caído</th>
                            <th class="py-3 px-4">Tipo de Falla</th>
                            <th class="py-3 px-4">Detalles del Usuario</th>
                            <th class="py-3 px-4">Fecha &amp; IP</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones de Reparación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#211B15] text-[#D8CFBE]">
                        @forelse($reports as $rep)
                            <tr class="hover:bg-[#1A1612] transition-colors {{ $rep->status === 'pending' ? 'bg-[#181210]/40' : '' }}"
                                id="report-row-{{ $rep->id }}">
                                <!-- App & Broken URL -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-[#201C18] border border-[#352C22] p-0.5 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            <img src="{{ $rep->application?->icon_url ?? asset('images/default-icon.png') }}"
                                                 alt="{{ $rep->application?->name ?? 'App' }}"
                                                 class="w-full h-full object-contain rounded-lg"
                                                 onerror="this.style.display='none'">
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $rep->application ? route('app', $rep->application->slug) : '#' }}" target="_blank"
                                               class="font-bold text-white hover:text-primary transition-colors block truncate max-w-[240px] text-xs">
                                                {{ $rep->application?->name ?? 'Programa Eliminado' }}
                                            </a>
                                            <span class="text-[10px] font-mono text-[#8C847A]">
                                                v{{ $rep->application?->version ?? 'N/A' }}
                                            </span>
                                            @if($rep->application && ($rep->application->download_url || $rep->application->torrent_url || $rep->application->magnet_link))
                                                <div class="mt-1 flex items-center gap-1 text-[10px] text-[#8C847A]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-danger flex-shrink-0"></span>
                                                    <span class="truncate max-w-[220px] font-mono text-[#B3AAA0]" title="{{ $rep->application->download_url ?: ($rep->application->torrent_url ?: 'Magnet Link') }}">
                                                        {{ $rep->application->download_url ?: ($rep->application->torrent_url ?: 'Magnet Link') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Type -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @php
                                        $typeStyles = [
                                            'broken_link' => ['bg' => 'bg-danger/10', 'text' => 'text-danger', 'border' => 'border-danger/30', 'label' => 'Enlace Caído'],
                                            'wrong_version' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30', 'label' => 'Versión Incorrecta'],
                                            'file_corrupted' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-400', 'border' => 'border-purple-500/30', 'label' => 'Archivo Dañado'],
                                            'other' => ['bg' => 'bg-[#262019]', 'text' => 'text-[#8C847A]', 'border' => 'border-[#3D3224]', 'label' => 'Otro Problema'],
                                        ];
                                        $curStyle = $typeStyles[$rep->type] ?? $typeStyles['broken_link'];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $curStyle['bg'] }} {{ $curStyle['text'] }} {{ $curStyle['border'] }}">
                                        {{ $curStyle['label'] }}
                                    </span>
                                </td>

                                <!-- User Notes -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-xs text-[#D8CFBE] truncate" title="{{ $rep->notes }}">
                                        {{ $rep->notes ?: 'Sin comentarios adicionales.' }}
                                    </p>
                                </td>

                                <!-- IP & Date -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-[11px] text-[#8C847A]">
                                    <span class="block text-white font-mono text-[10px]">{{ $rep->ip_address ?: 'IP Oculta' }}</span>
                                    <span>{{ $rep->created_at->diffForHumans() }}</span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span id="status-badge-{{ $rep->id }}">
                                        @if($rep->status === 'pending')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-danger/20 text-danger border border-danger/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-danger animate-pulse"></span>
                                                Pendiente
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-success/20 text-success border border-success/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                                Solucionado
                                            </span>
                                        @endif
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        @if($rep->application && $rep->status === 'pending')
                                            <!-- Auto-Repair Button -->
                                            <button type="button"
                                                    @click="autoRepair({{ $rep->id }}, '{{ addslashes($rep->application->name) }}')"
                                                    :disabled="repairingId === {{ $rep->id }}"
                                                    class="px-2.5 py-1 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 hover:text-amber-300 text-[11px] font-bold border border-amber-500/30 transition-all cursor-pointer flex items-center gap-1 shadow-sm"
                                                    title="Auto-reparar buscando en Torrentmac y Haxmac">
                                                <svg x-show="repairingId !== {{ $rep->id }}" class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                                </svg>
                                                <svg x-show="repairingId === {{ $rep->id }}" class="w-3.5 h-3.5 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                <span x-text="repairingId === {{ $rep->id }} ? 'Buscando...' : '⚡ Auto-Reparar'"></span>
                                            </button>

                                            <!-- Search Replacement (1-Click Fix Modal) -->
                                            <button type="button"
                                                    @click="openSearchModal({{ json_encode([
                                                        'id' => $rep->id,
                                                        'status' => $rep->status,
                                                        'notes' => $rep->notes,
                                                        'type' => $rep->type,
                                                        'app_id' => $rep->application->id,
                                                        'name' => $rep->application->name,
                                                        'version' => $rep->application->version,
                                                        'download_url' => $rep->application->download_url,
                                                        'magnet_link' => $rep->application->magnet_link,
                                                    ]) }})"
                                                    class="px-2.5 py-1 rounded-lg bg-sky-500/15 hover:bg-sky-500/25 text-sky-400 hover:text-sky-300 text-[11px] font-bold border border-sky-500/30 transition-all cursor-pointer flex items-center gap-1"
                                                    title="Buscar opciones en vivo con Asistente 1-Click">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                </svg>
                                                <span>Asistente</span>
                                            </button>
                                        @endif

                                        @if($rep->status === 'pending')
                                            <form method="POST" action="{{ route('admin.reports.resolve', $rep->id) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="px-2 py-1 rounded-lg bg-success/15 hover:bg-success/25 text-success text-[11px] font-bold border border-success/30 transition-all cursor-pointer flex items-center gap-1"
                                                        title="Marcar como resuelto">
                                                    <span>✓</span>
                                                </button>
                                            </form>
                                        @endif

                                        @if($rep->application)
                                            <a href="{{ route('admin.applications.edit', $rep->application) }}"
                                               class="p-1.5 rounded-lg text-primary hover:text-white hover:bg-primary/20 transition-colors border border-transparent"
                                               title="Editar aplicación">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif

                                        <form method="POST" action="{{ route('admin.reports.destroy', $rep->id) }}" onsubmit="return confirm('¿Eliminar este reporte?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-[#8C847A] hover:text-danger hover:bg-danger/10 transition-colors cursor-pointer"
                                                    title="Eliminar reporte">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-[#736B63]">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <span class="text-3xl block">🎉</span>
                                        <p class="font-bold text-white text-xs">No hay reportes de usuarios pendientes</p>
                                        <p class="text-[11px] text-[#8C847A]">Puedes usar la pestaña de Escáner en Vivo para verificar el catálogo automáticamente.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div class="p-4 border-t border-[#262019]">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 2: 📡 LIVE LINK HEALTH SCANNER (AUTOMÁTICO SIN ESPERAR REPORTE)      -->
    <!-- ========================================================================= -->
    <div x-show="mainTab === 'scanner'" x-cloak class="space-y-6">

        <!-- Live Scanner Control Center -->
        <div class="bg-[#14110E] border border-[#2B241C] p-6 rounded-2xl shadow-xl space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h2 class="text-sm font-extrabold text-white uppercase tracking-wider">Escáner Activo de Enlaces del Catálogo</h2>
                    </div>
                    <p class="text-xs text-[#8C847A] mt-1">
                        Verifica en tiempo real cada servidor de descarga (HTTP 200, 404, 502, timeouts) de tus programas publicados y repara los caídos en vivo.
                    </p>
                </div>

                <!-- Scanner Trigger Buttons -->
                <div class="flex items-center gap-3 flex-wrap">
                    <button type="button"
                            @click="toggleScanning()"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-lg flex items-center gap-2 cursor-pointer"
                            :class="isScanning 
                                ? 'bg-danger hover:bg-rose-600 text-white shadow-danger/20' 
                                : 'bg-gradient-to-r from-primary to-amber-600 hover:from-primary-hover hover:to-amber-500 text-white shadow-primary/20'">
                        <svg x-show="!isScanning" class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="isScanning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="isScanning ? '⏸ Pausar Escaneo en Vivo' : '▶ Iniciar Escaneo en Vivo'"></span>
                    </button>

                    <button type="button"
                            @click="clearScan()"
                            :disabled="isScanning"
                            class="btn-secondary text-xs px-3.5 py-2.5">
                        Reiniciar
                    </button>
                </div>
            </div>

            <!-- Metrics Counters -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <div class="p-3.5 rounded-xl bg-[#1A1612] border border-[#2B241C]">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider block">Total en Catálogo</span>
                    <span class="text-xl font-extrabold text-white font-mono mt-0.5 block" x-text="scanMetrics.totalApps || '--'"></span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#1A1612] border border-[#2B241C]">
                    <span class="text-[10px] font-bold text-[#8C847A] uppercase tracking-wider block">Verificados</span>
                    <span class="text-xl font-extrabold text-sky-400 font-mono mt-0.5 block" x-text="scanMetrics.processedCount || '0'"></span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#1A1612] border border-success/30 bg-success/5">
                    <span class="text-[10px] font-bold text-success uppercase tracking-wider block">Enlaces Vivos (200 OK)</span>
                    <span class="text-xl font-extrabold text-success font-mono mt-0.5 block" x-text="scanMetrics.aliveCount || '0'"></span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#1A1612] border border-danger/40 bg-danger/5">
                    <span class="text-[10px] font-bold text-danger uppercase tracking-wider block">Enlaces Caídos (404 / 502)</span>
                    <span class="text-xl font-extrabold text-danger font-mono mt-0.5 block" x-text="scanMetrics.deadCount || '0'"></span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div x-show="scanMetrics.totalApps > 0" class="space-y-1.5 pt-1">
                <div class="flex items-center justify-between text-xs text-[#8C847A]">
                    <span>Progreso del escaneo general</span>
                    <span class="font-mono font-bold text-white" x-text="scanProgressPercent + '%'"></span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-[#1F1B16] overflow-hidden border border-[#2F271E]">
                    <div class="h-full bg-gradient-to-r from-primary via-amber-500 to-emerald-400 transition-all duration-300"
                         :style="'width: ' + scanProgressPercent + '%'"></div>
                </div>
            </div>
        </div>

        <!-- Live Scanner Results Filter & Table -->
        <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl shadow-xl overflow-hidden space-y-3">
            <div class="p-4 border-b border-[#262019] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Resultados del Escáner en Vivo</h3>
                    <span class="text-[11px] text-[#8C847A]" x-text="'(' + filteredScannedApps.length + ' apps mostradas)'"></span>
                </div>

                <!-- Toggle: Only broken VS All -->
                <div class="flex items-center gap-1.5 bg-[#0F0E0C] p-1 rounded-xl border border-[#2B241C] text-xs">
                    <button type="button"
                            @click="scannerFilter = 'broken'"
                            class="px-3 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1"
                            :class="scannerFilter === 'broken' ? 'bg-danger/20 text-danger border border-danger/30' : 'text-[#8C847A] hover:text-white'">
                        <span>🔴 Solo Enlaces Caídos</span>
                        <span class="font-mono" x-text="'(' + scanMetrics.deadCount + ')'"></span>
                    </button>
                    <button type="button"
                            @click="scannerFilter = 'all'"
                            class="px-3 py-1 rounded-lg font-bold transition-all cursor-pointer"
                            :class="scannerFilter === 'all' ? 'bg-[#262019] text-white border border-[#3D3224]' : 'text-[#8C847A] hover:text-white'">
                        Todos los Escaneados
                    </button>
                </div>
            </div>

            <!-- Stream Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#181410] border-b border-[#262019] text-[#8C847A] uppercase text-[10px] tracking-wider font-bold">
                            <th class="py-3 px-4">Software &amp; Versión</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4">Estado del Servidor</th>
                            <th class="py-3 px-4">Enlaces Evaluados</th>
                            <th class="py-3 px-4 text-right">Acción Inmediata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#211B15] text-[#D8CFBE]">
                        <template x-for="app in filteredScannedApps" :key="app.id">
                            <tr class="hover:bg-[#1A1612] transition-colors"
                                :class="app.is_broken ? 'bg-danger/5' : ''">
                                <!-- App -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#201C18] border border-[#352C22] p-0.5 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            <img :src="app.icon_url" class="w-full h-full object-contain rounded" onerror="this.style.display='none'">
                                        </div>
                                        <div class="min-w-0">
                                            <a :href="app.live_url" target="_blank" class="font-bold text-white hover:text-primary transition-colors block truncate max-w-[220px]" x-text="app.name"></a>
                                            <span class="text-[10px] font-mono text-[#8C847A]" x-text="'v' + app.version"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#221C16] text-[#A39B91] border border-[#352C22]" x-text="app.category"></span>
                                </td>

                                <!-- Health Status -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <template x-if="app.is_broken">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-danger/20 text-danger border border-danger/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-danger animate-pulse"></span>
                                            <span>ENLACE CAÍDO</span>
                                        </span>
                                    </template>
                                    <template x-if="!app.is_broken">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-success/20 text-success border border-success/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                            <span>ACTIVO &amp; VIVO</span>
                                        </span>
                                    </template>
                                </td>

                                <!-- Links Diagnostics detail -->
                                <td class="py-3 px-4">
                                    <div class="space-y-1 max-w-sm">
                                        <template x-for="(l, i) in app.links" :key="i">
                                            <div class="flex items-center gap-1.5 text-[10px] font-mono">
                                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="l.alive ? 'bg-success' : 'bg-danger'"></span>
                                                <span class="text-white truncate max-w-[150px]" x-text="l.name"></span>
                                                <span :class="l.alive ? 'text-success' : 'text-danger'" x-text="'(' + l.reason + ')'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </td>

                                <!-- Action -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <template x-if="app.is_broken">
                                            <button type="button"
                                                    @click="healAppLive(app)"
                                                    :disabled="healingAppId === app.id"
                                                    class="px-2.5 py-1 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 hover:text-amber-300 text-[11px] font-bold border border-amber-500/30 transition-all cursor-pointer flex items-center gap-1 shadow-sm">
                                                <svg x-show="healingAppId !== app.id" class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                                </svg>
                                                <svg x-show="healingAppId === app.id" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                <span x-text="healingAppId === app.id ? 'Reparando...' : '⚡ Auto-Reparar'"></span>
                                            </button>
                                        </template>

                                        <a :href="app.edit_url"
                                           class="p-1.5 rounded-lg text-primary hover:text-white hover:bg-primary/20 transition-colors border border-transparent"
                                           title="Editar aplicación">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="scannedApps.length === 0 && !isScanning">
                            <td colspan="5" class="py-12 text-center text-[#736B63]">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <span class="text-3xl block">📡</span>
                                    <p class="text-xs text-white font-bold">Escáner en Vivo listo</p>
                                    <p class="text-[11px] text-[#8C847A]">Presiona el botón «Iniciar Escaneo en Vivo» para verificar el catálogo automáticamente.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= 1-CLICK FIX & SEARCH MODAL ================= -->
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
             @click="modalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-3xl bg-[#141210] border border-[#2F271E] rounded-2xl shadow-2xl p-6 text-left overflow-hidden space-y-5">
                
                <!-- Modal Header -->
                <div class="flex items-start justify-between pb-4 border-b border-[#262019]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-white tracking-tight flex items-center gap-2">
                                <span x-text="currentReport ? currentReport.name : 'Asistente de Enlaces'"></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-[#241F1A] text-primary" x-text="currentReport ? ('v' + (currentReport.version || '1.0')) : ''"></span>
                            </h3>
                            <p class="text-xs text-[#8C847A]">Asistente de diagnóstico y reemplazo automático de enlaces caídos.</p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-[#8C847A] hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Tabs inside Modal -->
                <div class="flex items-center gap-2 border-b border-[#262019] pb-2 text-xs">
                    <button type="button"
                            @click="activeTab = 'scraped'"
                            class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                            :class="activeTab === 'scraped' ? 'bg-primary/20 text-primary border border-primary/30' : 'text-[#8C847A] hover:text-white'">
                        🔍 Opciones Scraped (Torrentmac &amp; Haxmac)
                    </button>
                    <button type="button"
                            @click="activeTab = 'health'; diagnoseHealth()"
                            class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
                            :class="activeTab === 'health' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-[#8C847A] hover:text-white'">
                        <span>🩺 Diagnóstico de Salud</span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'manual'"
                            class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                            :class="activeTab === 'manual' ? 'bg-[#2B241C] text-white border border-[#3E3428]' : 'text-[#8C847A] hover:text-white'">
                        ✏️ Ingreso Manual Rápido
                    </button>
                </div>

                <!-- TAB 1: SCRAPED CANDIDATES -->
                <div x-show="activeTab === 'scraped'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-[#A39B91]">Resultados encontrados en vivo listos para aplicar con 1-Click:</p>
                        <button type="button"
                                @click="searchCandidates()"
                                :disabled="searching"
                                class="text-xs text-primary hover:underline flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': searching }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Volver a buscar</span>
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div x-show="searching" class="py-12 text-center space-y-3">
                        <div class="w-8 h-8 mx-auto border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-xs text-white font-medium">Buscando enlaces en Torrentmac y Haxmac...</p>
                    </div>

                    <!-- Candidates List -->
                    <div x-show="!searching" class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                        <template x-for="(cand, idx) in candidates" :key="idx">
                            <div class="p-3.5 rounded-xl bg-[#1A1612] border border-[#2B241C] hover:border-primary/40 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <!-- Source Badge -->
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                              :class="cand.source === 'Torrentmac' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-sky-500/20 text-sky-400 border border-sky-500/30'"
                                              x-text="cand.source"></span>
                                        <span class="text-xs font-bold text-white truncate" x-text="cand.title"></span>
                                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-[#241F1A] text-[#C4BCB3]" x-text="'v' + (cand.version || '1.0')"></span>
                                    </div>

                                    <!-- Score Bar & Links Summary -->
                                    <div class="flex items-center gap-3 text-[11px] text-[#8C847A]">
                                        <span class="flex items-center gap-1 font-semibold"
                                              :class="cand.score >= 80 ? 'text-success' : (cand.score >= 60 ? 'text-amber-400' : 'text-[#8C847A]')">
                                            <span>Coincidencia:</span>
                                            <span x-text="cand.score + '%'"></span>
                                        </span>
                                        <span x-show="cand.magnet_link" class="text-purple-400 font-mono">🧲 Magnet disponible</span>
                                        <span x-show="cand.torrent_url" class="text-amber-400 font-mono">⚡ .torrent</span>
                                        <span x-show="cand.mirrors && cand.mirrors.length > 0" class="text-sky-400 font-mono" x-text="cand.mirrors.length + ' Mirror(s)'"></span>
                                    </div>
                                </div>

                                <!-- Action Button: Apply Candidate -->
                                <button type="button"
                                        @click="applyCandidate(cand)"
                                        class="btn-primary text-xs py-2 px-3.5 font-bold flex items-center justify-center gap-1.5 whitespace-nowrap shadow-md cursor-pointer flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Aplicar este Enlace</span>
                                </button>
                            </div>
                        </template>

                        <div x-show="candidates.length === 0" class="py-8 text-center text-[#736B63] space-y-2">
                            <span class="text-2xl block">🔍</span>
                            <p class="text-xs text-white font-medium">No se encontraron resultados automáticos coincidentes.</p>
                            <p class="text-[11px] text-[#8C847A]">Puedes ingresar el nuevo enlace de forma manual en la pestaña correspondiente.</p>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: HEALTH DIAGNOSTIC -->
                <div x-show="activeTab === 'health'" class="space-y-4">
                    <div class="p-3.5 rounded-xl bg-[#1A1612] border border-[#2B241C] space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Estado de los Enlaces Actuales de la App</h4>
                            <button type="button" @click="diagnoseHealth()" class="text-xs text-primary hover:underline cursor-pointer">
                                Re-verificar ahora
                            </button>
                        </div>

                        <div x-show="checkingHealth" class="py-6 text-center text-xs text-white">
                            <div class="w-6 h-6 mx-auto border-2 border-primary border-t-transparent rounded-full animate-spin mb-2"></div>
                            <span>Comprobando respuesta HTTP de los servidores de descarga...</span>
                        </div>

                        <div x-show="!checkingHealth && healthData" class="space-y-2">
                            <template x-for="(res, i) in healthData ? healthData.results : []" :key="i">
                                <div class="p-2.5 rounded-lg bg-[#14110E] border border-[#241F1A] flex items-center justify-between gap-2 text-xs">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" :class="res.alive ? 'bg-success' : 'bg-danger'"></span>
                                            <span class="font-bold text-white" x-text="res.name"></span>
                                            <span class="text-[10px] font-mono px-1 rounded bg-[#201C18]" :class="res.alive ? 'text-success' : 'text-danger'" x-text="res.reason"></span>
                                        </div>
                                        <p class="text-[10px] text-[#736B63] font-mono truncate mt-0.5" x-text="res.url"></p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="res.alive ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'"
                                          x-text="res.alive ? 'Activo' : 'Caído'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: MANUAL INPUT -->
                <div x-show="activeTab === 'manual'" class="space-y-4">
                    <form @submit.prevent="applyManual()" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#A39B91] mb-1">
                                Nueva URL de Descarga Directa (Mega, Drive, Qiwi, etc.)
                            </label>
                            <input type="url"
                                   x-model="manualForm.download_url"
                                   placeholder="https://servidor.com/archivo.dmg"
                                   class="input w-full text-xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#A39B91] mb-1">
                                Enlace Magnet (Opcional)
                            </label>
                            <input type="text"
                                   x-model="manualForm.magnet_link"
                                   placeholder="magnet:?xt=urn:btih:..."
                                   class="input w-full text-xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#A39B91] mb-1">
                                Versión del Programa (Opcional)
                            </label>
                            <input type="text"
                                   x-model="manualForm.version"
                                   placeholder="Ej: v2.5"
                                   class="input w-full text-xs font-mono">
                        </div>

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="modalOpen = false" class="btn-secondary text-xs px-4 py-2">
                                Cancelar
                            </button>
                            <button type="submit"
                                    :disabled="submittingManual"
                                    class="btn-primary text-xs px-5 py-2 font-bold cursor-pointer">
                                <span x-text="submittingManual ? 'Guardando...' : 'Guardar y Resolver Reporte'"></span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show"
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border backdrop-blur-md"
         :class="toast.success ? 'bg-[#181410]/95 border-success/40 text-success shadow-success/10' : 'bg-[#181410]/95 border-danger/30 text-danger shadow-danger/10'"
         style="display: none;">
        <span class="text-xs font-semibold text-white pr-2" x-text="toast.message"></span>
        <button type="button" @click="toast.show = false" class="text-[#8C847A] hover:text-white transition-colors p-1 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

</div>

@push('scripts')
<script>
function reportsManager() {
    return {
        mainTab: 'reports', // 'reports' or 'scanner'
        repairingId: null,
        modalOpen: false,
        activeTab: 'scraped',
        currentReport: null,
        searching: false,
        candidates: [],
        checkingHealth: false,
        healthData: null,
        submittingManual: false,
        manualForm: {
            download_url: '',
            magnet_link: '',
            version: ''
        },
        toast: {
            show: false,
            message: '',
            success: true
        },

        // Live Scanner State
        isScanning: false,
        scanPage: 1,
        scanPerPage: 6,
        scannedApps: [],
        scannerFilter: 'broken', // 'broken' or 'all'
        healingAppId: null,
        scanMetrics: {
            totalApps: 0,
            processedCount: 0,
            aliveCount: 0,
            deadCount: 0
        },

        get scanProgressPercent() {
            if (!this.scanMetrics.totalApps || this.scanMetrics.totalApps === 0) return 0;
            return Math.min(100, Math.round((this.scanMetrics.processedCount / this.scanMetrics.totalApps) * 100));
        },

        get liveDeadCount() {
            return this.scanMetrics.deadCount;
        },

        get filteredScannedApps() {
            if (this.scannerFilter === 'broken') {
                return this.scannedApps.filter(a => a.is_broken);
            }
            return this.scannedApps;
        },

        showToast(message, success = true) {
            this.toast = { show: true, message, success };
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        // ================= LIVE SCANNER CONTROLS =================
        async toggleScanning() {
            if (this.isScanning) {
                this.isScanning = false;
                this.showToast('Escaneo en vivo pausado');
                return;
            }

            this.isScanning = true;
            this.showToast('Iniciando escaneo activo de enlaces...', true);
            await this.runNextBatch();
        },

        clearScan() {
            this.isScanning = false;
            this.scanPage = 1;
            this.scannedApps = [];
            this.scanMetrics = {
                totalApps: 0,
                processedCount: 0,
                aliveCount: 0,
                deadCount: 0
            };
        },

        async runNextBatch() {
            if (!this.isScanning) return;

            try {
                const res = await fetch('{{ route('admin.reports.scan-batch') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        page: this.scanPage,
                        per_page: this.scanPerPage
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.scanMetrics.totalApps = data.total_apps;
                    this.scanMetrics.processedCount = data.processed_count;

                    // Add evaluated apps to list (avoid duplicates)
                    for (const app of data.apps) {
                        const existingIdx = this.scannedApps.findIndex(a => a.id === app.id);
                        if (existingIdx >= 0) {
                            this.scannedApps[existingIdx] = app;
                        } else {
                            this.scannedApps.push(app);
                        }

                        if (app.is_broken) {
                            this.scanMetrics.deadCount++;
                        } else {
                            this.scanMetrics.aliveCount++;
                        }
                    }

                    if (data.has_more && this.isScanning) {
                        this.scanPage++;
                        // Short pause between batches for smooth browser rendering
                        setTimeout(() => this.runNextBatch(), 300);
                    } else {
                        this.isScanning = false;
                        this.showToast(`¡Escaneo finalizado! ${this.scanMetrics.deadCount} enlaces caídos detectados.`, this.scanMetrics.deadCount === 0);
                    }
                } else {
                    this.isScanning = false;
                    this.showToast(data.message || 'Error en el escáner', false);
                }
            } catch (e) {
                this.isScanning = false;
                this.showToast('Error de red durante el escaneo: ' + e.message, false);
            }
        },

        // Auto-heal an app directly from live scanner table
        async healAppLive(app) {
            if (this.healingAppId) return;
            this.healingAppId = app.id;

            try {
                const res = await fetch(`/admin/reports/heal-app/${app.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message, true);
                    app.is_broken = false;
                    this.scanMetrics.deadCount = Math.max(0, this.scanMetrics.deadCount - 1);
                    this.scanMetrics.aliveCount++;
                } else {
                    this.showToast(data.message || 'No se encontró enlace exacto. Abriendo asistente...', false);
                    if (data.candidates && data.candidates.length > 0) {
                        this.openSearchModal({
                            id: null,
                            name: app.name,
                            version: app.version
                        }, data.candidates);
                    }
                }
            } catch (e) {
                this.showToast('Error de conexión: ' + e.message, false);
            } finally {
                this.healingAppId = null;
            }
        },

        // ================= REPORTS TABLE ACTIONS =================
        async autoRepair(reportId, appName) {
            if (this.repairingId) return;
            if (!confirm(`¿Iniciar búsqueda y auto-reparación inteligente en Torrentmac y Haxmac para «${appName}»?`)) return;

            this.repairingId = reportId;
            try {
                const res = await fetch(`/admin/reports/${reportId}/auto-repair`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message, true);
                    this.markRowResolved(reportId);
                } else {
                    this.showToast(data.message || 'No se pudo auto-reparar con certeza. Abriendo asistente...', false);
                    if (data.candidates && data.candidates.length > 0) {
                        this.openSearchModal({
                            id: reportId,
                            name: appName,
                            version: ''
                        }, data.candidates);
                    }
                }
            } catch (e) {
                this.showToast('Error de conexión: ' + e.message, false);
            } finally {
                this.repairingId = null;
            }
        },

        async openSearchModal(report, preloadedCandidates = null) {
            this.currentReport = report;
            this.activeTab = 'scraped';
            this.modalOpen = true;
            this.manualForm = {
                download_url: report.download_url || '',
                magnet_link: report.magnet_link || '',
                version: report.version || ''
            };

            if (preloadedCandidates) {
                this.candidates = preloadedCandidates;
            } else {
                await this.searchCandidates();
            }
        },

        async searchCandidates() {
            if (!this.currentReport || !this.currentReport.id) return;
            this.searching = true;
            try {
                const res = await fetch(`/admin/reports/${this.currentReport.id}/search-replacement`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.candidates = data.candidates || [];
                } else {
                    this.showToast(data.message || 'Error al buscar reemplazos', false);
                }
            } catch (e) {
                this.showToast('Error de red: ' + e.message, false);
            } finally {
                this.searching = false;
            }
        },

        async applyCandidate(candidate) {
            if (!this.currentReport || !this.currentReport.id) return;
            if (!confirm(`¿Confirmas aplicar los enlaces de «${candidate.title}» (${candidate.source})?`)) return;

            try {
                const res = await fetch(`/admin/reports/${this.currentReport.id}/apply-replacement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ candidate })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message, true);
                    this.markRowResolved(this.currentReport.id);
                    this.modalOpen = false;
                } else {
                    this.showToast(data.message || 'Error al aplicar reemplazo', false);
                }
            } catch (e) {
                this.showToast('Error: ' + e.message, false);
            }
        },

        async applyManual() {
            if (!this.currentReport || !this.currentReport.id) return;
            this.submittingManual = true;
            try {
                const res = await fetch(`/admin/reports/${this.currentReport.id}/apply-replacement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.manualForm)
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message, true);
                    this.markRowResolved(this.currentReport.id);
                    this.modalOpen = false;
                } else {
                    this.showToast(data.message || 'Error al guardar enlace', false);
                }
            } catch (e) {
                this.showToast('Error: ' + e.message, false);
            } finally {
                this.submittingManual = false;
            }
        },

        async diagnoseHealth() {
            if (!this.currentReport || !this.currentReport.id) return;
            this.checkingHealth = true;
            try {
                const res = await fetch(`/admin/reports/${this.currentReport.id}/check-health`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.healthData = data.health;
                }
            } catch (e) {
                this.showToast('No se pudo completar el diagnóstico', false);
            } finally {
                this.checkingHealth = false;
            }
        },

        markRowResolved(reportId) {
            const badge = document.getElementById(`status-badge-${reportId}`);
            if (badge) {
                badge.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-success/20 text-success border border-success/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                        Solucionado
                    </span>
                `;
            }
        }
    };
}
</script>
@endpush
@endsection
