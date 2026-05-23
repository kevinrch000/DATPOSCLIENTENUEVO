<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEArticulo.php';
require_once __DIR__ . '/../BE/BEVariantes.php';

class DAArticulo {

    public function consultarArticulos($objConex) {
        return Database::selectStoredTenant(
            'sp_consultararticulos',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function consultarArticulo($codigo, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultararticulo',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo' => $codigo
            ),
            $objConex
        );
    }

    public function consultarVarianteArticulo($codigo, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarArticuloVariante',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo' => $codigo
            ),
            $objConex
        );
    }

    public function consultarDetalleVarianteArticulo($ccod_articulo, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarArticuloDetalleVariante',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_articulo' => $ccod_articulo
            ),
            $objConex
        );
    }

    public function consultarArticulosActivos($objConex) {
        return Database::selectStoredTenant(
            'sp_consultararticulosactivos',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function consultarArticulosTodos($texto, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultararticulotodos',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@texto' => $texto,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_almacen' => $objConex->ccod_almacen
            ),
            $objConex
        );
    }

    public function consultarArticulosCategoria($codigo, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultararticulocategoria',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo' => $codigo,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_almacen' => $objConex->ccod_almacen
            ),
            $objConex
        );
    }

    public function lsConsultarArticulosCategoria($codigo, $ccod_cblistpre, $objConex) {
        return Database::selectStoredTenant(
            'sp_lsconsultararticulocategoria',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo' => $codigo,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_almacen' => $objConex->ccod_almacen,
                '@ccod_cblistpre' => $ccod_cblistpre
            ),
            $objConex
        );
    }

    public function consultarCostoArticulo($codigo, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarCostoArticulo',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo' => $codigo
            ),
            $objConex
        );
    }

    public function cargarFavoritos($objConex) {
        return Database::selectStoredTenant(
            'sp_consultarfavoritos',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_almacen' => $objConex->ccod_almacen
            ),
            $objConex
        );
    }

    public function lsCargarFavoritos($ccod_cblistpre, $objConex) {
        return Database::selectStoredTenant(
            'sp_lpconsultarfavoritos',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_almacen' => $objConex->ccod_almacen,
                '@ccod_cblistpre' => $ccod_cblistpre
            ),
            $objConex
        );
    }

    public function consultarCostosArticulos($objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarCostosArticulos',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function consultarArticulosSalida($almacen, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarArticulosSalida',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@almacen' => $almacen
            ),
            $objConex
        );
    }

    public function consultarArticulosConStock($almacen, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarArticulosConStock',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@almacen' => $almacen
            ),
            $objConex
        );
    }

    public function actualizarFavorito($id_articulo, $bprefer, $objConex) {
        return Database::executeStoredTenant(
            'sp_actualizarfavorito',
            array(
                '@id_articulo' => $id_articulo,
                '@bprefer' => $bprefer
            ),
            $objConex
        );
    }

    public function registrarPdf($id_cbfact, $pdf, $objConex) {
        return Database::executeStoredTenant(
            'sp_registrarpdf',
            array(
                '@id_cbfact' => $id_cbfact,
                '@pdf' => base64_decode($pdf)
            ),
            $objConex
        );
    }

    public function validarArticuloAlmacenSalida($ccod_articulo, $ccod_alm, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_validarArticuloAlmacenSalida',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_articulo' => $ccod_articulo,
                '@ccod_alm' => $ccod_alm
            ),
            $objConex
        );
    }

    public function validarArticulo($ccod_articulo, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_validarArticulo',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_articulo' => $ccod_articulo
            ),
            $objConex
        );
    }

    private function ejecutarVarianteSP($conn, $spName, $params) {
        $placeholders = array();
        $values = array();
        foreach ($params as $name => $def) {
            if (isset($def['direction']) && $def['direction'] === 'output') {
                $placeholders[] = "{$name} = {$name} OUTPUT";
            } else {
                $placeholders[] = "{$name} = ?";
                $values[] = $def['value'];
            }
        }

        $decl = "";
        $sel = array();
        foreach ($params as $name => $def) {
            if (isset($def['direction']) && $def['direction'] === 'output') {
                $decl .= "DECLARE {$name} NVARCHAR(100);\n";
                $sel[] = "{$name} AS [{$name}]";
            }
        }

        $sql = $decl . "EXEC {$spName} " . implode(", ", $placeholders);
        if (!empty($sel)) {
            $sql .= ";\nSELECT " . implode(", ", $sel) . ";";
        }

        $stmt = sqlsrv_query($conn, $sql, $values);
        $outputValues = array();
        if ($stmt && !empty($sel)) {
            sqlsrv_next_result($stmt);
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                foreach ($row as $key => $val) {
                    $outputValues[$key] = $val;
                }
            }
        }
        if ($stmt) sqlsrv_free_stmt($stmt);
        return $outputValues;
    }

    public function insertarArticulo($objBE, $cabVariantes, $detVariantes, $objConex) {
        $connStr = Database::buildTenantConnectionString($objConex);
        $conn = sqlsrv_connect($connStr['server'], $connStr['connectionInfo']);
        if (!$conn) return array(false, 'Error de conexión', '', '');

        sqlsrv_begin_transaction($conn);
        try {
            $imageBytes = !empty($objBE->iimage) ? base64_decode($objBE->iimage) : '';

            $sql = "EXEC webDatpos_insertar_Articulo @ccod_empresa=?, @ccod_articulo=?, @cdsc_articulo=?, @ccod_lin=?, @ccod_unidadmedida=?, @cstatus=?, @ctip_articulo=?, @cigv=?, @cisc=?, @ccod_usuario=?, @iimage=?, @ccod_artSunat=?, @nstock_max=?, @nstock_min=?, @ctipo_isc=?, @nporcentaje_isc=?, @nmonto_isc=?";
            $params = array(
                $objConex->ccod_empresa, $objBE->ccod_articulo, $objBE->cdsc_articulo,
                $objBE->ccod_lin, $objBE->uni_medi, $objBE->cstatus,
                $objBE->ctip_articulo, $objBE->cigv, $objBE->cisc,
                $objConex->ccod_usuario, $imageBytes, $objBE->ccod_artSunat,
                $objBE->nstock_max, $objBE->nstock_min,
                $objBE->ctipo_isc, $objBE->nporcentaje_isc, $objBE->nmonto_isc
            );

            $stmt = sqlsrv_query($conn, $sql, $params);
            if (!$stmt) throw new Exception('Error insertando artículo');

            $errorNumber = 'OK';
            $errorMessage = '';
            if (sqlsrv_has_rows($stmt)) {
                sqlsrv_fetch($stmt);
                $errorNumber = sqlsrv_get_field($stmt, 0);
                $errorMessage = sqlsrv_get_field($stmt, 1);
            }
            sqlsrv_free_stmt($stmt);

            $objreturn = array(true, $errorNumber, $errorMessage, '');

            for ($d = 0; $d < count($detVariantes); $d++) {
                if ($detVariantes[$d]->cstate == "E" && $detVariantes[$d]->id_cbvariante != "id_cbvariante" && $detVariantes[$d]->id_cbvariante != "id_lnvariante") {
                    $sqlDel = "EXEC webDatpos_eliminarLNVariante @ccod_cia=?, @id_lnvariante=?";
                    $stmtDel = sqlsrv_query($conn, $sqlDel, array($objConex->ccod_empresa, $detVariantes[$d]->id_lnvariante));
                    if (!$stmtDel) throw new Exception('Error eliminando LN variante');
                    sqlsrv_free_stmt($stmtDel);
                }
            }

            for ($c = 0; $c < count($cabVariantes); $c++) {
                if ($cabVariantes[$c]->cstate == "E" && $cabVariantes[$c]->id_cbvariante != "id_cbvariante") {
                    $sqlDel = "EXEC webDatpos_eliminarCbVariante @ccod_cia=?, @id_cbvariante=?";
                    $stmtDel = sqlsrv_query($conn, $sqlDel, array($objConex->ccod_empresa, $cabVariantes[$c]->id_cbvariante));
                    if (!$stmtDel) throw new Exception('Error eliminando Cb variante');
                    sqlsrv_free_stmt($stmtDel);
                }
            }

            for ($i = 0; $i < count($cabVariantes); $i++) {
                if (($cabVariantes[$i]->cstate == "N" || $cabVariantes[$i]->cstate == "M") && $cabVariantes[$i]->id_cbvariante == "id_cbvariante") {
                    $outParams = $this->ejecutarVarianteSP($conn, 'webDatpos_insertarCbVariante', array(
                        '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                        '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                        '@ccod_articulo' => array('value' => $objBE->ccod_articulo),
                        '@cdsc_variante' => array('value' => $cabVariantes[$i]->cdsc_variante),
                        '@id_cbvariante' => array('direction' => 'output'),
                        '@ErrorNumber' => array('direction' => 'output'),
                        '@ErrorMessage' => array('direction' => 'output'),
                        '@Error' => array('direction' => 'output')
                    ));

                    $id_cbvariante = isset($outParams['@id_cbvariante']) ? $outParams['@id_cbvariante'] : '';
                    $objreturn[1] = isset($outParams['@ErrorNumber']) ? $outParams['@ErrorNumber'] : $objreturn[1];
                    $objreturn[2] = isset($outParams['@ErrorMessage']) ? $outParams['@ErrorMessage'] : $objreturn[2];
                    $objreturn[3] = isset($outParams['@Error']) ? $outParams['@Error'] : $objreturn[3];

                    for ($j = 0; $j < count($detVariantes); $j++) {
                        if (($detVariantes[$j]->cstate == "M" || $detVariantes[$j]->cstate == "N") && $cabVariantes[$i]->id_cbvariante == "id_cbvariante" && $cabVariantes[$i]->cdsc_variante == $detVariantes[$j]->cdsc_variante) {
                            $outParams2 = $this->ejecutarVarianteSP($conn, 'webDatpos_insertarLNVariante', array(
                                '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                                '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                                '@cdsc_lnvariante' => array('value' => $detVariantes[$j]->cdsc_lnvariante),
                                '@id_cbvariante' => array('value' => $id_cbvariante),
                                '@ErrorNumber' => array('direction' => 'output'),
                                '@ErrorMessage' => array('direction' => 'output'),
                                '@Error' => array('direction' => 'output')
                            ));
                            $objreturn[1] = isset($outParams2['@ErrorNumber']) ? $outParams2['@ErrorNumber'] : $objreturn[1];
                            $objreturn[2] = isset($outParams2['@ErrorMessage']) ? $outParams2['@ErrorMessage'] : $objreturn[2];
                            $objreturn[3] = isset($outParams2['@Error']) ? $outParams2['@Error'] : $objreturn[3];
                        }
                    }
                } elseif ($cabVariantes[$i]->cstate == "M" && $cabVariantes[$i]->id_cbvariante != "id_cbvariante") {
                    $outParams = $this->ejecutarVarianteSP($conn, 'webDatpos_editarCbVariante', array(
                        '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                        '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                        '@ccod_articulo' => array('value' => $objBE->ccod_articulo),
                        '@cdsc_variante' => array('value' => $cabVariantes[$i]->cdsc_variante),
                        '@id_cbvariante' => array('value' => $cabVariantes[$i]->id_cbvariante),
                        '@ErrorNumber' => array('direction' => 'output'),
                        '@ErrorMessage' => array('direction' => 'output'),
                        '@Error' => array('direction' => 'output')
                    ));
                    $objreturn[1] = isset($outParams['@ErrorNumber']) ? $outParams['@ErrorNumber'] : $objreturn[1];
                    $objreturn[2] = isset($outParams['@ErrorMessage']) ? $outParams['@ErrorMessage'] : $objreturn[2];
                    $objreturn[3] = isset($outParams['@Error']) ? $outParams['@Error'] : $objreturn[3];

                    for ($j = 0; $j < count($detVariantes); $j++) {
                        if ($detVariantes[$j]->cstate == "M") {
                            if ($cabVariantes[$i]->cdsc_variante == $detVariantes[$j]->cdsc_variante && $cabVariantes[$i]->cstate == "M" && $detVariantes[$j]->id_cbvariante != "id_cbvariante") {
                                $outParams2 = $this->ejecutarVarianteSP($conn, 'webDatpos_editarLNVariante', array(
                                    '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                                    '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                                    '@cdsc_lnvariante' => array('value' => $detVariantes[$j]->cdsc_lnvariante),
                                    '@id_cbvariante' => array('value' => $cabVariantes[$i]->id_cbvariante),
                                    '@id_lnvariante' => array('value' => $detVariantes[$j]->id_lnvariante),
                                    '@ErrorNumber' => array('direction' => 'output'),
                                    '@ErrorMessage' => array('direction' => 'output'),
                                    '@Error' => array('direction' => 'output')
                                ));
                                $objreturn[1] = isset($outParams2['@ErrorNumber']) ? $outParams2['@ErrorNumber'] : $objreturn[1];
                                $objreturn[2] = isset($outParams2['@ErrorMessage']) ? $outParams2['@ErrorMessage'] : $objreturn[2];
                                $objreturn[3] = isset($outParams2['@Error']) ? $outParams2['@Error'] : $objreturn[3];
                            }
                        }
                    }
                }
            }

            for ($p = 0; $p < count($cabVariantes); $p++) {
                for ($o = 0; $o < count($detVariantes); $o++) {
                    if ($cabVariantes[$p]->cdsc_variante == $detVariantes[$o]->cdsc_variante && $cabVariantes[$p]->cstate == "A" && ($detVariantes[$o]->cstate == "N" || $detVariantes[$o]->cstate == "M") && $detVariantes[$o]->id_cbvariante == "id_cbvariante") {
                        $outParams3 = $this->ejecutarVarianteSP($conn, 'webDatpos_insertarLNVariante', array(
                            '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                            '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                            '@cdsc_lnvariante' => array('value' => $detVariantes[$o]->cdsc_lnvariante),
                            '@id_cbvariante' => array('value' => $cabVariantes[$p]->id_cbvariante),
                            '@ErrorNumber' => array('direction' => 'output'),
                            '@ErrorMessage' => array('direction' => 'output'),
                            '@Error' => array('direction' => 'output')
                        ));
                        $objreturn[1] = isset($outParams3['@ErrorNumber']) ? $outParams3['@ErrorNumber'] : $objreturn[1];
                        $objreturn[2] = isset($outParams3['@ErrorMessage']) ? $outParams3['@ErrorMessage'] : $objreturn[2];
                        $objreturn[3] = isset($outParams3['@Error']) ? $outParams3['@Error'] : $objreturn[3];
                    } elseif ($cabVariantes[$p]->cdsc_variante == $detVariantes[$o]->cdsc_variante && $detVariantes[$o]->cstate == "M" && $cabVariantes[$p]->cstate == "A" && $detVariantes[$o]->id_cbvariante != "id_cbvariante") {
                        $outParams4 = $this->ejecutarVarianteSP($conn, 'webDatpos_editarLNVariante', array(
                            '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                            '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                            '@cdsc_lnvariante' => array('value' => $detVariantes[$o]->cdsc_lnvariante),
                            '@id_cbvariante' => array('value' => $cabVariantes[$p]->id_cbvariante),
                            '@id_lnvariante' => array('value' => $detVariantes[$o]->id_lnvariante),
                            '@ErrorNumber' => array('direction' => 'output'),
                            '@ErrorMessage' => array('direction' => 'output'),
                            '@Error' => array('direction' => 'output')
                        ));
                        $objreturn[1] = isset($outParams4['@ErrorNumber']) ? $outParams4['@ErrorNumber'] : $objreturn[1];
                        $objreturn[2] = isset($outParams4['@ErrorMessage']) ? $outParams4['@ErrorMessage'] : $objreturn[2];
                        $objreturn[3] = isset($outParams4['@Error']) ? $outParams4['@Error'] : $objreturn[3];
                    }
                }
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            return $objreturn;
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            return array(false, 'Error', $e->getMessage(), '');
        }
    }

    public function editarArticulo($objBE, $cabVariantes, $detVariantes, $objConex) {
        $connStr = Database::buildTenantConnectionString($objConex);
        $conn = sqlsrv_connect($connStr['server'], $connStr['connectionInfo']);
        if (!$conn) return array(false, 'Error de conexión', '', '');

        sqlsrv_begin_transaction($conn);
        try {
            $imageBytes = !empty($objBE->iimage) ? base64_decode($objBE->iimage) : '';

            $sql = "EXEC webDatpos_editarArticulo @ccod_empresa=?, @ccod_articulo=?, @cdsc_articulo=?, @ccod_lin=?, @ccod_unidadmedida=?, @cstatus=?, @ctip_articulo=?, @cigv=?, @cisc=?, @ccod_usuario=?, @iimage=?, @ccod_artSunat=?, @nstock_max=?, @nstock_min=?, @ctipo_isc=?, @nporcentaje_isc=?, @nmonto_isc=?";
            $params = array(
                $objConex->ccod_empresa, $objBE->ccod_articulo, $objBE->cdsc_articulo,
                $objBE->ccod_lin, $objBE->uni_medi, $objBE->cstatus,
                $objBE->ctip_articulo, $objBE->cigv, $objBE->cisc,
                $objConex->ccod_usuario, $imageBytes, $objBE->ccod_artSunat,
                $objBE->nstock_max, $objBE->nstock_min,
                $objBE->ctipo_isc, $objBE->nporcentaje_isc, $objBE->nmonto_isc
            );

            $stmt = sqlsrv_query($conn, $sql, $params);
            if (!$stmt) throw new Exception('Error editando artículo');

            $errorNumber = 'OK';
            if (sqlsrv_has_rows($stmt)) {
                sqlsrv_fetch($stmt);
                $errorNumber = sqlsrv_get_field($stmt, 0);
            }
            sqlsrv_free_stmt($stmt);

            $objreturn = array(true, $errorNumber, '', '');

            for ($d = 0; $d < count($detVariantes); $d++) {
                if ($detVariantes[$d]->cstate == "E") {
                    $sqlDel = "EXEC webDatpos_eliminarLNVariante @ccod_cia=?, @id_lnvariante=?";
                    $stmtDel = sqlsrv_query($conn, $sqlDel, array($objConex->ccod_empresa, $detVariantes[$d]->id_lnvariante));
                    if (!$stmtDel) throw new Exception('Error eliminando LN variante');
                    sqlsrv_free_stmt($stmtDel);
                }
            }

            for ($c = 0; $c < count($cabVariantes); $c++) {
                if ($cabVariantes[$c]->cstate == "E") {
                    $sqlDel = "EXEC webDatpos_eliminarCbVariante @ccod_cia=?, @id_cbvariante=?";
                    $stmtDel = sqlsrv_query($conn, $sqlDel, array($objConex->ccod_empresa, $cabVariantes[$c]->id_cbvariante));
                    if (!$stmtDel) throw new Exception('Error eliminando Cb variante');
                    sqlsrv_free_stmt($stmtDel);
                }
            }

            for ($i = 0; $i < count($cabVariantes); $i++) {
                if (($cabVariantes[$i]->cstate == "N" || $cabVariantes[$i]->cstate == "M") && $cabVariantes[$i]->id_cbvariante == "id_cbvariante") {
                    $outParams = $this->ejecutarVarianteSP($conn, 'webDatpos_insertarCbVariante', array(
                        '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                        '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                        '@ccod_articulo' => array('value' => $objBE->ccod_articulo),
                        '@cdsc_variante' => array('value' => $cabVariantes[$i]->cdsc_variante),
                        '@id_cbvariante' => array('direction' => 'output'),
                        '@ErrorNumber' => array('direction' => 'output'),
                        '@ErrorMessage' => array('direction' => 'output'),
                        '@Error' => array('direction' => 'output')
                    ));

                    $id_cbvariante = isset($outParams['@id_cbvariante']) ? $outParams['@id_cbvariante'] : '';
                    $objreturn[1] = isset($outParams['@ErrorNumber']) ? $outParams['@ErrorNumber'] : $objreturn[1];
                    $objreturn[2] = isset($outParams['@ErrorMessage']) ? $outParams['@ErrorMessage'] : $objreturn[2];
                    $objreturn[3] = isset($outParams['@Error']) ? $outParams['@Error'] : $objreturn[3];

                    for ($j = 0; $j < count($detVariantes); $j++) {
                        if (($detVariantes[$j]->cstate == "M" || $detVariantes[$j]->cstate == "N") && $cabVariantes[$i]->id_cbvariante == "id_cbvariante" && $cabVariantes[$i]->cdsc_variante == $detVariantes[$j]->cdsc_variante) {
                            $outParams2 = $this->ejecutarVarianteSP($conn, 'webDatpos_insertarLNVariante', array(
                                '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                                '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                                '@cdsc_lnvariante' => array('value' => $detVariantes[$j]->cdsc_lnvariante),
                                '@id_cbvariante' => array('value' => $id_cbvariante),
                                '@ErrorNumber' => array('direction' => 'output'),
                                '@ErrorMessage' => array('direction' => 'output'),
                                '@Error' => array('direction' => 'output')
                            ));
                            $objreturn[1] = isset($outParams2['@ErrorNumber']) ? $outParams2['@ErrorNumber'] : $objreturn[1];
                            $objreturn[2] = isset($outParams2['@ErrorMessage']) ? $outParams2['@ErrorMessage'] : $objreturn[2];
                            $objreturn[3] = isset($outParams2['@Error']) ? $outParams2['@Error'] : $objreturn[3];
                        }
                    }
                } elseif ($cabVariantes[$i]->cstate == "M" && $cabVariantes[$i]->id_cbvariante != "id_cbvariante") {
                    $outParams = $this->ejecutarVarianteSP($conn, 'webDatpos_editarCbVariante', array(
                        '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                        '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                        '@ccod_articulo' => array('value' => $objBE->ccod_articulo),
                        '@cdsc_variante' => array('value' => $cabVariantes[$i]->cdsc_variante),
                        '@id_cbvariante' => array('value' => $cabVariantes[$i]->id_cbvariante),
                        '@ErrorNumber' => array('direction' => 'output'),
                        '@ErrorMessage' => array('direction' => 'output'),
                        '@Error' => array('direction' => 'output')
                    ));
                    $objreturn[1] = isset($outParams['@ErrorNumber']) ? $outParams['@ErrorNumber'] : $objreturn[1];
                    $objreturn[2] = isset($outParams['@ErrorMessage']) ? $outParams['@ErrorMessage'] : $objreturn[2];
                    $objreturn[3] = isset($outParams['@Error']) ? $outParams['@Error'] : $objreturn[3];

                    for ($j = 0; $j < count($detVariantes); $j++) {
                        if (($detVariantes[$j]->cstate == "M" || $detVariantes[$j]->cstate == "N") && $cabVariantes[$i]->cdsc_variante == $detVariantes[$j]->cdsc_variante && $detVariantes[$j]->id_cbvariante != "id_cbvariante") {
                            $outParams2 = $this->ejecutarVarianteSP($conn, 'webDatpos_editarLNVariante', array(
                                '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                                '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                                '@cdsc_lnvariante' => array('value' => $detVariantes[$j]->cdsc_lnvariante),
                                '@id_cbvariante' => array('value' => $cabVariantes[$i]->id_cbvariante),
                                '@id_lnvariante' => array('value' => $detVariantes[$j]->id_lnvariante),
                                '@ErrorNumber' => array('direction' => 'output'),
                                '@ErrorMessage' => array('direction' => 'output'),
                                '@Error' => array('direction' => 'output')
                            ));
                            $objreturn[1] = isset($outParams2['@ErrorNumber']) ? $outParams2['@ErrorNumber'] : $objreturn[1];
                            $objreturn[2] = isset($outParams2['@ErrorMessage']) ? $outParams2['@ErrorMessage'] : $objreturn[2];
                            $objreturn[3] = isset($outParams2['@Error']) ? $outParams2['@Error'] : $objreturn[3];
                        }
                    }
                }
            }

            for ($p = 0; $p < count($cabVariantes); $p++) {
                for ($o = 0; $o < count($detVariantes); $o++) {
                    if ($cabVariantes[$p]->cdsc_variante == $detVariantes[$o]->cdsc_variante && ($cabVariantes[$p]->cstate == "A" || $cabVariantes[$p]->cstate == "M") && ($detVariantes[$o]->cstate == "N" || $detVariantes[$o]->cstate == "M") && $detVariantes[$o]->id_cbvariante == "id_cbvariante") {
                        $outParams3 = $this->ejecutarVarianteSP($conn, 'webDatpos_insertarLNVariante', array(
                            '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                            '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                            '@cdsc_lnvariante' => array('value' => $detVariantes[$o]->cdsc_lnvariante),
                            '@id_cbvariante' => array('value' => $cabVariantes[$p]->id_cbvariante),
                            '@ErrorNumber' => array('direction' => 'output'),
                            '@ErrorMessage' => array('direction' => 'output'),
                            '@Error' => array('direction' => 'output')
                        ));
                        $objreturn[1] = isset($outParams3['@ErrorNumber']) ? $outParams3['@ErrorNumber'] : $objreturn[1];
                        $objreturn[2] = isset($outParams3['@ErrorMessage']) ? $outParams3['@ErrorMessage'] : $objreturn[2];
                        $objreturn[3] = isset($outParams3['@Error']) ? $outParams3['@Error'] : $objreturn[3];
                    } elseif ($cabVariantes[$p]->cdsc_variante == $detVariantes[$o]->cdsc_variante && $detVariantes[$o]->cstate == "M" && $cabVariantes[$p]->cstate == "A" && $detVariantes[$o]->id_cbvariante != "id_cbvariante") {
                        $outParams4 = $this->ejecutarVarianteSP($conn, 'webDatpos_editarLNVariante', array(
                            '@ccod_cia' => array('value' => $objConex->ccod_empresa),
                            '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
                            '@cdsc_lnvariante' => array('value' => $detVariantes[$o]->cdsc_lnvariante),
                            '@id_cbvariante' => array('value' => $cabVariantes[$p]->id_cbvariante),
                            '@id_lnvariante' => array('value' => $detVariantes[$o]->id_lnvariante),
                            '@ErrorNumber' => array('direction' => 'output'),
                            '@ErrorMessage' => array('direction' => 'output'),
                            '@Error' => array('direction' => 'output')
                        ));
                        $objreturn[1] = isset($outParams4['@ErrorNumber']) ? $outParams4['@ErrorNumber'] : $objreturn[1];
                        $objreturn[2] = isset($outParams4['@ErrorMessage']) ? $outParams4['@ErrorMessage'] : $objreturn[2];
                        $objreturn[3] = isset($outParams4['@Error']) ? $outParams4['@Error'] : $objreturn[3];
                    }
                }
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            return $objreturn;
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            return array(false, 'Error', $e->getMessage(), '');
        }
    }

    public function eliminarArticulo($cabVariantes, $articulo, $objConex) {
        $connStr = Database::buildTenantConnectionString($objConex);
        $conn = sqlsrv_connect($connStr['server'], $connStr['connectionInfo']);
        if (!$conn) return array(false, 'Error de conexión', '', '');

        sqlsrv_begin_transaction($conn);
        try {
            for ($c = 0; $c < count($cabVariantes); $c++) {
                if ($cabVariantes[$c]->id_cbvariante != "id_cbvariante") {
                    $sqlDel = "EXEC webDatpos_eliminarCbVariante @ccod_cia=?, @id_cbvariante=?";
                    $stmtDel = sqlsrv_query($conn, $sqlDel, array($objConex->ccod_empresa, $cabVariantes[$c]->id_cbvariante));
                    if (!$stmtDel) throw new Exception('Error eliminando Cb variante');
                    sqlsrv_free_stmt($stmtDel);
                }
            }

            $sql = "EXEC sp_eliminararticulo @ccod_cia=?, @ccod_articulo=?";
            $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $articulo));

            $errorNumber = 'OK';
            $errorMessage = '';
            if ($stmt && sqlsrv_has_rows($stmt)) {
                sqlsrv_fetch($stmt);
                $errorNumber = sqlsrv_get_field($stmt, 0);
            }
            if ($stmt) sqlsrv_free_stmt($stmt);

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            return array(true, $errorNumber, $errorMessage, '');
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            return array(false, 'Error', $e->getMessage(), '');
        }
    }
}
?>
