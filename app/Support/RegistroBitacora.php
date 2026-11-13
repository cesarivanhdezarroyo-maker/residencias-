<?php

namespace App\Support;

use App\Models\Bitacora;
use App\Models\Mensaje;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Arma y guarda la bitácora de lo que hace el administrador.
 *
 *  - NUNCA se guardan contraseñas, usuarios asignados ni el texto de los mensajes: solo qué se hizo, sobre qué y quién.
 *  - Un fallo al registrar jamás interrumpe la acción del administrador (todo va en try/catch).
 */
class RegistroBitacora
{
    /** Rutas que no se registran (no son cambios de importancia). */
    private const IGNORAR = ['admin.mensajes.leido'];

    /** nombre de ruta => [acción, entidad sobre la que se actúa, texto]. Lo que no esté aquí se registra con el nombre de la ruta. */
    private const RUTAS = [
        'admin.solicitudes.estado' => ['solicitud.estado', 'solicitud', 'Cambió el estado de la solicitud'],
        'admin.solicitudes.eliminar' => ['solicitud.eliminada', 'solicitud', 'Eliminó la solicitud'],
        'admin.usuarios.eliminar' => ['usuario.eliminado', 'usuario', 'Eliminó la cuenta'],
        'admin.usuarios.credenciales_plataforma' => ['usuario.credenciales_plataforma', 'usuario', 'Actualizó las credenciales de acceso a la plataforma'],
        'admin.limpieza.ejecutar' => ['archivos.limpieza', null, 'Ejecutó la limpieza de archivos de solicitudes resueltas'],
        'admin.oficio.actualizar' => ['configuracion.oficio', null, 'Modificó los datos del oficio'],
        'admin.mensajes.store' => ['mensaje.enviado', null, 'Envió un mensaje o aviso'],
        'admin.mensajes.destroy' => ['mensaje.eliminado', 'mensaje', 'Eliminó un mensaje'],
        'admin.mensajes.destroyConversacion' => ['conversacion.eliminada', 'usuario', 'Eliminó la conversación'],
        'admin.mensajes.limpiar' => ['conversacion.limpieza', null, 'Eliminó las conversaciones de usuarios que ya no existen'],
    ];

    /** Se llama ANTES de ejecutar la acción: después, lo eliminado ya no se puede consultar. */
    public static function contexto(Request $request): ?array
    {
        try {
            $nombre = $request->route()?->getName();

            if (!$nombre || in_array($nombre, self::IGNORAR, true)) {
                return null;
            }

            [$accion, $entidad, $texto] = self::RUTAS[$nombre]
                ?? [$nombre, null, 'Acción de administración (' . $nombre . ')'];

            $contexto = [
                'accion' => $accion,
                'entidad' => $entidad,
                'entidad_id' => null,
                'texto' => $texto,
                'extra' => '',
                'afectado' => null,
                'detalles' => [],
            ];

            $id = $request->route('id');

            if ($entidad === 'solicitud') {
                $solicitud = Solicitud::with(['usuario', 'servicio', 'categoria'])->find($id);

                if ($solicitud) {
                    $contexto['entidad_id'] = $solicitud->id;
                    $contexto['afectado'] = $solicitud->usuario;
                    $contexto['detalles']['estado_anterior'] = $solicitud->estado_solicitud;
                    // Solo en memoria, para saber si algo cambió de verdad; NUNCA se guarda en la bitácora
                    $contexto['previo'] = [
                        'credencial_usuario' => (string) $solicitud->credencial_usuario,
                        'credencial_password' => (string) $solicitud->credencial_password,
                        'credencial_notas' => (string) $solicitud->credencial_notas,
                    ];
                    $contexto['extra'] = sprintf(
                        ' #%d (%s: %s / %s)',
                        $solicitud->id,
                        $solicitud->tipo_solicitud,
                        $solicitud->servicio->nombre_servicio ?? '-',
                        $solicitud->categoria->nombre_categoria ?? '-'
                    );
                } else {
                    $contexto['extra'] = ' #' . $id;
                }
            } elseif ($entidad === 'usuario') {
                $usuario = Usuario::find($id);
                $contexto['afectado'] = $usuario;
                $contexto['entidad_id'] = $usuario?->id ?? (is_numeric($id) ? (int) $id : null);
            } elseif ($entidad === 'mensaje') {
                $mensaje = Mensaje::select('id', 'usuario_id')->find($id);

                if ($mensaje) {
                    $contexto['entidad_id'] = $mensaje->id;
                    $contexto['afectado'] = Usuario::find($mensaje->usuario_id);
                }
            }

            if ($nombre === 'admin.mensajes.store') {
                $contexto['afectado'] = Usuario::find($request->input('usuario_id'));
            }

            return $contexto;
        } catch (\Throwable $e) {
            Log::warning('Bitácora: no se pudo preparar el registro: ' . $e->getMessage());

            return null;
        }
    }

    /** Se llama DESPUÉS de que la acción terminó bien. */
    public static function registrar(Request $request, array $contexto): void
    {
        try {
            $actor = Auth::user();
            $afectado = $contexto['afectado'] ?? null;
            $detalles = $contexto['detalles'] ?? [];
            $nombre = $request->route()?->getName();

            if ($nombre === 'admin.solicitudes.estado') {
                $detalles['estado_nuevo'] = $request->input('estado_solicitud');

                // Guardar sin cambiar nada (mismo estado y mismos datos) no es un cambio: no se registra
                if (($detalles['estado_anterior'] ?? null) === $detalles['estado_nuevo']) {
                    $previo = $contexto['previo'] ?? [];
                    $datosCambiaron = false;

                    foreach (['credencial_usuario', 'credencial_password', 'credencial_notas'] as $campo) {
                        if ((string) $request->input($campo, '') !== ($previo[$campo] ?? '')) {
                            $datosCambiaron = true;
                        }
                    }

                    if (!$datosCambiaron) {
                        return;
                    }

                    $detalles['datos_actualizados'] = true;
                }
            }

            if ($nombre === 'admin.usuarios.credenciales_plataforma') {
                // Solo se anota si se cambió el usuario; los valores NUNCA se guardan
                $detalles['cambio_usuario'] = $request->filled('credencial_usuario');
            }

            $descripcion = $contexto['texto'] . ($contexto['extra'] ?? '');

            if ($afectado) {
                $descripcion .= ' — ' . $afectado->name . ' (' . $afectado->numero_empleado . ')';
            }

            if (!empty($detalles['datos_actualizados'])) {
                $descripcion .= ': ' . $detalles['estado_nuevo'] . ' (datos actualizados)';
            } elseif (isset($detalles['estado_nuevo'])) {
                $descripcion .= ': ' . ($detalles['estado_anterior'] ?? '?') . ' → ' . $detalles['estado_nuevo'];
            }

            if ($nombre === 'admin.usuarios.credenciales_plataforma') {
                $descripcion .= !empty($detalles['cambio_usuario']) ? ' (usuario y contraseña)' : ' (solo la contraseña)';
            }

            Bitacora::create([
                'actor_id' => $actor?->id,
                'actor_nombre' => $actor?->name,
                'actor_matricula' => $actor?->numero_empleado,
                'accion' => $contexto['accion'],
                'entidad' => $contexto['entidad'],
                'entidad_id' => $contexto['entidad_id'],
                'afectado_id' => $afectado?->id,
                'afectado_nombre' => $afectado?->name,
                'afectado_matricula' => $afectado?->numero_empleado,
                'descripcion' => mb_substr($descripcion, 0, 500),
                'detalles' => $detalles ?: null,
                'ip' => $request->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Bitácora: no se pudo guardar el registro: ' . $e->getMessage());
        }
    }
}
