<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;
use App\Models\Puesto;
use App\Models\Servicio;
use App\Models\CategoriaServicio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class SolicitudController extends Controller
{
    private function getCatalogo()
    {
        return [
            'ABC' => [
                'nombre' => 'Cuentas de Red y Correo Institucional (ABC)',
                'categorias' => [
                    'AD USUARIO' => 'AD USUARIO (Cuenta de Red)',
                    'AD-EX USUARIO Y CUENTA DE CORREO' => 'AD-EX USUARIO Y CUENTA DE CORREO (Red y Correo)',
                    'EX CUENTA DE CORREO' => 'EX CUENTA DE CORREO (Solo Correo Institucional)',
                ]
            ],
            'LICENCIA' => [
                'nombre' => 'Licencias de Software Institucional',
                'categorias' => [
                    'Licencia Office 365' => 'Licencia Office 365 (Paquetería Office)',
                ]
            ],
            'HARDWARE' => [
                'nombre' => 'Hardware / Equipo de Cómputo',
                'categorias' => [
                    'Computadora / Laptop' => 'Asignación de Computadora / Laptop',
                    'Impresora / Escaner' => 'Asignación de Impresora / Escáner',
                ]
            ],
        ];
    }

    public function index()
    {
        $solicitudes = Solicitud::where('usuario_id', Auth::id())->latest()->get();
        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        $puestos = Puesto::orderBy('nombre_puesto', 'asc')->get();
        $serviciosCatalogo = $this->getCatalogo();
        return view('dashboard', compact('puestos', 'serviciosCatalogo'));
    }

    public function store(Request $request)
    {
                $request->validate([
            'tipo_solicitud' => 'required|in:ALTA,BAJA',
            'servicio' => 'required|string',
            'categoria' => 'required|string',
            'justificacion' => 'required|string',
            'puesto_id' => 'required|exists:puestos,id',
            'imagen_evidencia' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg,pdf',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value->getClientMimeType() === 'application/pdf') {
                        $resultado = \Illuminate\Support\Facades\Process::run(['pdfinfo', $value->getRealPath()]);
                        if (!$resultado->successful() || !preg_match('/Pages:\s*(\d+)/', $resultado->output(), $m)) {
                            $fail('No se pudo leer el archivo PDF. Verifica que no esté dañado.');
                            return;
                        }
                        $paginas = (int) $m[1];
                        if ($paginas > 1) {
                            $fail("El PDF debe tener solo 1 página (el archivo que subiste tiene {$paginas}).");
                        }
                    }
                },
            ],
        ]);

        $usuario = Auth::user();
        $catalogo = $this->getCatalogo();
        
        $servicioNombre = $catalogo[$request->servicio]['nombre'];
        $categoriaNombre = $catalogo[$request->servicio]['categorias'][$request->categoria];

        $servicioDb = Servicio::firstOrCreate(['nombre_servicio' => $servicioNombre]);

        $categoriaDb = CategoriaServicio::firstOrCreate([
            'nombre_categoria' => $categoriaNombre,
            'servicio_id' => $servicioDb->id
        ]);

        // BLOQUEO ESTRICTO: Solo permite 1 trámite por servicio, sin importar
        // el estado. Si está Rechazada, se corrige por "Corregir y reenviar"
        // (edita la misma solicitud), no creando una nueva desde cero.
        $existeSolicitud = Solicitud::where('usuario_id', $usuario->id)
            ->where('tipo_solicitud', $request->tipo_solicitud)
            ->where('servicio_id', $servicioDb->id)
            ->exists();

        if ($existeSolicitud) {
            return back()->withErrors([
                'categoria' => "Ya cuentas con una solicitud de {$request->tipo_solicitud} para '{$servicioNombre}'. No se permite duplicar trámites del mismo servicio."
            ])->withInput();
        }

        if ($request->categoria === 'Licencia Office 365' && $request->tipo_solicitud === 'ALTA') {
            $nombreCategoriaEquipo = $catalogo['HARDWARE']['categorias']['Computadora / Laptop'];
            $catEquipoDb = CategoriaServicio::where('nombre_categoria', $nombreCategoriaEquipo)->first();

            $tieneEquipo = false;
            if ($catEquipoDb) {
                $tieneEquipo = Solicitud::where('usuario_id', $usuario->id)
                    ->where('tipo_solicitud', 'ALTA')
                    ->where('categoria_id', $catEquipoDb->id)
                    ->exists();
            }

            if (!$tieneEquipo) {
                return back()->withErrors([
                    'categoria' => 'No puedes solicitar la Paquetería Office 365 si no cuentas previamente con una solicitud aprobada de Equipo de Cómputo.'
                ])->withInput();
            }
        }

        $rutaImagen = 'pendiente.jpg';
        if ($request->hasFile('imagen_evidencia')) {
            $file = $request->file('imagen_evidencia');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $rutaImagen = $file->storeAs('evidencias', $nombreArchivo, 'public');
        }

        $usuario->update(['puesto_id' => $request->puesto_id]);

        $solicitud = new Solicitud();
        $solicitud->usuario_id = $usuario->id;
        $solicitud->tipo_solicitud = $request->tipo_solicitud;
        $solicitud->servicio_id = $servicioDb->id;
        $solicitud->categoria_id = $categoriaDb->id;
        $solicitud->justificacion = $request->justificacion;
        $solicitud->imagen_evidencia_path = $rutaImagen;
        $solicitud->estado_solicitud = 'Pendiente';
        $solicitud->save();

        return $this->generarFormatoOficial($solicitud, $usuario, $servicioNombre, $categoriaNombre, $request->justificacion);
    }

    private function generarFormatoOficial($solicitud, $usuario, $servicioNombre, $categoriaNombre, $justificacionLimpia)
    {
        $templatePath = base_path('database/data/Formato_CCUSC_DSI_V8.0_Alta_de_buzon.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('Formato'); 

        $sheet->setCellValue('H8', $solicitud->created_at->format('Y-m-d'));
        $sheet->setCellValue('H10', $usuario->nombres);
        $sheet->setCellValue('T10', $usuario->apellidos);
        $sheet->setCellValue('H11', $usuario->numero_empleado);
        $sheet->setCellValue('N11', $usuario->puesto->nombre_puesto ?? 'Personal');
        $sheet->setCellValue('AB11', 'Por TI'); 
        $sheet->setCellValue('Y16', 'Por asignar por TI');  
        $sheet->setCellValue('Y17', 'Por asignar por TI');  
        $sheet->setCellValue('H19', $solicitud->tipo_solicitud);
        $sheet->setCellValue('X19', $usuario->tipo_contratacion ?? 'N/A');
        $sheet->setCellValue('H20', $servicioNombre);
        $sheet->setCellValue('H21', $categoriaNombre);
        $sheet->setCellValue('D30', $justificacionLimpia);
        $sheet->getStyle('D30')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('D23', 'Si el tipo de solicitud es una alta de usuario y/o cuenta de correo electrónico de nuevo ingreso, previamente debe levantar el ticket de la baja del servidor publico que deja el cargo e indicar el usuario y No. de ticket.');
        $sheet->setCellValue('D28', 'En caso de solicitar un alta de usuario y/o de cuenta de correo electrónico para personal operativo, favor de indicar el detalle de las funciones del usuario que lo justifique.');
        $sheet->setCellValue('D38', 'USUARIO');
        $sheet->setCellValue('K38', 'ENLACE INFORMATICO');
        $sheet->setCellValue('T38', 'JEFE DE SERVICIOS O SUPERIOR');
        $sheet->setCellValue('O14', 'Unidad Medica'); 
        $sheet->setCellValue('H15', 'Subdireccion Medica CH'); 
        $configExcel = \App\Models\ConfiguracionExcel::actual();
        $sheet->setCellValue('K34', $configExcel->autoriza_nombre);
        $sheet->setCellValue('K35', $configExcel->autoriza_cargo);
        $sheet->setCellValue('K39', $configExcel->enlace_nombre);
        $sheet->setCellValue('T39', $configExcel->jefe_servicios_nombre); 

        $writer = new XlsxWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        
        $fileName = 'Solicitud_' . $solicitud->tipo_solicitud . '_' . $usuario->numero_empleado . '_' . time() . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);
        
        $writer->save($filePath);
        $solicitud->update(['pdf_generado_path' => $fileName]);

        return redirect()->route('solicitudes.index')->with('success', '¡Solicitud generada con éxito! Ya puedes descargar tu formato.');
    }

        public function edit($id)
    {
        $solicitud = Solicitud::with(['servicio', 'categoria'])
            ->where('usuario_id', Auth::id())
            ->where('estado_solicitud', 'Rechazado')
            ->findOrFail($id);

        return view('dashboard', compact('solicitud'));
    }

        public function update(Request $request, $id)
    {
        $solicitud = Solicitud::where('usuario_id', Auth::id())
            ->where('estado_solicitud', 'Rechazado')
            ->findOrFail($id);

        $request->validate([
            'justificacion' => 'required|string',
            'imagen_evidencia' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg,pdf',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value->getClientMimeType() === 'application/pdf') {
                        $resultado = \Illuminate\Support\Facades\Process::run(['pdfinfo', $value->getRealPath()]);
                        if (!$resultado->successful() || !preg_match('/Pages:\s*(\d+)/', $resultado->output(), $m)) {
                            $fail('No se pudo leer el archivo PDF. Verifica que no esté dañado.');
                            return;
                        }
                        $paginas = (int) $m[1];
                        if ($paginas > 1) {
                            $fail("El PDF debe tener solo 1 página (el archivo que subiste tiene {$paginas}).");
                        }
                    }
                },
            ],
        ]);

        $usuario = Auth::user();
        $servicioNombre = $solicitud->servicio->nombre_servicio;
        $categoriaNombre = $solicitud->categoria->nombre_categoria;

        $file = $request->file('imagen_evidencia');
        $nombreArchivo = time() . '_' . $file->getClientOriginalName();
        $rutaImagen = $file->storeAs('evidencias', $nombreArchivo, 'public');

        $solicitud->justificacion = $request->justificacion;
        $solicitud->imagen_evidencia_path = $rutaImagen;
        $solicitud->estado_solicitud = 'Pendiente';
        $solicitud->credencial_usuario = null;
        $solicitud->credencial_password = null;
        $solicitud->credencial_notas = null;
        $solicitud->save();

        return $this->generarFormatoOficial($solicitud, $usuario, $servicioNombre, $categoriaNombre, $request->justificacion);
    }

    public function descargarPdf($id)
    {
        $solicitud = Solicitud::where('usuario_id', Auth::id())->findOrFail($id);

        if (!$solicitud->pdf_generado_path) {
            return back()->with('error', 'Este archivo ya no está disponible (fue eliminado por la limpieza periódica de archivos antiguos).');
        }

        $path = storage_path('app/public/' . $solicitud->pdf_generado_path);

        if (!file_exists($path)) {
            return back()->with('error', 'Este archivo ya no está disponible en el servidor.');
        }

        return response()->download($path);
    }

    public function convertirAPdf($id)
    {
        $solicitud = Solicitud::where('usuario_id', Auth::id())->findOrFail($id);
        $rutaExcel = storage_path('app/public/' . $solicitud->pdf_generado_path);

        if (!file_exists($rutaExcel)) {
            return back()->with('error', 'Este archivo ya no está disponible (fue eliminado por la limpieza periódica de archivos antiguos, o aún no se genera).');
        }

        try {
            $response = Http::attach(
                'archivo', file_get_contents($rutaExcel), 'documento.xlsx'
            )->post('http://localhost:5000/convertir');

            if ($response->successful()) {
                $pdfContent = $response->body();
                $pdfName = 'Solicitud_' . $solicitud->tipo_solicitud . '_' . time() . '.pdf';
                return response($pdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$pdfName.'"',
                ]);
            }
            return back()->with('error', 'El conversor falló al procesar el documento.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo conectar con el microservicio.');
        }
    }

       public function descargarOficio($id)
    {
        $solicitud = Solicitud::where('usuario_id', Auth::id())->findOrFail($id);

        if (!$solicitud->pdf_generado_path) {
            return back()->with('error', 'Este trámite ya no tiene documentos disponibles (fueron eliminados por la limpieza periódica de archivos antiguos).');
        }

        $pdf = Pdf::loadView('pdf.oficio', compact('solicitud'));
        return $pdf->download('Oficio_' . $solicitud->tipo_solicitud . '_' . $solicitud->id . '.pdf');
    }
}
