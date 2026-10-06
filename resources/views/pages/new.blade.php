<x-app-layout>
    @section('title', 'New Releases')
    @section('description', 'Latest applications and games')

    <div class="container-app py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-text">New Releases</h1>
            <p class="text-text-secondary text-sm mt-1">Latest applications and updates</p>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($applications as $app)
                <x-app-card :app="$app" />
            @empty
                <div class="col-span-2 card p-12 text-center">
                    <p class="text-text-muted">No new releases yet.</p>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$applications" />
    </div>
</x-app-layout>
