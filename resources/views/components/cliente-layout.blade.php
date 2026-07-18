<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MesaQR') }}</title>

    {{-- Favicon del comercio (RNF-24: identidad configurable) --}}
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    {{-- Sin fuentes de CDN externo: Figtree y Fraunces se sirven self-hosted
         vía @fontsource (empaquetadas por Vite). Evita cargar recursos de
         terceros sin control de integridad (SRI). --}}

    <!-- Scripts y estilos (Tailwind + Alpine compilados por Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-cocoa-950 antialiased bg-cream-100 min-h-screen">
    <header class="bg-white shadow-sm border-b border-cream-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('orders.create') }}"
               class="flex items-center gap-3 rounded-lg font-display text-2xl tracking-tight text-cocoa-900
                      transition duration-150 hover:text-caramel-700
                      focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">
                <img src="{{ asset(config('comercio.logo')) }}" alt="" class="h-12 w-auto">
                {{ config('comercio.nombre') }}
            </a>
            @isset($badge)
                <span class="badge-brand">
                    {{ $badge }}
                </span>
            @endisset
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6">
        {{-- Mensaje de estado (p. ej. tras confirmar) --}}
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="max-w-3xl mx-auto px-4 py-8 text-center text-xs text-cocoa-500">
        {{-- RNF-05: enlaces a redes sociales del comercio (sólo si están configurados en .env).
             RNF-10: además, el módulo de redes debe estar activo.
             RNF-13: la lectura va dentro de Module::safe — si la config de redes
             estuviera mal formada, el footer se degrada (sin enlaces) pero la
             página del cliente (flujo básico) NO se cae. --}}
        @php($redes = \App\Support\Module::enabled('redes')
                ? \App\Support\Module::safe(fn () => array_filter(config('comercio.redes', [])), [], 'redes (footer)')
                : [])
        @if (! empty($redes))
            <div class="mb-3 flex items-center justify-center gap-4">
                @isset($redes['instagram'])
                    <a href="{{ $redes['instagram'] }}" target="_blank" rel="noopener noreferrer"
                       class="rounded text-cocoa-600 hover:text-caramel-600 transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">Instagram</a>
                @endisset
                @isset($redes['facebook'])
                    <a href="{{ $redes['facebook'] }}" target="_blank" rel="noopener noreferrer"
                       class="rounded text-cocoa-600 hover:text-caramel-600 transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">Facebook</a>
                @endisset
                @isset($redes['tiktok'])
                    <a href="{{ $redes['tiktok'] }}" target="_blank" rel="noopener noreferrer"
                       class="rounded text-cocoa-600 hover:text-caramel-600 transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400">TikTok</a>
                @endisset
            </div>
        @endif
        MesaQR — Plantilla web modular de pedidos por código QR
    </footer>
</body>
</html>
