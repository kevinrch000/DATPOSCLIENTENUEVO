<?php
// Session mock
session_start();
require_once __DIR__ . '/../BE/BEUsuario.php';
$u = new BEUsuario();
$u->ccod_usuario   = 'ADMIN';
$u->cdsc_usuario   = 'ADMINISTRADOR';
$u->ccod_empresa   = 'EMP01';
$u->cnombre_bd     = 'DatPos_EMP01';
$u->cnomser        = 'localhost\\SQLEXPRESS';
$u->ccod_tiend     = 'T001';
$u->ccod_almacen   = 'ALM001';
$u->ccod_caja      = 'CAJ01';
$u->id_rol         = 1;
$_SESSION['objBEUsuario'] = $u;

// We will query the DB directly to test our new logic
require_once __DIR__ . '/../config/database.php';
$conn = Database::getTenantConnection($u);
if (!$conn) {
    die("No connection to tenant DB\n");
}

$ccod_lin = 'FAM01'; // Let's test with family 'FAM01' (or first family we find)
$stmtFam = sqlsrv_query($conn, "SELECT TOP 1 ccod_lin FROM Familias WHERE ccod_cia = ?", array($u->ccod_empresa));
if ($stmtFam && ($rowFam = sqlsrv_fetch_array($stmtFam, SQLSRV_FETCH_NUMERIC))) {
    $ccod_lin = $rowFam[0];
}
echo "Testing with Family Code: " . $ccod_lin . "\n";

$likePattern = $ccod_lin . '[0-9][0-9][0-9][0-9][0-9]';
$sql = "SELECT TOP 1 ccod_articulo 
        FROM Articulos 
        WHERE ccod_cia = ? AND ccod_lin = ? AND ccod_articulo LIKE ? 
        ORDER BY ccod_articulo DESC";
$stmt = sqlsrv_query($conn, $sql, array($u->ccod_empresa, $ccod_lin, $likePattern));
if ($stmt) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
    if ($row) {
        $lastCode = strval($row[0] ?? '');
        $correlativoStr = substr($lastCode, -5);
        $correlativo = intval($correlativoStr);
        $nextCorrelativo = $correlativo + 1;
        $nextCode = $ccod_lin . str_pad($nextCorrelativo, 5, '0', STR_PAD_LEFT);
        echo "Found last code: $lastCode -> Next code should be: $nextCode\n";
    } else {
        $nextCode = $ccod_lin . '00001';
        echo "No existing articles found matching pattern. Next code: $nextCode\n";
    }
    sqlsrv_free_stmt($stmt);
} else {
    echo "SQL error: " . print_r(sqlsrv_errors(), true) . "\n";
}

sqlsrv_close($conn);
echo "Test finished successfully\n";
?>
