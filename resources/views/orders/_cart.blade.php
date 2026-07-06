{{--
    Carrito de cliente con Alpine.js (compartido por presencial y domicilio).

    Variables esperadas:
      $dishes      colección de platos disponibles (RF-05)
      $type        'presencial' | 'domicilio'
      $tableNumber (sólo presencial) número de mesa para asociar al pedido (RF-08)

    Requerimientos cubiertos en el cliente:
      RF-09 / RF-11 agregar platos al carrito
      RF-13         modificar cantidades
      RF-14         calcular y mostrar el total
      RF-16         impedir confirmar con el carrito vacío
--}}
@php($type = $type ?? 'domicilio')

<div x-data="cart({
        dishes: {{ Illuminate\Support\Js::from($dishes->map(fn ($d) => [
            'id'    => $d->id,
            'name'  => $d->name,
            'price' => (float) $d->price,
        ])) }},
        type: @js($type)
     })"
     class="space-y-6">

    {{-- Lista de platos disponibles --}}
    <section>
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Carta disponible</h3>

        @if ($dishes->isEmpty())
            <p class="text-gray-500 bg-white rounded-lg p-4 shadow-sm">
                No hay platos disponibles en este momento.
            </p>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($dishes as $dish)
                    <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $dish->name }}</div>
                            @if ($dish->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $dish->description }}</p>
                            @endif
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="font-semibold text-gray-800">${{ number_format($dish->price, 0, ',', '.') }}</span>
                            {{-- RF-09 / RF-11 --}}
                            <button type="button"
                                    @click="addToCart({{ $dish->id }})"
                                    class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                                Agregar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Carrito --}}
    <section class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Tu pedido</h3>

        <template x-if="items.length === 0">
            <p class="text-gray-500 text-sm">El carrito está vacío. Agrega platos de la carta.</p>
        </template>

        <ul class="divide-y divide-gray-100" x-show="items.length > 0">
            <template x-for="item in items" :key="item.id">
                <li class="py-3 flex items-center justify-between gap-3">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900" x-text="item.name"></div>
                        <div class="text-sm text-gray-500">
                            $<span x-text="formatMoney(item.price)"></span> c/u
                        </div>
                    </div>
                    {{-- RF-13: modificar cantidad --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="decrement(item.id)"
                                class="w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">−</button>
                        <span class="w-6 text-center" x-text="item.quantity"></span>
                        <button type="button" @click="increment(item.id)"
                                class="w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">+</button>
                    </div>
                    <div class="w-20 text-right font-medium text-gray-800">
                        $<span x-text="formatMoney(item.price * item.quantity)"></span>
                    </div>
                </li>
            </template>
        </ul>

        {{-- RF-14: total --}}
        <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
            <span class="text-base font-semibold text-gray-800">Total</span>
            <span class="text-xl font-bold text-gray-900">$<span x-text="formatMoney(total)"></span></span>
        </div>
    </section>

    {{-- Formulario de confirmación --}}
    <form method="POST" action="{{ route('orders.store') }}" @submit="syncItems">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        @if ($type === 'presencial')
            {{-- RF-08: la mesa viaja oculta para asociarla al pedido --}}
            <input type="hidden" name="table_number" value="{{ $tableNumber }}">
        @else
            {{-- RF-12: dirección obligatoria en domicilio --}}
            <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                    Dirección de entrega
                </label>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       placeholder="Calle, número, barrio, referencia…"
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Los ítems del carrito se serializan aquí antes de enviar --}}
        <template x-for="(item, index) in items" :key="'h-' + item.id">
            <span>
                <input type="hidden" :name="'items[' + index + '][dish_id]'" :value="item.id">
                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
            </span>
        </template>

        @error('items')
            <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        {{-- RF-16: botón deshabilitado si el carrito está vacío --}}
        <button type="submit"
                :disabled="items.length === 0"
                class="w-full py-3 rounded-md text-white font-semibold transition
                       disabled:opacity-40 disabled:cursor-not-allowed
                       bg-green-600 hover:bg-green-700">
            Confirmar pedido
        </button>
    </form>
</div>

<script>
    // Componente Alpine del carrito. RF-09/11/13/14/16.
    function cart(config) {
        return {
            dishes: config.dishes,
            type: config.type,
            items: [],

            get total() {
                // RF-14: total reactivo
                return this.items.reduce((sum, i) => sum + i.price * i.quantity, 0);
            },

            addToCart(dishId) {
                const existing = this.items.find(i => i.id === dishId);
                if (existing) {
                    existing.quantity++;
                    return;
                }
                const dish = this.dishes.find(d => d.id === dishId);
                if (dish) {
                    this.items.push({ id: dish.id, name: dish.name, price: dish.price, quantity: 1 });
                }
            },

            increment(dishId) {
                const item = this.items.find(i => i.id === dishId);
                if (item) item.quantity++;
            },

            decrement(dishId) {
                const item = this.items.find(i => i.id === dishId);
                if (!item) return;
                item.quantity--;
                if (item.quantity <= 0) {
                    this.items = this.items.filter(i => i.id !== dishId);
                }
            },

            syncItems(e) {
                // RF-16: defensa extra en el cliente
                if (this.items.length === 0) {
                    e.preventDefault();
                    alert('Tu carrito está vacío.');
                }
            },

            formatMoney(value) {
                return new Intl.NumberFormat('es-CO').format(value);
            },
        };
    }
</script>
