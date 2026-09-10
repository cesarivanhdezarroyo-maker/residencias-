<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;
use App\Models\CategoriaServicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        // 1er Servicio: Cuentas y Accesos
        $cuentas = Servicio::create(['nombre_servicio' => 'ABC de cuentas de red / correo electrónico']);
        CategoriaServicio::create(['servicio_id' => $cuentas->id, 'nombre_categoria' => 'Cuenta de Red Médica / Administrativa']);
        CategoriaServicio::create(['servicio_id' => $cuentas->id, 'nombre_categoria' => 'Correo Electrónico Institucional']);
        CategoriaServicio::create(['servicio_id' => $cuentas->id, 'nombre_categoria' => 'SINAVID']);

        // 2do Servicio: Acceso a Internet
        $internet = Servicio::create(['nombre_servicio' => 'Acceso a Internet']);
        CategoriaServicio::create(['servicio_id' => $internet->id, 'nombre_categoria' => 'Navegación General']);
        CategoriaServicio::create(['servicio_id' => $internet->id, 'nombre_categoria' => 'Redes Sociales (Requiere justificación extra)']);

        // 3er Servicio: Licencias y Software
        $software = Servicio::create(['nombre_servicio' => 'Licencias y Software Institucional']);
        CategoriaServicio::create(['servicio_id' => $software->id, 'nombre_categoria' => 'Paquetería Office']);
        CategoriaServicio::create(['servicio_id' => $software->id, 'nombre_categoria' => 'Antivirus Institucional']);
        CategoriaServicio::create(['servicio_id' => $software->id, 'nombre_categoria' => 'Sistemas Médicos (SICEH, ECE, etc.)']);
        
        // 4to Servicio: Equipo de Cómputo
        $equipo = Servicio::create(['nombre_servicio' => 'Hardware / Equipo de Cómputo']);
        CategoriaServicio::create(['servicio_id' => $equipo->id, 'nombre_categoria' => 'Asignación de Computadora / Laptop']);
        CategoriaServicio::create(['servicio_id' => $equipo->id, 'nombre_categoria' => 'Asignación de Impresora / Escáner']);
    }
}