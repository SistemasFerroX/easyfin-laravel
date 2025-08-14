<?php

// app/Mail/SolicitudRechazadaMail.php
namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudRechazadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Solicitud $solicitud, public bool $esAdmin = false) {}

    public function build()
    {
        $asunto = $this->esAdmin
            ? "Rechazaste la solicitud #{$this->solicitud->id}"
            : "Tu solicitud #{$this->solicitud->id} fue rechazada";

        return $this->subject($asunto)
                    ->markdown('emails.solicitudes.rechazada', [
                        's'       => $this->solicitud,
                        'esAdmin' => $this->esAdmin,
                    ]);
    }
}
