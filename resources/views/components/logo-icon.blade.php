@props([
    'icon' => 'finder',
    'class' => 'w-[30px] h-[30px] flex-shrink-0',
])

<div {{ $attributes->merge(['class' => $class]) }}>
    @if($icon === 'apple')
        <div class="w-full h-full rounded-[7px] bg-[#F5F5F7] dark:bg-[#242426] border border-[#E5E7EB] dark:border-[#333336] text-[#1D1D1F] dark:text-white flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 170 170" aria-label="Apple">
                <path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.7-3.07-7.73-7.91-12.1-14.53-5.7-8.68-10.15-18.59-13.34-29.74-3.19-11.15-4.78-21.72-4.78-31.7 0-14.35 3.65-26.15 10.96-35.4 7.3-9.25 16.5-13.99 27.6-14.22 4.9.13 10.33 1.34 16.29 3.65 5.96 2.3 9.77 3.55 11.43 3.74 2.12-.39 6.07-1.74 11.86-4.05 5.79-2.31 11.16-3.37 16.12-3.18 12.39.64 22.39 5.25 29.98 13.84-10.88 6.59-16.22 15.68-16.03 27.27.2 9.24 3.73 17.06 10.61 23.47 6.88 6.41 15.02 10.12 24.42 11.14-2.12 6.53-4.8 13.1-8.04 19.7zM119.22 31.86c0-7.29 2.59-14.12 7.77-20.48 5.18-6.36 11.75-10.45 19.72-12.28.32 1.63.48 3.05.48 4.26 0 7.31-2.73 14.15-8.19 20.52-5.46 6.37-12.03 10.37-19.72 12-0.02-1.34-.06-2.67-.06-4.02z"/>
            </svg>
        </div>
    @elseif($icon === 'command')
        <div class="w-full h-full rounded-[7px] bg-[#EFF6FF] dark:bg-gradient-to-tr dark:from-[#242426] dark:to-[#161617] border border-[#BFDBFE] dark:border-[#333336] text-[#0071E3] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M18 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3H6a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3V6a3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/>
            </svg>
        </div>
    @elseif($icon === 'terminal')
        <div class="w-full h-full rounded-[7px] bg-[#F5EFEB] dark:bg-[#12100E] border border-[#E5DFD7] dark:border-[#3A3025] text-[#16A34A] dark:text-[#27C93F] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/>
            </svg>
        </div>
    @elseif($icon === 'cpu')
        <div class="w-full h-full rounded-[7px] bg-[#EFF6FF] dark:bg-[#0B1528] border border-[#BFDBFE] dark:border-[#1E3A8A] text-[#2563EB] dark:text-[#38BDF8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>
            </svg>
        </div>
    @elseif($icon === 'download')
        <div class="w-full h-full rounded-[7px] bg-[#ECFDF5] dark:bg-[#06281E] border border-[#A7F3D0] dark:border-[#065F46] text-[#059669] dark:text-[#34D399] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
            </svg>
        </div>
    @elseif($icon === 'cloud-download')
        <div class="w-full h-full rounded-[7px] bg-[#F0F9FF] dark:bg-[#082032] border border-[#BAE6FD] dark:border-[#0C4A6E] text-[#0284C7] dark:text-[#38BDF8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m8 17 4 4 4-4"/>
            </svg>
        </div>
    @elseif($icon === 'sparkles')
        <div class="w-full h-full rounded-[7px] bg-[#FFFBEB] dark:bg-[#281A05] border border-[#FDE68A] dark:border-[#78350F] text-[#D97706] dark:text-[#FBBF24] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>
            </svg>
        </div>
    @elseif($icon === 'app-window')
        <div class="w-full h-full rounded-[7px] bg-[#EEF2FF] dark:bg-[#131738] border border-[#C7D2FE] dark:border-[#312E81] text-[#4F46E5] dark:text-[#818CF8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 4v4"/><path d="M2 8h20"/><path d="M6 4v4"/>
            </svg>
        </div>
    @elseif($icon === 'laptop')
        <div class="w-full h-full rounded-[7px] bg-[#F8FAFC] dark:bg-[#121824] border border-[#E2E8F0] dark:border-[#1E293B] text-[#475569] dark:text-[#94A3B8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"/>
            </svg>
        </div>
    @elseif($icon === 'shield-check')
        <div class="w-full h-full rounded-[7px] bg-[#F0FDF4] dark:bg-[#072414] border border-[#BBF7D0] dark:border-[#14532D] text-[#16A34A] dark:text-[#4ADE80] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
            </svg>
        </div>
    @elseif($icon === 'rocket')
        <div class="w-full h-full rounded-[7px] bg-[#FFF1F2] dark:bg-[#2C0B12] border border-[#FECDD3] dark:border-[#881337] text-[#E11D48] dark:text-[#FB7185] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
            </svg>
        </div>
    @elseif($icon === 'package')
        <div class="w-full h-full rounded-[7px] bg-[#FFFBEB] dark:bg-[#2A1D08] border border-[#FDE68A] dark:border-[#78350F] text-[#B45309] dark:text-[#FBBF24] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
            </svg>
        </div>
    @elseif($icon === 'hard-drive')
        <div class="w-full h-full rounded-[7px] bg-[#ECFEFF] dark:bg-[#062328] border border-[#A5F3FC] dark:border-[#155E75] text-[#0891B2] dark:text-[#22D3EE] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="22" x2="2" y1="12" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" x2="6.01" y1="16" y2="16"/><line x1="10" x2="10.01" y1="16" y2="16"/>
            </svg>
        </div>
    @elseif($icon === 'disc')
        <div class="w-full h-full rounded-[7px] bg-[#F1F5F9] dark:bg-[#151D2A] border border-[#CBD5E1] dark:border-[#334155] text-[#475569] dark:text-[#94A3B8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="2"/>
            </svg>
        </div>
    @elseif($icon === 'code')
        <div class="w-full h-full rounded-[7px] bg-[#ECFDF5] dark:bg-[#06281E] border border-[#A7F3D0] dark:border-[#065F46] text-[#059669] dark:text-[#34D399] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
            </svg>
        </div>
    @elseif($icon === 'palette')
        <div class="w-full h-full rounded-[7px] bg-[#FDF2F8] dark:bg-[#2C0B20] border border-[#FBCFE8] dark:border-[#831843] text-[#DB2777] dark:text-[#F472B6] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
            </svg>
        </div>
    @elseif($icon === 'music')
        <div class="w-full h-full rounded-[7px] bg-[#F5F3FF] dark:bg-[#1B0F33] border border-[#DDD6FE] dark:border-[#5B21B6] text-[#8B5CF6] dark:text-[#C084FC] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
            </svg>
        </div>
    @elseif($icon === 'film')
        <div class="w-full h-full rounded-[7px] bg-[#FFF7ED] dark:bg-[#2C1409] border border-[#FFEDD5] dark:border-[#7C2D12] text-[#F97316] dark:text-[#FB923C] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 3v18"/><path d="M3 7.5h4"/><path d="M3 12h18"/><path d="M3 16.5h4"/><path d="M17 3v18"/><path d="M17 7.5h4"/><path d="M17 16.5h4"/>
            </svg>
        </div>
    @elseif($icon === 'globe')
        <div class="w-full h-full rounded-[7px] bg-[#EFF6FF] dark:bg-[#0C1E3C] border border-[#BFDBFE] dark:border-[#1E40AF] text-[#2563EB] dark:text-[#60A5FA] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>
            </svg>
        </div>
    @elseif($icon === 'compass')
        <div class="w-full h-full rounded-[7px] bg-[#FEF2F2] dark:bg-[#2B0E14] border border-[#FECACA] dark:border-[#7F1D1D] text-[#EF4444] dark:text-[#F87171] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
            </svg>
        </div>
    @elseif($icon === 'wrench')
        <div class="w-full h-full rounded-[7px] bg-[#F4F4F5] dark:bg-[#18181B] border border-[#E4E4E7] dark:border-[#3F3F46] text-[#71717A] dark:text-[#A1A1AA] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>
    @elseif($icon === 'database')
        <div class="w-full h-full rounded-[7px] bg-[#EEF2FF] dark:bg-[#121638] border border-[#C7D2FE] dark:border-[#312E81] text-[#6366F1] dark:text-[#818CF8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>
            </svg>
        </div>
    @elseif($icon === 'key')
        <div class="w-full h-full rounded-[7px] bg-[#FEFCE8] dark:bg-[#292206] border border-[#FEF08A] dark:border-[#713F12] text-[#CA8A04] dark:text-[#FACC15] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L21 8"/>
            </svg>
        </div>
    @elseif($icon === 'flame')
        <div class="w-full h-full rounded-[7px] bg-[#FFF7ED] dark:bg-[#2C1409] border border-[#FFEDD5] dark:border-[#7C2D12] text-[#EA580C] dark:text-[#FB923C] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
            </svg>
        </div>
    @elseif($icon === 'gamepad')
        <div class="w-full h-full rounded-[7px] bg-[#F5F3FF] dark:bg-[#1B0F33] border border-[#DDD6FE] dark:border-[#5B21B6] text-[#7C3AED] dark:text-[#A78BFA] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="6" x2="10" y1="12" y2="12"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="15" x2="15.01" y1="13" y2="13"/><line x1="18" x2="18.01" y1="11" y2="11"/><rect width="20" height="12" x="2" y="6" rx="6"/>
            </svg>
        </div>
    @elseif($icon === 'zap')
        <div class="w-full h-full rounded-[7px] bg-[#FEFCE8] dark:bg-[#292206] border border-[#FEF08A] dark:border-[#713F12] text-[#CA8A04] dark:text-[#FACC15] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </div>
    @elseif($icon === 'layers')
        <div class="w-full h-full rounded-[7px] bg-[#F0F9FF] dark:bg-[#0C2233] border border-[#BAE6FD] dark:border-[#075985] text-[#0284C7] dark:text-[#38BDF8] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
            </svg>
        </div>
    @elseif($icon === 'folder')
        <div class="w-full h-full rounded-[7px] bg-[#FFFBEB] dark:bg-[#251A07] border border-[#FDE68A] dark:border-[#78350F] text-[#D97706] dark:text-[#FBBF24] flex items-center justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
            </svg>
        </div>
    @else
        <!-- Exact macOS Finder Squircle Icon -->
        <svg class="w-full h-full rounded-[7px] shadow-sm flex-shrink-0" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="finder-blue-default" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#3FA9F5" />
                    <stop offset="100%" stop-color="#1B72E8" />
                </linearGradient>
                <linearGradient id="finder-white-default" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#FFFFFF" />
                    <stop offset="100%" stop-color="#E2E8F0" />
                </linearGradient>
                <clipPath id="finder-squircle-default">
                    <rect width="100" height="100" rx="20" ry="20" />
                </clipPath>
            </defs>
            <g clip-path="url(#finder-squircle-default)">
                <rect width="100" height="100" fill="url(#finder-white-default)" />
                <path d="M0 0 H50 C46 32 37 43 47 50 C54 55 49 68 49 100 H0 Z" fill="url(#finder-blue-default)" />
                <ellipse cx="28" cy="38" rx="4.5" ry="7.5" fill="#0F172A" />
                <ellipse cx="72" cy="38" rx="4.5" ry="7.5" fill="#0F172A" />
                <path d="M28 66 C38 80 62 80 72 66" stroke="#0F172A" stroke-width="5" stroke-linecap="round" fill="none" />
                <path d="M47 50 C44 54 44 56 46 58" stroke="#0F172A" stroke-width="4.5" stroke-linecap="round" fill="none" />
            </g>
        </svg>
    @endif
</div>
