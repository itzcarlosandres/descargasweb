<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Panel de Administración') - {{ config('app.name') }}</title>

    @if(setting('favicon_image'))
    <link rel="icon" href="{{ setting('favicon_image') }}">
    <link rel="shortcut icon" href="{{ setting('favicon_image') }}">
    <link rel="apple-touch-icon" href="{{ setting('favicon_image') }}">
    @else
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen bg-[#0E0D0B] text-white font-sans antialiased selection:bg-primary/30 selection:text-white"
      x-data="{ sidebarOpen: false }"
      @keydown.escape.window="sidebarOpen = false">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Off-Canvas Drawer (Only visible on mobile when toggled) -->
        <div x-show="sidebarOpen"
             x-cloak
             class="fixed inset-0 z-50 lg:hidden"
             style="display: none;"
             role="dialog"
             aria-modal="true">
            <!-- Backdrop Overlay with Fade Animation -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>

            <!-- Slide-in Drawer Container -->
            <div class="fixed inset-y-0 left-0 flex max-w-full z-50">
                <aside x-show="sidebarOpen"
                       x-transition:enter="transition ease-out duration-300 transform"
                       x-transition:enter-start="-translate-x-full"
                       x-transition:enter-end="translate-x-0"
                       x-transition:leave="transition ease-in duration-200 transform"
                       x-transition:leave-start="translate-x-0"
                       x-transition:leave-end="-translate-x-full"
                       class="w-72 max-w-[85vw] bg-[#12100E] border-r border-[#26211B] flex flex-col shadow-2xl h-full">
                    @include('layouts.partials.admin-sidebar', ['isMobile' => true])
                </aside>
            </div>
        </div>

        <!-- Desktop Static Sidebar (Hidden on mobile, static on lg:flex) -->
        <aside class="hidden lg:flex w-72 bg-[#12100E] border-r border-[#26211B] flex-col flex-shrink-0 z-20">
            @include('layouts.partials.admin-sidebar', ['isMobile' => false])
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#0E0D0B]">
            <!-- Topbar Header -->
            <header class="h-16 bg-[#12100E] border-b border-[#26211B] flex items-center justify-between px-4 sm:px-6 lg:px-8 flex-shrink-0 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Mobile Hamburger Button -->
                    <button type="button" @click="sidebarOpen = true"
                            class="lg:hidden p-2 rounded-xl bg-[#181410] border border-[#2B241C] text-white hover:text-primary transition-colors focus:outline-none shrink-0 cursor-pointer"
                            aria-label="Abrir menú de navegación">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Mobile Mini Brand -->
                    <a href="{{ route('admin.dashboard') }}" class="flex lg:hidden items-center gap-2.5 shrink-0">
                        <div class="w-7 h-7 flex-shrink-0 flex items-center justify-center">
                            @if(setting('logo_type') === 'image' && setting('logo_image'))
                                <img src="{{ setting('logo_image') }}" alt="Logo" class="w-full h-full object-contain rounded-md">
                            @else
                                <x-logo-icon :icon="setting('logo_icon', 'finder')" class="w-full h-full" />
                            @endif
                        </div>
                        <span class="text-white font-extrabold text-xs sm:text-sm tracking-tight hidden xs:inline">{{ setting('site_name', 'HackMac') }}<span class="text-primary">{{ setting('site_name_highlight', '.cc') }}</span></span>
                    </a>

                    <!-- Breadcrumbs -->
                    <div class="hidden sm:flex items-center text-xs text-[#8C847A] gap-2 min-w-0 truncate">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Admin</a>
                        <span>/</span>
                        <span class="text-white font-medium truncate">@yield('page-title', View::yieldContent('header', 'Dashboard'))</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    <!-- Environment / Time Pill -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-[#181410] border border-[#2B241C] text-[11px] text-[#A39B91]">
                        <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                        <span>Online</span>
                    </div>

                    <a href="{{ route('home') }}" target="_blank"
                       class="btn-secondary text-xs px-3 sm:px-3.5 py-1.5 gap-1.5">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span class="hidden sm:inline">Ver Web</span>
                        <span class="sm:hidden">Web</span>
                    </a>
                </div>
            </header>

            <!-- Alerts & Main Dynamic Content -->
            <main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-sm flex items-center justify-between shadow-lg shadow-success/5 animate-fade-in"
                         x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-success/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="font-medium text-white">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-success/70 hover:text-success p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-danger text-sm flex items-center justify-between shadow-lg shadow-danger/5"
                         x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-danger/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-white">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-danger/70 hover:text-danger p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
