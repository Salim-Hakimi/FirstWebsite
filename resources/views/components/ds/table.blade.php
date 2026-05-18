@props([
    'caption' => null,
])

<div {{ $attributes->merge(['class' => 'ds-table-wrap']) }}>
    <table class="ds-table">
        @if ($caption)
            <caption>{{ $caption }}</caption>
        @endif

        {{ $slot }}
    </table>
</div>
