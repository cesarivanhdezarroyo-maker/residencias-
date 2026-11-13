<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bitacora')) {
            return;
        }

        // Sin llaves foraneas a proposito: el registro debe sobrevivir aunque se elimine la cuenta o la solicitud
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_nombre', 150)->nullable();
            $table->string('actor_matricula', 20)->nullable();
            $table->string('accion', 80)->index();
            $table->string('entidad', 30)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->unsignedBigInteger('afectado_id')->nullable()->index();
            $table->string('afectado_nombre', 150)->nullable();
            $table->string('afectado_matricula', 20)->nullable();
            $table->string('descripcion', 500);
            $table->json('detalles')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
