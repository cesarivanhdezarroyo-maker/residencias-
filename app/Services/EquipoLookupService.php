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

        $campos = [$col['ip'] . ' as ip'];
        if (!empty($col['hostname'])) {
            $campos[] = $col['hostname'] . ' as hostname';
        }

        $filas = DB::connection(config('equipos.db.conexion'))
            ->table(config('equipos.db.tabla'))
            ->select($campos)
            ->where($col['numero'], $numero)
            ->when(!empty($col['activo']), fn ($q) => $q->where($col['activo'], 1))
            ->when(
                !empty($col['tipo']) && !empty(config('equipos.db.tipo_valor')),
                fn ($q) => $q->where($col['tipo'], 'like', '%' . config('equipos.db.tipo_valor') . '%')
            )
            ->limit(2)
            ->get();

        if ($filas->count() > 1) {
            Log::warning('Hay más de un equipo que cumple para un mismo número de empleado; no se completa la IP.');
            return null;
        }

        return $filas->count() === 1 ? (array) $filas->first() : null;
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
            ? ['ip' => $json['ip'] ?? null, 'hostname' => $json['hostname'] ?? null]
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

        return $resultado ?: null;
    }
}
