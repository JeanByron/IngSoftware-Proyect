{{-- RF-10: vista domicilio (acceso sin parámetro de mesa) --}}
<x-cliente-layout>
    <x-slot name="title">Pedido a domicilio — MesaQR</x-slot>
    <x-slot name="badge">A domicilio</x-slot>

    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Pedido a domicilio</h2>
        <p class="text-gray-600">Elige tus platos, indica tu dirección y confirma. Te lo llevamos.</p>
    </div>

    @include('orders._cart', ['type' => 'domicilio', 'dishes' => $dishes])
</x-cliente-layout>
