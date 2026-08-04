@props(['role'])

@php
    $styles = [
        'admin' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
        'moderator' => 'bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300',
    ];
    $labels = ['admin' => 'Admin', 'moderator' => 'Modo'];
@endphp

@if(isset($styles[$role]))
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium '.$styles[$role]]) }}>
        {{ $labels[$role] }}
    </span>
@endif
