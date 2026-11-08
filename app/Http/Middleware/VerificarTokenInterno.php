<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarTokenInterno
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.interno.token');

        if (! $token || ! hash_equals($token, (string) $request->header('X-Internal-Token'))) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        return $next($request);
    }
}