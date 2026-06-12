<?php
/**
 * DatPOS - API: Facturación (Ventas)
 * Reemplaza los WebMethods de Facturacion.aspx.vb.
 * El JS llama 'Facturacion.aspx/Method' y el adapter lo redirige a este archivo.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DA/DAMovimientoCabecera.php';
require_once __DIR__ . '/../DA/DAArticulo.php';
require_once __DIR__ . '/../DA/DAPrecio.php';
require_once __DIR__ . '/../DA/DACliente.php';
require_once __DIR__ . '/../DA/DATienda.php';
require_once __DIR__ . '/../DA/DAEmpresa.php';
require_once __DIR__ . '/../DA/DAVariante.php';
require_once __DIR__ . '/../DA/DAFamilia.php';
require_once __DIR__ . '/../DA/DAUsuario.php';
require_once __DIR__ . '/../DA/DACuenta.php';
require_once __DIR__ . '/../DA/DAConsultaDocumento.php';
require_once __DIR__ . '/../BL/BLMovimientoCabecera.php';

if (!isset($_SESSION['objBEUsuario'])) {
    header('Content-Type: application/json');
    echo json_encode(array('d' => '-1', 'error' => 'No hay sesión'));
    exit;
}
$o = $_SESSION['objBEUsuario'];

// FIX: si faltan cnomser/cnombre_bd en sesión, intentar con variables de entorno
// antes de abortar con '-1' (que el JS interpreta como "sesión expirada").
if (empty($o->cnomser) || empty($o->cnombre_bd)) {
    $envServer = getenv('DATPOS_TENANT_SERVER');
    $envDb     = getenv('DATPOS_TENANT_DATABASE');
    if (!empty($envServer) && !empty($envDb)) {
        // Completar el objeto desde las variables de entorno
        $o->cnomser    = $envServer;
        $o->cnombre_bd = $envDb;
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('d' => '-1', 'error' => 'Sesion incompleta: faltan cnomser/cnombre_bd. Vuelva a iniciar sesion.', 'debug' => array(
            'ccod_empresa' => $o->ccod_empresa ?? '',
            'cnomser'      => $o->cnomser    ?? 'VACIO',
            'cnombre_bd'   => $o->cnombre_bd ?? 'VACIO',
        )));
        exit;
    }
}

$m = $_GET['method'] ?? '';
$objBL = new BLMovimientoCabecera();

function S($v) { return ($v !== null) ? strval($v) : ''; }

function getFacturacionInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (is_array($data)) return $data;

    $data = array();
    if (preg_match_all('/([A-Za-z_][A-Za-z0-9_]*)\s*:\s*(?:"([^"]*)"|\'([^\']*)\'|([^,}\s]+))/', $raw, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $data[$m[1]] = $m[2] !== '' ? $m[2] : ($m[3] !== '' ? $m[3] : ($m[4] ?? ''));
        }
    }
    return $data;
}

function mapArticuloFacturacion($f) {
    return array(
        'cdsc_articulo' => S($f[0]),
        'iimage' => (!empty($f[1])) ? base64_encode($f[1]) : '',
        'id_articulo' => S($f[2]),
        'precio' => S($f[3]),
        'ctip_articulo' => S($f[4] ?? 'S'),
        'bprefer' => intval($f[5] ?? 0) * -1
    );
}

function mapPrecioFacturacion($f, $incluyeLista = false) {
    if (array_key_exists('npre_uni', $f) || array_key_exists('cdsc_articulo', $f)) {
        $row = array(
            'npre_uni' => S($f['npre_uni'] ?? 0),
            'cdsc_articulo' => S($f['cdsc_articulo'] ?? ''),
            'igv' => S($f['igv'] ?? $f['cigv'] ?? 0),
            'isc' => S($f['isc'] ?? $f['cisc'] ?? 0),
            'ctip_articulo' => S($f['ctip_articulo'] ?? ''),
            'state' => S($f['state'] ?? $f['ncantidad'] ?? ''),
            'npre_costo' => S($f['npre_costo'] ?? $f['ncosto'] ?? ''),
            'ndes_max' => S($f['ndes_max'] ?? '')
        );
        if ($incluyeLista) {
            $row['id_cblistpre'] = S($f['id_cblistpre'] ?? $f['ccod_cblistpre'] ?? 1);
        }
        return $row;
    }

    // Algunas migraciones devuelven: ccod_articulo, cdsc, igv, isc, tipo, precio, desc, stock, costo.
    if (isset($f[9]) && isset($f[5]) && is_numeric($f[5])) {
        $row = array(
            'npre_uni' => S($f[5]),
            'cdsc_articulo' => S($f[1]),
            'igv' => S($f[2]),
            'isc' => S($f[3]),
            'ctip_articulo' => S($f[4]),
            'state' => S($f[7] ?? ''),
            'npre_costo' => S($f[8] ?? ''),
            'ndes_max' => S($f[6] ?? '')
        );
        if ($incluyeLista) {
            $row['id_cblistpre'] = S($f[9] ?? 1);
        }
        return $row;
    }

    $row = array(
        'npre_uni' => S($f[0]),
        'cdsc_articulo' => S($f[1]),
        'igv' => S($f[2]),
        'isc' => S($f[3]),
        'ctip_articulo' => S($f[4]),
        'state' => S($f[5] ?? ''),
        'npre_costo' => S($f[6] ?? ''),
        'ndes_max' => S($f[7] ?? '')
    );
    if ($incluyeLista) {
        $row['id_cblistpre'] = S($f[8] ?? '');
    }
    return $row;
}

function mapPreciosResponse($rows, $incluyeLista = false, $ccod_articulo = '') {
    // FIX BUG Facturación: devolver [] en lugar de false cuando no hay resultados.
    // false hacía que el JS llamara MensajeFinSession() (confundía "no encontrado" con "sesión expirada").
    // El JS distingue la sesión expirada por response.d == "-1", no por response.d == false.
    if (count($rows) === 0) return array();
    $lst = array();
    foreach ($rows as $f) {
        $row = mapPrecioFacturacion($f, $incluyeLista);
        if ($ccod_articulo !== '') {
            $row['ccod_articulo'] = $ccod_articulo;
        }
        $lst[] = $row;
    }
    return $lst;
}

function resolverCodigoPrecio($codigo, $objConex) {
    $daPrecio = new DAPrecio();
    $resuelto = $daPrecio->ResolverCodigoArticulo($codigo, $objConex);
    return $resuelto !== '' ? $resuelto : $codigo;
}

function resolveDetalleArticulo($value, $objConex) {
    $codigo = S($value);
    if ($codigo === '') return '';
    $resuelto = resolverCodigoPrecio($codigo, $objConex);
    return $resuelto !== '' ? $resuelto : $codigo;
}

function resolveClienteCuenta($value, $objConex) {
    $cliente = trim(S($value));
    if ($cliente === '') {
        return 'CLI000';
    }

    $conn = Database::getTenantConnection($objConex);
    if (!$conn) {
        return $cliente;
    }

    $sql = "SELECT TOP 1 ccod_coa FROM Coa "
         . "WHERE ccod_cia = ? AND (ccod_coa = ? OR CAST(id_coa AS VARCHAR(20)) = ?)";
    $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $cliente, $cliente));
    $ccod_coa = $cliente;
    if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
        $ccod_coa = $row['ccod_coa'];
    }
    if ($stmt) {
        sqlsrv_free_stmt($stmt);
    }
    sqlsrv_close($conn);
    return $ccod_coa;
}

switch ($m) {

    // ============================================================
    // ObtenerIGV  (Facturacion.aspx.vb líneas 53-71)
    // ============================================================
    case 'ObtenerIGV':
        $rows = $objBL->ObtenerIGV($o);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('Impuesto' => (floatval($f[0]) / 100) + 1); }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ValidarFacturacion  (Page_Load helper)
    // ============================================================
    case 'ValidarFacturacion':
        $result = $objBL->ValidarFacturacion($o);
        jsonResponse(array('d' => $result));
        break;

    case 'ValidarAlFacturar':
        $input = getFacturacionInput();
        $cdoc = $input['cdoc'] ?? '';
        $result = $objBL->ValidarAlFacturar($cdoc, $o);
        jsonResponse(array('d' => $result));
        break;

    // ============================================================
    // ClientePorDefecto  (Facturacion.aspx.vb líneas 103-124)
    // VB DA: sp_clientepordefecto (@ccod_cia)  ← solo 1 parámetro
    // VB lee: 0:ctip_doc, 1:cdoc_coa, 2:cdsc_coa, 3:cdirc_coa
    // ============================================================
    case 'ClientePorDefecto':
        $rows = (new DACliente())->ClientePorDefecto($o);
        $lst = array();
        if (count($rows) > 0) {
            foreach ($rows as $f) {
                $lst[] = array(
                    'ctip_doc' => S($f[0]),
                    'cdoc_coa' => S($f[1]),
                    'cdsc_coa' => S($f[2]),
                    'cdirc_coa' => S($f[3]),
                    'id_coa' => S($f[4] ?? ''),
                    'ccod_coa' => S($f[5] ?? '')
                );
            }
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarTienda  (Facturacion.aspx.vb líneas 502-528)
    // VB DA: sp_consultartienda (@ccod_cia, @ccod_tienda)
    // VB lee 0..10
    // ============================================================
    case 'ConsultarTienda':
        // sp_consultartienda devuelve:
        // [0]id_tienda [1]ccod_cia [2]ccod_tiend [3]cnombr [4]cdirec [5]cmail
        // [6]ctelef [7]cpassw [8]cstatus [9]nlista_pre_normal [10]nlista_pre_preferencial ...
        $rows = (new DATienda())->ConsultarTienda($o->ccod_tiend, $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_tiend'              => S($f[2] ?? ''),
                'cnombr'                  => S($f[3] ?? ''),
                'cdirec'                  => S($f[4] ?? ''),
                'cmail'                   => S($f[5] ?? ''),
                'ctelef'                  => S($f[6] ?? ''),
                'cpassw'                  => S($f[7] ?? ''),
                'cstatus'                 => S($f[8] ?? ''),
                'nlista_pre_normal'       => S($f[9] ?? ''),
                'nlista_pre_preferencial' => S($f[10] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarCategoriasDisponibles  (Facturacion.aspx.vb 287-304)
    // SP: sp_consultafamiliasactivas (@ccod_cia)
    // VB lee 0..3 (ccod_lin, cdsc_lin, id_ctlin, ccolor)
    // ============================================================
    case 'ConsultarCategoriasDisponibles':
        $rows = (new DAFamilia())->consultarFamiliasActivas($o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_lin' => S($f[0]),
                'cdsc_lin' => S($f[1]),
                'id_ctlin' => S($f[2]),
                'ccolor' => S($f[3])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarArticulosTodos  (Facturacion.aspx.vb 127-150)
    // VB DA: sp_consultararticulotodos
    // VB lee 0..3 + ctip_articulo no se asigna; PHP lo añade desde f[4] si SP lo trae.
    // ============================================================
    case 'ConsultarArticulosTodos':
        $input = getFacturacionInput();
        $rows = (new DAArticulo())->consultarArticulosTodos($input['texto'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdsc_articulo' => S($f[0]),
                'iimage' => (!empty($f[1])) ? base64_encode($f[1]) : '',
                'id_articulo' => S($f[2]),
                'precio' => S($f[3]),
                'ctip_articulo' => S($f[4] ?? 'S'),
                'bprefer' => intval($f[5] ?? 0) * -1
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarArticulosCategoria  (Facturacion.aspx.vb 252-285)
    // ============================================================
    case 'ConsultarArticulosCategoria':
        $input = getFacturacionInput();
        $rows = (new DAArticulo())->consultarArticulosCategoria(intval($input['id_familia'] ?? 0), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = mapArticuloFacturacion($f);
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'LSConsultarArticulosCategoria':
        $input = getFacturacionInput();
        $rows = (new DAArticulo())->lsConsultarArticulosCategoria(
            intval($input['id_familia'] ?? 0),
            $input['ccod_cblistpre'] ?? '',
            $o
        );
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = mapArticuloFacturacion($f);
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // CargarFavoritos  (Facturacion.aspx.vb 352-379)
    // ============================================================
    case 'CargarFavoritos':
        $rows = (new DAArticulo())->cargarFavoritos($o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = mapArticuloFacturacion($f);
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'LSCargarFavoritos':
        $input = getFacturacionInput();
        $rows = (new DAArticulo())->lsCargarFavoritos($input['ccod_cblistpre'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = mapArticuloFacturacion($f);
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarClientesTodos  (Facturacion.aspx.vb 152-177)
    // VB DA: sp_consultarclientestodos (@ccod_cia, @texto, @ccod_usuario, @tipodoc)
    // VB lee: 0:cdsc_coa, 1:id_coa, 2:cdoc_coa, 3:ctipo_coa, 4:cdirc_coa, 5:ctip_doc
    // ============================================================
    case 'ConsultarClientesTodos':
        $input = getFacturacionInput();
        $rows = (new DACliente())->ConsultarClientesTodos($input['texto'] ?? '', $input['tipodoc'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdsc_coa' => S($f[0]),
                'id_coa' => S($f[1]),
                'cdoc_coa' => S($f[2]),
                'ctipo_coa' => S($f[3]),
                'cdirc_coa' => S($f[4]),
                'ctip_doc' => S($f[5])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarArticuloPrecio  (Facturacion.aspx.vb 217-249)
    // VB DA: sp_consultararticuloprecio (@ccod_cia, @ccod_usuario, @codigo, @ccod_almacen)
    // VB lee:
    //   0:npre_uni  1:cdsc_articulo  2:igv  3:isc  4:ctip_articulo
    //   5:state     6:npre_costo     7:ndes_max
    // ============================================================
    case 'ConsultarArticuloPrecio':
        $input = getFacturacionInput();
        $codigo = resolverCodigoPrecio($input['codigo'] ?? '', $o);
        $daPrecio = new DAPrecio();
        $rows = $daPrecio->ConsultarArticuloPrecio($codigo, $o);
        if (count($rows) === 0) {
            $rows = $daPrecio->ConsultarArticuloPrecioCodigo($codigo, $o);
        }
        jsonResponse(array('d' => mapPreciosResponse($rows, false, $codigo)));
        break;

    case 'LSConsultarArticuloPrecio':
        $input = getFacturacionInput();
        $codigo = resolverCodigoPrecio($input['codigo'] ?? '', $o);
        $daPrecio = new DAPrecio();
        $rows = $daPrecio->LSConsultarArticuloPrecio($codigo, $input['ccod_cblistpre'] ?? '', $o);
        if (count($rows) === 0) {
            $rows = $daPrecio->LSConsultarArticuloPrecioCodigo($codigo, $input['ccod_cblistpre'] ?? '', $o);
        }
        jsonResponse(array('d' => mapPreciosResponse($rows, false, $codigo)));
        break;

    // ============================================================
    // ConsultarArticuloPrecioCodigo  (Facturacion.aspx.vb 180-214)
    // VB lee:
    //   0:npre_uni  1:cdsc_articulo  2:igv  3:isc  4:ctip_articulo
    //   5:state     6:npre_costo     7:ndes_max  8:id_cblistpre
    // ============================================================
    case 'ConsultarArticuloPrecioCodigo':
        $input = getFacturacionInput();
        $codigo = resolverCodigoPrecio($input['codigo'] ?? '', $o);
        $rows = (new DAPrecio())->ConsultarArticuloPrecioCodigo($codigo, $o);
        jsonResponse(array('d' => mapPreciosResponse($rows, true, $codigo)));
        break;

    case 'LSConsultarArticuloPrecioCodigo':
        $input = getFacturacionInput();
        $codigo = resolverCodigoPrecio($input['codigo'] ?? '', $o);
        $rows = (new DAPrecio())->LSConsultarArticuloPrecioCodigo($codigo, $input['ccod_cblistpre'] ?? '', $o);
        jsonResponse(array('d' => mapPreciosResponse($rows, true, $codigo)));
        break;

    // ============================================================
    // ConsultarVariantesActivas / ConsultarSubVariantesActivas
    // ============================================================
    case 'ConsultarVariantesActivas':
        $input = getFacturacionInput();
        $rows = (new DAVariante())->ConsultarVariantesActivas($input['id_articulo'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('id_cbvariante' => S($f[1]), 'cdsc_variante' => S($f[2]));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarSubVariantesActivas':
        $input = getFacturacionInput();
        $rows = (new DAVariante())->ConsultarSubVariantesActivas($input['id_variante'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('id_cbvariante' => S($f[0]), 'cdsc_variante' => S($f[2]));
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarUsuarios  (Facturacion.aspx.vb 552-573)
    // VB DA: webDatpos_consultaUsuario (@ccod_cia)  — NO sp_consultarusuarios
    // VB lee 0..5 (ccod_usuario, cdsc_usuario, cdirec, cdsc_rol, estado, cdsc_tienda)
    // ============================================================
    case 'ConsultarUsuarios':
        $rows = (new DAUsuario())->consultarUsuarios($o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_usuario' => S($f[0]),
                'cdsc_usuario' => S($f[1]),
                'cdirec' => S($f[2]),
                'cdsc_rol' => S($f[3]),
                'estado' => S($f[4]),
                'cdsc_tienda' => S($f[5])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarCuentas / ConsultarCuentaDetalles
    // ============================================================
    case 'ConsultarCuentas':
        // Solo se usa Facturacion (ctip_cuenta = '1'); FacturaListaPrecio fue eliminado.
        $ctip_cuenta = '1';
        $rows = (new DACuenta())->ConsultarCuentas($o->ccod_empresa, $o->ccod_tiend, $o->ccod_caja, $ctip_cuenta, $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_cbcuenta' => S($f[0]),
                'cetiqueta' => S($f[5] ?? ''),
                'fechacreacion' => S($f[13] ?? ''),
                'ntot_desct' => S($f[8] ?? '0.00'),
                'ntot_impbruto' => S($f[9] ?? '0.00'),
                'ntot_igv' => S($f[10] ?? '0.00'),
                'ntot_impneto' => S($f[11] ?? '0.00')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarCuentaDetalles':
        $input = getFacturacionInput();
        $rows = (new DACuenta())->ConsultarCuentaDetalles(intval($input['id_cbcuenta'] ?? 0), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_articulo' => S($f[6] ?? ''),
                'cobser_variante' => S($f[11] ?? '-'),
                'ncantidad' => S($f[3] ?? '0'),
                'ndescuento' => S($f[9] ?? '0.00'),
                'ctip_descn' => S($f[10] ?? ''),
                'ctip_desc' => S($f[14] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'LSConsultarCuentaDetalles':
        $input = getFacturacionInput();
        $rows = (new DACuenta())->ConsultarCuentaDetallesFull(intval($input['id_cbcuenta'] ?? 0), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdsc_articulo' => S($f[20] ?? ''),
                'ncantidad' => S($f[3] ?? '0'),
                'nprecio' => S($f[4] ?? '0.00'),
                'nimporte_neto' => S($f[5] ?? '0.00'),
                'id_articulo' => S($f[6] ?? ''),
                'nigv_uni' => S($f[15] ?? '0.00'),
                'ctip_art' => S($f[21] ?? 'S'),
                'ncosto' => S($f[16] ?? '0.00'),
                'nimpuesto' => S($f[8] ?? '0.00'),
                'id_variante' => S($f[17] ?? ''),
                'cdescn_max' => S($f[18] ?? ''),
                'ndescuento' => S($f[9] ?? '0.00'),
                'ctip_desc' => S($f[14] ?? ''),
                'cobser_variante' => S($f[11] ?? '-'),
                'cdsc_desc' => S($f[14] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultaDocumentosSunat  (Facturacion.aspx.vb 74-101)
    // PENDIENTE: el SP real no existe en catálogo; el VB original tampoco
    // ejecuta CommandText. Devolvemos lista vacía — confirmar con admin BD.
    // ============================================================
    case 'ConsultaDocumentosSunat':
        $input = getFacturacionInput();
        $data = $input['objconsulta'][0] ?? array();
        $rows = $objBL->ConsultaDocumentosSunat($data, $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccoa_dsc' => S($f[0]),
                'cdoc' => S($f[1]),
                'cdoc_serie' => S($f[2]),
                'ntotal' => S($f[3]),
                'dfch_doc' => S($f[4]),
                'estadodoc' => S($f[5]),
                'fechsunat' => S($f[6])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ConsultarImpuestos  (Facturacion.aspx.vb 531-550)
    // ============================================================
    case 'ConsultarImpuestos':
        $rows = (new DAEmpresa())->ConsultarImpuestos($o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('nigv' => S($f[0]), 'nisc' => S($f[1]));
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // ActualizarFavorito  (Facturacion.aspx.vb 630-645)
    // VB DA: sp_actualizarfavorito (@id_articulo string, @bprefer)
    // ============================================================
    case 'ActualizarFavorito':
        $input = getFacturacionInput();
        (new DAArticulo())->actualizarFavorito(
            S($input['id_articulo'] ?? ''),
            S($input['bprefer'] ?? '')
        , $o);
        jsonResponse(array('d' => true));
        break;

    // ============================================================
    // GuardarCuenta  (Facturacion.aspx.vb 381-396)
    // VB BL.BLCuenta.InsertarCuenta — flujo transaccional con SPs
    // sp_insertarcuenta (OUTPUT @id_cbcuenta) + sp_insertarcuentadetalle (loop)
    // ============================================================
    case 'GuardarCuenta':
        $input = getFacturacionInput();
        $cliente = resolveClienteCuenta($input['cliente'] ?? '', $o);
        $etiqueta = $input['etiqueta'] ?? '';
        $detalle = $input['detalle'] ?? array();

        $conn = Database::getTenantConnection($o);
        if (!$conn) { jsonResponse(array('d' => array(false, 'Error de conexion'))); }

        sqlsrv_begin_transaction($conn);

        try {
            $sql = "DECLARE @id_cbcuenta INT;\n"
                 . "EXEC sp_insertarcuenta "
                 . "@ccod_cia=?, @ccod_coa=?, @ccod_tiend=?, @ccod_caja=?, "
                 . "@etiqueta=?, @ccod_usuario=?, @ctip_cuenta=?, "
                 . "@id_cbcuenta=@id_cbcuenta OUTPUT;\n"
                 . "SELECT @id_cbcuenta AS id_cbcuenta;";

            $params = array(
                $o->ccod_empresa, $cliente, $o->ccod_tiend, $o->ccod_caja,
                $etiqueta, $o->ccod_usuario, '1'
            );
            $stmt = sqlsrv_query($conn, $sql, $params);
            if (!$stmt) throw new Exception("Error inserting cuenta");

            $id_cbcuenta = 0;
            $rowC = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$rowC || !isset($rowC['id_cbcuenta'])) {
                while (sqlsrv_next_result($stmt) !== false) {
                    $rowC = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    if ($rowC && isset($rowC['id_cbcuenta'])) break;
                    $rowC = null;
                }
            }
            if ($rowC) { $id_cbcuenta = intval($rowC['id_cbcuenta']); }
            sqlsrv_free_stmt($stmt);

            for ($i = 0; $i < count($detalle); $i++) {
                $sql2 = "EXEC sp_insertarcuentadetalle "
                      . "@ncantidad=?, @nprecio=?, @nimporte_neto=?, @id_articulo=?, "
                      . "@nimporte_bruto=?, @nimpuesto=?, @ndescuento=?, @ctip_descn=?, "
                      . "@cobser_variante=?, @ccod_cia=?, @id_cbcuenta=?, @corden=?, "
                      . "@ccod_usuario=?, @ctip_desc=?";
                $params2 = array(
                    floatval($detalle[$i]['ncantidad'] ?? 0),
                    floatval($detalle[$i]['nprecio'] ?? 0),
                    floatval($detalle[$i]['nimporte_neto'] ?? 0),
                    resolveDetalleArticulo($detalle[$i]['id_articulo'] ?? '', $o),
                    floatval($detalle[$i]['nimporte_bruto'] ?? 0),
                    floatval($detalle[$i]['nimpuesto'] ?? 0),
                    floatval($detalle[$i]['ndescuento'] ?? 0),
                    $detalle[$i]['ctip_descn'] ?? '',
                    $detalle[$i]['cobser_variante'] ?? '',
                    $o->ccod_empresa,
                    $id_cbcuenta,
                    $i + 1,
                    $o->ccod_usuario,
                    $detalle[$i]['ctip_desc'] ?? ''
                );
                $stmt2 = sqlsrv_query($conn, $sql2, $params2);
                if (!$stmt2) throw new Exception("Error inserting detalle");
                sqlsrv_free_stmt($stmt2);
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            jsonResponse(array('d' => true));

        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            jsonResponse(array('d' => array(false, $e->getMessage())));
        }
        break;

    case 'LSGuardarCuenta':
        $input = getFacturacionInput();
        $cliente = resolveClienteCuenta($input['cliente'] ?? '', $o);
        $etiqueta = $input['etiqueta'] ?? '';
        $detalle = $input['detalle'] ?? array();

        $conn = Database::getTenantConnection($o);
        if (!$conn) { jsonResponse(array('d' => false)); }

        sqlsrv_begin_transaction($conn);

        try {
            $sql = "DECLARE @id_cbcuenta INT;\n"
                 . "EXEC sp_lsinsertarcuenta "
                 . "@ccod_cia=?, @ccod_coa=?, @ccod_tiend=?, @ccod_caja=?, "
                 . "@etiqueta=?, @ccod_usuario=?, @ctip_cuenta=?, "
                 . "@ntot_desct=?, @ntot_impbruto=?, @ntot_igv=?, @ntot_impneto=?, "
                 . "@id_cbcuenta=@id_cbcuenta OUTPUT;\n"
                 . "SELECT @id_cbcuenta AS id_cbcuenta;";

            $params = array(
                $o->ccod_empresa,
                $cliente,
                $o->ccod_tiend,
                $o->ccod_caja,
                $etiqueta,
                $o->ccod_usuario,
                '2',
                floatval($input['ntot_desct'] ?? 0),
                floatval($input['ntot_impbruto'] ?? 0),
                floatval($input['ntot_igv'] ?? 0),
                floatval($input['ntot_impneto'] ?? 0)
            );

            $stmt = sqlsrv_query($conn, $sql, $params);
            if (!$stmt) throw new Exception("Error insertando cuenta lista precio");

            $id_cbcuenta = 0;
            $rowLS = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$rowLS || !isset($rowLS['id_cbcuenta'])) {
                while (sqlsrv_next_result($stmt) !== false) {
                    $rowLS = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    if ($rowLS && isset($rowLS['id_cbcuenta'])) break;
                    $rowLS = null;
                }
            }
            if ($rowLS) { $id_cbcuenta = intval($rowLS['id_cbcuenta']); }
            sqlsrv_free_stmt($stmt);

            for ($i = 0; $i < count($detalle); $i++) {
                $sql2 = "EXEC sp_lsinsertarcuentadetalle "
                      . "@ncantidad=?, @nprecio=?, @nimporte_neto=?, @id_articulo=?, "
                      . "@nimporte_bruto=?, @nimpuesto=?, @ndescuento=?, @ctip_descn=?, "
                      . "@cobser_variante=?, @ccod_cia=?, @id_cbcuenta=?, @corden=?, "
                      . "@ccod_usuario=?, @ctip_desc=?, @nigv_uni=?, @ncosto=?, "
                      . "@id_variante=?, @cdescn_max=?";

                $params2 = array(
                    floatval($detalle[$i]['ncantidad'] ?? 0),
                    floatval($detalle[$i]['nprecio'] ?? 0),
                    floatval($detalle[$i]['nimporte_neto'] ?? 0),
                    resolveDetalleArticulo($detalle[$i]['id_articulo'] ?? '', $o),
                    floatval($detalle[$i]['nimporte_neto'] ?? 0),
                    floatval($detalle[$i]['nimpuesto'] ?? 0),
                    floatval($detalle[$i]['ndescuento'] ?? 0),
                    $detalle[$i]['ctip_descn'] ?? '',
                    $detalle[$i]['cobser_variante'] ?? '',
                    $o->ccod_empresa,
                    $id_cbcuenta,
                    $i + 1,
                    $o->ccod_usuario,
                    $detalle[$i]['ctip_desc'] ?? '',
                    floatval($detalle[$i]['nigv_uni'] ?? 0),
                    floatval($detalle[$i]['ncosto'] ?? 0),
                    $detalle[$i]['id_variante'] ?? '',
                    $detalle[$i]['cdescn_max'] ?? ''
                );

                $stmt2 = sqlsrv_query($conn, $sql2, $params2);
                if (!$stmt2) throw new Exception("Error insertando detalle cuenta lista precio");
                sqlsrv_free_stmt($stmt2);
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            jsonResponse(array('d' => true));
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            jsonResponse(array('d' => array(false, $e->getMessage())));
        }
        break;

    // ============================================================
    // Cobrar  (Facturacion.aspx.vb 463-500)
    // VB:
    //   1. ValidarAlFacturar (cabecera.cdoc, sesion)
    //   2. si resp == "" → InsertarMovimientoCabecera(cab, det, sesion, cant, cobranza, Session("id_turno"))
    // ============================================================
    case 'Cobrar':
        $input = getFacturacionInput();
        $cabecera = $input['cabecera'][0] ?? array();
        $detalle = $input['detalle'] ?? array();
        $cantidad_bienes = intval($input['cantidad_bienes'] ?? 0);
        $CobranzaDetalle = $input['CobranzaDetalle'] ?? array();
        $id_turno = intval($_SESSION['id_turno'] ?? 0);

        $resp = $objBL->ValidarAlFacturar($cabecera['cdoc'] ?? '', $o);

        if ($resp !== '' && $resp !== null) {
            jsonResponse(array('d' => array(false, $resp, '', '')));
            break;
        }

        $objBE = new stdClass();
        $objBE->cdoc = $cabecera['cdoc'] ?? '';
        $objBE->ccod_coa = resolveClienteCuenta($cabecera['ccod_coa'] ?? '', $o);
        $objBE->nimpuesto = floatval($cabecera['nimpuesto'] ?? 0);
        $objBE->nisc = floatval($cabecera['nisc'] ?? 0);
        $objBE->ndescuento = floatval($cabecera['ndescuento'] ?? 0);
        $objBE->ntotal = floatval($cabecera['ntotal'] ?? 0);
        $objBE->nsubtotal = floatval($cabecera['nsubtotal'] ?? 0);
        $objBE->nvuelto = floatval($cabecera['nvuelto'] ?? 0);
        $objBE->ntot_entreg = floatval($cabecera['ntot_entreg'] ?? 0);
        $objBE->costo = floatval($cabecera['costo'] ?? 0);
        $objBE->cobs = $cabecera['cobs'] ?? '';

        $result = $objBL->InsertarMovimientoCabecera(
            $objBE, $detalle, $o, $cantidad_bienes, $CobranzaDetalle, $id_turno
        );

        jsonResponse(array('d' => $result));
        break;

    // ============================================================
    // BuscarNCIdCliente  (Facturacion.aspx.vb 647-665)
    // SP: webDatpos_buscarNCIdCliente (@id_coa, @ccod_cia)
    // ============================================================
    case 'BuscarNCIdCliente':
        $input = getFacturacionInput();
        $rows = Database::selectStoredTenant('webDatpos_buscarNCIdCliente', array(
            '@id_coa' => $input['id_coa'] ?? '',
            '@ccod_cia' => $o->ccod_empresa
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_cbfact' => S($f[0]),
                'Doc' => "<input id='" . S($f[0]) . "' type='radio' name='radiob' />",
                'dfch_doc' => S($f[1]),
                'nimp_aplicado' => S($f[2]),
                'cdoc' => S($f[3])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ============================================================
    // RegistrarPdf  (Facturacion.aspx.vb 668-680)
    // VB DA: sp_registrarpdf (@id_cbfact, @pdf)  — NO @ccod_cia
    // El JS envía pdf como datauristring; el VB hace pdf.Split(";")(2).Remove(0,7)
    //   que retira el prefijo "base64,". Replicamos.
    // ============================================================
    case 'RegistrarPdf':
        $input = getFacturacionInput();
        $id_cbfact = intval($input['id_cbfact'] ?? 0);
        $pdf = $input['pdf'] ?? '';
        $parts = explode(';', $pdf);
        $base64 = isset($parts[2]) ? substr($parts[2], 7) : preg_replace('/^data:application\/pdf;base64,/', '', $pdf);
        (new DAArticulo())->registrarPdf($id_cbfact, $base64, $o);
        jsonResponse(array('d' => true));
        break;

    // ============================================================
    // CargarTienda / CargarCliente  (utilizados por algunas pantallas vinculadas)
    // VB equivalente: webDatpos_consultaTienda (@ccod_cia)
    // ============================================================
    case 'CargarTienda':
    case 'CargarCliente':
        $rows = (new DAConsultaDocumento())->ConsultaTienda($o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_tiend' => S($f[0]), 'cnombr' => S($f[1]));
        }
        jsonResponse(array('d' => $lst));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
