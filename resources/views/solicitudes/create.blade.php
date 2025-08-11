{{-- resources/views/solicitudes/create.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitud.css') }}">
@endpush

@section('title', 'EasyFin – Nueva Solicitud')

@section('content')
<div class="solicitud-wrapper">
  <div class="form-card">
    <header class="form-head">
      <div class="head-left">
        <div class="pill">Formulario</div>
        <h1>Ingresa tu solicitud</h1>
        <p class="muted">Completa tus datos y el monto a solicitar. ¡Es rápido y claro!</p>
      </div>
      <a href="{{ route('solicitudes.index') }}" class="btn-ghost">← Volver</a>
    </header>

    @if ($errors->any())
      <div class="alert error"><strong>Revisa el formulario:</strong> hay campos por corregir.</div>
    @endif
    @if (session('status'))
      <div class="alert success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('solicitudes.store') }}" id="solicitudForm" novalidate>
      @csrf

      {{-- 1. Datos personales --}}
      <section class="section">
        <div class="section-head">
          <div class="section-icon" aria-hidden="true">👤</div>
          <h2>1. Datos personales</h2>
        </div>

        <div class="grid-2">
          <div class="field">
            <label for="nombre_completo">Nombre Completo *</label>
            <input id="nombre_completo" name="nombre_completo" type="text" value="{{ old('nombre_completo') }}" required>
            @error('nombre_completo') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="identificacion">Identificación *</label>
            <input id="identificacion" name="identificacion" type="text" value="{{ old('identificacion') }}" required>
            @error('identificacion') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
            <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}" required>
            @error('fecha_nacimiento') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="email">Correo Electrónico *</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            @error('email') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="telefono">Teléfono *</label>
            <input id="telefono" name="telefono" type="text" value="{{ old('telefono') }}" required>
            @error('telefono') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="direccion">Dirección *</label>
            <input id="direccion" name="direccion" type="text" value="{{ old('direccion') }}" required>
            @error('direccion') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="empresa">Empresa</label>
            <input id="empresa" name="empresa" type="text" value="{{ old('empresa') }}">
            @error('empresa') <small class="error">{{ $message }}</small> @enderror
          </div>
        </div>
      </section>

      {{-- 2. Datos del préstamo --}}
      <section class="section">
        <div class="section-head">
          <div class="section-icon" aria-hidden="true">💳</div>
          <h2>2. Datos del préstamo</h2>
        </div>

        <div class="grid-2">
          <div class="field">
            <label for="monto_solicitado_ui">Monto Solicitado *</label>
            <div class="input-group">
              <input id="monto_solicitado_ui" type="text" inputmode="numeric" autocomplete="off"
                     value="{{ old('monto_solicitado') ? number_format(old('monto_solicitado'), 0, ',', '.') : '' }}"
                     placeholder="0" data-money required>
              <span class="suffix">COP</span>
            </div>
            {{-- el valor que se envía al back (sin puntos) --}}
            <input type="hidden" name="monto_solicitado" id="monto_solicitado">
            @error('monto_solicitado') <small class="error">{{ $message }}</small> @enderror
            <small class="help">Ej: 20.000.000</small>
          </div>

          <div class="field">
            <label for="plazo_meses">Plazo (meses) *</label>
            <div class="input-group">
              <select id="plazo_meses" name="plazo_meses" required>
                @foreach([6,9,12,18,24,36,48,60] as $m)
                  <option value="{{ $m }}" {{ old('plazo_meses', 12)==$m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
              </select>
              <span class="suffix">meses</span>
            </div>
            @error('plazo_meses') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field span-2">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Información adicional que quieras compartir...">{{ old('observaciones') }}</textarea>
            @error('observaciones') <small class="error">{{ $message }}</small> @enderror
          </div>
        </div>
      </section>

      <footer class="form-actions">
        <a href="{{ route('solicitudes.index') }}" class="btn-ghost">Cancelar</a>
        <button type="submit" class="btn-primary">Enviar solicitud</button>
      </footer>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Formato de moneda (es-CO) en UI; el hidden envía sólo dígitos
  (function(){
    const ui = document.getElementById('monto_solicitado_ui');
    const hidden = document.getElementById('monto_solicitado');
    const form = document.getElementById('solicitudForm');
    if(!ui || !hidden || !form) return;

    const fmt = new Intl.NumberFormat('es-CO');
    const onlyNums = s => (s||'').replace(/\D+/g,'');

    if(ui.value.trim()){ ui.value = fmt.format(parseInt(onlyNums(ui.value))); }

    ui.addEventListener('input', e => {
      const raw = onlyNums(e.target.value);
      ui.value = raw ? fmt.format(parseInt(raw,10)) : '';
    });

    form.addEventListener('submit', () => {
      hidden.value = onlyNums(ui.value);
    });
  })();
</script>
@endpush
