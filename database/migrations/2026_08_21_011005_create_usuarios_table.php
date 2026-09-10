<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla única de usuarios de la plataforma.
     * No existe ninguna otra tabla de usuarios en el sistema: esta es la fuente
     * de verdad tanto para login (Auth) como para el panel de administración
     * y las solicitudes.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('numero_empleado', 6)->unique();
            $table->string('correo_personal')->unique();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->enum('tipo_contratacion', ['Estructura', 'Interinato', 'Guardias y Suplencias', 'Honorarios'])->nullable();
            $table->string('usuario_sistema')->nullable();
            $table->string('correo_institucional')->nullable(); // Se llena cuando se aprueba el alta de Correo Institucional
            $table->string('password');
            $table->enum('rol', ['usuario', 'admin'])->default('usuario');
            $table->rememberToken();
            $table->timestamps();
        });

        // Requerida por el sistema de "olvidé mi contraseña" de Laravel.
        // La columna se llama 'email' porque el propio framework la busca así
        // internamente; aquí sólo guarda el valor de correo_personal del usuario.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Requerida por el driver de sesiones en base de datos.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('usuarios');
    }
};
