<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

// Prepare a mock user session
$user = new BEUsuario();
$user->ccod_empresa = 'EMP01';
$user->cnombre_bd = 'DatPos_EMP01';
$user->cnomser = 'localhost\\SQLEXPRESS';

// Connect to tenant DB to get the test invoice information
$conn = Database::getTenantConnection($user);
if (!$conn) {
    echo "Could not connect to tenant DB.\n";
    exit(1);
}

// Find invoice B001-69 (we know it exists and contains data)
$serie = 'B001';
$correlativo = 69;

$q = sqlsrv_query($conn, "SELECT id_cbfact FROM CbFactura WHERE ccod_cia = 'EMP01' AND cserie = ? AND nnumero = ?", array($serie, $correlativo));
if (!$q || !($row = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC))) {
    echo "Invoice B001-69 not found in DB.\n";
    sqlsrv_close($conn);
    exit(1);
}
sqlsrv_close($conn);

// Helper function to run the endpoint in a separate PHP process and save to a file
function runEndpointInProcess($endpoint, $getParams, $outputFile) {
    global $user;
    
    // Create a temporary script to run the test
    $tempScript = tempnam(sys_get_temp_dir(), 'test_endpoint_') . '.php';
    
    $getParamsExport = var_export($getParams, true);
    $serializedUser = base64_encode(serialize($user));
    
    $sessionCode = "
require_once '" . addslashes(__DIR__ . '/../BE/BEUsuario.php') . "';
\$_SESSION['objBEUsuario'] = unserialize(base64_decode('{$serializedUser}'));";
    
    $code = "<?php
session_start();
{$sessionCode}
\$_GET = {$getParamsExport};
ob_start();
register_shutdown_function(function() {
    \$content = ob_get_clean();
    file_put_contents('" . addslashes($outputFile) . "', \$content);
});
include '" . addslashes(__DIR__ . '/../api/documentos/' . $endpoint) . "';
";
    
    file_put_contents($tempScript, $code);
    
    // Execute child process
    shell_exec("php " . escapeshellarg($tempScript) . " 2>&1");
    
    // Delete temporary script
    @unlink($tempScript);
    
    if (file_exists($outputFile)) {
        return file_get_contents($outputFile);
    }
    return '';
}

echo "Running Verification on Invoice: {$serie}-{$correlativo}\n\n";

$pdfFile = 'scratch/verify_pdf.pdf';
$xmlFile = 'scratch/verify_xml.xml';
$cdrFile = 'scratch/verify_cdr.zip';

// Cleanup existing
@unlink($pdfFile);
@unlink($xmlFile);
@unlink($cdrFile);

// --- VERIFY PDF ---
echo "1. Testing descargar_pdf.php...\n";
$pdfData = runEndpointInProcess('descargar_pdf.php', array('serie' => $serie, 'correlativo' => $correlativo), $pdfFile);
$pdfSig = substr($pdfData, 0, 4);
echo "PDF Signature: " . bin2hex($pdfSig) . " ('" . $pdfSig . "')\n";
echo "PDF Length: " . filesize($pdfFile) . " bytes\n";
if ($pdfSig === '%PDF') {
    echo "Result: VALID PDF signature detected.\n\n";
} else {
    echo "Result: INVALID PDF signature.\n\n";
}

// --- VERIFY XML ---
echo "2. Testing descargar_xml.php...\n";
$xmlData = runEndpointInProcess('descargar_xml.php', array('serie' => $serie, 'correlativo' => $correlativo), $xmlFile);
$xmlTrimmed = trim($xmlData);
$xmlSig = substr($xmlTrimmed, 0, 5);
echo "XML Start: '" . htmlspecialchars($xmlSig) . "'\n";
echo "XML Length: " . filesize($xmlFile) . " bytes\n";
if ($xmlSig === '<?xml') {
    echo "Result: VALID XML declaration detected.\n\n";
} else {
    echo "Result: INVALID XML start.\n\n";
}

// --- VERIFY ZIP (CDR) ---
echo "3. Testing descargar_cdr.php...\n";
$cdrData = runEndpointInProcess('descargar_cdr.php', array('serie' => $serie, 'correlativo' => $correlativo), $cdrFile);
$cdrSig = substr($cdrData, 0, 2);
echo "ZIP Signature: " . bin2hex($cdrSig) . " ('" . $cdrSig . "')\n";
echo "ZIP Length: " . filesize($cdrFile) . " bytes\n";
if ($cdrSig === 'PK') {
    echo "Result: VALID ZIP signature detected.\n";
    
    // Extract using powershell
    $extractDir = 'scratch/verify_cdr_extracted';
    shell_exec("powershell -Command \"Expand-Archive -Path '{$cdrFile}' -DestinationPath '{$extractDir}' -Force\"");
    if (file_exists($extractDir)) {
        $files = scandir($extractDir);
        echo "Extracted files in ZIP: " . implode(', ', array_diff($files, array('.', '..'))) . "\n";
        // Cleanup extraction dir
        shell_exec("powershell -Command \"Remove-Item -Path '{$extractDir}' -Recurse -Force\"");
        echo "Result: ZIP successfully extracted and checked.\n\n";
    } else {
        echo "Result: Extraction FAILED.\n\n";
    }
} else {
    echo "Result: INVALID ZIP signature.\n\n";
}

// Cleanup temp files
@unlink($pdfFile);
@unlink($xmlFile);
@unlink($cdrFile);

echo "Verification complete.\n";
?>
