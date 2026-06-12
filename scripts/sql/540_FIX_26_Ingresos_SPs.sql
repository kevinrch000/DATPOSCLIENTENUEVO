/* =====================================================================
   FIX 26 — SPs para Ingresos.aspx (IndexOutOfRange)
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. sp_consultararticulosactivos ──
   VB espera 5 cols: [0]ccod_articulo [1]cdsc_articulo [2]linea [3]uni_medi [4]estado
   ANTES: solo 4 cols (faltaba cstatus/estado) → IndexOutOfRange en [4]
*/
IF OBJECT_ID('sp_consultararticulosactivos','P') IS NOT NULL DROP PROCEDURE sp_consultararticulosactivos;
GO
CREATE PROCEDURE sp_consultararticulosactivos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_articulo,
           cdsc_articulo,
           ISNULL(ccod_lin,'') AS ccod_lin,
           ISNULL(uni_medi,'') AS uni_medi,
           cstatus AS estado
    FROM Articulos
    WHERE ccod_cia=@ccod_cia AND cstatus='A'
    ORDER BY cdsc_articulo;
END
GO
PRINT '✓ sp_consultararticulosactivos (5 cols)';
GO

/* ── 2. webDatpos_validarArticulo ──
   VB ValidarArticulo espera 3 cols: [0]ccod_articulo [1]cdsc_articulo [2]uni_medi
   ANTES: solo 1 col (ncantidad) → IndexOutOfRange en [1] y [2]
   NOTA: el DA envía @ccod_articulo y @ccod_cia (NO @ccod_alm)
         pero el SP original tenía @ccod_alm. Ajustamos.
*/
IF OBJECT_ID('webDatpos_validarArticulo','P') IS NOT NULL DROP PROCEDURE webDatpos_validarArticulo;
GO
CREATE PROCEDURE webDatpos_validarArticulo @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,
           A.cdsc_articulo,
           ISNULL(A.uni_medi,'') AS uni_medi
    FROM Articulos A
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo=@ccod_articulo AND A.cstatus='A';
END
GO
PRINT '✓ webDatpos_validarArticulo (3 cols)';
GO

/* ── 3. sp_consultarinventariodetalle ──
   VB espera 6 cols: [0]id_lninve [1]ccod_articulo [2]cdsc_articulo
                     [3]csim_unidadmedida [4]ncantidad [5]ncosto
   ANTES: 6 cols pero [3]=ncantidad [4]=ncosto [5]=nimporte (faltaba csim_unidadmedida)
*/
IF OBJECT_ID('sp_consultarinventariodetalle','P') IS NOT NULL DROP PROCEDURE sp_consultarinventariodetalle;
GO
CREATE PROCEDURE sp_consultarinventariodetalle @ccod_cia VARCHAR(20), @id INT
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve,
           L.ccod_articulo,
           ISNULL(A.cdsc_articulo,'') AS cdsc_articulo,
           ISNULL(A.uni_medi,'')     AS csim_unidadmedida,
           L.ncantidad,
           L.ncosto
    FROM LnInventario L
    LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=@id;
END
GO
PRINT '✓ sp_consultarinventariodetalle (6 cols con uni_medi)';
GO

/* ── 4. webDatpos_consultarInventarioDetalleSalida ──
   Mismo patrón: VB espera [3]=csim_unidadmedida
*/
IF OBJECT_ID('webDatpos_consultarInventarioDetalleSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarInventarioDetalleSalida;
GO
CREATE PROCEDURE webDatpos_consultarInventarioDetalleSalida @ccod_cia VARCHAR(20), @id INT
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve,
           L.ccod_articulo,
           ISNULL(A.cdsc_articulo,'') AS cdsc_articulo,
           ISNULL(A.uni_medi,'')     AS csim_unidadmedida,
           L.ncantidad,
           L.ncosto
    FROM LnInventario L
    LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=@id;
END
GO
PRINT '✓ webDatpos_consultarInventarioDetalleSalida (6 cols con uni_medi)';
GO

/* ── 5. webDatpos_consultarProveedor ── */
IF OBJECT_ID('webDatpos_consultarProveedor','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarProveedor;
GO
CREATE PROCEDURE webDatpos_consultarProveedor @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_coa, cdsc_coa
    FROM Coa
    WHERE ccod_cia=@ccod_cia AND cstatus='A'
    ORDER BY cdsc_coa;
END
GO
PRINT '✓ webDatpos_consultarProveedor (2 cols)';
GO

/* ── 6. sp_consultaringreso ──
   VB accede [0]id_cbinve [2]ccod_tienda [3]ccod_alm [4]dfecha
   [6]vserie [7]nnumero [8]ctipo [9]vobservacion [11]ccod_coa
   Necesita 12 columnas mínimo
*/
IF OBJECT_ID('sp_consultaringreso','P') IS NOT NULL DROP PROCEDURE sp_consultaringreso;
GO
CREATE PROCEDURE sp_consultaringreso @ccod_cia VARCHAR(20), @codigo VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT C.id_cbinve,                              -- [0]
           C.ccod_cia,                                -- [1]
           ISNULL(C.ccod_tienda,'') AS ccod_tienda,   -- [2]
           ISNULL(C.ccod_alm,'')   AS ccod_alm,       -- [3]
           C.dfecha,                                   -- [4]
           ISNULL(C.ctipo,'I')     AS ctipo_raw,       -- [5]
           ISNULL(C.vserie,'')     AS vserie,          -- [6]
           ISNULL(C.nnumero,0)     AS nnumero,         -- [7]
           ISNULL(C.ctipo,'I')     AS ctipo,            -- [8]
           ISNULL(C.vobservacion,'') AS vobservacion,  -- [9]
           ISNULL(C.ntotal,0)      AS ntotal,          -- [10]
           ISNULL(C.ccod_coa,'')   AS ccod_coa         -- [11]
    FROM CbInventario C
    WHERE C.ccod_cia=@ccod_cia AND C.id_cbinve=CAST(@codigo AS INT);
END
GO
PRINT '✓ sp_consultaringreso (12 cols para VB)';
GO

PRINT '═══════════════════════════════════════';
PRINT '  FIX 26 COMPLETO - Ingresos operativo';
PRINT '═══════════════════════════════════════';
GO
