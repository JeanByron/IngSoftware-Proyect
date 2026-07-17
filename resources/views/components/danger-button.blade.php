{{-- Botón destructivo: rojo semántico (GuiaEstilo §3), mismo patrón de focus/transición de la marca. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-700 text-white text-sm font-semibold tracking-wide shadow-sm transition duration-150 hover:bg-red-600 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 focus-visible:ring-offset-2 active:scale-[0.98]']) }}>
    {{ $slot }}
</button>
