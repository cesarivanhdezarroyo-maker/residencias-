<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionExcel extends Model
{
    protected $table = 'configuracion_excel';

    protected $fillable = [
        'autoriza_nombre',
        'autoriza_cargo',
        'enlace_nombre',
        'jefe_servicios_nombre',
    ];

    /**
     * Regresa la única fila de configuración. Si por alguna razón la tabla
     * quedara vacía (nunca debería, la migración siembra los valores), crea
     * una con los valores actuales para que el sistema no se rompa.
     */
    public static function actual()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'autoriza_nombre' => 'Dr. Samuel Ferrer Palacios',
                'autoriza_cargo' => 'Director',
                'enlace_nombre' => 'Hugo Landero López',
                'jefe_servicios_nombre' => 'Dr. Samuel Ferrer Palacios',
            ]
        );
    }
}
