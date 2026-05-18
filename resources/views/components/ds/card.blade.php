@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
])

<section {{ $attributes->merge(['class' => 'ds-card']) }}>
    @if ($title || $subtitle || $actions)
        <div class="ds-card__header">
            <div class="ds-card__heading">
                @if ($title)
                    <h2 class="ds-card__title">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="ds-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if ($actions)
                <div class="ds-card__actions">{{ $actions }}</div>
            @endif
        </div>
    @endif

    <div class="ds-card__body">
        {{ $slot }}
    </div>
</section>
