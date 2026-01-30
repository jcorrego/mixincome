@props([
    'version' => 'color', // 'color' or 'white'
    'size' => 'large', // Size for loading overlay
    'duration' => 2, // Duration in seconds
])

@php
    $sizeClasses = match($size) {
        'small' => 'w-12 h-12',
        'default' => 'w-16 h-16',
        'large' => 'w-24 h-24'
    };

    $ringWidth = match($size) {
        'small' => 3,
        'default' => 4,
        'large' => 5
    };

    $innerRingWidth = match($size) {
        'small' => 2,
        'default' => 3,
        'large' => 3
    };

    $innerInset = match($size) {
        'small' => '4px',
        'default' => '6px',
        'large' => '10px'
    };

    $fontSize = match($size) {
        'small' => 'text-xl',
        'default' => 'text-3xl',
        'large' => 'text-5xl'
    };

    $outerTopColor = $version === 'white' ? '#ffffff' : '#0f62fe';
    $outerRightColor = $version === 'white' ? '#ffffff' : '#00d4aa';
    $innerBottomColor = $version === 'white' ? 'rgba(255,255,255,0.5)' : '#ff6b6b';
    $innerLeftColor = $version === 'white' ? 'rgba(255,255,255,0.5)' : '#00d4aa';
@endphp

<div {{ $attributes->merge(['class' => "fixed inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm z-50"]) }}
     data-loading-logo
     role="status"
     aria-live="polite"
     aria-label="{{ __('Loading...') }}"
     style="animation: fadeOut {{ $duration }}s ease-in-out forwards;"
     x-data
     x-init="setTimeout(() => $el.remove(), {{ $duration * 1000 }})">

    <div class="flex flex-col items-center gap-4">
        {{-- Animated Logo --}}
        <div class="relative {{ $sizeClasses }} shrink-0" aria-hidden="true">
            {{-- Outer rotating ring --}}
            <div class="absolute inset-0 rounded-full"
                 style="border: {{ $ringWidth }}px solid transparent; border-top-color: {{ $outerTopColor }}; border-right-color: {{ $outerRightColor }}; animation: rotate {{ $duration }}s ease-out;">
            </div>

            {{-- Inner counter-rotating ring --}}
            <div class="absolute rounded-full opacity-60"
                 style="inset: {{ $innerInset }}; border: {{ $innerRingWidth }}px solid transparent; border-bottom-color: {{ $innerBottomColor }}; border-left-color: {{ $innerLeftColor }}; animation: rotate-reverse {{ $duration }}s ease-out;">
            </div>

            {{-- Center M --}}
            <div class="absolute inset-0 flex items-center justify-center font-extrabold font-display {{ $fontSize }}"
                 @if($version === 'white')
                     style="color: #ffffff;"
                 @else
                     style="background: linear-gradient(135deg, #00d4aa 0%, #0f62fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"
                 @endif>
                M
            </div>
        </div>

        {{-- Loading Text --}}
        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900 dark:text-white font-display">
                MixIncome
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Loading...') }}
            </div>
        </div>
    </div>
</div>
