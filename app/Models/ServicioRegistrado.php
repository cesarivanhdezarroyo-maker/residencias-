<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServicioRegistrado extends Model
{
    protected $table = 'servicios_registrados';

    protected $fillable = [
        'usuario_id',
        'solicitud_id',
        'baja_solicitud_id',
        'servicio',
        'categoria',
        'identificador',
        'notas',
        'origen',
        'estado',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    /**
     * Catálogo para el formulario "Agregar un servicio que ya tengo".
     * Si existe App\Support\ListasFormato (paquete de listas del formato), usa EXACTAMENTE sus nombres.
     * Si no, usa el catálogo anterior, para que este apartado funcione sin importar el orden de instalación.
     * Debe coincidir siempre con SolicitudController::getCatalogo().
     */
    public static function catalogo(): array
    {
        if (class_exists(\App\Support\ListasFormato::class)) {
            $resultado = [];
            foreach (\App\Support\ListasFormato::catalogo() as $servicio => $info) {
                $resultado[$servicio] = array_values($info['categorias']);
            }
            return $resultado;
        }

        return [
            'Cuentas de Red y Correo Institucional (ABC)' => [
                'AD USUARIO (Cuenta de Red)',
                'AD-EX USUARIO Y CUENTA DE CORREO (Red y Correo)',
                'EX CUENTA DE CORREO (Solo Correo Institucional)',
            ],
            'Licencias de Software Institucional' => [
                'Licencia Office 365 (Paquetería Office)',
            ],
            'Hardware / Equipo de Cómputo' => [
                'Asignación de Computadora / Laptop',
                'Asignación de Impresora / Escáner',
            ],
        ];
    }

    /**
     * Mantiene "Mis servicios registrados" al día según el estado de una solicitud.
     *
     *  ALTA aprobada            -> aparece como servicio activo (una vez por solicitud; no resucita una baja).
     *  ALTA no aprobada         -> se quita el registro que esa solicitud había generado.
     *  BAJA aprobada            -> da de baja SOLO el servicio que corresponde (ver sincronizarBaja).
     *  BAJA que deja de aprobarse -> restaura los servicios que esa misma baja había marcado.
     *
     * Es repetible (idempotente), corre en una transacción y devuelve un texto de advertencia
     * cuando algo no se pudo resolver solo, para mostrárselo al administrador. Nunca lanza excepciones.
     */
    public static function sincronizarDesdeSolicitud(Solicitud $solicitud): ?string
    {
        try {
            return DB::transaction(function () use ($solicitud) {
                $solicitud->loadMissing(['servicio', 'categoria']);

                $servicio = $solicitud->servicio->nombre_servicio ?? null;
                $categoria = $solicitud->categoria->nombre_categoria ?? null;

                if (!$servicio || !$categoria) {
                    return null;
                }

                if ($solicitud->tipo_solicitud === 'ALTA') {
                    return self::sincronizarAlta($solicitud, $servicio, $categoria);
                }

                if ($solicitud->tipo_solicitud === 'BAJA') {
                    return self::sincronizarBaja($solicitud, $servicio, $categoria);
                }

                return null;
            });
        } catch (\Throwable $e) {
            report($e);
            return 'No se pudo actualizar "Mis servicios registrados"; revisa el registro de errores.';
        }
    }

    private static function sincronizarAlta(Solicitud $solicitud, string $servicio, string $categoria): ?string
    {
        if ($solicitud->estado_solicitud !== 'Aprobado') {
            self::where('solicitud_id', $solicitud->id)->where('origen', 'solicitud')->delete();
            return null;
        }

        $identificador = trim((string) $solicitud->credencial_usuario);
        $identificador = $identificador !== '' ? $identificador : null;

        // Si la propia persona ya había declarado exactamente ese servicio, no se duplica
        if ($identificador !== null) {
            $yaDeclarado = self::where('usuario_id', $solicitud->usuario_id)
                ->where('servicio', $servicio)
                ->where('categoria', $categoria)
                ->where('estado', 'Activo')
                ->whereNull('solicitud_id')
                ->whereRaw('LOWER(identificador) = ?', [mb_strtolower($identificador)])
                ->exists();

            if ($yaDeclarado) {
                return null;
            }
        }

        $registro = self::firstOrNew(['solicitud_id' => $solicitud->id]);
        $registro->fill([
            'usuario_id' => $solicitud->usuario_id,
            'servicio' => $servicio,
            'categoria' => $categoria,
            'identificador' => $identificador,
            'origen' => 'solicitud',
        ]);

        // El estado solo se fija al crear: guardar de nuevo la alta no revive un servicio dado de baja
        if (!$registro->exists) {
            $registro->estado = 'Activo';
        }

        $registro->save();

        return null;
    }

    private static function sincronizarBaja(Solicitud $solicitud, string $servicio, string $categoria): ?string
    {
        // La baja ya no está aprobada: se restauran SOLO los servicios que esta misma baja había marcado
        if ($solicitud->estado_solicitud !== 'Aprobado') {
            self::where('baja_solicitud_id', $solicitud->id)
                ->update(['estado' => 'Activo', 'baja_solicitud_id' => null]);
            return null;
        }

        $yaMarcados = self::where('baja_solicitud_id', $solicitud->id)->count();

        $base = self::where('usuario_id', $solicitud->usuario_id)
            ->where('servicio', $servicio)
            ->where('categoria', $categoria)
            ->where('estado', 'Activo');

        $identificador = trim((string) $solicitud->credencial_usuario);

        // 1) Si el administrador indicó el usuario o equipo dado de baja, se busca exactamente ese
        if ($identificador !== '') {
            $coincidencias = (clone $base)
                ->whereRaw('LOWER(identificador) = ?', [mb_strtolower($identificador)])
                ->get();

            if ($coincidencias->isEmpty()) {
                return $yaMarcados > 0
                    ? null
                    : 'No se encontró un servicio activo con el usuario o equipo "' . $identificador . '", por lo que "Mis servicios registrados" no cambió.';
            }

            self::whereIn('id', $coincidencias->pluck('id'))
                ->update(['estado' => 'Baja', 'baja_solicitud_id' => $solicitud->id]);

            return null;
        }

        // 2) Sin identificador: solo se actúa si no hay duda (exactamente un servicio de ese tipo)
        $activos = (clone $base)->get();

        if ($activos->count() === 1) {
            $activos->first()->update(['estado' => 'Baja', 'baja_solicitud_id' => $solicitud->id]);
            return null;
        }

        if ($activos->count() > 1) {
            return 'La persona tiene varios servicios de este tipo en "Mis servicios registrados". Escribe en "Usuario dado de baja" cuál es, para que se actualice solo ese.';
        }

        return null;
    }
}
