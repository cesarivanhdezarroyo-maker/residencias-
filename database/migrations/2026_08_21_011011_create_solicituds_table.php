<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('solicitudes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
        $table->enum('tipo_solicitud', ['ALTA', 'BAJA', 'CAMBIO']); // Para celda H19
        $table->foreignId('servicio_id')->constrained('servicios'); // Para celda H20
        $table->foreignId('categoria_id')->constrained('categorias_servicio'); // Para celda H21
        $table->text('justificacion'); // Para celda D30
        $table->string('imagen_evidencia_path');
        $table->string('pdf_generado_path')->nullable();
        $table->enum('estado_solicitud', ['Pendiente', 'Enviado', 'Aprobado', 'Rechazado'])->default('Pendiente');

        // Credenciales que México regresa aprobadas para ESTA solicitud en particular
        // (cada alta puede traer una credencial distinta: AD, correo, equipo, etc.)
        $table->string('credencial_usuario')->nullable();
        $table->text('credencial_password')->nullable(); // se guarda cifrada (cast 'encrypted' en el modelo)
        $table->text('credencial_notas')->nullable();

        $table->timestamps(); // created_at servirá para la celda H8
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
