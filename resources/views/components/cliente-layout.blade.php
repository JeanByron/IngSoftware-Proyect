<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MesaQR') }}</title>

    {{-- Sin fuentes de CDN externo: Tailwind cae a la pila de fuentes del sistema
         (ver tailwind.config.js). Evita cargar recursos de terceros sin control
         de integridad (SRI) y permite que la app funcione sin conexión. --}}

    <!-- Scripts y estilos (Tailwind + Alpine compilados por Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100 min-h-screen">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('orders.create') }}" class="text-xl font-semibold text-gray-800">
                🍽️ {{ config('app.name', 'MesaQR') }}
            </a>
            @isset($badge)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                    {{ $badge }}
                </span>
            @endisset
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6">
        {{-- Mensaje de estado (p. ej. tras confirmar) --}}
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="max-w-3xl mx-auto px-4 py-8 text-center text-xs text-gray-400">
        {{-- RNF-05: enlaces a redes sociales del comercio (sólo si están configurados en .env) --}}
        @php($redes = array_filter(config('comercio.redes', [])))
        @if (! empty($redes))
            <div class="mb-3 flex items-center justify-center gap-4">
                @isset($redes['instagram'])
                    <a href="{{ $redes['instagram'] }}" target="_blank" rel="noopener noreferrer"
                       class="text-gray-500 hover:text-indigo-600">Instagram</a>
                @endisset
                @isset($redes['facebook'])
                    <a href="{{ $redes['facebook'] }}" target="_blank" rel="noopener noreferrer"
                       class="text-gray-500 hover:text-indigo-600">Facebook</a>
                @endisset
                @isset($redes['tiktok'])
                    <a href="{{ $redes['tiktok'] }}" target="_blank" rel="noopener noreferrer"
                       class="text-gray-500 hover:text-indigo-600">TikTok</a>
                @endisset
            </div>
        @endif
        MesaQR — Plantilla web modular de pedidos por código QR
    </footer>
</body>
</html>
