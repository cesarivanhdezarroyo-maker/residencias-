<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesPlataformaActualizadas extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $usuarioNuevo,
        public string $passwordNueva,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciales de acceso actualizadas — ' . $this->usuario->name . ' (' . now()->format('d/m/Y H:i') . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales-actualizadas',
        );
    }
}
