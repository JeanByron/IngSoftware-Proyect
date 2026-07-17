{{-- RF-06: vista presencial (acceso vía QR con ?mesa=N) --}}
<x-cliente-layout>
    <x-slot name="title">Pedido en mesa — MesaQR</x-slot>
    {{-- RF-07: mostrar el número de mesa en pantalla --}}
    <x-slot name="badge">Mesa {{ $tableNumber }}</x-slot>

    <div class="mb-4">
        <h2 class="font-display text-2xl font-bold tracking-tight text-cocoa-900">Pedido en mesa</h2>
        <p class="text-cocoa-600">Estás en la <strong>mesa {{ $tableNumber }}</strong>. Elige tus platos y confirma; te lo llevamos a la mesa.</p>
    </div>

    @include('orders._cart', ['type' => 'presencial', 'dishes' => $dishes, 'tableNumber' => $tableNumber])
</x-cliente-layout>
