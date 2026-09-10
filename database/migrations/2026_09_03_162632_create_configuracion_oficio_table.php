<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_oficio', function (Blueprint $table) {
            $table->id();
            // Bloque "AT'N" (destinatario)
            $table->string('atn_nombre')->default("MTRO. HUGO LÓPEZ LANDERO");
            $table->string('atn_puesto')->default('JEFE DEL DEPARTAMENTO DE SISTEMAS.');
            $table->string('atn_lugar')->default('DELEGACIÓN ESTATAL VERACRUZ');
            // Bloque "Atentamente" (firma)
            $table->string('firma_nombre')->default('Ing. Juan Carlos Carrillo');
            $table->string('firma_puesto')->default('Encargado de Sistemas');
            $table->string('firma_lugar')->default('de la C.H. Xalapa');
            $table->timestamps();
        });

        // Fila única con los valores actuales -- el oficio siempre lee esta fila 1.
        \DB::table('configuracion_oficio')->insert([
            'atn_nombre' => "MTRO. HUGO LÓPEZ LANDERO",
            'atn_puesto' => 'JEFE DEL DEPARTAMENTO DE SISTEMAS.',
            'atn_lugar' => 'DELEGACIÓN ESTATAL VERACRUZ',
            'firma_nombre' => 'Ing. Juan Carlos Carrillo',
            'firma_puesto' => 'Encargado de Sistemas',
            'firma_lugar' => 'de la C.H. Xalapa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_oficio');
    }
};
