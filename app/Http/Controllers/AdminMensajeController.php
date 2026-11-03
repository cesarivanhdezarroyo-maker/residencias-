<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminMensajeController extends Controller
{
    /**
     * Lista de conversaciones: una por empleado, con su último mensaje y contadores.
     * Filtros: ?filtro=sin_leer | pendientes  y  ?q=nombre o matrícula
     */
    public function index(Request $request)
    {
        $filtro = $request->query('filtro');
        $busqueda = trim((string) $request->query('q', ''));

        $resumen = Mensaje::selectRaw(
                'usuario_id, COUNT(*) as total, MAX(created_at) as ultimo, ' .
                'SUM(CASE WHEN de_admin = 0 AND leido_at IS NULL THEN 1 ELSE 0 END) as sin_leer, ' .
                'SUM(CASE WHEN de_admin = 1 AND requiere_confirmacion = 1 AND confirmado_at IS NULL THEN 1 ELSE 0 END) as por_confirmar'
            )
            ->groupBy('usuario_id')
            ->orderByDesc('ultimo')
            ->get();

        $usuarios = Usuario::whereIn('id', $resumen->pluck('usuario_id'))->get()->keyBy('id');

        // Último mensaje de cada conversación (solo se usa el asunto, no se descifra el cuerpo)
        $ultimos = Mensaje::whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('mensajes')->groupBy('usuario_id');
            })
            ->get()
            ->keyBy('usuario_id');

        $conversaciones = $resumen->map(function ($r) use ($usuarios, $ultimos) {
            return (object) [
                'usuario_id' => $r->usuario_id,
                'usuario' => $usuarios->get($r->usuario_id),
                'total' => (int) $r->total,
                'ultimo' => $r->ultimo,
                'sin_leer' => (int) $r->sin_leer,
                'por_confirmar' => (int) $r->por_confirmar,
                'ultimo_mensaje' => $ultimos->get($r->usuario_id),
            ];
        });

        // Totales globales (antes de filtrar), para los botones de filtro
        $totalSinLeer = $conversaciones->where('sin_leer', '>', 0)->count();
        $totalPorConfirmar = $conversaciones->where('por_confirmar', '>', 0)->count();

        if ($filtro === 'sin_leer') {
            $conversaciones = $conversaciones->filter(fn ($c) => $c->sin_leer > 0);
        } elseif ($filtro === 'pendientes') {
            $conversaciones = $conversaciones->filter(fn ($c) => $c->por_confirmar > 0);
        }

        if ($busqueda !== '') {
            $b = mb_strtolower($busqueda);
            $conversaciones = $conversaciones->filter(function ($c) use ($b) {
                if (!$c->usuario) {
                    return false;
                }
                return str_contains(mb_strtolower($c->usuario->name), $b)
                    || str_contains(mb_strtolower((string) $c->usuario->numero_empleado), $b);
            });
        }

        return view('admin.mensajes.index', [
            'conversaciones' => $conversaciones->values(),
            'filtro' => $filtro,
            'busqueda' => $busqueda,
            'totalSinLeer' => $totalSinLeer,
            'totalPorConfirmar' => $totalPorConfirmar,
        ]);
    }

    /**
     * Conversación con un empleado, en tres pestañas: ?tipo=nuevo | avisos | usuario
     * Los mensajes del empleado se marcan como leídos solo al abrir la pestaña "usuario".
     */
    public function show(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        $conteos = [
            'avisos' => Mensaje::where('usuario_id', $id)->where('de_admin', true)->count(),
            'usuario' => Mensaje::where('usuario_id', $id)->where('de_admin', false)->count(),
            'sin_leer' => Mensaje::where('usuario_id', $id)->where('de_admin', false)->whereNull('leido_at')->count(),
        ];

        $tipo = $request->query('tipo');
        if (!in_array($tipo, ['nuevo', 'avisos', 'usuario'], true)) {
            if ($conteos['sin_leer'] > 0) {
                $tipo = 'usuario';
            } elseif ($conteos['avisos'] > 0) {
                $tipo = 'avisos';
            } elseif ($conteos['usuario'] > 0) {
                $tipo = 'usuario';
            } else {
                $tipo = 'nuevo';
            }
        }

        $idsNuevos = [];
        $mensajes = collect();

        if ($tipo === 'usuario') {
            $idsNuevos = Mensaje::where('usuario_id', $id)
                ->where('de_admin', false)
                ->whereNull('leido_at')
                ->pluck('id')
                ->all();

            if (!empty($idsNuevos)) {
                Mensaje::whereIn('id', $idsNuevos)->update(['leido_at' => now()]);
                $conteos['sin_leer'] = 0;
            }

            $mensajes = Mensaje::where('usuario_id', $id)->where('de_admin', false)
                ->orderByDesc('created_at')->orderByDesc('id')->get();
        } elseif ($tipo === 'avisos') {
            $mensajes = Mensaje::where('usuario_id', $id)->where('de_admin', true)
                ->orderByDesc('created_at')->orderByDesc('id')->get();
        }

        return view('admin.mensajes.show', compact('usuario', 'mensajes', 'idsNuevos', 'tipo', 'conteos', 'id'));
    }

    /**
     * Formulario para enviar un aviso a un empleado (se usa desde "Nuevo aviso").
     * Acepta ?usuario_id=...&asunto=...
     */
    public function create(Request $request)
    {
        $usuarios = Usuario::where(function ($q) {
                $q->where('rol', '!=', 'admin')->orWhereNull('rol');
            })
            ->orderBy('nombres')
            ->get();

        $usuarioSeleccionado = $request->query('usuario_id');
        $asuntoSugerido = $request->query('asunto');
        $solicitudId = $request->query('solicitud_id');

        return view('admin.mensajes.create', compact('usuarios', 'usuarioSeleccionado', 'asuntoSugerido', 'solicitudId'));
    }

    /**
     * Guarda el aviso (el cuerpo se almacena cifrado) y vuelve a la conversación del empleado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'solicitud_id' => ['nullable', 'integer'],
            'asunto' => ['required', 'string', 'max:150'],
            'cuerpo' => ['required', 'string', 'max:2000'],
        ]);

        Mensaje::create([
            'usuario_id' => $validated['usuario_id'],
            'solicitud_id' => $validated['solicitud_id'] ?? null,
            'de_admin' => true,
            'asunto' => $validated['asunto'],
            'cuerpo' => $validated['cuerpo'],
            'requiere_confirmacion' => $request->boolean('requiere_confirmacion'),
        ]);

        return redirect()->route('admin.mensajes.show', ['id' => $validated['usuario_id'], 'tipo' => 'avisos'])
            ->with('success', 'Aviso enviado. La persona lo verá en su apartado "Avisos".');
    }

    /**
     * Marca como leído un mensaje que escribió un empleado (ya no se usa desde la pantalla,
     * porque al abrir la conversación se marcan solos; se conserva por compatibilidad con la ruta).
     */
    public function marcarLeido($id)
    {
        $mensaje = Mensaje::where('de_admin', false)->findOrFail($id);

        if (is_null($mensaje->leido_at)) {
            $mensaje->update(['leido_at' => now()]);
        }

        return back();
    }
}
