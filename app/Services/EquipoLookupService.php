<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consulta la IP y el hostname del equipo de un empleado en el sistema de otros residentes.
 *
 * - Nunca lanza excepciones: si algo falla, registra una advertencia y devuelve null,
 *   para que la generación del formato no se rompa.
 * - Solo devuelve datos si hay UNA coincidencia clara y si los valores tienen formato válido.
 * - Dos mecanismos intercambiables (EQUIPOS_DRIVER): "db" (lectura directa) y "api".
 */
class EquipoLookupService
{
    /**
     * @return array{ip?: string, hostname?: string}|null
     */
    public function buscar(?string $numeroEmpleado): ?array
    {
        if (!config('equipos.enabled')) {
            return null;
        }

        $numero = trim((string) $numeroEmpleado);
        if ($numero === '') {
            return null;
        }

        try {
            $crudo = config('equipos.driver') === 'api'
                ? $this->buscarPorApi($numero)
                : $this->buscarPorBaseDeDatos($numero);
        } catch (\Throwable $e) {
            Log::warning('Consulta de equipos fallida: ' . $e->getMessage());
            return null;
        }

        return $this->normalizar($crudo);
    }

    /** Opción A: segunda conexión de base de datos, de solo lectura. */
    private function buscarPorBaseDeDatos(string $numero): ?array
    {
        $col = config('equipos.db.columnas');
        $conexion = DB::connection(config('equipos.db.conexion'));
        $tabla = config('equipos.db.tabla');

        // IP y hostname pertenecen al EQUIPO: se filtra por tipo y solo se usan si hay uno claro
        $campos = [$col['ip'] . ' as ip'];
        if (!empty($col['hostname'])) {
            $campos[] = $col['hostname'] . ' as hostname';
        }

        $filas = $conexion->table($tabla)
            ->select($campos)
            ->where($col['numero'], $numero)
            ->when(!empty($col['activo']), fn ($q) => $q->where($col['activo'], 1))
            ->when(
                !empty($col['tipo']) && !empty(config('equipos.db.tipo_valor')),
                function ($q) use ($col) {
                    // EQUIPOS_TIPO_VALOR admite varios valores separados por coma: "comput,laptop,pc"
                    $valores = array_filter(array_map('trim', explode(',', (string) config('equipos.db.tipo_valor'))));
                    $q->where(function ($sub) use ($col, $valores) {
                        foreach ($valores as $valor) {
                            $sub->orWhere($col['tipo'], 'like', '%' . $valor . '%');
                        }
                    });
                }
            )
            ->limit(2)
            ->get();

        $resultado = null;

        if ($filas->count() > 1) {
            Log::warning('Hay más de un equipo que cumple para un mismo número de empleado; no se completa la IP.');
        } elseif ($filas->count() === 1) {
            $resultado = (array) $filas->first();
        }

        // La EXTENSION (campo RED) pertenece a la PERSONA: se usa si todas sus filas coinciden en una misma,
        // aunque tenga varios equipos y no se pueda elegir la IP.
        if (!empty($col['extension'])) {
            $extensiones = $conexion->table($tabla)
                ->where($col['numero'], $numero)
                ->whereNotNull($col['extension'])
                ->where($col['extension'], '<>', '')
                ->distinct()
                ->pluck($col['extension']);

            if ($extensiones->count() === 1) {
                $resultado = ($resultado ?? []) + ['extension' => $extensiones->first()];
            }
        }

        return $resultado;
    }

    /** Opción B: API del otro sistema. Respuesta esperada: {"ip": "...", "hostname": "..."} */
    private function buscarPorApi(string $numero): ?array
    {
        $respuesta = Http::withToken((string) config('equipos.api.token'))
            ->acceptJson()
            ->timeout((int) config('equipos.api.timeout'))
            ->get(rtrim((string) config('equipos.api.url'), '/'), ['numero' => $numero]);

        if (!$respuesta->ok()) {
            return null;
        }

        $json = $respuesta->json();

        return is_array($json)
            ? ['ip' => $json['ip'] ?? null, 'hostname' => $json['hostname'] ?? null, 'extension' => $json['extension'] ?? null]
            : null;
    }

    /**
     * Valida el formato antes de escribir en el Excel. Importante: un valor que empiece con "="
     * se interpretaría como fórmula, así que solo se aceptan IP válidas y nombres de equipo simples.
     */
    private function normalizar(?array $crudo): ?array
    {
        if (!$crudo) {
            return null;
        }

        $resultado = [];

        $ip = trim((string) ($crudo['ip'] ?? ''));
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
            $resultado['ip'] = $ip;
        }

        $host = trim((string) ($crudo['hostname'] ?? ''));
        if ($host !== '' && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,99}$/', $host)) {
            $resultado['hostname'] = $host;
        }

        // RED = extension telefonica: el formato pide EXACTAMENTE 5 digitos (celda AB11)
        $extension = trim((string) ($crudo['extension'] ?? ''));
        if (preg_match('/^\d{5}$/', $extension)) {
            $resultado['extension'] = $extension;
        }

        return $resultado ?: null;
    }
}
