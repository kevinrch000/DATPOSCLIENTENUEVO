/* PARTE 8: DatPos_EMP01 — SPs INVENTARIO (Ingresos, Salidas, Transferencias) */
USE DatPos_EMP01;
GO

/* Helper interno: actualizar stock al insertar movimiento */
IF OBJECT_ID('_stock_actualizar','P') IS NOT NULL DROP PROCEDURE _stock_actualizar; 
GO
CREATE PROCEDURE _stock_actualizar @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20), @ccod_articulo VARCHAR(50), @ncantidad DECIMAL(18,4), @ncosto DECIMAL(18,4), @signo INT
AS BEGIN SET NOCOUNT ON;
    IF EXISTS (SELECT 1 FROM Stock WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ccod_articulo=@ccod_articulo)
        UPDATE Stock SET ncantidad=ncantidad+(@signo*@ncantidad), ncosto=CASE WHEN @signo=1 THEN @ncosto ELSE ncosto END WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ccod_articulo=@ccod_articulo;
    ELSE
        INSERT INTO Stock(ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto) VALUES(@ccod_cia,@ccod_alm,@ccod_articulo,@signo*@ncantidad,@ncosto);
END
GO

/* webDatpos_insertarinventario — Cabecera de ingreso */
IF OBJECT_ID('webDatpos_insertarinventario','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarinventario; 
GO
CREATE PROCEDURE webDatpos_insertarinventario
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha DATETIME, @ctipo VARCHAR(10), @vserie VARCHAR(10), @vobservacion VARCHAR(500),
    @ccod_usuario VARCHAR(50), @ccod_coa VARCHAR(20), @ntotal DECIMAL(18,4)
AS BEGIN SET NOCOUNT ON;
    DECLARE @nnumero INT;
    SELECT @nnumero = ISNULL(MAX(nnumero),0)+1 FROM CbInventario WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctipo=@ctipo AND vserie=@vserie;
    INSERT INTO CbInventario (ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ccod_coa,ntotal)
    VALUES (@ccod_cia,@ccod_tienda,@ccod_alm,@dfecha,@ctipo,@vserie,@nnumero,@vobservacion,@ccod_usuario,@ccod_coa,@ntotal);
    SELECT SCOPE_IDENTITY() AS id_cbinve;
END
GO

/* webDatpos_insertarSalida — Cabecera de salida */
IF OBJECT_ID('webDatpos_insertarSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarSalida; 
GO
CREATE PROCEDURE webDatpos_insertarSalida
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha DATETIME, @ctipo VARCHAR(10), @vserie VARCHAR(10), @vobservacion VARCHAR(500),
    @ccod_usuario VARCHAR(50), @ntotal DECIMAL(18,4),
    @id_cbinve NVARCHAR(16) OUTPUT, @ErrorNumber NVARCHAR(16) OUTPUT, @ErrorMessage NVARCHAR(200) OUTPUT
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @nnumero INT;
        SELECT @nnumero = ISNULL(MAX(nnumero),0)+1 FROM CbInventario WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctipo=@ctipo AND vserie=@vserie;
        INSERT INTO CbInventario (ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ntotal)
        VALUES (@ccod_cia,@ccod_tienda,@ccod_alm,@dfecha,@ctipo,@vserie,@nnumero,@vobservacion,@ccod_usuario,@ntotal);
        SET @id_cbinve = CAST(SCOPE_IDENTITY() AS NVARCHAR);
        SET @ErrorNumber = '0'; SET @ErrorMessage = '';
    END TRY BEGIN CATCH SET @ErrorNumber = CAST(ERROR_NUMBER() AS NVARCHAR); SET @ErrorMessage = ERROR_MESSAGE(); END CATCH
END
GO

/* webDatpos_insertarDetalleSalidaInventario */
IF OBJECT_ID('webDatpos_insertarDetalleSalidaInventario','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarDetalleSalidaInventario; 
GO
CREATE PROCEDURE webDatpos_insertarDetalleSalidaInventario
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_articulo VARCHAR(50),
    @ncantidad INT, @ncosto DECIMAL(18,4), @id_cbinve NVARCHAR(16), @almacen VARCHAR(20),
    @ErrorNumber NVARCHAR(16) OUTPUT, @ErrorMessage NVARCHAR(200) OUTPUT
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        INSERT INTO LnInventario (ccod_cia,id_cbinve,ccod_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
        VALUES (@ccod_cia,CAST(@id_cbinve AS INT),@ccod_articulo,@ncantidad,@ncosto,@almacen,@ccod_usuario);
        EXEC _stock_actualizar @ccod_cia,@almacen,@ccod_articulo,@ncantidad,@ncosto,-1;
        SET @ErrorNumber='0'; SET @ErrorMessage='';
    END TRY BEGIN CATCH SET @ErrorNumber=CAST(ERROR_NUMBER() AS NVARCHAR); SET @ErrorMessage=ERROR_MESSAGE(); END CATCH
END
GO

/* webDatpos_insertarCbTransferencia — Cabecera transferencia entre almacenes */
IF OBJECT_ID('webDatpos_insertarCbTransferencia','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarCbTransferencia; 
GO
CREATE PROCEDURE webDatpos_insertarCbTransferencia
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20),
    @ccod_almOrigen VARCHAR(20), @ctipoOrigen VARCHAR(10), @cserieOrigen VARCHAR(10), @nnumeroOrigen INT,
    @ccod_almDestino VARCHAR(20), @ctipoDestino VARCHAR(10), @cserieDestino VARCHAR(10), @nnumeroDestino INT,
    @dfecha DATETIME, @vobservacion VARCHAR(500), @ccod_usuario VARCHAR(50), @ntotal DECIMAL(18,4),
    @id_cbinve NVARCHAR(16) OUTPUT, @ErrorNumber NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        INSERT INTO CbInventario (ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ntotal)
        VALUES (@ccod_cia,@ccod_tienda,@ccod_almOrigen,@dfecha,@ctipoOrigen,@cserieOrigen,@nnumeroOrigen,@vobservacion,@ccod_usuario,@ntotal);
        SET @id_cbinve = CAST(SCOPE_IDENTITY() AS NVARCHAR);
        SET @ErrorNumber='0';
    END TRY BEGIN CATCH SET @ErrorNumber=CAST(ERROR_NUMBER() AS NVARCHAR); END CATCH
END
GO

/* webDatpos_insertarLnTransferencia */
IF OBJECT_ID('webDatpos_insertarLnTransferencia','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarLnTransferencia; 
GO
CREATE PROCEDURE webDatpos_insertarLnTransferencia
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_articulo VARCHAR(50),
    @ccod_artSunat VARCHAR(20), @cdsc_articulo VARCHAR(200), @ncantidad INT, @ncosto DECIMAL(18,4),
    @id_cbinve NVARCHAR(16), @ccod_alm_salida VARCHAR(20), @ccod_alm_ingreso VARCHAR(20),
    @ErrorNumber NVARCHAR(16) OUTPUT, @ErrorMessage NVARCHAR(200) OUTPUT, @Error NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        INSERT INTO LnInventario (ccod_cia,id_cbinve,ccod_articulo,ccod_artSunat,cdsc_articulo,ncantidad,ncosto,ccod_alm,ccod_alm_ingreso,ccod_usuario)
        VALUES (@ccod_cia,CAST(@id_cbinve AS INT),@ccod_articulo,@ccod_artSunat,@cdsc_articulo,@ncantidad,@ncosto,@ccod_alm_salida,@ccod_alm_ingreso,@ccod_usuario);
        EXEC _stock_actualizar @ccod_cia,@ccod_alm_salida,@ccod_articulo,@ncantidad,@ncosto,-1;
        EXEC _stock_actualizar @ccod_cia,@ccod_alm_ingreso,@ccod_articulo,@ncantidad,@ncosto,1;
        SET @ErrorNumber='0'; SET @ErrorMessage=''; SET @Error='0';
    END TRY BEGIN CATCH SET @ErrorNumber=CAST(ERROR_NUMBER() AS NVARCHAR); SET @ErrorMessage=ERROR_MESSAGE(); SET @Error='1'; END CATCH
END
GO

/* Detalle ingreso (usado por InsertarInventario BL) */
IF OBJECT_ID('webDatpos_insertarDetalleInventario','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarDetalleInventario; 
GO
CREATE PROCEDURE webDatpos_insertarDetalleInventario
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_articulo VARCHAR(50),
    @ncantidad INT, @ncosto DECIMAL(18,4), @id_cbinve INT, @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnInventario (ccod_cia,id_cbinve,ccod_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
    VALUES (@ccod_cia,@id_cbinve,@ccod_articulo,@ncantidad,@ncosto,@ccod_alm,@ccod_usuario);
    EXEC _stock_actualizar @ccod_cia,@ccod_alm,@ccod_articulo,@ncantidad,@ncosto,1;
END
GO

/* Consultas Inventario */
IF OBJECT_ID('sp_consultaringresos','P') IS NOT NULL DROP PROCEDURE sp_consultaringresos; 
GO
CREATE PROCEDURE sp_consultaringresos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve,C.ccod_alm,A.cdsc_alm,C.dfecha,C.ctipo,C.vserie,C.nnumero,C.vobservacion,C.ntotal,C.ccod_coa,O.cdsc_coa
    FROM CbInventario C LEFT JOIN Almacenes A ON A.ccod_alm=C.ccod_alm AND A.ccod_cia=C.ccod_cia
    LEFT JOIN Coa O ON O.ccod_coa=C.ccod_coa AND O.ccod_cia=C.ccod_cia
    WHERE C.ccod_cia=@ccod_cia AND C.ctipo NOT IN ('S','ST') ORDER BY C.dfecha DESC;
END
GO

IF OBJECT_ID('webDatpos_consultaringresos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaringresos; 
GO
CREATE PROCEDURE webDatpos_consultaringresos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve,C.ccod_alm,C.dfecha,C.ctipo,C.vserie,C.nnumero,C.vobservacion,C.ntotal
    FROM CbInventario C WHERE C.ccod_cia=@ccod_cia AND C.ctipo NOT IN ('S','ST') ORDER BY C.dfecha DESC;
END
GO

IF OBJECT_ID('sp_consultaringreso','P') IS NOT NULL DROP PROCEDURE sp_consultaringreso; 
GO
CREATE PROCEDURE sp_consultaringreso @ccod_cia VARCHAR(20), @codigo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT C.*,L.ccod_articulo,L.cdsc_articulo,L.ncantidad,L.ncosto FROM CbInventario C
    INNER JOIN LnInventario L ON L.id_cbinve=C.id_cbinve AND L.ccod_cia=C.ccod_cia
    WHERE C.ccod_cia=@ccod_cia AND C.id_cbinve=CAST(@codigo AS INT);
END
GO

IF OBJECT_ID('webDatpos_consultarSalidas','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarSalidas; 
GO
CREATE PROCEDURE webDatpos_consultarSalidas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve,C.ccod_alm,C.dfecha,C.ctipo,C.vserie,C.nnumero,C.vobservacion,C.ntotal
    FROM CbInventario C WHERE C.ccod_cia=@ccod_cia AND C.ctipo IN ('S') ORDER BY C.dfecha DESC;
END
GO

IF OBJECT_ID('webDatpos_consultarSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarSalida; 
GO
CREATE PROCEDURE webDatpos_consultarSalida @ccod_cia VARCHAR(20), @codigo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT C.*,L.ccod_articulo,L.cdsc_articulo,L.ncantidad,L.ncosto FROM CbInventario C
    INNER JOIN LnInventario L ON L.id_cbinve=C.id_cbinve AND L.ccod_cia=C.ccod_cia
    WHERE C.ccod_cia=@ccod_cia AND C.id_cbinve=CAST(@codigo AS INT);
END
GO

IF OBJECT_ID('webDatpos_consultarTransferencias','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarTransferencias; 
GO
CREATE PROCEDURE webDatpos_consultarTransferencias @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve,C.ccod_alm AS alm_origen,A.cdsc_alm,C.dfecha,C.ctipo,C.vserie,C.nnumero,C.ntotal
    FROM CbInventario C LEFT JOIN Almacenes A ON A.ccod_alm=C.ccod_alm AND A.ccod_cia=C.ccod_cia
    WHERE C.ccod_cia=@ccod_cia AND C.ctipo IN ('ST','T') ORDER BY C.dfecha DESC;
END
GO

IF OBJECT_ID('webDatpos_consultarTransferencia','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarTransferencia; 
GO
CREATE PROCEDURE webDatpos_consultarTransferencia @ccod_cia VARCHAR(20), @codigo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT C.*,L.ccod_articulo,L.cdsc_articulo,L.ncantidad,L.ncosto,L.ccod_alm,L.ccod_alm_ingreso FROM CbInventario C
    INNER JOIN LnInventario L ON L.id_cbinve=C.id_cbinve AND L.ccod_cia=C.ccod_cia
    WHERE C.ccod_cia=@ccod_cia AND C.id_cbinve=CAST(@codigo AS INT);
END
GO

IF OBJECT_ID('sp_editarinventario','P') IS NOT NULL DROP PROCEDURE sp_editarinventario; 
GO
CREATE PROCEDURE sp_editarinventario
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha DATETIME, @ctipo VARCHAR(10), @vserie VARCHAR(10), @nnumero INT,
    @vobservacion VARCHAR(500), @id_cbinve INT
AS BEGIN SET NOCOUNT ON;
    UPDATE CbInventario SET ccod_tienda=@ccod_tienda,ccod_alm=@ccod_alm,dfecha=@dfecha,
        ctipo=@ctipo,vserie=@vserie,nnumero=@nnumero,vobservacion=@vobservacion
    WHERE ccod_cia=@ccod_cia AND id_cbinve=@id_cbinve;
END
GO

IF OBJECT_ID('sp_eliminarinventario','P') IS NOT NULL DROP PROCEDURE sp_eliminarinventario; 
GO
CREATE PROCEDURE sp_eliminarinventario @id INT
AS BEGIN SET NOCOUNT ON; DELETE FROM LnInventario WHERE id_cbinve=@id; DELETE FROM CbInventario WHERE id_cbinve=@id; END
GO

IF OBJECT_ID('webDatpos_consultarProveedor','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarProveedor; 
GO
CREATE PROCEDURE webDatpos_consultarProveedor @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_coa,cdsc_coa,cruc_coa FROM Coa WHERE ccod_cia=@ccod_cia AND cproveedor='1' AND cstatus='A' ORDER BY cdsc_coa; END
GO

/* Saldo / Kardex */
IF OBJECT_ID('webDatpos_ConsultaArticuloKardex','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultaArticuloKardex; 
GO
CREATE PROCEDURE webDatpos_ConsultaArticuloKardex @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT S.ccod_articulo,A.cdsc_articulo,S.ncantidad,S.ncosto FROM Stock S
    INNER JOIN Articulos A ON A.ccod_articulo=S.ccod_articulo AND A.ccod_cia=S.ccod_cia
    WHERE S.ccod_cia=@ccod_cia AND S.ccod_alm=@ccod_alm AND A.cstatus='A' ORDER BY A.cdsc_articulo;
END
GO

IF OBJECT_ID('webDatpos_ConsultaKardex','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultaKardex; 
GO
CREATE PROCEDURE webDatpos_ConsultaKardex @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20), @fchDesde VARCHAR(20), @fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_cbinve,C.dfecha,C.ctipo,C.vserie,C.nnumero,L.ncantidad,L.ncosto,
           CASE WHEN C.ctipo IN ('I','GI') THEN L.ncantidad ELSE 0 END AS entrada,
           CASE WHEN C.ctipo IN ('S','GS') THEN L.ncantidad ELSE 0 END AS salida,
           L.ccod_alm,L.ccod_alm_ingreso
    FROM LnInventario L INNER JOIN CbInventario C ON C.id_cbinve=L.id_cbinve AND C.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.ccod_articulo=@ccod_articulo
      AND (L.ccod_alm=@ccod_alm OR L.ccod_alm_ingreso=@ccod_alm)
      AND C.dfecha BETWEEN @fchDesde AND @fchHasta
    ORDER BY C.dfecha;
END
GO

PRINT '✓ SPs Inventario creados.';
GO
