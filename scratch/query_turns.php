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
    $conn = Database::getTenantConnection($objUsuario);
    if (!$conn) {
        die("Connection failed\n");
    }
    
    $query = "SELECT id_turno, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin, dfchdoc_ini, dfchdoc_fin, cstatus FROM Turno ORDER BY id_turno DESC";
    $stmt = sqlsrv_query($conn, $query);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
