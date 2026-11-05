<?php

namespace App\Http\Controllers;

use App\Models\ServicioRegistrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServicioRegistradoController extends Controller
{
    /** "Mis servicios registrados": los que declaró el trabajador y los de solicitudes aprobadas. */
    public function index()
    {
        $servicios = ServicioRegistrado::where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $activos = $servicios->where('estado', 'Activo')->values();
        $bajas = $servicios->where('estado', 'Baja')->values();
        $catalogo = ServicioRegistrado::catalogo();

        return view('servicios.index', compact('activos', 'bajas', 'catalogo'));
    }

    /** Registrar un servicio que la persona ya tiene (no genera una solicitud). */
    public function store(Request $request)
    {
        $catalogo = ServicioRegistrado::catalogo();

        $validated = $request->validate([
            'servicio' => ['required', 'string', Rule::in(array_keys($catalogo))],
            'categoria' => ['required', 'string'],
            'identificador' => ['nullable', 'string', 'max:150'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        if (!in_array($validated['categoria'], $catalogo[$validated['servicio']], true)) {
            return back()->withErrors(['categoria' => 'La categoría no corresponde al servicio elegido.'])->withInput();
        }

        // Tope de registros declarados por persona, para que el apartado no se llene de basura
        $declarados = ServicioRegistrado::where('usuario_id', Auth::id())->where('origen', 'declarado')->count();
        if ($declarados >= 30) {
            return back()->withErrors(['servicio' => 'Alcanzaste el máximo de 30 servicios registrados por ti. Quita alguno antes de agregar otro.'])->withInput();
        }

        $identificador = isset($validated['identificador']) && trim($validated['identificador']) !== ''
            ? trim($validated['identificador'])
            : null;

        $yaExiste = ServicioRegistrado::where('usuario_id', Auth::id())
            ->where('servicio', $validated['servicio'])
            ->where('categoria', $validated['categoria'])
            ->where('estado', 'Activo')
            ->when(
                $identificador === null,
                fn ($q) => $q->whereNull('identificador'),
                fn ($q) => $q->where('identificador', $identificador)
            )
            ->exists();

        if ($yaExiste) {
            return back()->withErrors(['servicio' => 'Ya tienes registrado este servicio con esos mismos datos.'])->withInput();
        }

        ServicioRegistrado::create([
            'usuario_id' => Auth::id(),
            'servicio' => $validated['servicio'],
            'categoria' => $validated['categoria'],
            'identificador' => $identificador,
            'notas' => $validated['notas'] ?? null,
            'origen' => 'declarado',
            'estado' => 'Activo',
        ]);

        return redirect()->route('servicios.index')->with('success', 'Servicio registrado.');
    }

    /** Solo se pueden quitar los que registró la propia persona; los de solicitudes los maneja Sistemas. */
    public function destroy($id)
    {
        $registro = ServicioRegistrado::where('usuario_id', Auth::id())
            ->where('origen', 'declarado')
            ->findOrFail($id);

        $registro->delete();

        return redirect()->route('servicios.index')->with('success', 'Se quitó el registro.');
    }
}
