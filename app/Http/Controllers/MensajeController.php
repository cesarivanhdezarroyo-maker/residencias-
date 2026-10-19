<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MensajeController extends Controller
{
    /**
     * "Mis avisos": historial de mensajes entre el empleado y Sistemas.
     */
    public function index()
    {
        $mensajes = Mensaje::where('usuario_id', Auth::id())
            ->latest()
            ->paginate(15);

        // Se recuerdan cuáles eran nuevos (para resaltarlos) y después se marcan como leídos.
        $idsNuevos = $mensajes->getCollection()
            ->filter(fn ($m) => $m->de_admin && is_null($m->leido_at))
            ->pluck('id')
            ->all();

        if (!empty($idsNuevos)) {
            Mensaje::whereIn('id', $idsNuevos)->update(['leido_at' => now()]);
        }

        return view('mensajes.index', compact('mensajes', 'idsNuevos'));
    }

    /**
     * El empleado escribe a Sistemas (dudas, aclaraciones).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asunto' => ['required', 'string', 'max:150'],
            'cuerpo' => ['required', 'string', 'max:2000'],
        ]);

        Mensaje::create([
            'usuario_id' => Auth::id(),
            'de_admin' => false,
            'asunto' => $validated['asunto'],
            'cuerpo' => $validated['cuerpo'],
        ]);

        return back()->with('success', 'Tu mensaje fue enviado al Departamento de Sistemas.');
    }

    /**
     * Botón "Estoy enterado": solo para avisos que requieren confirmación.
     */
    public function confirmar($id)
    {
        $mensaje = Mensaje::where('usuario_id', Auth::id())
            ->where('de_admin', true)
            ->where('requiere_confirmacion', true)
            ->findOrFail($id);

        if (is_null($mensaje->confirmado_at)) {
            $mensaje->update([
                'confirmado_at' => now(),
                'leido_at' => $mensaje->leido_at ?? now(),
            ]);
        }

        return back()->with('success', 'Quedó registrado que estás enterado.');
    }
}
