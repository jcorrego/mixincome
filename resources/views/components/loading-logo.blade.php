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
    
    $fontSize = match($size) {
        'small' => 'text-xl',
        'default' => 'text-3xl',
        'large' => 'text-5xl'
    };
@endphp

<div {{ $attributes->merge(['class' => "fixed inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm z-50"]) }}
     style="animation: fadeOut {{ $duration }}s ease-in-out forwards;">
    
    <div class="flex flex-col items-center gap-4">
        <!-- Animated Logo -->
        <div class="relative {{ $sizeClasses }} flex-shrink-0">
            <!-- Outer rotating ring -->
            <div class="absolute inset-0 rounded-full border-transparent"
                 style="border-width: 4px; 
                        border-top-color: {{ $version === 'white' ? '#ffffff' : '#0f62fe' }}; 
                        border-right-color: {{ $version === 'white' ? '#ffffff' : '#00d4aa' }};
                        animation: rotate {{ $duration }}s ease-out;">
            </div>
            
            <!-- Inner rotating ring -->
            <div class="absolute inset-2 rounded-full border-transparent opacity-60"
                 style="border-width: 3px;
                        border-bottom-color: {{ $version === 'white' ? 'rgba(255,255,255,0.5)' : '#ff6b6b' }}; 
                        border-left-color: {{ $version === 'white' ? 'rgba(255,255,255,0.5)' : '#00d4aa' }};
                        animation: rotate-reverse {{ $duration }}s ease-out;">
            </div>
            
            <!-- Center M letter -->
            <div class="absolute inset-0 flex items-center justify-center font-bold font-['Outfit'] {{ $fontSize }}"
                 style="color: {{ $version === 'white' ? '#ffffff' : '#0f62fe' }}; 
                        background: {{ $version === 'white' ? '#ffffff' : 'linear-gradient(135deg, #00d4aa 0%, #0f62fe 100%)' }};
                        {{ $version !== 'white' ? '-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;' : '' }}">
                M
            </div>
        </div>
        
        <!-- Loading Text -->
        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900 dark:text-white font-['Outfit']">
                MixIncome
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Cargando...
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        75% { transform: rotate(360deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes rotate-reverse {
        0% { transform: rotate(360deg); }
        75% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }
    
    @keyframes fadeOut {
        0% { opacity: 1; }
        75% { opacity: 1; }
        100% { opacity: 0; pointer-events: none; }
    }
</style>

<script>
    // Auto-remove the loading component after animation completes
    document.addEventListener('DOMContentLoaded', function() {
        const loadingElement = document.querySelector('[data-loading-logo]');
        if (loadingElement) {
            setTimeout(() => {
                loadingElement.remove();
            }, {{ $duration * 1000 }});
        }
    });
</script>