<?php

namespace App\Http\Middleware;

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

        return $next($request);
    }
}