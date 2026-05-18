@props([
    'label',
    'value',
    'subtitle' => null,
    'tone' => 'default',
    'icon' => null,
])

<article {{ $attributes->merge(['class' => 'ds-stat ds-stat--'.$tone]) }}>
    @if ($icon)
        <span class="ds-stat__icon" aria-hidden="true">{{ $icon }}</span>
    @endif

    <span class="ds-stat__label">{{ $label }}</span>
    <strong class="ds-stat__value">{{ $value }}</strong>

    @if ($subtitle)
        <p class="ds-stat__subtitle">{{ $subtitle }}</p>
    @endif
</article>
