<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next, $role = null): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        try {
            $key = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // --- CORRECCIÓN AQUÍ ---
            // Convertimos el objeto $decoded a (array) para que Laravel no de error "Expected scalar..."
            $decodedArray = (array) $decoded;

            // Guardamos los datos como 'user' en la petición
            $request->merge(['user' => $decodedArray]);

            // Validar ROL (accediendo ahora como array)
            if ($role && $decodedArray['role'] !== $role) {
                return response()->json([
                    'error' => 'Acceso denegado. Se requiere rol: ' . $role
                ], 403);
            }

        } catch (Exception $e) {
            return response()->json(['error' => 'Token inválido: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}