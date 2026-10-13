<x-app-layout>
    @section('title', 'Recently Updated Apps')
    @section('description', 'Latest updated applications, new versions and patches for macOS')

    <div class="container-app py-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-[3px] bg-[#30D158] inline-block shadow-sm shadow-[#30D158]/50"></span>
                    <h1 class="text-2xl font-bold text-text">Recently Updated</h1>
                </div>
                <p class="text-text-secondary text-sm mt-1">Latest versions, patches and updates for macOS</p>
            </div>
            <a href="{{ route('new') }}" class="text-xs font-semibold text-text-muted hover:text-primary transition-colors">
                View New Releases →
            </a>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($applications as $app)
                <x-app-card :app="$app" />
            @empty
                <div class="col-span-2 card p-12 text-center">
                    <p class="text-text-muted">No recently updated applications found.</p>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$applications" />
    </div>
</x-app-layout>
