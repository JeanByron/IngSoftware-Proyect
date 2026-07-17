<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display tracking-tight font-semibold text-xl text-cocoa-950 leading-tight">
                Reservas
            </h2>
            <a href="{{ route('admin.reservations.create') }}" class="btn-brand">Nueva reserva</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card-brand overflow-hidden">
                @if ($reservations->isEmpty())
                    <p class="p-6 text-cocoa-600">No hay reservas próximas.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-cream-100 text-cocoa-700 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Cliente</th>
                                    <th class="px-4 py-3 text-left font-semibold">Fecha y hora</th>
                                    <th class="px-4 py-3 text-left font-semibold">Personas</th>
                                    <th class="px-4 py-3 text-left font-semibold">Mesa</th>
                                    <th class="px-4 py-3 text-left font-semibold">Estado</th>
                                    <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-200">
                                @foreach ($reservations as $reservation)
                                    <tr class="hover:bg-cream-50 transition duration-150">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-cocoa-900">{{ $reservation->customer_name }}</div>
                                            @if ($reservation->phone)
                                                <div class="text-xs text-cocoa-500">{{ $reservation->phone }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-cocoa-700">{{ $reservation->reserved_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-cocoa-700">{{ $reservation->party_size }}</td>
                                        <td class="px-4 py-3 text-cocoa-700">{{ $reservation->table_number ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @php($estilos = [
                                                \App\Models\Reservation::STATUS_PENDIENTE  => 'bg-caramel-100 text-caramel-800',
                                                \App\Models\Reservation::STATUS_CONFIRMADA => 'bg-green-100 text-green-800',
                                                \App\Models\Reservation::STATUS_CANCELADA  => 'bg-cocoa-100 text-cocoa-700',
                                            ])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estilos[$reservation->status] ?? 'bg-cocoa-100 text-cocoa-700' }}">
                                                {{ $reservation->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('admin.reservations.edit', $reservation) }}"
                                               class="text-caramel-700 hover:text-caramel-600 hover:underline transition duration-150">Editar</a>
                                            <form method="POST" action="{{ route('admin.reservations.destroy', $reservation) }}" class="inline"
                                                  onsubmit="return confirm('¿Eliminar esta reserva?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="ml-3 text-red-700 hover:text-red-600 hover:underline transition duration-150">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $reservations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
