<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
                {{ __('Gestión de Menú') }}
            </h2>
            <a href="{{ route('dishes.create') }}" class="btn-brand">
                + Nuevo plato
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card-brand overflow-hidden">
                <div class="text-cocoa-900">
                    @if ($dishes->isEmpty())
                        <p class="p-6 text-cocoa-600">Aún no hay platos. Crea el primero con “Nuevo plato”.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-cream-200">
                                <thead class="bg-cream-100">
                                    <tr class="text-left text-xs font-semibold text-cocoa-700 uppercase tracking-wider">
                                        <th class="px-6 py-3">Plato</th>
                                        <th class="px-6 py-3">Precio</th>
                                        <th class="px-6 py-3">Disponibilidad</th>
                                        <th class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-cream-200">
                                    @foreach ($dishes as $dish)
                                        <tr class="hover:bg-cream-50 transition duration-150">
                                            <td class="px-6 py-3">
                                                <div class="flex items-center gap-3">
                                                    {{-- RNF-01: miniatura o marcador de posición --}}
                                                    @if ($dish->imageUrl())
                                                        <img src="{{ $dish->imageUrl() }}" alt="{{ $dish->name }}"
                                                             class="h-12 w-12 object-cover rounded-lg border border-cream-200 shrink-0">
                                                    @else
                                                        <div class="h-12 w-12 rounded-lg bg-cream-100 border border-cream-200 flex items-center justify-center text-cocoa-300 shrink-0" aria-hidden="true">🍽️</div>
                                                    @endif
                                                    <div>
                                                        <div class="font-medium text-cocoa-900">{{ $dish->name }}</div>
                                                        @if ($dish->description)
                                                            <div class="text-sm text-cocoa-600">{{ \Illuminate\Support\Str::limit($dish->description, 60) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap font-display text-cocoa-900 tracking-tight">${{ number_format($dish->price, 0, ',', '.') }}</td>
                                            <td class="px-6 py-3">
                                                {{-- RF-04: alternar disponibilidad --}}
                                                <form method="POST" action="{{ route('dishes.toggle', $dish) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition duration-150 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400
                                                                {{ $dish->is_available ? 'bg-green-100 text-green-800 hover:bg-green-50' : 'bg-cocoa-100 text-cocoa-600 hover:bg-cocoa-50' }}">
                                                        {{ $dish->is_available ? 'Disponible' : 'No disponible' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                                <a href="{{ route('dishes.edit', $dish) }}"
                                                   class="text-caramel-700 hover:text-caramel-600 hover:underline text-sm font-medium rounded transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">Editar</a>
                                                {{-- RF-03: eliminar --}}
                                                <form method="POST" action="{{ route('dishes.destroy', $dish) }}" class="inline ms-3"
                                                      onsubmit="return confirm('¿Eliminar este plato?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-700 hover:text-red-600 hover:underline text-sm font-medium rounded transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 border-t border-cream-200">
                            {{ $dishes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
