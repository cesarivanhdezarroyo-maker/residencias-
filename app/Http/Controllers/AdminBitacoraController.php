<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;

class AdminBitacoraController extends Controller
{
    /** Consulta de la bitácora. Solo lectura: no hay forma de editar ni borrar registros desde el sistema. */
    public function index(Request $request)
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'accion' => ['nullable', 'string', 'max:80'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $consulta = Bitacora::query()->orderByDesc('id');

        if ($request->filled('desde')) {
            $consulta->whereDate('created_at', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $consulta->whereDate('created_at', '<=', $request->input('hasta'));
        }

        if ($request->filled('accion')) {
            $consulta->where('accion', $request->input('accion'));
        }

        if ($request->filled('q')) {
            $texto = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($request->input('q'))) . '%';

            $consulta->where(function ($sub) use ($texto) {
                $sub->where('descripcion', 'like', $texto)
                    ->orWhere('actor_nombre', 'like', $texto)
                    ->orWhere('actor_matricula', 'like', $texto)
                    ->orWhere('afectado_nombre', 'like', $texto)
                    ->orWhere('afectado_matricula', 'like', $texto);
            });
        }

        return view('admin.bitacora.index', [
            'registros' => $consulta->paginate(50)->withQueryString(),
            'acciones' => Bitacora::query()->select('accion')->distinct()->orderBy('accion')->pluck('accion'),
            'etiquetas' => Bitacora::ETIQUETAS,
        ]);
    }
}
