/* =====================================================================
   FIX 46 — Ventas CRUD final
   - Precios: filtro Top 50 estable y SP alias de IGV para compatibilidad.
   - Facturación/Cuentas: ClientePorDefecto retorna id y código COA.
   - Facturación: cliente por defecto CLI000 válido para FKs.
===================================================================== */
USE DatPos_EMP01;
GO

IF OBJECT_ID('sp_obtenerivg','P') IS NOT NULL DROP PROCEDURE sp_obtenerivg;
GO
CREATE PROCEDURE sp_obtenerivg @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    EXEC appDatpos_ObtenerIGV @ccod_cia;
END
GO

IF OBJECT_ID('sp_consultarprecios','P') IS NOT NULL DROP PROCEDURE sp_consultarprecios;
GO
CREATE PROCEDURE sp_consultarprecios
    @ccod_cia VARCHAR(20),
    @ccod_cblistpre VARCHAR(20),
    @TipFiltro VARCHAR(20),
    @Articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT TOP (CASE WHEN @TipFiltro='1' THEN 50 ELSE 2147483647 END)
        L.id_lnlistpre,
        L.ccod_cblistpre,
        L.ccod_articulo,
        A.cdsc_articulo,
        L.npre_uni,
        L.ndes_max,
        L.ndes_min
    FROM LnListaPrecio L
    INNER JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia
      AND L.ccod_cblistpre=@ccod_cblistpre
      AND (
          @TipFiltro IN ('1','2')
          OR @Articulo=''
          OR L.ccod_articulo=@Articulo
          OR A.cdsc_articulo LIKE '%'+@Articulo+'%'
      )
    ORDER BY A.cdsc_articulo, L.ccod_articulo;
END
GO

IF OBJECT_ID('sp_clientepordefecto','P') IS NOT NULL DROP PROCEDURE sp_clientepordefecto;
GO
CREATE PROCEDURE sp_clientepordefecto @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT TOP 1
        ctipo_coa AS ctip_doc,
        cdoc_coa,
        cdsc_coa,
        cdirc_coa,
        id_coa,
        ccod_coa
    FROM Coa
    WHERE ccod_cia=@ccod_cia AND ccod_coa='CLI000' AND cstatus='A';
END
GO

IF OBJECT_ID('sp_insertarcuenta','P') IS NOT NULL DROP PROCEDURE sp_insertarcuenta;
GO
CREATE PROCEDURE sp_insertarcuenta
    @ccod_cia VARCHAR(20),
    @ccod_coa VARCHAR(20),
    @ccod_tiend VARCHAR(20),
    @ccod_caja VARCHAR(20),
    @etiqueta VARCHAR(50),
    @ccod_usuario VARCHAR(50),
    @ctip_cuenta VARCHAR(5),
    @id_cbcuenta INT OUTPUT
AS BEGIN SET NOCOUNT ON;
    IF NULLIF(@ccod_coa,'') IS NULL SET @ccod_coa='CLI000';
    IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa)
        SET @ccod_coa='CLI000';

    INSERT INTO CbCuenta(ccod_cia,ccod_coa,ccod_tiend,ccod_caja,etiqueta,ccod_usuario,ctip_cuenta)
    VALUES(@ccod_cia,@ccod_coa,@ccod_tiend,@ccod_caja,@etiqueta,@ccod_usuario,@ctip_cuenta);
    SET @id_cbcuenta=SCOPE_IDENTITY();
END
GO

IF OBJECT_ID('sp_lsinsertarcuenta','P') IS NOT NULL DROP PROCEDURE sp_lsinsertarcuenta;
GO
CREATE PROCEDURE sp_lsinsertarcuenta
    @ccod_cia VARCHAR(20),
    @ccod_coa VARCHAR(20),
    @ccod_tiend VARCHAR(20),
    @ccod_caja VARCHAR(20),
    @etiqueta VARCHAR(50),
    @ccod_usuario VARCHAR(50),
    @ctip_cuenta VARCHAR(5),
    @ntot_desct DECIMAL(18,4),
    @ntot_impbruto DECIMAL(18,4),
    @ntot_igv DECIMAL(18,4),
    @ntot_impneto DECIMAL(18,4),
    @id_cbcuenta INT OUTPUT
AS BEGIN SET NOCOUNT ON;
    IF NULLIF(@ccod_coa,'') IS NULL SET @ccod_coa='CLI000';
    IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa)
        SET @ccod_coa='CLI000';

    INSERT INTO CbCuenta(ccod_cia,ccod_coa,ccod_tiend,ccod_caja,etiqueta,ccod_usuario,ctip_cuenta,ntot_desct,ntot_impbruto,ntot_igv,ntot_impneto)
    VALUES(@ccod_cia,@ccod_coa,@ccod_tiend,@ccod_caja,@etiqueta,@ccod_usuario,@ctip_cuenta,@ntot_desct,@ntot_impbruto,@ntot_igv,@ntot_impneto);
    SET @id_cbcuenta=SCOPE_IDENTITY();
END
GO

PRINT 'OK - FIX 46 Ventas CRUD final aplicado.';
GO
