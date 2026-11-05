<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_registrados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            // Solo se llena cuando el servicio viene de una solicitud aprobada
            $table->foreignId('solicitud_id')->nullable()->unique()->constrained('solicitudes')->cascadeOnDelete();
            // Solicitud de BAJA aprobada que marcó este servicio como dado de baja (permite revertirla)
            $table->foreignId('baja_solicitud_id')->nullable()->constrained('solicitudes')->nullOnDelete();
            $table->string('servicio');                       // nombre del servicio (igual que en el catálogo)
            $table->string('categoria');                      // categoría (igual que en el catálogo)
            $table->string('identificador', 150)->nullable(); // usuario, correo o nombre del equipo (sin contraseñas)
            $table->text('notas')->nullable();
            $table->string('origen', 20)->default('declarado'); // declarado | solicitud
            $table->string('estado', 20)->default('Activo');    // Activo | Baja
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_registrados');
    }
};
