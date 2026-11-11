<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo agrega la columna si la tabla existe y todavia no la tiene
        if (Schema::hasTable('servicios_registrados') && !Schema::hasColumn('servicios_registrados', 'baja_solicitud_id')) {
            Schema::table('servicios_registrados', function (Blueprint $table) {
                $table->foreignId('baja_solicitud_id')->nullable()->after('solicitud_id')->constrained('solicitudes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('servicios_registrados', 'baja_solicitud_id')) {
            Schema::table('servicios_registrados', function (Blueprint $table) {
                $table->dropConstrainedForeignId('baja_solicitud_id');
            });
        }
    }
};