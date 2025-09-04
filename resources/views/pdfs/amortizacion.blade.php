@php $fmt = fn($v) => '$'.number_format($v,0,',','.'); @endphp
<!doctype html><html lang="es"><head><meta charset="utf-8">
<title>Amortización #{{ $solicitud->id }}</title>
<style>
  body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#1f2937}
  h1{font-size:18px;margin:0 0 6px}
  .muted{color:#6b7280}
  .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #e5e7eb;padding:6px;text-align:right}
  th{background:#f3f4f6} td.left,th.left{text-align:left}
</style></head><body>
<div class="header">
  <div>
    <h1>Tabla de amortización</h1>
    <div class="muted">Solicitud #{{ $solicitud->id }} · {{ $solicitud->nombre_completo }}</div>
  </div>
  <img src="{{ public_path('img/logo-easyfin.png') }}" height="26" alt="EasyFin">
</div>

<p><b>Monto:</b> {{ $fmt($solicitud->monto_solicitado) }} ·
<b>Plazo:</b> {{ $solicitud->plazo_meses }} meses ·
<b>Tasa mensual:</b> {{ rtrim(rtrim(number_format($solicitud->tasa_interes,2,',','.'),'0'),',') }}%</p>

<p><b>Cuota estimada:</b> {{ $fmt($cuota) }}</p>

<table>
  <thead><tr>
    <th>#</th><th class="left">Fecha</th><th>Cuota</th><th>Interés</th><th>Capital</th><th>Saldo</th>
  </tr></thead>
  <tbody>
    @foreach($rows as $r)
    <tr>
      <td>{{ $r['n'] }}</td>
      <td class="left">{{ \Carbon\Carbon::parse($r['fecha'])->format('d/m/Y') }}</td>
      <td>{{ $fmt($r['cuota']) }}</td>
      <td>{{ $fmt($r['interes']) }}</td>
      <td>{{ $fmt($r['capital']) }}</td>
      <td>{{ $fmt($r['saldo']) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<p class="muted">Generado el {{ now()->format('d/m/Y H:i') }}.</p>
</body></html>
