<footer class="bg-[#F5F5F7] dark:bg-[#161617] border-t border-[#E5E7EB] dark:border-[#333336] mt-16 pt-12 pb-8">
    <div class="container-app">
        <!-- Centered Brand & Tagline -->
        <div class="flex flex-col items-center justify-center text-center mb-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 mb-2 hover:opacity-90 transition-opacity">
                @if(setting('logo_type') === 'image' && setting('logo_image'))
                    <img src="{{ setting('logo_image') }}" alt="{{ setting('site_name', 'HackMac') }}{{ setting('site_name_highlight', '.cc') }}" class="h-7 max-w-[150px] object-contain">
                @else
                    <x-logo-icon :icon="setting('logo_icon', 'finder')" class="w-7 h-7 flex-shrink-0" />
                    <span class="text-[#1D1D1F] dark:text-white font-extrabold text-lg tracking-tight">{{ setting('site_name', 'HackMac') }}<span class="text-primary font-bold">{{ setting('site_name_highlight', '.cc') }}</span></span>
                @endif
            </a>
            <p class="text-xs text-[#6B7280] dark:text-[#A1A1A6] font-medium">
                {{ setting('site_tagline', 'Essential Mac Apps & Productivity Tools') }}
            </p>
        </div>

        <!-- Center Navigation Links -->
        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-[#6B7280] dark:text-[#A1A1A6] mb-10 pb-8 border-b border-[#E5E7EB] dark:border-[#333336]">
            <a href="{{ route('contact') }}" class="hover:text-[#0071E3] dark:hover:text-white transition-colors">Contact Us</a>
            <a href="{{ route('privacy') }}" class="hover:text-[#0071E3] dark:hover:text-white transition-colors">Privacy Policy</a>
            <a href="{{ route('dmca') }}" class="hover:text-[#0071E3] dark:hover:text-white transition-colors">DMCA — Copyrights !</a>
            <a href="{{ route('sitemap') }}" class="hover:text-[#0071E3] dark:hover:text-white transition-colors">Sitemap</a>
            <a href="{{ route('terms') }}" class="hover:text-[#0071E3] dark:hover:text-white transition-colors">Terms and Conditions</a>
            <a href="{{ route('about') }}" class="hover:text-[#0071E3] dark:hover:text-white transition-colors">About US</a>
        </div>

        <!-- Bottom Row: Copyright & Back to Top -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#6B7280] dark:text-[#A1A1A6]">
            <div>
                {{ setting('footer_text', '© HackMac.cc ' . date('Y') . ' | All Rights Reserved.') }}
            </div>

            <!-- Back to top button -->
            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white dark:bg-[#242426] hover:bg-[#F5F5F7] dark:hover:bg-[#2C2C2F] border border-[#E5E7EB] dark:border-[#333336] text-[#1D1D1F] dark:text-[#A1A1A6] hover:text-[#0071E3] dark:hover:text-white transition-colors cursor-pointer text-xs shadow-sm">
                <svg class="w-3 h-3 text-[#6B7280] dark:text-[#A1A1A6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                <span>Back to top</span>
            </button>
        </div>
    </div>
</footer>
