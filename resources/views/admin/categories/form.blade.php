<x-admin-layout>
    @section('title', isset($category) ? 'Editar Categoría: ' . $category->name : 'Crear Nueva Categoría')
    @section('header', isset($category) ? 'Editar Categoría' : 'Nueva Categoría')
    <div x-data="categoryForm()">
        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-[#26211B]">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.categories') }}"
                   class="p-2 rounded-xl bg-[#1A1612] hover:bg-[#25201A] text-[#8C847A] hover:text-white transition-colors border border-[#2B241C]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-tight">
                        {{ isset($category) ? 'Editar Categoría: ' . $category->name : 'Crear Nueva Categoría' }}
                    </h1>
                    <p class="text-xs text-[#8C847A] mt-0.5">
                        Organiza el catálogo de aplicaciones por temáticas y áreas de trabajo.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.categories') }}" class="btn-secondary text-xs px-4 py-2">
                    Cancelar
                </a>
                <button type="submit" form="category-form"
                        class="btn-primary text-xs px-5 py-2.5 gap-2 shadow-lg shadow-primary/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ isset($category) ? 'Actualizar Categoría' : 'Guardar Categoría' }}</span>
                </button>
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-white text-xs">
                <p class="font-bold text-danger mb-2">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc list-inside space-y-1 text-[#E0D7D0]">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="category-form"
              action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              method="POST">
            @csrf
            @if(isset($category))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Main Form (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="card p-6 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-[#26211B]">
                            <div class="w-7 h-7 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Detalles de la Categoría</h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Nombre de la Categoría <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       x-model="name"
                                       @input="updateSlug()"
                                       value="{{ old('name', $category->name ?? '') }}"
                                       placeholder="Ej: Video & Multimedia, Diseño Gráfico, Utilidades..."
                                       class="input w-full text-sm font-medium"
                                       required>
                                @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Slug -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-semibold text-[#A39B91]">
                                        Slug URL (identificador)
                                    </label>
                                    <button type="button" @click="slugLocked = !slugLocked"
                                            class="text-[11px] text-primary hover:underline flex items-center gap-1">
                                        <span x-text="slugLocked ? 'Desbloquear edición manual' : 'Sincronizar con nombre'"></span>
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[#6B635A] font-mono">/category/</span>
                                    <input type="text"
                                           name="slug"
                                           x-model="slug"
                                           :readonly="slugLocked"
                                           placeholder="video-multimedia"
                                           class="input w-full pl-22 text-xs font-mono"
                                           :class="slugLocked ? 'opacity-80 bg-[#171411]' : ''">
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Descripción (Opcional)
                                </label>
                                <textarea name="description"
                                          x-model="description"
                                          rows="3"
                                          placeholder="Breve explicación de las herramientas que agrupa esta categoría..."
                                          class="input w-full text-xs resize-none leading-relaxed">{{ old('description', $category->description ?? '') }}</textarea>
                            </div>

                            <!-- Icon Selector & Presets -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Icono de la Categoría
                                </label>
                                <div class="flex items-center gap-3 mb-3">
                                    <input type="text"
                                           name="icon"
                                           x-model="icon"
                                           placeholder="palette, video, music..."
                                           class="input w-48 text-xs font-mono font-medium">
                                    <span class="text-xs text-[#8C847A]">Selecciona un icono rápido abajo o ingresa un identificador / emoji:</span>
                                </div>

                                <!-- Preset Click-to-pick Icons Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3.5 rounded-xl bg-[#0E0D0B] border border-[#2B241C]">
                                    <button type="button" @click="setIcon('palette')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'palette' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="palette" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Diseño (palette)</span>
                                    </button>

                                    <button type="button" @click="setIcon('video')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'video' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="video" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Video (video)</span>
                                    </button>

                                    <button type="button" @click="setIcon('music')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'music' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="music" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Audio (music)</span>
                                    </button>

                                    <button type="button" @click="setIcon('code')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'code' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="code" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Código (code)</span>
                                    </button>

                                    <button type="button" @click="setIcon('wrench')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'wrench' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="wrench" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Utilidades (wrench)</span>
                                    </button>

                                    <button type="button" @click="setIcon('shield')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'shield' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="shield" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Seguridad (shield)</span>
                                    </button>

                                    <button type="button" @click="setIcon('gamepad')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'gamepad' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="gamepad" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Juegos (gamepad)</span>
                                    </button>

                                    <button type="button" @click="setIcon('globe')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'globe' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="globe" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Internet (globe)</span>
                                    </button>

                                    <button type="button" @click="setIcon('briefcase')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'briefcase' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="briefcase" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Oficina (briefcase)</span>
                                    </button>

                                    <button type="button" @click="setIcon('app')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'app' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="app" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Apps (app)</span>
                                    </button>

                                    <button type="button" @click="setIcon('book')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'book' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="book" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Educación (book)</span>
                                    </button>

                                    <button type="button" @click="setIcon('image')"
                                            class="px-2.5 py-2 rounded-lg bg-[#1C1814] hover:bg-[#28221B] border border-[#342C22] flex items-center gap-2 transition-all text-left"
                                            :class="icon === 'image' ? 'border-primary bg-primary/10 text-primary' : 'text-[#A39B91] hover:text-white'">
                                        <x-category-icon icon="image" class="w-4 h-4 text-primary flex-shrink-0" />
                                        <span class="text-[11px] truncate">Gráficos (image)</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Orden de Visualización
                                </label>
                                <input type="number"
                                       name="sort_order"
                                       x-model="sort_order"
                                       value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                                       class="input w-full text-xs font-mono font-medium">
                                <p class="text-[11px] text-[#6B635A] mt-1">Los números menores aparecen primero (0, 1, 2...).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings & Live Preview (4 Cols) -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Status & Publishing -->
                    <div class="card p-5 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Estado</h3>
                            <span class="w-2.5 h-2.5 rounded-full" :class="is_active ? 'bg-success' : 'bg-warning'"></span>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] border border-[#2B241C]">
                                <div>
                                    <p class="text-xs font-bold text-white">Categoría Activa</p>
                                    <p class="text-[10px] text-[#8C847A]" x-text="is_active ? 'Visible en el menú y filtros' : 'Oculta en la navegación'"></p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="is_active" class="sr-only peer">
                                    <div class="w-10 h-5 bg-[#2B241C] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-success"></div>
                                </label>
                            </div>

                            <button type="submit"
                                    class="btn-primary w-full py-2.5 text-xs font-bold justify-center gap-2 shadow-lg shadow-primary/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ isset($category) ? 'Guardar Cambios' : 'Crear Categoría' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live Category Preview Card -->
                    <div class="card p-5 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Vista Previa</h3>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-primary/20 text-primary font-bold">Simulación</span>
                        </div>

                        <p class="text-[11px] text-[#8C847A] mb-3">Así se mostrará la tarjeta de la categoría:</p>

                        <!-- Simulated Category Card -->
                        <div class="p-4 rounded-2xl bg-[#1B1713] border border-[#2B241C] shadow-lg flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#2D241C] to-[#1A1612] border border-[#3E342A] flex items-center justify-center shadow-md flex-shrink-0"
                                 x-html="renderIconSvg()">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-white truncate" x-text="name || 'Nombre de la Categoría'"></p>
                                <p class="text-[11px] text-[#8C847A] truncate mt-0.5" x-text="description || 'Explora todas las aplicaciones...'"></p>
                                <span class="inline-block text-[10px] font-mono text-primary mt-1">/category/<span x-text="slug || 'slug'"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function categoryForm() {
            return {
                name: @json(old('name', $category->name ?? '')),
                slug: @json(old('slug', $category->slug ?? '')),
                slugLocked: @json(isset($category)),
                icon: @json(old('icon', $category->icon ?? 'palette')),
                description: @json(old('description', $category->description ?? '')),
                sort_order: {{ (int) old('sort_order', $category->sort_order ?? 0) }},
                is_active: @json(old('is_active', isset($category) ? ($category->is_active ? true : false) : true)),

                updateSlug() {
                    if (!this.slugLocked) {
                        this.slug = this.name
                            .toLowerCase()
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/(^-|-$)+/g, '');
                    }
                },

                setIcon(iconVal) {
                    this.icon = iconVal;
                },

                renderIconSvg() {
                    const val = (this.icon || 'palette').trim();
                    if (val.startsWith('<svg')) return val;

                    const isEmoji = /\p{Extended_Pictographic}/u.test(val);
                    if (isEmoji) {
                        return `<span class="text-xl leading-none select-none">${val}</span>`;
                    }

                    const k = val.toLowerCase();
                    const svgs = {
                        'palette': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.64 0 1.25.15 1.8.42A7 7 0 1119 14.5c0 1.93-1.57 3.5-3.5 3.5-1 0-1.5-.5-2.5-.5-.73 0-1.32.4-1.68 1A3.98 3.98 0 017 21z"/></svg>',
                        'media-design': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.64 0 1.25.15 1.8.42A7 7 0 1119 14.5c0 1.93-1.57 3.5-3.5 3.5-1 0-1.5-.5-2.5-.5-.73 0-1.32.4-1.68 1A3.98 3.98 0 017 21z"/></svg>',
                        'image': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                        'graphics': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                        'video': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
                        'multimedia': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
                        'music': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>',
                        'audio': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>',
                        'code': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>',
                        'developer-tools': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>',
                        'wrench': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                        'system-utilities': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                        'shield': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                        'security': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                        'gamepad': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0-4h2m-2 0h-2m-8 6h.01M6 15h.01M18 9l2 7a2 2 0 01-2 2h-2.5a2 2 0 01-1.6-.8L12 14.5l-1.9 2.7A2 2 0 018.5 18H6a2 2 0 01-2-2l2-7a4 4 0 014-3h4a4 4 0 014 3z"/></svg>',
                        'games': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0-4h2m-2 0h-2m-8 6h.01M6 15h.01M18 9l2 7a2 2 0 01-2 2h-2.5a2 2 0 01-1.6-.8L12 14.5l-1.9 2.7A2 2 0 018.5 18H6a2 2 0 01-2-2l2-7a4 4 0 014-3h4a4 4 0 014 3z"/></svg>',
                        'globe': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>',
                        'internet': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>',
                        'briefcase': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                        'productivity-business': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                        'app': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
                        'applications': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
                        'book': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                        'education': '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                    };

                    return svgs[k] || '<svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>';
                }
            };
        }

        window.categoryForm = categoryForm;
        if (window.Alpine) {
            Alpine.data('categoryForm', categoryForm);
        } else {
            document.addEventListener('alpine:init', () => {
                Alpine.data('categoryForm', categoryForm);
            });
        }
    </script>
    @endpush
</x-admin-layout>
