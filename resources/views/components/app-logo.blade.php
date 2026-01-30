@props([
    'sidebar' => false,
    'animate' => true, // Whether to use animated or static logo
])

<a href="{{ route('dashboard') }}" {{ $attributes->class('flex items-center gap-3') }} wire:navigate>
    <x-animated-logo
        version="color"
        size="default"
        :animate="$animate"
        class="dark:hidden"
    />
    <x-animated-logo
        version="white"
        size="default"
        :animate="$animate"
        class="hidden dark:block"
    />

    @if($sidebar)
        <span class="text-xl font-bold font-display bg-linear-to-br from-[#00d4aa] to-[#0f62fe] bg-clip-text text-transparent dark:bg-none dark:text-white">MixIncome</span>
    @endif
</a>
