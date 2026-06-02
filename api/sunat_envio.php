<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DA/DAMovimientoCabecera.php';
require_once __DIR__ . '/../BL/BLMovimientoCabecera.php';

if (!isset($_SESSION['objBEUsuario'])) {
    header('Content-Type: application/json');
    echo json_encode(array('d' => '-1', 'error' => 'No hay sesión'));
    exit;
}

$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

function sunatS($v) { return $v !== null ? strval($v) : null; }

function sunatInput()
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

function sunatJsonResponse($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function sunatBuildItem($f)
{
    return array(
        'unidad_de_medida' => sunatS($f[0] ?? null),
        'codigo' => sunatS($f[1] ?? null),
        'descripcion' => sunatS($f[2] ?? null),
        'cantidad' => $f[3] ?? null,
        'valor_unitario' => $f[4] ?? null,
        'precio_unitario' => $f[5] ?? null,
        'descuento' => null,
        'subtotal' => $f[6] ?? null,
        'tipo_de_igv' => null,
        'igv' => $f[7] ?? null,
        'total' => $f[8] ?? null,
        'anticipo_regularizacion' => false,
        'anticipo_documento_serie' => null,
        'anticipo_documento_numero' => null,
        'codigo_producto_sunat' => null
    );
}

function sunatBuildInvoice($idFact, $objUsuario)
{
    $objBL = new BLMovimientoCabecera();
    $cab = $objBL->ConsultarSunatFactura($idFact, $objUsuario);
    $det = $objBL->ConsultarSunatFacturaDetalle($idFact, $objUsuario);

    $invoice = array(
        'operacion' => null,
        'tipo_de_comprobante' => null,
        'serie' => sunatS($cab['serie'] ?? null),
        'numero' => $cab['numero'] ?? null,
        'sunat_transaction' => null,
        'cliente_tipo_de_documento' => $cab['cliente_tipo_de_documento'] ?? null,
        'cliente_numero_de_documento' => sunatS($cab['cliente_numero_de_documento'] ?? null),
        'cliente_denominacion' => sunatS($cab['cliente_denominacion'] ?? null),
        'cliente_direccion' => sunatS($cab['cliente_direccion'] ?? null),
        'cliente_email' => null,
        'cliente_email_1' => null,
        'cliente_email_2' => null,
        'fecha_de_emision' => $cab['fecha_de_emision'] ?? null,
        'fecha_de_vencimiento' => $cab['fecha_de_vencimiento'] ?? null,
        'moneda' => null,
        'tipo_de_cambio' => null,
        'porcentaje_de_igv' => $cab['porcentaje_de_igv'] ?? null,
        'descuento_global' => null,
        'total_descuento' => null,
        'total_anticipo' => null,
        'total_gravada' => $cab['total_gravada'] ?? null,
        'total_inafecta' => null,
        'total_exonerada' => null,
        'total_igv' => $cab['total_igv'] ?? null,
        'total_gratuita' => null,
        'total_otros_cargos' => null,
        'total' => $cab['total'] ?? null,
        'percepcion_tipo' => null,
        'percepcion_base_imponible' => null,
        'total_percepcion' => null,
        'total_incluido_percepcion' => null,
        'detraccion' => false,
        'observaciones' => null,
        'documento_que_se_modifica_tipo' => null,
        'documento_que_se_modifica_serie' => null,
        'documento_que_se_modifica_numero' => null,
        'tipo_de_nota_de_credito' => null,
        'tipo_de_nota_de_debito' => null,
        'enviar_automaticamente_a_la_sunat' => false,
        'enviar_automaticamente_al_cliente' => false,
        'codigo_unico' => null,
        'condiciones_de_pago' => null,
        'medio_de_pago' => null,
        'placa_vehiculo' => null,
        'orden_compra_servicio' => null,
        'tabla_personalizada_codigo' => null,
        'formato_de_pdf' => null,
        'items' => array(),
        'guias' => null
    );

    foreach ($det as $fila) {
        $invoice['items'][] = sunatBuildItem($fila);
    }

    return $invoice;
}

function sunatSendJson($ruta, $token, $json)
{
    $headers = array('Content-Type: application/json; charset=utf-8');
    if ($token !== '') {
        $headers[] = 'Authorization: Token token=' . $token;
    }

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $json,
            'ignore_errors' => true,
            'timeout' => 60
        )
    ));

    $respuesta = file_get_contents($ruta, false, $context);
    return $respuesta === false ? '' : $respuesta;
}

$input = sunatInput();
$idFact = intval($input['id_fact'] ?? $_GET['id_fact'] ?? 0);

switch ($method) {
    case 'PrepararFacturaSunat':
        if ($idFact <= 0) {
            sunatJsonResponse(array('d' => array(false, 'id_fact requerido')));
        }
        $payload = sunatBuildInvoice($idFact, $objUsuario);
        sunatJsonResponse(array('d' => array(true, $payload, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))));
        break;

    case 'EnviarFacturaSunat':
        if (getenv('DATPOS_SUNAT_ENVIO_ENABLED') !== '1') {
            sunatJsonResponse(array('d' => array(false, 'Envio SUNAT deshabilitado por defecto')));
        }
        if ($idFact <= 0) {
            sunatJsonResponse(array('d' => array(false, 'id_fact requerido')));
        }
        $ruta = $input['ruta_uri'] ?? (getenv('DATPOS_SUNAT_URL') ?: 'http://34.213.72.183:442/api/ccq_envio_doc');
        $token = $input['token'] ?? (getenv('DATPOS_SUNAT_TOKEN') ?: '');
        $payload = sunatBuildInvoice($idFact, $objUsuario);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        sunatJsonResponse(array('d' => array(true, sunatSendJson($ruta, $token, $json))));
        break;

    default:
        sunatJsonResponse(array('d' => array(false, 'Metodo no soportado')));
}
?>
