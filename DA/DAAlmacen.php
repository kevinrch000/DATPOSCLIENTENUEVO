<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEAlmacen.php';
require_once __DIR__ . '/../BE/BENumerador.php';

class DAAlmacen {

    public function consultarAlmacenes($objConex) {
        return Database::selectStoredTenant(
            'sp_consultaalmacenes',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function consultarAlmacen($codigo, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultaalmacen',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo' => $codigo
            ),
            $objConex
        );
    }

    public function consultarAlmacenesActivos($objConex) {
        return Database::selectStoredTenant(
            'sp_consultaalmacenesactivos',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function consultarAlmEmpActivos($cod, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultaalmempactivos',
            array(
                '@ccod_tiend' => $cod,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }

    public function consultarCargarAlmacenesDisponibles($objConex) {
        return Database::selectStoredTenant(
            'sp_consultaalmacenesdispo',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function consultarNumerador($almacen, $objConex) {
        return Database::selectStoredTenant(
            'appDatpos_consultaNumeradorAlmacen',
            array(
                '@ccod_alm' => $almacen,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }

    public function consultarNumeradorSalida($almacen, $objConex) {
        return Database::selectStoredTenant(
            'appDatpos_consultaNumeradorSalida',
            array(
                '@ccod_alm' => $almacen,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }

    public function consultarTiendaAlmacenes($tienda, $objConex) {
        // SP usa @ccod_empresa (consistente con api/tienda_api.php)
        return Database::selectStoredTenant(
            'sp_consultartiendaalmacenes',
            array(
                '@ccod_empresa' => $objConex->ccod_empresa,
                '@ccod_tiend'   => $tienda
            ),
            $objConex
        );
    }

    public function asignarTiendaAlmacen($tienda, $almacen, $objConex) {
        return Database::executeStoredTenant(
            'sp_asignartiendaalmacen',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_tiend' => $tienda,
                '@ccod_alm' => $almacen
            ),
            $objConex
        );
    }

    public function limpiarTiendasAlmacen($cod, $objConex) {
        return Database::executeStoredTenant(
            'sp_limpiartiendasalmacen',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_tiend' => $cod
            ),
            $objConex
        );
    }

    public function eliminarAlmacen($cod, $objConex) {
        return Database::selectStoredTenant(
            'sp_eliminaralmacen',
            array(
                '@ccod_alm' => $cod,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }

    public function insertarAlmacen($objBE, $numeradores, $objConex) {
        $connStr = Database::buildTenantConnectionString($objConex);
        $conn = sqlsrv_connect($connStr['server'], $connStr['connectionInfo']);

        if (!$conn) {
            $errs = sqlsrv_errors();
            error_log('[insertarAlmacen] connect fallo: ' . print_r($errs, true));
            return array(false, 'Error de conexión', is_array($errs) ? ($errs[0]['message'] ?? '') : '', '');
        }

        // Algunos SPs hacen CONVERT implicito de strings con formato espanol; forzamos formato US.
        sqlsrv_query($conn, "SET LANGUAGE us_english; SET DATEFORMAT ymd;");

        sqlsrv_begin_transaction($conn);

        try {
            $sql = "DECLARE @ErrorNumber NVARCHAR(16);\nDECLARE @ErrorMessage NVARCHAR(100);\nDECLARE @id_ctalmac NVARCHAR(16);\n";
            $sql .= "EXEC webDatpos_insertarAlmacen @ccod_alm=?, @ccod_cia=?, @cdsc_alm=?, @cstatus=?, @ccod_usuario=?, @cdepartamento=?, @cprovincia=?, @cdistrito=?, @cdirc_almac=?, @curba_almac=?, @cubigeo=?, @ErrorNumber=@ErrorNumber OUTPUT, @ErrorMessage=@ErrorMessage OUTPUT, @id_ctalmac=@id_ctalmac OUTPUT;\n";
            $sql .= "SELECT @ErrorNumber AS ErrorNumber, @ErrorMessage AS ErrorMessage, @id_ctalmac AS id_ctalmac;";

            $params = array(
                strval($objBE->ccod_alm ?? ''),
                strval($objConex->ccod_empresa ?? ''),
                strval($objBE->cdsc_alm ?? ''),
                strval($objBE->cstatus ?? 'A'),
                strval($objConex->ccod_usuario ?? ''),
                strval($objBE->cdepartamento ?? ''),
                strval($objBE->cprovincia ?? ''),
                strval($objBE->cdistrito ?? ''),
                strval($objBE->cdirc_almac ?? ''),
                strval($objBE->curba_almac ?? ''),
                strval($objBE->cubigeo ?? '')
            );

            $stmt = sqlsrv_query($conn, $sql, $params);
            if (!$stmt) {
                $errs = sqlsrv_errors();
                $msg = is_array($errs) ? ($errs[0]['message'] ?? 'sqlsrv_query fallo') : 'sqlsrv_query fallo';
                error_log('[insertarAlmacen] ejec fallo: ' . print_r($errs, true) . ' params=' . print_r($params, true));
                throw new Exception($msg);
            }

            // Avanzar resultsets hasta encontrar la fila del SELECT @ErrorNumber...
            $errorNumber = 'OK';
            $errorMessage = '';
            $id_ctalmac = '';
            $row = $this->fetchFirstRow($stmt);
            if ($row !== null) {
                $errorNumber  = $row['ErrorNumber']  ?? 'OK';
                $errorMessage = $row['ErrorMessage'] ?? '';
                $id_ctalmac   = $row['id_ctalmac']   ?? '';
            }
            sqlsrv_free_stmt($stmt);

            // Si la cabecera falló, no insertar numeradores
            if ($errorNumber !== 'OK' && $errorNumber !== '') {
                sqlsrv_rollback($conn);
                sqlsrv_close($conn);
                return array(false, strval($errorNumber), strval($errorMessage), '');
            }

            $numerErrorNumber = '';
            foreach ($numeradores as $num) {
                $sql2 = "DECLARE @NumErrorNumber NVARCHAR(16);\nDECLARE @NumErrorMessage NVARCHAR(100);\nDECLARE @NumError NVARCHAR(16);\n";
                $sql2 .= "EXEC webDatpos_insertarNumeradorAlmacen @ccod_cia=?, @ccod_usuario=?, @ccod_alm=?, @ctip_doc=?, @cserie=?, @nnumero=?, @cdsc_numeralmacen=?, @ErrorNumber=@NumErrorNumber OUTPUT, @ErrorMessage=@NumErrorMessage OUTPUT, @Error=@NumError OUTPUT, @id_ctalmac=?;\n";
                $sql2 .= "SELECT @NumErrorNumber AS ErrorNumber, @NumErrorMessage AS ErrorMessage, @NumError AS [Error];";

                $params2 = array(
                    $objConex->ccod_empresa, $objConex->ccod_usuario,
                    $objBE->ccod_alm, $num->cdoc_tipo, $num->cdoc_serie,
                    $num->cdoc_nro, $num->cdsc_numer, $id_ctalmac
                );
                $stmt2 = sqlsrv_query($conn, $sql2, $params2);
                if (!$stmt2) throw new Exception('Error insertando numerador');

                $row2 = $this->fetchFirstRow($stmt2);
                if ($row2 !== null) {
                    $en = $row2['ErrorNumber'] ?? 'OK';
                    if ($en !== 'OK' && $en !== '') {
                        $numerErrorNumber = strval($en);
                        $errorMessage     = strval($row2['ErrorMessage'] ?? '');
                        sqlsrv_free_stmt($stmt2);
                        sqlsrv_rollback($conn);
                        sqlsrv_close($conn);
                        // [false, ErrorNumber, ErrorMessage, NumeradorConflicto(serie del que falló)]
                        return array(false, $numerErrorNumber, $errorMessage, $num->cdoc_serie);
                    }
                }
                sqlsrv_free_stmt($stmt2);
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            return array(true, 'OK', '', '');

        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            return array(false, 'Error', $e->getMessage(), '');
        }
    }

    public function editarAlmacen($objBE, $numeradores, $objConex) {
        $connStr = Database::buildTenantConnectionString($objConex);
        $conn = sqlsrv_connect($connStr['server'], $connStr['connectionInfo']);

        if (!$conn) {
            return array(false, 'Error de conexión', '', '');
        }

        sqlsrv_begin_transaction($conn);

        try {
            $sql = "DECLARE @ErrorNumber NVARCHAR(16);\n";
            $sql .= "EXEC webDatpos_editaralmacen @ccod_alm=?, @ccod_cia=?, @cdsc_alm=?, @cstatus=?, @ccod_usuario=?, @cdepartamento=?, @cprovincia=?, @cdistrito=?, @cdirc_almac=?, @curba_almac=?, @cubigeo=?, @ErrorNumber=@ErrorNumber OUTPUT;\n";
            $sql .= "SELECT @ErrorNumber AS ErrorNumber;";

            $params = array(
                $objBE->ccod_alm, $objConex->ccod_empresa, $objBE->cdsc_alm,
                $objBE->cstatus, $objConex->ccod_usuario, $objBE->cdepartamento,
                $objBE->cprovincia, $objBE->cdistrito, $objBE->cdirc_almac,
                $objBE->curba_almac, $objBE->cubigeo
            );

            $stmt = sqlsrv_query($conn, $sql, $params);
            if (!$stmt) throw new Exception('Error editando almacén');

            $errorNumber = 'OK';
            $row = $this->fetchFirstRow($stmt);
            if ($row !== null) $errorNumber = $row['ErrorNumber'] ?? 'OK';
            sqlsrv_free_stmt($stmt);

            if ($errorNumber !== 'OK' && $errorNumber !== '') {
                sqlsrv_rollback($conn);
                sqlsrv_close($conn);
                return array(false, strval($errorNumber), '', '');
            }

            $sql2 = "EXEC webDatpos_eliminarNumeradoresAlmacen @ccod_cia=?, @ccod_alm=?";
            $stmt2 = sqlsrv_query($conn, $sql2, array($objConex->ccod_empresa, $objBE->ccod_alm));
            if ($stmt2) sqlsrv_free_stmt($stmt2);

            foreach ($numeradores as $num) {
                $sql3 = "DECLARE @NumErrorNumber NVARCHAR(16);\nDECLARE @NumErrorMessage NVARCHAR(100);\nDECLARE @NumError NVARCHAR(16);\n";
                $sql3 .= "EXEC webDatpos_insertarNumeradorAlmacen @ccod_cia=?, @ccod_usuario=?, @ccod_alm=?, @ctip_doc=?, @cserie=?, @nnumero=?, @cdsc_numeralmacen=?, @ErrorNumber=@NumErrorNumber OUTPUT, @ErrorMessage=@NumErrorMessage OUTPUT, @Error=@NumError OUTPUT, @id_ctalmac=?;\n";
                $sql3 .= "SELECT @NumErrorNumber AS ErrorNumber, @NumErrorMessage AS ErrorMessage, @NumError AS [Error];";

                $params3 = array(
                    $objConex->ccod_empresa, $objConex->ccod_usuario,
                    $objBE->ccod_alm, $num->cdoc_tipo, $num->cdoc_serie,
                    $num->cdoc_nro, $num->cdsc_numer, ''
                );
                $stmt3 = sqlsrv_query($conn, $sql3, $params3);
                if ($stmt3) {
                    $row3 = $this->fetchFirstRow($stmt3);
                    if ($row3 !== null) {
                        $en = $row3['ErrorNumber'] ?? 'OK';
                        if ($en !== 'OK' && $en !== '') {
                            $em = strval($row3['ErrorMessage'] ?? '');
                            sqlsrv_free_stmt($stmt3);
                            sqlsrv_rollback($conn);
                            sqlsrv_close($conn);
                            return array(false, strval($en), $em, $num->cdoc_serie);
                        }
                    }
                    sqlsrv_free_stmt($stmt3);
                }
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            return array(true, 'OK', '', '');

        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            return array(false, 'Error', $e->getMessage(), '');
        }
    }

    /**
     * Recorre todos los result sets hasta encontrar uno con datos y devuelve la primera fila asociativa.
     * Maneja correctamente DECLARE+EXEC+SELECT donde el orden de resultados puede variar.
     */
    private function fetchFirstRow($stmt) {
        do {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($row !== null && $row !== false) return $row;
        } while (sqlsrv_next_result($stmt) === true);
        return null;
    }
}
?>
