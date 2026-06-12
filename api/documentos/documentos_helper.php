<?php
/**
 * DatPOS - Documentos Helper
 * Generador de PDF (FPDF), XML (UBL 2.1) y CDR (ZIP) en caliente
 */

require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

class SimpleZip {
    private $files = array();

    public function addFile($filename, $data) {
        $this->files[] = array(
            'name' => $filename,
            'data' => $data
        );
    }

    public function getZipData() {
        $zipData = '';
        $cdData = '';
        $offset = 0;

        foreach ($this->files as $file) {
            $name = $file['name'];
            $data = $file['data'];
            $len = strlen($data);
            $crc = crc32($data);
            $nameLen = strlen($name);

            // Local File Header
            $lfh = pack('VvvvvvVVVvv', 
                0x04034b50, // signature
                10,         // version needed
                0,          // flags
                0,          // compression method (Store)
                0,          // last mod time
                0,          // last mod date
                $crc,       // crc32
                $len,       // compressed size
                $len,       // uncompressed size
                $nameLen,   // filename length
                0           // extra field length
            ) . $name;
            
            $zipData .= $lfh . $data;

            // Central Directory File Header
            $cdfh = pack('VvvvvvvVVVvvvvvVV', 
                0x02014b50, // signature
                20,         // version made by
                10,         // version needed
                0,          // flags
                0,          // compression method
                0,          // last mod time
                0,          // last mod date
                $crc,       // crc32
                $len,       // compressed size
                $len,       // uncompressed size
                $nameLen,   // filename length
                0,          // extra field length
                0,          // comment length
                0,          // disk number start
                0,          // internal file attrs
                0,          // external file attrs
                $offset     // local header offset
            ) . $name;

            $cdData .= $cdfh;
            $offset += strlen($lfh) + $len;
        }

        $cdLen = strlen($cdData);
        
        // End of Central Directory
        $eocd = pack('VvvvvVVv', 
            0x06054b50,         // signature
            0,                  // disk number
            0,                  // disk with CD start
            count($this->files),// num CD records on disk
            count($this->files),// total num CD records
            $cdLen,             // size of CD
            $offset,            // offset of CD
            0                   // comment length
        );

        return $zipData . $cdData . $eocd;
    }
}

class DocumentosHelper {
    
    private static function txt($str) {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $str);
    }

    /**
     * Generar PDF con formato de comprobante A4 usando FPDF
     */
    public static function generarPdf($header, $details, $tienda) {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 15);

        // HEADER - INFORMACIÓN DE LA EMPRESA
        $pdf->SetFont('Arial', 'B', 14);
        $storeName = $tienda['cnombr'] ?? 'DATPOS S.A.C.';
        $pdf->Cell(110, 8, self::txt($storeName), 0, 0, 'L');

        // CUADRO DE COMPROBANTE (RUC, TIPO, NRO)
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(70, 8, self::txt('R.U.C. 20609876543'), 'LTR', 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $storeDir = $tienda['cdirec'] ?? 'Av. Principal 123 - Lima';
        $pdf->Cell(110, 5, self::txt($storeDir), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 10);
        $docName = ($header['cdoc'] === 'FT') ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA';
        $pdf->Cell(70, 6, self::txt($docName), 'LR', 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $storeTelMail = 'Telf: ' . ($tienda['ctelef'] ?? '987654321') . ' | Email: ' . ($tienda['cmail'] ?? 'contacto@datpos.com');
        $pdf->Cell(110, 5, self::txt($storeTelMail), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 12);
        $docNumber = $header['cserie'] . '-' . sprintf('%08d', $header['nnumero']);
        $pdf->Cell(70, 8, self::txt($docNumber), 'LBR', 1, 'C');

        $pdf->Ln(10);

        // DETALLES DEL CLIENTE
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, self::txt('Adquirente:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(100, 6, self::txt($header['cdsc_coa'] ?? 'CONSUMIDOR FINAL'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(25, 6, self::txt('F. Emisión:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(35, 6, self::txt(date('d/m/Y H:i', strtotime($header['fecha_emision']))), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, self::txt('RUC/NIF/DNI:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(100, 6, self::txt($header['cruc_coa'] ?? '00000000'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(25, 6, self::txt('Moneda:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(35, 6, self::txt('Soles (S/)'), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, self::txt('Dirección:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(150, 6, self::txt($header['cdirc_coa'] ?? 'SIN DIRECCIÓN'), 0, 1, 'L');

        $pdf->Ln(6);

        // TABLA DE ITEMS
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(15, 7, self::txt('CANT.'), 1, 0, 'C');
        $pdf->Cell(20, 7, self::txt('CÓDIGO'), 1, 0, 'C');
        $pdf->Cell(105, 7, self::txt('DESCRIPCIÓN'), 1, 0, 'L');
        $pdf->Cell(20, 7, self::txt('P. UNIT'), 1, 0, 'R');
        $pdf->Cell(20, 7, self::txt('TOTAL'), 1, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        foreach ($details as $d) {
            $pdf->Cell(15, 7, self::txt(number_format($d['ncantidad'], 2)), 1, 0, 'C');
            $pdf->Cell(20, 7, self::txt($d['id_articulo']), 1, 0, 'C');
            $pdf->Cell(105, 7, self::txt($d['cdsc_articulo']), 1, 0, 'L');
            $pdf->Cell(20, 7, self::txt(number_format($d['nprecio'], 2)), 1, 0, 'R');
            $pdf->Cell(20, 7, self::txt(number_format($d['nimporte_neto'], 2)), 1, 1, 'R');
        }

        $pdf->Ln(4);

        // RESUMEN DE TOTALES
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(140, 6, '', 0, 0);
        $pdf->Cell(20, 6, self::txt('Subtotal:'), 0, 0, 'R');
        $pdf->Cell(20, 6, self::txt(number_format($header['nsubtotal'], 2)), 0, 1, 'R');

        $pdf->Cell(140, 6, '', 0, 0);
        $pdf->Cell(20, 6, self::txt('IGV (18%):'), 0, 0, 'R');
        $pdf->Cell(20, 6, self::txt(number_format($header['nimpuesto'], 2)), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(140, 6, '', 0, 0);
        $pdf->Cell(20, 6, self::txt('Total:'), 0, 0, 'R');
        $pdf->Cell(20, 6, self::txt(number_format($header['ntotal'], 2)), 0, 1, 'R');

        $pdf->Ln(10);

        // OBSERVACIONES
        if (!empty($header['cobs'])) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(180, 5, self::txt('Observaciones:'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->MultiCell(180, 4, self::txt($header['cobs']), 0, 'L');
            $pdf->Ln(5);
        }

        // PIE DE PÁGINA
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(180, 5, self::txt('Representación impresa de la Boleta de Venta / Factura Electrónica.'), 0, 1, 'C');
        $pdf->Cell(180, 5, self::txt('Este documento puede ser consultado en el portal de SUNAT.'), 0, 1, 'C');

        return $pdf->Output('S');
    }

    /**
     * Generar XML en formato UBL 2.1
     */
    public static function generarXml($header, $details, $tienda) {
        $serie = $header['cserie'];
        $correlativo = sprintf('%08d', $header['nnumero']);
        $fechaEmision = date('Y-m-d', strtotime($header['fecha_emision']));
        $horaEmision = date('H:i:s', strtotime($header['fecha_emision']));
        
        $tipoDoc = ($header['cdoc'] === 'FT') ? '01' : '03'; // 01=Factura, 03=Boleta
        
        $rucEmisor = '20609876543'; // RUC de la empresa
        $nombreEmisor = htmlspecialchars($tienda['cnombr'] ?? 'DATPOS S.A.C.', ENT_QUOTES, 'UTF-8');
        $direccionEmisor = htmlspecialchars($tienda['cdirec'] ?? 'Av. Principal 123 - Lima', ENT_QUOTES, 'UTF-8');
        
        $nombreCliente = htmlspecialchars($header['cdsc_coa'] ?? 'CONSUMIDOR FINAL', ENT_QUOTES, 'UTF-8');
        $docCliente = $header['cruc_coa'] ?? '00000000';
        $tipoDocCliente = (strlen($docCliente) === 11) ? '6' : ((strlen($docCliente) === 8) ? '1' : '0');
        
        $subtotal = number_format($header['nsubtotal'], 2, '.', '');
        $igv = number_format($header['nimpuesto'], 2, '.', '');
        $total = number_format($header['ntotal'], 2, '.', '');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>' . $serie . '-' . $correlativo . '</cbc:ID>
    <cbc:IssueDate>' . $fechaEmision . '</cbc:IssueDate>
    <cbc:IssueTime>' . $horaEmision . '</cbc:IssueTime>
    <cbc:InvoiceTypeCode listID="0101">' . $tipoDoc . '</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">' . $rucEmisor . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>' . $nombreEmisor . '</cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>' . $nombreEmisor . '</cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:AddressLine>' . $direccionEmisor . '</cbc:AddressLine>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="' . $tipoDocCliente . '">' . $docCliente . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>' . $nombreCliente . '</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="PEN">' . $igv . '</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="PEN">' . $subtotal . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $igv . '</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="PEN">' . $subtotal . '</cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="PEN">' . $total . '</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="PEN">' . $total . '</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>';

        foreach ($details as $idx => $d) {
            $lineNum = $idx + 1;
            $qty = number_format($d['ncantidad'], 2, '.', '');
            $price = number_format($d['nprecio'], 2, '.', '');
            $lineTotal = number_format($d['nimporte_neto'], 2, '.', '');
            $itemDesc = htmlspecialchars($d['cdsc_articulo'], ENT_QUOTES, 'UTF-8');
            $itemId = htmlspecialchars($d['id_articulo'], ENT_QUOTES, 'UTF-8');

            $xml .= '
    <cac:InvoiceLine>
        <cbc:ID>' . $lineNum . '</cbc:ID>
        <cbc:InvoicedQuantity unitCode="NIU">' . $qty . '</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="PEN">' . $lineTotal . '</cbc:LineExtensionAmount>
        <cac:PricingReference>
            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">' . $price . '</cbc:PriceAmount>
                <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
        </cac:PricingReference>
        <cac:Item>
            <cbc:Description>' . $itemDesc . '</cbc:Description>
            <cac:SellersItemIdentification>
                <cbc:ID>' . $itemId . '</cbc:ID>
            </cac:SellersItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="PEN">' . $price . '</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>';
        }

        $xml .= '
</Invoice>';

        return $xml;
    }

    /**
     * Generar Constancia de Recepción (CDR) en XML firmada ficticiamente dentro de un ZIP
     */
    public static function generarCdr($header) {
        $serie = $header['cserie'];
        $correlativo = sprintf('%08d', $header['nnumero']);
        $fechaEmision = date('Y-m-d', strtotime($header['fecha_emision']));
        $horaEmision = date('H:i:s', strtotime($header['fecha_emision']));

        $xmlCdr = '<?xml version="1.0" encoding="UTF-8"?>
<ar:ApplicationResponse xmlns:ar="urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2"
                        xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                        xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:ID>R-' . $serie . '-' . $correlativo . '</cbc:ID>
    <cbc:IssueDate>' . $fechaEmision . '</cbc:IssueDate>
    <cbc:IssueTime>' . $horaEmision . '</cbc:IssueTime>
    <cbc:ResponseDate>' . $fechaEmision . '</cbc:ResponseDate>
    <cbc:ResponseTime>' . $horaEmision . '</cbc:ResponseTime>
    <cac:SenderParty>
        <cac:PartyIdentification>
            <cbc:ID schemeID="6">20609876543</cbc:ID>
        </cac:PartyIdentification>
    </cac:SenderParty>
    <cac:ReceiverParty>
        <cac:PartyIdentification>
            <cbc:ID schemeID="6">20609876543</cbc:ID>
        </cac:PartyIdentification>
    </cac:ReceiverParty>
    <cac:DocumentResponse>
        <cac:Response>
            <cbc:ReferenceID>' . $serie . '-' . $correlativo . '</cbc:ReferenceID>
            <cbc:ResponseCode>0</cbc:ResponseCode>
            <cbc:Description>El comprobante numero ' . $serie . '-' . $correlativo . ' ha sido aceptado</cbc:Description>
        </cac:Response>
    </cac:DocumentResponse>
</ar:ApplicationResponse>';

        $zip = new SimpleZip();
        $zip->addFile('R-' . $serie . '-' . $correlativo . '.xml', $xmlCdr);
        return $zip->getZipData();
    }
}
