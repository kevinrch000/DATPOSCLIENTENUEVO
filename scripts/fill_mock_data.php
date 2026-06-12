<?php
/**
 * Script para rellenar comprobantes locales con datos de prueba (PDF, XML, CDR)
 * para validar la descarga en ambiente de desarrollo.
 */

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

// Convertimos strings a sus equivalentes hexadecimales de SQL Server
$pdfHex = "0x" . bin2hex("%PDF-1.4 Mock PDF Content");
$xmlHex = "0x" . bin2hex("<xml><comprobante>Mock XML Content</comprobante></xml>");
$cdrHex = "0x" . bin2hex("Mock ZIP/CDR Content (Constancia de Recepcion)");

$sql = "UPDATE CbFactura SET 
    pdf = {$pdfHex}, 
    xml = {$xmlHex}, 
    xml_cdr = {$cdrHex} 
    WHERE ccod_cia = 'EMP01'";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo "Error al actualizar la base de datos:\n";
    print_r(sqlsrv_errors());
} else {
    $rows = sqlsrv_rows_affected($stmt);
    echo "Se han actualizado {$rows} comprobantes con archivos de prueba exitosamente!\n";
}
sqlsrv_close($conn);
?>
