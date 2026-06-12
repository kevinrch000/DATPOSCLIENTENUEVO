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
ALTER PROCEDURE [dbo].[sp_consultaoperalmacenpricipal]
    @ccod_cia       VARCHAR(20),
    @ccod_alm       VARCHAR(20)  = '',
    @fchDesde       VARCHAR(20)  = '',
    @fchHasta       VARCHAR(20)  = '',
    @ctipo          VARCHAR(20)  = '',
    @cserie         VARCHAR(20)  = '',
    @nnumero        VARCHAR(20)  = '',
    @ccod_coa       VARCHAR(200) = '',
    @cdsc_usuario   VARCHAR(200) = ''
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @dDesde  DATETIME = TRY_CONVERT(DATETIME, @fchDesde, 103);
    DECLARE @dHasta  DATETIME = TRY_CONVERT(DATETIME, @fchHasta, 103);
    IF @dHasta IS NOT NULL
        SET @dHasta = DATEADD(SECOND, 86399, CAST(CAST(@dHasta AS DATE) AS DATETIME));

    SELECT
        CB.id_cbinve,                                            -- [0]
        CB.ctipo,                                                -- [1]
        ISNULL(CB.vserie,'')                       AS vserie,    -- [2]
        CB.nnumero,                                              -- [3]
        CB.ntotal,                                               -- [4]
        CONVERT(VARCHAR(10), CB.dfecha, 103)       AS dfecha,    -- [5]
        ISNULL(CB.ccod_alm,'')                     AS ccod_alm_ing,-- [6]
        ISNULL(CB.ccod_usuario,'')                 AS cdsc_usuario,-- [7]
        ISNULL(CB.ccod_coa,'')                     AS ccoa_dsc,  -- [8]
        ISNULL(
            (SELECT TOP 1
                 ISNULL(F.cdoc,'')
                 + CASE WHEN ISNULL(F.cserie,'') <> ''
                        THEN ' ' + F.cserie ELSE '' END
                 + CASE WHEN ISNULL(F.nnumero,0) <> 0
                        THEN '-' + CAST(F.nnumero AS VARCHAR(20)) ELSE '' END
             FROM CbFactura F
             WHERE F.ccod_cia=CB.ccod_cia AND F.id_cbinve=CB.id_cbinve
             ORDER BY F.id_cbfact DESC), '')        AS DocRef,   -- [9]
        ''                                          AS DocFact   -- [10]
    FROM CbInventario CB
    WHERE CB.ccod_cia = @ccod_cia
      AND (@ccod_alm      = '' OR CB.ccod_alm      = @ccod_alm)
      AND (@ctipo         = '' OR CB.ctipo          = @ctipo)
      AND (@cserie        = '' OR CB.vserie         LIKE '%' + @cserie + '%')
      AND (@nnumero       = '' OR CAST(CB.nnumero AS VARCHAR(20)) = @nnumero)
      AND (@ccod_coa      = '' OR ',' + REPLACE(@ccod_coa, ' ', '') + ',' LIKE '%,' + RTRIM(CB.ccod_coa) + ',%')
      AND (@cdsc_usuario  = '' OR ',' + REPLACE(@cdsc_usuario, ' ', '') + ',' LIKE '%,' + RTRIM(CB.ccod_usuario) + ',%')
      AND (@dDesde IS NULL OR CB.dfecha >= @dDesde)
      AND (@dHasta IS NULL OR CB.dfecha <= @dHasta)
    ORDER BY CB.dfecha DESC, CB.id_cbinve DESC;
END
";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo "ERROR al alterar el procedimiento:\n";
    print_r(sqlsrv_errors());
    sqlsrv_close($conn);
    exit(1);
}

echo "OK: Stored Procedure sp_consultaoperalmacenpricipal alterado correctamente.\n";
sqlsrv_close($conn);
?>
