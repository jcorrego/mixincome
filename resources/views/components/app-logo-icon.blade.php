@props([
    'animate' => true, // Whether to animate the rings
])

<x-animated-logo
    version="color"
    size="small"
    :animate="$animate"
    {{ $attributes->merge(['class' => 'dark:hidden']) }}
/>
<x-animated-logo
    version="white"
    size="small"
    :animate="$animate"
    {{ $attributes->merge(['class' => 'hidden dark:block']) }}
/>
