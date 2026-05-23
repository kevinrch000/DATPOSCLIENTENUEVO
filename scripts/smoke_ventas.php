<?php
require_once __DIR__ . '/../BE/BEUsuario.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DA/DAFamilia.php';
require_once __DIR__ . '/../DA/DAArticulo.php';
require_once __DIR__ . '/../DA/DAPrecio.php';
require_once __DIR__ . '/../DA/DACliente.php';
require_once __DIR__ . '/../DA/DATienda.php';
require_once __DIR__ . '/../DA/DAEmpresa.php';
require_once __DIR__ . '/../DA/DACuenta.php';
require_once __DIR__ . '/../BL/BLMovimientoCabecera.php';

if (!defined('STDERR')) {
    define('STDERR', fopen('php://stderr', 'w'));
}

function assert_true($cond, $name, $detail='') {
    if (!$cond) {
        fwrite(STDERR, "FAIL: $name" . ($detail !== '' ? " - $detail" : '') . "\n");
        echo "FAIL: $name" . ($detail !== '' ? " - $detail" : '') . "\n";
        exit(1);
    }
    echo "PASS: $name\n";
}

$u = new BEUsuario();
$u->ccod_usuario   = 'ADMIN';
$u->cdsc_usuario   = 'ADMINISTRADOR';
$u->ccod_empresa   = 'EMP01';
$u->cnombre_bd     = 'DatPos_EMP01';
$u->cnomser        = getenv('DATPOS_TENANT_SERVER') ?: 'localhost\SQLEXPRESS';
$u->ccod_tiend     = 'T001';
$u->ccod_almacen   = 'ALM001';
$u->ccod_caja      = 'CAJ01';
$u->id_rol         = 1;
$u->rolMaster      = 1;


$conn = Database::getTenantConnection($u);
if ($conn === false) {
    echo "FAIL: tenant connection\n";
    echo "Detalle de errores sqlsrv:\n";
    var_dump(sqlsrv_errors());
    exit(1);
}
assert_true($conn !== false, 'tenant connection');
sqlsrv_close($conn);


$familias = (new DAFamilia())->consultarFamiliasActivas($u);
assert_true(count($familias) > 0, 'familias activas');
$idFamilia = intval($familias[0][2]);


$articulos = (new DAArticulo())->consultarArticulosTodos('', $u);
assert_true(count($articulos) > 0, 'articulos todos');
$codigo = strval($articulos[0][2]);
$ccodArticulo = (new DAPrecio())->ResolverCodigoArticulo($codigo, $u);


$articulosCat = (new DAArticulo())->consultarArticulosCategoria($idFamilia, $u);
assert_true(count($articulosCat) > 0, 'articulos categoria');


$favoritos = (new DAArticulo())->cargarFavoritos($u);
assert_true(is_array($favoritos), 'favoritos query');


$precio = (new DAPrecio())->ConsultarArticuloPrecio($codigo, $u);
assert_true(count($precio) > 0, 'precio por id');


$precioCodigo = (new DAPrecio())->ConsultarArticuloPrecioCodigo($ccodArticulo, $u);
assert_true(count($precioCodigo) > 0, 'precio por codigo');


$cliente = (new DACliente())->ClientePorDefecto($u);
assert_true(count($cliente) > 0, 'cliente por defecto');
$ccodCoa = 'CLI000';
$clientes = (new DACliente())->ConsultarClientesTodos('', '', $u);
assert_true(count($clientes) > 0, 'clientes todos');


$tienda = (new DATienda())->ConsultarTienda($u->ccod_tiend, $u);
assert_true(count($tienda) > 0, 'consultar tienda');
$impuestos = (new DAEmpresa())->ConsultarImpuestos($u);
assert_true(count($impuestos) > 0, 'consultar impuestos');


$bl = new BLMovimientoCabecera();
$validacion = $bl->ValidarFacturacion($u);
assert_true($validacion === '' || $validacion === null, 'validar facturacion', strval($validacion));
$validarDoc = $bl->ValidarAlFacturar('BV', $u);
assert_true($validarDoc === '' || $validarDoc === null, 'validar al facturar BV', strval($validarDoc));


$conn = Database::getTenantConnection($u);
sqlsrv_begin_transaction($conn);
$sql = "DECLARE @id_cbcuenta INT; EXEC sp_lsinsertarcuenta @ccod_cia=?, @ccod_coa=?, @ccod_tiend=?, @ccod_caja=?, @etiqueta=?, @ccod_usuario=?, @ctip_cuenta=?, @ntot_desct=?, @ntot_impbruto=?, @ntot_igv=?, @ntot_impneto=?, @id_cbcuenta=@id_cbcuenta OUTPUT; SELECT @id_cbcuenta AS id_cbcuenta;";
$stmt = sqlsrv_query($conn, $sql, array($u->ccod_empresa, $ccodCoa, $u->ccod_tiend, $u->ccod_caja, 'SMOKE', $u->ccod_usuario, '2', 0, 10, 1.8, 11.8));
if ($stmt === false) { fwrite(STDERR, print_r(sqlsrv_errors(), true)); }
assert_true($stmt !== false, 'insert cuenta lista precio');
$idCuenta = 0;
do {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row && isset($row['id_cbcuenta'])) { $idCuenta = intval($row['id_cbcuenta']); break; }
} while (sqlsrv_next_result($stmt));
assert_true($idCuenta > 0, 'cuenta id generado');
$stmt2 = sqlsrv_query($conn, "EXEC sp_lsinsertarcuentadetalle @ncantidad=?, @nprecio=?, @nimporte_neto=?, @id_articulo=?, @nimporte_bruto=?, @nimpuesto=?, @ndescuento=?, @ctip_descn=?, @cobser_variante=?, @ccod_cia=?, @id_cbcuenta=?, @corden=?, @ccod_usuario=?, @ctip_desc=?, @nigv_uni=?, @ncosto=?, @id_variante=?, @cdescn_max=?", array(1, 10, 10, $ccodArticulo, 10, 1.8, 0, '', '', $u->ccod_empresa, $idCuenta, 1, $u->ccod_usuario, '', 1.8, 1, 0, ''));
if ($stmt2 === false) { fwrite(STDERR, print_r(sqlsrv_errors(), true)); }
assert_true($stmt2 !== false, 'insert cuenta detalle lista precio');
$stmt3 = sqlsrv_query($conn, 'EXEC sp_lsconsultarcuentadetalles @id_cbcuenta=?', array($idCuenta));
assert_true($stmt3 !== false, 'consultar detalle cuenta statement');
$hasDet = false;
if ($stmt3) {
    while (true) {
        $rowDet = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_NUMERIC);
        if ($rowDet) { $hasDet = true; break; }
        $next = sqlsrv_next_result($stmt3);
        if ($next === false || $next === null) { break; }
    }
}
assert_true($hasDet, 'consultar detalle cuenta');
sqlsrv_rollback($conn);
sqlsrv_close($conn);


echo "SMOKE_VENTAS_OK\n";