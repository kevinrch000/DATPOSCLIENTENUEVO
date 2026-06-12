<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

$objUsuario = new BEUsuario();
$objUsuario->ccod_empresa = 'EMP01';
$objUsuario->ccod_usuario = 'ADMIN';
$objUsuario->id_rol = 1;
$objUsuario->cnombre_bd = 'DatPos_EMP01';
$objUsuario->cnomser = 'localhost\\SQLEXPRESS';

try {
    $rows = Database::selectStoredTenant('sp_helptext', array('@objname' => 'webDatpos_editarTurno'), $objUsuario);
    foreach ($rows as $row) {
        echo $row[0];
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
