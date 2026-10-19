<?php

namespace App\Mail;

use App\Models\Mensaje;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MensajeNuevo extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Mensaje $mensaje) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso del Departamento de Sistemas: ' . $this->mensaje->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mensaje-nuevo',
        );
    }
}
