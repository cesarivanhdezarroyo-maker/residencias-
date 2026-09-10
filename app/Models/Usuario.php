<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    // Le decimos a Laravel qué tabla usar exactamente
    protected $table = 'usuarios';

    // Los campos que el usuario puede llenar en el formulario
    protected $fillable = [
        'nombres',
        'apellidos',
        'numero_empleado',
        'correo_personal',
        'puesto_id',
        'tipo_contratacion',
        'usuario_sistema',
        'correo_institucional',
        'password',
        'rol',
    ];

    // Ocultamos la contraseña por seguridad
    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Compatibilidad con las vistas de Breeze, que esperan ->name y ->email
    public function getNameAttribute(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    public function getEmailAttribute()
    {
        return $this->correo_personal;
    }

    // Relación: Un usuario pertenece a un puesto
    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }

    // Relación: Un usuario tiene muchas solicitudes
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'usuario_id');
    }
}