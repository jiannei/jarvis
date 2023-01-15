<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="antialiased">
<div class="relative flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    @auth
        <p class="px-6 py-3 bg-white rounded-md shadow">
            <span class="text-lg text-gray-500">{{ $token }}</span>
        </p>
    @endauth

    @guest
    <a class="hover:underline decoration-sky-500 decoration-1 decoration-wavy underline-offset-8 cursor-pointer" href="{{ route('login') }}">
        <span class="font-bold text-5xl text-transparent bg-clip-text bg-gradient-to-r from-yellow-500 to-red-500">
             {{ config('app.name') }}
        </span>
    </a>
    @endguest
</div>
</body>
</html>
