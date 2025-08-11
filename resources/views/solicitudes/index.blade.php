{{-- resources/views/solicitudes/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('title', 'EasyFin – Mis Solicitudes')

@section('content')
<div class="solicitudes-wrapper">
  <header class="page-head">
    <div class="page-title">
      <img src="{{ asset('img/logo-easyfin.png') }}" alt="EasyFin" class="brand-mark">
      <div>
        <h1>Mis Solicitudes</h1>
        <p class="muted">Resumen de tus préstamos y su estado actual.</p>
      </div>
    </div>

    @role('user')
      <a href="{{ route('solicitudes.create') }}" class="btn-primary">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
        Nueva solicitud
      </a>
    @endrole
  </header>

  @php
    $pendientes = $solicitudes->where('status','pendiente')->count();
    $aprobadas  = $solicitudes->where('status','aprobada')->count();
    $rechazadas = $solicitudes->where('status','rechazada')->count();
    $total      = $solicitudes->count();
  @endphp

  <section class="stats">
    <div class="stat-card"><span class="stat-label">Totales</span><span class="stat-value">{{ $total }}</span></div>
    <div class="stat-card is-pending"><span class="stat-label">Pendientes</span><span class="stat-value">{{ $pendientes }}</span></div>
    <div class="stat-card is-approved"><span class="stat-label">Aprobadas</span><span class="stat-value">{{ $aprobadas }}</span></div>
    <div class="stat-card is-rejected"><span class="stat-label">Rechazadas</span><span class="stat-value">{{ $rechazadas }}</span></div>
  </section>

  <form method="GET" action="{{ route('solicitudes.index') }}" class="filters">
    <div class="field">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o referencia…">
    </div>
    <div class="field">
      <select name="status" onchange="this.form.submit()">
        <option value="">Todos los estados</option>
        <option value="pendiente" {{ request('status')==='pendiente'?'selected':'' }}>Pendiente</option>
        <option value="aprobada"  {{ request('status')==='aprobada'?'selected':'' }}>Aprobada</option>
        <option value="rechazada" {{ request('status')==='rechazada'?'selected':'' }}>Rechazada</option>
      </select>
    </div>
    <div class="field">
      <select name="order" onchange="this.form.submit()">
        <option value="">Ordenar por</option>
        <option value="recientes"  {{ request('order')==='recientes'?'selected':'' }}>Más recientes</option>
        <option value="monto_desc" {{ request('order')==='monto_desc'?'selected':'' }}>Monto ↓</option>
        <option value="monto_asc"  {{ request('order')==='monto_asc'?'selected':''  }}>Monto ↑</option>
      </select>
    </div>
    <button type="submit" class="btn-ghost">Aplicar</button>
  </form>

  @if($solicitudes->count() === 0)
    <section class="empty-state">
      <div class="empty-card">
        <div class="empty-illu" aria-hidden="true">💳</div>
        <h3>Aún no tienes solicitudes</h3>
        <p>Crea tu primera solicitud y haz seguimiento aquí.</p>
        @role('user')
          <a href="{{ route('solicitudes.create') }}" class="btn-primary">Crear solicitud</a>
        @endrole
      </div>
    </section>
  @else
    <section class="cards">
      @foreach($solicitudes as $s)
        @php
          $estadoRaw = $s->status ?? 'pendiente';
          $estado = strtolower($estadoRaw);
          $badgeClass = match($estado) {
            'aprobada'  => 'badge badge--approved',
            'rechazada' => 'badge badge--rejected',
            default     => 'badge badge--pending',
          };
        @endphp

        <article class="loan-card">
          <header class="loan-head">
            <div class="loan-id">
              <div class="loan-icon" aria-hidden="true">📄</div>
              <div>
                <h4 class="loan-title">{{ $s->nombre_completo ?? 'Solicitante' }}</h4>
                <span class="loan-date">{{ optional($s->created_at)->locale('es')->translatedFormat('d M Y, H:i') }}</span>
              </div>
            </div>
            <span class="{{ $badgeClass }}">{{ $estado }}</span>
          </header>

          <div class="loan-body">
            <div class="kv">
              <span class="k">Monto</span>
              <span class="v">${{ number_format($s->monto_solicitado ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="kv">
              <span class="k">Plazo</span>
              <span class="v">{{ $s->plazo_meses ?? '—' }} meses</span>
            </div>
            <div class="kv">
              <span class="k">Tasa</span>
              <span class="v">
                {{ isset($s->tasa_interes) ? rtrim(rtrim(number_format($s->tasa_interes, 2, ',', '.'), '0'), ',') . '%' : '—' }}
              </span>
            </div>
          </div>

          <footer class="loan-foot">
            @if($estado === 'pendiente')
              <span class="hint">Tu solicitud está en revisión.</span>
            @elseif($estado === 'aprobada')
              <span class="hint success">¡Aprobada! Pronto te contactaremos.</span>
            @else
              <span class="hint danger">Rechazada. Puedes crear una nueva solicitud.</span>
            @endif
          </footer>
        </article>
      @endforeach
    </section>
  @endif
</div>
@endsection
