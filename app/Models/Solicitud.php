<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    // Fuerza a Laravel a usar el nombre correcto de tu tabla en español
    protected $table = 'solicitudes';

    // Le damos permiso a Laravel para guardar datos en todas estas columnas
    protected $fillable = [
        'usuario_id',
        'tipo_solicitud',
        'servicio_id',
        'categoria_id',
        'justificacion',
        'imagen_evidencia_path',
        'estado_solicitud',
        'pdf_generado_path', // Clave para que se guarde la ruta de tu Excel
        'credencial_usuario',
        'credencial_password',
        'credencial_notas',
    ];

    // La contraseña asignada por México se guarda cifrada en la base de datos
    protected function casts(): array
    {
        return [
            'credencial_password' => 'encrypted',
        ];
    }

    // Relación con el Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación con el Servicio
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    // Relación con la Categoría
    public function categoria()
    {
        return $this->belongsTo(CategoriaServicio::class, 'categoria_id');
    }
}