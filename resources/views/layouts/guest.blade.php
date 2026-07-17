<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Identidad del comercio (RNF-24): favicon desde el logo. --}}
        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

        {{-- Fuentes self-hosted vía @fontsource (empaquetadas por Vite, sin CDN externo). --}}

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-cocoa-950 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cream-100">
            <div>
                <a href="/" class="inline-block rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-caramel-400 transition duration-150">
                    <x-application-logo class="h-24 w-auto" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white rounded-xl shadow-sm border border-cream-200 overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
