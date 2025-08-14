<?php
// app/Mail/SolicitudAprobadaMail.php (similar para Rechazada/Propuesta)
namespace App\Mail;
use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudAprobadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Solicitud $solicitud, public bool $esAdmin = false) {}

    public function build()
    {
        $asunto = $this->esAdmin ? "Aprobaste la solicitud #{$this->solicitud->id}" : "¡Tu solicitud #{$this->solicitud->id} fue aprobada!";
        return $this->subject($asunto)
                    ->markdown('emails.solicitudes.aprobada', ['s'=>$this->solicitud,'esAdmin'=>$this->esAdmin]);
    }
}
