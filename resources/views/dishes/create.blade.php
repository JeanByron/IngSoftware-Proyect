<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
            {{ __('Nuevo plato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card-brand p-6">
                {{-- RF-01: crear plato --}}
                <form method="POST" action="{{ route('dishes.store') }}">
                    @csrf
                    @include('dishes._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
