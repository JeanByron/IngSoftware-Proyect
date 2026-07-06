<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar plato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- RF-02: editar plato --}}
                    <form method="POST" action="{{ route('dishes.update', $dish) }}">
                        @csrf
                        @method('PUT')
                        @include('dishes._form', ['dish' => $dish])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
