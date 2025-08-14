{{-- resources/views/solicitudes/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('title', 'EasyFin – Mis Solicitudes')

@section('content')
<div class="solicitudes-wrapper">
  {{-- Head --}}
  <header class="page-head">
    <div class="page-title">
      <img src="{{ asset('img/logo-easyfin.png') }}" alt="EasyFin" class="brand-mark">
      <div>
        <h1>{{ ($historial ?? false) ? 'Historial' : 'Mis Solicitudes' }}</h1>
        <p class="muted">
          {{ ($historial ?? false) ? 'Aprobadas y rechazadas' : 'Resumen de tus préstamos pendientes.' }}
        </p>
      </div>
    </div>

    <div class="flex gap-2">
      @role('user')
        <a href="{{ route('solicitudes.create') }}" class="btn-primary">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
            <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
          </svg>
          Nueva solicitud
        </a>
      @endrole

      @if(!($historial ?? false))
        <a class="btn-ghost" href="{{ route('solicitudes.history') }}">Historial</a>
      @else
        <a class="btn-ghost" href="{{ route('solicitudes.index') }}">Ver pendientes</a>
      @endif
    </div>
  </header>

  {{-- Flash messages --}}
  @if (session('success'))
    <div class="alert success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert error">{{ session('error') }}</div>
  @endif

  @php
    // Con paginación, trabajamos sobre la colección de la página actual
    $items = collect($solicitudes->items());

    if (!($historial ?? false)) {
      // Vista de PENDIENTES
      $totalPagina = $solicitudes->total(); // total (todas las páginas) de pendientes
      $propuestasPendientes = $items->filter(fn($s) => $s->propuesta_estado === 'enviada');
    } else {
      // Vista de HISTORIAL (aprobadas + rechazadas)
      $aprobadasPag = $items->where('status','aprobada')->count();
      $rechazadasPag = $items->where('status','rechazada')->count();
      $totalPagina   = $solicitudes->total(); // total historial
    }
  @endphp

  {{-- Stats --}}
  <section class="stats">
    @if(!($historial ?? false))
      <div class="stat-card is-pending">
        <span class="stat-label">Pendientes (total)</span>
        <span class="stat-value">{{ number_format($totalPagina) }}</span>
      </div>
    @else
      <div class="stat-card">
        <span class="stat-label">Historial (total)</span>
        <span class="stat-value">{{ number_format($totalPagina) }}</span>
      </div>
      <div class="stat-card is-approved">
        <span class="stat-label">Aprobadas (esta página)</span>
        <span class="stat-value">{{ $aprobadasPag }}</span>
      </div>
      <div class="stat-card is-rejected">
        <span class="stat-label">Rechazadas (esta página)</span>
        <span class="stat-value">{{ $rechazadasPag }}</span>
      </div>
    @endif
  </section>

  {{-- Filtros (sin status; acción depende de la vista) --}}
  <form method="GET"
        action="{{ ($historial ?? false) ? route('solicitudes.history') : route('solicitudes.index') }}"
        class="filters">
    <div class="field">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o #id…">
    </div>
    <div class="field">
      <select name="order" onchange="this.form.submit()">
        <option value="">Ordenar por</option>
        <option value="recientes"  @selected(request('order')==='recientes')>Más recientes</option>
        <option value="monto_desc" @selected(request('order')==='monto_desc')>Monto ↓</option>
        <option value="monto_asc"  @selected(request('order')==='monto_asc')>Monto ↑</option>
      </select>
    </div>
    <button type="submit" class="btn-ghost">Aplicar</button>
  </form>

  {{-- Propuestas del administrador (sólo en pendientes) --}}
  @role('user')
    @if(!($historial ?? false) && $propuestasPendientes->count() > 0)
      <section class="proposal-section">
        <h3 class="proposal-title">
          Tienes {{ $propuestasPendientes->count() }} propuesta{{ $propuestasPendientes->count()>1?'s':'' }} del administrador
        </h3>

        <div class="cards">
          @foreach($propuestasPendientes as $p)
            <article class="loan-card proposal-card">
              <header class="loan-head">
                <div class="loan-id">
                  <div class="loan-icon">🤝</div>
                  <div>
                    <h4 class="loan-title">Solicitud #{{ $p->id }} – {{ $p->nombre_completo }}</h4>
                    <span class="loan-date">Recibida {{ optional($p->propuesta_enviada_at)->diffForHumans() }}</span>
                  </div>
                </div>
                <span class="badge badge--pending">propuesta</span>
              </header>

              <div class="loan-body">
                <div class="kv">
                  <span class="k">Tu solicitud</span>
                  <span class="v">${{ number_format($p->monto_solicitado,0,',','.') }} · {{ $p->plazo_meses }} meses</span>
                </div>
                <div class="kv">
                  <span class="k">Propuesto por EasyFin</span>
                  <span class="v">${{ number_format($p->propuesta_monto,0,',','.') }} · {{ $p->propuesta_plazo_meses }} meses</span>
                </div>
                <div class="kv">
                  <span class="k">Mensaje</span>
                  <span class="v">{{ $p->propuesta_mensaje ?: '—' }}</span>
                </div>
              </div>

              <footer class="loan-foot">
                <form method="POST" action="{{ route('solicitudes.propuesta.aceptar', $p) }}" class="inline">
                  @csrf
                  <button class="btn-primary">Aceptar propuesta</button>
                </form>
                <form method="POST" action="{{ route('solicitudes.propuesta.rechazar', $p) }}" class="inline ml-2">
                  @csrf
                  <button class="btn-danger" onclick="return confirm('¿Seguro que deseas rechazar esta propuesta?')">Rechazar</button>
                </form>
              </footer>
            </article>
          @endforeach
        </div>
      </section>
    @endif
  @endrole

  {{-- Lista --}}
  @if($solicitudes->total() === 0)
    <section class="empty-state">
      <div class="empty-card">
        <div class="empty-illu" aria-hidden="true">💳</div>
        <h3>{{ ($historial ?? false) ? 'No hay historial' : 'Aún no tienes solicitudes' }}</h3>
        <p>
          {{ ($historial ?? false)
              ? 'Cuando tengas solicitudes aprobadas o rechazadas aparecerán aquí.'
              : 'Crea tu primera solicitud y haz seguimiento aquí.' }}
        </p>
        @role('user')
          @unless($historial ?? false)
            <a href="{{ route('solicitudes.create') }}" class="btn-primary">Crear solicitud</a>
          @endunless
        @endrole
      </div>
    </section>
  @else
    <section class="cards">
      @foreach($items as $s)
        @php
          $estado = strtolower($s->status ?? 'pendiente');
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
              @if($s->propuesta_estado === 'enviada')
                <span class="hint">Tienes una propuesta pendiente de respuesta.</span>
              @else
                <span class="hint">Tu solicitud está en revisión.</span>
              @endif
            @elseif($estado === 'aprobada')
              <span class="hint success">¡Aprobada! Pronto te contactaremos.</span>
            @else
              <span class="hint danger">Rechazada. Puedes crear una nueva solicitud.</span>
            @endif
          </footer>
        </article>
      @endforeach
    </section>

    <div class="pagination mt-3">
      {{ $solicitudes->links() }}
    </div>
  @endif
</div>
@endsection
