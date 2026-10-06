<x-app-layout>
    @section('title', 'How to Disable SIP (System Integrity Protection) on macOS - ' . config('app.name'))
    @section('description', 'Step-by-step guide to disable System Integrity Protection (SIP) on Apple Silicon and Intel Macs.')

    <div class="container-app pt-3 sm:pt-5 pb-16">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Breadcrumbs with macOS traffic lights -->
            <div class="flex items-center gap-2.5 text-xs text-[#8C847A] pb-2">
                <span class="w-3 h-3 rounded-full bg-[#FF5F56] inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-[#27C93F] inline-block"></span>
                <nav class="ml-2 flex items-center gap-1.5">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                    <span>&rsaquo;</span>
                    <span class="text-white font-medium">Guides</span>
                    <span>&rsaquo;</span>
                    <span class="text-primary font-medium">Disable SIP</span>
                </nav>
            </div>

            <!-- Main Guide Card -->
            <div class="bg-[#1A1713] border border-[#2B251E] rounded-2xl p-6 sm:p-9 shadow-2xl relative overflow-hidden"
                 x-data="{ macType: 'silicon', copied: '' }">
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/15 border border-primary/25 text-primary text-xs font-bold mb-4">
                    <span>⚡ macOS Terminal Guide</span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight mb-3">
                    How to Disable System Integrity Protection (SIP)
                </h1>
                <p class="text-sm text-[#A8A199] leading-relaxed mb-6">
                    System Integrity Protection (SIP) is a security technology in macOS that restricts root accounts from modifying protected files and memory. Certain modified applications, audio plugins, and low-level system utilities require SIP to be disabled or set to permissive mode.
                </p>

                <!-- Architecture Tabs Selector -->
                <div class="flex items-center gap-3 p-1.5 rounded-xl bg-[#14120F] border border-[#28211A] mb-8 max-w-md">
                    <button type="button" @click="macType = 'silicon'"
                            :class="macType === 'silicon' ? 'bg-primary text-white font-bold shadow-md' : 'text-[#8C847A] hover:text-white'"
                            class="flex-1 py-2 px-3 rounded-lg text-xs transition-all cursor-pointer text-center">
                        Apple Silicon (M1/M2/M3/M4)
                    </button>
                    <button type="button" @click="macType = 'intel'"
                            :class="macType === 'intel' ? 'bg-primary text-white font-bold shadow-md' : 'text-[#8C847A] hover:text-white'"
                            class="flex-1 py-2 px-3 rounded-lg text-xs transition-all cursor-pointer text-center">
                        Intel Processors
                    </button>
                </div>

                <!-- Steps for Apple Silicon -->
                <div x-show="macType === 'silicon'" class="space-y-6">
                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">1</span>
                            Enter macOS Recovery Mode
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8">
                            Shut down your Mac completely. Press and <strong>hold down the Power button (Touch ID)</strong> until you see <em>"Loading startup options"</em>. Then click on <strong>Options</strong> and select <strong>Continue</strong>.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">2</span>
                            Open Terminal
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8">
                            In the top menu bar, click on <strong>Utilities</strong> and choose <strong>Terminal</strong>.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">3</span>
                            Run the SIP Disable Command
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8 mb-2">
                            Type or copy the following command into the Terminal and press Enter:
                        </p>
                        <div class="ml-8 relative">
                            <div class="bg-[#12100E] border border-[#2E2720] rounded-xl p-3.5 font-mono text-xs text-white flex items-center justify-between">
                                <code class="text-primary font-bold">csrutil disable</code>
                                <button type="button" @click="navigator.clipboard.writeText('csrutil disable'); copied = 'disable'; setTimeout(() => copied = '', 2000)"
                                        class="px-3 py-1 rounded bg-[#25201A] hover:bg-[#302821] text-[#A8A199] hover:text-white text-xs font-sans transition-colors cursor-pointer">
                                    <span x-text="copied === 'disable' ? 'Copied!' : 'Copy'"></span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-[#7E776F] pl-8 mt-2">
                            Type <code class="text-white">y</code> and enter your administrator password when prompted.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">4</span>
                            Reboot Your Mac
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8">
                            In Terminal, type <code class="text-primary">reboot</code> or click the Apple logo () and restart your Mac normally.
                        </p>
                    </div>
                </div>

                <!-- Steps for Intel Macs -->
                <div x-show="macType === 'intel'" x-cloak class="space-y-6">
                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">1</span>
                            Reboot into Recovery
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8">
                            Restart your Mac and immediately press and hold <kbd class="px-2 py-0.5 rounded bg-[#2C241C] text-white">Cmd (⌘) + R</kbd> until the Apple logo or a spinning globe appears.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">2</span>
                            Launch Terminal & Execute Command
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8 mb-2">
                            Go to <strong>Utilities &gt; Terminal</strong> and run:
                        </p>
                        <div class="ml-8 relative">
                            <div class="bg-[#12100E] border border-[#2E2720] rounded-xl p-3.5 font-mono text-xs text-white flex items-center justify-between">
                                <code class="text-primary font-bold">csrutil disable</code>
                                <button type="button" @click="navigator.clipboard.writeText('csrutil disable'); copied = 'disable_intel'; setTimeout(() => copied = '', 2000)"
                                        class="px-3 py-1 rounded bg-[#25201A] hover:bg-[#302821] text-[#A8A199] hover:text-white text-xs font-sans transition-colors cursor-pointer">
                                    <span x-text="copied === 'disable_intel' ? 'Copied!' : 'Copy'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 rounded-xl bg-[#201C17] border border-[#302820] space-y-3">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">3</span>
                            Restart System
                        </h3>
                        <p class="text-xs sm:text-sm text-[#B6B0A8] leading-relaxed pl-8">
                            Restart your Mac normally. SIP will now be turned off.
                        </p>
                    </div>
                </div>

                <!-- How to Check Status & Re-enable -->
                <div class="mt-8 pt-6 border-t border-[#262019] space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">How to Check Current SIP Status</h3>
                    <p class="text-xs text-[#8C847A]">
                        Open standard Terminal inside macOS (no need for Recovery mode) and run:
                    </p>
                    <div class="bg-[#12100E] border border-[#2E2720] rounded-xl p-3.5 font-mono text-xs text-white flex items-center justify-between">
                        <code class="text-warning font-bold">csrutil status</code>
                        <button type="button" @click="navigator.clipboard.writeText('csrutil status'); copied = 'status'; setTimeout(() => copied = '', 2000)"
                                class="px-3 py-1 rounded bg-[#25201A] hover:bg-[#302821] text-[#A8A199] hover:text-white text-xs font-sans transition-colors cursor-pointer">
                            <span x-text="copied === 'status' ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>

                    <div class="p-4 rounded-xl bg-primary/10 border border-primary/25 mt-4">
                        <h4 class="text-xs font-bold text-white mb-1">To Re-enable SIP in the future:</h4>
                        <p class="text-xs text-[#B6B0A8]">
                            Follow the same steps to enter Recovery Terminal and run <code class="text-success font-bold">csrutil enable</code>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
