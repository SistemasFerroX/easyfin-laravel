{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('title', 'EasyFin – Dashboard')

@section('content')
<div class="dashboard-wrapper">
  <!-- Sidebar -->
  <aside class="sidebar">
    <nav>
      <a href="{{ route('dashboard') }}" class="active">
        🏠 <span>Inicio</span>
      </a>
      <a href="{{ route('solicitudes.create') }}">
        📄 <span>Solicitudes</span>
      </a>
      <a href="#">
        📊 <span>Informes</span>
      </a>
      <a href="{{ route('profile.edit') }}">
        👤 <span>Perfil</span>
      </a>
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault();document.getElementById('logout-form').submit();">
        🚪 <span>Salir</span>
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
      </form>
    </nav>
  </aside>

  <!-- Main content -->
  <div class="main-content">
    <header class="dashboard-header">
      <div>
        <h1>Hola, {{ $user->name }}</h1>
        <small>{{ \Carbon\Carbon::now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y, H:i') }}</small>
      </div>
    </header>

    <section class="stats-overview">
      <div class="stat-card total">
        <h3>Total</h3>
        <p>{{ $total }}</p>
      </div>
      <div class="stat-card pendientes">
        <h3>Pendientes</h3>
        <p>{{ $pendientes }}</p>
      </div>
      <div class="stat-card aprobadas">
        <h3>Aprobadas</h3>
        <p>{{ $aprobadas }}</p>
      </div>
      <div class="stat-card rechazadas">
        <h3>Rechazadas</h3>
        <p>{{ $rechazadas }}</p>
      </div>
    </section>

    <!-- Banner Carrusel SOLO IMÁGENES dentro de un contenedor destacado -->
    <div class="d-flex justify-content-center mt-5">
      <div class="bg-white rounded-4 shadow-lg p-3" style="width:100%;">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="{{ asset('img/banner1.jpg') }}" class="d-block w-100" alt="Banner 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('img/banner2.jpg') }}" class="d-block w-100" alt="Banner 2">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('img/banner3.jpg') }}" class="d-block w-100" alt="Banner 3">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection