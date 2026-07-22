@php($user = auth()->user())

<section>
    <header>
        <h2 class="text-lg font-semibold text-cocoa-900">
            {{ __('Verificación en dos pasos (2FA)') }}
        </h2>
        <p class="mt-1 text-sm text-cocoa-600">
            {{ __('Añade una capa extra de seguridad al panel usando una app autenticadora (Google Authenticator, Authy…).') }}
        </p>
    </header>

    @if (session('status') === 'two-factor-enabled')
        <p class="mt-3 text-sm text-green-700">La verificación en dos pasos quedó activada.</p>
    @elseif (session('status') === 'two-factor-disabled')
        <p class="mt-3 text-sm text-green-700">La verificación en dos pasos quedó desactivada.</p>
    @endif

    @if ($user->hasTwoFactorEnabled())
        {{-- Estado: ACTIVO --}}
        <div class="mt-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Activo
            </span>
            <form method="POST" action="{{ route('2fa.disable') }}" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-red-700 text-white text-sm font-semibold transition duration-150 hover:bg-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400 focus-visible:ring-offset-2">
                    Desactivar 2FA
                </button>
            </form>
        </div>
    @elseif ($user->two_factor_secret)
        {{-- Estado: CONFIGURANDO (secreto generado, falta confirmar un código) --}}
        @php($uri = \App\Services\TwoFactor\TwoFactor::otpauthUri($user->two_factor_secret, $user->email))
        <div class="mt-4">
            <p class="text-sm text-cocoa-700">1) Escanea este código con tu app autenticadora:</p>
            <div class="mt-2 w-44 bg-white p-2 rounded-lg border border-cream-200">
                {!! \App\Services\TwoFactor\TwoFactor::qrSvg($uri) !!}
            </div>
            <p class="mt-2 text-xs text-cocoa-500">
                ¿No puedes escanear? Introduce esta clave: <code class="font-mono text-cocoa-800 break-all">{{ $user->two_factor_secret }}</code>
            </p>

            <form method="POST" action="{{ route('2fa.confirm') }}" class="mt-4">
                @csrf
                <x-input-label for="code" :value="'2) Escribe el código de 6 dígitos para confirmar'" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric" maxlength="6"
                              class="mt-1 block w-40 tracking-widest text-center" required autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                <x-primary-button class="mt-4">Confirmar y activar</x-primary-button>
            </form>

            <form method="POST" action="{{ route('2fa.disable') }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-cocoa-500 hover:text-cocoa-700 hover:underline transition duration-150">
                    Cancelar
                </button>
            </form>
        </div>
    @else
        {{-- Estado: NO ACTIVADO --}}
        <form method="POST" action="{{ route('2fa.enable') }}" class="mt-4">
            @csrf
            <x-primary-button>Activar 2FA</x-primary-button>
        </form>
    @endif
</section>
