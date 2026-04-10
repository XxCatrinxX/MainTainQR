<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        dd('RoleMiddleware hit', [
            'user' => $request->user()->email ?? 'None',
            'role' => $request->user()->rol ?? 'None',
            'required' => $roles
        ]);
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