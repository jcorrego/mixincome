@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="MixIncome" logo="/images/icon-dark.svg" logo:dark="/images/icon-white.svg" {{ $attributes }} />
@else
    <flux:brand name="MixIncome" logo="/images/icon-dark.svg" logo:dark="/images/icon-white.svg" {{ $attributes }} />
@endif
