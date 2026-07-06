<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de pedidos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Filtro por estado --}}
            <div class="mb-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.orders.index') }}"
                   class="px-3 py-1.5 rounded-md text-sm {{ ! $filter ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-200' }}">
                    Todos
                </a>
                @foreach ($statuses as $status)
                    <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
                       class="px-3 py-1.5 rounded-md text-sm {{ $filter === $status ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-200' }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($orders->isEmpty())
                        <p class="text-gray-500">No hay pedidos registrados todavía.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Tipo</th>
                                        <th class="px-4 py-3">Mesa / Dirección</th>
                                        <th class="px-4 py-3">Ítems</th>
                                        <th class="px-4 py-3">Total</th>
                                        <th class="px-4 py-3">Estado</th>
                                        <th class="px-4 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td class="px-4 py-3 font-medium">#{{ $order->id }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $order->isPresencial() ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $order->isPresencial() ? 'Presencial' : 'Domicilio' }}
                                                </span>
                                            </td>
                                            {{-- RF-19: mesa o dirección --}}
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                @if ($order->isPresencial())
                                                    Mesa {{ $order->table_number }}
                                                @else
                                                    {{ \Illuminate\Support\Str::limit($order->address, 40) }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ $order->items_count }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">${{ number_format($order->total, 0, ',', '.') }}</td>
                                            {{-- RF-20: cambiar estado en línea --}}
                                            <td class="px-4 py-3">
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()"
                                                            class="text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
                                                        @foreach ($statuses as $status)
                                                            <option value="{{ $status }}" @selected($order->status === $status)>
                                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('admin.orders.show', $order) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Ver</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
