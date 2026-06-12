<?php
require_once __DIR__ . '/../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

// Simular el usuario de la sesion
$user = new BEUsuario();
$user->ccod_empresa = 'EMP01';
$user->cnombre_bd = 'DatPos_EMP01';
$user->cnomser = 'localhost\\SQLEXPRESS';

$conn = Database::getTenantConnection($user);
if (!$conn) {
    echo "No se pudo conectar a la base de datos.\n";
    exit(1);
}

$serie = 'B001';
$correlativo = 69;

// 1. Obtener cabecera de la factura
$sqlHeader = "SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.fecha_emision,
                     F.nsubtotal, F.nimpuesto, F.ntotal, F.nvuelto, F.ntot_entreg, F.cobs,
                     C.cdsc_coa, C.cruc_coa, C.cdirc_coa, F.ccod_tiend, F.ccod_caja
              FROM CbFactura F
              LEFT JOIN Coa C ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
              WHERE F.ccod_cia = ? AND F.cserie = ? AND F.nnumero = ?";

$stmtHeader = sqlsrv_query($conn, $sqlHeader, array($user->ccod_empresa, $serie, $correlativo));
$header = null;
if ($stmtHeader) {
    $header = sqlsrv_fetch_array($stmtHeader, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmtHeader);
}

if (!$header) {
    echo "Factura no encontrada.\n";
    sqlsrv_close($conn);
    exit(1);
}

// 2. Obtener detalles
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

// 3. Obtener datos de la Tienda
$sqlTienda = "SELECT cnombr, cdirec, ctelef, cmail FROM Tiendas WHERE ccod_cia = ? AND ccod_tiend = ?";
$stmtTienda = sqlsrv_query($conn, $sqlTienda, array($user->ccod_empresa, $header['ccod_tiend']));
$tienda = null;
if ($stmtTienda) {
    $tienda = sqlsrv_fetch_array($stmtTienda, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmtTienda);
}

sqlsrv_close($conn);

// 4. Generar el PDF usando FPDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

// Helper para codificación
function txt($str) {
    return iconv('UTF-8', 'windows-1252//TRANSLIT', $str);
}

// HEADER - INFORMACIÓN DE LA EMPRESA
$pdf->SetFont('Arial', 'B', 14);
$storeName = $tienda['cnombr'] ?? 'DATPOS S.A.C.';
$pdf->Cell(110, 8, txt($storeName), 0, 0, 'L');

// CUADRO DE COMPROBANTE (RUC, TIPO, NRO)
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, txt('R.U.C. 20609876543'), 'LTR', 1, 'C'); // RUC de ejemplo

$pdf->SetFont('Arial', '', 9);
$storeDir = $tienda['cdirec'] ?? 'Av. Principal 123 - Lima';
$pdf->Cell(110, 5, txt($storeDir), 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 10);
$docName = ($header['cdoc'] === 'FT') ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA';
$pdf->Cell(70, 6, txt($docName), 'LR', 1, 'C');

$pdf->SetFont('Arial', '', 9);
$storeTelMail = 'Telf: ' . ($tienda['ctelef'] ?? '987654321') . ' | Email: ' . ($tienda['cmail'] ?? 'contacto@datpos.com');
$pdf->Cell(110, 5, txt($storeTelMail), 0, 0, 'L');

$pdf->SetFont('Arial', 'B', 12);
$docNumber = $header['cserie'] . '-' . sprintf('%08d', $header['nnumero']);
$pdf->Cell(70, 8, txt($docNumber), 'LBR', 1, 'C');

$pdf->Ln(10);

// DETALLES DEL CLIENTE
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 6, txt('Adquirente:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(100, 6, txt($header['cdsc_coa'] ?? 'CONSUMIDOR FINAL'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(25, 6, txt('F. Emisión:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(35, 6, txt(date('d/m/Y H:i', strtotime($header['fecha_emision']))), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 6, txt('RUC/NIF/DNI:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(100, 6, txt($header['cruc_coa'] ?? '00000000'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(25, 6, txt('Moneda:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(35, 6, txt('Soles (S/)'), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 6, txt('Dirección:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(150, 6, txt($header['cdirc_coa'] ?? 'SIN DIRECCIÓN'), 0, 1, 'L');

$pdf->Ln(6);

// TABLA DE ITEMS
$pdf->SetFont('Arial', 'B', 9);
// Cabeceras de tabla
$pdf->Cell(15, 7, txt('CANT.'), 1, 0, 'C');
$pdf->Cell(20, 7, txt('CÓDIGO'), 1, 0, 'C');
$pdf->Cell(105, 7, txt('DESCRIPCIÓN'), 1, 0, 'L');
$pdf->Cell(20, 7, txt('P. UNIT'), 1, 0, 'R');
$pdf->Cell(20, 7, txt('TOTAL'), 1, 1, 'R');

$pdf->SetFont('Arial', '', 9);
foreach ($details as $d) {
    $pdf->Cell(15, 7, txt(number_format($d['ncantidad'], 2)), 1, 0, 'C');
    $pdf->Cell(20, 7, txt($d['id_articulo']), 1, 0, 'C');
    $pdf->Cell(105, 7, txt($d['cdsc_articulo']), 1, 0, 'L');
    $pdf->Cell(20, 7, txt(number_format($d['nprecio'], 2)), 1, 0, 'R');
    $pdf->Cell(20, 7, txt(number_format($d['nimporte_neto'], 2)), 1, 1, 'R');
}

$pdf->Ln(4);

// RESUMEN DE TOTALES
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(140, 6, '', 0, 0);
$pdf->Cell(20, 6, txt('Subtotal:'), 0, 0, 'R');
$pdf->Cell(20, 6, txt(number_format($header['nsubtotal'], 2)), 0, 1, 'R');

$pdf->Cell(140, 6, '', 0, 0);
$pdf->Cell(20, 6, txt('IGV (18%):'), 0, 0, 'R');
$pdf->Cell(20, 6, txt(number_format($header['nimpuesto'], 2)), 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(140, 6, '', 0, 0);
$pdf->Cell(20, 6, txt('Total:'), 0, 0, 'R');
$pdf->Cell(20, 6, txt(number_format($header['ntotal'], 2)), 0, 1, 'R');

$pdf->Ln(10);

// OBSERVACIONES / CONDICIONES
if (!empty($header['cobs'])) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(180, 5, txt('Observaciones:'), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(180, 4, txt($header['cobs']), 0, 'L');
    $pdf->Ln(5);
}

// PIE DE PÁGINA
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(180, 5, txt('Representación impresa de la Boleta de Venta / Factura Electrónica.'), 0, 1, 'C');
$pdf->Cell(180, 5, txt('Este documento puede ser consultado en el portal de SUNAT.'), 0, 1, 'C');

$pdfData = $pdf->Output('S');
file_put_contents('scratch/test_invoice.pdf', $pdfData);
echo "PDF Invoice generated successfully! Size: " . strlen($pdfData) . " bytes\n";
?>
