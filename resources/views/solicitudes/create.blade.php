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
            <input id="nombre_completo" name="nombre_completo" type="text"
                   value="{{ old('nombre_completo') }}" maxlength="255" required>
            @error('nombre_completo') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="identificacion">Identificación *</label>
            <input id="identificacion" name="identificacion" type="text"
                   inputmode="numeric" pattern="\d+" maxlength="20"
                   value="{{ old('identificacion') }}" required>
            @error('identificacion') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
            <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                   value="{{ old('fecha_nacimiento') }}" required>
            @error('fecha_nacimiento') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="email">Correo Electrónico *</label>
            <input id="email" name="email" type="email" maxlength="255"
                   value="{{ old('email') }}" required>
            @error('email') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="telefono">Teléfono *</label>
            <input id="telefono" name="telefono" type="text"
                   inputmode="numeric" pattern="\d{7,15}" maxlength="15"
                   value="{{ old('telefono') }}" required>
            @error('telefono') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="direccion">Dirección *</label>
            <input id="direccion" name="direccion" type="text" maxlength="255"
                   value="{{ old('direccion') }}" required>
            @error('direccion') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="empresa">Empresa</label>
            <input id="empresa" name="empresa" type="text" maxlength="255"
                   value="{{ old('empresa') }}">
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
                     placeholder="0" data-money data-max="999999999999" required>
              <span class="suffix">COP</span>
            </div>
            {{-- el valor que se envía al back (sin puntos) --}}
            <input type="hidden" name="monto_solicitado" id="monto_solicitado">
            @error('monto_solicitado') <small class="error">{{ $message }}</small> @enderror
            <small class="help">Máximo: 999.999.999.999</small>
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
            <textarea id="observaciones" name="observaciones" rows="3" maxlength="1000"
                      placeholder="Información adicional que quieras compartir...">{{ old('observaciones') }}</textarea>
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
  // Formato de moneda (es-CO) en UI; el hidden envía sólo dígitos + tope máximo
  (function(){
    const ui     = document.getElementById('monto_solicitado_ui');
    const hidden = document.getElementById('monto_solicitado');
    const form   = document.getElementById('solicitudForm');
    if(!ui || !hidden || !form) return;

    const fmt     = new Intl.NumberFormat('es-CO');
    const onlyNum = s => (s||'').replace(/\D+/g,'');
    const MAX     = parseInt(ui.dataset.max || '999999999999', 10);

    if(ui.value.trim()){ ui.value = fmt.format(parseInt(onlyNum(ui.value)||'0',10)); }

    ui.addEventListener('input', e => {
      const raw = onlyNum(e.target.value);
      ui.value = raw ? fmt.format(parseInt(raw,10)) : '';
      ui.setCustomValidity(''); // limpia mensajes previos
    });

    form.addEventListener('submit', (ev) => {
      const raw = onlyNum(ui.value);
      hidden.value = raw;

      // Validaciones básicas del lado cliente
      if(!raw){
        ui.setCustomValidity('Ingresa un monto válido.');
        ui.reportValidity();
        ev.preventDefault();
        return;
      }
      const n = parseInt(raw,10);
      if(n < 1){
        ui.setCustomValidity('El monto debe ser mayor a 0.');
        ui.reportValidity();
        ev.preventDefault();
        return;
      }
      if(n > MAX){
        ui.setCustomValidity('El monto máximo permitido es ' + fmt.format(MAX));
        ui.reportValidity();
        ev.preventDefault();
        return;
      }
      ui.setCustomValidity('');
    });
  })();
</script>
@endpush
