<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pedido') }} #{{ $order->id }}
            </h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver</a>
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
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Tipo</dt>
                        <dd class="font-medium">{{ $order->isPresencial() ? 'Presencial (mesa)' : 'Domicilio' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ $order->isPresencial() ? 'Mesa' : 'Dirección' }}</dt>
                        <dd class="font-medium">{{ $order->isPresencial() ? $order->table_number : $order->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Fecha</dt>
                        <dd class="font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total</dt>
                        <dd class="font-bold">${{ number_format($order->total, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Líneas del pedido --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Platos</h3>
                <ul class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <li class="py-2 flex justify-between text-sm">
                            <span>{{ $item->quantity }}× {{ $item->dish_name }}
                                <span class="text-gray-400">(${{ number_format($item->unit_price, 0, ',', '.') }} c/u)</span>
                            </span>
                            <span class="font-medium">${{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- RF-20: cambiar estado --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Estado del pedido</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="flex items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                    <x-primary-button>Actualizar estado</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
