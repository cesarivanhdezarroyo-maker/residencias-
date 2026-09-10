<?php

namespace App\Services;

use App\Models\Solicitud;
use Illuminate\Support\Facades\Log;

class LimpiezaSolicitudesService
{
    /**
     * Borra de disco (no de la base de datos) la imagen de evidencia y el
     * Excel oficial de las solicitudes ya Aprobadas o Rechazadas cuya
     * última actualización tiene más de $meses meses. El registro en la
     * base de datos se conserva completo -- solo se libera el espacio en
     * disco de los archivos.
     *
     * @return array{solicitudes_afectadas:int, detalle:array, liberados_mb:float, errores:int}
     */
    public function limpiar(int $meses = 2, bool $dryRun = false): array
    {
        $limite = now()->subMonths($meses);

        $solicitudes = Solicitud::whereIn('estado_solicitud', ['Aprobado', 'Rechazado'])
            ->where('updated_at', '<', $limite)
            ->where(function ($q) {
                $q->whereNotNull('imagen_evidencia_path')
                  ->orWhereNotNull('pdf_generado_path');
            })
            ->get();

        $detalle = [];
        $liberados = 0;
        $errores = 0;

        foreach ($solicitudes as $solicitud) {
            $archivos = [];
            if ($solicitud->imagen_evidencia_path) {
                $archivos[] = 'evidencias/' . basename($solicitud->imagen_evidencia_path);
            }
            if ($solicitud->pdf_generado_path) {
                $archivos[] = $solicitud->pdf_generado_path;
            }

            foreach ($archivos as $rutaRelativa) {
                $rutaCompleta = storage_path('app/public/' . $rutaRelativa);
                if (file_exists($rutaCompleta)) {
                    $peso = filesize($rutaCompleta);
                    if (!$dryRun) {
                        if (@unlink($rutaCompleta)) {
                            $liberados += $peso;
                        } else {
                            $errores++;
                            continue;
                        }
                    } else {
                        $liberados += $peso;
                    }
                    $detalle[] = [
                        'solicitud_id' => $solicitud->id,
                        'archivo' => basename($rutaCompleta),
                        'kb' => round($peso / 1024, 1),
                    ];
                }
            }

            if (!$dryRun) {
                $solicitud->imagen_evidencia_path = null;
                $solicitud->pdf_generado_path = null;
                $solicitud->saveQuietly();
            }
        }

        if (!$dryRun && count($detalle) > 0) {
            Log::info(sprintf(
                'Limpieza de solicitudes ejecutada: %.1f MB liberados en %d solicitud(es).',
                $liberados / 1024 / 1024,
                $solicitudes->count()
            ));
        }

        return [
            'solicitudes_afectadas' => $solicitudes->count(),
            'detalle' => $detalle,
            'liberados_mb' => round($liberados / 1024 / 1024, 2),
            'errores' => $errores,
        ];
    }
}
