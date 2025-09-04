{{-- resources/views/admin/solicitudes/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('title','EasyFin – Solicitudes (Admin)')

@section('content')
<div class="solicitudes-wrapper">
  <header class="page-head">
    <div class="page-title">
      <img src="{{ asset('img/logo-easyfin.png') }}" class="brand-mark" alt="EasyFin">
      <div>
        <h1>{{ ($historial ?? false) ? 'Historial de solicitudes' : 'Solicitudes pendientes' }}</h1>
        <p class="muted">Panel del administrador</p>
      </div>
    </div>

    @if(!($historial ?? false))
      <a class="btn-ghost" href="{{ route('admin.solicitudes.history') }}">Historial</a>
    @else
      <a class="btn-ghost" href="{{ route('admin.solicitudes.index') }}">Ver pendientes</a>
    @endif
  </header>

  @if(session('success')) <div class="alert success">{{ session('success') }}</div> @endif
  @if($errors->any())     <div class="alert error">Revisa los datos del formulario.</div> @endif

  {{-- Filtros --}}
  <form method="GET"
        action="{{ ($historial ?? false) ? route('admin.solicitudes.history') : route('admin.solicitudes.index') }}"
        class="filters">
    <div class="field">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, correo o #id">
    </div>
    <div class="field">
      <select name="order" onchange="this.form.submit()">
        <option value="recientes"  @selected(request('order')==='recientes')>Más recientes</option>
        <option value="monto_desc" @selected(request('order')==='monto_desc')>Monto ↓</option>
        <option value="monto_asc"  @selected(request('order')==='monto_asc')>Monto ↑</option>
      </select>
    </div>
    <button class="btn-ghost" type="submit">Aplicar</button>
  </form>

  {{-- Lista --}}
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
          <button class="btn-primary" data-open="#m{{ $s->id }}">Ver / Gestionar</button>
        </footer>
      </article>

      {{-- MODAL --}}
      <div id="m{{ $s->id }}" class="modal-backdrop hidden" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="mdl-title-{{ $s->id }}">
          {{-- Header --}}
          <div class="modal-head">
            <h3 id="mdl-title-{{ $s->id }}" class="modal-title">Solicitud <span class="muted">#{{ $s->id }}</span></h3>
            <button class="btn-ghost rounded-full px-4" data-close="#m{{ $s->id }}">Cerrar</button>
          </div>

          {{-- Body --}}
          <div class="modal-body">
            <div class="info-grid">
              <div class="info-card">
                <h4>Solicitante</h4>
                <ul class="info-list">
                  <li><b>Nombre:</b> {{ $s->nombre_completo }}</li>
                  <li><b>Tipo doc.:</b> {{ strtoupper($s->tipo_documento ?? '—') }}</li>
                  <li><b>Identificación:</b> {{ $s->identificacion }}</li>
                  <li><b>Correo:</b> {{ $s->email }}</li>
                  <li><b>Teléfono:</b> {{ $s->telefono }}</li>
                  <li><b>Dirección:</b> {{ $s->direccion }}</li>
                  <li><b>Empresa:</b> {{ $s->empresa ?: '—' }}</li>
                  @if($s->empresa_nit)
                    <li><b>NIT:</b> {{ $s->empresa_nit }}</li>
                  @endif
                  <li><b>Nacimiento:</b> {{ optional($s->fecha_nacimiento)->format('Y-m-d') }}</li>
                </ul>
              </div>

              <div class="info-card">
                <h4>Solicitud</h4>
                <ul class="info-list">
                  <li><b>Monto:</b> ${{ number_format($s->monto_solicitado,0,',','.') }}</li>
                  <li><b>Plazo:</b> {{ $s->plazo_meses }} meses</li>
                  <li><b>Estado:</b> {{ ucfirst($s->status) }}</li>
                  <li><b>Tasa:</b>
                    @if(!is_null($s->tasa_interes))
                      {{ rtrim(rtrim(number_format($s->tasa_interes,2,',','.'),'0'),',') }}% EA
                    @else — @endif
                  </li>
                </ul>
                @if($s->observaciones)
                  <div class="note"><b>Obs.:</b> {{ $s->observaciones }}</div>
                @endif
              </div>

              {{-- Documentos del usuario (si existen) --}}
              @if($s->doc_cedula_path || $s->cert_bancario_path)
                <div class="info-card">
                  <h4>Documentos del usuario</h4>
                  <div class="actions-inline">
                    @if($s->doc_cedula_path)
                      <a class="btn-ghost" href="{{ Storage::url($s->doc_cedula_path) }}" target="_blank" rel="noopener">
                        Cédula (PDF)
                      </a>
                    @endif
                    @if($s->cert_bancario_path)
                      <a class="btn-ghost" href="{{ Storage::url($s->cert_bancario_path) }}" target="_blank" rel="noopener">
                        Certificado bancario (PDF)
                      </a>
                    @endif
                  </div>
                </div>
              @endif

              {{-- Documento interno del admin (subida/visualización) --}}
              <div class="info-card">
                <h4>Documento interno (Admin)</h4>

                @if($s->admin_pdf_path)
                  <p class="mb-2">
                    <a class="btn-ghost" href="{{ Storage::url($s->admin_pdf_path) }}" target="_blank" rel="noopener">
                      Ver PDF adjunto
                    </a>
                  </p>
                @endif

                <form method="POST"
                      action="{{ route('admin.solicitudes.adminpdf', $s) }}"
                      enctype="multipart/form-data" class="actions-inline">
                  @csrf
                  <input type="file" name="admin_pdf" accept="application/pdf" required>
                  <button class="btn-primary" type="submit">
                    {{ $s->admin_pdf_path ? 'Reemplazar PDF' : 'Subir PDF' }}
                  </button>
                </form>
                <small class="muted">Solo visible para administradores.</small>
              </div>

              {{-- Documentos de aprobación (amortización / certificado) --}}
              @if($estado === 'aprobada')
                <div class="info-card">
                  <h4>Documentos de aprobación</h4>

                  @if($s->amortizacion_pdf_path || $s->certificado_pdf_path)
                    <div class="actions-inline">
                      @if($s->amortizacion_pdf_path)
                        <a class="btn-ghost"
                           href="{{ route('admin.solicitudes.pdf.amortizacion', $s) }}"
                           target="_blank" rel="noopener">
                          Amortización PDF
                        </a>
                      @endif

                      @if($s->certificado_pdf_path)
                        <a class="btn-ghost"
                           href="{{ route('admin.solicitudes.pdf.certificado', $s) }}"
                           target="_blank" rel="noopener">
                          Certificado PDF
                        </a>
                      @endif
                    </div>
                  @else
                    <p class="muted">Generando documentos…</p>
                  @endif

                  @if($s->fecha_aprobacion)
                    <small class="muted">Aprobada el {{ optional($s->fecha_aprobacion)->format('d/m/Y H:i') }}</small>
                  @endif
                </div>
              @endif
            </div>

            {{-- Contraoferta (solo pendiente) --}}
            @if($estado === 'pendiente')
              <form id="offer-{{ $s->id }}-form" method="POST" action="{{ route('admin.solicitudes.counter',$s) }}" class="offer-form">
                @csrf
                <h4 class="section-title">Enviar contraoferta</h4>
                <div class="form-grid">
                  <div class="form-field">
                    <label>Monto propuesto</label>
                    <input type="number" name="propuesta_monto"
                           step="1" min="1" max="999999999999"
                           value="{{ old('propuesta_monto', $s->propuesta_monto ?? $s->monto_solicitado) }}">
                  </div>
                  <div class="form-field">
                    <label>Plazo (meses)</label>
                    <input type="number" name="propuesta_plazo_meses"
                           step="1" min="1" max="360"
                           value="{{ old('propuesta_plazo_meses', $s->propuesta_plazo_meses ?? $s->plazo_meses) }}">
                  </div>
                  <div class="form-field col-span-2">
                    <label>Mensaje</label>
                    <input type="text" name="propuesta_mensaje" maxlength="1000"
                           value="{{ old('propuesta_mensaje', $s->propuesta_mensaje) }}"
                           placeholder="Ej.: no es viable por 10M a 12m, pero podemos 6M a 12m o 10M a 15m">
                  </div>
                </div>

                <div class="actions-inline">
                  <button class="btn-primary" type="submit">Enviar propuesta</button>
                </div>

                @if($s->propuesta_estado)
                  <div class="proposal-meta">
                    <b>Propuesta:</b> {{ $s->propuesta_estado }}
                    @if($s->propuesta_enviada_at)
                      · {{ $s->propuesta_enviada_at->diffForHumans() }}
                    @endif
                  </div>
                @endif
              </form>
            @endif
          </div>

          {{-- Footer: Aprobar / Rechazar (solo pendiente) --}}
          <div class="modal-footer">
            <div class="footer-actions">
              @if($estado === 'pendiente')
                <form method="POST" action="{{ route('admin.solicitudes.approve',$s) }}"
                      onsubmit="return confirm('¿Aprobar esta solicitud?')">
                  @csrf
                  <button class="btn-ghost" type="submit">Aprobar</button>
                </form>

                <form method="POST" action="{{ route('admin.solicitudes.reject',$s) }}"
                      onsubmit="return confirm('¿Rechazar esta solicitud?')">
                  @csrf
                  <button class="btn-danger" type="submit">Rechazar</button>
                </form>
              @else
                <small class="muted">Esta solicitud ya fue {{ strtolower($s->status) }}.</small>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <section class="empty-state">
        <div class="empty-card">
          <div class="empty-illu">📭</div>
          <h3>No hay solicitudes</h3>
          <p>{{ ($historial ?? false)
                ? 'Cuando existan aprobadas o rechazadas aparecerán aquí.'
                : 'Cuando los usuarios creen solicitudes pendientes aparecerán aquí.' }}</p>
        </div>
      </section>
    @endforelse
  </section>

  <div class="pagination mt-3">
    {{ $solicitudes->links() }}
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Modales: abrir/cerrar
  document.addEventListener('click', (e) => {
    const openSel  = e.target.closest('[data-open]');
    const closeSel = e.target.closest('[data-close]');
    if (openSel)  { const el = document.querySelector(openSel.dataset.open);  if (el) el.classList.remove('hidden'); }
    if (closeSel) { const el = document.querySelector(closeSel.dataset.close); if (el) el.classList.add('hidden');    }
  });
  // Cerrar al hacer click fuera del card
  document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', (e) => { if (e.target === b) b.classList.add('hidden'); });
  });
</script>
@endpush
