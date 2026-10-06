@props(['app', 'position' => null])

<a href="{{ route('app', $app->slug) }}" class="card card-hover p-4 sm:p-5 flex flex-col items-center text-center relative bg-white dark:bg-[#1d1d1f] border border-[#E5E7EB] dark:border-[#ffffff]/10 rounded-[18px] group overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[rgba(0,_0,_0,_0.6)_0px_12px_40px_0px] hover:shadow-md hover:border-[#CBD5E1] dark:hover:border-[#ffffff]/25 transition-all">
    @if($position)
    <span class="absolute top-2.5 left-2.5 w-5 h-5 sm:w-6 sm:h-6 rounded-[5px] bg-[#0071E3] text-white text-[10px] sm:text-xs font-bold flex items-center justify-center shadow-sm">
        {{ $position }}
    </span>
    @endif

    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#F5F5F7] dark:bg-[#161617] border border-[#E5E7EB] dark:border-[#333336] flex items-center justify-center p-1.5 shadow-sm mb-2.5 overflow-hidden group-hover:scale-105 transition-transform">
        <img src="{{ $app->icon_url }}" alt="{{ $app->name }}" class="w-full h-full object-cover rounded-xl" loading="lazy"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="w-full h-full bg-[#0071E3]/20 rounded-xl items-center justify-center text-[#0071E3] font-bold text-2xl" style="display: none;">
            {{ strtoupper(substr($app->name, 0, 1)) }}
        </div>
    </div>

    <h3 class="font-heading text-[#1D1D1F] dark:text-white font-bold text-sm sm:text-[15px] truncate w-full group-hover:text-[#0071E3] transition-colors mb-2">
        {{ $app->name }}
    </h3>

    <div class="flex items-center gap-1.5 mt-auto">
        <span class="border border-[#0071E3]/30 text-[#4D9BE9] bg-[#0071E3]/10 text-[9px] uppercase tracking-wider font-semibold px-2 py-0.5 rounded-md">
            {{ $app->category?->name ?? 'SYSTEM' }}
        </span>
        <span class="text-[#6B7280] dark:text-[#A1A1A6] text-[10px] font-medium flex items-center gap-0.5 ml-1">
            <svg class="w-2.5 h-2.5 text-[#6B7280] dark:text-[#A1A1A6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ $app->formatted_downloads }}
        </span>
    </div>
</a>
