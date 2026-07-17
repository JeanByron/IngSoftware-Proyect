{{--
    Inicio del panel del restaurante (RF-18: acceso autenticado).
    Accesos rápidos a los módulos: menú (RF-01..05), pedidos (RF-19/20),
    QR por mesa (RNF-06) y exportación de ventas (RNF-16).
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display tracking-tight font-semibold text-xl text-cocoa-950 leading-tight">
            Panel de {{ config('comercio.nombre') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="mb-6 text-sm text-cocoa-600">
                Bienvenido. Elige una sección para empezar a trabajar.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('dishes.index') }}"
                   class="card-brand block p-6 transition duration-150 hover:shadow-md hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
                    <span class="text-3xl" aria-hidden="true">🍽️</span>
                    <h3 class="mt-3 font-display tracking-tight text-lg font-semibold text-cocoa-950">Gestionar carta</h3>
                    <p class="mt-1 text-sm text-cocoa-600">Crear, editar y activar los platos del menú.</p>
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="card-brand block p-6 transition duration-150 hover:shadow-md hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
                    <span class="text-3xl" aria-hidden="true">🧾</span>
                    <h3 class="mt-3 font-display tracking-tight text-lg font-semibold text-cocoa-950">Pedidos entrantes</h3>
                    <p class="mt-1 text-sm text-cocoa-600">Ver los pedidos de mesa y domicilio, y su estado.</p>
                </a>

                <a href="{{ route('admin.tables.qr', ['mesa' => 1]) }}"
                   class="card-brand block p-6 transition duration-150 hover:shadow-md hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
                    <span class="text-3xl" aria-hidden="true">🔳</span>
                    <h3 class="mt-3 font-display tracking-tight text-lg font-semibold text-cocoa-950">QR de mesas</h3>
                    <p class="mt-1 text-sm text-cocoa-600">Generar el código QR imprimible de cada mesa.</p>
                </a>

                <a href="{{ route('admin.export.ventas') }}"
                   class="card-brand block p-6 transition duration-150 hover:shadow-md hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
                    <span class="text-3xl" aria-hidden="true">📊</span>
                    <h3 class="mt-3 font-display tracking-tight text-lg font-semibold text-cocoa-950">Exportar ventas CSV</h3>
                    <p class="mt-1 text-sm text-cocoa-600">Descargar el respaldo de ventas en formato CSV.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
