<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Esta columna se creó originalmente como obligatoria, pero el botón
     * de limpieza necesita poder ponerla en NULL cuando borra el archivo
     * del disco. Usamos SQL directo (en vez de ->nullable()->change())
     * para no depender del paquete doctrine/dbal, que no está instalado.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE solicitudes MODIFY imagen_evidencia_path VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE solicitudes MODIFY imagen_evidencia_path VARCHAR(255) NOT NULL');
    }
};
