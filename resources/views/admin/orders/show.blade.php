<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
                {{ __('Pedido') }} #{{ $order->id }}
            </h2>
            <div class="flex items-center gap-3">
                {{-- RNF-07: comanda de cocina (módulo activable MODULE_COMANDA). --}}
                @if (config('modules.comanda'))
                    <a href="{{ route('admin.orders.comanda', $order) }}" target="_blank" rel="noopener"
                       class="btn-ghost">🧾 Comanda</a>
                @endif
                <a href="{{ route('admin.orders.index') }}" class="btn-ghost">← Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Datos del pedido (RF-19) --}}
            <div class="card-brand p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-cocoa-600">Tipo</dt>
                        <dd class="font-medium text-cocoa-900">{{ $order->isPresencial() ? 'Presencial (mesa)' : 'Domicilio' }}</dd>
                    </div>
                    <div>
                        <dt class="text-cocoa-600">{{ $order->isPresencial() ? 'Mesa' : 'Dirección' }}</dt>
                        <dd class="font-medium text-cocoa-900">{{ $order->isPresencial() ? $order->table_number : $order->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-cocoa-600">Fecha</dt>
                        <dd class="font-medium text-cocoa-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-cocoa-600">Total</dt>
                        <dd class="font-display font-bold text-lg text-cocoa-900 tracking-tight">${{ number_format($order->total, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Líneas del pedido --}}
            <div class="card-brand p-6">
                <h3 class="font-display font-semibold text-cocoa-900 tracking-tight mb-3">Platos</h3>
                <ul class="divide-y divide-cream-200">
                    @foreach ($order->items as $item)
                        <li class="py-2 flex justify-between text-sm text-cocoa-900">
                            <span>{{ $item->quantity }}× {{ $item->dish_name }}
                                <span class="text-cocoa-400">(${{ number_format($item->unit_price, 0, ',', '.') }} c/u)</span>
                            </span>
                            <span class="font-display font-medium tracking-tight">${{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- RF-20: cambiar estado --}}
            <div class="card-brand p-6">
                <h3 class="font-display font-semibold text-cocoa-900 tracking-tight mb-3">Estado del pedido</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="flex items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="border-cocoa-200 rounded-lg shadow-sm transition duration-150 hover:border-cocoa-300 focus:border-caramel-400 focus:ring-caramel-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-brand">Actualizar estado</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
