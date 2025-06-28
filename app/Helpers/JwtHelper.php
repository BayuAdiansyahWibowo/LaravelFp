<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Log;

class JwtHelper
{
    /**
     * Ambil secret key dari file .env
     */
    private static function getSecretKey(): string
    {
        return env('JWT_SECRET', 'defaultsecretkey');
    }

    /**
     * Buat token JWT
     */
    public static function encode(array $payload, int $expiryInSeconds = 3600): string
    {
        // Tambahkan waktu kadaluarsa (exp) jika belum ada
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + $expiryInSeconds;
        }

        return JWT::encode($payload, self::getSecretKey(), 'HS256');
    }

    /**
     * Validasi apakah token valid dan belum kedaluwarsa
     */
    public static function validate(string $token): bool
    {
        try {
            self::decode($token);
            return true;
        } catch (ExpiredException $e) {
            Log::warning('JWT expired: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::warning('JWT validation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decode token JWT, return null jika gagal
     */
    public static function safeDecode(string $token): ?object
    {
        try {
            return self::decode($token);
        } catch (\Exception $e) {
            Log::warning('JWT decode failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decode token JWT, melempar exception jika gagal
     */
    public static function decode(string $token): object
    {
        return JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
    }
}
