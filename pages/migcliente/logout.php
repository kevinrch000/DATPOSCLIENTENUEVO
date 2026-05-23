<?php
/**
 * DatPOS - Cerrar Sesion
 * Equivale a: btnCerrarSesion.ServerClick en Site.Master.vb
 * 
 * Destruye la sesion PHP y elimina la cookie JWT.
 */
require_once __DIR__ . '/../../includes/auth.php';
cerrarSesion();
?>
