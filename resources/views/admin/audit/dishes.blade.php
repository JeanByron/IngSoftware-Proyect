<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
                Historial de cambios del menú
            </h2>
            <a href="{{ route('dishes.index') }}" class="btn-ghost">← Volver a la carta</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="mb-4 text-sm text-cocoa-600">
                Registro inalterable (RNF-20): quién cambió qué en el catálogo y los precios, y cuándo.
            </p>

            <div class="card-brand overflow-hidden">
                @if ($logs->isEmpty())
                    <p class="p-6 text-cocoa-600">Todavía no hay cambios registrados en el menú.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-cream-100 text-cocoa-700 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Acción</th>
                                    <th class="px-4 py-3 text-left font-semibold">Plato</th>
                                    <th class="px-4 py-3 text-left font-semibold">Precio</th>
                                    <th class="px-4 py-3 text-left font-semibold">Usuario</th>
                                    <th class="px-4 py-3 text-left font-semibold">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-200">
                                @foreach ($logs as $log)
                                    @php($estilos = [
                                        'created' => ['Creado',    'bg-green-100 text-green-800'],
                                        'updated' => ['Editado',   'bg-caramel-100 text-caramel-800'],
                                        'deleted' => ['Eliminado', 'bg-cocoa-100 text-cocoa-700'],
                                    ])
                                    @php($info = $estilos[$log->action] ?? [ucfirst($log->action), 'bg-cocoa-100 text-cocoa-700'])
                                    <tr class="hover:bg-cream-50 transition duration-150">
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $info[1] }}">
                                                {{ $info[0] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-cocoa-900">{{ $log->dish_name }}</td>
                                        <td class="px-4 py-3 text-cocoa-700 whitespace-nowrap">
                                            @if ($log->action === 'updated')
                                                <span class="text-cocoa-500">${{ number_format($log->old_price, 0, ',', '.') }}</span>
                                                <span class="text-cocoa-400">→</span>
                                                <span class="font-display text-cocoa-900">${{ number_format($log->new_price, 0, ',', '.') }}</span>
                                            @elseif ($log->action === 'created')
                                                <span class="font-display text-cocoa-900">${{ number_format($log->new_price, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-cocoa-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-cocoa-700">{{ $log->user?->name ?? 'sistema' }}</td>
                                        <td class="px-4 py-3 text-cocoa-500 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-4 border-t border-cream-200">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
