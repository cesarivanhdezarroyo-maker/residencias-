<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = [
        'usuario_id', 'solicitud_id', 'de_admin', 'asunto', 'cuerpo',
        'requiere_confirmacion', 'leido_at', 'confirmado_at', 'correo_enviado',
    ];

    protected $casts = [
        'cuerpo' => 'encrypted',
        'de_admin' => 'boolean',
        'requiere_confirmacion' => 'boolean',
        'correo_enviado' => 'boolean',
        'leido_at' => 'datetime',
        'confirmado_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }
}