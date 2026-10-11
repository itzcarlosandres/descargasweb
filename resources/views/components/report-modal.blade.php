@props(['application'])

<div x-data="{
        reportOpen: false,
        reported: false,
        sending: false,
        errorMessage: null,
        reportType: '{{ $application->has_torrent ? 'torrent' : 'ddl' }}',
        notes: '',
        dropdownOpen: false,
        reportOptions: [
            { id: 'torrent', label: 'Torrent o Magnet sin semillas / no descarga', icon: '🧲' },
            { id: 'ddl', label: 'Enlace Directo (DDL) caído / Error 404', icon: '❌' },
            { id: 'mirror', label: 'Servidor alternativo / Mirror caído', icon: '🔄' },
            { id: 'other', label: 'Archivo dañado / Contraseña errónea', icon: '📁' }
        ],
        currentOption() {
            return this.reportOptions.find(o => o.id === this.reportType) || this.reportOptions[0];
        }
     }"
     @open-report-modal.window="
        reportOpen = true; 
        reported = false; 
        errorMessage = null;
        dropdownOpen = false;
        if ($event.detail && $event.detail.type) {
            reportType = $event.detail.type;
        }
     "
     @keydown.escape.window="if (reportOpen) { reportOpen = false; dropdownOpen = false; }"
     x-cloak>

    <!-- Modal Backdrop -->
    <div x-show="reportOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         @click.self="reportOpen = false; dropdownOpen = false">

        <!-- Modal Dialog (Apple macOS Dark Glassmorphism) -->
        <div x-show="reportOpen"
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="w-full max-w-md rounded-2xl bg-[#18181B] border border-[#333336] p-6 shadow-[0_25px_60px_rgba(0,0,0,0.8)] relative"
             @click.stop>

            <!-- Close Button (Top-Right, Clean and Separate) -->
            <button type="button" 
                    @click="reportOpen = false"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-[#242426] hover:bg-[#2C2C2F] text-[#A1A1A6] hover:text-white flex items-center justify-center transition-colors cursor-pointer border border-[#333336]/60 z-10"
                    aria-label="Cerrar modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Modal Header -->
            <div class="flex items-center gap-3.5 mb-5 pr-8">
                <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl flex-shrink-0 shadow-sm">
                    ⚠️
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-heading font-bold text-white text-base leading-tight">Reportar Enlace Caído</h3>
                    <p class="text-xs text-[#A1A1A6] truncate mt-0.5">{{ $application->name }} &middot; v{{ $application->version }}</p>
                </div>
            </div>

            <!-- Form State -->
            <template x-if="!reported">
                <form @submit.prevent="
                    sending = true;
                    errorMessage = null;
                    fetch('{{ route('app.report', $application->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            type: reportType,
                            notes: notes
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        sending = false;
                        if (data.success) {
                            reported = true;
                            setTimeout(() => { reportOpen = false; reported = false; notes = ''; }, 3000);
                        } else {
                            errorMessage = data.message || 'Error al enviar el reporte.';
                        }
                    })
                    .catch(() => {
                        sending = false;
                        errorMessage = 'Error de conexión con el servidor. Intenta de nuevo.';
                    });
                " class="space-y-4">

                    <!-- Error Alert -->
                    <template x-if="errorMessage">
                        <div class="p-3 rounded-xl bg-danger/15 border border-danger/30 text-danger text-xs font-medium" x-text="errorMessage"></div>
                    </template>

                    <!-- Problem Selector (Custom High-Contrast Dropdown) -->
                    <div class="relative" @click.away="dropdownOpen = false">
                        <label class="block text-[11px] font-bold text-[#A1A1A6] uppercase tracking-wider mb-1.5">
                            ¿Qué problema encontraste?
                        </label>
                        
                        <!-- Trigger Button -->
                        <button type="button"
                                @click="dropdownOpen = !dropdownOpen"
                                class="w-full bg-[#121214] hover:bg-[#1A1A1D] border border-[#333336] focus:border-[#0071E3] rounded-xl px-3.5 py-2.5 text-xs text-white flex items-center justify-between transition-all cursor-pointer shadow-inner">
                            <div class="flex items-center gap-2.5 truncate">
                                <span class="text-sm flex-shrink-0" x-text="currentOption().icon"></span>
                                <span class="truncate font-medium text-white text-xs" x-text="currentOption().label"></span>
                            </div>
                            <svg class="w-4 h-4 text-[#A1A1A6] transition-transform duration-200 flex-shrink-0 ml-2" 
                                 :class="dropdownOpen ? 'rotate-180 text-[#0071E3]' : ''" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Custom Dropdown Menu -->
                        <div x-show="dropdownOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                             class="absolute z-30 left-0 right-0 mt-1.5 bg-[#1E1E20] border border-[#333336] rounded-xl p-1.5 shadow-[0_16px_36px_rgba(0,0,0,0.85)] space-y-1"
                             style="display: none;">
                            <template x-for="opt in reportOptions" :key="opt.id">
                                <button type="button"
                                        @click="reportType = opt.id; dropdownOpen = false"
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition-colors cursor-pointer text-left"
                                        :class="reportType === opt.id ? 'bg-[#0071E3] text-white font-semibold' : 'text-[#E5E5EA] hover:bg-[#2C2C2F] hover:text-white'">
                                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                        <span class="text-sm flex-shrink-0" x-text="opt.icon"></span>
                                        <span class="truncate" x-text="opt.label"></span>
                                    </div>
                                    <template x-if="reportType === opt.id">
                                        <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Notes Textarea -->
                    <div>
                        <label class="block text-[11px] font-bold text-[#A1A1A6] uppercase tracking-wider mb-1.5">
                            Detalles u Observaciones <span class="text-[#6E6E73] font-normal lowercase">(opcional)</span>
                        </label>
                        <textarea x-model="notes"
                                  rows="3"
                                  placeholder="Ej: El enlace da error 404, o Mega pide clave de cifrado no especificada..."
                                  class="w-full bg-[#121214] border border-[#333336] focus:border-[#0071E3] rounded-xl p-3 text-xs text-white placeholder-[#6E6E73] focus:outline-none transition-colors resize-none"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="reportOpen = false"
                                class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#A1A1A6] hover:text-white hover:bg-[#242426] transition-colors cursor-pointer border border-transparent">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="sending"
                                class="px-5 py-2.5 rounded-xl bg-[#0071E3] hover:bg-[#0077ED] active:bg-[#005FBF] text-white font-bold text-xs shadow-lg shadow-[#0071E3]/25 flex items-center gap-2 transition-all cursor-pointer disabled:opacity-50">
                            <svg x-show="sending" class="w-3.5 h-3.5 animate-spin text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="sending ? 'Enviando Reporte...' : 'Enviar Reporte'"></span>
                        </button>
                    </div>
                </form>
            </template>

            <!-- Success State -->
            <template x-if="reported">
                <div class="py-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-success/20 border border-success/40 text-success text-2xl flex items-center justify-center mx-auto shadow-sm">
                        ✓
                    </div>
                    <h4 class="font-bold text-white text-base">¡Reporte Enviado con Éxito!</h4>
                    <p class="text-xs text-[#A1A1A6] leading-relaxed max-w-xs mx-auto">
                        Hemos notificado al equipo técnico. Revisaremos el enlace y lo actualizaremos a la mayor brevedad.
                    </p>
                </div>
            </template>
        </div>
    </div>
</div>
