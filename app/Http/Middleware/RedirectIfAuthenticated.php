<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Si estás utilizando JWT, podrías modificar este bloque
                // para manejar la respuesta de acuerdo a tus necesidades.
                // Puedes devolver un JSON, un mensaje de error, etc.
                abort(403, 'Acceso no autorizado');
            }
        }

        return $next($request);
    }
}
