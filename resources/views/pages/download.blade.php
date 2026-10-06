<x-app-layout>
    @php
        $selectedVersion = $targetVersion ? $targetVersion->version : $application->version;
        $selectedSize = $targetVersion && $targetVersion->size ? $targetVersion->size : $application->formatted_size;
        $downloadFormat = $isTorrent ? '.torrent File (P2P)' : 'macOS Installer (.dmg / .pkg)';

        $pageTitle = "Downloading {$application->name} {$selectedVersion} for Mac - " . config('app.name');
        $pageDesc = "Your download of {$application->name} {$selectedVersion} is ready. Safe, verified, and optimized software for Apple Silicon M1/M2/M3/M4 and Intel.";
    @endphp

    @section('title', $pageTitle)
    @section('description', $pageDesc)
    @section('robots', 'noindex, follow')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Back Link & Breadcrumbs -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 text-xs text-[#8C847A]">
            <a href="{{ route('app', $application->slug) }}"
               class="inline-flex items-center gap-2 font-semibold text-primary hover:text-primary-hover transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to {{ $application->name }}</span>
            </a>

            <nav class="cp-crumbs cp-crumbs-yoast hidden sm:block" aria-label="Breadcrumb">
                <span>
                    <span><a href="{{ route('home') }}">Home</a></span>
                    <span class="mx-1 text-[#A1A1A6]">»</span>
                    <span><a href="{{ route('category', $application->category->slug) }}">{{ $application->category->name }}</a></span>
                    <span class="mx-1 text-[#A1A1A6]">»</span>
                    <span><a href="{{ route('app', $application->slug) }}">{{ $application->name }}</a></span>
                    <span class="mx-1 text-[#A1A1A6]">»</span>
                    <span class="breadcrumb_last text-[#E0D7D0]" aria-current="page">Download</span>
                </span>
            </nav>
        </div>

        <!-- Main Window Container (macOS Glassmorphism Window) -->
        <div class="single-window bg-[#14110E] border border-[#2D251D] rounded-3xl shadow-[0_30px_70px_rgba(0,0,0,0.7)] overflow-hidden backdrop-blur-xl">
            <!-- Window Titlebar with Traffic Lights -->
            <div class="px-5 py-4 bg-[#1C1814]/90 border-b border-[#2D251D] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-[#E0443E] inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-[#DEA123] inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-[#1AAB29] inline-block"></span>
                    <span class="ml-3 text-xs font-semibold text-[#8C847A] tracking-wide">
                        Secure Download Center &middot; macOS Universal
                    </span>
                </div>
                <div class="flex items-center gap-2 text-[11px] font-mono text-[#8C847A]">
                    <span class="w-2 h-2 rounded-full bg-success animate-pulse inline-block"></span>
                    <span>Server Active</span>
                </div>
            </div>

            <div class="p-6 sm:p-10">
                <!-- App Meta Header Banner -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-8 border-b border-[#262018]">
                    <!-- Squircle App Icon -->
                    <div class="w-24 h-24 rounded-3xl bg-[#1C1814] border border-[#2D251D] p-2 flex items-center justify-center flex-shrink-0 shadow-xl relative overflow-hidden group">
                        <img src="{{ $application->icon_url }}"
                             alt="{{ $application->name }}"
                             class="w-full h-full object-contain rounded-2xl transition-transform duration-300 group-hover:scale-105"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="hidden w-full h-full rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center">
                            <span class="font-heading font-bricolage text-3xl font-extrabold text-primary">
                                {{ strtoupper(substr($application->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>

                    <!-- App Title & Badges -->
                    <div class="text-center sm:text-left flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isTorrent ? 'bg-[#30D158]/15 text-[#30D158] border border-[#30D158]/30' : 'bg-primary/15 text-primary border border-primary/30' }}">
                                {{ $isTorrent ? '⚡ P2P Torrent' : '☁️ Direct Download' }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#262019] text-[#A8A199] border border-[#3D3224]">
                                {{ strtoupper($application->license ?? 'Free') }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#262019] text-[#A8A199] border border-[#3D3224]">
                                Universal (M1/M2/M3/M4 & Intel)
                            </span>
                        </div>

                        <h1 class="font-heading font-bricolage text-2xl sm:text-3xl font-bold text-white tracking-tight">
                            Downloading {{ $application->name }}
                        </h1>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-2 text-xs text-[#A8A199]">
                            <span class="font-mono font-bold text-primary text-sm">v{{ $selectedVersion }}</span>
                            <span>&middot;</span>
                            <span>{{ $selectedSize }}</span>
                            <span>&middot;</span>
                            <span>{{ $downloadFormat }}</span>
                            @if($targetVersion && ! $targetVersion->is_current)
                                <span>&middot;</span>
                                <span class="text-amber-400 font-semibold bg-amber-400/10 px-2 py-0.5 rounded border border-amber-400/20">
                                    Previous Version
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Alpine.js 5-Second Countdown Engine -->
                <div x-data="{
                    countdown: 5,
                    downloadReady: false,
                    fileUrl: '',
                    _k: '{{ $obfuscatedToken }}',

                    init() {
                        let timer = setInterval(() => {
                            if (this.countdown > 1) {
                                this.countdown--;
                            } else {
                                this.countdown = 0;
                                this.downloadReady = true;
                                clearInterval(timer);
                                this.prepareDownloadUrl();
                            }
                        }, 1000);
                    },

                    prepareDownloadUrl() {
                        if (!this.fileUrl) {
                            try {
                                const token = atob(this._k);
                                this.fileUrl = '{{ url('/dl') }}/' + token;
                            } catch (e) {
                                this.fileUrl = '{{ $fileDownloadUrl }}';
                            }
                        }
                    }
                }" class="my-8">

                    <!-- State 1: Counting Down (5.. 4.. 3.. 2.. 1) -->
                    <div x-show="!downloadReady" class="text-center py-10 px-6 rounded-3xl bg-[#1A1612]/70 border border-[#2B231B] relative overflow-hidden shadow-inner">
                        <!-- Glowing Countdown Ring -->
                        <div class="relative w-24 h-24 mx-auto mb-5 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="42" stroke="rgba(255,255,255,0.08)" stroke-width="6" fill="transparent"/>
                                <circle cx="50" cy="50" r="42" stroke="#0071E3" stroke-width="6" stroke-linecap="round" fill="transparent"
                                        stroke-dasharray="264"
                                        :stroke-dashoffset="264 - ((5 - countdown) / 5) * 264"
                                        class="transition-all duration-1000 ease-linear"/>
                            </svg>
                            <span class="absolute font-heading font-bricolage text-3xl font-extrabold text-white" x-text="countdown"></span>
                        </div>

                        <h2 class="font-heading font-bricolage text-xl sm:text-2xl font-bold text-white mb-2">
                            Preparing secure download link...
                        </h2>
                        <p class="text-xs sm:text-sm text-[#8C847A] max-w-md mx-auto leading-relaxed">
                            Verifying high-speed servers and package integrity for macOS.
                        </p>

                        <!-- Visual Progress Bar -->
                        <div class="w-full max-w-sm mx-auto bg-[#262018] h-2 rounded-full mt-6 overflow-hidden">
                            <div class="bg-gradient-to-r from-primary to-[#4D9BE9] h-full transition-all duration-1000 ease-linear rounded-full shadow-[0_0_12px_rgba(0,113,227,0.5)]"
                                 :style="'width: ' + ((5 - countdown) / 5 * 100) + '%'"></div>
                        </div>
                    </div>

                    <!-- State 2: Ready State (User clicks manually to download in new tab) -->
                    <div x-show="downloadReady" x-cloak class="text-center py-10 px-6 rounded-3xl bg-[#171F17]/80 border border-[#30D158]/30 relative overflow-hidden shadow-[0_15px_40px_rgba(48,209,88,0.1)]">
                        <!-- Big Check Icon -->
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[#30D158]/15 border-2 border-[#30D158]/40 mb-4 shadow-[0_0_30px_rgba(48,209,88,0.3)] animate-bounce">
                            <svg class="w-10 h-10 text-[#30D158]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        <h2 class="font-heading font-bricolage text-2xl sm:text-3xl font-bold text-white mb-2">
                            Your link is ready!
                        </h2>
                        <p class="text-xs sm:text-sm text-[#8C847A] max-w-md mx-auto mb-6 leading-relaxed">
                            Click the button below to start your download:
                        </p>

                        <!-- Primary Direct Action Button (Opens in New Tab on Click) -->
                        <div class="max-w-md mx-auto">
                            <a :href="fileUrl"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="w-full py-4 px-6 rounded-2xl text-base font-extrabold text-black bg-gradient-to-r from-[#30D158] to-[#10B981] hover:from-[#28C840] hover:to-[#059669] shadow-xl shadow-[#30D158]/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-3 cursor-pointer group">
                                <svg class="w-5 h-5 text-black group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Download Now</span>
                            </a>
                        </div>

                        @php
                            $mirrors = $targetVersion?->download_mirrors ?? $application->download_mirrors ?? [];
                            if (is_string($mirrors)) {
                                $mirrors = json_decode($mirrors, true) ?? [];
                            }
                        @endphp
                        @if(!empty($mirrors) && count($mirrors) > 0)
                            <div class="mt-6 pt-5 border-t border-[#2B231B] max-w-md mx-auto">
                                <p class="text-xs font-semibold text-[#8C847A] uppercase tracking-wider mb-3">
                                    Alternative Mirrors
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($mirrors as $mirror)
                                        @if(!empty($mirror['url']))
                                            <a href="{{ $mirror['url'] }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="px-4 py-2.5 rounded-xl bg-[#14120F] hover:bg-[#1E1914] border border-[#2B231B] hover:border-[#30D158]/50 text-xs font-semibold text-white transition flex items-center justify-between group shadow-sm">
                                                <span class="flex items-center gap-2 truncate">
                                                    <span class="w-2 h-2 rounded-full bg-[#30D158]"></span>
                                                    <span class="truncate">{{ !empty($mirror['name']) ? $mirror['name'] : 'Alternative Mirror' }}</span>
                                                </span>
                                                <svg class="w-3.5 h-3.5 text-[#8C847A] group-hover:text-[#30D158] transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Trust & Security Highlights -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-8">
                    <div class="p-4 rounded-2xl bg-[#1A1612] border border-[#2B231B] flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-success/15 border border-success/30 flex items-center justify-center text-success flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-white">100% Malware-Free</span>
                            <span class="block text-[11px] text-[#8C847A]">Scanned with VirusTotal and verified safe</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#1A1612] border border-[#2B231B] flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-primary/15 border border-primary/30 flex items-center justify-center text-primary flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-white">Maximum Speed</span>
                            <span class="block text-[11px] text-[#8C847A]">High-speed CDN servers with unlimited bandwidth</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#1A1612] border border-[#2B231B] flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-purple-400 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-white">macOS Optimized</span>
                            <span class="block text-[11px] text-[#8C847A]">Sequoia, Sonoma & Ventura</span>
                        </div>
                    </div>
                </div>

                <!-- macOS Gatekeeper & Terminal Helper Box -->
                <div class="p-5 sm:p-6 rounded-2xl bg-[#181410] border border-[#2A2219] text-xs">
                    <div class="flex items-center gap-2 mb-2 font-bold text-white">
                        <span>💡 Does macOS show: <em>"App is damaged and can't be opened"</em>?</span>
                    </div>
                    <p class="text-[#8C847A] leading-relaxed mb-3">
                        This happens due to Gatekeeper security when opening apps outside the Mac App Store. To resolve it in 5 seconds, open <strong>Terminal</strong> and run the following command:
                    </p>
                    <div class="p-3 rounded-xl bg-black/60 border border-[#332A20] font-mono text-[12px] text-primary flex items-center justify-between gap-3 select-all">
                        <span class="truncate">sudo xattr -cr /Applications/{{ str_replace(' ', '\ ', $application->name) }}.app</span>
                        <span class="text-[10px] text-[#8C847A] uppercase tracking-wider font-sans font-bold flex-shrink-0">Terminal</span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px]">
                        <a href="{{ route('guide.fix') }}" target="_blank" class="text-primary hover:underline font-semibold flex items-center gap-1">
                            <span>View detailed guide to fix damaged apps →</span>
                        </a>
                        <span class="text-[#3D3224]">&middot;</span>
                        <a href="{{ route('guide.sip') }}" target="_blank" class="text-primary hover:underline font-semibold flex items-center gap-1">
                            <span>How to disable SIP on macOS →</span>
                        </a>
                    </div>
                </div>

                <!-- Related Applications in Category -->
                @if($relatedApps && $relatedApps->count() > 0)
                    <div class="mt-10 pt-8 border-t border-[#262018]">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-heading font-bricolage text-base font-bold text-white">
                                Other {{ $application->category->name }} apps you might like
                            </h3>
                            <a href="{{ route('category', $application->category->slug) }}" class="text-xs text-primary hover:underline font-medium">
                                View all →
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($relatedApps as $rel)
                                <a href="{{ route('app', $rel->slug) }}"
                                   class="p-3 rounded-2xl bg-[#1A1612] hover:bg-[#221D17] border border-[#2B231B] hover:border-[#3D3224] transition-all flex items-center gap-3 group">
                                    <div class="w-10 h-10 rounded-xl bg-[#14110E] border border-[#2D251D] p-1 flex items-center justify-center flex-shrink-0">
                                        <img src="{{ $rel->icon_url }}" alt="{{ $rel->name }}" class="w-full h-full object-contain rounded-lg">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-heading font-bricolage text-xs font-bold text-white truncate group-hover:text-primary transition-colors">
                                            {{ $rel->name }}
                                        </h4>
                                        <span class="text-[10px] text-[#8C847A] font-mono">v{{ $rel->version }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
