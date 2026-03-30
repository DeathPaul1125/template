<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si no existe el archivo de instalación terminada y no estamos en una ruta de instalación
        if (!file_exists(storage_path('installed')) && !$request->is('install*')) {
            return redirect()->route('install.welcome');
        }

        return $next($request);
    }
}
