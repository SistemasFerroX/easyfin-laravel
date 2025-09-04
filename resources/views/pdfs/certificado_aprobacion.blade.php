<!doctype html><html lang="es"><head><meta charset="utf-8">
<title>Certificado de aprobación #{{ $solicitud->id }}</title>
<style>
 body{font-family:DejaVu Sans,sans-serif;color:#111827}
 .box{border:2px solid #1f2937;border-radius:10px;padding:18px}
 h1{font-size:20px;margin:0 0 10px}
 .muted{color:#6b7280}
 .row{margin:6px 0}
 .sign{margin-top:28px;display:flex;gap:40px}
 .sign .field{flex:1}
 .line{border-top:1px solid #111827;height:2px;margin-top:45px}
 .label{font-size:12px;color:#374151;margin-top:4px}
</style></head><body>
<img src="{{ public_path('img/logo-easyfin.png') }}" height="28" alt="EasyFin">
<div class="box">
  <h1>Certificado de Aprobación</h1>
  <div class="row">Solicitud <b>#{{ $solicitud->id }}</b> aprobada el <b>{{ optional($solicitud->fecha_aprobacion)->format('d/m/Y H:i') }}</b>.</div>
  <div class="row"><b>Solicitante:</b> {{ $solicitud->nombre_completo }} ({{ $solicitud->identificacion }})</div>
  <div class="row"><b>Monto:</b> ${{ number_format($solicitud->monto_solicitado,0,',','.') }}</div>
  <div class="row"><b>Plazo:</b> {{ $solicitud->plazo_meses }} meses</div>
  <div class="row"><b>Tasa mensual:</b> {{ rtrim(rtrim(number_format($solicitud->tasa_interes,2,',','.'),'0'),',') }}%</div>
  <div class="row muted">Este documento certifica la aprobación del préstamo bajo las condiciones indicadas.</div>

  <div class="sign">
    <div class="field">
      <div class="line"></div>
      <div class="label">Firma y sello EASYFIN</div>
    </div>
    <div class="field">
      <div class="line"></div>
      <div class="label">Firma del Solicitante</div>
    </div>
  </div>
</div>
<p class="muted">Generado el {{ now()->format('d/m/Y H:i') }}.</p>
</body></html>
