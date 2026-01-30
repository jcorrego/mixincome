@props([
    'animate' => true, // Whether to use animated or static icon
])

@if($animate)
    <x-animated-logo 
        version="color"
        size="small"
        {{ $attributes->merge(['class' => 'dark:hidden']) }}
    />
    <x-animated-logo 
        version="white"
        size="small"
        {{ $attributes->merge(['class' => 'hidden dark:block']) }}
    />
@else
    <img src="/images/icon-color.svg" alt="MixIncome" {{ $attributes->merge(['class' => 'dark:hidden']) }} />
    <img src="/images/icon-white.svg" alt="MixIncome" {{ $attributes->merge(['class' => 'hidden dark:block']) }} />
@endif
