@props([
    'version' => 'color', // 'color' or 'white'
    'size' => 'default', // 'small', 'default', 'large'
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
        'small' => '2',
        'default' => '3',
        'large' => '4'
    };
    
    $innerInset = match($size) {
        'small' => 'inset-1',
        'default' => 'inset-1.5',
        'large' => 'inset-2'
    };
    
    $animationDuration = $loading ? 'animate-[rotate_2s_linear]' : 'animate-[rotate_4s_linear_infinite]';
    $animationDurationInner = $loading ? 'animate-[rotate-reverse_2s_linear]' : 'animate-[rotate-reverse_3s_linear_infinite]';
@endphp

<div {{ $attributes->merge(['class' => "relative {$sizeClasses} flex-shrink-0"]) }}>
    <!-- Outer rotating ring -->
    <div class="absolute inset-0 rounded-full border-transparent {{ $animationDuration }}"
         style="border-width: {{ $ringWidth }}px; 
                border-top-color: {{ $version === 'white' ? '#ffffff' : '#0f62fe' }}; 
                border-right-color: {{ $version === 'white' ? '#ffffff' : '#00d4aa' }};">
    </div>
    
    <!-- Inner rotating ring -->
    <div class="absolute {{ $innerInset }} rounded-full border-transparent opacity-60 {{ $animationDurationInner }}"
         style="border-width: {{ max(1, intval($ringWidth) - 1) }}px;
                border-bottom-color: {{ $version === 'white' ? 'rgba(255,255,255,0.5)' : '#ff6b6b' }}; 
                border-left-color: {{ $version === 'white' ? 'rgba(255,255,255,0.5)' : '#00d4aa' }};">
    </div>
    
    <!-- Center M letter -->
    <div class="absolute inset-0 flex items-center justify-center font-bold font-['Outfit'] {{ $fontSize }}"
         style="color: {{ $version === 'white' ? '#ffffff' : '#0f62fe' }}; 
                background: {{ $version === 'white' ? '#ffffff' : 'linear-gradient(135deg, #00d4aa 0%, #0f62fe 100%)' }};
                {{ $version !== 'white' ? '-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;' : '' }}">
        M
    </div>
</div>

<style>
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes rotate-reverse {
        from { transform: rotate(360deg); }
        to { transform: rotate(0deg); }
    }
</style>