<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudActualizada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Solicitud $solicitud,
    ) {}

    public function envelope(): Envelope
    {
        $servicio = $this->solicitud->servicio->nombre_servicio ?? 'Servicio';

        return new Envelope(
            subject: 'Solicitud #' . $this->solicitud->id . ' ' . $this->solicitud->estado_solicitud . ' — ' . $servicio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-actualizada',
        );
    }
}
