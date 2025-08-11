{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@push('styles')
  {{-- CSS del dashboard --}}
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  {{-- Splide (CDN) --}}
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" />
@endpush

@section('title', 'EasyFin – Dashboard')

@section('content')
<div class="dashboard-wrapper">
  {{-- Sidebar (overlay) --}}
  <aside class="sidebar">
    <nav>
      <a href="{{ route('dashboard') }}" class="active">🏠 <span>Inicio</span></a>

      @role('user')
        <a href="{{ route('solicitudes.create') }}">📄 <span>Solicitar Préstamo</span></a>
        <a href="{{ route('solicitudes.index') }}">📑 <span>Mis Préstamos</span></a>
      @endrole

      @hasanyrole('admin|super-admin')
        <a href="{{ route('solicitudes.index') }}">📄 <span>Solicitudes</span></a>
        <a href="{{ route('informes') }}">📊 <span>Informes</span></a>
      @endhasanyrole

      @role('super-admin')
        <a href="{{ route('admin.users.index') }}">👥 <span>Usuarios</span></a>
      @endrole

      <a href="{{ route('profile.edit') }}">👤 <span>Perfil</span></a>
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault();document.getElementById('logout-form').submit();">
         🚪 <span>Salir</span>
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
      </form>
    </nav>
  </aside>

  {{-- Contenido principal --}}
  <div class="main-content">
    <header class="dashboard-header">
      <div>
        <h1>Hola, {{ auth()->user()->name }}</h1>
        <small>{{ \Carbon\Carbon::now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y, H:i') }}</small>
      </div>
    </header>

    {{-- Estadísticas --}}
    <section class="stats-overview">
      @role('user')
        <div class="stat-card total"><h3>Total solicitados</h3><p>{{ $totalUsuario }}</p></div>
        <div class="stat-card pendientes"><h3>Pendientes</h3><p>{{ $pendientesUsuario }}</p></div>
        <div class="stat-card aprobadas"><h3>Aprobadas</h3><p>{{ $aprobadasUsuario }}</p></div>
        <div class="stat-card rechazadas"><h3>Rechazadas</h3><p>{{ $rechazadasUsuario }}</p></div>
      @endrole

      @hasanyrole('admin|super-admin')
        <div class="stat-card total"><h3>Total solicitudes</h3><p>{{ $total }}</p></div>
        <div class="stat-card pendientes"><h3>Pendientes</h3><p>{{ $pendientes }}</p></div>
        <div class="stat-card aprobadas"><h3>Aprobadas</h3><p>{{ $aprobadas }}</p></div>
        <div class="stat-card rechazadas"><h3>Rechazadas</h3><p>{{ $rechazadas }}</p></div>
      @endhasanyrole
    </section>

    {{-- Banner con Splide --}}
    <div class="d-flex justify-content-center mt-5">
      <div class="w-full max-w-7xl">
        <div id="bannerSplide" class="splide">
          <div class="splide__track">
            <ul class="splide__list">
              <li class="splide__slide">
                <img src="{{ asset('img/banner1.jpg') }}" alt="Banner 1" class="w-full rounded-md shadow-lg">
              </li>
              <li class="splide__slide">
                <img src="{{ asset('img/banner2.jpg') }}" alt="Banner 2" class="w-full rounded-md shadow-lg">
              </li>
              <li class="splide__slide">
                <img src="{{ asset('img/banner3.jpg') }}" alt="Banner 3" class="w-full rounded-md shadow-lg">
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

  </div> {{-- /main-content --}}
</div> {{-- /dashboard-wrapper --}}
@endsection

@push('scripts')
  {{-- Splide (CDN) --}}
  <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      new Splide('#bannerSplide', {
        type: 'loop',
        autoplay: true,
        interval: 4000,
        speed: 600,
        arrows: false,
        pagination: false,
        pauseOnHover: false,
        pauseOnFocus: false,
        heightRatio: 0.35,
        breakpoints: {
          1024: { heightRatio: 0.45 },
          640:  { heightRatio: 0.60 },
        },
      }).mount();
    });
  </script>
@endpush
