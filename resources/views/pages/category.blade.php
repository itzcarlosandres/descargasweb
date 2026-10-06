<x-app-layout>
    @section('title', "Download {$category->name} for macOS - " . config('app.name'))
    @section('description', $category->description ?: "Explore and download the best {$category->name} apps for Mac. Safe, verified and direct downloads.")
    @section('canonical', route('category', $category->slug))
    @section('og_image', asset('images/og-share.jpg'))

    @section('schema')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "{{ route('home') }}"
            },
            {
                "@@type": "ListItem",
                "position": 2,
                "name": "{{ addslashes($category->name) }}",
                "item": "{{ route('category', $category->slug) }}"
            }
        ]
    }
    </script>
    @endsection

    <div class="container-app py-8">
        <x-breadcrumb :items="[['label' => $category->name]]" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-text">{{ $category->name }}</h1>
            <p class="text-text-secondary text-sm mt-1">{{ $category->description }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($applications as $app)
                <x-app-card :app="$app" />
            @empty
                <div class="col-span-2 card p-12 text-center">
                    <p class="text-text-muted">No applications found in this category.</p>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$applications" />
    </div>
</x-app-layout>
