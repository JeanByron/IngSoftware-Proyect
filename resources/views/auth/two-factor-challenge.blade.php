<x-guest-layout>
    {{-- RNF-19: paso del segundo factor tras validar la contraseña. --}}
    <p class="mb-4 text-sm text-cocoa-600">
        Verificación en dos pasos. Escribe el código de 6 dígitos que muestra tu app autenticadora.
    </p>

    <form method="POST" action="{{ route('2fa.challenge') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="'Código de verificación'" />
            <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center"
                          type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                          maxlength="6" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Verificar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
