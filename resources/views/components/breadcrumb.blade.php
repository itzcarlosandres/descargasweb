@props(['items' => []])

<nav class="flex items-center gap-2 text-sm text-text-muted mb-6">
    <a href="{{ route('home') }}" class="hover:text-text-secondary transition-colors">Home</a>
    @foreach($items as $item)
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        @if(isset($item['url']))
            <a href="{{ $item['url'] }}" class="hover:text-text-secondary transition-colors">{{ $item['label'] }}</a>
        @else
            <span class="text-text-secondary">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
