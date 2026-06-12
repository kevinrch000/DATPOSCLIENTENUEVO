<?php
/**
 * DatPOS - JWT Helper (Pure PHP, HMAC-SHA256)
 * 
 * Implementacion ligera de JSON Web Tokens sin dependencias externas.
 * Usa HMAC-SHA256 para firma y verificacion.
 *
 * Uso:
 *   JWT::init('mi_clave_secreta');
 *   $token = JWT::encode(['user' => 'admin'], 3600);
 *   $payload = JWT::decode($token); // null si invalido/expirado
 */

class JWT
{
    private static $secret = '';
    private static $algo = 'sha256';

    /**
     * Inicializar con la clave secreta.
     * Debe llamarse una vez antes de encode/decode.
     */
    public static function init($secret)
    {
        self::$secret = $secret;
    }

    /**
     * Codificacion Base64 URL-safe (sin padding)
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificacion Base64 URL-safe
     */
    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Generar un token JWT.
     *
     * @param array $payload  Datos del usuario a incluir en el token
     * @param int   $ttl      Tiempo de vida en segundos (default: 8 horas)
     * @return string          Token JWT (header.payload.signature)
     */
    public static function encode(array $payload, $ttl = 28800)
    {
        $header = self::base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        // Agregar timestamps
        $now = time();
        $payload['iat'] = $now;              // Issued At
        $payload['exp'] = $now + $ttl;       // Expiration
        $payload['jti'] = bin2hex(random_bytes(16)); // Unique ID

        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = self::base64UrlEncode(
            hash_hmac(self::$algo, "{$header}.{$payloadEncoded}", self::$secret, true)
        );

        return "{$header}.{$payloadEncoded}.{$signature}";
    }

    /**
     * Decodificar y verificar un token JWT.
     *
     * @param string $token  Token JWT completo
     * @return array|null    Payload decodificado, o null si invalido/expirado
     */
    public static function decode($token)
    {
        if (empty($token) || empty(self::$secret)) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        // Verificar firma
        $expectedSignature = self::base64UrlEncode(
            hash_hmac(self::$algo, "{$headerB64}.{$payloadB64}", self::$secret, true)
        );

        if (!hash_equals($expectedSignature, $signatureB64)) {
            return null;
        }

        // Decodificar payload
        $payload = json_decode(self::base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }

        // Verificar expiracion
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Renovar un token existente (extender expiracion).
     * Devuelve un nuevo token con los mismos datos pero nueva expiracion.
     *
     * @param string $token  Token JWT actual
     * @param int    $ttl    Nuevo tiempo de vida en segundos
     * @return string|null   Nuevo token, o null si el token original es invalido
     */
    public static function refresh($token, $ttl = 28800)
    {
        $payload = self::decode($token);
        if ($payload === null) {
            return null;
        }

        // Remover claims de tiempo anteriores
        unset($payload['iat'], $payload['exp'], $payload['jti']);

        return self::encode($payload, $ttl);
    }
}
?>
