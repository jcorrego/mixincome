@props([
    'version' => 'color', // 'color' or 'white'
    'size' => 'default', // 'small', 'default', 'large'
    'animate' => true, // Whether to animate the rings
    'loading' => false, // Whether to show as loading animation (2s duration)
])

@php
    $sizeClasses = match($size) {
        'small' => 'w-8 h-8',
        'default' => 'w-10 h-10',
        'large' => 'w-16 h-16'
    };

    $fontSize = match($size) {
        'small' => 'text-lg',
        'default' => 'text-2xl',
        'large' => 'text-4xl'
    };

    $ringWidth = match($size) {
        'small' => 2,
        'default' => 2,
        'large' => 4
    };

    $innerRingWidth = match($size) {
        'small' => 1,
        'default' => 1,
        'large' => 2
    };

    $innerInset = match($size) {
        'small' => '3px',
        'default' => '4px',
        'large' => '8px'
    };

    $outerAnimation = !$animate ? 'none' : ($loading ? 'rotate 2s linear' : 'rotate 4s linear infinite');
    $innerAnimation = !$animate ? 'none' : ($loading ? 'rotate-reverse 2s linear' : 'rotate-reverse 3s linear infinite');

    $outerTopColor = $version === 'white' ? '#ffffff' : '#0f62fe';
    $outerRightColor = $version === 'white' ? '#ffffff' : '#00d4aa';
    $innerBottomColor = $version === 'white' ? 'rgba(255,255,255,0.5)' : '#ff6b6b';
    $innerLeftColor = $version === 'white' ? 'rgba(255,255,255,0.5)' : '#00d4aa';
@endphp

<div {{ $attributes->merge(['class' => "relative {$sizeClasses} shrink-0"]) }} role="img" aria-label="MixIncome Logo">
    {{-- Outer rotating ring --}}
    <div class="absolute inset-0 rounded-full"
         style="border: {{ $ringWidth }}px solid transparent; border-top-color: {{ $outerTopColor }}; border-right-color: {{ $outerRightColor }}; animation: {{ $outerAnimation }};"
         aria-hidden="true">
    </div>

    {{-- Inner counter-rotating ring --}}
    <div class="absolute rounded-full opacity-60"
         style="inset: {{ $innerInset }}; border: {{ $innerRingWidth }}px solid transparent; border-bottom-color: {{ $innerBottomColor }}; border-left-color: {{ $innerLeftColor }}; animation: {{ $innerAnimation }};"
         aria-hidden="true">
    </div>

    {{-- Center M --}}
    <div class="absolute inset-0 flex items-center justify-center font-extrabold font-display {{ $fontSize }}"
         @if($version === 'white')
             style="color: #ffffff;"
         @else
             style="background: linear-gradient(135deg, #00d4aa 0%, #0f62fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"
         @endif
         aria-hidden="true">
        M
    </div>
</div>
