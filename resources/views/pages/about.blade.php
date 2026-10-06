<x-app-layout>
    @section('title', 'About Us - ' . config('app.name'))
    @section('description', 'About HackMac.cc - The leading portal for essential macOS applications, utilities, and games.')

    <div class="container-app pt-4 sm:pt-6 pb-16">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-[#242426] border border-[#333336] rounded-2xl p-6 sm:p-9 shadow-2xl space-y-6 text-xs sm:text-sm text-[#A1A1A6] leading-relaxed">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2C2C2F] via-[#242426] to-[#161617] border border-[#333336] flex items-center justify-center p-2 shadow-inner">
                        <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.63-.77 1.06-1.85.94-2.93-.93.04-2.07.63-2.73 1.4-.58.67-1.1 1.76-.96 2.82 1.05.08 2.12-.52 2.75-1.29z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">About HackMac.cc</h1>
                        <p class="text-xs text-[#A1A1A6]">Essential Mac Apps &amp; Productivity Tools</p>
                    </div>
                </div>

                <p>
                    <strong>HackMac.cc</strong> was founded with a clear mission: to provide the macOS community with a curated, lightning-fast, and verified directory of software, developer tools, creative suites, and games.
                </p>

                <!-- Live stats grid -->
                <div class="grid grid-cols-3 gap-3 p-4 rounded-xl bg-[#161617] border border-[#333336] text-center my-4">
                    <div>
                        <p class="text-2xl font-extrabold text-primary">{{ number_format($stats['total_apps'] ?? 1023) }}</p>
                        <p class="text-[10px] uppercase font-bold text-[#A1A1A6] tracking-wider mt-1">Apps &amp; Games</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-white">{{ number_format(($stats['total_downloads'] ?? 530800) / 1000, 1) }}K</p>
                        <p class="text-[10px] uppercase font-bold text-[#A1A1A6] tracking-wider mt-1">Downloads Served</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-success">{{ $stats['total_categories'] ?? 12 }}</p>
                        <p class="text-[10px] uppercase font-bold text-[#A1A1A6] tracking-wider mt-1">Categories</p>
                    </div>
                </div>

                <h2 class="text-base font-bold text-white pt-2">Our Quality Standard</h2>
                <ul class="list-disc pl-5 space-y-1.5 text-[#CBD5E1]">
                    <li>Every release is scanned and checked against malicious software.</li>
                    <li>Direct high-speed mirrors and cloud downloads.</li>
                    <li>Comprehensive guides for Gatekeeper bypass and SIP management.</li>
                    <li>Community feedback, threaded discussions, and verification by real Mac users.</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
