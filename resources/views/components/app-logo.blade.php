@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="MixIncome" logo="/images/icon-dark.png" logo:dark="/images/icon-white.png" {{ $attributes }} />
@else
    <flux:brand name="MixIncome" logo="/images/icon-dark.png" logo:dark="/images/icon-white.png" {{ $attributes }} />
@endif
