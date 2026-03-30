<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si el sistema ya está instalado y se intenta acceder al instalador
        if (file_exists(storage_path('installed'))) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
