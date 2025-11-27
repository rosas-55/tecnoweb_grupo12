<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $key = "page_visits:{$path}";
        
        // Incrementar contador de visitas para esta página
        Cache::increment($key, 1);
        
        // Si no existe, inicializarlo en 1
        if (!Cache::has($key)) {
            Cache::put($key, 1);
        }

        return $next($request);
    }
}
