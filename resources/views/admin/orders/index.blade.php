<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
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
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 {{ ! $filter ? 'bg-cocoa-800 text-cream-50 shadow-sm hover:bg-cocoa-700 hover:shadow-md' : 'bg-white text-cocoa-700 border border-cream-200 hover:bg-cream-50 hover:shadow-md' }}">
                    Todos
                </a>
                @foreach ($statuses as $status)
                    <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 {{ $filter === $status ? 'bg-cocoa-800 text-cream-50 shadow-sm hover:bg-cocoa-700 hover:shadow-md' : 'bg-white text-cocoa-700 border border-cream-200 hover:bg-cream-50 hover:shadow-md' }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </a>
                @endforeach
            </div>

            <div class="card-brand overflow-hidden">
                <div class="text-cocoa-900">
                    @if ($orders->isEmpty())
                        <p class="p-6 text-cocoa-600">No hay pedidos registrados todavía.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-cream-200">
                                <thead class="bg-cream-100">
                                    <tr class="text-left text-xs font-semibold text-cocoa-700 uppercase tracking-wider">
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Tipo</th>
                                        <th class="px-4 py-3">Mesa / Dirección</th>
                                        <th class="px-4 py-3">Ítems</th>
                                        <th class="px-4 py-3">Total</th>
                                        <th class="px-4 py-3">Estado</th>
                                        <th class="px-4 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-cream-200">
                                    @foreach ($orders as $order)
                                        <tr class="hover:bg-cream-50 transition duration-150">
                                            <td class="px-4 py-3 font-medium text-cocoa-900">#{{ $order->id }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                    {{ $order->isPresencial() ? 'bg-caramel-100 text-caramel-800' : 'bg-cocoa-100 text-cocoa-700' }}">
                                                    {{ $order->isPresencial() ? 'Presencial' : 'Domicilio' }}
                                                </span>
                                            </td>
                                            {{-- RF-19: mesa o dirección --}}
                                            <td class="px-4 py-3 text-sm text-cocoa-700">
                                                @if ($order->isPresencial())
                                                    Mesa {{ $order->table_number }}
                                                @else
                                                    {{ \Illuminate\Support\Str::limit($order->address, 40) }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ $order->items_count }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap font-display text-cocoa-900 tracking-tight">${{ number_format($order->total, 0, ',', '.') }}</td>
                                            {{-- RF-20: cambiar estado en línea --}}
                                            <td class="px-4 py-3">
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()"
                                                            class="text-sm border-cocoa-200 rounded-lg shadow-sm transition duration-150 hover:border-cocoa-300 focus:border-caramel-400 focus:ring-caramel-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
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
                                                   class="text-caramel-700 hover:text-caramel-600 hover:underline text-sm font-medium rounded transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">Ver</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 border-t border-cream-200">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
