@if(setting('topbar_enabled', false) && !empty(setting('topbar_text')))
@php
    $style = setting('topbar_style', 'vip');
    $closable = (bool) setting('topbar_closable', true);
    $text = setting('topbar_text');
    $badge = setting('topbar_badge');
    $buttonText = setting('topbar_button_text');
    $buttonUrl = setting('topbar_button_url');
    $openNewTab = (bool) setting('topbar_open_new_tab', true);
    $hash = substr(md5($text . $buttonUrl), 0, 8);
@endphp
<aside x-data="{
        show: !localStorage.getItem('hide_topbar_{{ $hash }}'),
        dismiss() {
            this.show = false;
            try { localStorage.setItem('hide_topbar_{{ $hash }}', '1'); } catch(e){}
        }
    }"
    x-show="show"
    x-cloak
    class="relative z-50 text-xs py-2 px-3 sm:px-6 transition-all duration-300 {{ 
        $style === 'vip' 
            ? 'bg-gradient-to-r from-[#D97706] via-[#F59E0B] to-[#EA580C] text-white shadow-sm' 
            : ($style === 'sky' 
                ? 'bg-gradient-to-r from-sky-600 via-blue-600 to-indigo-600 text-white shadow-sm' 
                : ($style === 'emerald' 
                    ? 'bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-600 text-white shadow-sm' 
                    : 'bg-[#181410] border-b border-[#2E251C] text-[#E0D8CE] shadow-sm')) 
    }}">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
        <!-- Content -->
        <div class="flex-1 flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-center sm:text-left">
            @if(!empty($badge))
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold tracking-wider uppercase {{
                    $style === 'dark' 
                        ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' 
                        : 'bg-black/25 text-white backdrop-blur-sm border border-white/20'
                }}">
                    {{ $badge }}
                </span>
            @endif

            <span class="font-medium text-xs sm:text-[13px] leading-tight">
                {{ $text }}
            </span>

            @if(!empty($buttonText) && !empty($buttonUrl))
                <a href="{{ $buttonUrl }}" 
                   target="{{ $openNewTab ? '_blank' : '_self' }}"
                   rel="{{ $openNewTab ? 'noopener noreferrer' : '' }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm transform hover:scale-[1.03] active:scale-[0.98] {{
                       $style === 'dark'
                           ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black'
                           : 'bg-white hover:bg-white/95 text-[#181410]'
                   }}">
                    <span>{{ $buttonText }}</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            @endif
        </div>

        <!-- Dismiss button -->
        @if($closable)
            <button type="button" @click="dismiss()" 
                    title="Cerrar aviso"
                    class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-black/20 transition-colors flex-shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</aside>
@endif
