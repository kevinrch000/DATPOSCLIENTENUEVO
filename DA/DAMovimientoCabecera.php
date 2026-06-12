<?php
/**
 * DatPOS - Data Access: MovimientoCabecera (Ventas/Facturación)
 * Reemplaza: DA/DAMovimientoCabecera.vb
 * 
 * SPs usados (parámetros verificados contra BD):
 *   - sp_insertarmovimientocabeceranew
 *   - sp_insertarmovimientodetalle
 *   - sp_insertarcobranzacabecera
 *   - sp_insertarcobranzadetalle
 *   - sp_actualizarnumeradorcobranza
 *   - sp_validarfacturacion
 *   - sp_validaralfacturar
 *   - sp_consultarsunatfactura
 *   - sp_consultarsunatfacturadetalle
 *   - sp_consultardocumentocabecera
 *   - sp_consultardocumentodetalle
 *   - sp_consultardocumentocobranza
 *   - InsertarNotaCredito
 */

require_once __DIR__ . '/../config/database.php';

class DAMovimientoCabecera
{
    private function getTenantConnection($objConex) {
        return Database::getTenantConnection($objConex);
    }

    public function ValidarFacturacion($objConex)
    {
        $conn = $this->getTenantConnection($objConex);
        if (!$conn) return "Error de conexion";

        $sql = "DECLARE @resp NVARCHAR(256);\n"
             . "EXEC sp_validarfacturacion @CodCia = ?, @ccod_usuario = ?, @resp = @resp OUTPUT;\n"
             . "SELECT @resp AS resp;";
        $params = array(
            $objConex->ccod_empresa,
            $objConex->ccod_usuario
        );

        $stmt = sqlsrv_query($conn, $sql, $params);
        $result = "";
        
        if ($stmt) {
            sqlsrv_next_result($stmt);
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result = $row['resp'];
            }
            sqlsrv_free_stmt($stmt);
        } else {
            $result = "Error en validacion";
        }

        sqlsrv_close($conn);
        return strtoupper(trim($result)) === 'OK' ? '' : $result;
    }

    public function ValidarAlFacturar($cdoc, $objConex)
    {
        $conn = $this->getTenantConnection($objConex);
        if (!$conn) return "Error de conexion";

        $sql = "DECLARE @resp NVARCHAR(256);\n"
             . "EXEC sp_validaralfacturar @CodCia = ?, @ccod_usuario = ?, @cdoc_tipo = ?, @resp = @resp OUTPUT;\n"
             . "SELECT @resp AS resp;";
        $params = array(
            $objConex->ccod_empresa,
            $objConex->ccod_usuario,
            $cdoc
        );

        $stmt = sqlsrv_query($conn, $sql, $params);
        $result = "";

        if ($stmt) {
            sqlsrv_next_result($stmt);
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result = $row['resp'];
            }
            sqlsrv_free_stmt($stmt);
        } else {
            $result = "Error en validacion";
        }

        sqlsrv_close($conn);
        return strtoupper(trim($result)) === 'OK' ? '' : $result;
    }

    public function ObtenerIGV($objConex)
    {
        return Database::selectStoredTenant('appDatpos_ObtenerIGV', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }

    public function InsertarMovimientoCabecera($objBE, $objBED, $objConex, $cantidad_bienes, $objCobranzaDetalle, $id_apertura)
    {
        $conn = $this->getTenantConnection($objConex);
        if (!$conn) {
            return array(false, "Error de conexion", 0, "");
        }

        $objreturn = array(false, "", 0, "");

        sqlsrv_begin_transaction($conn);

        try {
            // Patrón DECLARE/EXEC/SELECT idéntico a DAAlmacen.php para capturar OUTPUTs
            $sql1 = "DECLARE @id_cbfact INT;\n"
                  . "DECLARE @id_cbinve INT;\n"
                  . "DECLARE @fecha_emision NVARCHAR(16);\n"
                  . "DECLARE @documento NVARCHAR(16);\n"
                  . "EXEC sp_insertarmovimientocabeceranew "
                  . "@ccod_cia=?, @ccod_usuario=?, @ccod_tiend=?, @ccod_caja=?, "
                  . "@ccod_almacen=?, @cdoc=?, @ccod_coa=?, @nimpuesto=?, @nisc=?, "
                  . "@ndescuento=?, @ntotal=?, @nsubtotal=?, @nvuelto=?, @ntot_entreg=?, "
                  . "@cantidad_bienes=?, @id_turno=?, @costo=?, @cobs=?, "
                  . "@id_cbfact=@id_cbfact OUTPUT, @id_cbinve=@id_cbinve OUTPUT, "
                  . "@fecha_emision=@fecha_emision OUTPUT, @documento=@documento OUTPUT;\n"
                  . "SELECT @id_cbfact AS id_cbfact, @id_cbinve AS id_cbinve, @fecha_emision AS fecha_emision, @documento AS documento;";

            $params1 = array(
                $objConex->ccod_empresa,
                $objConex->ccod_usuario,
                $objConex->ccod_tiend,
                $objConex->ccod_caja,
                $objConex->ccod_almacen,
                $objBE->cdoc,
                ($objBE->ccod_coa !== '' && $objBE->ccod_coa !== null) ? $objBE->ccod_coa : null, // NULL si no hay cliente (boleta sin ID)
                floatval($objBE->nimpuesto),
                floatval($objBE->nisc),
                floatval($objBE->ndescuento),
                floatval($objBE->ntotal),
                floatval($objBE->nsubtotal),
                floatval($objBE->nvuelto),
                floatval($objBE->ntot_entreg),
                $cantidad_bienes,
                $id_apertura,
                floatval($objBE->costo),
                $objBE->cobs
            );

            $stmt1 = sqlsrv_query($conn, $sql1, $params1);
            if (!$stmt1) {
                $errs = sqlsrv_errors();
                $msg = isset($errs[0]['message']) ? $errs[0]['message'] : 'SQL error desconocido';
                error_log("DAMovimientoCabecera::InsertarMovimientoCabecera cabecera: " . print_r($errs, true));
                throw new Exception("Error insertando cabecera: " . $msg);
            }

            $id_cbfact = 0;
            $id_cbinve = 0;
            $fecha_emision = "";
            $documento = "";

            // Lectura robusta: el driver ODBC 17 puede posicionar el cursor
            // antes o después del result set del SELECT dependiendo de cuántos
            // tokens DML genera el SP. Intentamos en la posición actual y, si no
            // encontramos id_cbfact, avanzamos hasta encontrarlo.
            $rowCab = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC);
            if (!$rowCab || !isset($rowCab['id_cbfact'])) {
                while (sqlsrv_next_result($stmt1) !== false) {
                    $rowCab = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC);
                    if ($rowCab && isset($rowCab['id_cbfact'])) break;
                    $rowCab = null;
                }
            }
            if ($rowCab) {
                $id_cbfact = intval($rowCab['id_cbfact']);
                $id_cbinve = intval($rowCab['id_cbinve']);
                $fecha_emision = $rowCab['fecha_emision'];
                $documento = $rowCab['documento'];
            }
            sqlsrv_free_stmt($stmt1);

            if ($id_cbfact === 0) {
                throw new Exception("Error leyendo id_cbfact del SP (valor 0): la transaccion no puede continuar");
            }

            $objreturn[1] = $documento;
            $objreturn[2] = $id_cbfact;
            $objreturn[3] = $fecha_emision;

            for ($i = 0; $i < count($objBED); $i++) {
                // @respuesta es OUTPUT en el SP pero el VB no lo lee; usamos DECLARE local
                $sql2 = "DECLARE @respuesta NVARCHAR(16);\n"
                      . "EXEC sp_insertarmovimientodetalle "
                      . "@ccod_cia=?, @ccod_tiend=?, @id_articulo=?, @cdsc_articulo=?, "
                      . "@id_cbfact=?, @cdoc=?, @nprecio=?, @ncantidad=?, @nimporte_bruto=?, "
                      . "@nimpuesto=?, @nisc=?, @ndescuento=?, @nimporte_neto=?, @corden=?, "
                      . "@ccod_usuario=?, @id_cbinve=?, @ccod_almacen=?, @cobser_variante=?, "
                      . "@ctip_descn=?, @respuesta=@respuesta OUTPUT;";

                $params2 = array(
                    $objConex->ccod_empresa,
                    $objConex->ccod_tiend,
                    $objBED[$i]['id_articulo'],
                    $objBED[$i]['cdsc_articulo'],
                    $id_cbfact,
                    $objBE->cdoc,
                    floatval($objBED[$i]['nprecio']),
                    floatval($objBED[$i]['ncantidad']),
                    floatval($objBED[$i]['nimporte_bruto']),
                    floatval($objBED[$i]['nimpuesto']),
                    floatval($objBED[$i]['nisc']),
                    floatval($objBED[$i]['ndescuento']),
                    floatval($objBED[$i]['nimporte_neto']),
                    $i + 1,
                    $objConex->ccod_usuario,
                    $id_cbinve,
                    $objConex->ccod_almacen,
                    $objBED[$i]['cobser_variante'] ?? '',
                    $objBED[$i]['ctip_descn'] ?? ''
                );

                $stmt2 = sqlsrv_query($conn, $sql2, $params2);
                if (!$stmt2) {
                    $errs2 = sqlsrv_errors();
                    $msg2 = isset($errs2[0]['message']) ? $errs2[0]['message'] : 'SQL error desconocido';
                    error_log("DAMovimientoCabecera::InsertarMovimientoCabecera detalle[$i]: " . print_r($errs2, true));
                    throw new Exception("Error insertando detalle $i: " . $msg2);
                }
                sqlsrv_free_stmt($stmt2);
            }

            $sql3 = "DECLARE @id_cbcajac INT;\n"
                  . "EXEC sp_insertarcobranzacabecera "
                  . "@id_cbfact=?, @id_turno=?, @ccod_cia=?, @ccod_tiend=?, @ccod_caja=?, "
                  . "@ccod_usuario=?, @ntotal=?, @ntot_entreg=?, @nvuelto=?, "
                  . "@id_cbcajac=@id_cbcajac OUTPUT;\n"
                  . "SELECT @id_cbcajac AS id_cbcajac;";

            $params3 = array(
                $id_cbfact,
                $id_apertura,
                $objConex->ccod_empresa,
                $objConex->ccod_tiend,
                $objConex->ccod_caja,
                $objConex->ccod_usuario,
                floatval($objBE->ntotal),
                floatval($objBE->ntot_entreg),
                floatval($objBE->nvuelto)
            );

            $stmt3 = sqlsrv_query($conn, $sql3, $params3);
            if (!$stmt3) {
                $errs3 = sqlsrv_errors();
                $msg3 = isset($errs3[0]['message']) ? $errs3[0]['message'] : 'SQL error desconocido';
                error_log("DAMovimientoCabecera::InsertarMovimientoCabecera cobranza: " . print_r($errs3, true));
                throw new Exception("Error insertando cobranza: " . $msg3);
            }

            $id_cbcajac = 0;
            $rowCob = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
            if (!$rowCob || !isset($rowCob['id_cbcajac'])) {
                while (sqlsrv_next_result($stmt3) !== false) {
                    $rowCob = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC);
                    if ($rowCob && isset($rowCob['id_cbcajac'])) break;
                    $rowCob = null;
                }
            }
            if ($rowCob) {
                $id_cbcajac = intval($rowCob['id_cbcajac']);
            }
            sqlsrv_free_stmt($stmt3);

            for ($i = 0; $i < count($objCobranzaDetalle); $i++) {
                $sql4 = "EXEC sp_insertarcobranzadetalle 
                    @ccod_cia = ?, @id_cbcajac = ?, @id_cbfact = ?, @ccod_tiend = ?,
                    @nmonto = ?, @cnum_opera = ?, @cnum_tarje = ?, @cnom_tarje = ?,
                    @id_cbfactNC = ?, @ccod_usuario = ?, @ccod_caja = ?";

                $params4 = array(
                    $objConex->ccod_empresa,
                    $id_cbcajac,
                    $id_cbfact,
                    $objConex->ccod_tiend,
                    floatval($objCobranzaDetalle[$i]['nmonto']),
                    $objCobranzaDetalle[$i]['cnum_opera'] ?? '',
                    $objCobranzaDetalle[$i]['cnum_tarje'] ?? '',
                    $objCobranzaDetalle[$i]['cnom_tarje'] ?? '',
                    $objCobranzaDetalle[$i]['id_cbfact'] ?? 0,
                    $objConex->ccod_usuario,
                    $objConex->ccod_caja
                );

                $stmt4 = sqlsrv_query($conn, $sql4, $params4);
                if (!$stmt4) {
                    $errs4 = sqlsrv_errors();
                    $msg4 = isset($errs4[0]['message']) ? $errs4[0]['message'] : 'SQL error desconocido';
                    error_log("DAMovimientoCabecera::InsertarMovimientoCabecera detalle_cobranza[$i]: " . print_r($errs4, true));
                    throw new Exception("Error insertando detalle cobranza $i: " . $msg4);
                }
                sqlsrv_free_stmt($stmt4);
            }

            $sql5 = "EXEC sp_actualizarnumeradorcobranza 
                @ccod_cia = ?, @ccod_caja = ?, @ccod_usuario = ?";

            $params5 = array(
                $objConex->ccod_empresa,
                $objConex->ccod_caja,
                $objConex->ccod_usuario
            );

            $stmt5 = sqlsrv_query($conn, $sql5, $params5);
            if (!$stmt5) {
                throw new Exception("Error actualizando numerador");
            }
            sqlsrv_free_stmt($stmt5);

            sqlsrv_commit($conn);
            $objreturn[0] = true;

        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            $objreturn[0] = false;
            $objreturn[1] = $e->getMessage();
        }

        sqlsrv_close($conn);
        return $objreturn;
    }

    /**
     * Consultar factura SUNAT (12 parámetros OUTPUT en VB).
     * Patrón DECLARE/EXEC/SELECT para capturar los OUTPUTs como una fila.
     */
    public function ConsultarSunatFactura($id, $objConex)
    {
        $conn = $this->getTenantConnection($objConex);
        if (!$conn) return array();

        $sql = "DECLARE @cliente_tipo_de_documento NVARCHAR(16);\n"
             . "DECLARE @serie NVARCHAR(16);\n"
             . "DECLARE @numero NVARCHAR(16);\n"
             . "DECLARE @cliente_numero_de_documento NVARCHAR(32);\n"
             . "DECLARE @cliente_denominacion NVARCHAR(32);\n"
             . "DECLARE @cliente_direccion NVARCHAR(32);\n"
             . "DECLARE @fecha_de_emision NVARCHAR(32);\n"
             . "DECLARE @fecha_de_vencimiento NVARCHAR(32);\n"
             . "DECLARE @porcentaje_de_igv NVARCHAR(32);\n"
             . "DECLARE @total NVARCHAR(32);\n"
             . "DECLARE @total_igv NVARCHAR(32);\n"
             . "DECLARE @total_gravada NVARCHAR(32);\n"
             . "EXEC sp_consultarsunatfactura @CodCia=?, @id_fact=?, "
             . "@cliente_tipo_de_documento=@cliente_tipo_de_documento OUTPUT, "
             . "@serie=@serie OUTPUT, @numero=@numero OUTPUT, "
             . "@cliente_numero_de_documento=@cliente_numero_de_documento OUTPUT, "
             . "@cliente_denominacion=@cliente_denominacion OUTPUT, "
             . "@cliente_direccion=@cliente_direccion OUTPUT, "
             . "@fecha_de_emision=@fecha_de_emision OUTPUT, "
             . "@fecha_de_vencimiento=@fecha_de_vencimiento OUTPUT, "
             . "@porcentaje_de_igv=@porcentaje_de_igv OUTPUT, "
             . "@total=@total OUTPUT, @total_igv=@total_igv OUTPUT, @total_gravada=@total_gravada OUTPUT;\n"
             . "SELECT @cliente_tipo_de_documento AS cliente_tipo_de_documento, "
             . "@serie AS serie, @numero AS numero, "
             . "@cliente_numero_de_documento AS cliente_numero_de_documento, "
             . "@cliente_denominacion AS cliente_denominacion, "
             . "@cliente_direccion AS cliente_direccion, "
             . "@fecha_de_emision AS fecha_de_emision, "
             . "@fecha_de_vencimiento AS fecha_de_vencimiento, "
             . "@porcentaje_de_igv AS porcentaje_de_igv, "
             . "@total AS total, @total_igv AS total_igv, @total_gravada AS total_gravada;";

        $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $id));
        $result = array();
        if ($stmt) {
            sqlsrv_next_result($stmt);
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result = $row;
            }
            sqlsrv_free_stmt($stmt);
        }
        sqlsrv_close($conn);
        return $result;
    }

    public function ConsultarSunatFacturaDetalle($id, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarsunatfacturadetalle', array(
            '@CodCia' => $objConex->ccod_empresa,
            '@id_fact' => $id
        ), $objConex);
    }

    public function ConsultarDocumentoCabecera($id, $objConex)
    {
        return Database::selectStoredTenant('sp_consultardocumentocabecera', array(
            '@id_cbfact' => $id
        ), $objConex);
    }

    public function ConsultarDocumentoDetalle($id, $objConex)
    {
        return Database::selectStoredTenant('sp_consultardocumentodetalle', array(
            '@id_cbfact' => $id
        ), $objConex);
    }

    public function ConsultarDocumentoCobranza($id, $objConex)
    {
        return Database::selectStoredTenant('sp_consultardocumentocobranza', array(
            '@id_cbfact' => $id
        ), $objConex);
    }

    public function InsertarNotaCredito($id_cbfact, $cod_motivo, $nimp_aplicado, $cdsc_movito, $objConex)
    {
        return Database::selectStoredTenant('InsertarNotaCredito', array(
            '@id_cbfact' => $id_cbfact,
            '@cod_motivo' => $cod_motivo,
            '@nimp_aplicado' => $nimp_aplicado,
            '@cdsc_movito' => $cdsc_movito,
            '@ccod_usuario' => $objConex->ccod_usuario,
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }

    /**
     * ConsultaDocumentosSunat: en el VB original (DAMovimientoCabecera.vb líneas 8-19)
     * el cmd no tiene CommandText asignado (todos los parámetros están comentados);
     * efectivamente no ejecuta ningún SP. PENDIENTE: confirmar con admin BD si existe
     * un SP real para este flujo. Mientras tanto devuelve array vacío (mismo
     * comportamiento que el VB).
     */
    public function ConsultaDocumentosSunat($objconsulta, $obj)
    {
        return array();
    }

    public function AnularDocumento($id_cbfact, $motivo, $objConex)
    {
        return Database::selectStoredTenant('appDatpos_anulacion', array(
            '@id_cbfact' => $id_cbfact,
            '@motivo' => $motivo,
            '@ccod_usu' => $objConex->ccod_usuario,
            '@ccod_cia' => $objConex->ccod_empresa,
            '@ccod_tienda' => $objConex->ccod_tiend,
            '@ccod_almacen' => $objConex->ccod_almacen,
            '@ccod_caja' => $objConex->ccod_caja
        ), $objConex);
    }

    public function ActualizarStock($ccod_cia, $ccod_alm, $ccod_articulo, $ncantidad, $ncosto, $signo, $objConex)
    {
        return Database::selectStoredTenant('_stock_actualizar', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_alm' => $ccod_alm,
            '@ccod_articulo' => $ccod_articulo,
            '@ncantidad' => $ncantidad,
            '@ncosto' => $ncosto,
            '@signo' => $signo
        ), $objConex);
    }

    public function ValidarStockArticulos($ccod_cia, $ccod_alm, $producto, $objConex)
    {
        return Database::selectStoredTenant('appDatpos_validarStockArticulos', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_alm' => $ccod_alm,
            '@producto' => $producto
        ), $objConex);
    }
}
?>