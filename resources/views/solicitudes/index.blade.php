{{-- resources/views/solicitudes/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('title','EasyFin – Mis Solicitudes')

@section('content')
<div class="solicitudes-wrapper">
  <header class="page-head">
    <div class="page-title">
      <img src="{{ asset('img/logo-easyfin.png') }}" class="brand-mark" alt="EasyFin">
      <div>
        <h1>{{ ($historial ?? false) ? 'Mi historial de solicitudes' : 'Mis solicitudes' }}</h1>
        <p class="muted">Aquí puedes ver tus préstamos y su estado.</p>
      </div>
    </div>

    {{-- IMPORTANTÍSIMO: rutas de USUARIO --}}
    @if(!($historial ?? false))
      <a class="btn-ghost" href="{{ route('solicitudes.history') }}">Historial</a>
    @else
      <a class="btn-ghost" href="{{ route('solicitudes.index') }}">Ver pendientes</a>
    @endif
  </header>

  {{-- filtros simples opcionales --}}
  <form method="GET" action="{{ ($historial ?? false) ? route('solicitudes.history') : route('solicitudes.index') }}" class="filters">
    <div class="field">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o #id">
    </div>
    <div class="field">
      <select name="order" onchange="this.form.submit()">
        <option value="">Ordenar</option>
        <option value="recientes"  @selected(request('order')==='recientes')>Más recientes</option>
        <option value="monto_desc" @selected(request('order')==='monto_desc')>Monto ↓</option>
        <option value="monto_asc"  @selected(request('order')==='monto_asc')>Monto ↑</option>
      </select>
    </div>
    <button class="btn-ghost" type="submit">Aplicar</button>
  </form>

  <section class="cards">
    @forelse($solicitudes as $s)
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
            <div class="loan-icon">📄</div>
            <div>
              <h4 class="loan-title">#{{ $s->id }} — {{ $s->nombre_completo }}</h4>
              <span class="loan-date">{{ optional($s->created_at)->locale('es')->translatedFormat('d M Y, H:i') }}</span>
            </div>
          </div>
          <span class="{{ $badgeClass }}">{{ ucfirst($estado) }}</span>
        </header>

        <div class="loan-body">
          <div class="kv"><span class="k">Monto</span><span class="v">${{ number_format($s->monto_solicitado,0,',','.') }}</span></div>
          <div class="kv"><span class="k">Plazo</span><span class="v">{{ $s->plazo_meses }} meses</span></div>
          <div class="kv">
            <span class="k">Tasa</span>
            <span class="v">
              @if(!is_null($s->tasa_interes))
                {{ rtrim(rtrim(number_format($s->tasa_interes,2,',','.'),'0'),',') }}% EA
              @else — @endif
            </span>
          </div>
        </div>

        <footer class="loan-foot">
          {{-- El USUARIO solo puede descargar su PDF de amortización si está aprobada --}}
          @if($estado === 'aprobada' && $s->amortizacion_pdf_path)
            <a class="btn-primary"
               href="{{ route('solicitudes.pdf.amortizacion', $s) }}"
               target="_blank" rel="noopener">
              Descargar amortización
            </a>
          @endif
        </footer>
      </article>
    @empty
      <section class="empty-state">
        <div class="empty-card">
          <div class="empty-illu">📭</div>
          <h3>No hay solicitudes</h3>
          <p>{{ ($historial ?? false)
                ? 'Cuando tengas solicitudes aprobadas o rechazadas aparecerán aquí.'
                : 'Cuando crees solicitudes pendientes aparecerán aquí.' }}</p>
        </div>
      </section>
    @endforelse
  </section>

  <div class="pagination mt-3">
    {{ $solicitudes->links() }}
  </div>
</div>
@endsection
