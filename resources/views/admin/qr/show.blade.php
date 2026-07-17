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
        <div class="max-w-md mx-auto px-4">
            {{-- El QR va sobre fondo blanco (contraste necesario para escanear). --}}
            <div id="qr-imprimible" class="card-brand p-8 text-center">
                <h1 class="font-display text-2xl font-bold text-cocoa-900 tracking-tight">Mesa {{ $mesa }}</h1>
                <p class="text-cocoa-600 text-sm mb-6">Escanea para ver la carta y pedir</p>

                {{-- RNF-06: QR imprimible que codifica /pedido?mesa=N --}}
                <div class="mx-auto w-64">
                    {!! $svg !!}
                </div>

                <p class="mt-6 text-xs text-cocoa-400 break-all">{{ $url }}</p>
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
