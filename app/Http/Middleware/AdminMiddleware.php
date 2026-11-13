<?php

namespace App\Http\Middleware;

use App\Support\RegistroBitacora;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // El candado verifica el rol del usuario
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'Acceso denegado. Área exclusiva del Departamento de Sistemas TI.');
        }

        // Bitácora: todo lo que el administrador CAMBIA (no las consultas) queda registrado
        $esCambio = !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
        $contexto = $esCambio ? RegistroBitacora::contexto($request) : null;

        $respuesta = $next($request);

        // Solo se registra si la acción terminó bien (sin error y sin fallar la validación del formulario)
        if (
            $contexto !== null
            && $respuesta->getStatusCode() < 400
            && !($request->hasSession() && $request->session()->has('errors'))
        ) {
            RegistroBitacora::registrar($request, $contexto);
        }

        return $respuesta;
    }
}
