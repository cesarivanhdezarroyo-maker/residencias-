<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->index();      // empleado dueño de la conversación
            $table->unsignedBigInteger('solicitud_id')->nullable(); // solicitud relacionada, si aplica
            $table->boolean('de_admin');                            // true: Sistemas a empleado; false: empleado a Sistemas
            $table->string('asunto');
            $table->text('cuerpo');                                 // se guarda cifrado
            $table->boolean('requiere_confirmacion')->default(false);
            $table->timestamp('leido_at')->nullable();
            $table->timestamp('confirmado_at')->nullable();
            $table->boolean('correo_enviado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};