<?php

namespace App\Console\Commands;

use App\Models\ServicioRegistrado;
use App\Models\Solicitud;
use Illuminate\Console\Command;

class SincronizarServiciosRegistrados extends Command
{
    protected $signature = 'servicios:sincronizar';

    protected $description = 'Carga en "servicios registrados" las solicitudes que ya estaban aprobadas antes de existir ese apartado (se puede repetir sin duplicar)';

    public function handle(): int
    {
        $procesadas = 0;
        $advertencias = 0;

        Solicitud::with(['servicio', 'categoria'])
            ->orderBy('id')
            ->chunkById(100, function ($lote) use (&$procesadas, &$advertencias) {
                foreach ($lote as $solicitud) {
                    $mensaje = ServicioRegistrado::sincronizarDesdeSolicitud($solicitud);
                    $procesadas++;

                    if ($mensaje) {
                        $advertencias++;
                        $this->warn("Solicitud #{$solicitud->id}: {$mensaje}");
                    }
                }
            });

        $this->info("Solicitudes revisadas: {$procesadas}");
        $this->info('Servicios activos: ' . ServicioRegistrado::where('estado', 'Activo')->count());
        $this->info('Servicios dados de baja: ' . ServicioRegistrado::where('estado', 'Baja')->count());
        $this->info("Casos que requieren revisión manual: {$advertencias}");

        return self::SUCCESS;
    }
}
