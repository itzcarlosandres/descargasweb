<x-app-layout>
    @section('title', 'Terms and Conditions - ' . config('app.name'))
    @section('description', 'Terms and conditions for using ' . config('app.name'))

    <div class="container-app pt-4 sm:pt-6 pb-16">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-[#1A1713] border border-[#2B251E] rounded-2xl p-6 sm:p-9 shadow-2xl space-y-5 text-xs sm:text-sm text-[#B6B0A8] leading-relaxed">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Terms and Conditions</h1>
                <p>
                    By accessing the website at <a href="{{ route('home') }}" class="text-primary hover:underline">{{ config('app.name') }}</a>, you agree to be bound by these terms of service, all applicable laws and regulations.
                </p>
                <h2 class="text-base font-bold text-white pt-2">Use License</h2>
                <p>
                    Permission is granted to temporarily download the materials on {{ config('app.name') }}'s website for personal, non-commercial transitory viewing only.
                </p>
                <h2 class="text-base font-bold text-white pt-2">Disclaimer</h2>
                <p>
                    The materials on {{ config('app.name') }}'s website are provided on an 'as is' basis. We make no warranties, expressed or implied, and hereby disclaim all other warranties including merchantability and fitness for a particular purpose.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
