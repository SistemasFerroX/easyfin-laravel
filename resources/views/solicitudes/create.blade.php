{{-- resources/views/solicitudes/create.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<div class="solicitud-card-wrapper">
  <div class="solicitud-card">
    <h2 class="solicitud-title">Ingresa tu solicitud</h2>

    <form action="{{ route('solicitudes.store') }}" method="POST" class="solicitud-form">
      @csrf

      {{-- 1. Datos personales --}}
      <fieldset class="mb-6">
        <legend>1. Datos personales</legend>

        <div class="two-columns gap mb-4">
          <div class="field">
            <label for="nombre_completo">Nombre Completo *</label>
            <input type="text" name="nombre_completo" id="nombre_completo"
                   class="form-control" value="{{ old('nombre_completo') }}">
            @error('nombre_completo')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
          <div class="field">
            <label for="identificacion">Identificación *</label>
            <input type="text" name="identificacion" id="identificacion"
                   class="form-control" value="{{ old('identificacion') }}">
            @error('identificacion')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="two-columns gap mb-4">
          <div class="field">
            <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                   class="form-control" value="{{ old('fecha_nacimiento') }}">
            @error('fecha_nacimiento')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
          <div class="field">
            <label for="email">Correo Electrónico *</label>
            <input type="email" name="email" id="email"
                   class="form-control" value="{{ old('email') }}">
            @error('email')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="two-columns gap mb-4">
          <div class="field">
            <label for="telefono">Teléfono *</label>
            <input type="text" name="telefono" id="telefono"
                   class="form-control" value="{{ old('telefono') }}">
            @error('telefono')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
          <div class="field">
            <label for="direccion">Dirección *</label>
            <input type="text" name="direccion" id="direccion"
                   class="form-control" value="{{ old('direccion') }}">
            @error('direccion')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="two-columns gap">
          <div class="field">
            <label for="empresa">Empresa</label>
            <input type="text" name="empresa" id="empresa"
                   class="form-control" value="{{ old('empresa') }}">
            @error('empresa')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
          <div></div>
        </div>
      </fieldset>

      {{-- 2. Datos del préstamo --}}
      <fieldset class="mb-6">
        <legend>2. Datos del préstamo</legend>

        <div class="two-columns gap mb-4">
          <div class="field">
            <label for="monto_solicitado">Monto Solicitado *</label>
            <div class="input-group">
              <input type="number" name="monto_solicitado" id="monto_solicitado"
                     class="form-control" value="{{ old('monto_solicitado') }}">
              <span class="input-group-text">COP</span>
            </div>
            @error('monto_solicitado')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
          <div class="field">
            <label for="plazo_meses">Plazo (meses) *</label>
            <input type="number" name="plazo_meses" id="plazo_meses"
                   class="form-control" value="{{ old('plazo_meses',12) }}" min="1">
            @error('plazo_meses')
              <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
          </div>
        </div>
      </fieldset>

      {{-- Botón enviar --}}
      <button type="submit" class="btn-next">
        Enviar solicitud ➔
      </button>
    </form>
  </div>
</div>
@endsection
