{{-- Confirmación tras registrar el pedido (RF-15) --}}
<x-cliente-layout>
    <x-slot name="title">Pedido confirmado — MesaQR</x-slot>

    <div class="card-brand p-8 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700 text-3xl font-bold">✓</div>
        <h2 class="font-display text-2xl font-bold tracking-tight text-cocoa-900">¡Pedido confirmado!</h2>
        <p class="text-cocoa-600 mt-1">Tu número de pedido es <strong class="font-display text-cocoa-900">#{{ $order->id }}</strong>.</p>

        <div class="mt-4 badge-brand">
            Estado: {{ $order->statusLabel() }}
        </div>
    </div>

    <div class="card-brand p-6 mt-4">
        <h3 class="font-display text-lg font-semibold tracking-tight text-cocoa-900 mb-3">Detalle</h3>

        <dl class="text-sm text-cocoa-700 space-y-1 mb-4">
            <div class="flex justify-between">
                <dt class="text-cocoa-500">Tipo</dt>
                <dd>{{ $order->isPresencial() ? 'En mesa (presencial)' : 'A domicilio' }}</dd>
            </div>
            @if ($order->isPresencial())
                <div class="flex justify-between">
                    <dt class="text-cocoa-500">Mesa</dt>
                    <dd>{{ $order->table_number }}</dd>
                </div>
            @else
                <div class="flex justify-between">
                    <dt class="text-cocoa-500">Dirección</dt>
                    <dd class="text-right">{{ $order->address }}</dd>
                </div>
            @endif
        </dl>

        <ul class="divide-y divide-cream-200">
            @foreach ($order->items as $item)
                <li class="py-2 flex justify-between text-sm text-cocoa-700">
                    <span>{{ $item->quantity }}× {{ $item->dish_name }}</span>
                    <span>${{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-3 pt-3 border-t border-cream-200 flex justify-between">
            <span class="font-semibold text-cocoa-900">Total</span>
            <span class="font-display font-bold text-lg text-cocoa-900">${{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('orders.create', $order->isPresencial() ? ['mesa' => $order->table_number] : []) }}"
           class="btn-ghost">
            ← Hacer otro pedido
        </a>
    </div>
</x-cliente-layout>
