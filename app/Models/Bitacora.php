<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de cambios hechos por el administrador. Solo se escribe desde el sistema (middleware) y
 * solo se consulta; no hay pantalla para editarlo ni borrarlo.
 */
class Bitacora extends Model
{
    protected $table = 'bitacora';

    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'actor_nombre', 'actor_matricula',
        'accion', 'entidad', 'entidad_id',
        'afectado_id', 'afectado_nombre', 'afectado_matricula',
        'descripcion', 'detalles', 'ip', 'created_at',
    ];

    protected $casts = [
        'detalles' => 'array',
        'created_at' => 'datetime',
    ];

    /** Nombre legible de cada tipo de acción (para el filtro de la pantalla). */
    public const ETIQUETAS = [
        'solicitud.estado' => 'Cambio de estado de una solicitud',
        'solicitud.eliminada' => 'Solicitud eliminada',
        'usuario.eliminado' => 'Cuenta eliminada',
        'usuario.credenciales_plataforma' => 'Credenciales de la plataforma',
        'archivos.limpieza' => 'Limpieza de archivos',
        'configuracion.oficio' => 'Datos del oficio',
        'mensaje.enviado' => 'Mensaje o aviso enviado',
        'mensaje.eliminado' => 'Mensaje eliminado',
        'conversacion.eliminada' => 'Conversación eliminada',
        'conversacion.limpieza' => 'Limpieza de conversaciones',
    ];
}
