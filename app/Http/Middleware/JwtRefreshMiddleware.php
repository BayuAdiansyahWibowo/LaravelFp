<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtHelper;
use App\Models\Driver;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtRefreshMiddleware
{
    // Waktu threshold (detik) sebelum token expired → 10 menit = 600 detik
    protected $refreshThreshold = 600;

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        try {
            $decoded = JwtHelper::decode($token);
            $driverId = $decoded->data->id;

            $driver = Driver::find($driverId);
            if (!$driver) {
                return response()->json(['message' => 'Driver tidak ditemukan'], 401);
            }

            // Inject ke request
            $request->attributes->add(['driver' => $driver]);

            // Cek apakah perlu refresh token
            $now = time();
            $exp = $decoded->exp ?? 0;

            $shouldRefresh = ($exp - $now) <= $this->refreshThreshold;

            // Proses request ke controller
            $response = $next($request);

            // Jika perlu refresh, generate token baru dan kirim di header
            if ($shouldRefresh) {
                $newToken = JwtHelper::generateToken($driver);
                $response->headers->set('X-Refresh-Token', $newToken);
            }

            return $response;

        } catch (ExpiredException $e) {
            return response()->json(['message' => 'Token telah kedaluwarsa'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }
    }
}
