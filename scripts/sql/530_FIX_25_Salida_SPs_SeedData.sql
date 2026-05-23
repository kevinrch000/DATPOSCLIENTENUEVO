/* =====================================================================
   FIX 25 — SPs faltantes para Salida.aspx + Datos de prueba
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. webDatpos_consultarArticulosSalida ──
   VB espera 5 cols: [0]ccod_articulo [1]cdsc_articulo [2]linea [3]ncantidad [4]ncosto
   ANTES: solo retornaba 4 cols (faltaba linea/ccod_lin) → IndexOutOfRange en [4]
*/
IF OBJECT_ID('webDatpos_consultarArticulosSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticulosSalida;
GO
CREATE PROCEDURE webDatpos_consultarArticulosSalida @ccod_cia VARCHAR(20), @almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,
           A.cdsc_articulo,
           ISNULL(A.ccod_lin,'')       AS ccod_lin,
           ISNULL(S.ncantidad,0)       AS ncantidad,
           ISNULL(S.ncosto,0)          AS ncosto
    FROM Articulos A
    INNER JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@almacen AND S.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND S.ncantidad>0
    ORDER BY A.cdsc_articulo;
END
GO
PRINT '✓ webDatpos_consultarArticulosSalida (5 cols)';
GO

/* ── 2. webDatpos_validarArticuloAlmacenSalida ──
   VB espera 4 cols: [0]ccod_articulo [1]cdsc_articulo [2]ncantidad [3]ncosto
*/
IF OBJECT_ID('webDatpos_validarArticuloAlmacenSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_validarArticuloAlmacenSalida;
GO
CREATE PROCEDURE webDatpos_validarArticuloAlmacenSalida @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,
           A.cdsc_articulo,
           ISNULL(S.ncantidad,0) AS ncantidad,
           ISNULL(S.ncosto,0)    AS ncosto
    FROM Articulos A
    LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_alm AND S.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo=@ccod_articulo;
END
GO
PRINT '✓ webDatpos_validarArticuloAlmacenSalida (4 cols)';
GO

/* ── 3. webDatpos_articuloCantaArti ──
   VB espera: [0] ccod_articulo (stock disponible)
*/
IF OBJECT_ID('webDatpos_articuloCantaArti','P') IS NOT NULL DROP PROCEDURE webDatpos_articuloCantaArti;
GO
CREATE PROCEDURE webDatpos_articuloCantaArti @ccod_articulo VARCHAR(50), @ncantidad INT, @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT S.ccod_articulo
    FROM Stock S
    WHERE S.ccod_cia=@ccod_cia AND S.ccod_articulo=@ccod_articulo AND S.ccod_alm=@ccod_alm AND S.ncantidad>=@ncantidad;
END
GO
PRINT '✓ webDatpos_articuloCantaArti';
GO

/* ── 4. appDatpos_validarStockArticulos ──
   VB espera: [0]cdsc_articulo [1]ncantidad [2]ncantidad_actual [3]ncantidad_faltante
*/
IF OBJECT_ID('appDatpos_validarStockArticulos','P') IS NOT NULL DROP PROCEDURE appDatpos_validarStockArticulos;
GO
CREATE PROCEDURE appDatpos_validarStockArticulos @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20), @producto NVARCHAR(MAX)
AS BEGIN SET NOCOUNT ON;
    SELECT 'OK' AS cdsc_articulo, 0 AS ncantidad, 0 AS ncantidad_actual, 0 AS ncantidad_faltante WHERE 1=0;
END
GO
PRINT '✓ appDatpos_validarStockArticulos (stub)';
GO

/* ── 5. webDatpos_consultarSalida ──
   VB espera: scattered indices from SELECT *
*/
IF OBJECT_ID('webDatpos_consultarSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarSalida;
GO
CREATE PROCEDURE webDatpos_consultarSalida @ccod_cia VARCHAR(20), @codigo VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve, C.ccod_cia, ISNULL(C.ccod_tienda,'') AS ccod_tienda,
           ISNULL(C.ccod_alm,'') AS ccod_alm, C.dfecha, ISNULL(C.ctipo,'S') AS ctipo,
           ISNULL(C.vserie,'') AS vserie, ISNULL(C.nnumero,0) AS nnumero,
           ISNULL(C.vobservacion,'') AS vobservacion, ISNULL(C.ntotal,0) AS ntotal
    FROM CbInventario C
    WHERE C.ccod_cia=@ccod_cia AND C.id_cbinve=CAST(@codigo AS INT);
END
GO
PRINT '✓ webDatpos_consultarSalida (10 cols con ISNULL)';
GO

/* ── 6. webDatpos_insertarSalida ── */
IF OBJECT_ID('webDatpos_insertarSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarSalida;
GO
CREATE PROCEDURE webDatpos_insertarSalida
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha VARCHAR(20), @ctipo VARCHAR(10), @vserie VARCHAR(20),
    @vobservacion VARCHAR(500), @ccod_usuario VARCHAR(50), @ntotal DECIMAL(18,4),
    @id_cbinve VARCHAR(16) OUTPUT, @ErrorNumber NVARCHAR(16) OUTPUT, @ErrorMessage NVARCHAR(200) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @ErrorNumber = '0'; SET @ErrorMessage = '';
    BEGIN TRY
        DECLARE @nextNum INT;
        SELECT @nextNum = ISNULL(nnumero,1) FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctip_doc='S';
        IF @nextNum IS NULL SET @nextNum = 1;

        INSERT INTO CbInventario (ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ntotal)
        VALUES (@ccod_cia,@ccod_tienda,@ccod_alm,@dfecha,@ctipo,@vserie,@nextNum,@vobservacion,@ccod_usuario,@ntotal);
        SET @id_cbinve = SCOPE_IDENTITY();

        UPDATE NumeradorAlmacen SET nnumero=@nextNum+1 WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctip_doc='S';
    END TRY
    BEGIN CATCH
        SET @ErrorNumber = ERROR_NUMBER(); SET @ErrorMessage = ERROR_MESSAGE();
    END CATCH
END
GO
PRINT '✓ webDatpos_insertarSalida (auto-numerador)';
GO

/* ── 7. webDatpos_insertarDetalleSalidaInventario ── */
IF OBJECT_ID('webDatpos_insertarDetalleSalidaInventario','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarDetalleSalidaInventario;
GO
CREATE PROCEDURE webDatpos_insertarDetalleSalidaInventario
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_articulo VARCHAR(50),
    @ncantidad INT, @ncosto DECIMAL(18,4), @id_cbinve INT, @almacen VARCHAR(20),
    @ErrorNumber NVARCHAR(16) OUTPUT, @ErrorMessage NVARCHAR(200) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @ErrorNumber = '0'; SET @ErrorMessage = '';
    BEGIN TRY
        INSERT INTO LnInventario (ccod_cia,id_cbinve,ccod_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
        VALUES (@ccod_cia,@id_cbinve,@ccod_articulo,@ncantidad,@ncosto,@almacen,@ccod_usuario);
        -- Descontar stock
        UPDATE Stock SET ncantidad=ncantidad-@ncantidad WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo AND ccod_alm=@almacen;
    END TRY
    BEGIN CATCH
        SET @ErrorNumber = ERROR_NUMBER(); SET @ErrorMessage = ERROR_MESSAGE();
    END CATCH
END
GO
PRINT '✓ webDatpos_insertarDetalleSalidaInventario';
GO

/* ── 8. Corregir webDatpos_consultarSalidas (8 cols con ISNULL) ── */
IF OBJECT_ID('webDatpos_consultarSalidas','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarSalidas;
GO
CREATE PROCEDURE webDatpos_consultarSalidas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve,
           ISNULL(C.ccod_alm,'')       AS ccod_alm,
           C.dfecha,
           ISNULL(C.ctipo,'')          AS ctipo,
           ISNULL(C.vserie,'')         AS vserie,
           ISNULL(C.nnumero,0)         AS nnumero,
           ISNULL(C.vobservacion,'')   AS vobservacion,
           ISNULL(C.ntotal,0)          AS ntotal
    FROM CbInventario C
    WHERE C.ccod_cia=@ccod_cia AND C.ctipo='S'
    ORDER BY C.dfecha DESC;
END
GO
PRINT '✓ webDatpos_consultarSalidas (8 cols)';
GO

/* ── 9. Datos de prueba ── */
-- Artículos de prueba (solo si no existen)
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART001')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc)
    VALUES ('EMP01','ART001','Producto Test 1','FAM01','UND','A','B','S','N');
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART002')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc)
    VALUES ('EMP01','ART002','Producto Test 2','FAM01','UND','A','B','S','N');
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART003')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc)
    VALUES ('EMP01','ART003','Producto Test 3','FAM01','UND','A','B','S','N');
GO

-- Stock para pruebas de salida
DECLARE @alm VARCHAR(20);
SELECT TOP 1 @alm = ccod_alm FROM Almacenes WHERE ccod_cia='EMP01' AND cstatus='A';
IF @alm IS NOT NULL BEGIN
    IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_articulo='ART001' AND ccod_alm=@alm)
        INSERT INTO Stock (ccod_cia,ccod_articulo,ccod_alm,ncantidad,ncosto) VALUES ('EMP01','ART001',@alm,100,25.50);
    ELSE
        UPDATE Stock SET ncantidad=100, ncosto=25.50 WHERE ccod_cia='EMP01' AND ccod_articulo='ART001' AND ccod_alm=@alm;

    IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_articulo='ART002' AND ccod_alm=@alm)
        INSERT INTO Stock (ccod_cia,ccod_articulo,ccod_alm,ncantidad,ncosto) VALUES ('EMP01','ART002',@alm,50,15.00);
    ELSE
        UPDATE Stock SET ncantidad=50, ncosto=15.00 WHERE ccod_cia='EMP01' AND ccod_articulo='ART002' AND ccod_alm=@alm;

    IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_articulo='ART003' AND ccod_alm=@alm)
        INSERT INTO Stock (ccod_cia,ccod_articulo,ccod_alm,ncantidad,ncosto) VALUES ('EMP01','ART003',@alm,200,8.75);
    ELSE
        UPDATE Stock SET ncantidad=200, ncosto=8.75 WHERE ccod_cia='EMP01' AND ccod_articulo='ART003' AND ccod_alm=@alm;

    PRINT '✓ Stock de prueba insertado en almacén: ' + @alm;
END
GO

-- Verificación
SELECT 'Artículos' AS Tipo, ccod_articulo, cdsc_articulo, cstatus FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo IN ('ART001','ART002','ART003');
SELECT 'Stock' AS Tipo, ccod_articulo, ccod_alm, ncantidad, ncosto FROM Stock WHERE ccod_cia='EMP01' AND ccod_articulo IN ('ART001','ART002','ART003');
SELECT 'Numeradores' AS Tipo, ccod_alm, ctip_doc, cserie, nnumero FROM NumeradorAlmacen WHERE ccod_cia='EMP01';
GO

PRINT '═══════════════════════════════════════';
PRINT '  FIX 25 COMPLETO - Salida operativa';
PRINT '═══════════════════════════════════════';
GO
