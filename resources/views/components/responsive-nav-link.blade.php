@props(['active'])

@php
// Enlace del menú responsive sobre fondo cocoa-900: activo con borde caramelo.
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-caramel-400 text-start text-base font-medium text-white bg-cocoa-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 focus:text-white focus:bg-cocoa-800 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-cream-200 hover:text-white hover:bg-cocoa-800 hover:border-cocoa-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 focus:text-white focus:bg-cocoa-800 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
