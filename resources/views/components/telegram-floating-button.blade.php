@php
    $floatingEnabled = (bool) setting('telegram_floating_button_enabled', true);
    $channelUrl = setting('telegram_channel_url', setting('telegram_channel', 'https://t.me/hackmac'));
    $tooltipText = setting('telegram_button_tooltip', '¿Problemas con la descarga o buscas otra app? Únete a nuestro Telegram');
@endphp

@if($floatingEnabled && !empty($channelUrl))
<div x-data="{ openTooltip: true, dismissed: false }"
     x-init="setTimeout(() => { if (!dismissed) openTooltip = true; }, 1500)"
     class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-2.5 pointer-events-none">

    <!-- Interactive Speech Bubble / Tooltip -->
    <div x-show="openTooltip && !dismissed"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         class="pointer-events-auto bg-white/95 dark:bg-[#161924]/95 backdrop-blur-xl border border-[#E5E7EB] dark:border-[#282F42] shadow-[0_12px_36px_rgba(0,0,0,0.18)] dark:shadow-[0_16px_40px_rgba(0,0,0,0.6)] rounded-2xl p-3.5 max-w-[270px] text-left relative group">

        <!-- Close Tooltip Button -->
        <button type="button"
                @click="dismissed = true; openTooltip = false"
                class="absolute -top-2 -left-2 w-5 h-5 rounded-full bg-[#F5F5F7] dark:bg-[#202534] border border-[#E5E7EB] dark:border-[#2C3347] text-[#6B7280] dark:text-[#A1A1A6] hover:text-black dark:hover:text-white flex items-center justify-center text-xs shadow-sm cursor-pointer transition-colors"
                title="Cerrar aviso">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <a href="{{ $channelUrl }}" target="_blank" rel="noopener noreferrer" class="block">
            <div class="flex items-center gap-1.5 mb-1">
                <span class="w-2 h-2 rounded-full bg-[#0088CC] animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#0088CC]">Canal Oficial</span>
            </div>
            <p class="text-xs text-[#1D1D1F] dark:text-[#E4E4E7] font-medium leading-relaxed">
                {{ $tooltipText }}
            </p>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0088CC] mt-2 group-hover:underline">
                <span>Unirme al canal</span>
                <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </a>

        <!-- Arrow pointing to button -->
        <div class="absolute -bottom-1.5 right-6 w-3 h-3 bg-white dark:bg-[#161924] border-r border-b border-[#E5E7EB] dark:border-[#282F42] rotate-45"></div>
    </div>

    <!-- Floating Telegram Circle Button -->
    <a href="{{ $channelUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       @mouseenter="if (!dismissed) openTooltip = true"
       aria-label="Únete a nuestro canal de Telegram"
       class="pointer-events-auto relative w-14 h-14 rounded-full bg-gradient-to-tr from-[#0088CC] via-[#1DA1F2] to-[#229ED9] shadow-[0_8px_25px_rgba(0,136,204,0.45)] hover:shadow-[0_12px_32px_rgba(0,136,204,0.65)] hover:scale-108 active:scale-95 transition-all duration-300 flex items-center justify-center cursor-pointer group focus:outline-none">

        <!-- Pulse Ring Animation -->
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0088CC] opacity-35 pointer-events-none [animation-duration:2.5s]"></span>

        <!-- White Inner Disc (Concentric & Perfectly Centered) -->
        <div class="relative w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform duration-300">
            <!-- Telegram Paper Plane (Optically Balanced) -->
            <svg class="w-4.5 h-4.5 text-[#0088CC] fill-current -ml-0.5 mt-0.5 group-hover:-rotate-6 transition-transform duration-300" viewBox="0 0 24 24">
                <path d="M18.384 5.221 3.518 10.963c-1.016.408-.999 1.189.173 1.545l3.817 1.191 1.446 4.436c.16.494.38.68.79.68.324 0 .54-.15.75-.36l2.115-2.057 4.394 3.246c.808.445 1.39.215 1.591-.749l2.883-13.582c.294-1.182-.453-1.716-1.094-1.347z"/>
            </svg>
        </div>
    </a>
</div>
@endif
