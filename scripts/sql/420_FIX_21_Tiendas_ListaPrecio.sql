/* =====================================================================
   FIX 21 — Tiendas: ListaPreciosActivos necesita alias 'id_cblistpre'
   DataBind busca: DataTextField="cdsc_cblistpre", DataValueField="id_cblistpre"
   Pero SP retorna ccod_cblistpre, cdsc_cblistpre
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

IF OBJECT_ID('sp_consultarlistaspreciosactivos','P') IS NOT NULL DROP PROCEDURE sp_consultarlistaspreciosactivos;
GO
CREATE PROCEDURE sp_consultarlistaspreciosactivos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_cblistpre AS id_cblistpre, cdsc_cblistpre
    FROM CbListaPrecio
    WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

PRINT '✓ FIX 21: sp_consultarlistaspreciosactivos — alias id_cblistpre';
GO
