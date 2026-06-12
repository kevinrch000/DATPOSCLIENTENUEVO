<?php
// Update appDatpos_abrirCaja stored procedure in the database
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

$obj = new BEUsuario();
$obj->ccod_empresa = 'EMP01';
$obj->ccod_usuario = 'ADMIN';
$obj->id_rol = 1;
$obj->cnombre_bd = 'DatPos_EMP01';
$obj->cnomser = 'localhost\\SQLEXPRESS';

$sql = "
ALTER PROCEDURE [dbo].[appDatpos_abrirCaja]
    @CodTie      VARCHAR(20),
    @IdUsuario   VARCHAR(50),
    @CodCaj      VARCHAR(20),
    @Monto       DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50),
    @dfchdoc_ini DATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @dfchdoc_ini IS NULL SET @dfchdoc_ini = GETDATE();

    DECLARE @id_turno INT;

    /* Si el usuario ya tiene turno abierto, devolver su id_turno real */
    SELECT TOP 1 @id_turno = id_turno
    FROM Turno
    WHERE ccod_cia = @CodCia
      AND ccod_usuario = @IdUsuario
      AND cstatus = 'A'
    ORDER BY id_turno DESC;

    IF @id_turno IS NOT NULL
    BEGIN
        SELECT CAST(@id_turno AS VARCHAR(20)) AS id_turno;
        RETURN;
    END

    /* Crear nuevo turno */
    INSERT INTO Turno(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja,
                      nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');

    SET @id_turno = SCOPE_IDENTITY();

    SELECT CAST(@id_turno AS VARCHAR(20)) AS id_turno;
END
";

$connectionInfo = array(
    "Database" => $obj->cnombre_bd,
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true
);
$conn = sqlsrv_connect($obj->cnomser, $connectionInfo);
if ($conn === false) {
    echo "Connection failed:\n";
    print_r(sqlsrv_errors());
    exit;
}

$stmt = sqlsrv_query($conn, $sql);
if ($stmt !== false) {
    echo "SUCCESS: Stored procedure appDatpos_abrirCaja updated successfully in database.\n";
} else {
    echo "ERROR updating SP:\n";
    print_r(sqlsrv_errors());
}

sqlsrv_close($conn);
