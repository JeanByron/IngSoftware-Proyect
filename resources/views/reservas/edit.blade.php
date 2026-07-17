<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display tracking-tight font-semibold text-xl text-cocoa-950 leading-tight">
            Editar reserva
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('reservas._form', [
                'reservation' => $reservation,
                'action'      => route('admin.reservations.update', $reservation),
                'method'      => 'PUT',
            ])
        </div>
    </div>
</x-app-layout>
