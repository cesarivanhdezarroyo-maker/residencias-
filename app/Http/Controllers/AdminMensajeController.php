<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminMensajeController extends Controller
{
    /**
     * Bandeja del administrador: pendientes de confirmar, mensajes de empleados y avisos enviados.
     */
    public function index()
    {
        $pendientes = Mensaje::with('usuario')
            ->where('de_admin', true)
            ->where('requiere_confirmacion', true)
            ->whereNull('confirmado_at')
            ->latest()
            ->get();

        $recibidos = Mensaje::with('usuario')
            ->where('de_admin', false)
            ->latest()
            ->paginate(10, ['*'], 'recibidos');

        $enviados = Mensaje::with('usuario')
            ->where('de_admin', true)
            ->latest()
            ->paginate(10, ['*'], 'enviados');

        return view('admin.mensajes.index', compact('pendientes', 'recibidos', 'enviados'));
    }

    /**
     * Formulario para enviar un aviso a un empleado.
     * Acepta ?usuario_id=...&asunto=... para responder rápido.
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
     * Guarda el aviso (el cuerpo se almacena cifrado). Solo se entrega dentro del sistema.
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

        return redirect()->route('admin.mensajes.index')
            ->with('success', 'Aviso enviado. La persona lo verá en su apartado "Avisos".');
    }

    /**
     * Marca como leído un mensaje que escribió un empleado.
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
