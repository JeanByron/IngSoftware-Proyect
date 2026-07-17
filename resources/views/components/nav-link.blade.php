@props(['active'])

@php
// Enlace de la navbar oscura (cocoa-900): activo con subrayado caramelo.
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-caramel-400 text-sm font-medium leading-5 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-cream-200 hover:text-white hover:border-cocoa-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 focus:text-white transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
