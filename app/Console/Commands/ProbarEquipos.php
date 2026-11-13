<?php

namespace App\Console\Commands;

use App\Services\EquipoLookupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProbarEquipos extends Command
{
    protected $signature = 'equipos:probar {numero : Número de empleado} {--forzar : Probar aunque EQUIPOS_ENABLED esté apagado} {--detalle : Mostrar todas las filas que devuelve la vista para ese número}';

    protected $description = 'Prueba la consulta de IP y hostname de un empleado y muestra el resultado';

    public function handle(EquipoLookupService $servicio): int
    {
        if ($this->option('forzar')) {
            config(['equipos.enabled' => true]);
        }

        $this->line('Habilitado: ' . (config('equipos.enabled') ? 'sí' : 'no (usa --forzar para probar)'));
        $this->line('Mecanismo: ' . config('equipos.driver'));

        if (config('equipos.driver') !== 'api') {
            try {
                DB::connection(config('equipos.db.conexion'))->getPdo();
                $this->info('Conexión a la base de equipos: correcta.');
            } catch (\Throwable $e) {
                $this->error('No se pudo conectar: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        if ($this->option('detalle') && config('equipos.driver') !== 'api') {
            $col = config('equipos.db.columnas');
            $filas = DB::connection(config('equipos.db.conexion'))
                ->table(config('equipos.db.tabla'))
                ->where($col['numero'], trim($this->argument('numero')))
                ->get();

            $this->line('Filas encontradas para ese número: ' . $filas->count());
            foreach ($filas as $fila) {
                $this->line('  - ' . json_encode((array) $fila, JSON_UNESCAPED_UNICODE));
            }
        }

        $resultado = $servicio->buscar($this->argument('numero'));

        if (!$resultado) {
            $this->warn('Sin resultado válido (no existe, el formato no es válido o la consulta falló).');
            $this->line('El formato usaría: "Por asignar por TI".');
            return self::SUCCESS;
        }

        $this->info('IP: ' . ($resultado['ip'] ?? '(sin dato)'));
        $this->info('Hostname: ' . ($resultado['hostname'] ?? '(sin dato)'));
        $this->info('Extension (RED): ' . ($resultado['extension'] ?? '(sin dato)'));

        return self::SUCCESS;
    }
}
