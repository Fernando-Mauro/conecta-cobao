<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use JWTAuth;
use Illuminate\Support\Facades\Cookie;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        // Intenta obtener el token desde la cookie
        $token = Cookie::get('jwt');

        if (!$token) {
            // Si no hay token en la cookie, intenta obtenerlo de la cabecera Authorization
            $token = $request->header('Authorization');
            // Verifica si la cabecera tiene el prefijo 'Bearer'
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7); // Elimina el prefijo 'Bearer '
            }
        }

        try {
            // Intenta autenticar al usuario con el token
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (Exception $e) {
            // Maneja las excepciones relacionadas con el token
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => 'Token is Invalid']);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => 'Token is Expired'], 403);
            } else {
                return response()->json(['status' => 'Authorization Token not found']);
            }
        }

        // Asigna el usuario autenticado a la solicitud
        $request->attributes->add(['authenticated_user' => $user]);

        return $next($request);
    }
}
