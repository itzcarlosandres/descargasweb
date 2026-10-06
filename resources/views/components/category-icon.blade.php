@props(['icon' => null, 'name' => null, 'class' => 'w-5 h-5'])

@php
    $key = strtolower(trim($icon ?? $name ?? ''));
    $isSvg = str_starts_with($key, '<svg');
    $isEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $icon ?? '');
@endphp

@if($isSvg)
    {!! $icon !!}
@elseif($isEmoji)
    <span class="text-base leading-none select-none">{{ $icon }}</span>
@else
    @switch($key)
        @case('app')
        @case('applications')
        @case('apps')
            <x-lucide-layout-grid class="{{ $class }}" />
            @break

        @case('gamepad')
        @case('games')
        @case('juegos')
            <x-lucide-gamepad-2 class="{{ $class }}" />
            @break

        @case('palette')
        @case('media-design')
        @case('design')
        @case('creative')
            <x-lucide-palette class="{{ $class }}" />
            @break

        @case('wrench')
        @case('system-utilities')
        @case('utilities')
        @case('tools')
            <x-lucide-wrench class="{{ $class }}" />
            @break

        @case('briefcase')
        @case('productivity-business')
        @case('productivity')
        @case('business')
            <x-lucide-briefcase class="{{ $class }}" />
            @break

        @case('code')
        @case('developer-tools')
        @case('developer')
        @case('dev')
            <x-lucide-code-2 class="{{ $class }}" />
            @break

        @case('globe')
        @case('internet')
        @case('network')
            <x-lucide-globe class="{{ $class }}" />
            @break

        @case('book')
        @case('education')
            <x-lucide-book-open class="{{ $class }}" />
            @break

        @case('shield')
        @case('security')
            <x-lucide-shield-check class="{{ $class }}" />
            @break

        @case('image')
        @case('graphics')
            <x-lucide-image class="{{ $class }}" />
            @break

        @case('music')
        @case('audio')
            <x-lucide-music class="{{ $class }}" />
            @break

        @case('video')
        @case('multimedia')
            <x-lucide-video class="{{ $class }}" />
            @break

        @case('terminal')
            <x-lucide-terminal class="{{ $class }}" />
            @break

        @case('cpu')
        @case('hardware')
            <x-lucide-cpu class="{{ $class }}" />
            @break

        @default
            <x-lucide-tag class="{{ $class }}" />
    @endswitch
@endif
