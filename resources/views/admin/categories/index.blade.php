<x-admin-layout>
    @section('title', 'Gestionar Categorías')
    @section('header', 'Categorías')

    <div class="space-y-6">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Total Categorías</span>
                    <p class="text-xl font-extrabold text-white mt-1">{{ $categories->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>

            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Activas</span>
                    <p class="text-xl font-extrabold text-success mt-1">{{ $categories->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-success/10 border border-success/20 flex items-center justify-center text-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Inactivas</span>
                    <p class="text-xl font-extrabold text-warning mt-1">{{ $categories->where('is_active', false)->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-warning/10 border border-warning/20 flex items-center justify-center text-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Apps Asociadas</span>
                    <p class="text-xl font-extrabold text-primary mt-1">{{ $categories->sum('applications_count') }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter & Actions Bar -->
        <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.categories') }}" class="w-full md:w-auto flex items-center gap-3 flex-1">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#7E776F]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar categoría por nombre o slug..."
                           class="input w-full pl-9 py-2 text-xs">
                </div>

                <button type="submit" class="btn-secondary text-xs py-2 px-3">
                    Buscar
                </button>

                @if(request()->filled('search'))
                    <a href="{{ route('admin.categories') }}" class="text-xs text-[#8C847A] hover:text-white transition-colors underline">
                        Limpiar
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.categories.create') }}"
               class="btn-primary text-xs px-4 py-2.5 gap-2 shadow-md shadow-primary/20 flex-shrink-0 w-full md:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nueva Categoría</span>
            </a>
        </div>

        <!-- Categories Table -->
        <div class="card overflow-hidden bg-[#141210] border-[#26211B] shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1A1612] border-b border-[#26211B]">
                        <tr>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Categoría</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Slug URL</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Orden</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Total Apps</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#241F1A]">
                        @forelse($categories as $category)
                        <tr class="hover:bg-[#181410] transition-colors group">
                            <!-- Category Name & Icon -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#28211A] to-[#1A1612] border border-[#3E342A] flex items-center justify-center text-primary shadow-sm flex-shrink-0">
                                        <x-category-icon :icon="$category->icon" :name="$category->slug" class="w-5 h-5 text-primary" />
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="text-xs font-bold text-white hover:text-primary transition-colors block">
                                            {{ $category->name }}
                                        </a>
                                        @if($category->description)
                                            <p class="text-[11px] text-[#7E776F] truncate max-w-xs mt-0.5">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-[#A39B91] font-mono bg-[#1C1814] px-2 py-0.5 rounded border border-[#2B241C]">
                                    /category/{{ $category->slug }}
                                </span>
                            </td>

                            <!-- Sort Order -->
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-white font-mono font-medium">
                                    #{{ $category->sort_order }}
                                </span>
                            </td>

                            <!-- Apps count -->
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.applications', ['category_id' => $category->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary hover:bg-primary/20 transition-colors border border-primary/20">
                                    <span>{{ $category->applications_count }}</span>
                                    <span class="text-[10px] font-normal text-[#8C847A]">programas</span>
                                </a>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-success/10 text-success text-[10px] font-semibold border border-success/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>
                                        Activa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-warning/10 text-warning text-[10px] font-semibold border border-warning/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                                        Inactiva
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View on portal -->
                                    <a href="{{ route('category', $category->slug) }}" target="_blank"
                                       title="Ver en portal"
                                       class="p-1.5 rounded-lg text-[#8C847A] hover:text-white hover:bg-[#241F1A] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       title="Editar categoría"
                                       class="p-1.5 rounded-lg text-primary hover:text-white hover:bg-primary/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.categories.destroy', $category) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar la categoría {{ addslashes($category->name) }}? Solo podrá eliminarse si no tiene programas vinculados.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Eliminar categoría"
                                                class="p-1.5 rounded-lg text-[#8C847A] hover:text-danger hover:bg-danger/10 transition-colors">
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
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-[#1A1612] border border-[#2B241C] flex items-center justify-center text-[#736B63]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-[#8C847A] font-medium">No se encontraron categorías</p>
                                    <a href="{{ route('admin.categories.create') }}" class="btn-primary text-xs px-4 py-2 mt-2">
                                        Crear la Primera Categoría
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
