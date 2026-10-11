@props(['app'])

<article id="post-{{ $app->id }}" class="post-card post-{{ $app->id }} post type-post status-publish format-standard has-post-thumbnail hentry category-application category-{{ $app->category?->slug ?? 'system-utilities' }} group">
  <a href="{{ route('app', $app->slug) }}" aria-hidden="true" tabindex="-1" class="shrink-0 flex items-center justify-center">
    <img width="168" height="168" src="{{ $app->icon_url }}" class="card-icon wp-post-image" alt="{{ $app->name }} Logo" loading="lazy" decoding="async"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    <div class="card-icon bg-[#0071E3]/20 flex items-center justify-center text-[#0071E3] font-bold text-2xl" style="display: none;">
        {{ strtoupper(substr($app->name, 0, 1)) }}
    </div>
  </a>
  <div class="card-body">
    <h2 class="entry-title flex items-center gap-1.5 min-w-0">
      <a href="{{ route('app', $app->slug) }}" class="truncate after:absolute after:inset-0 after:z-[1]">{{ $app->name }}</a>
      @if($app->is_updated)
      <!-- Animated Update Badge (Blue, High-contrast White Icon, No text) -->
      <span class="relative flex h-4 w-4 shrink-0 z-[2]" title="Programa actualizado">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0071E3] opacity-60"></span>
        <span class="relative inline-flex items-center justify-center rounded-full h-4 w-4 bg-gradient-to-tr from-[#0071E3] to-[#2997FF] text-white shadow-md shadow-[#0071E3]/50 ring-1 ring-sky-300/40">
          <svg class="w-2.5 h-2.5 animate-spin [animation-duration:3.5s] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
        </span>
      </span>
      @endif
    </h2>
    <div class="entry-meta relative z-[2]">
      <a class="cat-pill" href="{{ route('category', $app->category?->slug ?? 'system-utilities') }}">{{ $app->category?->name ?? 'System Utilities' }}</a>
      @if($app->has_torrent && $app->download_url_external)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-500/15 text-purple-400 border border-purple-500/30" title="Direct Download + Torrent">DUAL</span>
      @elseif($app->has_torrent)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30" title="Torrent Download">TORRENT</span>
      @elseif($app->download_url_external || $app->download_url)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#0071E3]/15 text-[#0071E3] dark:text-[#4D9BE9] border border-[#0071E3]/30" title="Direct Download">DDL</span>
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
