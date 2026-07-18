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
        type: @js($type),
        tableNumber: @js($tableNumber ?? null)
     })"
     class="space-y-6">

    {{-- Lista de platos disponibles --}}
    <section>
        <h3 class="font-display text-lg font-semibold tracking-tight text-cocoa-900 mb-3">Carta disponible</h3>

        @if ($dishes->isEmpty())
            <p class="text-cocoa-600 card-brand p-4">
                No hay platos disponibles en este momento.
            </p>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($dishes as $dish)
                    <div class="card-brand p-4 flex flex-col justify-between transition duration-150 hover:shadow-md hover:border-caramel-300">
                        <div>
                            {{-- RNF-01: foto del plato (o marcador de posición) --}}
                            @if ($dish->imageUrl())
                                <img src="{{ $dish->imageUrl() }}" alt="{{ $dish->name }}"
                                     class="w-full aspect-video object-cover rounded-lg mb-3">
                            @else
                                <div class="w-full aspect-video rounded-lg mb-3 bg-cream-100 flex items-center justify-center text-3xl text-cocoa-300" aria-hidden="true">🍽️</div>
                            @endif
                            <div class="font-semibold text-cocoa-900">{{ $dish->name }}</div>
                            @if ($dish->description)
                                <p class="text-sm text-cocoa-600 mt-1">{{ $dish->description }}</p>
                            @endif
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="font-display text-lg text-caramel-700">${{ number_format($dish->price, 0, ',', '.') }}</span>
                            {{-- RF-09 / RF-11 --}}
                            {{-- RNF-12: control táctil de al menos 44x44px --}}
                            <button type="button"
                                    @click="addToCart({{ $dish->id }})"
                                    class="btn-accent min-h-[44px]">
                                Agregar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Carrito --}}
    <section class="card-brand p-4">
        <h3 class="font-display text-lg font-semibold tracking-tight text-cocoa-900 mb-3">Tu pedido</h3>

        <template x-if="items.length === 0">
            <p class="text-cocoa-500 text-sm">El carrito está vacío. Agrega platos de la carta.</p>
        </template>

        <ul class="divide-y divide-cream-200" x-show="items.length > 0">
            <template x-for="item in items" :key="item.id">
                <li class="py-3 flex items-center justify-between gap-3">
                    <div class="flex-1">
                        <div class="font-medium text-cocoa-900" x-text="item.name"></div>
                        <div class="text-sm text-cocoa-500">
                            $<span x-text="formatMoney(item.price)"></span> c/u
                        </div>
                    </div>
                    {{-- RF-13: modificar cantidad · RNF-12: botones táctiles de 44x44px --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="decrement(item.id)"
                                class="w-11 h-11 rounded-full bg-cream-200 text-cocoa-800 text-lg transition duration-150 hover:bg-caramel-200 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">−</button>
                        <span class="w-6 text-center" x-text="item.quantity"></span>
                        <button type="button" @click="increment(item.id)"
                                class="w-11 h-11 rounded-full bg-cream-200 text-cocoa-800 text-lg transition duration-150 hover:bg-caramel-200 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">+</button>
                    </div>
                    <div class="w-20 text-right font-medium text-cocoa-800">
                        $<span x-text="formatMoney(item.price * item.quantity)"></span>
                    </div>
                </li>
            </template>
        </ul>

        {{-- RF-14: total --}}
        <div class="mt-4 pt-4 border-t border-cream-200 flex items-center justify-between">
            <span class="text-base font-semibold text-cocoa-900">Total</span>
            <span class="font-display text-xl font-bold text-cocoa-900">$<span x-text="formatMoney(total)"></span></span>
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
            <div class="card-brand p-4 mb-4">
                <label for="address" class="block text-sm font-medium text-cocoa-700 mb-1">
                    Dirección de entrega
                </label>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       placeholder="Calle, número, barrio, referencia…"
                       class="block w-full border-cocoa-200 rounded-lg shadow-sm transition duration-150 focus:border-caramel-500 focus:ring-caramel-400">
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
                class="btn-accent w-full py-3 text-base">
            Confirmar pedido
        </button>
    </form>
</div>

<script>
    // Componente Alpine del carrito. RF-09/11/13/14/16 · RNF-15 (persistencia).
    function cart(config) {
        return {
            dishes: config.dishes,
            type: config.type,
            tableNumber: config.tableNumber ?? null,
            items: [],

            // RNF-15: al montar, se recupera el carrito guardado (sobrevive a
            // recargas de página). Alpine llama init() automáticamente.
            init() {
                this.load();
            },

            get total() {
                // RF-14: total reactivo
                return this.items.reduce((sum, i) => sum + i.price * i.quantity, 0);
            },

            addToCart(dishId) {
                const existing = this.items.find(i => i.id === dishId);
                if (existing) {
                    existing.quantity++;
                } else {
                    const dish = this.dishes.find(d => d.id === dishId);
                    if (dish) {
                        this.items.push({ id: dish.id, name: dish.name, price: dish.price, quantity: 1 });
                    }
                }
                this.persist();
            },

            increment(dishId) {
                const item = this.items.find(i => i.id === dishId);
                if (item) item.quantity++;
                this.persist();
            },

            decrement(dishId) {
                const item = this.items.find(i => i.id === dishId);
                if (!item) return;
                item.quantity--;
                if (item.quantity <= 0) {
                    this.items = this.items.filter(i => i.id !== dishId);
                }
                this.persist();
            },

            syncItems(e) {
                // RF-16: defensa extra en el cliente
                if (this.items.length === 0) {
                    e.preventDefault();
                    alert('Tu carrito está vacío.');
                    return;
                }
                // El pedido se envía: se limpia el carrito guardado para no
                // arrastrarlo al volver a la carta (RNF-15).
                this.clearStorage();
            },

            formatMoney(value) {
                return new Intl.NumberFormat('es-CO').format(value);
            },

            // --- Persistencia en localStorage (RNF-15) ---
            // Clave por contexto: el carrito de la mesa 5 no se mezcla con el de
            // domicilio ni con otra mesa.
            storageKey() {
                return 'mesaqr.cart.' + this.type + '.' + (this.tableNumber ?? 'domicilio');
            },

            load() {
                try {
                    const raw = window.localStorage.getItem(this.storageKey());
                    if (!raw) return;
                    const saved = JSON.parse(raw);
                    if (!Array.isArray(saved)) return;
                    // Sólo se conservan platos que siguen en la carta disponible;
                    // se refresca nombre/precio desde los datos actuales.
                    this.items = saved
                        .map(s => {
                            const dish = this.dishes.find(d => d.id === s.id);
                            return dish ? { id: dish.id, name: dish.name, price: dish.price, quantity: s.quantity } : null;
                        })
                        .filter(Boolean);
                } catch (e) {
                    // localStorage no disponible o dato corrupto: se ignora.
                }
            },

            persist() {
                try {
                    window.localStorage.setItem(this.storageKey(), JSON.stringify(this.items));
                } catch (e) {
                    // Modo privado / sin espacio: se ignora (el carrito sigue en memoria).
                }
            },

            clearStorage() {
                try {
                    window.localStorage.removeItem(this.storageKey());
                } catch (e) {}
            },
        };
    }
</script>
