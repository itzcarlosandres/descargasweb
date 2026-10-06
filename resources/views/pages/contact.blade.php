<x-app-layout>
    @section('title', 'Contact Us - ' . config('app.name'))
    @section('description', 'Get in touch with the HackMac.cc team for support, broken link reports, DMCA, and app requests.')

    <div class="container-app pt-4 sm:pt-6 pb-16">
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/15 border border-primary/25 text-primary text-xs font-bold mb-1">
                    <span>Contact &amp; Support</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Get in Touch</h1>
                <p class="text-xs sm:text-sm text-[#8C847A] max-w-md mx-auto">
                    Have a question, want to request an app, or found a broken download link? Let us know below.
                </p>
            </div>

            @if(session('contact_success'))
            <div class="p-4 rounded-2xl bg-success/15 border border-success/30 text-success text-xs flex items-center gap-2.5 shadow-lg">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('contact_success') }}</span>
            </div>
            @endif

            <!-- Contact Form Card -->
            <div class="bg-[#1A1713] border border-[#2B251E] rounded-2xl p-6 sm:p-8 shadow-2xl">
                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#8C847A] uppercase tracking-wider mb-1">Your Name *</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required
                                   class="w-full bg-[#13110E] border border-[#2E2822] focus:border-primary rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder-[#6B645C] focus:outline-none transition-colors"
                                   placeholder="John Doe">
                            @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#8C847A] uppercase tracking-wider mb-1">Your Email *</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required
                                   class="w-full bg-[#13110E] border border-[#2E2822] focus:border-primary rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder-[#6B645C] focus:outline-none transition-colors"
                                   placeholder="john@example.com">
                            @error('email')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#8C847A] uppercase tracking-wider mb-1">Subject / Category *</label>
                        <select name="subject" required
                                class="w-full bg-[#13110E] border border-[#2E2822] focus:border-primary rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none transition-colors cursor-pointer">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Report Broken Link">Report Broken Download Link</option>
                            <option value="Request Application">Request an Application or Game</option>
                            <option value="DMCA & Copyright">DMCA &amp; Copyright Notice</option>
                            <option value="Bug Report">Technical Bug Report</option>
                        </select>
                        @error('subject')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#8C847A] uppercase tracking-wider mb-1">Your Message *</label>
                        <textarea name="message" rows="5" required
                                  placeholder="Please provide as much detail as possible..."
                                  class="w-full bg-[#13110E] border border-[#2E2822] focus:border-primary rounded-xl p-3.5 text-xs sm:text-sm text-white placeholder-[#6B645C] focus:outline-none resize-none transition-colors">{{ old('message') }}</textarea>
                        @error('message')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-full bg-primary hover:bg-[#ea580c] text-white text-xs sm:text-sm font-bold shadow-lg shadow-primary/25 cursor-pointer transition-all hover:scale-[1.01]">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
