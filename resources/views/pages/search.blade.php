<x-app-layout>
    @section('title', 'Search Results')
    @section('description', 'Search results for applications and games')

    <div class="container-app py-8">
        <div class="card p-6 mb-8 bg-surface/70 border border-border/80">
            <form action="{{ route('search') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="q" value="{{ $query }}" placeholder="Search by name, description, developer or tag..."
                           class="input w-full pl-10 text-sm">
                    <svg class="w-4 h-4 text-text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                @if(isset($categoryId))
                    <input type="hidden" name="category" value="{{ $categoryId }}">
                @endif
                <button type="submit" class="btn-primary text-sm whitespace-nowrap px-6 font-semibold cursor-pointer">
                    Search
                </button>
            </form>

            @if(isset($categories) && $categories->count() > 0)
            <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-border">
                <span class="text-xs text-text-muted font-medium">Category:</span>
                <a href="{{ route('search', array_merge(request()->except('category', 'page'), ['category' => ''])) }}"
                   class="badge {{ empty($categoryId) ? 'bg-primary text-white font-semibold' : 'bg-surface text-text-secondary hover:text-text' }} transition-colors">
                    All
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('search', array_merge(request()->except('category', 'page'), ['category' => $cat->id])) }}"
                       class="badge {{ ($categoryId ?? '') == $cat->id ? 'bg-primary text-white font-semibold' : 'bg-surface text-text-secondary hover:text-text' }} transition-colors">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-text">Search Results</h1>
                <p class="text-text-secondary text-xs mt-0.5">
                    @if($query)
                        {{ $results->total() }} results for "<span class="text-primary font-medium">{{ $query }}</span>"
                    @else
                        {{ $results->total() }} applications available
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-text-muted mr-1">Sort by:</span>
                <a href="{{ route('search', array_merge(request()->except('sort', 'page'), ['sort' => 'relevance'])) }}"
                   class="px-2.5 py-1 rounded-md {{ request('sort', 'relevance') === 'relevance' ? 'bg-primary text-white font-medium' : 'bg-surface text-text-secondary hover:text-text' }} transition-colors">
                    Relevance
                </a>
                <a href="{{ route('search', array_merge(request()->except('sort', 'page'), ['sort' => 'newest'])) }}"
                   class="px-2.5 py-1 rounded-md {{ request('sort') === 'newest' ? 'bg-primary text-white font-medium' : 'bg-surface text-text-secondary hover:text-text' }} transition-colors">
                    Newest
                </a>
                <a href="{{ route('search', array_merge(request()->except('sort', 'page'), ['sort' => 'downloads'])) }}"
                   class="px-2.5 py-1 rounded-md {{ request('sort') === 'downloads' ? 'bg-primary text-white font-medium' : 'bg-surface text-text-secondary hover:text-text' }} transition-colors">
                    Most downloaded
                </a>
                <a href="{{ route('search', array_merge(request()->except('sort', 'page'), ['sort' => 'rating'])) }}"
                   class="px-2.5 py-1 rounded-md {{ request('sort') === 'rating' ? 'bg-primary text-white font-medium' : 'bg-surface text-text-secondary hover:text-text' }} transition-colors">
                    Top rated
                </a>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($results as $app)
                <x-app-card :app="$app" />
            @empty
                <div class="col-span-2 card p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-surface text-text-muted flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-text font-medium">No results found for "{{ request('q') }}"</p>
                    <p class="text-text-muted text-xs mt-1">Try different keywords or browse our popular categories.</p>
                    <a href="{{ route('categories') }}" class="btn-secondary text-xs inline-block mt-4">Browse all categories</a>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$results" />
    </div>
</x-app-layout>
