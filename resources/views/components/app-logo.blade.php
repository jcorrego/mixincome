@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <img src="/images/logo-color.svg" alt="MixIncome" class="h-6 dark:hidden" />
        <img src="/images/logo-white.svg" alt="MixIncome" class="hidden h-6 dark:block" />
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <img src="/images/logo-color.svg" alt="MixIncome" class="h-6 dark:hidden" />
        <img src="/images/logo-white.svg" alt="MixIncome" class="hidden h-6 dark:block" />
    </flux:brand>
@endif
