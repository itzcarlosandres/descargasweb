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
       class="pointer-events-auto relative w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-gradient-to-tr from-[#0088CC] via-[#1DA1F2] to-[#229ED9] text-white shadow-[0_8px_25px_rgba(0,136,204,0.45)] hover:shadow-[0_12px_32px_rgba(0,136,204,0.65)] hover:scale-108 active:scale-95 transition-all duration-300 flex items-center justify-center cursor-pointer group focus:outline-none">

        <!-- Pulse Ring Animation -->
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0088CC] opacity-35 pointer-events-none [animation-duration:2.5s]"></span>

        <!-- Telegram Icon -->
        <svg class="w-6 h-6 sm:w-7 sm:h-7 fill-current transform -translate-x-0.5 translate-y-0.5 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300" viewBox="0 0 24 24">
            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.458c.536-.195 1.006.128.832.969z"/>
        </svg>
    </a>
</div>
@endif
