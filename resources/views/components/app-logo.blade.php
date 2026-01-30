@props([
    'sidebar' => false,
    'animate' => true, // Whether to use animated or static logo
])

<a href="{{ route('dashboard') }}" {{ $attributes->class('flex items-center gap-3') }} wire:navigate>
    @if($animate)
        <x-animated-logo 
            version="color"
            size="default"
            class="dark:hidden"
        />
        <x-animated-logo 
            version="white"
            size="default"
            class="hidden dark:block"
        />
    @else
        <img src="/images/logo-color.svg" alt="MixIncome" class="h-10 dark:hidden" />
        <img src="/images/logo-white.svg" alt="MixIncome" class="hidden h-10 dark:block" />
    @endif
    
    @if($sidebar)
        <span class="text-xl font-bold text-gray-900 dark:text-white font-['Outfit']">MixIncome</span>
    @endif
</a>
