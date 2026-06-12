<?php
$connectionInfo = array(
    "Database" => "DatPos_EMP01",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true
);
$conn = sqlsrv_connect('localhost\\SQLEXPRESS', $connectionInfo);
if ($conn === false) {
    echo "Connection failed:\n";
    print_r(sqlsrv_errors());
    exit;
}

$sql = "SELECT id_turno, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus FROM Turno ORDER BY id_turno DESC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt !== false) {
    echo "Turno records:\n";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        print_r($row);
    }
} else {
    print_r(sqlsrv_errors());
}
sqlsrv_close($conn);
