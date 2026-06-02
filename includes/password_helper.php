<?php
/**
 * DatPOS - Password Helper
 * 
 * Manejo seguro de contrasenas con bcrypt (password_hash / password_verify).
 * 
 * Estrategia de migracion:
 * - Las contrasenas existentes estan almacenadas como MD5 hex en la columna cpassw.
 * - Al hacer login, si la contrasena en BD es un hash MD5 (32 chars hex),
 *   se verifica md5(input) == stored_hash, y luego se migra a bcrypt.
 * - Las contrasenas nuevas se almacenan directamente como bcrypt.
 * - La columna cpassw_bcrypt almacena el hash bcrypt (VARCHAR(255)).
 *   Mientras esta vacia, se usa cpassw (MD5 legacy).
 */

class PasswordHelper
{
    /**
     * Generar hash bcrypt de una contrasena en texto plano.
     *
     * @param string $plainPassword  Contrasena en texto plano
     * @return string                Hash bcrypt (60 chars)
     */
    public static function hash($plainPassword)
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verificar una contrasena contra un hash bcrypt.
     *
     * @param string $plainPassword  Contrasena en texto plano
     * @param string $hash           Hash bcrypt almacenado
     * @return bool
     */
    public static function verify($plainPassword, $hash)
    {
        return password_verify($plainPassword, $hash);
    }

    /**
     * Verificar contrasena con soporte de migracion a bcrypt.
     * 
     * Logica:
     * 1. Si hay hash bcrypt (cpassw_bcrypt), verificar con bcrypt
     * 2. Si cpassw es un hash MD5 (32 hex chars), comparar md5(input) == cpassw
     * 3. Si cpassw es texto plano (legacy), comparar directamente
     * 4. Si coincide por MD5 o texto plano, marcar para migracion
     *
     * @param string      $plainPassword   Contrasena en texto plano ingresada por el usuario
     * @param string      $storedPassword  Contrasena almacenada en cpassw (MD5 hex o texto plano)
     * @param string|null $bcryptHash      Hash bcrypt almacenado en cpassw_bcrypt (null si no migrado)
     * @return array ['valid' => bool, 'needs_migration' => bool]
     */
    public static function verifyWithMigration($plainPassword, $storedPassword, $bcryptHash = null)
    {
        // Caso 1: Ya tiene hash bcrypt → verificar con bcrypt
        if (!empty($bcryptHash)) {
            return [
                'valid' => password_verify($plainPassword, $bcryptHash),
                'needs_migration' => false
            ];
        }

        // Caso 2: cpassw es un hash MD5 (32 caracteres hexadecimales)
        if (self::isMd5Hash($storedPassword)) {
            $inputMd5 = md5($plainPassword);
            $valid = hash_equals($storedPassword, $inputMd5);
            return [
                'valid' => $valid,
                'needs_migration' => $valid
            ];
        }

        // Caso 3: cpassw es texto plano (legacy)
        $valid = ($plainPassword === $storedPassword);
        return [
            'valid' => $valid,
            'needs_migration' => $valid
        ];
    }

    /**
     * Verificar si un string parece un hash MD5 (32 caracteres hexadecimales).
     *
     * @param string $str
     * @return bool
     */
    public static function isMd5Hash($str)
    {
        return (bool)preg_match('/^[a-f0-9]{32}$/i', $str);
    }
}
?>
