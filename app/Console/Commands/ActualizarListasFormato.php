<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActualizarListasFormato extends Command
{
    protected $signature = 'listas:actualizar {--archivo= : Ruta del Excel (por defecto, la plantilla del formato)}';

    protected $description = 'Regenera database/data/listas_formato.json leyendo las listas de la plantilla de Excel';

    public function handle(): int
    {
        $ruta = $this->option('archivo') ?: base_path('database/data/Formato_CCUSC_DSI_V8.0_Alta_de_buzon.xlsx');

        if (!is_file($ruta)) {
            $this->error("No existe el archivo: {$ruta}");
            return self::FAILURE;
        }

        $libro = IOFactory::load($ruta);
        $aux = $libro->getSheetByName('Aux');
        $catalogo = $libro->getSheetByName('Catalogo');
        $formato = $libro->getSheetByName('Formato');

        if (!$aux || !$catalogo || !$formato) {
            $this->error('La plantilla no tiene las hojas Aux, Catalogo y Formato.');
            return self::FAILURE;
        }

        $oficinas = $this->columna($aux, 'E', 2);

        // Servicios (Catalogo!C2:C4) y categorías (rangos de la hoja Catalogo, columna B)
        $nombresServicios = $this->rango($catalogo, 'C', 2, 4);
        $servicios = [
            'ABC' => $this->rango($catalogo, 'B', 2, 4),
            'PERFILES_DE_ACCESO_A_INTERNET' => $this->rango($catalogo, 'B', 5, 12),
            'LICENCIA' => $this->rango($catalogo, 'B', 13, 13),
        ];

        if (array_keys($servicios) !== $nombresServicios) {
            $this->warn('Los nombres de servicio de la hoja Catalogo ya no coinciden con ABC, PERFILES_DE_ACCESO_A_INTERNET y LICENCIA.');
            $this->warn('Encontrados: ' . implode(', ', $nombresServicios) . '. Hay que ajustar los rangos de este comando.');
            return self::FAILURE;
        }

        $tipos = [];
        try {
            $formula = (string) $formato->getDataValidation('X19')->getFormula1();
            $tipos = array_values(array_filter(array_map('trim', explode(',', trim($formula, '"')))));
        } catch (\Throwable $e) {
            $this->warn('No se pudo leer la lista de tipos de contratación (X19): ' . $e->getMessage());
        }

        if (empty($tipos)) {
            $this->error('No se encontraron tipos de contratación; no se modifica el archivo.');
            return self::FAILURE;
        }

        $datos = [
            'origen' => 'Hoja Aux (oficinas), Catalogo (servicios y categorias) y validacion X19 (tipos de contratacion) del formato de Excel',
            'oficinas' => $oficinas,
            'tipos_contratacion' => $tipos,
            'servicios' => $servicios,
        ];

        file_put_contents(
            database_path('data/listas_formato.json'),
            json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        $this->info('Oficinas: ' . count($oficinas));
        $this->info('Tipos de contratación: ' . implode(' | ', $tipos));
        foreach ($servicios as $servicio => $categorias) {
            $this->info("{$servicio}: " . count($categorias) . ' categorías');
        }
        $this->info('Archivo actualizado: database/data/listas_formato.json');

        return self::SUCCESS;
    }

    private function columna(Worksheet $hoja, string $col, int $desde): array
    {
        $valores = [];
        $fila = $desde;

        while (true) {
            $valor = trim((string) $hoja->getCell($col . $fila)->getValue());
            if ($valor === '') {
                break;
            }
            $valores[] = $valor;
            $fila++;
        }

        return array_values(array_unique($valores));
    }

    private function rango(Worksheet $hoja, string $col, int $desde, int $hasta): array
    {
        $valores = [];
        for ($fila = $desde; $fila <= $hasta; $fila++) {
            $valores[] = trim((string) $hoja->getCell($col . $fila)->getValue());
        }

        return $valores;
    }
}
