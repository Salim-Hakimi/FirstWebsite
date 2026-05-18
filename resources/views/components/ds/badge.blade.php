@props([
    'tone' => 'default',
])

<span {{ $attributes->merge(['class' => 'ds-badge ds-badge--'.$tone]) }}>
    {{ $slot }}
</span>
