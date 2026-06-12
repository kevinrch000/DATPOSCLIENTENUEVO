<?php
// Test database query for appDatpos_abrirCaja with PEPE0123
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';
require_once __DIR__ . '/../BE/BEAperturaCaja.php';
require_once __DIR__ . '/../BL/BLAperturaCaja.php';

$objUsuario = new BEUsuario();
$objUsuario->ccod_empresa = 'EMP01';
$objUsuario->ccod_usuario = 'ADMIN';
$objUsuario->id_rol = 1;
$objUsuario->cnombre_bd = 'DatPos_EMP01';
$objUsuario->cnomser = 'localhost\\SQLEXPRESS';

$objBL = new BLAperturaCaja();
$DatTurno = new BEAperturaCaja();
$DatTurno->ccod_tienda = 'T001';
$DatTurno->ccod_usuario = 'PEPE0123';
$DatTurno->ccod_caja = 'CAJ01';
$DatTurno->nmonto_ini = 120.00;
$DatTurno->dfchdoc_ini = date('Y-m-d H:i:s');

echo "Opening turn via BL for PEPE0123...\n";
try {
    $rows = $objBL->AperturarCaja($DatTurno, $objUsuario);
    echo "AperturarCaja rows returned:\n";
    print_r($rows);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
