@props(['category'])

<a href="{{ route('category', $category->slug) }}" class="card card-hover p-4 flex items-center gap-3 group">
    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#28211A] to-[#181410] border border-[#3E342A] flex items-center justify-center flex-shrink-0 text-primary group-hover:scale-105 group-hover:border-primary/40 transition-all shadow-md">
        <x-category-icon :icon="$category->icon" :name="$category->slug" class="w-5 h-5 text-primary" />
    </div>
    <div class="flex-1 min-w-0">
        <h3 class="text-text font-medium text-sm group-hover:text-primary transition-colors">{{ $category->name }}</h3>
        <p class="text-text-muted text-xs">{{ $category->published_applications_count ?? $category->publishedApplications()->count() }} apps</p>
    </div>
    <svg class="w-4 h-4 text-text-muted group-hover:text-primary group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
