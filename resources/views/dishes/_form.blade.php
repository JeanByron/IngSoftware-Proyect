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
                  class="mt-1 block w-full border-cocoa-200 focus:border-caramel-400 focus:ring-caramel-400 rounded-lg shadow-sm transition duration-150">{{ old('description', $dish->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    {{-- RNF-01: imagen del plato (opcional) --}}
    <div>
        <x-input-label for="image" :value="__('Imagen del plato (opcional)')" />

        @if ($dish && $dish->imageUrl())
            <div class="mt-2 mb-3">
                <img src="{{ $dish->imageUrl() }}" alt="{{ $dish->name }}"
                     class="h-32 w-32 object-cover rounded-lg border border-cream-200">
                <p class="mt-1 text-xs text-cocoa-500">Imagen actual. Sube otra para reemplazarla.</p>
            </div>
        @endif

        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
               class="mt-1 block w-full text-sm text-cocoa-700
                      file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                      file:text-sm file:font-semibold file:bg-cocoa-800 file:text-cream-50
                      hover:file:bg-cocoa-700 file:cursor-pointer transition duration-150">
        <p class="mt-1 text-xs text-cocoa-500">JPG, PNG o WebP. Máximo 2 MB.</p>
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
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
                   class="rounded border-cocoa-300 text-caramel-600 shadow-sm focus:ring-caramel-400 transition duration-150"
                   {{ old('is_available', $dish->is_available ?? true) ? 'checked' : '' }}>
            <span class="ms-2 text-sm text-cocoa-700">{{ __('Disponible para los clientes') }}</span>
        </label>
        <x-input-error :messages="$errors->get('is_available')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-brand">{{ __('Guardar') }}</button>
        <a href="{{ route('dishes.index') }}" class="btn-ghost">Cancelar</a>
    </div>
</div>
