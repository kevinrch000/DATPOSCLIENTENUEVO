<?php
/**
 * DatPOS - Configuracion JWT
 * 
 * IMPORTANTE: Cambie JWT_SECRET por un valor unico y seguro en produccion.
 * Puede generar uno con: php -r "echo bin2hex(random_bytes(32));"
 */

// Clave secreta para firmar tokens JWT (HMAC-SHA256)
// CAMBIAR EN PRODUCCION - minimo 32 caracteres aleatorios
define('JWT_SECRET', '31b1fd0d94ccea8898685fef4f061d7a4c5f3334a9c73e4622045a009924059f');

// Tiempo de vida del token en segundos (8 horas = 28800)
define('JWT_TTL', 28800);

// Nombre de la cookie donde se almacena el token
define('JWT_COOKIE_NAME', 'datpos_token');

// La cookie solo se envia por HTTPS (activar en produccion)
define('JWT_COOKIE_SECURE', false);

// La cookie no es accesible desde JavaScript
define('JWT_COOKIE_HTTPONLY', true);

// SameSite policy para la cookie
define('JWT_COOKIE_SAMESITE', 'Lax');
?>
