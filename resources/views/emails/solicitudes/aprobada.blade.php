
{{-- resources/views/emails/solicitudes/aprobada.blade.php --}}
@component('mail::message')
# {{ $esAdmin ? 'Aprobación realizada' : '¡Solicitud aprobada!' }}

@component('mail::panel')
**Solicitud #{{ $s->id }}**  
Monto: ${{ number_format($s->monto_solicitado,0,',','.') }}  
Plazo: {{ $s->plazo_meses }} meses
@endcomponent

@if(!$esAdmin)
Te contactaremos para el desembolso.  
@else
¡Bien hecho! Se notificó al usuario.
@endif

@endcomponent
