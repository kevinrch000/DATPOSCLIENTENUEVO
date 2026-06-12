<?php
/**
 * DatPOS - Configuración de Email (PHPMailer + Gmail SMTP)
 * 
 * Requiere una "App Password" de Google (no la contraseña normal).
 * Generarla en: https://myaccount.google.com/apppasswords
 * 
 * IMPORTANTE: En producción, mover estas credenciales a variables de entorno.
 */

// Configuración SMTP
define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_PORT', 587);
define('EMAIL_ENCRYPTION', 'tls');  // TLS para puerto 587

// Credenciales Gmail
define('EMAIL_USER', 'amazonprume6@gmail.com');
define('EMAIL_PASS', 'ytaz fhzf loql sxml');  // App Password de Google (16 caracteres)

// Datos del remitente
define('EMAIL_FROM_NAME', 'DatPOS - Facturación');
define('EMAIL_FROM_ADDRESS', 'amazonprume6@gmail.com');

// Configuración adicional
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_DEBUG', 0);  // 0 = off, 1 = client, 2 = client+server (para debug)
?>
