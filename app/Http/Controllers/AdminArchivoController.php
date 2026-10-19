<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;

class AdminArchivoController extends Controller
{
    // Abre la evidencia (imagen o PDF) solo para administradores
    public function evidencia($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $ruta = storage_path('app/public/' . $solicitud->imagen_evidencia_path);

        abort_unless($solicitud->imagen_evidencia_path && is_file($ruta), 404);

        return response()->file($ruta);
    }

    // Descarga el Excel generado solo para administradores
    public function excel($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $ruta = storage_path('app/public/' . $solicitud->pdf_generado_path);

        abort_unless($solicitud->pdf_generado_path && is_file($ruta), 404);

        return response()->download($ruta);
    }
}