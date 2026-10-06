@extends('layouts.admin')

@section('title', 'Configuración de la Web')
@section('page-title', 'Configuración General')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    tab: '{{ request('tab', 'logo') }}',
    logoType: '{{ old('logo_type', $settings['logo_type'] ?? 'text_icon') }}',
    logoSize: {{ old('logo_size', (int) ($settings['logo_size'] ?? 24)) }},
    siteName: '{{ old('site_name', $settings['site_name'] ?? 'HackMac') }}',
    siteHighlight: '{{ old('site_name_highlight', $settings['site_name_highlight'] ?? '.cc') }}',
    logoIcon: '{{ old('logo_icon', $settings['logo_icon'] ?? 'finder') }}',
    seoTitle: '{{ old('seo_meta_title', $settings['seo_meta_title'] ?? '') }}',
    seoDesc: '{{ old('seo_meta_description', $settings['seo_meta_description'] ?? '') }}',
    geminiApiKey: '{{ old('gemini_api_key', $settings['gemini_api_key'] ?? '') }}',
    geminiModel: '{{ old('gemini_model', $settings['gemini_model'] ?? 'gemini-2.5-flash') }}',
    showApiKey: false,
    testingGemini: false,
    testGeminiResult: null,

    // Cloudflare R2 Storage State
    r2AccountId: '{{ old('r2_account_id', $settings['r2_account_id'] ?? env('CLOUDFLARE_R2_ACCOUNT_ID', '')) }}',
    r2AccessKeyId: '{{ old('r2_access_key_id', $settings['r2_access_key_id'] ?? env('CLOUDFLARE_R2_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', ''))) }}',
    r2SecretAccessKey: '{{ old('r2_secret_access_key', $settings['r2_secret_access_key'] ?? env('CLOUDFLARE_R2_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY', ''))) }}',
    r2Bucket: '{{ old('r2_bucket', $settings['r2_bucket'] ?? env('CLOUDFLARE_R2_BUCKET', env('AWS_BUCKET', ''))) }}',
    r2Url: '{{ old('r2_url', $settings['r2_url'] ?? env('CLOUDFLARE_R2_URL', '')) }}',
    torrentDisk: '{{ old('torrentmac_storage_disk', $settings['torrentmac_storage_disk'] ?? 'local') }}',
    showR2Secret: false,
    testingR2: false,
    testR2Result: null,

    async testGeminiConnection() {
        if (!this.geminiApiKey) {
            this.testGeminiResult = { success: false, message: 'Introduce una API Key antes de probar la conexión.' };
            return;
        }
        this.testingGemini = true;
        this.testGeminiResult = null;
        try {
            const res = await fetch('{{ route('admin.settings.test-gemini') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    api_key: this.geminiApiKey,
                    model: this.geminiModel
                })
            });
            const data = await res.json();
            this.testGeminiResult = data;
        } catch (e) {
            this.testGeminiResult = { success: false, message: 'Error de red al conectar: ' + e.message };
        } finally {
            this.testingGemini = false;
        }
    },

    async testR2Connection() {
        if (!this.r2AccountId || !this.r2AccessKeyId || !this.r2SecretAccessKey || !this.r2Bucket) {
            this.testR2Result = { success: false, message: 'Completa Account ID, Access Key ID, Secret Access Key y Bucket antes de probar la conexión.' };
            return;
        }
        this.testingR2 = true;
        this.testR2Result = null;
        try {
            const res = await fetch('{{ route('admin.settings.test-r2') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    account_id: this.r2AccountId,
                    access_key_id: this.r2AccessKeyId,
                    secret_access_key: this.r2SecretAccessKey,
                    bucket: this.r2Bucket,
                    url: this.r2Url
                })
            });
            const data = await res.json();
            this.testR2Result = data;
        } catch (e) {
            this.testR2Result = { success: false, message: 'Error de red al conectar con Cloudflare R2: ' + e.message };
        } finally {
            this.testingR2 = false;
        }
    }
}">
    <!-- Header with Traffic Lights & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#14110E] border border-[#2B241C] p-5 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-primary/15 border border-primary/30 flex items-center justify-center text-primary shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white tracking-tight">Configuración del Portal</h1>
                <p class="text-xs text-[#8C847A]">Personaliza la identidad visual, logo, SEO, metadatos, IA Gemini y descripciones públicas.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" form="settings-form"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-lg shadow-primary/25 transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2.5 shadow-lg">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-danger/10 border border-danger/30 text-danger text-xs font-medium space-y-2 shadow-lg">
            <div class="flex items-center gap-2 font-bold text-sm">
                <svg class="w-5 h-5 text-danger flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>No se pudieron guardar los cambios:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Navigation Pills / Tabs -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 p-1.5 bg-[#14110E] border border-[#262019] rounded-2xl">
        <button type="button" @click="tab = 'logo'"
                :class="tab === 'logo' ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-bold' : 'text-[#8C847A] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Identidad & Logo</span>
        </button>

        <button type="button" @click="tab = 'seo'"
                :class="tab === 'seo' ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-bold' : 'text-[#8C847A] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>SEO & Metadatos</span>
        </button>

        <button type="button" @click="tab = 'general'"
                :class="tab === 'general' ? 'bg-[#221C16] text-white border border-[#3A3025] shadow-sm font-bold' : 'text-[#8C847A] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Información & Textos</span>
        </button>

        <button type="button" @click="tab = 'gemini'"
                :class="tab === 'gemini' ? 'bg-primary/20 text-white border border-primary/40 shadow-sm font-bold' : 'text-[#8C847A] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span>🤖 IA Gemini 2.5</span>
        </button>

        <button type="button" @click="tab = 'storage'"
                :class="tab === 'storage' ? 'bg-[#F38020]/20 text-white border border-[#F38020]/50 shadow-sm font-bold' : 'text-[#8C847A] hover:text-white'"
                class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-[#F38020]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
            </svg>
            <span>☁️ Cloudflare R2</span>
        </button>
    </div>

    <!-- Main Settings Form -->
    <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="active_tab" :value="tab">

        <!-- TAB 1: IDENTIDAD & LOGO -->
        @php
            $availableIcons = [
                // Classic macOS & Apple
                'finder' => ['label' => 'Finder macOS', 'badge' => 'Classic'],
                'apple' => ['label' => 'Apple ', 'badge' => 'Brand'],
                'command' => ['label' => 'Command ⌘', 'badge' => 'Shortcut'],
                'terminal' => ['label' => 'Terminal >_', 'badge' => 'Dev'],
                // Hardware & Mac Apps (Lucide)
                'cpu' => ['label' => 'Silicon CPU', 'badge' => 'Lucide'],
                'laptop' => ['label' => 'MacBook', 'badge' => 'Lucide'],
                'app-window' => ['label' => 'Ventana App', 'badge' => 'Lucide'],
                'hard-drive' => ['label' => 'Disco DMG', 'badge' => 'Lucide'],
                'disc' => ['label' => 'Disco CD/DVD', 'badge' => 'Lucide'],
                'folder' => ['label' => 'Carpeta Apps', 'badge' => 'Lucide'],
                'package' => ['label' => 'Instalador', 'badge' => 'Lucide'],
                // Descargas & Conectividad (Lucide)
                'download' => ['label' => 'Descargas', 'badge' => 'Lucide'],
                'cloud-download' => ['label' => 'Cloud Sync', 'badge' => 'Lucide'],
                'globe' => ['label' => 'Web Global', 'badge' => 'Lucide'],
                'compass' => ['label' => 'Safari Brújula', 'badge' => 'Lucide'],
                // Rendimiento & Destacados (Lucide)
                'sparkles' => ['label' => 'Sparkles / IA', 'badge' => 'Lucide'],
                'rocket' => ['label' => 'Cohete Turbo', 'badge' => 'Lucide'],
                'flame' => ['label' => 'Popular / Top', 'badge' => 'Lucide'],
                'zap' => ['label' => 'Rayo Rápido', 'badge' => 'Lucide'],
                'shield-check' => ['label' => 'Verificado', 'badge' => 'Lucide'],
                'key' => ['label' => 'Licencias Key', 'badge' => 'Lucide'],
                // Creatividad & Especialidades (Lucide)
                'code' => ['label' => 'Dev Código', 'badge' => 'Lucide'],
                'layers' => ['label' => 'Capas / Stack', 'badge' => 'Lucide'],
                'palette' => ['label' => 'Diseño Gráfico', 'badge' => 'Lucide'],
                'music' => ['label' => 'Audio & Música', 'badge' => 'Lucide'],
                'film' => ['label' => 'Cine & Video', 'badge' => 'Lucide'],
                'gamepad' => ['label' => 'Juegos Mac', 'badge' => 'Lucide'],
                'wrench' => ['label' => 'Utilidades', 'badge' => 'Lucide'],
                'database' => ['label' => 'Base Datos', 'badge' => 'Lucide'],
            ];
        @endphp

        <div x-show="tab === 'logo'" class="space-y-6">
            <!-- Real-Time Navbar Preview Card -->
            <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-5 shadow-xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#241E18]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                        <span class="ml-2 text-xs font-bold uppercase tracking-wider text-[#736B63]">Previsualización en la Barra de Navegación</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-primary/20 text-primary font-mono font-bold">LIVE PREVIEW</span>
                </div>

                <!-- Mock Navbar Header -->
                <div class="h-16 px-6 bg-[rgba(28,25,21,0.95)] border border-[rgba(255,244,224,0.1)] rounded-xl flex items-center justify-between shadow-2xl backdrop-blur-md">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                        </span>

                        <!-- Dynamic Logo in Preview -->
                        <div class="flex items-center gap-2.5">
                            <!-- Image Logo Preview -->
                            <div x-show="logoType === 'image'" class="flex items-center">
                                @if(!empty($settings['logo_image']))
                                    <img src="{{ $settings['logo_image'] }}" alt="Logo" :style="'height: ' + (parseInt(logoSize) + 8) + 'px;'" class="max-w-[180px] object-contain transition-all">
                                @else
                                    <div :style="'height: ' + (parseInt(logoSize) + 8) + 'px;'" class="px-3 rounded bg-primary/20 border border-primary/40 text-primary flex items-center text-xs font-bold transition-all">
                                        [Logo Imagen Subida]
                                    </div>
                                @endif
                            </div>

                            <!-- Text + Icon Logo Preview -->
                            <div x-show="logoType === 'text_icon'" class="flex items-center gap-2.5">
                                <!-- Dynamic Logo Icon Preview -->
                                <div :style="'width: ' + (parseInt(logoSize) + 8) + 'px; height: ' + (parseInt(logoSize) + 8) + 'px;'" class="flex-shrink-0 relative transition-all">
                                    @foreach(array_keys($availableIcons) as $ic)
                                        <div x-show="logoIcon === '{{ $ic }}'" x-cloak class="w-full h-full">
                                            <x-logo-icon :icon="$ic" class="w-full h-full" />
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Logo Text -->
                                <span :style="'font-size: ' + logoSize + 'px; line-height: 1;'" class="text-white font-black tracking-[-0.03em] transition-all inline-flex items-center">
                                    <span x-text="siteName || 'Hax'"></span><span class="text-[#2997FF] font-black" x-text="siteHighlight || 'Mac'"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-4 text-xs text-[#8C847A]">
                        <span>Editor's Choice</span>
                        <span>Categorías</span>
                        <span>Novedades</span>
                        <div class="w-6 h-6 rounded-full bg-[#201C18] border border-[#302821] flex items-center justify-center text-[10px]">🔍</div>
                    </div>
                </div>
            </div>

            <!-- Logo Configuration Options -->
            <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    Modo del Logotipo
                </h3>

                <!-- Selection Radio Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Option 1: Texto + Icono -->
                    <label @click="logoType = 'text_icon'"
                           :class="logoType === 'text_icon' ? 'border-primary bg-primary/10 shadow-lg shadow-primary/10' : 'border-[#262019] bg-[#171411] hover:border-[#382E24]'"
                           class="p-4 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3.5 select-none">
                        <input type="radio" name="logo_type" value="text_icon" x-model="logoType" :checked="logoType === 'text_icon'" class="mt-1 text-primary focus:ring-primary">
                        <div class="pointer-events-none">
                            <p class="text-sm font-bold text-white">Logotipo en Letras e Icono</p>
                            <p class="text-xs text-[#8C847A] mt-1">Usa tipografía personalizada con sufijo destacado y un icono macOS Finder o Lucide.</p>
                        </div>
                    </label>

                    <!-- Option 2: Logo con Imagen -->
                    <label @click="logoType = 'image'"
                           :class="logoType === 'image' ? 'border-primary bg-primary/10 shadow-lg shadow-primary/10' : 'border-[#262019] bg-[#171411] hover:border-[#382E24]'"
                           class="p-4 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3.5 select-none">
                        <input type="radio" name="logo_type" value="image" x-model="logoType" :checked="logoType === 'image'" class="mt-1 text-primary focus:ring-primary">
                        <div class="pointer-events-none">
                            <p class="text-sm font-bold text-white">Logotipo en Imagen</p>
                            <p class="text-xs text-[#8C847A] mt-1">Sube una imagen propia (SVG, PNG transparente o WebP) para mostrar como logo oficial.</p>
                        </div>
                    </label>
                </div>

                <!-- Logo Size Slider Control -->
                <div class="pt-4 border-t border-[#241E18]">
                    <div class="bg-[#12100E] border border-[#2B241C] rounded-2xl p-4.5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                <div>
                                    <label class="block text-xs font-bold text-white uppercase tracking-wider">Tamaño del Logotipo</label>
                                    <p class="text-[11px] text-[#736B63] mt-0.5">Desliza para ajustar en tiempo real el tamaño del logo en la cabecera.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-lg bg-[#1E1914] border border-[#3A3025] text-primary font-mono font-bold text-xs shadow-inner" x-text="logoSize + ' px'"></span>
                            </div>
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <input type="range" name="logo_size" min="14" max="40" step="1"
                                   x-model.number="logoSize"
                                   @input="logoSize = Number($event.target.value)"
                                   class="w-full h-2 bg-[#241D17] rounded-lg appearance-none cursor-pointer accent-primary focus:outline-none">
                            <div class="flex justify-between text-[10px] text-[#736B63] font-mono px-0.5">
                                <span>14px (Muy compacto)</span>
                                <span>20px (Compacto)</span>
                                <span>25px (Estándar)</span>
                                <span>32px (Grande)</span>
                                <span>40px (Máximo)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text + Icon Settings Fields -->
                <div x-show="logoType === 'text_icon'" x-transition class="pt-4 border-t border-[#241E18] space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Nombre / Prefijo del Logo *</label>
                            <input type="text" name="site_name" x-model="siteName"
                                   class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                                   placeholder="Hax">
                            <p class="text-[11px] text-[#736B63] mt-1.5">Texto principal en color blanco (ejemplo: <strong>Hax</strong>).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Sufijo Destacado (Acento Azul)</label>
                            <input type="text" name="site_name_highlight" x-model="siteHighlight"
                                   class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                                   placeholder="Mac">
                            <p class="text-[11px] text-[#736B63] mt-1.5">Palabra final con acento de color azul estilo Apple (ejemplo: <strong>Mac</strong>).</p>
                        </div>
                    </div>

                    <!-- Icon Selector -->
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider">Icono del Logotipo ({{ count($availableIcons) }} opciones)</label>
                            <span class="text-[11px] text-[#7E776F]">Haz clic en cualquier icono para previsualizarlo arriba</span>
                        </div>

                        <!-- Dedicated hidden input ensuring exact value is submitted with the form -->
                        <input type="hidden" name="logo_icon" :value="logoIcon">

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7 gap-3">
                            @foreach($availableIcons as $key => $meta)
                                <div role="button"
                                     tabindex="0"
                                     @click="logoIcon = '{{ $key }}'"
                                     @keydown.enter.prevent="logoIcon = '{{ $key }}'"
                                     @keydown.space.prevent="logoIcon = '{{ $key }}'"
                                     :class="logoIcon === '{{ $key }}' ? 'border-primary bg-primary/15 shadow-md shadow-primary/10 ring-1 ring-primary' : 'border-[#262019] bg-[#12100E] hover:border-[#382E24] hover:bg-[#171411]'"
                                     class="p-3 rounded-xl border flex flex-col items-center gap-2 cursor-pointer transition-all text-center relative group select-none">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow pointer-events-none">
                                        <x-logo-icon :icon="$key" class="w-8 h-8" />
                                    </div>

                                    <span class="text-xs font-semibold text-white truncate max-w-full block pointer-events-none" title="{{ $meta['label'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-mono uppercase pointer-events-none {{ $meta['badge'] === 'Lucide' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : 'bg-[#221C16] text-[#8C847A] border border-[#302820]' }}">
                                        {{ $meta['badge'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Logo Image Upload Field -->
                <div x-show="logoType === 'image'" x-transition class="pt-4 border-t border-[#241E18] space-y-4">
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider">Subir Archivo de Imagen para el Logo</label>

                    @if(!empty($settings['logo_image']))
                    <div class="p-4 rounded-xl bg-[#12100E] border border-[#2B241C] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $settings['logo_image'] }}" alt="Logo actual" class="h-10 max-w-[180px] object-contain rounded">
                            <div>
                                <p class="text-xs font-bold text-white">Logo actual activo</p>
                                <span class="text-[10px] text-[#736B63] font-mono">{{ $settings['logo_image'] }}</span>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-danger hover:text-danger/80 cursor-pointer">
                            <input type="checkbox" name="remove_logo_image" value="1" class="rounded text-danger focus:ring-danger">
                            <span>Eliminar logo</span>
                        </label>
                    </div>
                    @endif

                    <div class="border-2 border-dashed border-[#2B241C] hover:border-primary/50 rounded-2xl p-6 text-center transition-colors">
                        <input type="file" name="logo_image" id="logo_image" accept="image/png,image/svg+xml,image/webp" class="hidden"
                               onchange="document.getElementById('logo-preview-name').textContent = this.files[0]?.name || ''">
                        <label for="logo_image" class="cursor-pointer flex flex-col items-center">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white">Selecciona una imagen de logotipo</span>
                            <span class="text-[11px] text-[#736B63] mt-1">Formato SVG recomendado, PNG transparente o WebP (Máx. 2MB)</span>
                            <span id="logo-preview-name" class="text-xs text-primary font-mono mt-2"></span>
                        </label>
                    </div>
                </div>

                <!-- Favicon Upload -->
                <div class="pt-5 border-t border-[#241E18] space-y-3">
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider">Favicon del Navegador (.ico, .svg, .png)</label>

                    @if(!empty($settings['favicon_image']))
                    <div class="p-3 rounded-xl bg-[#12100E] border border-[#2B241C] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $settings['favicon_image'] }}" alt="Favicon" class="w-7 h-7 object-contain">
                            <span class="text-xs text-white font-medium">Favicon actual</span>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-danger cursor-pointer">
                            <input type="checkbox" name="remove_favicon_image" value="1" class="rounded text-danger">
                            <span>Quitar</span>
                        </label>
                    </div>
                    @endif

                    <input type="file" name="favicon_image" accept=".ico,image/svg+xml,image/png"
                           class="w-full text-xs text-[#8C847A] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#201C18] file:text-white hover:file:bg-[#2A241F] file:cursor-pointer cursor-pointer">
                </div>
            </div>
        </div>

        <!-- TAB 2: SEO Y METADATOS -->
        <div x-show="tab === 'seo'" x-cloak class="space-y-6">
            <!-- Google Search Preview Card -->
            <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-5 shadow-xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#241E18]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#736B63]">Previsualización en Resultados de Google</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-success/20 text-success font-mono font-bold">SEO PREVIEW</span>
                </div>

                <div class="p-4 rounded-xl bg-[#1A1612] border border-[#262019] space-y-1 max-w-2xl">
                    <div class="flex items-center gap-1.5 text-xs text-[#8C847A]">
                        <span>{{ config('app.url') }}</span>
                        <span>›</span>
                        <span>home</span>
                    </div>
                    <h4 class="text-base font-bold text-[#8AB4F8] hover:underline cursor-pointer truncate"
                        x-text="seoTitle || '{{ $settings['seo_meta_title'] ?? config('app.name') }}'">
                    </h4>
                    <p class="text-xs text-[#BDC1C6] leading-relaxed line-clamp-2"
                       x-text="seoDesc || '{{ $settings['seo_meta_description'] ?? 'Descubre y descarga las mejores aplicaciones y utilidades para macOS.' }}'">
                    </p>
                </div>
            </div>

            <!-- SEO Input Fields -->
            <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-5">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold text-[#A8A199] uppercase tracking-wider">Título SEO Principal (&lt;title&gt;)</label>
                        <span class="text-[11px] font-mono text-[#736B63]" x-text="(seoTitle ? seoTitle.length : 0) + '/60 caracteres'"></span>
                    </div>
                    <input type="text" name="seo_meta_title" x-model="seoTitle" value="{{ $settings['seo_meta_title'] ?? '' }}"
                           class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                           placeholder="HackMac.cc - Descarga Aplicaciones y Juegos para macOS">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold text-[#A8A199] uppercase tracking-wider">Meta Descripción (&lt;meta name=&quot;description&quot;&gt;)</label>
                        <span class="text-[11px] font-mono text-[#736B63]" x-text="(seoDesc ? seoDesc.length : 0) + '/160 caracteres'"></span>
                    </div>
                    <textarea name="seo_meta_description" x-model="seoDesc" rows="3"
                              class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl p-3 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors resize-none"
                              placeholder="Descubre y descarga las mejores aplicaciones y utilidades para macOS. Rápido, seguro y verificado.">{{ $settings['seo_meta_description'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Palabras Clave (Keywords separadas por coma)</label>
                    <input type="text" name="seo_keywords" value="{{ $settings['seo_keywords'] ?? '' }}"
                           class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                           placeholder="mac apps, macos, apple silicon, cleanmymac, utilidades mac, software mac">
                </div>

                <div class="pt-4 border-t border-[#241E18]">
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Imagen OpenGraph para Redes Sociales (1200x630)</label>
                    @if(!empty($settings['seo_og_image']))
                    <div class="mb-3 p-3 rounded-xl bg-[#12100E] border border-[#2B241C] flex items-center justify-between">
                        <img src="{{ $settings['seo_og_image'] }}" alt="OG Preview" class="h-14 rounded object-cover">
                        <label class="flex items-center gap-2 text-xs text-danger cursor-pointer">
                            <input type="checkbox" name="remove_seo_og_image" value="1" class="rounded text-danger">
                            <span>Quitar imagen</span>
                        </label>
                    </div>
                    @endif
                    <input type="file" name="seo_og_image" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-xs text-[#8C847A] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#201C18] file:text-white hover:file:bg-[#2A241F] file:cursor-pointer cursor-pointer">
                </div>
            </div>
        </div>

        <!-- TAB 3: INFORMACIÓN GENERAL Y TEXTOS -->
        <div x-show="tab === 'general'" x-cloak class="space-y-6">
            <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 shadow-xl space-y-5">
                <div>
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Subtítulo / Hero Tagline</label>
                    <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}"
                           class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                           placeholder="Discover the best free Mac apps & games">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Descripción General / Acerca de la Plataforma</label>
                    <textarea name="site_description" rows="3"
                              class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl p-3 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors resize-none"
                              placeholder="Direct downloads of verified, secure, and clean macOS applications, utilities, creative software, and productivity tools.">{{ $settings['site_description'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Texto de Pie de Página (Copyright / Aviso Legal)</label>
                    <input type="text" name="footer_text" value="{{ $settings['footer_text'] ?? '' }}"
                           class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                           placeholder="© 2026 HackMac.cc. All rights reserved. Clean, verified and direct macOS downloads.">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-3">
                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Email de Contacto</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                               placeholder="contact@hackmac.cc">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Canal de Telegram</label>
                        <input type="text" name="telegram_channel" value="{{ $settings['telegram_channel'] ?? '' }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                               placeholder="https://t.me/hackmac">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">X / Twitter</label>
                        <input type="text" name="twitter_url" value="{{ $settings['twitter_url'] ?? '' }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors"
                               placeholder="https://twitter.com/hackmac">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: IA GEMINI 2.5 -->
        <div x-show="tab === 'gemini'" class="space-y-6">
            <!-- Header Explanatory Card -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#1C1712] via-[#14110E] to-[#120F0D] border border-primary/30 rounded-2xl p-6 shadow-2xl">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary to-[#EA580C] p-0.5 shadow-lg shadow-primary/20 shrink-0">
                            <div class="w-full h-full bg-[#120F0D] rounded-[14px] flex items-center justify-center text-primary">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-white tracking-tight">Motor de Redacción Google Gemini 2.5</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-primary/20 text-primary border border-primary/30 uppercase">IA Editorial</span>
                            </div>
                            <p class="text-xs text-[#A8A199] max-w-2xl leading-relaxed">
                                Redacta automáticamente descripciones concisas en <strong class="text-white">inglés nativo</strong> de <strong class="text-white">~4 renglones</strong> (50-65 palabras) con <strong class="text-white">enlaces SEO internos a su categoría</strong> y genera <strong class="text-white">7 características clave</strong> formateadas para los bullets brillantes naranjas.
                            </p>
                        </div>
                    </div>

                    <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer"
                       class="px-4 py-2 rounded-xl bg-[#221C16] hover:bg-[#2A231C] border border-[#3A3025] hover:border-primary/50 text-[#C4BDB5] hover:text-white text-xs font-semibold flex items-center gap-2 transition-all shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Obtener API Key Gratis</span>
                    </a>
                </div>
            </div>

            <!-- Configuration Form Fields -->
            <div class="bg-[#14110E] border border-[#2B241C] rounded-2xl p-6 space-y-6 shadow-xl">
                <!-- API Key Input with Eye Toggle & Live Test -->
                <div>
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Google Gemini API Key</span>
                        <span class="text-[11px] text-[#736B63] normal-case">Guardada de forma segura en la base de datos</span>
                    </label>
                    <div class="relative flex items-center">
                        <input :type="showApiKey ? 'text' : 'password'"
                               name="gemini_api_key"
                               x-model="geminiApiKey"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl pl-4 pr-24 py-3 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors font-mono"
                               placeholder="AIzaSy...">
                        
                        <div class="absolute right-2 flex items-center gap-1.5">
                            <button type="button" @click="showApiKey = !showApiKey"
                                    class="p-1.5 rounded-lg text-[#8C847A] hover:text-white hover:bg-[#1E1914] transition-colors"
                                    title="Mostrar/Ocultar clave">
                                <svg x-show="!showApiKey" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showApiKey" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                            
                            <button type="button" @click="testGeminiConnection()"
                                    :disabled="testingGemini"
                                    class="px-3 py-1.5 rounded-lg bg-primary/20 hover:bg-primary/30 border border-primary/40 text-primary text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                <svg x-show="testingGemini" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="testingGemini ? 'Probando...' : 'Probar'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Live Connection Test Feedback -->
                    <div x-show="testGeminiResult" x-cloak class="mt-3">
                        <div :class="testGeminiResult?.success ? 'bg-success/15 border-success/30 text-success' : 'bg-danger/15 border-danger/30 text-danger'"
                             class="p-3 rounded-xl border text-xs flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full shrink-0" :class="testGeminiResult?.success ? 'bg-success' : 'bg-danger'"></span>
                            <span class="font-medium" x-text="testGeminiResult?.message"></span>
                        </div>
                    </div>
                </div>

                <!-- Model Selection & Automation Toggle -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Modelo de Gemini</label>
                        <select name="gemini_model" x-model="geminiModel"
                                class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-3 text-sm text-white focus:outline-none transition-colors">
                            <option value="gemini-2.5-flash">gemini-2.5-flash (Recomendado - Ultrarrápido & Económico)</option>
                            <option value="gemini-2.5-pro">gemini-2.5-pro (Máximo razonamiento & Calidad editorial)</option>
                            <option value="gemini-2.0-flash">gemini-2.0-flash (Alternativa estable)</option>
                        </select>
                        <p class="text-[11px] text-[#736B63] mt-1.5">
                            <code>gemini-2.5-flash</code> es el modelo recomendado por Google para redacción de contenido a gran escala con latencia mínima.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Generación Automática en Scraper</label>
                        <div class="flex items-center justify-between p-3.5 bg-[#12100E] border border-[#2B241C] rounded-xl">
                            <div>
                                <span class="text-sm font-semibold text-white block">Auto-generar al importar</span>
                                <span class="text-[11px] text-[#736B63]">Aplica IA a cada app importada en el catálogo</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="gemini_auto_generate" value="0">
                                <input type="checkbox" name="gemini_auto_generate" value="1"
                                       {{ ($settings['gemini_auto_generate'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-[#262019] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Word Count & Features Controls -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2 border-t border-[#241E18]">
                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>Longitud Objetivo de Descripción</span>
                            <span class="text-primary font-mono font-bold">~4 renglones (50-65 palabras)</span>
                        </label>
                        <input type="number" name="gemini_target_words" min="30" max="300"
                               value="{{ $settings['gemini_target_words'] ?? '60' }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors">
                        <p class="text-[11px] text-[#736B63] mt-1.5">
                            Genera estrictamente 1 párrafo conciso de 3 a 4 renglones con hipervínculos SEO internos automáticos hacia su categoría.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>Cantidad de Características (Features)</span>
                            <span class="text-primary font-mono font-bold">7 viñetas</span>
                        </label>
                        <input type="number" name="gemini_features_count" min="3" max="15"
                               value="{{ $settings['gemini_features_count'] ?? '7' }}"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-primary rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors">
                        <p class="text-[11px] text-[#736B63] mt-1.5">
                            Genera exactamente 7 viñetas con formato <code>&lt;li&gt;&lt;strong&gt;Título:&lt;/strong&gt; Descripción&lt;/li&gt;</code> para lucir con los bullets brillantes.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 5: CLOUDFLARE R2 & ALMACENAMIENTO -->
        <div x-show="tab === 'storage'" x-cloak class="space-y-6">
            <div class="bg-[#14110E] border border-[#2B241C] p-6 rounded-2xl shadow-xl space-y-6">
                <!-- Tab Title & Description -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#241E18]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#F38020]/15 border border-[#F38020]/30 flex items-center justify-center text-[#F38020]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white flex items-center gap-2">
                                <span>Cloudflare R2 Storage (Torrents & Media)</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#F38020]/20 text-[#F38020] border border-[#F38020]/30">Zero Egress Fees</span>
                            </h2>
                            <p class="text-xs text-[#8C847A]">Configura tu Bucket R2 para almacenar archivos .torrent e imágenes en la nube de alta velocidad con 10 GB gratis.</p>
                        </div>
                    </div>

                    <!-- Test Connection Button -->
                    <button type="button" @click="testR2Connection()" :disabled="testingR2"
                            class="px-4 py-2 rounded-xl bg-[#F38020]/15 hover:bg-[#F38020]/25 text-[#F38020] border border-[#F38020]/40 text-xs font-bold transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50">
                        <template x-if="!testingR2">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Probar Conexión R2
                            </span>
                        </template>
                        <template x-if="testingR2">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Probando bucket...
                            </span>
                        </template>
                    </button>
                </div>

                <!-- Test Result Banner -->
                <div x-show="testR2Result" x-cloak
                     :class="testR2Result?.success ? 'bg-success/10 border-success/30 text-success' : 'bg-danger/10 border-danger/30 text-danger'"
                     class="p-4 rounded-xl border text-xs flex items-start gap-2.5 transition-all">
                    <template x-if="testR2Result?.success">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="!testR2Result?.success">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <div class="flex-1">
                        <span class="font-bold block" x-text="testR2Result?.success ? '¡Conexión Exitosa con Cloudflare R2!' : 'Error de Conexión'"></span>
                        <span class="text-[11px] opacity-90" x-text="testR2Result?.message"></span>
                    </div>
                </div>

                <!-- Storage Mode Selector (Local vs R2) -->
                <div>
                    <label class="block text-xs font-bold text-[#A8A199] uppercase tracking-wider mb-2">Destino de Almacenamiento para Scraper TorrentMac</label>
                    <input type="hidden" name="torrentmac_storage_disk" :value="torrentDisk">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button" @click="torrentDisk = 'local'"
                                :class="torrentDisk === 'local' ? 'border-primary bg-primary/10 text-white shadow-lg shadow-primary/10' : 'border-[#262019] bg-[#12100E] text-[#8C847A] hover:border-[#383025] hover:text-white'"
                                class="p-4 rounded-xl border text-left transition-all flex items-start gap-3 cursor-pointer">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="torrentDisk === 'local' ? 'bg-primary text-white' : 'bg-white/5 text-[#8C847A]'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-xs flex items-center gap-1.5">
                                    <span>📁 Almacenamiento Local</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-white/10 text-white/80">storage/app/public</span>
                                </div>
                                <p class="text-[11px] text-[#736B63] mt-1">Guarda los archivos directamente en tu servidor local. Ideal para pruebas rápidas sin configurar cuentas externas.</p>
                            </div>
                        </button>

                        <button type="button" @click="torrentDisk = 'r2'"
                                :class="torrentDisk === 'r2' ? 'border-[#F38020] bg-[#F38020]/10 text-white shadow-lg shadow-[#F38020]/10' : 'border-[#262019] bg-[#12100E] text-[#8C847A] hover:border-[#383025] hover:text-white'"
                                class="p-4 rounded-xl border text-left transition-all flex items-start gap-3 cursor-pointer">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="torrentDisk === 'r2' ? 'bg-[#F38020] text-white' : 'bg-white/5 text-[#8C847A]'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-xs flex items-center gap-1.5">
                                    <span>☁️ Cloudflare R2 (Recomendado)</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-[#F38020]/20 text-[#F38020]">Producción</span>
                                </div>
                                <p class="text-[11px] text-[#736B63] mt-1">Sube los .torrent a la nube global de Cloudflare. Cero consumo de ancho de banda en tu servidor principal.</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Credential Inputs Grid -->
                <div class="space-y-4 pt-4 border-t border-[#241E18]">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#F38020]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Credenciales de la API R2
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Account ID -->
                        <div>
                            <label class="block text-xs font-bold text-[#A8A199] mb-1.5">
                                Cloudflare Account ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="r2_account_id" x-model="r2AccountId"
                                   placeholder="Ej: a1b2c3d4e5f67890abcdef1234567890"
                                   class="w-full bg-[#12100E] border border-[#2B241C] focus:border-[#F38020] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors font-mono">
                            <p class="text-[11px] text-[#736B63] mt-1">Lo encuentras en tu panel de Cloudflare > R2 > 'Account ID' (columna derecha).</p>
                        </div>

                        <!-- Bucket Name -->
                        <div>
                            <label class="block text-xs font-bold text-[#A8A199] mb-1.5">
                                Nombre del Bucket <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="r2_bucket" x-model="r2Bucket"
                                   placeholder="Ej: descargasweb-torrents"
                                   class="w-full bg-[#12100E] border border-[#2B241C] focus:border-[#F38020] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors font-mono">
                            <p class="text-[11px] text-[#736B63] mt-1">El nombre exacto de tu bucket creado en Cloudflare R2.</p>
                        </div>

                        <!-- Access Key ID -->
                        <div>
                            <label class="block text-xs font-bold text-[#A8A199] mb-1.5">
                                Access Key ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="r2_access_key_id" x-model="r2AccessKeyId"
                                   placeholder="Ej: 8c6a01b7a2d48c3f..."
                                   class="w-full bg-[#12100E] border border-[#2B241C] focus:border-[#F38020] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors font-mono">
                            <p class="text-[11px] text-[#736B63] mt-1">Generado en Cloudflare > R2 > 'Manage R2 API Tokens'.</p>
                        </div>

                        <!-- Secret Access Key -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-[#A8A199]">
                                    Secret Access Key <span class="text-danger">*</span>
                                </label>
                                <button type="button" @click="showR2Secret = !showR2Secret"
                                        class="text-[11px] text-[#F38020] hover:underline cursor-pointer">
                                    <span x-text="showR2Secret ? 'Ocultar' : 'Mostrar'"></span>
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showR2Secret ? 'text' : 'password'"
                                       name="r2_secret_access_key" x-model="r2SecretAccessKey"
                                       placeholder="••••••••••••••••••••••••••••••••"
                                       class="w-full bg-[#12100E] border border-[#2B241C] focus:border-[#F38020] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors font-mono">
                            </div>
                            <p class="text-[11px] text-[#736B63] mt-1">El token secreto HMAC para firmar las peticiones S3 a R2.</p>
                        </div>
                    </div>

                    <!-- Public Custom URL / Dev URL -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-[#A8A199] mb-1.5">
                            Dominio Público / URL de Entrega de R2 (Opcional)
                        </label>
                        <input type="text" name="r2_url" x-model="r2Url"
                               placeholder="Ej: https://pub-xxxxxxxxxxxxxxxxxxxxxxxx.r2.dev o https://torrents.tudominio.com"
                               class="w-full bg-[#12100E] border border-[#2B241C] focus:border-[#F38020] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#6E675E] focus:outline-none transition-colors font-mono">
                        <p class="text-[11px] text-[#736B63] mt-1">
                            URL pública para descargar directamente los archivos desde Cloudflare CDN. Puedes habilitar 'Public Development URL' en Cloudflare R2 o conectar tu propio dominio.
                        </p>
                    </div>
                </div>

                <!-- Mini Guide Card -->
                <div class="p-4 bg-[#110E0C] border border-[#2B221B] rounded-xl space-y-2.5">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#F38020]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        ¿Cómo obtener tus credenciales R2 en Cloudflare en 3 minutos?
                    </span>
                    <ol class="text-[11px] text-[#8C847A] space-y-1 list-decimal list-inside leading-relaxed">
                        <li>Inicia sesión en <a href="https://dash.cloudflare.com/" target="_blank" class="text-[#F38020] underline">dash.cloudflare.com</a> y haz clic en <strong>R2</strong> en el menú lateral.</li>
                        <li>Crea un Bucket (por ejemplo: <code>descargasweb-torrents</code>) con ubicación automática o Norteamérica / Europa.</li>
                        <li>Haz clic en <strong>Manage R2 API Tokens</strong> &gt; <strong>Create API Token</strong> con permisos <strong>Object Read & Write</strong>. Copia tu <em>Access Key ID</em>, <em>Secret Access Key</em> y el <em>Account ID</em> de tu cuenta.</li>
                        <li>¡Listo! Pégalos aquí arriba y pulsa <strong>Probar Conexión R2</strong>.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Sticky Save Bar -->
        <div class="p-4 bg-[#14110E] border border-[#2B241C] rounded-2xl flex items-center justify-between shadow-xl">
            <span class="text-xs text-[#8C847A] flex items-center gap-1.5">
                <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Los cambios se reflejarán instantáneamente en todo el portal web.
            </span>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary to-[#EA580C] hover:from-primary-hover hover:to-[#F97316] text-white text-xs font-bold shadow-lg shadow-primary/25 transition-all flex items-center gap-2 cursor-pointer">
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>
@endsection
