<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionOficio extends Model
{
    protected $table = 'configuracion_oficio';

    protected $fillable = [
        'atn_nombre',
        'atn_puesto',
        'atn_lugar',
        'firma_nombre',
        'firma_puesto',
        'firma_lugar',
    ];

    /**
     * Solo existe una fila de configuración -- este helper siempre la
     * regresa (y la crea con los valores por defecto si por alguna razón
     * no existiera todavía).
     */
    public static function actual(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
