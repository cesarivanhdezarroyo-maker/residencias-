<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nombres',
        'apellidos',
        'email',
        'password',
        'numero_empleado',
        'puesto_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }

    public function solicitudes()
    {
        // Fuerza a Laravel a contar usando la columna correcta
        return $this->hasMany(Solicitud::class, 'usuario_id');
    }
}