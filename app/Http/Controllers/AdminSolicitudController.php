<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Solicitud;
use App\Mail\CredencialesPlataformaActualizadas;
use App\Mail\SolicitudActualizada;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class AdminSolicitudController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('solicitudes')
                        ->where('rol', '!=', 'admin')
                        ->orderBy('nombres', 'asc')
                        ->get();

        return view('admin.usuarios', compact('usuarios'));
    }

    public function verSolicitudes($id)
    {
        $usuario = Usuario::findOrFail($id);

        $solicitudes = Solicitud::with(['servicio', 'categoria'])
                        ->where('usuario_id', $usuario->id)
                        ->latest()
                        ->get();

        // Separamos altas y bajas para mostrarlas en secciones distintas
        $altas = $solicitudes->where('tipo_solicitud', 'ALTA');
        $bajas = $solicitudes->where('tipo_solicitud', 'BAJA');

        return view('admin.solicitudes_usuario', compact('usuario', 'altas', 'bajas'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        // Cargamos la solicitud primero para saber de qué servicio se trata
        // antes de validar -- así podemos exigir formato de correo real
        // solo cuando de verdad aplica (Cuentas de Red y Correo Institucional).
        $solicitud = Solicitud::with(['usuario', 'servicio'])->findOrFail($id);

        $esCorreoInstitucional = $solicitud->tipo_solicitud === 'ALTA'
            && $solicitud->servicio
            && $solicitud->servicio->nombre_servicio === 'Cuentas de Red y Correo Institucional (ABC)';

        $validated = $request->validate([
            'estado_solicitud' => 'required|in:Pendiente,Aprobado,Rechazado',
            'credencial_usuario' => $esCorreoInstitucional
                ? 'nullable|email:rfc|max:255'
                : 'nullable|string|max:255',
            'credencial_password' => 'nullable|string|max:255',
            'credencial_notas' => 'nullable|string|max:2000',
        ], [
            'credencial_usuario.email' => 'Para este servicio, el campo debe ser un correo institucional válido (ej. nombre@issste.gob.mx).',
        ]);

        $solicitud->update($validated);

        // Si es el servicio de Correo Institucional y ya se capturó un correo
        // válido (ya lo garantiza la validación de arriba), ese dato se
        // convierte en el correo institucional oficial de la persona.
        if ($esCorreoInstitucional && $solicitud->credencial_usuario) {
            $solicitud->usuario->update(['correo_institucional' => $solicitud->credencial_usuario]);
        }

        // Avisamos por correo cuando: (a) el estado cambia a Aprobado/Rechazado por
        // primera vez, o (b) ya estaba en ese estado pero se acaban de actualizar las
        // credenciales o el motivo (el flujo real: primero se aprueba, y las
        // credenciales de México llegan y se capturan después, en otro momento).
        // Así evitamos correos duplicados solo cuando de verdad no cambió nada.
        $huboCambioRelevante = $solicitud->wasChanged('estado_solicitud')
            || $solicitud->wasChanged('credencial_usuario')
            || $solicitud->wasChanged('credencial_password')
            || $solicitud->wasChanged('credencial_notas');

        if ($huboCambioRelevante && in_array($solicitud->estado_solicitud, ['Aprobado', 'Rechazado'])) {
            $destinatarios = array_values(array_filter([
                $solicitud->usuario->correo_personal,
                $solicitud->usuario->correo_institucional,
            ]));

            try {
                Mail::to($destinatarios)->send(new SolicitudActualizada($solicitud));
            } catch (\Exception $e) {
                report($e);
            }
        }

        return back()->with('success', 'La solicitud se actualizó correctamente.');
    }

    public function eliminarSolicitud($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $solicitud->delete();

        return back()->with('success', 'La solicitud fue eliminada definitivamente.');
    }

    public function eliminarUsuario($id)
    {
        $usuario = Usuario::findOrFail($id);

        // BORRADO EN CASCADA: Evita registros huérfanos
        Solicitud::where('usuario_id', $usuario->id)->delete();
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'La cuenta y todas sus solicitudes fueron eliminadas.');
    }

    /**
     * Convierte lo capturado en las credenciales REALES de acceso a esta
     * plataforma para ese empleado, reemplazando las que usó al autorregistrarse.
     * Es independiente de las solicitudes: aplica a la persona completa.
     */
    public function actualizarCredencialesPlataforma(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'credencial_usuario' => ['required', 'string', 'size:6', \Illuminate\Validation\Rule::unique('usuarios', 'usuario_sistema')->ignore($usuario->id)],
            'credencial_password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $usuario->update([
            'usuario_sistema' => $validated['credencial_usuario'],
            'password' => Hash::make($validated['credencial_password']),
        ]);

        try {
            $destinatarios = array_values(array_filter([
                $usuario->correo_personal,
                $usuario->correo_institucional,
            ]));

            Mail::to($destinatarios)->send(
                new CredencialesPlataformaActualizadas($usuario, $validated['credencial_usuario'], $validated['credencial_password'])
            );
        } catch (\Exception $e) {
            report($e);
        }

        return back()->with('success', 'Las credenciales de acceso a la plataforma de ' . $usuario->name . ' fueron actualizadas.');
    }

    /**
     * Pantalla de reportes: formulario para elegir un rango de fechas.
     */
    public function reportes()
    {
        return view('admin.reportes');
    }

    /**
     * Genera y descarga un Excel con todas las solicitudes (altas y bajas)
     * dentro del rango de fechas elegido.
     */
    public function exportarReporte(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo' => 'nullable|in:ALTA,BAJA',
        ]);

        $query = Solicitud::with(['usuario.puesto', 'servicio', 'categoria'])
            ->whereDate('created_at', '>=', $validated['fecha_inicio'])
            ->whereDate('created_at', '<=', $validated['fecha_fin']);

        if (!empty($validated['tipo'])) {
            $query->where('tipo_solicitud', $validated['tipo']);
        }

        $solicitudes = $query->orderBy('created_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Solicitudes');

        $encabezados = ['Fecha', 'Tipo', 'Empleado', 'Matrícula', 'Puesto', 'Servicio', 'Categoría', 'Estado', 'Usuario Asignado'];
        $sheet->fromArray($encabezados, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $fila = 2;
        foreach ($solicitudes as $s) {
            $sheet->fromArray([
                $s->created_at->format('d/m/Y'),
                $s->tipo_solicitud,
                trim(($s->usuario->nombres ?? '') . ' ' . ($s->usuario->apellidos ?? '')),
                $s->usuario->numero_empleado ?? '',
                $s->usuario->puesto->nombre_puesto ?? '',
                $s->servicio->nombre_servicio ?? '',
                $s->categoria->nombre_categoria ?? '',
                $s->estado_solicitud,
                $s->credencial_usuario ?? '',
                // Nota: la contraseña NUNCA se incluye en el reporte exportable por seguridad.
            ], null, 'A' . $fila);
            $fila++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Reporte_Solicitudes_' . $validated['fecha_inicio'] . '_a_' . $validated['fecha_fin'] . '.xlsx';
        $tempPath = storage_path('app/' . $fileName);

        $writer = new XlsxWriter($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Pantalla del botón manual de limpieza de archivos (Excel + imágenes).
     */
        public function generarPdfAdmin($id)
    {
        $solicitud = \App\Models\Solicitud::findOrFail($id);
        $rutaExcel = storage_path('app/public/' . $solicitud->pdf_generado_path);

        if (!$solicitud->pdf_generado_path || !file_exists($rutaExcel)) {
            return back()->with('error', 'Este archivo ya no está disponible (fue eliminado por la limpieza periódica de archivos antiguos, o aún no se genera).');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::attach(
                'archivo', file_get_contents($rutaExcel), 'documento.xlsx'
            )->post('http://localhost:5000/convertir');

            if ($response->successful()) {
                $pdfName = 'Solicitud_' . $solicitud->tipo_solicitud . '_' . time() . '.pdf';
                return response($response->body(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$pdfName.'"',
                ]);
            }
            return back()->with('error', 'El conversor falló al procesar el documento.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo conectar con el microservicio.');
        }
    }

        public function descargarOficioAdmin($id)
    {
        $solicitud = \App\Models\Solicitud::findOrFail($id);

        if (!$solicitud->pdf_generado_path) {
            return back()->with('error', 'Este trámite ya no tiene documentos disponibles (fueron eliminados por la limpieza periódica de archivos antiguos).');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.oficio', compact('solicitud'));
        return $pdf->download('Oficio_' . $solicitud->tipo_solicitud . '_' . $solicitud->id . '.pdf');
    }

    public function limpiezaIndex()
    {
                return view('admin.limpieza');
    }


    /**
     * Ejecuta la limpieza manual con el periodo que elija el admin en el
     * formulario -- usa el mismo servicio que corre solo automáticamente
     * todos los días, así que nunca se desincronizan.
     */
    public function limpiezaEjecutar(Request $request, \App\Services\LimpiezaSolicitudesService $servicio)
    {
        $validated = $request->validate([
            'meses' => 'required|integer|min:0|max:24',
        ], [
            'meses.required' => 'Indica a partir de cuántos meses de antigüedad quieres borrar.',
        ]);

        $resultado = $servicio->limpiar((int) $validated['meses'], false);

        if ($resultado['solicitudes_afectadas'] === 0) {
            return back()->with('success', 'No había archivos que cumplieran el periodo indicado -- no se borró nada.');
        }

        $mensaje = sprintf(
            'Se liberaron %.2f MB en %d solicitud(es).',
            $resultado['liberados_mb'],
            $resultado['solicitudes_afectadas']
        );

        if ($resultado['errores'] > 0) {
            $mensaje .= " ({$resultado['errores']} archivo(s) no se pudieron borrar, revisa permisos.)";
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Pantalla para editar el destinatario (AT'N) y la firma del Oficio.
     */
    public function configuracionOficioIndex()
    {
        $configuracion = \App\Models\ConfiguracionOficio::actual();

        return view('admin.configuracion_oficio', compact('configuracion'));
    }

    public function configuracionOficioActualizar(Request $request)
    {
        $validated = $request->validate([
            'atn_nombre' => ['required', 'string', 'max:255'],
            'atn_puesto' => ['required', 'string', 'max:255'],
            'atn_lugar' => ['required', 'string', 'max:255'],
            'firma_nombre' => ['required', 'string', 'max:255'],
            'firma_puesto' => ['required', 'string', 'max:255'],
            'firma_lugar' => ['required', 'string', 'max:255'],
        ]);

        \App\Models\ConfiguracionOficio::actual()->update($validated);

        return back()->with('success', 'Los datos del oficio se actualizaron correctamente.');
    }
}
