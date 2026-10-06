<x-app-layout>
    @section('title', 'Page Not Found')

    <div class="container-app py-20 text-center">
        <h1 class="text-6xl font-bold text-primary mb-4">404</h1>
        <h2 class="text-xl font-semibold text-text mb-2">Page Not Found</h2>
        <p class="text-text-secondary text-sm mb-6">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ route('home') }}" class="btn-primary text-sm">Go Home</a>
    </div>
</x-app-layout>
