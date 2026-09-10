<?php

namespace App\Console\Commands;

use App\Services\LimpiezaSolicitudesService;
use Illuminate\Console\Command;

class LimpiarSolicitudesFinalizadas extends Command
{
    protected $signature = 'solicitudes:limpiar
                            {--dry-run : Solo mostrar qué se borraría, sin borrar nada}
                            {--meses=2 : Antigüedad mínima en meses para considerar el borrado}';

    protected $description = 'Borra archivos (imagen y Excel) de solicitudes finalizadas con más de N meses de antigüedad';

    public function handle(LimpiezaSolicitudesService $servicio): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $meses = (int) $this->option('meses');

        $resultado = $servicio->limpiar($meses, $dryRun);

        if ($resultado['solicitudes_afectadas'] === 0) {
            $this->info("No hay solicitudes finalizadas con más de {$meses} mes(es) pendientes de limpiar.");
            return self::SUCCESS;
        }

        $this->info("Encontradas {$resultado['solicitudes_afectadas']} solicitudes" . ($dryRun ? ' (dry-run, no se borrará nada):' : ':'));

        foreach ($resultado['detalle'] as $item) {
            $this->line(" - Solicitud #{$item['solicitud_id']}: {$item['archivo']} ({$item['kb']} KB)");
        }

        $resumen = sprintf(
            '%s %.2f MB en %d solicitud(es).',
            $dryRun ? 'Se liberarían' : 'Liberados',
            $resultado['liberados_mb'],
            $resultado['solicitudes_afectadas']
        );
        $this->info($resumen);

        if ($resultado['errores'] > 0) {
            $this->warn("{$resultado['errores']} archivo(s) no se pudieron borrar (revisa permisos).");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
