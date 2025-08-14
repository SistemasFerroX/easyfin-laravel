@component('mail::message')
# Tienes una propuesta de EasyFin

Hemos revisado tu **solicitud #{{ $s->id }}** y te enviamos una propuesta:

@component('mail::panel')
**Tu solicitud:**  
- Monto: ${{ number_format($s->monto_solicitado, 0, ',', '.') }}  
- Plazo: {{ $s->plazo_meses }} meses

**Propuesta de EasyFin:**  
- Monto: ${{ number_format($s->propuesta_monto, 0, ',', '.') }}  
- Plazo: {{ $s->propuesta_plazo_meses }} meses

**Mensaje:** {{ $s->propuesta_mensaje ?: '—' }}
@endcomponent

Para **aceptar o rechazar** la propuesta, ingresa a tu panel:

@component('mail::button', ['url' => route('solicitudes.index')])
Revisar propuesta
@endcomponent

> Nota: La respuesta debe hacerse desde la plataforma por seguridad.

Gracias,  
**EasyFin**
@endcomponent
