<x-app-layout>
    @section('title', 'DMCA Copyright Policy - ' . config('app.name'))
    @section('description', 'Digital Millennium Copyright Act (DMCA) Notice & Policy for ' . config('app.name'))

    <div class="container-app pt-4 sm:pt-6 pb-16">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-[#1A1713] border border-[#2B251E] rounded-2xl p-6 sm:p-9 shadow-2xl space-y-5 text-xs sm:text-sm text-[#B6B0A8] leading-relaxed">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/15 border border-primary/25 text-primary text-xs font-bold mb-1">
                    <span>Legal Notice</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    DMCA Copyright Policy
                </h1>

                <p>
                    <strong>{{ config('app.name') }}</strong> respects the intellectual property rights of others and complies with the Digital Millennium Copyright Act (DMCA). It is our policy to respond promptly to clear notices of alleged copyright infringement.
                </p>

                <h2 class="text-base font-bold text-white pt-2">Notice and Take-Down Procedure</h2>
                <p>
                    None of the media, files, or applications are hosted directly on our servers. All links and content are indexed from third-party publicly accessible services. If you believe your copyrighted material is being linked improperly:
                </p>

                <ol class="list-decimal pl-5 space-y-2 text-[#C4BEB7]">
                    <li>Identify the copyrighted work that you claim has been infringed.</li>
                    <li>Provide the exact URL(s) of the infringing content on {{ config('app.name') }}.</li>
                    <li>Provide your contact information (Full name, address, telephone number, and email).</li>
                    <li>Include a statement of good faith belief that the use is not authorized by the copyright owner.</li>
                    <li>Provide a physical or electronic signature of a person authorized to act on behalf of the owner.</li>
                </ol>

                <div class="p-4 rounded-xl bg-[#14120F] border border-[#262019] mt-4">
                    <p class="text-white font-bold text-xs mb-1">Send Take-Down Notices To:</p>
                    <p class="text-[#8C847A] text-xs">Email: <a href="mailto:dmca@hackmac.cc" class="text-primary hover:underline">dmca@hackmac.cc</a> or use our <a href="{{ route('contact') }}" class="text-primary hover:underline">Contact Form</a>.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
