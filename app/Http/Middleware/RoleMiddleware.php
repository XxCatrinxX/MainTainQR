<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->rol, $roles)) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para realizar esa acción. Rol requerido: ' . implode(' o ', $roles) . '.');
        }

        return $next($request);
    }
}
