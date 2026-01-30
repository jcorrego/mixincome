@props([
    'sidebar' => false,
])

<a href="{{ route('dashboard') }}" {{ $attributes->class('flex items-center') }} wire:navigate>
    <img src="/images/logo-color.svg" alt="MixIncome" class="h-8 dark:hidden" />
    <img src="/images/logo-white.svg" alt="MixIncome" class="hidden h-8 dark:block" />
</a>
