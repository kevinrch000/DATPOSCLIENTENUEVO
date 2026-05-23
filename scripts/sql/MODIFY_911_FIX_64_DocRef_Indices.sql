/* =====================================================================
   MODIFY_911_FIX_64_DocRef_Indices.sql
   ---------------------------------------------------------------------
   Fix #64 — Vista previa de Consulta Documento y Ver detalle de
   Consulta Formas de Pago no funcionaban.

   Causa raíz
   ----------
   - `sp_consultadatosdocref` (creado en MODIFY_910) solo poblaba los
     indices [0..3], [11], [16..17] y [27..33] del arreglo de salida.
     Los modales `ModalBuscarDoc` y `ArmarHtml` del front (Consulta
     Documento + Consulta Formas de Pago) leen tambien los indices
     [1], [4..10], [14..15], [17..20], [21..26]. Estaban vacios.

   - Por compatibilidad, el endpoint `DatosReferencia` del API debe
     enrutarse a este SP cuando se invoca con `id_cbinve = 0/""` y
     `id_cbfact > 0` (caso ConsultaDocumento/ConsultaFormasPago);
     cuando viene con `id_cbinve > 0` sigue usando el SP original
     `sp_datosreferencia` (caso ConsultaOperAlmacen).

   Que cambia
   ----------
   - Recreamos `sp_consultadatosdocref` para que devuelva los 34
     indices alineados con la lectura del JS.

   Como ejecutarlo
   ---------------
   USE DatPos_EMP01;
   :r MODIFY_911_FIX_64_DocRef_Indices.sql
   ===================================================================== */

USE DatPos_EMP01;
GO

PRINT '== MODIFY_911_FIX_64: sp_consultadatosdocref (indices completos) ==';
GO

IF OBJECT_ID('sp_consultadatosdocref','P') IS NOT NULL
    DROP PROCEDURE sp_consultadatosdocref;
GO

/* ──────────────────────────────────────────────────────────
   sp_consultadatosdocref
   Datos completos de la factura para los modales y la
   vista previa.

   Indices que lee el JS:
   ConsultaDocumento5.js  ModalBuscarDoc:
       [1]  cdoc           [2]  dfecha          [3]  ntotal
       [4]  ccod_tiend     [5]  cdsc_tiend
       [6]  ccod_alm       [7]  cdsc_alm
       [8]  ccod_usuario   [9]  cdsc_usuario
       [10] ccod_coa       [11] cdsc_coa
       [14] ntotal_inve    [15] cdoc_inve       [16] dfch_inve
       [17] ccod_caja      [18] cdsc_caja
       [19] ccod_tiend_inv [20] cdsc_tiend_inv
       [24] ccod_usuario_f [25] cdsc_usuario_f
       [26] cobs

   ConsultaFormaPago.js   ModalBuscarDoc:
       [1]  cdoc           [21] nvuelto         [22] ntot_entreg
       [23] comprobante (cserie-nnumero)

   ConsultaDocumento5.js  ArmarHtml:
       [2]  dfecha         [3]  ntotal          [11] cdsc_coa
       [27] cdoc_coa       [28] cdirc_coa
       [29] nsubtotal      [30] nimpuesto
       [31] cserie         [32] nnumero         [33] cdoc

   Cuando no hay movimiento de inventario asociado, los indices
   "_inve" devuelven los mismos valores de la factura (replica), de
   modo que el modal se rellena igualmente.
────────────────────────────────────────────────────────── */
CREATE PROCEDURE sp_consultadatosdocref
    @ccod_cia  VARCHAR(20),
    @id_cbfact VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @id INT = TRY_CAST(@id_cbfact AS INT);

    SELECT
        /*  0 */ ISNULL(CAST(F.id_cbfact AS VARCHAR(20)), '')                       AS c0,
        /*  1 */ ISNULL(F.cdoc, '')                                                  AS c1,
        /*  2 */ CONVERT(VARCHAR(10), F.fecha_emision, 103)                          AS c2,
        /*  3 */ ISNULL(CAST(F.ntotal AS VARCHAR(50)), '0')                          AS c3,
        /*  4 */ ISNULL(F.ccod_tiend, '')                                            AS c4,
        /*  5 */ ISNULL(T.cnombr, '')                                                AS c5,
        /*  6 */ ISNULL(F.ccod_almacen, '')                                          AS c6,
        /*  7 */ ISNULL(A.cdsc_alm, '')                                              AS c7,
        /*  8 */ ISNULL(F.ccod_usuario, '')                                          AS c8,
        /*  9 */ ISNULL(U.cdsc_usuario, '')                                          AS c9,
        /* 10 */ ISNULL(F.ccod_coa, '')                                              AS c10,
        /* 11 */ ISNULL(C.cdsc_coa, '')                                              AS c11,
        /* 12 */ CAST('' AS VARCHAR(50))                                             AS c12,
        /* 13 */ CAST('' AS VARCHAR(50))                                             AS c13,
        /* 14 */ ISNULL(CAST(F.ntotal AS VARCHAR(50)), '0')                          AS c14,
        /* 15 */ ISNULL(F.cdoc, '')                                                  AS c15,
        /* 16 */ CONVERT(VARCHAR(10), F.fecha_emision, 103)                          AS c16,
        /* 17 */ ISNULL(F.ccod_caja, '')                                             AS c17,
        /* 18 */ ISNULL(K.cdsc_caja, '')                                             AS c18,
        /* 19 */ ISNULL(F.ccod_tiend, '')                                            AS c19,
        /* 20 */ ISNULL(T.cnombr, '')                                                AS c20,
        /* 21 */ ISNULL(CAST(F.nvuelto AS VARCHAR(50)), '0')                         AS c21,
        /* 22 */ ISNULL(CAST(F.ntot_entreg AS VARCHAR(50)), '0')                     AS c22,
        /* 23 */ ISNULL(F.cserie, '') + '-' + ISNULL(CAST(F.nnumero AS VARCHAR(20)), '') AS c23,
        /* 24 */ ISNULL(F.ccod_usuario, '')                                          AS c24,
        /* 25 */ ISNULL(U.cdsc_usuario, '')                                          AS c25,
        /* 26 */ ISNULL(F.cobs, '')                                                  AS c26,
        /* 27 */ ISNULL(C.cdoc_coa, '')                                              AS c27,
        /* 28 */ ISNULL(C.cdirc_coa, '')                                             AS c28,
        /* 29 */ ISNULL(CAST(F.nsubtotal AS VARCHAR(50)), '0')                       AS c29,
        /* 30 */ ISNULL(CAST(F.nimpuesto AS VARCHAR(50)), '0')                       AS c30,
        /* 31 */ ISNULL(F.cserie, '')                                                AS c31,
        /* 32 */ ISNULL(CAST(F.nnumero AS VARCHAR(20)), '')                          AS c32,
        /* 33 */ ISNULL(F.cdoc, '')                                                  AS c33
    FROM CbFactura F
    LEFT JOIN Coa       C ON C.ccod_cia      = F.ccod_cia AND C.ccod_coa   = F.ccod_coa
    LEFT JOIN Tiendas   T ON T.ccod_cia      = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
    LEFT JOIN Almacenes A ON A.ccod_cia      = F.ccod_cia AND A.ccod_alm   = F.ccod_almacen
    LEFT JOIN Cajas     K ON K.ccod_cia      = F.ccod_cia AND K.ccod_caja  = F.ccod_caja
    LEFT JOIN Usuarios  U ON U.ccod_empresa  = F.ccod_cia AND U.ccod_usuario = F.ccod_usuario
    WHERE F.ccod_cia = @ccod_cia
      AND F.id_cbfact = @id;
END
GO
PRINT '  -> sp_consultadatosdocref OK (indices 0..33 completos)';
GO

-- Verificacion final
SELECT name
FROM sys.procedures
WHERE name = 'sp_consultadatosdocref';
GO
