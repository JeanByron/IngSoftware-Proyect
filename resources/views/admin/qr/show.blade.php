<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            QR — Mesa {{ $mesa }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto px-4">
            {{-- RNF-06: QR imprimible que codifica /pedido?mesa=N --}}
            <div id="qr-imprimible" class="bg-white rounded-lg shadow-sm p-8 text-center">
                <h1 class="text-2xl font-bold text-gray-800">Mesa {{ $mesa }}</h1>
                <p class="text-gray-500 text-sm mb-4">Escanea para ver la carta y pedir</p>

                <div class="mx-auto w-64 h-64">
                    {{-- El SVG viene del servidor (bacon-qr-code), es seguro imprimirlo --}}
                    {!! $svg !!}
                </div>

                <p class="mt-4 text-xs text-gray-400 break-all">{{ $url }}</p>
            </div>

            <div class="mt-6 flex justify-center gap-3 print:hidden">
                <button onclick="window.print()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Imprimir
                </button>
                <a href="{{ route('admin.orders.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    Volver
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
