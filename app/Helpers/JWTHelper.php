<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    private static $secretKey = null;

    public static function getSecretKey()
    {
        if (self::$secretKey === null) {
            self::$secretKey = env('JWT_SECRET', 'default_secret_key'); // Fallback para chave padrão
        }
        return self::$secretKey;
    }

    public static function encode($payload)
    {
        return JWT::encode($payload, self::getSecretKey(), 'HS256');
    }

    public static function decode($token)
    {
        try {
            return JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
