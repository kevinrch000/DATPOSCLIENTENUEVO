<?php
/**
 * DatPOS - API: Familias
 * Reemplaza los WebMethods de Tablas/Familias.aspx.vb:
 *   - ConsultarFamilias
 *   - ConsultarFamilia
 *   - Guardar (nuevo/editar)
 *   - Eliminar
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLFamilia.php';
require_once __DIR__ . '/../BE/BEFamilia.php';

// Verificar sesión
if (!isset($_SESSION['objBEUsuario'])) {
    jsonResponse(array('d' => '-1'));
}

$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

switch ($method) {

    case 'ConsultarFamilias':
        $blFamilia = new BLFamilia();
        $rows = $blFamilia->consultarFamilias($objUsuario);

        // sp_consultafamilias devuelve: id_lin(0), ccod_lin(1), cdsc_lin(2), cstatus(3), ccolor(4)
        $lstCom = array();
        foreach ($rows as $fila) {
            $obj = new BEFamilia();
            $cstatus = strval($fila[3] ?? '');
            $obj->item     = "<input id='" . strval($fila[1] ?? '') . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>";
            $obj->ccod_lin = strval($fila[1] ?? '');
            $obj->cdsc_lin = strval($fila[2] ?? '');
            $obj->estado   = ($cstatus === 'A' || $cstatus === '1') ? 'Activo' : 'Inactivo';
            $obj->ccolor   = strval($fila[4] ?? '');
            $lstCom[] = $obj;
        }

        jsonResponse(array('d' => $lstCom));
        break;

    case 'ConsultarFamilia':
        $input = getJsonInput();
        $codigo = $input['codigo'] ?? '';

        $blFamilia = new BLFamilia();
        $rows = $blFamilia->consultarFamilia($codigo, $objUsuario);

        // sp_consultafamilia => SELECT * FROM Familias:
        //   id_lin(0), ccod_cia(1), ccod_lin(2), cdsc_lin(3), cstatus(4), ccolor(5), ccod_usuario(6), dfch_crea(7)
        $lstCom = array();
        foreach ($rows as $fila) {
            $obj = new BEFamilia();
            $obj->ccod_lin = strval($fila[2] ?? '');
            $obj->cdsc_lin = strval($fila[3] ?? '');
            $cstatus = strval($fila[4] ?? '');
            // Convertir 'A'/'I' a 1/0 para el dropdown
            $obj->cstatus  = ($cstatus === 'A' || $cstatus === '1') ? 1 : 0;
            $obj->ccolor   = strval($fila[5] ?? '');
            $lstCom[] = $obj;
        }

        jsonResponse(array('d' => $lstCom));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $familiaData = $input['familia'][0] ?? array();
        $operacion   = $input['operacion'] ?? '';

        $objBE = new BEFamilia();
        $objBE->ccod_lin = $familiaData['ccod_lin'] ?? '';
        $objBE->cdsc_lin = $familiaData['cdsc_lin'] ?? '';
        // JS envía 1/0 → SP espera A/I
        $cs = strval($familiaData['cstatus'] ?? '0');
        $objBE->cstatus  = ($cs === '1' || $cs === 'A') ? 'A' : 'I';
        $objBE->ccolor   = $familiaData['ccolor'] ?? '';

        $blFamilia = new BLFamilia();
        $lstCom = array();

        if ($operacion === 'nuevo') {
            $rows = $blFamilia->insertarFamilia($objBE, $objUsuario);
            foreach ($rows as $fila) {
                $obj = new BEFamilia();
                $obj->ccod_lin = strval($fila[0] ?? '');
                $obj->ccod_cia = strval($fila[1] ?? '');
                $lstCom[] = $obj;
            }
        }

        if ($operacion === 'editar') {
            $rows = $blFamilia->editarFamilia($objBE, $objUsuario);
            foreach ($rows as $fila) {
                $obj = new BEFamilia();
                $obj->ccod_lin = strval($fila[0] ?? '');
                $obj->ccod_cia = strval($fila[1] ?? '');
                $lstCom[] = $obj;
            }
        }

        jsonResponse(array('d' => $lstCom));
        break;

    case 'Eliminar':
        $input = getJsonInput();
        $familia = $input['familia'] ?? '';

        $blFamilia = new BLFamilia();
        $rows = $blFamilia->eliminarFamilia($familia, $objUsuario);

        $lstCom = array();
        foreach ($rows as $fila) {
            $obj = new BEFamilia();
            $obj->ccod_lin = strval($fila[0] ?? '');
            $obj->ccod_cia = strval($fila[1] ?? '');
            $lstCom[] = $obj;
        }

        jsonResponse(array('d' => $lstCom));
        break;

    default:
        jsonResponse(array('d' => array()));
        break;
}
?>
