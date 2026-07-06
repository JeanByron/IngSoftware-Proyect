{{-- RF-06: vista presencial (acceso vía QR con ?mesa=N) --}}
<x-cliente-layout>
    <x-slot name="title">Pedido en mesa — MesaQR</x-slot>
    {{-- RF-07: mostrar el número de mesa en pantalla --}}
    <x-slot name="badge">Mesa {{ $tableNumber }}</x-slot>

    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Pedido en mesa</h2>
        <p class="text-gray-600">Estás en la <strong>mesa {{ $tableNumber }}</strong>. Elige tus platos y confirma; te lo llevamos a la mesa.</p>
    </div>

    @include('orders._cart', ['type' => 'presencial', 'dishes' => $dishes, 'tableNumber' => $tableNumber])
</x-cliente-layout>
