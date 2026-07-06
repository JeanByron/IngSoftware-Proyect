{{-- Confirmación tras registrar el pedido (RF-15) --}}
<x-cliente-layout>
    <x-slot name="title">Pedido confirmado — MesaQR</x-slot>

    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
        <div class="text-5xl mb-3">✅</div>
        <h2 class="text-2xl font-bold text-gray-800">¡Pedido confirmado!</h2>
        <p class="text-gray-600 mt-1">Tu número de pedido es <strong>#{{ $order->id }}</strong>.</p>

        <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
            Estado: {{ $order->statusLabel() }}
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 mt-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Detalle</h3>

        <dl class="text-sm text-gray-700 space-y-1 mb-4">
            <div class="flex justify-between">
                <dt class="text-gray-500">Tipo</dt>
                <dd>{{ $order->isPresencial() ? 'En mesa (presencial)' : 'A domicilio' }}</dd>
            </div>
            @if ($order->isPresencial())
                <div class="flex justify-between">
                    <dt class="text-gray-500">Mesa</dt>
                    <dd>{{ $order->table_number }}</dd>
                </div>
            @else
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dirección</dt>
                    <dd class="text-right">{{ $order->address }}</dd>
                </div>
            @endif
        </dl>

        <ul class="divide-y divide-gray-100">
            @foreach ($order->items as $item)
                <li class="py-2 flex justify-between text-sm">
                    <span>{{ $item->quantity }}× {{ $item->dish_name }}</span>
                    <span>${{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between">
            <span class="font-semibold">Total</span>
            <span class="font-bold text-lg">${{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('orders.create', $order->isPresencial() ? ['mesa' => $order->table_number] : []) }}"
           class="text-indigo-600 hover:text-indigo-800 font-medium">
            ← Hacer otro pedido
        </a>
    </div>
</x-cliente-layout>
