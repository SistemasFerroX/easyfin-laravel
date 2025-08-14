<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PropuestaEnviadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Solicitud $solicitud) {}

    public function build()
    {
        return $this->subject("Tienes una propuesta para la solicitud #{$this->solicitud->id}")
                    ->markdown('emails.solicitudes.propuesta', [
                        's' => $this->solicitud,
                    ]);
    }
}
