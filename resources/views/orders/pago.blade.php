{{-- RNF-08: cobro del pedido (paso obligatorio del flujo). --}}
<x-cliente-layout>
    <x-slot name="title">Pago del pedido — {{ config('comercio.nombre') }}</x-slot>

    <div class="card-brand p-8">
        <h2 class="font-display text-2xl font-bold tracking-tight text-cocoa-900 text-center">Completa tu pago</h2>
        <p class="text-cocoa-600 text-sm text-center mt-1">Pedido #{{ $order->id }}</p>

        {{-- Resumen del importe --}}
        <div class="mt-6 rounded-lg bg-cream-100 border border-cream-200 p-4 flex items-center justify-between">
            <span class="font-semibold text-cocoa-900">Total a pagar</span>
            <span class="font-display font-bold text-2xl text-caramel-700">${{ number_format($order->total, 0, ',', '.') }}</span>
        </div>

        {{-- El método elegido cambia el texto del botón y el aviso: en efectivo
             se PIDE (se cobra en persona), no se paga por la app. --}}
        <form method="POST" action="{{ $action }}" class="mt-6 space-y-4"
              x-data="{ method: @js(old('payment_method', array_key_first($methods))) }">
            @csrf

            <fieldset>
                <legend class="text-sm font-medium text-cocoa-800 mb-2">Método de pago</legend>
                <div class="space-y-2">
                    @foreach ($methods as $key => $label)
                        <label class="flex items-center gap-3 rounded-lg border border-cocoa-200 p-3 cursor-pointer transition duration-150 hover:border-caramel-300 hover:bg-cream-50 has-[:checked]:border-caramel-500 has-[:checked]:bg-caramel-50">
                            <input type="radio" name="payment_method" value="{{ $key }}"
                                   x-model="method"
                                   class="text-caramel-600 border-cocoa-300 focus:ring-caramel-400">
                            <span class="text-cocoa-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
            </fieldset>

            {{-- Efectivo: se cobra en persona. Tarjeta/transferencia: pasarela (simulada). --}}
            <p class="text-xs text-cocoa-500" x-show="method === 'efectivo'" x-cloak>
                Pagarás en efectivo al recibir tu pedido; no se cobra por la app.
            </p>
            <p class="text-xs text-cocoa-500" x-show="method !== 'efectivo'">
                Pago en modo demostración (pasarela simulada). No se realiza ningún cobro real.
            </p>

            <button type="submit" class="btn-accent w-full py-3 text-base"
                    x-text="method === 'efectivo' ? 'Realizar pedido' : 'Pagar ${{ number_format($order->total, 0, ',', '.') }}'">
                Pagar ${{ number_format($order->total, 0, ',', '.') }}
            </button>
        </form>
    </div>
</x-cliente-layout>
