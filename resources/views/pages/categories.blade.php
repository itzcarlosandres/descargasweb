<x-app-layout>
    @section('title', 'All Categories')
    @section('description', 'Browse all application categories')

    <div class="container-app py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-text">Categories</h1>
            <p class="text-text-secondary text-sm mt-1">Browse applications by category</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($categories as $category)
                <x-category-card :category="$category" />
            @endforeach
        </div>
    </div>
</x-app-layout>
