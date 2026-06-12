<?php
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, '¡Hola Mundo FPDF!');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'Probando la generacion de PDF en DatPOS.');

$data = $pdf->Output('S');
file_put_contents('scratch/test_output.pdf', $data);
echo "PDF generated. Size: " . strlen($data) . " bytes\n";
?>
