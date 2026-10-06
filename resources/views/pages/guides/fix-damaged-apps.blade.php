<x-app-layout>
    @section('title', 'How to Fix Damaged Apps on macOS - ' . config('app.name'))
    @section('description', 'Complete solution for "App is damaged and cannot be opened" on macOS Sequoia, Sonoma, and Ventura.')

    <div class="container-app pt-3 sm:pt-5 pb-16">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2.5 text-xs text-[#8C847A] pb-2">
                <span class="w-3 h-3 rounded-full bg-[#FF5F56] inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] inline-block shadow-sm"></span>
                <span class="w-3 h-3 rounded-full bg-[#27C93F] inline-block shadow-sm"></span>
                <nav class="ml-2 flex items-center gap-1.5">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                    <span>&rsaquo;</span>
                    <span class="text-white font-medium">Guides</span>
                    <span>&rsaquo;</span>
                    <span class="text-primary font-medium">Fix Damaged Apps</span>
                </nav>
            </div>

            <!-- Guide Card -->
            <div class="bg-[#1A1713] border border-[#2B251E] rounded-2xl p-6 sm:p-9 shadow-2xl relative overflow-hidden"
                 x-data="{ copied: '' }">
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/15 border border-primary/25 text-primary text-xs font-bold mb-4">
                    <span>🛠️ Fix Damaged Apps & Gatekeeper</span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight mb-3">
                    Fix "App is damaged and can't be opened"
                </h1>
                <p class="text-sm text-[#A8A199] leading-relaxed mb-6">
                    This error commonly occurs on macOS Sequoia, Sonoma, and Ventura when opening downloaded software that is not signed by an official Apple Developer ID. Apple Gatekeeper flags the application as "quarantined".
                </p>

                <div class="space-y-6">
                    <!-- Method 1: Remove Quarantine Attribute (Best & Recommended) -->
                    <div class="p-5 sm:p-6 rounded-xl bg-[#201C17] border border-[#302820] space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded-full bg-success/20 text-success text-xs font-bold">
                                Method 1 (Recommended)
                            </span>
                            <span class="text-xs text-[#7E776F]">99% Success Rate</span>
                        </div>
                        <h3 class="text-base font-bold text-white">
                            Remove the macOS Quarantine Attribute (xattr)
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed">
                            Open your <strong>Terminal</strong> (search with Cmd + Space &gt; Terminal) and paste the following command, replacing <code>AppName.app</code> with your application's actual name:
                        </p>

                        <div class="bg-[#12100E] border border-[#2E2720] rounded-xl p-3.5 font-mono text-xs text-white flex items-center justify-between">
                            <code class="text-primary font-bold">sudo xattr -cr /Applications/AppName.app</code>
                            <button type="button" @click="navigator.clipboard.writeText('sudo xattr -cr /Applications/AppName.app'); copied = 'xattr'; setTimeout(() => copied = '', 2000)"
                                    class="px-3 py-1 rounded bg-[#25201A] hover:bg-[#302821] text-[#A8A199] hover:text-white text-xs font-sans transition-colors cursor-pointer">
                                <span x-text="copied === 'xattr' ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                        <p class="text-xs text-[#7E776F]">
                            Tip: You can type <code class="text-white">sudo xattr -cr </code> (with a space) and then drag and drop the app icon from Finder directly into the Terminal window. Press Enter and enter your Mac password.
                        </p>
                    </div>

                    <!-- Method 2: Allow Apps from Anywhere (Disable Gatekeeper) -->
                    <div class="p-5 sm:p-6 rounded-xl bg-[#201C17] border border-[#302820] space-y-3.5">
                        <span class="px-2.5 py-0.5 rounded-full bg-warning/20 text-warning text-xs font-bold">
                            Method 2
                        </span>
                        <h3 class="text-base font-bold text-white">
                            Allow Apps from Anywhere (Disable Gatekeeper)
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed">
                            To bring back the missing <em>"Anywhere"</em> security option in <strong>System Settings &gt; Privacy &amp; Security</strong>:
                        </p>

                        <div class="bg-[#12100E] border border-[#2E2720] rounded-xl p-3.5 font-mono text-xs text-white flex items-center justify-between">
                            <code class="text-warning font-bold">sudo spctl --master-disable</code>
                            <button type="button" @click="navigator.clipboard.writeText('sudo spctl --master-disable'); copied = 'spctl'; setTimeout(() => copied = '', 2000)"
                                    class="px-3 py-1 rounded bg-[#25201A] hover:bg-[#302821] text-[#A8A199] hover:text-white text-xs font-sans transition-colors cursor-pointer">
                                <span x-text="copied === 'spctl' ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                        <p class="text-xs text-[#7E776F]">
                            To re-enable Gatekeeper later, simply run <code class="text-success">sudo spctl --master-enable</code>.
                        </p>
                    </div>

                    <!-- Method 3: Self-sign Application with Codesign -->
                    <div class="p-5 sm:p-6 rounded-xl bg-[#201C17] border border-[#302820] space-y-3.5">
                        <span class="px-2.5 py-0.5 rounded-full bg-primary/20 text-primary text-xs font-bold">
                            Method 3
                        </span>
                        <h3 class="text-base font-bold text-white">
                            Re-sign Application Locally (Ad-Hoc Codesign)
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed">
                            If the app crashes immediately upon launch (SIGKILL / invalid signature), you can apply an ad-hoc local signature:
                        </p>

                        <div class="bg-[#12100E] border border-[#2E2720] rounded-xl p-3.5 font-mono text-xs text-white flex items-center justify-between">
                            <code class="text-primary font-bold">sudo codesign --force --deep --sign - /Applications/AppName.app</code>
                            <button type="button" @click="navigator.clipboard.writeText('sudo codesign --force --deep --sign - /Applications/AppName.app'); copied = 'codesign'; setTimeout(() => copied = '', 2000)"
                                    class="px-3 py-1 rounded bg-[#25201A] hover:bg-[#302821] text-[#A8A199] hover:text-white text-xs font-sans transition-colors cursor-pointer">
                                <span x-text="copied === 'codesign' ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-[#262019] flex items-center justify-between">
                    <span class="text-xs text-[#8C847A]">Need extra assistance?</span>
                    <a href="{{ route('contact') }}" class="text-xs text-primary hover:underline">Contact our support team &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
