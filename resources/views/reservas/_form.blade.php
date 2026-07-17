{{--
    Formulario compartido de reserva (crear/editar). Módulo Reservas (RNF-10).
    Espera:
      $reservation  (sólo en edición) instancia existente
      $action       URL de destino del form
      $method       'POST' | 'PUT'
--}}
@php($reservation = $reservation ?? null)

<form method="POST" action="{{ $action }}" class="card-brand p-6 space-y-6">
    @csrf
    @if (($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    <div>
        <x-input-label for="customer_name" :value="'Nombre del cliente'" />
        <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full"
                      :value="old('customer_name', $reservation->customer_name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="phone" :value="'Teléfono (opcional)'" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                          :value="old('phone', $reservation->phone ?? '')" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="reserved_at" :value="'Fecha y hora'" />
            <x-text-input id="reserved_at" name="reserved_at" type="datetime-local" class="mt-1 block w-full"
                          :value="old('reserved_at', optional($reservation->reserved_at ?? null)->format('Y-m-d\TH:i'))" required />
            <x-input-error :messages="$errors->get('reserved_at')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="party_size" :value="'Número de personas'" />
            <x-text-input id="party_size" name="party_size" type="number" min="1" max="50" class="mt-1 block w-full"
                          :value="old('party_size', $reservation->party_size ?? 2)" required />
            <x-input-error :messages="$errors->get('party_size')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="table_number" :value="'Mesa (opcional)'" />
            <x-text-input id="table_number" name="table_number" type="number" min="1" class="mt-1 block w-full"
                          :value="old('table_number', $reservation->table_number ?? '')" />
            <x-input-error :messages="$errors->get('table_number')" class="mt-2" />
        </div>
    </div>

    @if ($reservation)
        <div>
            <x-input-label for="status" :value="'Estado'" />
            <select id="status" name="status"
                    class="mt-1 block w-full border-cocoa-200 rounded-lg shadow-sm transition duration-150 focus:border-caramel-500 focus:ring-caramel-400">
                @foreach (\App\Models\Reservation::STATUSES as $estado)
                    <option value="{{ $estado }}" @selected(old('status', $reservation->status) === $estado)>
                        {{ ucfirst($estado) }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    @endif

    <div>
        <x-input-label for="notes" :value="'Notas (opcional)'" />
        <textarea id="notes" name="notes" rows="3"
                  class="mt-1 block w-full border-cocoa-200 rounded-lg shadow-sm transition duration-150 focus:border-caramel-500 focus:ring-caramel-400">{{ old('notes', $reservation->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="btn-brand">Guardar</button>
        <a href="{{ route('admin.reservations.index') }}" class="btn-ghost">Cancelar</a>
    </div>
</form>
