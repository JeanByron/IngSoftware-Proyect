<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Menú') }}
            </h2>
            <a href="{{ route('dishes.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($dishes->isEmpty())
                        <p class="text-gray-500">Aún no hay platos. Crea el primero con “Nuevo plato”.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <th class="px-4 py-3">Plato</th>
                                        <th class="px-4 py-3">Precio</th>
                                        <th class="px-4 py-3">Disponibilidad</th>
                                        <th class="px-4 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($dishes as $dish)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900">{{ $dish->name }}</div>
                                                @if ($dish->description)
                                                    <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($dish->description, 60) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">${{ number_format($dish->price, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3">
                                                {{-- RF-04: alternar disponibilidad --}}
                                                <form method="POST" action="{{ route('dishes.toggle', $dish) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                {{ $dish->is_available ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                                        {{ $dish->is_available ? 'Disponible' : 'No disponible' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <a href="{{ route('dishes.edit', $dish) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Editar</a>
                                                {{-- RF-03: eliminar --}}
                                                <form method="POST" action="{{ route('dishes.destroy', $dish) }}" class="inline ms-3"
                                                      onsubmit="return confirm('¿Eliminar este plato?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $dishes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
