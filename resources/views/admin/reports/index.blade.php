@extends('layouts.admin')

@section('title', 'Reportes de Enlaces Caídos')
@section('page-title', 'Reportes de Enlaces')

@section('content')
<div class="space-y-6 max-w-[1600px] mx-auto w-full">

    <!-- Header Panel macOS Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#14110E] border border-[#2B241C] p-5 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-danger to-rose-600 p-0.5 shadow-lg shadow-danger/20 flex-shrink-0">
                <div class="w-full h-full bg-[#181410] rounded-[10px] flex items-center justify-center text-danger">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-white tracking-tight">Reportes de Enlaces Caídos</h1>
                    @if($totalPending > 0)
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-danger/20 text-danger border border-danger/30 animate-pulse">
                            {{ $totalPending }} PENDIENTES
                        </span>
                    @endif
                </div>
                <p class="text-xs text-[#8C847A]">Reportes generados por usuarios que detectaron enlaces caídos, archivos dañados o mirrors sin conexión.</p>
            </div>
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

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="bg-success/15 border border-success/30 text-success px-4 py-3 rounded-2xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Reports Table Card -->
    <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#181410] border-b border-[#262019] text-[#8C847A] uppercase text-[10px] tracking-wider font-bold">
                        <th class="py-3 px-4">Programa Afectado</th>
                        <th class="py-3 px-4">Tipo de Falla</th>
                        <th class="py-3 px-4">Detalles del Usuario</th>
                        <th class="py-3 px-4">IP &amp; Fecha</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#211B15] text-[#D8CFBE]">
                    @forelse($reports as $rep)
                        <tr class="hover:bg-[#1A1612] transition-colors {{ $rep->status === 'pending' ? 'bg-[#181210]/40' : '' }}">
                            <!-- App -->
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
                                           class="font-bold text-white hover:text-primary transition-colors block truncate max-w-[200px] text-xs">
                                            {{ $rep->application?->name ?? 'Programa Eliminado' }}
                                        </a>
                                        <span class="text-[10px] font-mono text-[#8C847A]">
                                            v{{ $rep->application?->version ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Type -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $typeStyles = [
                                        'ddl' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                        'torrent' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                        'mirror' => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
                                        'other' => 'bg-purple-500/15 text-purple-400 border-purple-500/30',
                                    ];
                                    $typeLabels = [
                                        'ddl' => 'Enlace DDL Caído',
                                        'torrent' => 'Torrent / Magnet',
                                        'mirror' => 'Servidor Espejo',
                                        'other' => 'Problema General',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $typeStyles[$rep->type] ?? 'bg-[#2B241C] text-[#8C847A]' }}">
                                    {{ $typeLabels[$rep->type] ?? ucfirst($rep->type) }}
                                </span>
                            </td>

                            <!-- Notes -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-xs text-[#D8CFBE] truncate" title="{{ $rep->notes }}">
                                    {{ $rep->notes ?: 'Sin comentarios adicionales del usuario.' }}
                                </p>
                            </td>

                            <!-- IP & Date -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-[11px] text-[#8C847A]">
                                <span class="block text-white font-mono text-[10px]">{{ $rep->ip_address ?: 'IP Oculta' }}</span>
                                <span>{{ $rep->created_at->diffForHumans() }}</span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
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
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($rep->status === 'pending')
                                        <form method="POST" action="{{ route('admin.reports.resolve', $rep->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1 rounded-lg bg-success/15 hover:bg-success/25 text-success text-[11px] font-bold border border-success/30 transition-all cursor-pointer flex items-center gap-1"
                                                    title="Marcar este reporte como solucionado">
                                                <span>✓</span>
                                                <span>Resolver</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if($rep->application)
                                        <a href="{{ route('admin.applications.edit', $rep->application) }}"
                                           class="px-2.5 py-1 rounded-lg bg-primary/15 hover:bg-primary/25 text-primary text-[11px] font-bold border border-primary/30 transition-all cursor-pointer"
                                           title="Editar la aplicación para cambiar el enlace">
                                            Editar App
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('admin.reports.destroy', $rep->id) }}" onsubmit="return confirm('¿Eliminar este reporte?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1 rounded-lg text-[#8C847A] hover:text-danger hover:bg-danger/10 transition-colors cursor-pointer"
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
                                    <p class="font-bold text-white text-xs">No hay reportes de enlaces caídos</p>
                                    <p class="text-[11px] text-[#8C847A]">Todos los enlaces del catálogo están en orden o no se han recibido quejas recientes.</p>
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
@endsection
