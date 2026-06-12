<?php
require_once __DIR__ . '/../BE/BEUsuario.php';
require_once __DIR__ . '/../config/database.php';

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
    echo "ERROR: No se pudo conectar a la base de datos.\n";
    exit(1);
}

$sql = "
ALTER PROCEDURE [dbo].[sp_eliminarcliente]
    @ccod_cia VARCHAR(20),
    @ccod_coa VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    -- 1. Verificar si tiene relaciones conocidas
    IF EXISTS (SELECT 1 FROM CbFactura WHERE ccod_cia = @ccod_cia AND ccod_coa = @ccod_coa)
       OR EXISTS (SELECT 1 FROM CbCuenta WHERE ccod_cia = @ccod_cia AND ccod_coa = @ccod_coa)
       OR EXISTS (SELECT 1 FROM CbGuia WHERE ccod_cia = @ccod_cia AND ccod_coa = @ccod_coa)
    BEGIN
        -- Si tiene relaciones conocidas, simplemente inactivar
        UPDATE Coa 
        SET cstatus = 'I' 
        WHERE ccod_cia = @ccod_cia AND ccod_coa = @ccod_coa;

        SELECT 'INACTIVADO' AS resultado;
        RETURN;
    END

    BEGIN TRY
        -- Intentar la eliminación física de la base de datos
        DELETE FROM Coa 
        WHERE ccod_cia = @ccod_cia AND ccod_coa = @ccod_coa;

        SELECT 'ELIMINADO' AS resultado;
    END TRY
    BEGIN CATCH
        -- Si falla por cualquier otra FK no controlada, inactivamos en su lugar
        UPDATE Coa 
        SET cstatus = 'I' 
        WHERE ccod_cia = @ccod_cia AND ccod_coa = @ccod_coa;

        SELECT 'INACTIVADO' AS resultado;
    END CATCH
END
";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo "ERROR al alterar el procedimiento:\n";
    print_r(sqlsrv_errors());
    sqlsrv_close($conn);
    exit(1);
}

echo "OK: Stored Procedure sp_eliminarcliente alterado correctamente.\n";
sqlsrv_close($conn);
?>
