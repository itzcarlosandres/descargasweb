<x-app-layout>
    @section('title', 'Privacy Policy - ' . config('app.name'))
    @section('description', 'Privacy policy for ' . config('app.name'))

    <div class="container-app pt-4 sm:pt-6 pb-16">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-[#1A1713] border border-[#2B251E] rounded-2xl p-6 sm:p-9 shadow-2xl space-y-5 text-xs sm:text-sm text-[#B6B0A8] leading-relaxed">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Privacy Policy</h1>
                <p>Last updated: October 2026</p>
                <p>
                    Your privacy is important to us. It is {{ config('app.name') }}'s policy to respect your privacy regarding any information we may collect from you across our website.
                </p>
                <h2 class="text-base font-bold text-white pt-2">Information We Collect</h2>
                <p>
                    We do not require account registration to search or download free files. When you submit a comment or contact us, we collect only the name and email address you provide voluntarily.
                </p>
                <h2 class="text-base font-bold text-white pt-2">Cookies &amp; Local Storage</h2>
                <p>
                    We use cookies and localStorage strictly to remember your preferences (such as your preferred dark theme and comment interaction state). We do not track personal identifying information across external websites.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
