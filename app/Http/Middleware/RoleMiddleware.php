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
            \Illuminate\Support\Facades\Log::warning('RoleMiddleware Access Denied', [
                'user_id' => $request->user()->id ?? 'Guest',
                'user_rol' => $request->user()->rol ?? 'None',
                'expected_roles' => $roles,
                'path' => $request->path(),
                'method' => $request->method()
            ]);

            return redirect()->route('home')
                ->with('error', 'No tienes permiso para realizar esa acción. Tu rol es "' . ($request->user()->rol ?? 'ninguno') . '" y se requiere: ' . implode(' o ', $roles) . '.');
        }

        return $next($request);
    }
}
