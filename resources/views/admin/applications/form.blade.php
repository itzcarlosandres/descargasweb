<x-admin-layout>
    @section('title', (isset($app) && $app->exists) ? 'Editar: ' . $app->name : 'Publicar Nuevo Programa')
    @section('header', (isset($app) && $app->exists) ? 'Editar Programa' : 'Publicar Nuevo Programa')

    <div x-data="{
        name: '{{ addslashes(old('name', $app->name ?? '')) }}',
        slug: '{{ addslashes(old('slug', $app->slug ?? '')) }}',
        slugLocked: {{ (isset($app) && $app->exists) ? 'true' : 'false' }},
        version: '{{ addslashes(old('version', $app->version ?? '')) }}',
        sizeValue: '{{ addslashes(preg_replace('/[^0-9.]/', '', old('size', $app->size ?? ''))) }}',
        sizeUnit: '{{ str_contains(strtoupper(old('size', $app->size ?? '')), 'GB') ? 'GB' : 'MB' }}',
        platform: '{{ addslashes(old('platform', $app->platform ?? 'macOS Universal (Apple Silicon & Intel)')) }}',
        license: '{{ addslashes(old('license', $app->license ?? 'Full Pre-activado')) }}',
        developer: '{{ addslashes(old('developer', $app->developer ?? '')) }}',
        category_id: '{{ old('category_id', $app->category_id ?? '') }}',
        iconPreview: '{{ (isset($app) && $app->icon) ? $app->icon_url : '' }}',
        screenshotPreview: '{{ (isset($app) && $app->screenshot) ? $app->screenshot_url : '' }}',
        published: {{ old('published', (isset($app) && $app->exists) ? ($app->published ? 'true' : 'false') : 'true') }},
        featured: {{ old('featured', ($app->featured ?? false) ? 'true' : 'false') }},
        popular: {{ old('popular', ($app->popular ?? false) ? 'true' : 'false') }},
        mirrors: @js(old('download_mirrors', $app->download_mirrors ?? [])),
        addMirror() {
            this.mirrors.push({ name: '', url: '' });
        },
        removeMirror(idx) {
            this.mirrors.splice(idx, 1);
        },
        htmlMode: false,
        toggleHtmlMode() {
            this.htmlMode = !this.htmlMode;
            const descEl = document.getElementById('app-description');
            if (this.htmlMode) {
                if (window.quillDescEditor && descEl) {
                    const html = window.quillDescEditor.root.innerHTML;
                    descEl.value = (html === '<p><br></p>') ? '' : html;
                }
            } else {
                if (window.quillDescEditor && descEl) {
                    window.quillDescEditor.root.innerHTML = descEl.value;
                }
            }
        },
        generatingAi: false,
        aiNotification: null,

        async generateWithAi() {
            if (!confirm('¿Deseas generar la descripción enriquecida (412 palabras en inglés) y 7 características con Gemini 2.5?')) return;
            this.generatingAi = true;
            this.aiNotification = null;
            try {
                const res = await fetch('{{ (isset($app) && $app->exists) ? route('admin.applications.generate-ai', $app) : '#' }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const descEl = document.getElementById('app-description');
                    if (descEl) descEl.value = data.description;
                    if (this.$refs.descTextarea) this.$refs.descTextarea.value = data.description;
                    if (window.quillDescEditor) window.quillDescEditor.root.innerHTML = data.description;
                    if (this.$refs.featuresTextarea) this.$refs.featuresTextarea.value = data.features;
                    this.aiNotification = { success: true, message: data.message };
                } else {
                    this.aiNotification = { success: false, message: data.message || 'Error al generar contenido' };
                }
            } catch (e) {
                this.aiNotification = { success: false, message: 'Error de red: ' + e.message };
            } finally {
                this.generatingAi = false;
            }
        },

        init() {
            if (!this.slug && this.name) {
                this.updateSlug();
            }
        },

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

        previewIcon(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.iconPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        previewScreenshot(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.screenshotPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        get formattedSize() {
            if (!this.sizeValue) return '';
            return `${this.sizeValue} ${this.sizeUnit}`;
        }
    }">

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-[#26211B]">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.applications') }}"
                       class="p-2 rounded-xl bg-[#1A1612] hover:bg-[#25201A] text-[#8C847A] hover:text-white transition-colors border border-[#2B241C]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-tight">
                            {{ (isset($app) && $app->exists) ? 'Editar: ' . $app->name : 'Publicar Nuevo Programa macOS' }}
                        </h1>
                        <p class="text-xs text-[#8C847A] mt-0.5">
                            Completa los campos para publicar o actualizar el software en el portal.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.applications') }}" class="btn-secondary text-xs px-4 py-2">
                    Cancelar
                </a>
                <button type="submit" form="application-form"
                        class="btn-primary text-xs px-5 py-2.5 gap-2 shadow-lg shadow-primary/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ (isset($app) && $app->exists) ? 'Guardar Cambios' : 'Publicar Programa' }}</span>
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-white text-xs">
                <div class="flex items-center gap-2 font-bold text-danger mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Por favor corrige los siguientes errores:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-[#E0D7D0]">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="application-form"
              action="{{ (isset($app) && $app->exists) ? route('admin.applications.update', $app) : route('admin.applications.store') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @if(isset($app) && $app->exists)
                @method('PUT')
            @endif

            <!-- Hidden input to submit combined formatted size -->
            <input type="hidden" name="size" :value="formattedSize">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- ================= LEFT COLUMN: MAIN SPECS & INFO (7 or 8 Cols) ================= -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- Card 1: General Info -->
                    <div class="card p-6 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-[#26211B]">
                            <div class="w-7 h-7 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Información Principal</h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Nombre del Programa <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       x-model="name"
                                       @input="updateSlug()"
                                       placeholder="Ej: Final Cut Pro, CleanMyMac X, Adobe Photoshop..."
                                       value="{{ old('name', $app->name ?? '') }}"
                                       class="input w-full text-sm font-medium"
                                       required>
                                @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Slug with lock/unlock toggle -->
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
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[#6B635A] font-mono">/app/</span>
                                    <input type="text"
                                           name="slug"
                                           x-model="slug"
                                           :readonly="slugLocked"
                                           placeholder="final-cut-pro"
                                           class="input w-full pl-14 text-xs font-mono"
                                           :class="slugLocked ? 'opacity-80 bg-[#171411]' : ''">
                                </div>
                                <p class="text-[11px] text-[#6B635A] mt-1">Se genera automáticamente. No uses espacios ni caracteres especiales.</p>
                            </div>

                            <!-- Category & Developer -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                        Categoría <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id"
                                            x-model="category_id"
                                            class="input w-full text-xs font-medium cursor-pointer"
                                            required>
                                        <option value="">-- Seleccionar Categoría --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id', $app->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                        Desarrollador / Empresa
                                    </label>
                                    <input type="text"
                                           name="developer"
                                           x-model="developer"
                                           value="{{ old('developer', $app->developer ?? '') }}"
                                           placeholder="Ej: Apple Inc., Adobe, JetBrains..."
                                           class="input w-full text-xs">
                                </div>
                            </div>

                            <!-- Short Description -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Descripción Corta (Resumen para tarjetas y SEO)
                                </label>
                                <input type="text"
                                       name="short_description"
                                       value="{{ old('short_description', $app->short_description ?? '') }}"
                                       placeholder="Ej: Potente editor de video profesional optimizado para chips Apple Silicon M-series."
                                       maxlength="250"
                                       class="input w-full text-xs">
                            </div>

                            <!-- AI Content Generator & Feedback Banner -->
                            @if(isset($app) && $app->exists)
                                <div class="p-3.5 bg-gradient-to-r from-primary/10 via-[#1C1712] to-[#14110E] border border-primary/30 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-white block">Asistente Editorial Gemini 2.5</span>
                                            <span class="text-[11px] text-[#A8A199]">Genera ~4 renglones con SEO interno a categorías y 7 características de alto impacto</span>
                                        </div>
                                    </div>

                                    <button type="button" @click="generateWithAi()" :disabled="generatingAi"
                                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-md shadow-primary/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 shrink-0">
                                        <svg x-show="!generatingAi" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                        <svg x-show="generatingAi" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span x-text="generatingAi ? 'Generando con Gemini...' : '✨ Generar con Gemini 2.5'"></span>
                                    </button>
                                </div>

                                <div x-show="aiNotification" x-cloak class="mt-2">
                                    <div :class="aiNotification?.success ? 'bg-success/15 border-success/30 text-success' : 'bg-danger/15 border-danger/30 text-danger'"
                                         class="p-3 rounded-xl border text-xs flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" :class="aiNotification?.success ? 'bg-success' : 'bg-danger'"></span>
                                        <span x-text="aiNotification?.message"></span>
                                    </div>
                                </div>
                            @endif

                            <!-- Full Description with Rich Text Editor -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                    <label class="text-xs font-semibold text-[#A39B91] flex items-center gap-2">
                                        <span>Descripción Concisa (~4 renglones en inglés con SEO interno)</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">Editor Visual</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                @click="toggleHtmlMode()"
                                                class="text-[11px] px-2.5 py-1 rounded-lg bg-[#181410] hover:bg-[#201B15] text-[#A39B91] hover:text-white border border-[#2D261E] transition-colors flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                            </svg>
                                            <span x-text="htmlMode ? '← Volver al Editor Visual' : 'Ver / Editar Código HTML'"></span>
                                        </button>
                                        <span class="text-[10px] text-[#736B63] hidden sm:inline">Párrafo &lt;p&gt; con enlace &lt;a&gt;</span>
                                    </div>
                                </div>

                                <!-- Rich Text WYSIWYG Editor Container -->
                                <div x-show="!htmlMode" class="quill-editor-wrapper">
                                    <div id="quill-toolbar">
                                        <span class="ql-formats">
                                            <select class="ql-header">
                                                <option value="1">Título 1 (H1)</option>
                                                <option value="2">Título 2 (H2)</option>
                                                <option value="3">Título 3 (H3)</option>
                                                <option selected>Párrafo</option>
                                            </select>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-bold" title="Negrita (Ctrl+B)"></button>
                                            <button class="ql-italic" title="Cursiva (Ctrl+I)"></button>
                                            <button class="ql-underline" title="Subrayado (Ctrl+U)"></button>
                                            <button class="ql-strike" title="Tachado"></button>
                                        </span>
                                        <span class="ql-formats">
                                            <select class="ql-color" title="Color de texto"></select>
                                            <select class="ql-background" title="Color de resaltado"></select>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-list" value="ordered" title="Lista numerada"></button>
                                            <button class="ql-list" value="bullet" title="Lista con viñetas"></button>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-link" title="Insertar Enlace SEO"></button>
                                            <button class="ql-clean" title="Limpiar Formato"></button>
                                        </span>
                                    </div>
                                    <div id="quill-editor"></div>
                                </div>

                                <!-- Textarea for Form Submission & Raw HTML Mode -->
                                <textarea name="description"
                                          id="app-description"
                                          x-ref="descTextarea"
                                          rows="7"
                                          :class="htmlMode ? 'block' : 'hidden'"
                                          placeholder="Detalla qué hace el software en inglés con formato editorial para macOS..."
                                          class="input w-full text-xs resize-y leading-relaxed font-mono">{{ old('description', $app->description ?? '') }}</textarea>
                            </div>

                            <!-- Features List -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5 flex items-center justify-between">
                                    <span>Características Clave (Features - 7 viñetas en inglés)</span>
                                    <span class="text-[10px] text-primary font-mono">Renderiza con viñetas brillantes naranjas</span>
                                </label>
                                <textarea name="features"
                                          x-ref="featuresTextarea"
                                          rows="6"
                                          placeholder="<ul>&#10;  <li><strong>Native Apple Silicon:</strong> Optimized for M-series chips...</li>&#10;</ul>"
                                          class="input w-full text-xs resize-y leading-relaxed font-mono">{{ old('features', $app->features ?? '') }}</textarea>
                                <p class="text-[11px] text-[#6B635A] mt-1">Formato: <code>&lt;ul&gt;&lt;li&gt;&lt;strong&gt;Título:&lt;/strong&gt; Descripción&lt;/li&gt;&lt;/ul&gt;</code></p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Version, File Size & Compatibility -->
                    <div class="card p-6 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-[#26211B]">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Versión, Tamaño y Compatibilidad</h2>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Version Input -->
                                <div>
                                    <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                        Número de Versión
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="version"
                                               x-model="version"
                                               placeholder="v10.8.1 o 2024.3"
                                               class="input w-full text-xs font-mono font-medium">
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        <span class="text-[10px] text-[#6B635A]">Sugerencias:</span>
                                        <button type="button" @click="version = 'v1.0.0'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">v1.0.0</button>
                                        <button type="button" @click="version = 'v2024.1'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">v2024.1</button>
                                        <button type="button" @click="version = 'Latest'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">Latest</button>
                                    </div>
                                </div>

                                <!-- Formatted Size Selector with Unit Switcher -->
                                <div>
                                    <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                        Tamaño de Descarga
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               x-model="sizeValue"
                                               placeholder="450"
                                               class="input flex-1 text-xs font-mono font-medium">
                                        <select x-model="sizeUnit"
                                                class="input w-24 text-xs font-bold text-primary cursor-pointer">
                                            <option value="MB">MB</option>
                                            <option value="GB">GB</option>
                                            <option value="KB">KB</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        <span class="text-[10px] text-[#6B635A]">Rápidos:</span>
                                        <button type="button" @click="sizeValue = '150'; sizeUnit = 'MB'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">150 MB</button>
                                        <button type="button" @click="sizeValue = '1.2'; sizeUnit = 'GB'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">1.2 GB</button>
                                        <button type="button" @click="sizeValue = '3.5'; sizeUnit = 'GB'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">3.5 GB</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Platform & License Chips -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                        Plataforma / Arquitectura
                                    </label>
                                    <input type="text"
                                           name="platform"
                                           x-model="platform"
                                           placeholder="macOS Universal (Apple Silicon & Intel)"
                                           class="input w-full text-xs">
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        <button type="button" @click="platform = 'macOS Universal (M1/M2/M3 & Intel)'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">Universal</button>
                                        <button type="button" @click="platform = 'Apple Silicon Solo (ARM64)'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">M-Series</button>
                                        <button type="button" @click="platform = 'macOS 13+ Ventura o Superior'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">macOS 13+</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                        Tipo de Licencia
                                    </label>
                                    <input type="text"
                                           name="license"
                                           x-model="license"
                                           placeholder="Full Pre-activado"
                                           class="input w-full text-xs">
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        <button type="button" @click="license = 'Full Pre-activado'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">Pre-activado</button>
                                        <button type="button" @click="license = 'Open Source / Gratis'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">Open Source</button>
                                        <button type="button" @click="license = 'Freeware'" class="px-2 py-0.5 rounded text-[10px] bg-[#1F1B16] text-[#A39B91] hover:text-white border border-[#2D261E]">Freeware</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Changelog / What's new in this version -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Novedades de la Versión (Changelog)
                                </label>
                                <textarea name="changelog"
                                          rows="3"
                                          placeholder="• Corrección de compatibilidad con macOS Sequoia&#10;• Mejoras de rendimiento en renderizado Apple Silicon&#10;• Nuevas herramientas integradas..."
                                          class="input w-full text-xs font-mono leading-relaxed">{{ old('changelog', isset($app) ? ($app->currentVersion->changelog ?? '') : '') }}</textarea>
                                <p class="text-[11px] text-[#6B635A] mt-1">Este registro se mostrará a los usuarios en la pestaña de versiones.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Download Servers & Links -->
                    <div class="card p-6 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center justify-between pb-4 mb-5 border-b border-[#26211B] flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Servidores de Descarga</h2>
                                    <p class="text-[11px] text-[#8C847A]">Gestiona los enlaces directos y servidores alternativos.</p>
                                </div>
                            </div>
                            <button type="button" @click="addMirror()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary/15 hover:bg-primary/25 border border-primary/30 text-primary text-xs font-bold transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>+ Agregar Servidor / Enlace</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Enlace Principal -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Enlace de Descarga Directa (Servidor Primario / CDN / Mega / Drive)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B635A]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                    </span>
                                    <input type="url"
                                           name="download_url"
                                           value="{{ old('download_url', $app->download_url ?? '') }}"
                                           placeholder="https://descargas.servidor.com/archivo.dmg"
                                           class="input w-full pl-10 text-xs font-mono">
                                </div>
                                <p class="text-[11px] text-[#6B635A] mt-1">Enlace principal que iniciará la descarga directa.</p>
                            </div>

                            <!-- Enlace Alternativo / Web Oficial -->
                            <div>
                                <label class="block text-xs font-semibold text-[#A39B91] mb-1.5">
                                    Enlace Alternativo / Espejo / Web Oficial
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B635A]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </span>
                                    <input type="url"
                                           name="download_url_external"
                                           value="{{ old('download_url_external', $app->download_url_external ?? '') }}"
                                           placeholder="https://sitiooficial.com/descargas"
                                           class="input w-full pl-10 text-xs font-mono">
                                </div>
                                <p class="text-[11px] text-[#6B635A] mt-1">Servidor secundario o enlace de respaldo para usuarios.</p>
                            </div>

                            <!-- Lista Dinámica de Servidores Adicionales (Mirrors) -->
                            <div class="pt-4 border-t border-[#26211B] space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">
                                            Servidores &amp; Enlaces Adicionales (Mirrors)
                                        </h3>
                                        <p class="text-[11px] text-[#8C847A]">Añade múltiples servidores alternativos (Mega, Google Drive, Mediafire, 1Fichier, etc.).</p>
                                    </div>
                                    <span class="text-[11px] font-mono font-bold text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded border border-emerald-400/20"
                                          x-text="mirrors.length + ' servidor(es)'"></span>
                                </div>

                                <!-- Dynamic Mirror Rows -->
                                <template x-for="(mirror, index) in mirrors" :key="index">
                                    <div class="p-3.5 rounded-xl bg-[#1A1612] border border-[#2B241C] space-y-2.5 transition-all">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] font-bold text-emerald-400 font-mono flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                <span x-text="'Servidor #' + (index + 1)"></span>
                                            </span>
                                            <button type="button" @click="removeMirror(index)"
                                                    class="text-[#8C847A] hover:text-danger text-xs font-medium flex items-center gap-1 transition-colors cursor-pointer"
                                                    title="Eliminar este servidor">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Quitar</span>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                            <!-- Nombre o Proveedor del Servidor -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#8C847A] uppercase tracking-wider mb-1">Nombre / Servidor</label>
                                                <input type="text"
                                                       :name="'download_mirrors[' + index + '][name]'"
                                                       x-model="mirror.name"
                                                       placeholder="Mega, Google Drive, Mediafire..."
                                                       class="input w-full text-xs"
                                                       list="server-suggestions">
                                            </div>

                                            <!-- Enlace URL -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-[10px] font-bold text-[#8C847A] uppercase tracking-wider mb-1">URL de Descarga Directa</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B635A]">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                        </svg>
                                                    </span>
                                                    <input type="url"
                                                           :name="'download_mirrors[' + index + '][url]'"
                                                           x-model="mirror.url"
                                                           placeholder="https://..."
                                                           class="input w-full pl-9 text-xs font-mono"
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Datalist for Quick Host Autocomplete -->
                                <datalist id="server-suggestions">
                                    <option value="Mega">
                                    <option value="Google Drive">
                                    <option value="MediaFire">
                                    <option value="1Fichier">
                                    <option value="OneDrive">
                                    <option value="Rapidgator">
                                    <option value="DDownload">
                                    <option value="Katfile">
                                    <option value="Pixeldrain">
                                    <option value="Dropbox">
                                    <option value="Servidor Espejo">
                                </datalist>

                                <!-- Empty State when no extra mirrors -->
                                <div x-show="mirrors.length === 0"
                                     class="p-4 rounded-xl border border-dashed border-[#2B241C] text-center bg-[#100E0C]">
                                    <p class="text-xs text-[#8C847A]">No hay enlaces adicionales agregados aún.</p>
                                    <button type="button" @click="addMirror()"
                                            class="mt-2 text-xs text-primary hover:underline font-bold cursor-pointer inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Añadir un servidor alternativo (Mega, Drive, etc.)</span>
                                    </button>
                                </div>

                                <!-- Add Button at Bottom of List -->
                                <div x-show="mirrors.length > 0" class="pt-1">
                                    <button type="button" @click="addMirror()"
                                            class="w-full py-2.5 px-3 rounded-xl border border-dashed border-primary/40 hover:border-primary text-primary hover:bg-primary/5 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>+ Agregar otro servidor de descarga</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= RIGHT COLUMN: MEDIA, PREVIEW & PUBLISH CONTROLS (4 Cols) ================= -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- Publishing Status & Visibility Card -->
                    <div class="card p-5 border-[#2B241C] bg-[#141210] shadow-xl">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Estado y Visibilidad</h3>
                            <span class="w-2.5 h-2.5 rounded-full" :class="published ? 'bg-success' : 'bg-warning'"></span>
                        </div>

                        <div class="space-y-4">
                            <!-- Toggle: Published -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] border border-[#2B241C]">
                                <div>
                                    <p class="text-xs font-bold text-white">Publicado en la Web</p>
                                    <p class="text-[10px] text-[#8C847A]" x-text="published ? 'Visible para todos los usuarios' : 'Guardado como borrador privado'"></p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="published" value="1" x-model="published" class="sr-only peer">
                                    <div class="w-10 h-5 bg-[#2B241C] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-success"></div>
                                </label>
                            </div>

                            <!-- Toggle: Featured -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] border border-[#2B241C]">
                                <div>
                                    <p class="text-xs font-bold text-white flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span>Destacado Portada</span>
                                    </p>
                                    <p class="text-[10px] text-[#8C847A]">Aparece en carrusel y cabecera</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="featured" value="1" x-model="featured" class="sr-only peer">
                                    <div class="w-10 h-5 bg-[#2B241C] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>

                            <!-- Toggle: Popular -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#1A1612] border border-[#2B241C]">
                                <div>
                                    <p class="text-xs font-bold text-white flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        <span>Apps Populares</span>
                                    </p>
                                    <p class="text-[10px] text-[#8C847A]">Top descargas y valoraciones</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="popular" value="1" x-model="popular" class="sr-only peer">
                                    <div class="w-10 h-5 bg-[#2B241C] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-warning"></div>
                                </label>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 border-t border-[#26211B] flex flex-col gap-2">
                                <button type="submit"
                                        class="btn-primary w-full py-2.5 text-xs font-bold justify-center gap-2 shadow-lg shadow-primary/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ (isset($app) && $app->exists) ? 'Actualizar Aplicación' : 'Guardar y Publicar' }}</span>
                                </button>
                                
                                @if(isset($app) && $app->exists)
                                    <a href="{{ route('app', $app->slug) }}" target="_blank"
                                       class="btn-secondary w-full py-2 text-xs font-medium justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        <span>Ver en Portal Web</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card: App Icon with Squircle Preview & Drag-and-Drop -->
                    <div class="card p-5 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Icono del Programa (macOS Squircle)</h3>
                            <span class="text-[10px] text-primary font-bold">512×512</span>
                        </div>

                        <div class="space-y-4">
                            <!-- Squircle Live Icon Mockup -->
                            <div class="flex flex-col items-center justify-center p-4 rounded-2xl bg-[#0E0D0B] border border-[#2B241C]">
                                <div class="relative group">
                                    <!-- Squircle Frame -->
                                    <div class="w-24 h-24 rounded-[22px] bg-gradient-to-b from-[#2A241E] to-[#161310] p-0.5 shadow-2xl shadow-black/80 flex items-center justify-center overflow-hidden border border-[#3E342A]">
                                        <template x-if="iconPreview">
                                            <img :src="iconPreview" alt="Icon Preview" class="w-full h-full object-cover rounded-[20px]">
                                        </template>
                                        <template x-if="!iconPreview">
                                            <div class="flex flex-col items-center justify-center text-[#736B63] p-2 text-center">
                                                <svg class="w-8 h-8 mb-1 text-primary/60" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                                </svg>
                                                <span class="text-[9px] uppercase font-bold tracking-wider">Sin Icono</span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-primary flex items-center justify-center text-white shadow-md">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-[11px] text-[#A39B91] mt-3 font-medium">Previsualización exacta del Icono</span>
                            </div>

                            <!-- File Drag and Drop Zone -->
                            <div class="relative border-2 border-dashed border-[#3A3025] hover:border-primary/60 rounded-xl p-4 text-center transition-colors bg-[#181410]/50 cursor-pointer">
                                <input type="file"
                                       name="icon"
                                       accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                       @change="previewIcon($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <div class="space-y-1">
                                    <svg class="w-6 h-6 text-primary mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-white">Haz clic o arrastra un icono aquí</p>
                                    <p class="text-[10px] text-[#7E776F]">PNG transparente, WebP o SVG (máx. 4MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Screenshot / Captura de Pantalla con macOS Window Frame -->
                    <div class="card p-5 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Captura de Pantalla</h3>
                            <span class="text-[10px] text-[#8C847A]">Opcional</span>
                        </div>

                        <div class="space-y-4">
                            <!-- macOS Window Preview -->
                            <div class="rounded-xl overflow-hidden bg-[#0E0D0B] border border-[#2B241C] shadow-lg">
                                <div class="h-6 bg-[#1A1612] border-b border-[#2B241C] flex items-center px-3 gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56] inline-block"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E] inline-block"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F] inline-block"></span>
                                    <span class="ml-2 text-[9px] text-[#736B63] font-mono truncate" x-text="name ? name + ' - Screenshot' : 'App Window'"></span>
                                </div>
                                <div class="aspect-video bg-[#12100E] flex items-center justify-center overflow-hidden">
                                    <template x-if="screenshotPreview">
                                        <img :src="screenshotPreview" alt="Screenshot Preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!screenshotPreview">
                                        <div class="p-6 text-center text-[#6B635A]">
                                            <svg class="w-8 h-8 mx-auto mb-1 text-[#423C35]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-[10px]">Sin captura seleccionada</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="relative border-2 border-dashed border-[#3A3025] hover:border-primary/60 rounded-xl p-3 text-center transition-colors bg-[#181410]/50 cursor-pointer">
                                <input type="file"
                                       name="screenshot"
                                       accept="image/png,image/jpeg,image/webp"
                                       @change="previewScreenshot($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <div class="space-y-0.5">
                                    <p class="text-xs font-semibold text-white">Subir captura de pantalla</p>
                                    <p class="text-[10px] text-[#7E776F]">1920×1080 o superior (JPG, PNG, WebP)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Realtime Live Catalog Card Preview -->
                    <div class="card p-5 border-[#2B241C] bg-[#141210]">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#26211B]">
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Simulador en Tiempo Real</h3>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-primary/20 text-primary font-bold">Live Preview</span>
                        </div>

                        <p class="text-[11px] text-[#8C847A] mb-3">Así se verá tu aplicación en la cuadrícula de inicio:</p>

                        <!-- Simulated App Card -->
                        <div class="p-3.5 rounded-2xl bg-[#1B1713] border border-[#2B241C] shadow-lg">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-[14px] bg-gradient-to-b from-[#2B241C] to-[#181410] border border-[#3E3428] flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <template x-if="iconPreview">
                                        <img :src="iconPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!iconPreview">
                                        <span class="text-sm font-bold text-primary" x-text="name ? name.substring(0, 1).toUpperCase() : 'A'"></span>
                                    </template>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-extrabold text-white truncate" x-text="name || 'Título de la Aplicación'"></p>
                                    <p class="text-[11px] text-[#8C847A] truncate mt-0.5" x-text="developer || 'Desarrollador'"></p>
                                    
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#27211A] text-[#B6B0A8] font-mono" x-text="version || 'v1.0.0'"></span>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-primary/10 text-primary font-bold" x-text="formattedSize || '100 MB'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        /* Quill Dark macOS Editor Styling */
        .quill-editor-wrapper {
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid #2D261E;
            background-color: #12100E;
        }
        .ql-toolbar.ql-snow {
            background-color: #181410 !important;
            border: none !important;
            border-bottom: 1px solid #26211B !important;
            padding: 8px 12px !important;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .ql-container.ql-snow {
            background-color: #12100E !important;
            border: none !important;
            color: #F5EFEB !important;
            font-family: inherit !important;
            font-size: 0.8125rem !important;
        }
        .ql-editor {
            min-height: 180px;
            line-height: 1.65;
            padding: 14px 16px;
        }
        .ql-editor p {
            margin-bottom: 0.5rem;
        }
        .ql-editor h1 {
            font-size: 1.4rem;
            font-weight: 800;
            margin-top: 0.75rem;
            margin-bottom: 0.5rem;
            color: #FFFFFF;
        }
        .ql-editor h2 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 0.6rem;
            margin-bottom: 0.4rem;
            color: #FFFFFF;
        }
        .ql-editor h3 {
            font-size: 1.05rem;
            font-weight: 600;
            margin-top: 0.5rem;
            margin-bottom: 0.3rem;
            color: #FFFFFF;
        }
        .ql-editor a {
            color: #38BDF8 !important;
            text-decoration: underline;
        }
        .ql-editor.ql-blank::before {
            color: #736B63 !important;
            font-style: normal !important;
            left: 16px !important;
            right: 16px !important;
        }
        /* Toolbar Icons and Pickers */
        .ql-snow .ql-stroke {
            stroke: #A39B91 !important;
        }
        .ql-snow .ql-fill {
            fill: #A39B91 !important;
        }
        .ql-snow .ql-picker {
            color: #A39B91 !important;
        }
        .ql-snow .ql-picker.ql-header {
            width: 105px !important;
        }
        .ql-snow .ql-picker-label {
            border-radius: 6px;
            padding: 2px 6px !important;
            display: flex !important;
            align-items: center !important;
            white-space: nowrap !important;
            height: auto !important;
            min-height: 26px !important;
            line-height: 1.4 !important;
            font-size: 11.5px !important;
            font-weight: 500 !important;
        }
        .ql-snow .ql-picker-label svg {
            flex-shrink: 0;
            margin-left: auto;
        }
        .ql-snow .ql-picker-label:hover {
            background-color: #221C16;
            color: #FFFFFF !important;
        }
        .ql-snow .ql-picker-label:hover .ql-stroke {
            stroke: #FFFFFF !important;
        }
        .ql-snow .ql-picker-options {
            background-color: #1A1612 !important;
            border: 1px solid #2D261E !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6) !important;
            border-radius: 0.5rem !important;
            padding: 6px !important;
            z-index: 50 !important;
            min-width: 130px !important;
            white-space: nowrap !important;
        }
        .ql-snow .ql-picker-item {
            color: #A39B91 !important;
            border-radius: 0.25rem !important;
            padding: 4px 8px !important;
            white-space: nowrap !important;
            font-size: 12px !important;
        }
        .ql-snow .ql-picker-item:hover,
        .ql-snow .ql-picker-item.ql-selected {
            color: #FFFFFF !important;
            background-color: #27211A !important;
        }
        .ql-snow button {
            border-radius: 6px !important;
            padding: 3px 5px !important;
        }
        .ql-snow button:hover,
        .ql-snow button.ql-active {
            background-color: #221C16 !important;
        }
        .ql-snow button:hover .ql-stroke,
        .ql-snow button.ql-active .ql-stroke {
            stroke: #0071E3 !important;
        }
        .ql-snow button:hover .ql-fill,
        .ql-snow button.ql-active .ql-fill {
            fill: #0071E3 !important;
        }
        .ql-snow .ql-tooltip {
            background-color: #1A1612 !important;
            border: 1px solid #2D261E !important;
            color: #F5EFEB !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6) !important;
            border-radius: 0.5rem !important;
            padding: 8px 12px !important;
            z-index: 50 !important;
        }
        .ql-snow .ql-tooltip input[type=text] {
            background-color: #12100E !important;
            border: 1px solid #2D261E !important;
            color: #FFFFFF !important;
            border-radius: 0.375rem !important;
            padding: 4px 8px !important;
        }
        .ql-snow .ql-tooltip a.ql-action,
        .ql-snow .ql-tooltip a.ql-remove {
            color: #0071E3 !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const descTextarea = document.getElementById('app-description');
        const editorContainer = document.getElementById('quill-editor');
        if (!descTextarea || !editorContainer) return;

        const quill = new Quill('#quill-editor', {
            modules: {
                toolbar: '#quill-toolbar'
            },
            placeholder: 'Detalla qué hace el software en inglés con formato editorial para macOS (puedes agregar negritas, encabezados H1/H2, colores y enlaces SEO)...',
            theme: 'snow'
        });
        window.quillDescEditor = quill;

        if (descTextarea.value && descTextarea.value.trim() !== '') {
            quill.root.innerHTML = descTextarea.value;
        }

        quill.on('text-change', function() {
            const html = quill.root.innerHTML;
            descTextarea.value = (html === '<p><br></p>') ? '' : html;
        });

        const form = descTextarea.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (window.quillDescEditor && descTextarea) {
                    const formContainer = document.querySelector('[x-data]');
                    const alpineData = window.Alpine ? Alpine.$data(formContainer) : null;
                    if (!alpineData || !alpineData.htmlMode) {
                        const html = window.quillDescEditor.root.innerHTML;
                        descTextarea.value = (html === '<p><br></p>') ? '' : html;
                    }
                }
            });
        }
    });
    </script>
    @endpush
</x-admin-layout>
