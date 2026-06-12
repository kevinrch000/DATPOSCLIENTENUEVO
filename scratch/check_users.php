<?php
// Check users in DatPos_EMP01
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
echo "Connected successfully to DatPos_EMP01.\n";

$sql = "SELECT ccod_usuario, cdsc_usuario, ccod_tiend, ccod_caja, id_estado FROM Usuarios";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt !== false) {
    echo "Users:\n";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        print_r($row);
    }
} else {
    print_r(sqlsrv_errors());
}

sqlsrv_close($conn);
