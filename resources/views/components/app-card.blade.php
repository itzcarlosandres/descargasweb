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
    <h2 class="entry-title">
      <a href="{{ route('app', $app->slug) }}" class="after:absolute after:inset-0 after:z-[1]">{{ $app->name }}</a>
    </h2>
    <div class="entry-meta relative z-[2]">
      <a class="cat-pill" href="{{ route('category', $app->category?->slug ?? 'system-utilities') }}">{{ $app->category?->name ?? 'System Utilities' }}</a>
      @if($app->has_torrent && $app->download_url_external)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30" title="Descarga Directa + Torrent">DUAL</span>
      @elseif($app->has_torrent)
      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30" title="Descarga Torrent">TORRENT</span>
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
