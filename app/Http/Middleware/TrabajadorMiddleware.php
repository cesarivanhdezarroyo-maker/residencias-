<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo personal solicitante (rol distinto de admin). El administrador recibe 403,
 * igual que un trabajador recibe 403 en las rutas de AdminMiddleware.
 */
class TrabajadorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (!$usuario || $usuario->rol === 'admin') {
            abort(403, 'Este apartado es exclusivo del personal solicitante.');
        }

        return $next($request);
    }
}
