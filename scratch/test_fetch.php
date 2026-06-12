<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

$user = new BEUsuario();
$user->ccod_empresa = 'EMP01';
$user->cnombre_bd = 'DatPos_EMP01';
$user->cnomser = 'localhost\\SQLEXPRESS';

$conn = Database::getTenantConnection($user);
if (!$conn) {
    echo "No se pudo conectar a la base de datos del tenant.\n";
    exit(1);
}

$serie = 'B001';
$correlativo = 69; // nnumero is INT

$sqlHeader = "SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.fecha_emision,
                     F.nsubtotal, F.nimpuesto, F.ntotal, F.nvuelto, F.ntot_entreg, F.cobs,
                     C.cdsc_coa, C.cruc_coa, C.cdirc_coa, F.ccod_tiend, F.ccod_caja
              FROM CbFactura F
              LEFT JOIN Coa C ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
              WHERE F.ccod_cia = ? AND F.cserie = ? AND F.nnumero = ?";

$stmtHeader = sqlsrv_query($conn, $sqlHeader, array($user->ccod_empresa, $serie, $correlativo));
if (!$stmtHeader) {
    echo "Error al obtener cabecera:\n";
    print_r(sqlsrv_errors());
    sqlsrv_close($conn);
    exit(1);
}

$header = null;
if ($row = sqlsrv_fetch_array($stmtHeader, SQLSRV_FETCH_ASSOC)) {
    $header = $row;
}
sqlsrv_free_stmt($stmtHeader);

if (!$header) {
    echo "Comprobante no encontrado.\n";
    sqlsrv_close($conn);
    exit(1);
}

echo "CABECERA:\n";
print_r($header);

$sqlDetails = "SELECT id_articulo, cdsc_articulo, nprecio, ncantidad, nimporte_neto
               FROM LnFactura
               WHERE ccod_cia = ? AND id_cbfact = ?
               ORDER BY corden ASC, id_lnfact ASC";

$stmtDetails = sqlsrv_query($conn, $sqlDetails, array($user->ccod_empresa, $header['id_cbfact']));
$details = array();
if ($stmtDetails) {
    while ($row = sqlsrv_fetch_array($stmtDetails, SQLSRV_FETCH_ASSOC)) {
        $details[] = $row;
    }
    sqlsrv_free_stmt($stmtDetails);
}

echo "DETALLES:\n";
print_r($details);

sqlsrv_close($conn);
?>
