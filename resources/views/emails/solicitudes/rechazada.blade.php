@component('mail::message')
# {{ $esAdmin ? 'Rechazo registrado' : 'Solicitud rechazada' }}

@component('mail::panel')
**Solicitud #{{ $s->id }}**  
Monto: ${{ number_format($s->monto_solicitado, 0, ',', '.') }}  
Plazo: {{ $s->plazo_meses }} meses  
Estado actual: **rechazada**
@endcomponent

@if($esAdmin)
Has rechazado la solicitud del usuario **{{ $s->nombre_completo }}**.  
Si fue por política o riesgo, no olvides registrar la justificación interna.

@else
Lamentamos informarte que tu solicitud fue rechazada.  
Si deseas, puedes crear una nueva solicitud con condiciones diferentes.

@endif

@component('mail::button', ['url' => route('solicitudes.index')])
Ir a mis solicitudes
@endcomponent

Gracias,  
**EasyFin**
@endcomponent
