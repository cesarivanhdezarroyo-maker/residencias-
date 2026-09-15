<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_excel', function (Blueprint $table) {
            $table->id();
            $table->string('autoriza_nombre');
            $table->string('autoriza_cargo');
            $table->string('enlace_nombre');
            $table->string('jefe_servicios_nombre');
            $table->timestamps();
        });

        \DB::table('configuracion_excel')->insert([
            'autoriza_nombre' => 'Dr. Samuel Ferrer Palacios',
            'autoriza_cargo' => 'Director',
            'enlace_nombre' => 'Hugo Landero López',
            'jefe_servicios_nombre' => 'Dr. Samuel Ferrer Palacios',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_excel');
    }
};
