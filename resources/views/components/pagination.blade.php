@if($paginator->hasPages())
<nav class="flex items-center justify-center gap-1.5 mt-10">
    @if(!$paginator->onFirstPage())
        <a href="{{ $paginator->previousPageUrl() }}" class="w-8 h-8 rounded-lg bg-white dark:bg-[#242426] hover:bg-[#F5F5F7] dark:hover:bg-[#2C2C2F] text-[#1D1D1F] dark:text-[#A1A1A6] hover:text-[#0071E3] dark:hover:text-white border border-[#E5E7EB] dark:border-[#333336] shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-colors flex items-center justify-center text-xs font-bold">
            &larr;
        </a>
    @endif

    @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
        @if($page == $paginator->currentPage())
            <span class="w-8 h-8 rounded-lg bg-[#0071E3] text-white text-xs font-bold flex items-center justify-center shadow-sm">
                {{ $page }}
            </span>
        @elseif($page == 1 || $page == $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 2)
            <a href="{{ $url }}" class="w-8 h-8 rounded-lg bg-white dark:bg-[#242426] hover:bg-[#F5F5F7] dark:hover:bg-[#2C2C2F] text-[#1D1D1F] dark:text-[#A1A1A6] hover:text-[#0071E3] dark:hover:text-white border border-[#E5E7EB] dark:border-[#333336] shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-colors flex items-center justify-center text-xs font-medium">
                {{ $page }}
            </a>
        @elseif(abs($page - $paginator->currentPage()) == 3)
            <span class="px-1 text-[#9CA3AF] dark:text-[#64748B] text-xs">...</span>
        @endif
    @endforeach

    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="w-8 h-8 rounded-lg bg-white dark:bg-[#242426] hover:bg-[#F5F5F7] dark:hover:bg-[#2C2C2F] text-[#1D1D1F] dark:text-[#A1A1A6] hover:text-[#0071E3] dark:hover:text-white border border-[#E5E7EB] dark:border-[#333336] shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-colors flex items-center justify-center text-xs font-bold">
            &rarr;
        </a>
    @endif
</nav>
@endif
