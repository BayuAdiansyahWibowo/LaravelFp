<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtHelper;
use App\Models\Driver;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
{
    $token = $request->bearerToken();

    if (!$token) {
        return response()->json(['message' => 'Token tidak ditemukan'], 401);
    }

    try {
        $decoded = JwtHelper::decode($token);

        // Validasi user ID dari token
        if (!isset($decoded->sub)) {
            return response()->json(['message' => 'Token tidak valid (User ID tidak ditemukan)'], 401);
        }

        // Ambil data driver berdasarkan user_id
        $driver = Driver::where('user_id', $decoded->sub)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver tidak ditemukan'], 401);
        }

        // Masukkan driver ke request
        $request->attributes->add(['driver' => $driver]);

    } catch (ExpiredException $e) {
        return response()->json(['message' => 'Token telah kedaluwarsa'], 401);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Token tidak valid',
        ], 401);
    }

    return $next($request);
}

}
