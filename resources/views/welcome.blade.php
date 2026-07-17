<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('comercio.nombre') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Vista de bienvenida. La raíz redirige a /pedido (routes/web.php), así que
     normalmente no se ve; se mantiene mínima y con la marca por si se enlaza. --}}
<body class="font-sans antialiased bg-cream-100 text-cocoa-950 min-h-screen flex items-center justify-center p-6">
    <div class="card-brand p-8 text-center max-w-sm w-full">
        <img src="{{ asset(config('comercio.logo')) }}" alt="{{ config('comercio.nombre') }}" class="h-28 w-auto mx-auto mb-4">
        <h1 class="font-display tracking-tight text-3xl text-cocoa-900">{{ config('comercio.nombre') }}</h1>
        <p class="mt-2 text-cocoa-600 text-sm">Pedidos en mesa y a domicilio</p>
        <div class="mt-6 flex flex-col gap-3">
            <a href="{{ route('orders.create') }}" class="btn-accent w-full py-3 text-base">Ver la carta</a>
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn-ghost w-full">Panel del restaurante</a>
            @endif
        </div>
    </div>
</body>
</html>
