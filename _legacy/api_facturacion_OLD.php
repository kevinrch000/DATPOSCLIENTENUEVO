<?php
/**
 * api/facturacion.php
 * Reemplaza todos los WebMethods de Facturacion.aspx.vb
 * El JS llama a: Facturacion.aspx/NombreMetodo
 * PHP responde con: { "d": <datos> }  (mismo formato que ASP.NET ScriptMethod)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();
$objUsuario = getUsuarioSesion();

header('Content-Type: application/json; charset=utf-8');

// Leer acción del request
$action = $_GET['action'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// Helper: envolver respuesta igual que ASP.NET
function resp($data)
{
    echo json_encode(['d' => $data]);
    exit;
}

// Helper: error de sesión
function sessionError()
{
    echo json_encode(['d' => '-1']);
    exit;
}

// ============================================================
// ROUTER
// ============================================================
switch ($action) {

    // --------------------------------------------------------
    // CargarFavoritos
    // Reemplaza: Facturacion.aspx/CargarFavoritos
    // --------------------------------------------------------
    case 'CargarFavoritos':
        global $objUsuario;
        $rows = Database::selectStoredTenant('sp_lpconsultarfavoritos', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
            '@ccod_cblistpre' => getListaPrecio($objUsuario),
        ], $objUsuario);
        resp(mapArticulos($rows));
        break;

    // --------------------------------------------------------
    // ConsultarCategoriasDisponibles
    // Reemplaza: Facturacion.aspx/ConsultarCategoriasDisponibles
    // --------------------------------------------------------
    case 'ConsultarCategoriasDisponibles':
        $rows = Database::selectStoredTenant('sp_consultafamiliasactivas', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
        ], $objUsuario);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id_ctlin' => $r['id_lin'] ?? $r[0] ?? '',
                'cdsc_lin' => $r['cdsc_lin'] ?? $r[1] ?? '',
                'ccolor' => $r['ccolor'] ?? $r[2] ?? '#228ac9',
            ];
        }
        resp($out);
        break;

    // --------------------------------------------------------
    // ConsultarArticulosCategoria
    // Reemplaza: Facturacion.aspx/ConsultarArticulosCategoria
    // --------------------------------------------------------
    case 'ConsultarArticulosCategoria':
        $id_familia = $body['id_familia'] ?? '';
        $rows = Database::selectStoredTenant('sp_lsconsultararticulocategoria', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@codigo' => (int) $id_familia,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
            '@ccod_cblistpre' => getListaPrecio($objUsuario),
        ], $objUsuario);
        resp(mapArticulos($rows));
        break;

    // --------------------------------------------------------
    // ConsultarArticulosTodos  (búsqueda por texto/código)
    // Reemplaza: Facturacion.aspx/ConsultarArticulosTodos
    // --------------------------------------------------------
    case 'ConsultarArticulosTodos':
        $texto = $body['texto'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultararticulopreciocodigo', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@codigo' => $texto,
            '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
        ], $objUsuario);
        resp(mapArticulos($rows));
        break;

    // --------------------------------------------------------
    // ConsultarArticuloPrecio  (por click en artículo)
    // Reemplaza: Facturacion.aspx/ConsultarArticuloPrecio
    // --------------------------------------------------------
    case 'ConsultarArticuloPrecio':
        $codigo = $body['codigo'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultararticulopreciocodigo', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@codigo' => $codigo,
            '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
        ], $objUsuario);
        if (empty($rows)) {
            resp(false);
            break;
        }
        $r = $rows[0];
        resp([
            [
                'cdsc_articulo' => $r['cdsc_articulo'] ?? '',
                'npre_uni' => $r['npre_uni'] ?? 0,
                'ndes_max' => $r['ndes_max'] ?? 0,
                'igv' => calcIgv($r),
                'isc' => 0,
                'ctip_articulo' => $r['ctip_articulo'] ?? 'B',
                'npre_costo' => $r['ncosto'] ?? 0,
                'state' => $r['ncantidad'] ?? 0,
                'id_cblistpre' => 1,
            ]
        ]);
        break;

    // --------------------------------------------------------
    // ConsultarArticuloPrecioCodigo  (tb_anadir / Enter)
    // Reemplaza: Facturacion.aspx/ConsultarArticuloPrecioCodigo
    // --------------------------------------------------------
    case 'ConsultarArticuloPrecioCodigo':
        $codigo = $body['codigo'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultararticulopreciocodigo', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@codigo' => $codigo,
            '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
        ], $objUsuario);
        if (empty($rows)) {
            resp(false);
            break;
        }
        $r = $rows[0];
        resp([
            [
                'cdsc_articulo' => $r['cdsc_articulo'] ?? '',
                'npre_uni' => $r['npre_uni'] ?? 0,
                'ndes_max' => $r['ndes_max'] ?? 0,
                'igv' => calcIgv($r),
                'isc' => 0,
                'ctip_articulo' => $r['ctip_articulo'] ?? 'B',
                'npre_costo' => $r['ncosto'] ?? 0,
                'state' => $r['ncantidad'] ?? 0,
                'id_cblistpre' => 1,
            ]
        ]);
        break;

    // --------------------------------------------------------
    // ObtenerIGV
    // Reemplaza: Facturacion.aspx/ObtenerIGV
    // --------------------------------------------------------
    case 'ObtenerIGV':
        $rows = Database::selectStoredTenant('appDatpos_ObtenerIGV', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
        ], $objUsuario);
        if (empty($rows)) {
            resp([['Impuesto' => 1.18]]);
            break;
        }
        $nigv = ($rows[0]['nigv'] ?? 18) / 100 + 1;
        resp([['Impuesto' => $nigv]]);
        break;

    // --------------------------------------------------------
    // ClientePorDefecto
    // Reemplaza: Facturacion.aspx/ClientePorDefecto
    // --------------------------------------------------------
    case 'ClientePorDefecto':
        $rows = Database::selectStoredTenant('webDatpos_cargarClientePredeterminado', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
        ], $objUsuario);
        if (empty($rows)) {
            resp(false);
            break;
        }
        $r = $rows[0];
        resp([
            [
                'ccod_coa' => $r['ccod_coa'] ?? '',
                'cdsc_coa' => $r['cdsc_coa'] ?? '',
                'cdoc_coa' => $r['cdoc_coa'] ?? '',
                'cruc_coa' => $r['cruc_coa'] ?? '',
                'ctip_doc' => 'DNI',
                'cdirc_coa' => '',
            ]
        ]);
        break;

    // --------------------------------------------------------
    // ConsultarClientesTodos  (búsqueda live de clientes)
    // Reemplaza: Facturacion.aspx/ConsultarClientesTodos
    // --------------------------------------------------------
    case 'ConsultarClientesTodos':
        $texto = $body['texto'] ?? '';
        $tipodoc = $body['tipodoc'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultarclientestodos', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@texto' => $texto,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@tipodoc' => $tipodoc,
        ], $objUsuario);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id_coa' => $r['id_coa'] ?? $r[0] ?? '',
                'ccod_coa' => $r['ccod_coa'] ?? $r[1] ?? '',
                'cdsc_coa' => $r['cdsc_coa'] ?? $r[2] ?? '',
                'cdoc_coa' => $r['cdoc_coa'] ?? $r[3] ?? '',
                'cdirc_coa' => $r['cdirc_coa'] ?? $r[4] ?? '',
                'ctip_doc' => strlen($r['cdoc_coa'] ?? '') == 8 ? 'DNI' : 'RUC',
                'ctipo_coa' => strlen($r['cdoc_coa'] ?? '') == 11 ? 2 : 1,
            ];
        }
        resp($out);
        break;

    // --------------------------------------------------------
    // ConsultarVariantesActivas
    // Reemplaza: Facturacion.aspx/ConsultarVariantesActivas
    // --------------------------------------------------------
    case 'ConsultarVariantesActivas':
        $id_articulo = $body['id_articulo'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultarvariantesactivas', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_articulo' => $id_articulo,
        ], $objUsuario);
        resp($rows ?: false);
        break;

    // --------------------------------------------------------
    // ConsultarSubVariantesActivas
    // Reemplaza: Facturacion.aspx/ConsultarSubVariantesActivas
    // --------------------------------------------------------
    case 'ConsultarSubVariantesActivas':
        $id_variante = $body['id_variante'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultarsubvariantesactivas', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_cbvariante' => (int) $id_variante,
        ], $objUsuario);
        resp($rows ?: false);
        break;

    // --------------------------------------------------------
    // ActualizarFavorito
    // Reemplaza: Facturacion.aspx/ActualizarFavorito
    // --------------------------------------------------------
    case 'ActualizarFavorito':
        $id_articulo = $body['id_articulo'] ?? '';
        $bprefer = (int) ($body['bprefer'] ?? 0);
        Database::executeStoredTenant('sp_actualizarfavorito', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_articulo' => $id_articulo,
            '@bprefer' => $bprefer,
        ], $objUsuario);
        resp(true);
        break;

    // --------------------------------------------------------
    // ConsultarTienda  (para ticket de impresión)
    // Reemplaza: Facturacion.aspx/ConsultarTienda
    // --------------------------------------------------------
    case 'ConsultarTienda':
        $rows = Database::selectStoredTenant('sp_consultartienda', [
            '@ccod_empresa' => $objUsuario->ccod_empresa,
            '@ccod_tiend' => $objUsuario->ccod_tiend ?? '',
        ], $objUsuario);
        if (empty($rows)) {
            resp([['cdirec' => '', 'ctelef' => '']]);
            break;
        }
        resp([['cdirec' => $rows[0]['cdirec'] ?? '', 'ctelef' => $rows[0]['ctelef'] ?? '']]);
        break;

    // --------------------------------------------------------
    // GuardarCuenta
    // Reemplaza: Facturacion.aspx/GuardarCuenta
    // --------------------------------------------------------
    case 'GuardarCuenta':
        $cliente = $body['cliente'] ?? '';
        $etiqueta = $body['etiqueta'] ?? '';
        $detalle = $body['detalle'] ?? [];
        if (empty($etiqueta) || empty($detalle)) {
            resp(false);
            break;
        }
        try {
            // Insertar cabecera cuenta (tabla CbCuenta si existe, si no se omite)
            // Como no está en el plan de tablas, guardamos en sesión como fallback
            $_SESSION['cuenta_' . $objUsuario->ccod_empresa . '_' . time()] = [
                'etiqueta' => $etiqueta,
                'cliente' => $cliente,
                'detalle' => $detalle,
                'fecha' => date('Y-m-d H:i:s'),
            ];
            resp(true);
        } catch (Exception $e) {
            resp([false, $e->getMessage()]);
        }
        break;

    // --------------------------------------------------------
    // ConsultarCuentas
    // Reemplaza: Facturacion.aspx/ConsultarCuentas
    // --------------------------------------------------------
    case 'ConsultarCuentas':
        $cuentas = [];
        foreach ($_SESSION as $k => $v) {
            if (strpos($k, 'cuenta_' . $objUsuario->ccod_empresa . '_') === 0) {
                $ts = str_replace('cuenta_' . $objUsuario->ccod_empresa . '_', '', $k);
                $cuentas[] = [
                    'id_cbcuenta' => $ts,
                    'cetiqueta' => $v['etiqueta'] ?? '',
                    'fechacreacion' => $v['fecha'] ?? '',
                ];
            }
        }
        resp($cuentas);
        break;

    // --------------------------------------------------------
    // ConsultarCuentaDetalles
    // Reemplaza: Facturacion.aspx/ConsultarCuentaDetalles
    // --------------------------------------------------------
    case 'ConsultarCuentaDetalles':
        $id = $body['id_cbcuenta'] ?? '';
        $key = 'cuenta_' . $objUsuario->ccod_empresa . '_' . $id;
        if (!isset($_SESSION[$key])) {
            resp(false);
            break;
        }
        resp($_SESSION[$key]['detalle']);
        break;

    // --------------------------------------------------------
    // Cobrar  (grabar venta completa)
    // Reemplaza: Facturacion.aspx/Cobrar
    // --------------------------------------------------------
    case 'Cobrar':
        $cabecera = $body['cabecera'] ?? [];
        $detalle = $body['detalle'] ?? [];
        $cantidad_bienes = $body['cantidad_bienes'] ?? 0;
        $cobranza_detalle = $body['CobranzaDetalle'] ?? [];

        if (empty($cabecera) || empty($detalle)) {
            resp([false, 'Datos incompletos']);
            break;
        }

        try {
            $cab = $cabecera[0];
            $id_turno = $_SESSION['id_turno'] ?? 0;

            // 1. Insertar movimiento cabecera (factura)
            $paramsCab = [
                '@ccod_cia' => $objUsuario->ccod_empresa,
                '@ccod_tiend' => $objUsuario->ccod_tiend ?? '',
                '@ccod_caja' => $objUsuario->ccod_caja ?? '',
                '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
                '@ccod_usuario' => $objUsuario->ccod_usuario,
                '@cdoc' => $cab['cdoc'] ?? 'BV',
                '@ccod_coa' => $cab['ccod_coa'] ?? '',
                '@nimpuesto' => (float) ($cab['nimpuesto'] ?? 0),
                '@nisc' => (float) ($cab['nisc'] ?? 0),
                '@ndescuento' => (float) ($cab['ndescuento'] ?? 0),
                '@ntotal' => (float) ($cab['ntotal'] ?? 0),
                '@nsubtotal' => (float) ($cab['nsubtotal'] ?? 0),
                '@nvuelto' => (float) ($cab['nvuelto'] ?? 0),
                '@ntot_entreg' => (float) ($cab['ntot_entreg'] ?? 0),
                '@cantidad_bienes' => (int) $cantidad_bienes,
                '@id_turno' => (int) $id_turno,
                '@costo' => (float) ($cab['costo'] ?? 0),
                '@cobs' => $cab['cobs'] ?? '',
            ];

            $resultCab = Database::executeStoredTenantWithOutput(
                'sp_insertarmovimientocabeceranew',
                $paramsCab,
                $objUsuario
            );

            $id_cbfact = $resultCab['@id_cbfact']['value']
                ?? $resultCab['id_cbfact']
                ?? Database::getLastInsertId($objUsuario);

            if (!$id_cbfact) {
                resp([false, 'No se pudo registrar la cabecera']);
                break;
            }

            // 2. Insertar detalles de factura
            foreach ($detalle as $d) {
                Database::executeStoredTenant('sp_insertarmovimientodetalle', [
                    '@ccod_cia' => $objUsuario->ccod_empresa,
                    '@id_cbfact' => (int) $id_cbfact,
                    '@ccod_tiend' => $objUsuario->ccod_tiend ?? '',
                    '@id_articulo' => $d['id_articulo'] ?? '',
                    '@cdsc_articulo' => $d['cdsc_articulo'] ?? '',
                    '@cdoc' => $cab['cdoc'] ?? 'BV',
                    '@nprecio' => (float) ($d['nprecio'] ?? 0),
                    '@ncantidad' => (float) ($d['ncantidad'] ?? 1),
                    '@nimporte_bruto' => (float) ($d['nimporte_bruto'] ?? 0),
                    '@nimpuesto' => (float) ($d['nimpuesto'] ?? 0),
                    '@nisc' => (float) ($d['nisc'] ?? 0),
                    '@ndescuento' => (float) ($d['ndescuento'] ?? 0),
                    '@nimporte_neto' => (float) ($d['nimporte_neto'] ?? 0),
                    '@corden' => 1,
                    '@ccod_usuario' => $objUsuario->ccod_usuario,
                    '@id_cbinve' => 0,
                    '@ccod_almacen' => $objUsuario->ccod_almacen ?? '',
                    '@cobser_variante' => $d['cobser_variante'] ?? '-',
                    '@ctip_descn' => $d['ctip_descn'] ?? '',
                ], $objUsuario);
            }

            // 3. Insertar cobranza cabecera
            $paramsCob = [
                '@ccod_cia' => $objUsuario->ccod_empresa,
                '@id_cbfact' => (int) $id_cbfact,
                '@id_turno' => (int) $id_turno,
                '@ccod_tiend' => $objUsuario->ccod_tiend ?? '',
                '@ccod_caja' => $objUsuario->ccod_caja ?? '',
                '@ccod_usuario' => $objUsuario->ccod_usuario,
                '@ntotal' => (float) ($cab['ntotal'] ?? 0),
                '@ntot_entreg' => (float) ($cab['ntot_entreg'] ?? 0),
                '@nvuelto' => (float) ($cab['nvuelto'] ?? 0),
            ];
            $resultCob = Database::executeStoredTenantWithOutput(
                'sp_insertarcobranzacabecera',
                $paramsCob,
                $objUsuario
            );
            $id_cbcajac = $resultCob['@id_cbcajac']['value']
                ?? $resultCob['id_cbcajac']
                ?? Database::getLastInsertId($objUsuario);

            // 4. Insertar detalles de cobranza
            foreach ($cobranza_detalle as $c) {
                Database::executeStoredTenant('sp_insertarcobranzadetalle', [
                    '@ccod_cia' => $objUsuario->ccod_empresa,
                    '@id_cbcajac' => (int) $id_cbcajac,
                    '@id_cbfact' => (int) $id_cbfact,
                    '@ccod_tiend' => $objUsuario->ccod_tiend ?? '',
                    '@nmonto' => (float) ($c['nmonto'] ?? 0),
                    '@cnum_opera' => $c['cnum_opera'] ?? '-',
                    '@cnum_tarje' => $c['cnum_tarje'] ?? '-',
                    '@cnom_tarje' => $c['cnom_tarje'] ?? 'Efectivo',
                    '@id_cbfactNC' => (int) ($c['id_cbfact'] ?? 0),
                    '@ccod_usuario' => $objUsuario->ccod_usuario,
                    '@ccod_caja' => $objUsuario->ccod_caja ?? '',
                ], $objUsuario);
            }

            // 5. Obtener serie y número del documento generado
            $docRows = Database::selectStoredTenant('sp_consultardocumentocabecera', [
                '@ccod_cia' => $objUsuario->ccod_empresa,
                '@id_cbfact' => (int) $id_cbfact,
            ], $objUsuario);

            $serie = $docRows[0]['cserie'] ?? 'B001';
            $numero = $docRows[0]['nnumero'] ?? $id_cbfact;
            $cdoc = $docRows[0]['cdoc'] ?? $cab['cdoc'];
            $fecha = $docRows[0]['fecha_emision'] ?? date('d/m/Y');

            $label_doc = strtoupper($cdoc) . ' ' . $serie . ' - ' . str_pad($numero, 8, '0', STR_PAD_LEFT);

            resp([true, $label_doc, $id_cbfact, $fecha]);

        } catch (Exception $e) {
            resp([false, $e->getMessage()]);
        }
        break;

    // --------------------------------------------------------
    // RegistrarPdf
    // Reemplaza: Facturacion.aspx/RegistrarPdf
    // --------------------------------------------------------
    case 'RegistrarPdf':
        $id_cbfact = $body['id_cbfact'] ?? '';
        $pdf = $body['pdf'] ?? '';
        // Guardar referencia (el PDF base64 es muy grande, se omite en BD por ahora)
        resp(true);
        break;

    // --------------------------------------------------------
    // CargarFotoUsuario  (logo para el ticket)
    // Reemplaza: Home.aspx/CargarFotoUsuario
    // --------------------------------------------------------
    case 'CargarFotoUsuario':
        $rows = Database::selectAdmin(
            'SELECT ilogo FROM ConfigGeneral WHERE ccod_cia = ?',
            [$objUsuario->ccod_empresa],
            $objUsuario
        );
        if (empty($rows) || empty($rows[0]['ilogo'])) {
            resp([]);
            break;
        }
        resp([['ilogo' => base64_encode($rows[0]['ilogo'])]]);
        break;

    // --------------------------------------------------------
    // BuscarNCIdCliente  (Nota de Crédito disponibles)
    // --------------------------------------------------------
    case 'BuscarNCIdCliente':
        $id_coa = $body['id_coa'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultardocumentocabecera', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_coa' => $id_coa,
            '@cdoc' => 'NC',
        ], $objUsuario);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id_cbfact' => $r['id_cbfact'] ?? '',
                'Doc' => ($r['cdoc'] ?? '') . ' ' . ($r['cserie'] ?? '') . '-' . ($r['nnumero'] ?? ''),
                'dfch_doc' => $r['fecha_emision'] ?? '',
                'nimp_aplicado' => $r['ntotal'] ?? 0,
                'cdoc' => $r['cdoc'] ?? '',
            ];
        }
        resp($out ?: false);
        break;

    // --------------------------------------------------------
    // CargarClienteFacturar  (modal buscar clientes)
    // --------------------------------------------------------
    case 'CargarClienteFacturar':
        $tip_doc = $body['tip_doc'] ?? '';
        $rows = Database::selectStoredTenant('sp_consultarclientestodos', [
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@texto' => '',
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@tipodoc' => $tip_doc,
        ], $objUsuario);
        $out = [];
        $i = 1;
        foreach ($rows as $r) {
            $out[] = [
                'item' => $i++,
                'id_coa' => $r['id_coa'] ?? $r[0] ?? '',
                'ccod_coa' => $r['ccod_coa'] ?? $r[1] ?? '',
                'cdsc_coa' => $r['cdsc_coa'] ?? $r[2] ?? '',
                'cdoc_coa' => $r['cdoc_coa'] ?? $r[3] ?? '',
                'cdirc_coa' => $r['cdirc_coa'] ?? '',
                'ctip_doc' => strlen($r['cdoc_coa'] ?? '') == 8 ? 'DNI' : 'RUC',
            ];
        }
        resp($out);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada: ' . $action]);
        exit;
}

// ============================================================
// HELPERS INTERNOS
// ============================================================

/**
 * Obtener lista de precio activa para el usuario
 */
function getListaPrecio($objUsuario)
{
    // Primero intentar la lista de precio del usuario/tienda
    $listaNormal = $objUsuario->nlista_pre_normal ?? 1;
    return (string) $listaNormal;
}

/**
 * Calcular IGV de un artículo (devuelve monto, no porcentaje)
 */
function calcIgv($r)
{
    $precio = (float) ($r['npre_uni'] ?? 0);
    $cigv = $r['cigv'] ?? 'G'; // G = gravado, E = exonerado, I = inafecto
    if ($cigv === 'G' && $precio > 0) {
        // El IGV está incluido en el precio: IGV = precio - precio/1.18
        try {
            $rows = Database::selectStoredTenant('appDatpos_ObtenerIGV', [
                '@ccod_cia' => $GLOBALS['objUsuario']->ccod_empresa,
            ], $GLOBALS['objUsuario']);
            $tasa = isset($rows[0]['nigv']) ? ($rows[0]['nigv'] / 100) : 0.18;
        } catch (Exception $e) {
            $tasa = 0.18;
        }
        return round($precio - $precio / (1 + $tasa), 6);
    }
    return 0;
}

/**
 * Mapear filas de artículos al formato que espera el JS original
 */
function mapArticulos($rows)
{
    if (empty($rows))
        return [];
    $out = [];
    foreach ($rows as $r) {
        $precio = (float) ($r['npre_uni'] ?? 0);
        $igv = calcIgv($r);
        $out[] = [
            'id_articulo' => $r['ccod_articulo'] ?? $r[0] ?? '',
            'cdsc_articulo' => $r['cdsc_articulo'] ?? $r[1] ?? '',
            'precio' => number_format($precio, 2),
            'npre_uni' => $precio,
            'ndes_max' => (float) ($r['ndes_max'] ?? 0),
            'igv' => $igv,
            'isc' => 0,
            'ctip_articulo' => $r['ctip_articulo'] ?? 'B',
            'ncantidad' => (float) ($r['ncantidad'] ?? 0),
            'npre_costo' => (float) ($r['ncosto'] ?? 0),
            'iimage' => $r['iimage'] ? base64_encode($r['iimage']) : '',
            'bprefer' => (int) ($r['bprefer'] ?? 0),
            'state' => (float) ($r['ncantidad'] ?? 0),
            'id_cblistpre' => 1,
        ];
    }
    return $out;
}