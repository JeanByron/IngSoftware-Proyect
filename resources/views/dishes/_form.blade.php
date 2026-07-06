{{--
    Formulario compartido de plato (create/edit).
    Espera la variable opcional $dish; si no existe, es creación.
--}}
@php($dish = $dish ?? null)

<div class="space-y-6">
    {{-- RF-01: nombre --}}
    <div>
        <x-input-label for="name" :value="__('Nombre')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $dish->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- RF-01: descripción --}}
    <div>
        <x-input-label for="description" :value="__('Descripción')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $dish->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    {{-- RF-01: precio --}}
    <div>
        <x-input-label for="price" :value="__('Precio')" />
        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full"
                      :value="old('price', $dish->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    {{-- RF-04: disponibilidad --}}
    <div>
        <label for="is_available" class="inline-flex items-center">
            <input type="checkbox" id="is_available" name="is_available" value="1"
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   {{ old('is_available', $dish->is_available ?? true) ? 'checked' : '' }}>
            <span class="ms-2 text-sm text-gray-600">{{ __('Disponible para los clientes') }}</span>
        </label>
        <x-input-error :messages="$errors->get('is_available')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
        <a href="{{ route('dishes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    </div>
</div>
