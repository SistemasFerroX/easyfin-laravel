{{-- resources/views/solicitudes/create.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Formulario de Solicitud de Préstamo
  </h2>
@endsection

@section('content')
  <div class="container py-5">
    <div class="card shadow rounded">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Ingrese los datos de su préstamo</h4>
      </div>
      <div class="card-body">
        {{-- Errores --}}
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Éxito --}}
        @if (session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        {{-- FORMULARIO --}}
        <form
          id="solicitudForm"
          action="{{ route('solicitudes.store') }}"
          method="POST"
          novalidate
          onsubmit="document.getElementById('monto_solicitado').value =
                       document.getElementById('monto_solicitado').value.replace(/\D/g,'');"
        >
          @csrf

          <h5 class="mt-3">1. Datos Personales</h5>
          <div class="row g-3">
            {{-- Nombre Completo --}}
            <div class="col-md-6">
              <label for="nombre_completo" class="form-label">Nombre Completo *</label>
              <input 
                type="text"
                id="nombre_completo"
                name="nombre_completo"
                class="form-control @error('nombre_completo') is-invalid @enderror"
                value="{{ old('nombre_completo') }}"
                required
              >
              @error('nombre_completo')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Identificación --}}
            <div class="col-md-6">
              <label for="identificacion" class="form-label">Identificación *</label>
              <input 
                type="text"
                id="identificacion"
                name="identificacion"
                class="form-control @error('identificacion') is-invalid @enderror"
                value="{{ old('identificacion') }}"
                inputmode="numeric"
                pattern="\d*"
                required
              >
              @error('identificacion')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Fecha de Nacimiento --}}
            <div class="col-md-6">
              <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
              <input 
                type="date"
                id="fecha_nacimiento"
                name="fecha_nacimiento"
                class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                value="{{ old('fecha_nacimiento') }}"
                required
              >
              @error('fecha_nacimiento')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Correo Electrónico --}}
            <div class="col-md-6">
              <label for="email" class="form-label">Correo Electrónico *</label>
              <input 
                type="email"
                id="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                required
              >
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Teléfono --}}
            <div class="col-md-6">
              <label for="telefono" class="form-label">Teléfono *</label>
              <input 
                type="text"
                id="telefono"
                name="telefono"
                class="form-control @error('telefono') is-invalid @enderror"
                value="{{ old('telefono') }}"
                inputmode="numeric"
                pattern="\d*"
                required
              >
              @error('telefono')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Dirección --}}
            <div class="col-md-6">
              <label for="direccion" class="form-label">Dirección *</label>
              <input 
                type="text"
                id="direccion"
                name="direccion"
                class="form-control @error('direccion') is-invalid @enderror"
                value="{{ old('direccion') }}"
                required
              >
              @error('direccion')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <h5>2. Datos del Préstamo</h5>
          <div class="row g-3"> {{-- sin align-items-end --}}
            {{-- Monto Solicitado --}}
            <div class="col-md-6">
              <label for="monto_solicitado" class="form-label">Monto Solicitado *</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input 
                  type="text"
                  id="monto_solicitado"
                  name="monto_solicitado"
                  class="form-control @error('monto_solicitado') is-invalid @enderror"
                  value="{{ old('monto_solicitado') }}"
                  inputmode="numeric"
                  pattern="\d*"
                  required
                >
                <span class="input-group-text">COP</span>
              </div>
              @error('monto_solicitado')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            {{-- Plazo Sugerido --}}
            <div class="col-md-6">
              <label for="plazo_meses" class="form-label">Plazo Sugerido (meses)</label>
              <input 
                type="number"
                id="plazo_meses"
                name="plazo_meses"
                class="form-control @error('plazo_meses') is-invalid @enderror"
                value="{{ old('plazo_meses', 12) }}"
                min="1"
                required
              >
              @error('plazo_meses')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="alert alert-info mt-4">
            <strong>Tasa de interés fija:</strong> 2.2%
            <input type="hidden" name="tasa_interes" value="2.2">
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-success px-4">
              Enviar Solicitud
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('monto_solicitado');

    // Sólo formatea mientras escribes
    input.addEventListener('input', e => {
      let v = e.target.value.replace(/\D/g, '');
      e.target.value = v ? new Intl.NumberFormat('es-CO').format(v) : '';
    });
  });
</script>
@endpush
