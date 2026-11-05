<?php

namespace App\Http\Controllers;

use App\Models\ServicioRegistrado;
use App\Models\Usuario;

class AdminServicioRegistradoController extends Controller
{
    /** Servicios registrados de un empleado (solo lectura). La ruta está dentro del grupo AdminMiddleware. */
    public function index($id)
    {
        $usuario = Usuario::findOrFail($id);

        $servicios = ServicioRegistrado::where('usuario_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $activos = $servicios->where('estado', 'Activo')->values();
        $bajas = $servicios->where('estado', 'Baja')->values();

        return view('admin.servicios_usuario', compact('usuario', 'activos', 'bajas'));
    }
}
