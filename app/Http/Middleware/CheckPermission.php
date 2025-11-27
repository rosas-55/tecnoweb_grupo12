<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->hasPermission($permission)) {
            // Si es una petición Inertia, retornar error 403
            if ($request->header('X-Inertia')) {
                return Inertia::render('Errors/403', [
                    'message' => 'No tienes permiso para acceder a esta página.'
                ])->toResponse($request)->setStatusCode(403);
            }

            // Si es una petición normal, abortar con 403
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}
