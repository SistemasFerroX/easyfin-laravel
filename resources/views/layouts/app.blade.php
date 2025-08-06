<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fuentes, meta, etc. -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- CSS + JS compilados por Vite (Tailwind + Bootstrap) -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Estilos extra sólo para dashboard -->
  @stack('styles')
</head>
<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    @include('layouts.navigation')

    {{-- Cabecera sólo si la sección 'header' está definida --}}
    @if (View::hasSection('header'))
      <header class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          @yield('header')
        </div>
      </header>
    @endif

    {{-- Contenido principal --}}
    <main>
      @yield('content')
    </main>
  </div>

  <!-- Scripts adicionales -->
  @stack('scripts')
</body>
</html>
