@props([
    'name',
    'label' => null,
])

@php
    $icons = [
        'bed' => 'bed.svg',
        'bell' => 'bell.svg',
        'book' => 'book.svg',
        'books' => 'books.svg',
        'building' => 'building.svg',
        'calendar' => 'calendar-clock.svg',
        'cash' => 'cash.svg',
        'cash-minus' => 'cash-banknote-minus.svg',
        'chart' => 'chart-bar.svg',
        'dashboard' => 'dashboard.svg',
        'edit' => 'edit.svg',
        'eye' => 'eye.svg',
        'filter' => 'filter.svg',
        'home' => 'home.svg',
        'library' => 'library.svg',
        'logout' => 'logout.svg',
        'logs' => 'logs.svg',
        'moon' => 'moon.svg',
        'plus' => 'plus.svg',
        'report' => 'report-analytics.svg',
        'search' => 'search.svg',
        'settings' => 'settings.svg',
        'shield' => 'shield.svg',
        'sun' => 'sun.svg',
        'trash' => 'trash.svg',
        'user' => 'user.svg',
        'users' => 'users.svg',
        'service' => 'hotel-service.svg',
    ];

    $file = $icons[$name] ?? $name;
    $path = public_path('icons/'.$file);
    $svg = is_file($path) ? file_get_contents($path) : '';
    $class = trim('ds-icon '.$attributes->get('class', ''));
    $attributeHtml = $attributes->except('class')->toHtml();
    $extraAttributes = $label ? ' role="img" aria-label="'.e($label).'"' : ' aria-hidden="true"';
    $svg = preg_match('/<svg\b[^>]*class="/', $svg)
        ? preg_replace('/<svg\b([^>]*)class="([^"]*)"([^>]*)>/', '<svg$1class="'.e($class).' $2"$3 '.$attributeHtml.$extraAttributes.'>', $svg, 1)
        : preg_replace('/<svg\b([^>]*)>/', '<svg class="'.e($class).'"$1 '.$attributeHtml.$extraAttributes.'>', $svg, 1);
@endphp

{!! $svg !!}
