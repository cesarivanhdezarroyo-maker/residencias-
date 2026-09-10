<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    // Esta es la línea mágica que da permiso de escritura
    protected $fillable = ['nombre_servicio'];

    // (Si tenías funciones de relaciones aquí abajo, déjalas intactas)
}