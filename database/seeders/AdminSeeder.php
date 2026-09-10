<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['numero_empleado' => 'ADM001'], // Usamos ADM001 porque tu tabla exige exactamente 6 caracteres
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Sistemas',
                'correo_personal' => 'admin@issste.gob.mx',
                'password' => Hash::make('12345678'),
                'rol' => 'admin', // ¡Esto es clave para diferenciarlo!
            ]
        );
    }
}