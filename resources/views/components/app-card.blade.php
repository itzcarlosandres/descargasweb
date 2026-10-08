@props(['app'])

<article id="post-{{ $app->id }}" class="post-card post-{{ $app->id }} post type-post status-publish format-standard has-post-thumbnail hentry category-application category-{{ $app->category?->slug ?? 'system-utilities' }} group">
  <a href="{{ route('app', $app->slug) }}" aria-hidden="true" tabindex="-1" class="shrink-0 flex items-center justify-center relative">
    <img width="168" height="168" src="{{ $app->icon_url }}" class="card-icon wp-post-image" alt="{{ $app->name }} Logo" loading="lazy" decoding="async"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    <div class="card-icon bg-[#0071E3]/20 flex items-center justify-center text-[#0071E3] font-bold text-2xl" style="display: none;">
        {{ strtoupper(substr($app->name, 0, 1)) }}
    </div>

    @if($app->is_updated)
    <!-- Animated Update Badge (Icon-only, animated, no text) -->
    <div class="absolute -top-1.5 -right-1.5 z-10 flex items-center justify-center pointer-events-none" title="Programa actualizado">
      <span class="relative flex h-5 w-5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
        <span class="relative inline-flex items-center justify-center rounded-full h-5 w-5 bg-gradient-to-tr from-amber-500 to-amber-300 text-black shadow-lg shadow-amber-500/50 ring-2 ring-[#121214]">
          <svg class="w-3 h-3 animate-spin [animation-duration:4s]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
        </span>
      </span>
    </div>
    @endif
  </a>
  <div class="card-body">
    <h2 class="entry-title">
      <a href="{{ route('app', $app->slug) }}" class="after:absolute after:inset-0 after:z-[1]">{{ $app->name }}</a>
    </h2>
    <div class="entry-meta relative z-[2]">
      <a class="cat-pill" href="{{ route('category', $app->category?->slug ?? 'system-utilities') }}">{{ $app->category?->name ?? 'System Utilities' }}</a>
      @if($app->has_torrent && $app->download_url_external)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30" title="Direct Download + Torrent">DUAL</span>
      @elseif($app->has_torrent)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30" title="Torrent Download">TORRENT</span>
      @endif
      @if($app->version)
      <span class="meta-item meta-ver">{{ $app->version }}</span>
      @endif
      <span class="meta-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M21 12a9 9 0 1 1-2.64-6.36M21 3v6h-6"></path>
        </svg>
        <time datetime="{{ $app->updated_at?->toIso8601String() }}">Updated {{ $app->updated_at?->format('M j, Y') ?? 'Oct 4, 2026' }}</time>
      </span>
      <span class="card-dl" title="{{ $app->formatted_downloads }} downloads">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
          <path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"></path>
        </svg>{{ $app->formatted_downloads }}
      </span>
    </div>
  </div>
</article>
