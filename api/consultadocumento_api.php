<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php'; require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DA/DAAperturaCaja.php';
if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario']; $m = $_GET['method'] ?? '';
switch ($m) {
    case 'ReporteTurnoPrincipal':
        $input = getJsonInput();
        $_SESSION['objReporteTurno'] = $input['ReporteTurno'][0] ?? array();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'CargarTurnoUsuario':
        $input = getJsonInput();
        $rows = (new DAAperturaCaja())->CargarTurnoUsuario($input['id_usuario'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_usuario' => strval($f[0] ?? ''), 'cdsc_usuario' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'InformeTurnoDatos':
        $data = $_SESSION['objReporteTurno'] ?? array();
        if (empty($data)) { jsonResponse(array('d' => array('filtros' => null, 'rows' => array()))); }
        $rows = Database::selectStoredTenant('webDatpos_ReporteTurno', array(
            '@ccod_cia' => $o->ccod_empresa,
            '@ccod_tienda' => $data['ccod_tienda'] ?? '',
            '@id_usuario' => $data['ccod_usuario'] ?? '',
            '@fchDesde' => $data['dfecha_ini'] ?? '',
            '@fchHasta' => $data['dfecha_fin'] ?? ''
        ), $o);
        jsonResponse(array('d' => array('filtros' => $data, 'rows' => $rows)));
        break;

    case 'ReporteAlmacenPrincipal':
        $input = getJsonInput();
        $_SESSION['objReportAlmacen'] = $input['ReportAlmacen'][0] ?? array();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'ReporteKardexPrincipal':
        $input = getJsonInput();
        $_SESSION['objReporteKardex'] = $input['ReporteKardex'][0] ?? array();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'ReporteSaldoPrincipal':
        $input = getJsonInput();
        $_SESSION['objReporteSaldo'] = $input['ReporteSaldo'][0] ?? array();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'ReporteTributarioPrincipal':
        $input = getJsonInput();
        $_SESSION['objReporteTributario'] = $input['ReporteTributario'][0] ?? array();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'ConsultaTributarioPrincipal':
        // El JS de ReporteTributario.js envia
        //   { ReporteTributario: [ { ccod_tienda, dfch_desde, dfch_hasta,
        //     cdoc, cdoc_serie, cdoc_nro, cstatus_tributario, ccod_coa } ] }
        // El SP webDatpos_ConsultaTributarioPrincipal (recreado en
        // MODIFY_912_FIX_65) devuelve 8 columnas en el orden:
        //   [0] id_cbfact (idDoc para los iconos), [1] cdsc_coa,
        //   [2] cdoc, [3] cdoc_serie, [4] cdoc_nro, [5] ntotal,
        //   [6] dfch_doc, [7] cstatus_tributario.
        $input = getJsonInput();
        $data = $input['ReporteTributario'][0] ?? array();
        $rows = Database::selectStoredTenant('webDatpos_ConsultaTributarioPrincipal', array(
            '@ccod_tienda'        => $data['ccod_tienda'] ?? '',
            '@fchDesde'           => $data['dfch_desde'] ?? '',
            '@fchHasta'           => $data['dfch_hasta'] ?? '',
            '@cdoc'               => $data['cdoc'] ?? '',
            '@cdoc_serie'         => $data['cdoc_serie'] ?? '',
            '@cdoc_nro'           => $data['cdoc_nro'] ?? '',
            '@ccod_coa'           => $data['ccod_coa'] ?? '',
            '@cstatus_tributario' => $data['cstatus_tributario'] ?? '',
            '@ccod_cia'           => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $idDoc = strval($f[0] ?? '');
            // DataTables ya envuelve el valor en su propio <td>, asi
            // que solo se devuelve el <i> con el id y el onclick que
            // dispara el handler del JS.
            $pdf = $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-file-pdf-o' title='Descargar PDF' onclick='DescargarArchivoPDF(this);'></i>" : '';
            $xml = $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-file-code-o' title='Descargar XML' onclick='DescargarArchivoXML(this);'></i>" : '';
            $zip = $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-file-archive-o' title='Descargar XML CDR' onclick='DescargarArchivoXMLCDR(this);'></i>" : '';
            $lst[] = array(
                // El JS de ReporteTributario muestra la columna "Cliente"
                // como `ccod_coa`; le pasamos el nombre del cliente para
                // que sea legible (el ccod real es ahora el RUC tras FIX_50).
                'ccod_coa'           => strval($f[1] ?? ''),
                'cdoc'               => strval($f[2] ?? ''),
                'cdoc_serie'         => strval($f[3] ?? ''),
                'cdoc_nro'           => strval($f[4] ?? ''),
                'ntotal'             => strval($f[5] ?? ''),
                'dfch_doc'           => strval($f[6] ?? ''),
                // FIX 73 / BUG 3.23: traducir codigos SUNAT a texto.
                // 1=Pendiente, 4=Aceptado, 5=Aceptado c/observaciones,
                // 6=Error, 8=Anulado. Cualquier otro: se devuelve crudo.
                'cstatus_tributario' => (function($cs){
                    switch ($cs) {
                        case '1': return 'Pendiente de envio';
                        case '4': return 'Aceptado';
                        case '5': return 'Aceptado con observaciones';
                        case '6': return 'Error';
                        case '8': return 'Anulado';
                        default:  return $cs;
                    }
                })(strval($f[7] ?? '')),
                'pdf' => $pdf,
                'xml' => $xml,
                'zip' => $zip,
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'DescargarArchivoPDF':
    case 'DescargarArchivoXML':
    case 'DescargarArchivoXMLCDR':
        // Los SPs devuelven 4 columnas: el binario + cdoc + cserie +
        // nnumero. El JS lee `obj[0].ipdf_datpos|contentxml|contentzipcdr`
        // junto a cdoc / cdoc_serie / cdoc_nro para nombrar el archivo.
        $input = getJsonInput();
        $sp = $m === 'DescargarArchivoPDF' ? 'webDatpos_DescargarArchivoPDF'
            : ($m === 'DescargarArchivoXML' ? 'webDatpos_DescargarArchivoXML' : 'webDatpos_DescargarArchivoXMLCDR');
        $key = $m === 'DescargarArchivoPDF' ? 'ipdf_datpos'
            : ($m === 'DescargarArchivoXML' ? 'contentxml' : 'contentzipcdr');
        $rows = Database::selectStoredTenant($sp, array(
            '@id_cbfact' => $input['codigo'] ?? '',
            '@ccod_cia'  => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                $key         => !empty($f[0]) ? base64_encode($f[0]) : '',
                'cdoc'       => strval($f[1] ?? ''),
                'cdoc_serie' => strval($f[2] ?? ''),
                'cdoc_nro'   => strval($f[3] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // === ConsultaDocumento ===
    case 'ConsultasDocumentoPricipal':
        $input = getJsonInput();
        // El JS legacy envia { consultadocumentos: [ {...} ] }; mantenemos
        // compatibilidad con la clave antigua "ConsultarDoc" por si algun
        // entry-point la sigue usando.
        $data = $input['consultadocumentos'][0] ?? ($input['ConsultarDoc'][0] ?? array());
        $rows = Database::selectStoredTenant('sp_consultasdocumentopricipal', array(
            '@ccod_cia'        => $o->ccod_empresa,
            '@cdoc'            => $data['cdoc'] ?? '',
            '@cdoc_serie'      => $data['cdoc_serie'] ?? '',
            '@cdoc_nro'        => $data['cdoc_nro'] ?? '',
            '@ccod_coa'        => $data['ccod_coa'] ?? ($data['id_coa'] ?? ''),
            '@n_fchDesde'      => $data['n_fchDesde'] ?? ($data['fchDesde'] ?? ''),
            '@n_fchHasta'      => $data['n_fchHasta'] ?? ($data['fchHasta'] ?? ''),
            '@ccod_tienda'     => $data['ccod_tienda'] ?? '',
            '@cusu_crea'       => $data['cusu_crea'] ?? ($data['ccod_usuario'] ?? ''),
            '@cobs'            => $data['cobs'] ?? '',
            '@cobser_variante' => $data['cobser_variante'] ?? '',
            '@Opcion'          => $data['Opcion'] ?? 'TLista',
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $idDoc = strval($f[0] ?? '');
            $lst[] = array(
                'id_cbfact'       => $idDoc,
                // El JS legacy de ModalBuscarDoc lee `id_cbinve` desde el
                // objfactura cacheado; cuando la consulta es de
                // facturas (sin movimiento de inventario asociado en
                // este flujo) lo dejamos en '' para que el modal use
                // el SP de factura (sp_consultadatosdocref).
                'id_cbinve'       => '',
                'cdoc'            => strval($f[1] ?? ''),
                'cdoc_serie'      => strval($f[2] ?? ''),
                'cdoc_nro'        => strval($f[3] ?? ''),
                'cusu_crea'       => strval($f[4] ?? ''),
                'ccoa_dsc'        => strval($f[5] ?? ''),
                'cdsc_coa'        => strval($f[5] ?? ''),
                'ctelf'           => strval($f[6] ?? ''),
                'cdsc_tienda'     => strval($f[7] ?? ''),
                'ntotal'          => strval($f[8] ?? ''),
                'dfch_doc'        => strval($f[9] ?? ''),
                // FIX 73 / BUG 3.19: traducir codigo de estado a texto.
                // 'P' = Pendiente, 'A' = Anulado. Mantenemos los codigos
                // crudos en 'nestado' por compatibilidad con flujos
                // legacy que comparan contra 'P'/'A'.
                'cstatus'         => (function($cs){
                    if ($cs === 'P') return 'Pendiente';
                    if ($cs === 'A') return 'Anulado';
                    return $cs;
                })(strval($f[10] ?? '')),
                'nestado'         => strval($f[10] ?? ''),
                'cobs'            => strval($f[11] ?? ''),
                'ccod_articulo'   => strval($f[12] ?? ''),
                'cdsc_articulo'   => strval($f[13] ?? ''),
                'ncantidad'       => strval($f[14] ?? ''),
                'nprecio'         => strval($f[15] ?? ''),
                'ndescuento'      => strval($f[16] ?? ''),
                'nimpuesto'       => strval($f[17] ?? ''),
                'nimporte_bruto'  => strval($f[18] ?? ''),
                'nimporte_neto'   => strval($f[19] ?? ''),
                'cobser_variante' => strval($f[20] ?? ''),
                // Iconos para botones de DataTables.
                // - DocFact   -> abre el modal de detalle del documento
                //               (ModalBuscarDoc, definido en
                //               ConsultaDocumento5.js).
                // - impresion -> descarga / imprime el PDF.
                // - ArmarHtml -> vista previa armada en HTML.
                'DocFact'         => $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-file-text-o' title='Ver detalle' onclick='ModalBuscarDoc(this);'></i>" : '',
                'impresion'       => $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-print' title='Imprimir' onclick='Imprimir(this);'></i>" : '',
                'ArmarHtml'       => $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-eye' title='Vista previa' onclick='ArmarHtml(this);'></i>" : '',
                'DocRef'          => '',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'DatosAdicionales':
        // Llamada desde varias pantallas:
        //   - ConsultaFormaPago.js  -> { FormaPago: <filas> }, lee
        //     Efectivo/Tarjeta/NotaCredito/NotaDebito/ntotal.
        //   - ConsultaMargenUtilidadDia.js -> { Datos: <filas> }, lee
        //     nprecio/ncosto/n_margenUtilidad.
        // Se totalizan en PHP a partir del array de filas que envia el JS.
        $input = getJsonInput();
        $datos = $input['FormaPago'] ?? ($input['Datos'] ?? array());
        $ncantidad = 0; $ntotal = 0;
        $efectivo  = 0; $tarjeta = 0; $nc = 0; $nd = 0;
        $nprecio   = 0; $ncosto  = 0; $nmargen = 0;
        foreach ($datos as $item) {
            $ncantidad += floatval($item['ncantidad'] ?? 0);
            $ntotal    += floatval($item['ntotal'] ?? $item['costo_tot'] ?? 0);
            $efectivo  += floatval($item['Efectivo'] ?? 0);
            $tarjeta   += floatval($item['Tarjeta'] ?? 0);
            $nc        += floatval($item['NotaCredito'] ?? 0);
            $nd        += floatval($item['NotaDebito'] ?? 0);
            $nprecio   += floatval($item['nprecio'] ?? 0);
            $ncosto    += floatval($item['ncosto']  ?? 0);
            $nmargen   += floatval($item['n_margenUtilidad'] ?? 0);
        }
        jsonResponse(array('d' => array(
            'ncantidad'        => $ncantidad,
            'ntotal'           => number_format(round($ntotal,   2), 2, '.', ''),
            'Efectivo'         => number_format(round($efectivo, 2), 2, '.', ''),
            'Tarjeta'          => number_format(round($tarjeta,  2), 2, '.', ''),
            'NotaCredito'      => number_format(round($nc, 2),       2, '.', ''),
            'NotaDebito'       => number_format(round($nd, 2),       2, '.', ''),
            'nprecio'          => number_format(round($nprecio, 2),  2, '.', ''),
            'ncosto'           => number_format(round($ncosto,  2),  2, '.', ''),
            'n_margenUtilidad' => number_format(round($nmargen, 2),  2, '.', ''),
        )));
        break;

    case 'DatosAdicionales3':
        // Llamado desde Consulta Documento modo TDetallado para totalizar
        // las lineas (LnFactura). El JS solo lee response.d.ntotal.
        $input = getJsonInput();
        $datos = $input['FormaPago'] ?? ($input['Datos'] ?? array());
        if (is_array($datos) && !empty($datos)) {
            $ntotal = 0;
            foreach ($datos as $item) {
                $ntotal += floatval($item['nimporte_neto'] ?? $item['ntotal'] ?? 0);
            }
            jsonResponse(array('d' => array(
                'ntotal' => number_format(round($ntotal, 2), 2, '.', ''),
            )));
            break;
        }
        // Llamada legacy con id_cbfact -> devuelve las lineas LnCobranza
        $rows = Database::selectStoredTenant('sp_datosadicionales3', array(
            '@ccod_cia' => $o->ccod_empresa, '@id_cbfact' => $input['id_cbfact'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cnom_tarje' => strval($f[0] ?? ''), 'nmonto' => strval($f[1] ?? ''),
                'cnum_tarje' => strval($f[2] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    case 'ConsultaDatosDocRef':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultadatosdocref', array(
            '@ccod_cia' => $o->ccod_empresa, '@id_cbfact' => $input['id_cbfact'] ?? ''), $o);
        // El JS lee response.d[i].* directamente; devolvemos las 34 columnas
        // tal cual las emite el SP en sus alias [0]..[33].
        jsonResponse(array('d' => $rows));
        break;

    case 'ConsultaPDF':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultapdf', array(
            '@ccod_cia' => $o->ccod_empresa, '@id_cbfact' => $input['id_cbfact'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $b64 = (!empty($f[0])) ? base64_encode($f[0]) : '';
            // El JS legacy usa obj[0].impresion; mantenemos cpdf por
            // compatibilidad con consumidores existentes.
            $lst[] = array('cpdf' => $b64, 'impresion' => $b64);
        } jsonResponse(array('d' => $lst)); break;

    case 'ConsultaListImventarioPorId':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultalistimventarioporid', array(
            '@ccod_cia' => $o->ccod_empresa, '@id_cbfact' => $input['id_cbfact'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_articulo' => strval($f[0] ?? ''), 'ncantidad' => strval($f[1] ?? ''),
                'npre_uni' => strval($f[2] ?? ''), 'nimporte_neto' => strval($f[3] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    case 'CargarClienteFacturar':
        // FIX 73 / BUG 3.6: 'tableVisibleConsulClientes' DataTable
        // declara la columna { data: 'item' } y espera un radio cuyo
        // input[name=radiob] tenga como id el codigo del cliente
        // (Facturacion6.js -> PasaDatosCodCliente lee cells[0].lastChild.id).
        $rows = Database::selectStoredTenant('sp_cargarclientefacturar', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $codCoa = strval($f[0] ?? '');
            $radio  = "<input type='radio' name='radiob' id='" . $codCoa . "'>";
            $lst[] = array(
                'item'      => $radio,
                'cbx'       => $radio, // alias legacy por compatibilidad
                'id_coa'    => $codCoa,
                'ccod_coa'  => $codCoa,
                'cdsc_coa'  => strval($f[1] ?? ''),
                'cdoc_coa'  => strval($f[2] ?? ''),
                'cdirc_coa' => '',
                'ctip_doc'  => '',
            );
        } jsonResponse(array('d' => $lst)); break;

    // === ConsultaOperAlmacen ===
    case 'CargarAlmacenes':
        // Pobla el select de almacenes en ConsultaOperAlmacen
        $rows = Database::selectStoredTenant('sp_consultaalmacenes', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarTiposOperacionAlmacen':
        // Pobla el select de tipo de operación en ConsultaOperAlmacen
        $rows = Database::selectStoredTenant('sp_consultartiposoperacion', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_tipoper' => strval($f[2] ?? ''), 'cdsc_tipoper' => strval($f[3] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultaOperAlmacenPricipal':
        $input = getJsonInput();
        $data = $input['consultaoperalmacen'][0] ?? $input['ConsultarOper'][0] ?? array();
        $rows = Database::selectStoredTenant('sp_consultaoperalmacenpricipal', array(
            '@ccod_cia'      => $o->ccod_empresa,
            '@ccod_alm'      => $data['ccod_alm']      ?? '',
            '@fchDesde'      => $data['fchDesde']      ?? '',
            '@fchHasta'      => $data['fchHasta']      ?? '',
            '@ctipo'         => $data['ctipo']         ?? '',
            '@cserie'        => $data['cserie']        ?? '',
            '@nnumero'       => $data['nnumero']       ?? '',
            '@ccod_coa'      => $data['ccoa_dsc']      ?? '',
            '@cdsc_usuario'  => $data['cdsc_usuario']  ?? '',
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_cbinve'    => strval($f[0] ?? ''),
                'ctipo'        => strval($f[1] ?? ''),
                'cserie'       => strval($f[2] ?? ''),
                'nnumero'      => strval($f[3] ?? ''),
                'ntotal'       => strval($f[4] ?? ''),
                'dfecha'       => strval($f[5] ?? ''),
                'ccod_alm_ing' => strval($f[6] ?? ''),
                'cdsc_usuario' => strval($f[7] ?? ''),
                'ccoa_dsc'     => strval($f[8] ?? ''),
                'DocRef'       => strval($f[9] ?? ''),
                'DocFact'      => '<a href="#" onclick="ModalDocFac(this)" id="' . strval($f[0] ?? '') . '"><i class="fa fa-search"></i></a>',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultaListImventarioPorId':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultalistimventarioporid', array(
            '@ccod_cia' => $o->ccod_empresa,
            '@id_cbfact' => intval($input['id_cbfact'] ?? 0)), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_articulo'      => strval($f[0] ?? ''),
                'cdsc_articulo'      => strval($f[1] ?? ''),
                'csim_unidadmedida'  => strval($f[2] ?? ''),
                'ncantidad'          => strval($f[3] ?? ''),
                'ncosto'             => strval($f[4] ?? ''),
                'ncosto_tot'         => strval($f[5] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultaListArticulosPorId':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultalistarticulosporid', array(
            '@ccod_cia'  => $o->ccod_empresa,
            '@id_cbinv'  => intval($input['id_cbfact'] ?? 0)), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_articulo'  => strval($f[0] ?? ''),
                'cdsc_articulo'  => strval($f[1] ?? ''),
                'ncantidad'      => strval($f[2] ?? ''),
                'nprecio'        => strval($f[3] ?? ''),
                'nimpuesto'      => strval($f[4] ?? ''),
                'ndescuento'     => strval($f[5] ?? ''),
                'nimporte_neto'  => strval($f[6] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'DatosReferencia':
        // Endpoint usado por tres pantallas:
        //  * ConsultaOperAlmacen  -> pasa id_cbinve > 0 (movimiento de
        //    inventario). Usamos sp_datosreferencia (26 indices).
        //  * ConsultaDocumento    -> pasa id_cbfact > 0 y id_cbinve = ''.
        //  * ConsultaFormasPago   -> pasa id_cbfact > 0 y id_cbinve = ''.
        //    Para esos dos ultimos usamos sp_consultadatosdocref
        //    (34 indices, datos completos de la factura).
        $input = getJsonInput();
        $idCbinve = intval($input['id_cbinve'] ?? 0);
        $idCbfact = intval($input['id_cbfact'] ?? 0);
        if ($idCbinve === 0 && $idCbfact > 0) {
            $rows = Database::selectStoredTenant('sp_consultadatosdocref', array(
                '@ccod_cia'  => $o->ccod_empresa,
                '@id_cbfact' => $idCbfact), $o);
            if (empty($rows)) { jsonResponse(array('d' => false)); break; }
            $f = $rows[0];
            $row = array_fill(0, 34, '');
            for ($i = 0; $i <= 33; $i++) {
                $row[$i] = strval($f[$i] ?? '');
            }
            jsonResponse(array('d' => $row));
            break;
        }
        $rows = Database::selectStoredTenant('sp_datosreferencia', array(
            '@ccod_cia'   => $o->ccod_empresa,
            '@id_cbinve'  => $idCbinve > 0 ? $idCbinve : $idCbfact), $o);
        if (empty($rows)) { jsonResponse(array('d' => false)); break; }
        $f = $rows[0];
        // El JS accede por índice numérico: d[1], d[2], ... d[25]
        $row = array_fill(0, 26, '');
        $row[0]  = strval($f[0] ?? ''); // id_cbinve
        $row[1]  = strval($f[1] ?? ''); // cdoc
        $row[2]  = strval($f[2] ?? ''); // dfecha
        $row[3]  = strval($f[3] ?? ''); // ntotal
        $row[4]  = strval($f[4] ?? ''); // ccod_tiend
        $row[5]  = strval($f[5] ?? ''); // cdsc_tiend
        $row[6]  = strval($f[6] ?? ''); // ccod_alm
        $row[7]  = strval($f[7] ?? ''); // cdsc_alm
        $row[8]  = strval($f[8] ?? ''); // ccod_usuario
        $row[9]  = strval($f[9] ?? ''); // cdsc_usuario
        $row[10] = strval($f[10] ?? ''); // ccod_coa
        $row[11] = strval($f[11] ?? ''); // cdsc_coa
        $row[14] = strval($f[14] ?? ''); // ntotal_inve
        $row[15] = strval($f[15] ?? ''); // cdoc_inve
        $row[16] = strval($f[16] ?? ''); // dfecha_inve
        $row[17] = strval($f[17] ?? ''); // ccod_caja
        $row[18] = strval($f[18] ?? ''); // cdsc_caja
        $row[24] = strval($f[24] ?? ''); // ccod_usuario_fac
        $row[25] = strval($f[25] ?? ''); // cdsc_usuario_fac
        jsonResponse(array('d' => $row));
        break;

    // === ConsultasAlmacen ===
    case 'ConsultasAlmacenPrincipal':
        $input = getJsonInput();
        $familia = $input['familia'] ?? '';
        if ($familia === '%%%') $familia = '';
        $rows = Database::selectStoredTenant('webDatpos_consultasAlmacenPrincipal', array(
            '@ccod_articulo' => $input['codigo'] ?? '',
            '@cdsc_articulo' => $input['nombre'] ?? '',
            '@ccod_lin'      => $familia,
            '@Codalmacen'    => $input['almacen'] ?? '',
            '@CodCia'        => $o->ccod_empresa
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $ncantidad = floatval($f[4] ?? 0);
            $npre_costo = floatval($f[5] ?? 0);
            $lst[] = array(
                'ccod_articulo'     => strval($f[0] ?? ''),
                'cdsc_articulo'     => strval($f[1] ?? ''),
                'cdsc_unidadmedida' => strval($f[2] ?? ''),
                'cdsc_lin'          => strval($f[3] ?? ''),
                'ncantidad'         => $ncantidad,
                'npre_costo'        => $npre_costo,
                'costo_tot'         => round($ncantidad * $npre_costo, 2),
                'cigv'              => strval($f[7] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;
    case 'CargarArticuloSaldo':
        $input = getJsonInput();
        $saldo = $input['objSaldo'][0] ?? array();
        $ccod_lin = $saldo['cdsc_lin'] ?? '';
        if ($ccod_lin === '%%%') $ccod_lin = '';
        $rows = Database::selectStoredTenant('webDatpos_cargarArticuloSaldo', array(
            '@ccod_lin'   => $ccod_lin,
            '@Codalmacen' => $saldo['ccod_alm'] ?? '',
            '@CodCia'     => $o->ccod_empresa
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_articulo' => strval($f[0] ?? ''),
                'cdsc_articulo' => strval($f[1] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // === ConsultaArticulos ===
    // FIX 74 / BUG 2.17: el SP sp_consultararticulospricipal antes
    // devolvia 4 cols (resumen de ventas) pero ConsultaArticulos.js
    // espera el catalogo de articulos (7 cols). MODIFY_930 recrea el SP
    // como SELECT sobre Articulos+Familias+UnidadMedida con los filtros
    // CodArticulo/NomAticulo/TipArticulo/Tributos/Familia/UniMedida/Estado.
    case 'ConsultarArticulosPricipal':
        $input = getJsonInput();
        // El JS envia los filtros como propiedades planas, no dentro de un
        // array anidado. Aceptamos ambos formatos por compatibilidad.
        $data = $input['ConsultarArti'][0] ?? $input;
        $rows = Database::selectStoredTenant('sp_consultararticulospricipal', array(
            '@ccod_cia'     => $o->ccod_empresa,
            '@CodArticulo'  => $data['CodArticulo'] ?? '',
            '@NomAticulo'   => $data['NomAticulo']  ?? '',
            '@TipArticulo'  => $data['TipArticulo'] ?? '',
            '@Tributos'     => $data['Tributos']    ?? '',
            '@Familia'      => $data['Familia']     ?? '',
            '@UniMedida'    => $data['UniMedida']   ?? '',
            '@Estado'       => $data['Estado']      ?? '',
            '@id_articulo'  => $data['id_articulo'] ?? '',
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_articulo' => strval($f[0] ?? ''),
                'cdsc_articulo' => strval($f[1] ?? ''),
                'linea'         => strval($f[2] ?? ''),
                'uni_medi'      => strval($f[3] ?? ''),
                'ctip_articulo' => strval($f[4] ?? ''),
                'estado'        => strval($f[5] ?? ''),
                'cigv'          => strval($f[6] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;
    case 'CargarEstadisticasConsArti':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_cargarestadisticasconsakti', array(
            '@ccod_cia' => $o->ccod_empresa, '@id_articulo' => $input['id_articulo'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('total_venta' => strval($f[0] ?? ''), 'total_cantidad' => strval($f[1] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    case 'CargarArticulo':
        $rows = Database::selectStoredTenant('sp_cargararticuloconsulta', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_articulo' => strval($f[0] ?? ''), 'ccod_articulo' => strval($f[0] ?? ''), 'cdsc_articulo' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    // === ConsultaArticulosMasVendidos ===
    // El JS de ConsultaArticulosMasVendidos.js envia
    //   { ArticulosMasVendidos: [ { ccod_articulo, ccod_tienda,
    //     n_fchDesde, n_fchHasta, ccod_lin } ] }
    // El SP webDatpos_ConsultaArticulosMasVendidos agrupa
    // CbFactura+LnFactura+Articulos+Cajas y devuelve 6 columnas:
    //   [0] ccod_caja [1] cdsc_caja [2] ccod_lin
    //   [3] ccod_articulo [4] cdsc_articulo [5] ncantidad
    case 'ConsultaArticulosMasVendidos':
        $input = getJsonInput();
        $data = $input['ArticulosMasVendidos'][0]
             ?? ($input['Consultar'][0] ?? array());
        $rows = Database::selectStoredTenant('webDatpos_ConsultaArticulosMasVendidos', array(
            '@ccod_tienda'   => $data['ccod_tienda']   ?? '',
            '@ccod_articulo' => $data['ccod_articulo'] ?? '',
            '@ccod_lin'      => $data['ccod_lin']      ?? '',
            '@fchDesde'      => $data['n_fchDesde']    ?? ($data['fchDesde'] ?? ''),
            '@fchHasta'      => $data['n_fchHasta']    ?? ($data['fchHasta'] ?? ''),
            '@CodCia'        => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_caja'     => strval($f[0] ?? ''),
                'cdsc_caja'     => strval($f[1] ?? ''),
                'ccod_lin'      => strval($f[2] ?? ''),
                'ccod_articulo' => strval($f[3] ?? ''),
                'cdsc_articulo' => strval($f[4] ?? ''),
                'ncantidad'     => strval($f[5] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;
    case 'ConsultarArticulos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultararticulos_masvendidos', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_articulo' => strval($f[0] ?? ''), 'ccod_articulo' => strval($f[0] ?? ''), 'cdsc_articulo' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarFamilia':
        // sp_consultafamilias devuelve: id_lin, ccod_lin, cdsc_lin, cstatus, ccolor
        $rows = Database::selectStoredTenant('sp_consultafamilias', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_lin' => strval($f[1] ?? ''), 'cdsc_lin' => strval($f[2] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    // === ConsultaFormasPago ===
    case 'ConsultaFormasPagoPricipal':
        $input = getJsonInput();
        // El JS legacy envia { FormaPago: [ {...} ] }; mantenemos
        // compatibilidad con la clave antigua "ConsultarFP".
        $data = $input['FormaPago'][0] ?? ($input['ConsultarFP'][0] ?? array());
        $rows = Database::selectStoredTenant('sp_consultaformaspagop', array(
            '@ccod_cia'   => $o->ccod_empresa,
            '@cnom_tarje' => $data['cnom_tarje'] ?? '',
            '@ccod_coa'   => $data['ccod_coa'] ?? '',
            '@cdoc'       => $data['cdoc'] ?? '',
            '@cdoc_serie' => $data['cdoc_serie'] ?? '',
            '@cdoc_nro'   => $data['cdoc_nro'] ?? '',
            '@ccod_caja'  => $data['ccod_caja'] ?? '',
            '@fchDesde'   => $data['fchDesde'] ?? '',
            '@fchHasta'   => $data['fchHasta'] ?? '',
            '@cusu_crea'  => $data['cusu_crea'] ?? '',
            '@Opcion'     => $data['Opcion'] ?? 'TLista',
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $idDoc    = strval($f[0] ?? '');
            $idCobro  = strval($f[1] ?? '');
            $lst[] = array(
                'id_cbfact'    => $idDoc,
                'id_cbcajac'   => $idCobro,
                'id_cbcobr'    => $idCobro,
                'cdoc'         => strval($f[2] ?? ''),
                'cdoc_serie'   => strval($f[3] ?? ''),
                'cdoc_nro'     => strval($f[4] ?? ''),
                'cusu_crea'    => strval($f[5] ?? ''),
                'cdsc_usuario' => strval($f[6] ?? ''),
                'ccod_coa'     => strval($f[7] ?? ''),
                'cdsc_coa'     => strval($f[8] ?? ''),
                'Efectivo'     => strval($f[9]  ?? '0'),
                'Tarjeta'      => strval($f[10] ?? '0'),
                'NotaCredito'  => strval($f[11] ?? '0'),
                'NotaDebito'   => strval($f[12] ?? '0'),
                'ntotal'       => strval($f[13] ?? '0'),
                'nvuelto'      => strval($f[14] ?? '0'),
                'dfch_crea'    => strval($f[15] ?? ''),
                'dfch_doc'     => strval($f[15] ?? ''),
                'cnom_tarje'   => strval($f[16] ?? ''),
                'nmonto'       => strval($f[17] ?? '0'),
                'DocRef'       => strval($f[18] ?? ''),
                // Icono para boton "Ver detalle" en modo Lista. Abre
                // el modal completo de pago + documento
                // (ModalBuscarDoc, definido en ConsultaFormaPago.js).
                'DocFact'      => $idDoc !== '' ? "<i id='{$idDoc}' class='fa fa-eye' title='Ver detalle' onclick='ModalBuscarDoc(this);'></i>" : '',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultaListCobranzaId':
        $input = getJsonInput();
        // El JS envia id_cbcajac (id de cabecera de cobranza). Aceptamos
        // tambien id_cbcobr para compatibilidad con consumidores antiguos.
        $rows = Database::selectStoredTenant('sp_consultalistcobranzaid', array(
            '@ccod_cia'   => $o->ccod_empresa,
            '@id_cbcajac' => $input['id_cbcajac'] ?? '',
            '@id_cbcobr'  => $input['id_cbcobr']  ?? '',
        ), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'cnom_tarje' => strval($f[0] ?? ''),
                'nmonto'     => strval($f[1] ?? ''),
                'cnum_tarje' => strval($f[2] ?? ''),
                'cnum_opera' => strval($f[3] ?? ''),
            );
        } jsonResponse(array('d' => $lst)); break;

    case 'CargarNumeradorCobranza':
        $rows = Database::selectStoredTenant('sp_cargarnumeradorcobranza', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $tipo = strval($f[0] ?? '');
            $lst[] = array(
                'id_cbnumerador' => $tipo,
                'cdoc_tipo'      => $tipo,
                'cdsc_numer'     => strval($f[1] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;

    // === ConsultaListPrecio ===
    // El JS de ConsultaListPrecio.js envia
    //   { articulo: [ { ccod_cblistpre, ccod_articulo, cdsc_articulo,
    //     ccod_lin, ccod_unidadmedida } ] }
    // El SP webDatpos_ConsultaListPrecioPricipal devuelve 7 columnas
    // (ccod_cblistpre, cdsc_cblistpre, ccod_articulo, cdsc_articulo,
    //  cdsc_lin, csim_unidadmedida, npre_uni) en el orden que lee
    // DataTables.
    case 'ConsultaListPrecioPricipal':
        $input = getJsonInput();
        $data = $input['articulo'][0]
             ?? ($input['ConsultarLP'][0] ?? array());
        $rows = Database::selectStoredTenant('webDatpos_ConsultaListPrecioPricipal', array(
            '@ccod_cblistpre'    => $data['ccod_cblistpre']    ?? '',
            '@ccod_articulo'     => $data['ccod_articulo']     ?? '',
            '@cdsc_articulo'     => $data['cdsc_articulo']     ?? '',
            '@ccod_lin'          => $data['ccod_lin']          ?? '',
            '@ccod_unidadmedida' => $data['ccod_unidadmedida'] ?? '',
            '@CodCia'            => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_cblistpre'    => strval($f[0] ?? ''),
                'cdsc_cblistpre'    => strval($f[1] ?? ''),
                'ccod_articulo'     => strval($f[2] ?? ''),
                'cdsc_articulo'     => strval($f[3] ?? ''),
                'cdsc_lin'          => strval($f[4] ?? ''),
                'csim_unidadmedida' => strval($f[5] ?? ''),
                'npre_uni'          => strval($f[6] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;
    case 'CargarListPrecio':
        // sp_consultarlistaspreciosactivos (definido en 080) devuelve
        // (ccod_cblistpre, cdsc_cblistpre) de CbListaPrecio con cstatus='A'.
        // Republicamos los mismos valores bajo las claves legacy
        // (id_cblistpre / cdsc_listpre) por si algun otro JS las usa.
        $rows = Database::selectStoredTenant('sp_consultarlistaspreciosactivos',
            array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array();
        foreach ($rows as $f) {
            $cod = strval($f[0] ?? '');
            $dsc = strval($f[1] ?? '');
            $lst[] = array(
                'ccod_cblistpre' => $cod,
                'cdsc_cblistpre' => $dsc,
                'id_cblistpre'   => $cod,
                'cdsc_listpre'   => $dsc,
            );
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarArticuloListPrecio':
        // ConsultaListPrecio.php -> modal "Seleccione Articulo".
        // JS envia { objArticuloListPrecio: [ { ccod_cblistpre,
        //   ccod_articulo, cdsc_articulo, ccod_lin, ccod_unidadmedida } ] }.
        // El JS espera (cbx, ccod_articulo, cdsc_articulo) para pintar
        // un radio button + dos columnas en el DataTable del modal.
        $input = getJsonInput();
        $data  = $input['objArticuloListPrecio'][0]
              ?? ($input['articulo'][0] ?? array());
        $rows  = Database::selectStoredTenant('webDatpos_CargarArticuloListPrecio', array(
            '@ccod_cblistpre'    => $data['ccod_cblistpre']    ?? '',
            '@ccod_articulo'     => $data['ccod_articulo']     ?? '',
            '@cdsc_articulo'     => $data['cdsc_articulo']     ?? '',
            '@ccod_lin'          => $data['ccod_lin']          ?? '',
            '@ccod_unidadmedida' => $data['ccod_unidadmedida'] ?? '',
            '@CodCia'            => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cbx'           => '',
                'ccod_articulo' => strval($f[0] ?? ''),
                'cdsc_articulo' => strval($f[1] ?? ''),
                'id_articulo'   => strval($f[0] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarEstadisticasListPrecio':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_cargarestadisticaslistprecio', array(
            '@ccod_cia' => $o->ccod_empresa, '@id_cblistpre' => $input['id_cblistpre'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('total' => strval($f[0] ?? ''), 'cantidad' => strval($f[1] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    // === ConsultaMargenUtilidadDia ===
    case 'MargenUtilidadDiaPricipal':
        // El JS de ConsultaMargenUtilidadDia.js envia
        //   { MargenUtilidadDia: [ { ccod_tienda, ccod_caja,
        //     n_fchDesde, n_fchHasta } ] }
        // El SP webDatpos_MargenUtilidadDiaPricipal (recreado en
        // MODIFY_912_FIX_65) agrupa CbFactura por tienda/caja/dia y
        // devuelve 9 columnas en este orden:
        //   [0] ccod_tienda, [1] cdsc_tienda, [2] ccod_caja,
        //   [3] cdsc_caja, [4] nprecio (sum ntotal),
        //   [5] ncosto (sum costo), [6] n_margenUtilidad,
        //   [7] n_marUtiPorcenta, [8] dfch_crea.
        $input = getJsonInput();
        $data = $input['MargenUtilidadDia'][0] ?? ($input['Consultar'][0] ?? array());
        $rows = Database::selectStoredTenant('webDatpos_MargenUtilidadDiaPricipal', array(
            '@ccod_tienda' => $data['ccod_tienda'] ?? '',
            '@ccod_caja'   => $data['ccod_caja']   ?? '',
            '@fchDesde'    => $data['n_fchDesde']  ?? ($data['fchDesde'] ?? ''),
            '@fchHasta'    => $data['n_fchHasta']  ?? ($data['fchHasta'] ?? ''),
            '@CodCia'      => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_tienda'      => strval($f[0] ?? ''),
                'cdsc_tienda'      => strval($f[1] ?? ''),
                'ccod_caja'        => strval($f[2] ?? ''),
                'cdsc_caja'        => strval($f[3] ?? ''),
                'nprecio'          => strval($f[4] ?? ''),
                'ncosto'           => strval($f[5] ?? ''),
                'n_margenUtilidad' => strval($f[6] ?? ''),
                'n_marUtiPorcenta' => strval($f[7] ?? ''),
                'dfch_crea'        => strval($f[8] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // === ConsultaStockMinimo ===
    case 'ConsultaStockMinimoPrincipal':
        $input = getJsonInput(); $data = $input['Consultar'][0] ?? array();
        $rows = Database::selectStoredTenant('sp_consultastockminimoprincipal', array(
            '@ccod_cia' => $o->ccod_empresa, '@ccod_alm' => $data['ccod_alm'] ?? '',
            '@id_articulo' => $data['id_articulo'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_articulo' => strval($f[0] ?? ''), 'nstock' => strval($f[1] ?? ''),
                'nstock_min' => strval($f[2] ?? ''), 'cdsc_alm' => strval($f[3] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    case 'CargarArticuloSoloBienes':
        $rows = Database::selectStoredTenant('sp_cargararticulosolobienes', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_articulo' => strval($f[0] ?? ''), 'ccod_articulo' => strval($f[0] ?? ''), 'cdsc_articulo' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    // === Kardex (Kardex.php) ===
    // FIX 73 / BUG 2.20: el JS de Kardex.js llama
    // Kardex.aspx/ConsultaKardexPricipal (typo legacy) y espera
    // columnas ccod_tienda, ccod_alm, ccod_articulo, cdsc_articulo,
    // n_anio, n_mes, n_cantInicial, n_cantIngreso, n_cantSalisa, n_saldo.
    // Antes el SP sp_kardexprincipal no existia ("no funciona, no
    // muestra error visible"). Ahora se crea en MODIFY_924 y se
    // soportan ambos nombres de metodo (con y sin typo).
    case 'KardexPrincipal':
    case 'ConsultaKardexPricipal':
    case 'ConsultaKardexPrincipal':
        $input = getJsonInput(); $data = $input['Kardex'][0] ?? array();
        $rows = Database::selectStoredTenant('sp_kardexprincipal', array(
            '@ccod_cia' => $o->ccod_empresa,
            '@fchDesde' => $data['n_fchDesde'] ?? ($data['fchDesde'] ?? ''),
            '@fchHasta' => $data['n_fchHasta'] ?? ($data['fchHasta'] ?? ''),
            '@id_articulo' => $data['ccod_articulo'] ?? ($data['id_articulo'] ?? ''),
            '@ccod_alm' => $data['ccod_alm'] ?? ''
        ), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'ccod_tienda'   => strval($f[0] ?? ''),
                'ccod_alm'      => strval($f[1] ?? ''),
                'ccod_articulo' => strval($f[2] ?? ''),
                'cdsc_articulo' => strval($f[3] ?? ''),
                'n_anio'        => strval($f[4] ?? ''),
                'n_mes'         => strval($f[5] ?? ''),
                'n_cantInicial' => strval($f[6] ?? ''),
                'n_cantIngreso' => strval($f[7] ?? ''),
                'n_cantSalisa'  => strval($f[8] ?? ''),
                'n_saldo'       => strval($f[9] ?? ''),
            );
        } jsonResponse(array('d' => $lst)); break;

    // === MargenUtilidad por documento ===
    // El JS de MargenUtilidad.js envia
    //   { MargenUtilidad: [ { cdoc, cdoc_serie, cdoc_nro,
    //     n_fchDesde, n_fchHasta, ccoa_dsc } ] }
    // El SP webDatpos_MargenUtilidadPricipal devuelve 11 columnas en
    // este orden: cdoc, cdoc_serie, cdoc_nro, ccoa_dsc, nprecio,
    // ncosto, n_margenUtilidad, n_marUtiPorcenta, n_docRef, dfch_crea,
    // id_cbfact.
    case 'MargenUtilidadPrincipal':
    case 'MargenUtilidadPricipal':
        $input = getJsonInput();
        $data = $input['MargenUtilidad'][0]
             ?? ($input['Consultar'][0] ?? array());
        $rows = Database::selectStoredTenant('webDatpos_MargenUtilidadPricipal', array(
            '@cdoc'       => $data['cdoc']        ?? '',
            '@cdoc_serie' => $data['cdoc_serie']  ?? '',
            '@cdoc_nro'   => $data['cdoc_nro']    ?? '',
            '@fchDesde'   => $data['n_fchDesde']  ?? ($data['fchDesde'] ?? ''),
            '@fchHasta'   => $data['n_fchHasta']  ?? ($data['fchHasta'] ?? ''),
            '@ccoa_dsc'   => $data['ccoa_dsc']    ?? '',
            '@CodCia'     => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdoc'             => strval($f[0] ?? ''),
                'cdoc_serie'       => strval($f[1] ?? ''),
                'cdoc_nro'         => strval($f[2] ?? ''),
                'ccoa_dsc'         => strval($f[3] ?? ''),
                'nprecio'          => strval($f[4] ?? ''),
                'ncosto'           => strval($f[5] ?? ''),
                'n_margenUtilidad' => strval($f[6] ?? ''),
                'n_marUtiPorcenta' => strval($f[7] ?? ''),
                'n_docRef'         => strval($f[8] ?? ''),
                'dfch_crea'        => strval($f[9] ?? ''),
                'id_cbfact'        => strval($f[10] ?? ''),
                'cbx'              => '',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // MargenUtilidad → detalle por documento (cabecera)
    case 'ConsultarMargenUtilidadArticuloDatos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_ConsultarMargenUtilidadArticuloDatos', array(
            '@cdoc'       => $input['cdoc']       ?? '',
            '@cdoc_serie' => $input['cdoc_serie'] ?? '',
            '@cdoc_nro'   => $input['cdoc_nro']   ?? '',
            '@CodCia'     => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_tienda'  => strval($f[0] ?? ''),
                'cdsc_tienda'  => strval($f[1] ?? ''),
                'ccod_caja'    => strval($f[2] ?? ''),
                'cdsc_caja'    => strval($f[3] ?? ''),
                'cusu_crea'    => strval($f[4] ?? ''),
                'cdsc_usuario' => strval($f[5] ?? ''),
                'ccod_coa'     => strval($f[6] ?? ''),
                'ccoa_dsc'     => strval($f[7] ?? ''),
                'n_tipoOper'   => strval($f[8] ?? ''),
                'n_serie'      => strval($f[9] ?? ''),
                'n_numero'     => strval($f[10] ?? ''),
                'ccod_alm'     => strval($f[11] ?? ''),
                'cdsc_alm'     => strval($f[12] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // MargenUtilidad → detalle por documento (lineas)
    case 'ConsultarMargenUtilidadArticulo':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_ConsultarMargenUtilidadArticulo', array(
            '@cdoc'       => $input['cdoc']       ?? '',
            '@cdoc_serie' => $input['cdoc_serie'] ?? '',
            '@cdoc_nro'   => $input['cdoc_nro']   ?? '',
            '@CodCia'     => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_articulo'    => strval($f[0] ?? ''),
                'cdsc_articulo'    => strval($f[1] ?? ''),
                'ncantidad'        => strval($f[2] ?? ''),
                'nprecio'          => strval($f[3] ?? ''),
                'ncosto'           => strval($f[4] ?? ''),
                'n_margenUtilidad' => strval($f[5] ?? ''),
                'n_marUtiPorcenta' => strval($f[6] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // === Catch-all for CargarCliente, CargarListaUsuario etc ===
    case 'CargarCliente':
        $rows = Database::selectStoredTenant('sp_cargarcliente', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_coa' => strval($f[0] ?? ''), 'ccod_coa' => strval($f[0] ?? ''), 'cdsc_coa' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarListaUsuario':
        $rows = Database::selectStoredTenant('sp_cargarlistausuario', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'ccod_usuario' => strval($f[0] ?? ''), 'cdsc_usuario' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    default: jsonResponse(array('d' => array()));
}
?>
