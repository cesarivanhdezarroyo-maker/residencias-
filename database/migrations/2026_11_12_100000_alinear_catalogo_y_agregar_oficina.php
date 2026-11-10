<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Nombres viejos => nombres exactos del formato de Excel */
    private array $servicios = [
        'Cuentas de Red y Correo Institucional (ABC)' => 'ABC',
        'Licencias de Software Institucional' => 'LICENCIA',
    ];

    private array $categorias = [
        'AD USUARIO (Cuenta de Red)' => 'AD USUARIO',
        'AD-EX USUARIO Y CUENTA DE CORREO (Red y Correo)' => 'AD-EX USUARIO Y CUENTA DE CORREO',
        'EX CUENTA DE CORREO (Solo Correo Institucional)' => 'EX CUENTA DE CORREO',
        'Licencia Office 365 (Paquetería Office)' => 'Licencia Office 365',
    ];

    public function up(): void
    {
        // 1) Oficina del trabajador (menú del formato)
        if (!Schema::hasColumn('usuarios', 'oficina')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('oficina', 150)->nullable()->after('puesto_id');
            });
        }

        DB::transaction(function () {
            // 2) Servicios: renombrar, o unir con el nuevo si ya existe
            foreach ($this->servicios as $viejo => $nuevo) {
                $v = DB::table('servicios')->where('nombre_servicio', $viejo)->first();
                if (!$v) {
                    continue;
                }

                $n = DB::table('servicios')->where('nombre_servicio', $nuevo)->first();

                if ($n) {
                    DB::table('solicitudes')->where('servicio_id', $v->id)->update(['servicio_id' => $n->id]);
                    DB::table('categorias_servicio')->where('servicio_id', $v->id)->update(['servicio_id' => $n->id]);
                    DB::table('servicios')->where('id', $v->id)->delete();
                } else {
                    DB::table('servicios')->where('id', $v->id)->update(['nombre_servicio' => $nuevo]);
                }
            }

            // 3) Categorías: renombrar, o unir con la nueva si ya existe en el mismo servicio
            foreach ($this->categorias as $viejo => $nuevo) {
                foreach (DB::table('categorias_servicio')->where('nombre_categoria', $viejo)->get() as $c) {
                    $n = DB::table('categorias_servicio')
                        ->where('servicio_id', $c->servicio_id)
                        ->where('nombre_categoria', $nuevo)
                        ->first();

                    if ($n) {
                        DB::table('solicitudes')->where('categoria_id', $c->id)->update(['categoria_id' => $n->id]);
                        DB::table('categorias_servicio')->where('id', $c->id)->delete();
                    } else {
                        DB::table('categorias_servicio')->where('id', $c->id)->update(['nombre_categoria' => $nuevo]);
                    }
                }
            }

            // 4) "Mis servicios registrados" (si ya se instaló ese apartado)
            if (Schema::hasTable('servicios_registrados')) {
                foreach ($this->servicios as $viejo => $nuevo) {
                    DB::table('servicios_registrados')->where('servicio', $viejo)->update(['servicio' => $nuevo]);
                }
                foreach ($this->categorias as $viejo => $nuevo) {
                    DB::table('servicios_registrados')->where('categoria', $viejo)->update(['categoria' => $nuevo]);
                }
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'oficina')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('oficina');
            });
        }
        // Los nombres de servicio y categoría no se revierten.
    }
};
