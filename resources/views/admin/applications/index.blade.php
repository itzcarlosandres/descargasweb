<x-admin-layout>
    @section('title', 'Gestionar Aplicaciones')
    @section('header', 'Aplicaciones')

    <div class="space-y-6">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Total Programas</span>
                    <p class="text-xl font-extrabold text-white mt-1">{{ number_format($totalApps) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>

            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Publicadas</span>
                    <p class="text-xl font-extrabold text-success mt-1">{{ number_format($publishedApps) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-success/10 border border-success/20 flex items-center justify-center text-success">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <a href="{{ route('admin.applications', ['status' => 'featured']) }}"
               x-data="{ count: {{ $featuredApps }} }"
               @featured-toggled.window="count = $event.detail.count"
               class="card p-4 bg-[#141210] border {{ request('status') === 'featured' ? 'border-primary' : 'border-[#26211B]' }} hover:border-primary/50 transition-colors flex items-center justify-between group">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider group-hover:text-primary transition-colors">Destacadas (Featured)</span>
                    <p class="text-xl font-extrabold text-primary mt-1" x-text="count">{{ number_format($featuredApps) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </a>

            <div class="card p-4 bg-[#141210] border-[#26211B] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#8C847A] uppercase tracking-wider">Borradores</span>
                    <p class="text-xl font-extrabold text-warning mt-1">{{ number_format($totalApps - $publishedApps) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-warning/10 border border-warning/20 flex items-center justify-center text-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card p-4 bg-[#141210] border-[#26211B] flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.applications') }}" class="w-full md:w-auto flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[220px]">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#7E776F]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre, desarrollador..."
                           class="input w-full pl-9 py-2 text-xs">
                </div>

                <!-- Category Dropdown Filter -->
                <select name="category_id"
                        onchange="this.form.submit()"
                        class="input py-2 text-xs cursor-pointer min-w-[150px]">
                    <option value="">Todas las Categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select name="status"
                        onchange="this.form.submit()"
                        class="input py-2 text-xs cursor-pointer">
                    <option value="">Todos los Estados</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Solo Publicados</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Solo Borradores</option>
                    <option value="featured" {{ request('status') === 'featured' ? 'selected' : '' }}>Solo Destacados</option>
                </select>

                <button type="submit" class="btn-secondary text-xs py-2 px-3">
                    Filtrar
                </button>

                @if(request()->anyFilled(['search', 'category_id', 'status']))
                    <a href="{{ route('admin.applications') }}" class="text-xs text-[#8C847A] hover:text-white transition-colors underline">
                        Limpiar filtros
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <form action="{{ route('admin.applications.reset-downloads') }}" method="POST"
                      onsubmit="return confirm('¿Deseas reiniciar a 0 el contador de descargas de TODAS las aplicaciones? Esta acción dejará los contadores totalmente limpios para producción.');">
                    @csrf
                    <input type="hidden" name="mode" value="zero">
                    <button type="submit"
                            title="Reiniciar contadores de descargas a 0 para producción"
                            class="px-3 py-2.5 rounded-xl bg-[#1C1814] hover:bg-[#25201A] border border-[#332A22] hover:border-warning/50 text-[#A39B91] hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reiniciar Descargas</span>
                    </button>
                </form>

                <a href="{{ route('admin.applications.create') }}"
                   class="btn-primary text-xs px-4 py-2.5 gap-2 shadow-md shadow-primary/20 flex-shrink-0 w-full md:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Publicar Nuevo Programa</span>
                </a>
            </div>
        </div>

        <!-- Table Card with Bulk Action Support -->
        <div class="card overflow-hidden bg-[#141210] border-[#26211B] shadow-xl"
             x-data="{
                 selected: [],
                 selectAll: false,
                 bulkAction: '',
                 toggleSelectAll() {
                     if (this.selectAll) {
                         this.selected = [{{ $applications->pluck('id')->implode(',') }}];
                     } else {
                         this.selected = [];
                     }
                 }
             }">

            <!-- Bulk Action Toolbar (appears when items are selected) -->
            <div x-show="selected.length > 0" x-cloak
                 class="p-3 bg-[#1C1814] border-b border-[#3D3224] flex flex-wrap items-center justify-between gap-4 transition-all">
                <div class="flex items-center gap-2 text-xs font-semibold text-white">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span><strong class="text-primary" x-text="selected.length"></strong> aplicaciones seleccionadas</span>
                </div>
                <form method="POST" action="{{ route('admin.applications.bulk-action') }}" class="flex items-center gap-2 flex-wrap">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="selected_ids[]" :value="id">
                    </template>
                    <select name="action" x-model="bulkAction" required class="input text-xs py-1.5 px-3 bg-[#14110E] border-[#3D3224] text-white cursor-pointer">
                        <option value="">Seleccionar Acción en Lote...</option>
                        <option value="publish">Publicar seleccionadas</option>
                        <option value="unpublish">Mover a borrador (despublicar)</option>
                        <option value="feature">Marcar como destacadas</option>
                        <option value="unfeature">Quitar de destacadas</option>
                        <option value="delete">Eliminar definitivamente</option>
                    </select>
                    <button type="submit"
                            :disabled="!bulkAction"
                            onclick="return confirm('¿Confirmas aplicar esta acción en lote a las aplicaciones seleccionadas?')"
                            class="btn-primary text-xs py-1.5 px-3 disabled:opacity-50 cursor-pointer">
                        Aplicar
                    </button>
                    <button type="button" @click="selected = []; selectAll = false" class="btn-secondary text-xs py-1.5 px-2 cursor-pointer">
                        Cancelar
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1A1612] border-b border-[#26211B]">
                        <tr>
                            <th class="w-10 px-4 py-3.5 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded bg-[#221C16] border-[#3E342A] text-primary focus:ring-0 cursor-pointer">
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Aplicación</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Categoría</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Versión & Tamaño</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Estado</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider">Descargas</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-[#8C847A] uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#241F1A]">
                        @forelse($applications as $app)
                        <tr class="hover:bg-[#181410] transition-colors group" :class="selected.includes({{ $app->id }}) ? 'bg-primary/5' : ''">
                            <td class="w-10 px-4 py-3.5 text-center">
                                <input type="checkbox" :value="{{ $app->id }}" x-model="selected" class="rounded bg-[#221C16] border-[#3E342A] text-primary focus:ring-0 cursor-pointer">
                            </td>
                            <!-- App & Icon -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <!-- Squircle Icon -->
                                    <div class="w-11 h-11 rounded-[13px] bg-gradient-to-b from-[#2B241C] to-[#181410] border border-[#3E342A] p-0.5 shadow-md flex-shrink-0 flex items-center justify-center overflow-hidden">
                                        <img src="{{ $app->icon_url }}" alt="{{ $app->name }}"
                                             class="w-full h-full object-cover rounded-[11px]"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full bg-primary/20 text-primary font-extrabold text-sm rounded-[11px] items-center justify-center" style="display: none;">
                                            {{ strtoupper(substr($app->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.applications.edit', $app) }}"
                                           class="text-xs font-bold text-white hover:text-primary transition-colors block truncate max-w-xs">
                                            {{ $app->name }}
                                        </a>
                                        <p class="text-[11px] text-[#7E776F] truncate mt-0.5">
                                            {{ $app->developer ?? 'Desarrollador macOS' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-3.5">
                                <span class="badge bg-[#1F1B16] text-[#B6B0A8] border border-[#2D261E] text-[11px]">
                                    {{ $app->category->name }}
                                </span>
                            </td>

                            <!-- Version & Size -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-[#221C16] text-white border border-[#342C22]">
                                        {{ $app->version ?? 'v1.0' }}
                                    </span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-primary/10 text-primary font-bold">
                                        {{ $app->formatted_size }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status & Badges -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($app->published)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-success/10 text-success text-[10px] font-semibold border border-success/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>
                                            Publicado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-warning/10 text-warning text-[10px] font-semibold border border-warning/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                                            Borrador
                                        </span>
                                    @endif

                                    <span x-data="{ isFeatured: {{ $app->featured ? 'true' : 'false' }} }"
                                          @featured-toggled.window="if ($event.detail.appId === {{ $app->id }}) isFeatured = $event.detail.isFeatured"
                                          x-show="isFeatured"
                                          x-cloak
                                          style="{{ $app->featured ? '' : 'display: none;' }}"
                                          class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-400/20 text-amber-400 border border-amber-400/30 inline-flex items-center gap-1"
                                          title="Destacado en portada (Featured)">
                                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Featured
                                    </span>
                                </div>
                            </td>

                            <!-- Downloads -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 text-xs text-[#A39B91]">
                                    <svg class="w-3.5 h-3.5 text-[#6B635A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span class="font-mono">{{ number_format($app->downloads) }}</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Toggle Featured (Destacado) Button -->
                                    <form action="{{ route('admin.applications.toggle-featured', $app) }}"
                                          method="POST"
                                          class="inline"
                                          x-data="{ 
                                              loading: false,
                                              isFeatured: {{ $app->featured ? 'true' : 'false' }},
                                              async toggleFeatured() {
                                                  if (this.loading) return;
                                                  this.loading = true;
                                                  try {
                                                      const res = await fetch('{{ route('admin.applications.toggle-featured', $app) }}', {
                                                          method: 'POST',
                                                          headers: {
                                                              'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                              'Accept': 'application/json',
                                                              'X-Requested-With': 'XMLHttpRequest'
                                                          }
                                                      });
                                                      const data = await res.json();
                                                      if (data.success) {
                                                          this.isFeatured = data.featured;
                                                          $dispatch('featured-toggled', { appId: {{ $app->id }}, isFeatured: data.featured, count: data.featuredCount });
                                                          $dispatch('show-toast', { message: data.message, success: true });
                                                      }
                                                  } catch (e) {
                                                      this.$el.submit();
                                                  } finally {
                                                      this.loading = false;
                                                  }
                                              }
                                          }"
                                          @submit.prevent="toggleFeatured()">
                                        @csrf
                                        <button type="submit"
                                                :disabled="loading"
                                                :title="isFeatured ? 'Quitar de Featured (Destacados de portada)' : 'Agregar a Featured (Destacados de portada)'"
                                                class="p-1.5 rounded-lg transition-all duration-200 relative group flex items-center justify-center border"
                                                :class="isFeatured 
                                                    ? 'text-amber-400 bg-amber-400/10 hover:bg-amber-400/20 border-amber-400/30 shadow-sm shadow-amber-500/10' 
                                                    : 'text-[#8C847A] hover:text-amber-400 hover:bg-[#241F1A] border-transparent'">
                                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" 
                                                 :class="{ 'animate-spin opacity-50': loading }"
                                                 :fill="isFeatured ? 'currentColor' : 'none'" 
                                                 stroke="currentColor" 
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- View Live App -->
                                    <a href="{{ route('app', $app->slug) }}" target="_blank"
                                       title="Ver en portal"
                                       class="p-1.5 rounded-lg text-[#8C847A] hover:text-white hover:bg-[#241F1A] transition-colors border border-transparent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="{{ route('admin.applications.edit', $app) }}"
                                       title="Editar programa"
                                       class="p-1.5 rounded-lg text-primary hover:text-white hover:bg-primary/20 transition-colors border border-transparent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.applications.destroy', $app) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente {{ addslashes($app->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Eliminar programa"
                                                class="p-1.5 rounded-lg text-[#8C847A] hover:text-danger hover:bg-danger/10 transition-colors border border-transparent">
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
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-[#1A1612] border border-[#2B241C] flex items-center justify-center text-[#736B63]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-[#8C847A] font-medium">No se encontraron programas con los filtros seleccionados</p>
                                    <a href="{{ route('admin.applications.create') }}" class="btn-primary text-xs px-4 py-2 mt-2">
                                        Publicar el Primer Programa
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            <x-pagination :paginator="$applications" />
        </div>

        <!-- Floating Toast Notification for Real-Time Actions -->
        <div x-data="{ show: false, message: '', isSuccess: true }"
             @show-toast.window="message = $event.detail.message; isSuccess = $event.detail.success; show = true; setTimeout(() => show = false, 3500)"
             x-show="show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border backdrop-blur-md"
             :class="isSuccess ? 'bg-[#181410]/95 border-amber-500/40 text-amber-400 shadow-amber-500/10' : 'bg-[#181410]/95 border-danger/30 text-danger shadow-danger/10'"
             style="display: none;">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                 :class="isSuccess ? 'bg-amber-400/20 text-amber-400' : 'bg-danger/20 text-danger'">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-white pr-2" x-text="message"></span>
            <button @click="show = false" class="text-[#8C847A] hover:text-white transition-colors p-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</x-admin-layout>
