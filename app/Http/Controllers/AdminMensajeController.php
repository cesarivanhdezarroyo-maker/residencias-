<?php

namespace App\Http\Controllers;

use App\Mail\MensajeNuevo;
use App\Models\Mensaje;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
     * Guarda el aviso (cuerpo cifrado) y, si se pidió, manda también una copia por correo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'solicitud_id' => ['nullable', 'integer'],
            'asunto' => ['required', 'string', 'max:150'],
            'cuerpo' => ['required', 'string', 'max:2000'],
        ]);

        $mensaje = Mensaje::create([
            'usuario_id' => $validated['usuario_id'],
            'solicitud_id' => $validated['solicitud_id'] ?? null,
            'de_admin' => true,
            'asunto' => $validated['asunto'],
            'cuerpo' => $validated['cuerpo'],
            'requiere_confirmacion' => $request->boolean('requiere_confirmacion'),
        ]);

        $nota = '';

        if ($request->boolean('enviar_correo')) {
            $usuario = Usuario::find($validated['usuario_id']);

            $destinos = collect([$usuario->correo_personal, $usuario->correo_institucional])
                ->filter(fn ($correo) => filter_var($correo, FILTER_VALIDATE_EMAIL))
                ->unique()
                ->values()
                ->all();

            if (empty($destinos)) {
                $nota = ' No se envió correo porque la persona no tiene un correo válido registrado.';
            } else {
                try {
                    Mail::to($destinos)->send(new MensajeNuevo($mensaje));
                    $mensaje->update(['correo_enviado' => true]);
                } catch (\Throwable $e) {
                    report($e);
                    $nota = ' El correo no pudo enviarse, pero el aviso quedó disponible dentro del sistema.';
                }
            }
        }

        return redirect()->route('admin.mensajes.index')->with('success', 'Aviso enviado.' . $nota);
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
