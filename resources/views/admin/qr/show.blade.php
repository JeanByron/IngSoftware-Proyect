<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
            QR — Mesa {{ $mesa }}
        </h2>
    </x-slot>

    {{-- El SVG del QR trae un tamaño fijo (300px); lo forzamos a escalar al
         contenedor para que no desborde ni se solape con la URL. --}}
    <style>
        #qr-imprimible svg { width: 100%; height: auto; display: block; }
    </style>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            {{-- Navegación entre los QR de todas las mesas: anterior (izquierda)
                 y siguiente (derecha). No se imprimen (print:hidden). --}}
            <div class="flex flex-wrap items-center justify-center gap-4">

                {{-- ◀ Anterior: deshabilitado en la mesa 1 (no existe la mesa 0). --}}
                @if ($mesa > 1)
                    <a href="{{ route('admin.tables.qr', ['mesa' => $mesa - 1]) }}"
                       class="btn-ghost print:hidden shrink-0" aria-label="Ver la mesa {{ $mesa - 1 }}">
                        ◀ Mesa {{ $mesa - 1 }}
                    </a>
                @else
                    <span class="btn-ghost opacity-40 cursor-not-allowed print:hidden shrink-0" aria-disabled="true">
                        ◀ Anterior
                    </span>
                @endif

                {{-- El QR va sobre fondo blanco (contraste necesario para escanear). --}}
                <div id="qr-imprimible" class="card-brand p-8 text-center w-full max-w-md">
                    <h1 class="font-display text-2xl font-bold text-cocoa-900 tracking-tight">Mesa {{ $mesa }}</h1>
                    <p class="text-cocoa-600 text-sm mb-6">Escanea para ver la carta y pedir</p>

                    {{-- RNF-06: QR imprimible que codifica /pedido?mesa=N --}}
                    <div class="mx-auto w-64">
                        {!! $svg !!}
                    </div>

                    <p class="mt-6 text-xs text-cocoa-400 break-all">{{ $url }}</p>
                </div>

                {{-- Siguiente ▶: sin tope (el QR se genera para cualquier mesa ≥ 1). --}}
                <a href="{{ route('admin.tables.qr', ['mesa' => $mesa + 1]) }}"
                   class="btn-ghost print:hidden shrink-0" aria-label="Ver la mesa {{ $mesa + 1 }}">
                    Mesa {{ $mesa + 1 }} ▶
                </a>
            </div>

            <div class="mt-6 flex justify-center gap-3 print:hidden">
                <button onclick="window.print()" class="btn-brand">
                    Imprimir
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn-ghost">
                    Volver
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
