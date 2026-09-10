<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaServicio extends Model
{
    // Le indicamos a Laravel el nombre exacto de la tabla en la base de datos
    protected $table = 'categorias_servicio';

    protected $fillable = [
        'servicio_id', 
        'nombre_categoria'
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}