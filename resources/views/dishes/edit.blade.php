<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
            {{ __('Editar plato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card-brand p-6">
                {{-- RF-02: editar plato --}}
                <form method="POST" action="{{ route('dishes.update', $dish) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('dishes._form', ['dish' => $dish])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
