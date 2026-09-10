<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Puesto;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PuestoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ruta exacta de tu archivo Excel
        $filePath = base_path('database/data/Lista_Puestos (1).xlsx');

        // 2. Cargar el archivo con PhpSpreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        // 3. Obtener el número total de filas con datos
        $highestRow = $sheet->getHighestDataRow();

        // 4. Recorrer desde la fila 2 (para saltar el encabezado 'PUESTO')
        for ($row = 2; $row <= $highestRow; $row++) {
            // Extraer el valor de la celda en la columna A
            $nombrePuesto = $sheet->getCell('A' . $row)->getValue();

            // Si la celda no está vacía, se inserta en la base de datos
            if (!empty($nombrePuesto)) {
                Puesto::firstOrCreate([
                    'nombre_puesto' => trim($nombrePuesto)
                ]);
            }
        }
    }
}