/* =========================================================================
   SEED_02 — Datos de prueba completos para Ventas y Almacén (20 Documentos)
   =========================================================================

   ¿Para qué sirve este script?
   ─────────────────────────────────────────────────────────────────────────
   Este script pobla la base de datos con un conjunto completo, coherente
   y realista de datos de prueba distribuidos en los últimos 25 días para
   que puedas probar todas las pantallas y consultas del sistema sin ningún
   error de llave foránea (Foreign Key) o registro duplicado.
   
   Características especiales:
   - Deshabilita temporalmente las restricciones de llave foránea para
     realizar una limpieza segura y profunda de datos de prueba previos.
   - Corrige los tipos de transacción a los códigos reales autorizados en
     TipoOperacion ('COMP', 'VENT', 'AJER', 'TRAN') para evitar conflictos.
   - Inserta un usuario administrador con las credenciales de acceso correctas.
   - Popula CbCobranza y LnCobranza para probar el módulo de cobranzas / formas de pago.
   - Configura el cliente por defecto como 'CLI000' para coincidir con la lógica
     del backend PHP, evitando errores de FK_CbFact_Coa durante nuevas ventas.
   - Genera exactamente 20 documentos de venta (Boletas 'BV' y Facturas 'FV')
     distribuidos en el tiempo para tener un historial de reportes robusto.
   - Utiliza los códigos de categoría / familia originales ('FAM001', 'FAM002', 'FAM003')
     para que las imágenes por defecto y relaciones de familias del frontend coincidan perfectamente.
   - Corrige los códigos de tipo de documento a los reales del frontend ('BV' y 'FV')
     en lugar de los antiguos ('B' y 'F') para que los filtros de documentos coincidan.
   - Corrige los estados tributarios de facturas electrónicas a '1' (Pendiente)
     y '4' (Aceptado) para que aparezcan correctamente en Consultas Tributarias / SUNAT.
   - Re-habilita y valida todas las restricciones al finalizar la carga.

   Instrucciones de ejecución:
   ─────────────────────────────────────────────────────────────────────────
   Ejecutar en la base de datos DatPos_EMP01.
   ========================================================================= */

USE DatPos_EMP01;
GO

PRINT '=== SEED_02: Iniciando carga de datos de prueba ===';

/* =========================================================================
   PASO 0: Deshabilitar temporalmente las llaves foráneas para limpieza segura
   ========================================================================= */
PRINT '-> Deshabilitando constraints temporalmente para limpieza segura...';
EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT ALL";
GO

PRINT '-> Limpiando tablas transaccionales y maestras de EMP01...';
DELETE FROM LnCobranza WHERE ccod_cia = 'EMP01';
DELETE FROM CbCobranza WHERE ccod_cia = 'EMP01';
DELETE FROM CbGuia WHERE ccod_cia = 'EMP01';
DELETE FROM LnCuenta WHERE ccod_cia = 'EMP01';
DELETE FROM CbCuenta WHERE ccod_cia = 'EMP01';
DELETE FROM LnFactura WHERE ccod_cia = 'EMP01';
DELETE FROM CbFactura WHERE ccod_cia = 'EMP01';
DELETE FROM LnInventario WHERE ccod_cia = 'EMP01';
DELETE FROM CbInventario WHERE ccod_cia = 'EMP01';
DELETE FROM Stock WHERE ccod_cia = 'EMP01';
DELETE FROM Turno WHERE ccod_cia = 'EMP01';
DELETE FROM LnListaPrecio WHERE ccod_cia = 'EMP01';
DELETE FROM CbListaPrecio WHERE ccod_cia = 'EMP01';
DELETE FROM Articulos WHERE ccod_cia = 'EMP01';
DELETE FROM Coa WHERE ccod_cia = 'EMP01';
DELETE FROM Familias WHERE ccod_cia = 'EMP01';
DELETE FROM UnidadMedida WHERE ccod_cia = 'EMP01';
DELETE FROM NumeradorCaja WHERE ccod_cia = 'EMP01';
DELETE FROM NumeradorAlmacen WHERE ccod_cia = 'EMP01';
DELETE FROM TiendaAlmacen WHERE ccod_cia = 'EMP01';
DELETE FROM TiendaCaja WHERE ccod_cia = 'EMP01';
DELETE FROM Usuarios WHERE ccod_empresa = 'EMP01';
DELETE FROM Tiendas WHERE ccod_cia = 'EMP01';
DELETE FROM Almacenes WHERE ccod_cia = 'EMP01';
DELETE FROM Cajas WHERE ccod_cia = 'EMP01';
DELETE FROM TipoOperacion WHERE ccod_cia = 'EMP01';
GO

/* =========================================================================
   PASO 1: Tipos de Operación Base (REQUERIDO para CbInventario)
   ========================================================================= */
PRINT '-> Insertando Tipos de Operación Autorizados...';
INSERT INTO TipoOperacion (ccod_cia, ccod_tipoper, cdsc_tipoper, ctipo_flag, cstatus, ccod_usuario) VALUES 
('EMP01', 'COMP', 'COMPRA / INGRESO', 'I', 'A', 'ADMIN'),
('EMP01', 'AJIN', 'AJUSTE DE INGRESO', 'I', 'A', 'ADMIN'),
('EMP01', 'VENT', 'VENTA / SALIDA', 'S', 'A', 'ADMIN'),
('EMP01', 'AJER', 'AJUSTE DE EGRESO', 'S', 'A', 'ADMIN'),
('EMP01', 'TRAN', 'TRANSFERENCIA ENTRE ALMACENES', 'T', 'A', 'ADMIN');
GO

/* =========================================================================
   PASO 2: Tiendas, Almacenes y Cajas (Demo y Secundarias para filtros)
   ========================================================================= */
PRINT '-> Insertando Tiendas, Almacenes y Cajas...';

-- Almacenes
INSERT INTO Almacenes (ccod_cia, ccod_alm, cdsc_alm, cstatus) VALUES 
('EMP01', 'ALM01', 'ALMACEN PRINCIPAL', 'A'),
('EMP01', 'ALM02', 'ALMACEN SECUNDARIO', 'A');

-- Tiendas
INSERT INTO Tiendas (ccod_cia, ccod_tiend, cnombr, cdirec, cstatus, nlista_pre_normal, nlista_pre_preferencial) VALUES 
('EMP01', 'T001', 'TIENDA PRINCIPAL', 'AV. PRINCIPAL 123', 'A', 1, 1),
('EMP01', 'T002', 'TIENDA SUCURSAL EXPRESS', 'AV. SUCURSAL 456', 'A', 1, 1);

-- Cajas
INSERT INTO Cajas (ccod_cia, ccod_caja, cdsc_caja, cstatus) VALUES 
('EMP01', 'CAJ01', 'CAJA PRINCIPAL 01', 'A'),
('EMP01', 'CAJ02', 'CAJA SECUNDARIA 02', 'A');

-- Relaciones Tiendas/Almacenes/Cajas
INSERT INTO TiendaAlmacen (ccod_cia, ccod_tiend, ccod_alm) VALUES 
('EMP01', 'T001', 'ALM01'),
('EMP01', 'T001', 'ALM02'),
('EMP01', 'T002', 'ALM02');

INSERT INTO TiendaCaja (ccod_cia, ccod_tiend, ccod_caja, ccod_usuario) VALUES 
('EMP01', 'T001', 'CAJ01', 'ADMIN'),
('EMP01', 'T002', 'CAJ02', 'ADMIN');

-- Numeradores de Caja (BV=Boleta de Venta, FV=Factura de Venta, T=Ticket)
INSERT INTO NumeradorCaja (ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer) VALUES 
('EMP01', 'CAJ01', 'BV', 'B001', 50, 'BOLETA DE VENTA'),
('EMP01', 'CAJ01', 'FV', 'F001', 20, 'FACTURA DE VENTA'),
('EMP01', 'CAJ01', 'T', 'T001', 40, 'TICKET'),
('EMP01', 'CAJ02', 'BV', 'B002', 10, 'BOLETA DE VENTA'),
('EMP01', 'CAJ02', 'FV', 'F002', 5, 'FACTURA DE VENTA');

-- Numeradores de Almacén (I=Ingreso, S=Salida, T=Transferencia)
INSERT INTO NumeradorAlmacen (ccod_cia, ccod_alm, ctip_doc, cserie, nnumero, cdsc_numeralmacen, ccod_usuario) VALUES 
('EMP01', 'ALM01', 'I', 'ALM', 10, 'Num Ingreso', 'ADMIN'),
('EMP01', 'ALM01', 'S', 'ALM', 5, 'Num Salida', 'ADMIN'),
('EMP01', 'ALM01', 'T', 'ALM', 2, 'Num Transf', 'ADMIN'),
('EMP01', 'ALM02', 'I', 'ALM', 2, 'Num Ingreso', 'ADMIN'),
('EMP01', 'ALM02', 'S', 'ALM', 1, 'Num Salida', 'ADMIN'),
('EMP01', 'ALM02', 'T', 'ALM', 1, 'Num Transf', 'ADMIN');
GO

/* =========================================================================
   PASO 3: Unidades de Medida y Familias (Usando códigos originales del sistema)
   ========================================================================= */
PRINT '-> Insertando Unidades de Medida y Familias...';

INSERT INTO UnidadMedida (ccod_cia, ccod_unimed, cdsc_unimed, cstatus) VALUES 
('EMP01', 'UND', 'UNIDADES', 'A'),
('EMP01', 'KGM', 'KILOGRAMOS', 'A'),
('EMP01', 'LTR', 'LITROS', 'A');

INSERT INTO Familias (ccod_cia, ccod_lin, cdsc_lin, ccolor, cstatus) VALUES 
('EMP01', 'FAM001', 'BEBIDAS', '#2ecc71', 'A'),
('EMP01', 'FAM002', 'ABARROTES / COMIDAS', '#e67e22', 'A'),
('EMP01', 'FAM003', 'SNACKS / PIQUEOS', '#e74c3c', 'A');
GO

/* =========================================================================
   PASO 4: Clientes y Proveedores (COA) - Sincronizado con CLI000 por defecto
   ========================================================================= */
PRINT '-> Insertando Clientes y Proveedores...';

-- Clientes (cproveedor = '0')
-- NOTA: CLI000 es requerido por el backend PHP como fallback del Consumidor Final
INSERT INTO Coa (ccod_cia, ccod_coa, cdoc_coa, cdsc_coa, ctipo_coa, cstatus, cproveedor, cdirc_coa, cruc_coa) VALUES 
('EMP01', 'CLI000', '00000000', 'CONSUMIDOR FINAL', '1', 'A', '0', 'SIN DIRECCION', '00000000'),
('EMP01', 'CLI002', '44445555', 'JUAN PEREZ GONZALES', '1', 'A', '0', 'CALLE LAS FLORES 123', '44445555'),
('EMP01', 'CLI003', '20456789123', 'CONSTRUCTORA ANDINA S.A.C.', '6', 'A', '0', 'AV. INDUSTRIAL 789', '20456789123');

-- Proveedores (cproveedor = '1')
INSERT INTO Coa (ccod_cia, ccod_coa, cdoc_coa, cdsc_coa, ctipo_coa, cstatus, cproveedor, cdirc_coa, cruc_coa) VALUES 
('EMP01', 'PROV01', '20112233445', 'DISTRIBUIDORA DE BEBIDAS S.A.', '6', 'A', '1', 'AV. LOS ALISOS 456', '20112233445'),
('EMP01', 'PROV02', '20998877665', 'ALIMENTOS DEL PERU S.A.', '6', 'A', '1', 'JIRON PROGRESO 890', '20998877665');
GO

/* =========================================================================
   PASO 5: Artículos y Listas de Precios (Vinculados a códigos originales de familia)
   ========================================================================= */
PRINT '-> Insertando Artículos y Precios...';

-- Artículos (iimage se inserta en NULL porque el sistema carga su marcador de posición por defecto)
INSERT INTO Articulos (ccod_cia, ccod_articulo, cdsc_articulo, ccod_lin, uni_medi, ctip_articulo, cstatus, cigv, bprefer, nstock_min, nstock_max) VALUES 
('EMP01', 'ART001', 'COCA COLA 500ML', 'FAM001', 'UND', 'P', 'A', '18', 1, 10, 100),
('EMP01', 'ART002', 'INCA KOLA 500ML', 'FAM001', 'UND', 'P', 'A', '18', 1, 10, 100),
('EMP01', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'FAM001', 'UND', 'P', 'A', '18', 0, 5, 50),
('EMP01', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FAM002', 'KGM', 'P', 'A', '18', 0, 15, 200),
('EMP01', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'FAM003', 'UND', 'P', 'A', '18', 0, 8, 80);

-- Lista de Precios
INSERT INTO CbListaPrecio (ccod_cia, ccod_cblistpre, cdsc_cblistpre, cstatus) VALUES 
('EMP01', 'LP001', 'LISTA DE PRECIO GENERAL', 'A'),
('EMP01', 'LP002', 'LISTA DE PRECIO MAYORISTA', 'A');

INSERT INTO LnListaPrecio (ccod_cia, ccod_cblistpre, ccod_articulo, npre_uni, ndes_max, ndes_min) VALUES 
('EMP01', 'LP001', 'ART001', 3.50, 10, 0),
('EMP01', 'LP001', 'ART002', 3.50, 10, 0),
('EMP01', 'LP001', 'ART003', 2.20, 5, 0),
('EMP01', 'LP001', 'ART004', 5.80, 8, 0),
('EMP01', 'LP001', 'ART005', 11.50, 15, 0),

('EMP01', 'LP002', 'ART001', 3.00, 10, 0),
('EMP01', 'LP002', 'ART002', 3.00, 10, 0),
('EMP01', 'LP002', 'ART003', 1.80, 5, 0),
('EMP01', 'LP002', 'ART004', 5.00, 8, 0),
('EMP01', 'LP002', 'ART005', 10.00, 15, 0);

-- Configuración General
INSERT INTO ConfigGeneral (ccod_cia, ccod_clibol, coper_ingreso, coper_salida, ctipo_flag_ingreso, ctipo_flag_salida, nigv, nisc, nmonto_maxboleta, ccod_usuario)
VALUES ('EMP01', 'CLI000', 'COMP', 'VENT', 'I', 'S', 18, 0, 700, 'ADMIN');
GO

/* =========================================================================
   PASO 6: Turnos de Caja (Activos e Históricos)
   ========================================================================= */
PRINT '-> Insertando Turnos de Caja...';

-- Turno 1 (Hace 15 días - Cerrado)
INSERT INTO Turno (ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin, ntot_entreg, ndiferencia, dfchdoc_ini, dfchdoc_fin, cstatus) VALUES 
('EMP01', 'T001', 'ADMIN', 'CAJ01', 100.00, 718.70, 718.70, 0.00, DATEADD(DAY, -15, GETDATE()), DATEADD(DAY, -15, GETDATE()), 'C');
DECLARE @id_turno1 INT = SCOPE_IDENTITY();

-- Turno 2 (Hace 5 días - Cerrado)
INSERT INTO Turno (ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin, ntot_entreg, ndiferencia, dfchdoc_ini, dfchdoc_fin, cstatus) VALUES 
('EMP01', 'T001', 'ADMIN', 'CAJ01', 100.00, 526.60, 520.00, -6.60, DATEADD(DAY, -5, GETDATE()), DATEADD(DAY, -5, GETDATE()), 'C');
DECLARE @id_turno2 INT = SCOPE_IDENTITY();

-- Turno 3 (Hoy - Abierto/Activo)
INSERT INTO Turno (ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin, ntot_entreg, ndiferencia, dfchdoc_ini, dfchdoc_fin, cstatus) VALUES 
('EMP01', 'T001', 'ADMIN', 'CAJ01', 150.00, 0.00, 0.00, 0.00, GETDATE(), NULL, 'A');
DECLARE @id_turno3 INT = SCOPE_IDENTITY();
GO

/* =========================================================================
   PASO 7: Movimientos de Almacén Históricos (Compras e Ingresos directos)
   ========================================================================= */
PRINT '-> Insertando Movimientos de Almacén (Ingresos, Salidas, Transferencias)...';

-- Movimiento 1 (Ingreso COMP - Compra hace 25 días): Carga inicial de stock
INSERT INTO CbInventario (ccod_cia, ccod_tienda, ccod_alm, dfecha, ctipo, vserie, nnumero, vobservacion, ccod_usuario, ccod_coa, ntotal) VALUES 
('EMP01', 'T001', 'ALM01', DATEADD(DAY, -25, GETDATE()), 'COMP', 'F001', 1, 'COMPRA INICIAL DE MERCADERIA', 'ADMIN', 'PROV01', 2500.00);
DECLARE @id_inve1 INT = SCOPE_IDENTITY();

INSERT INTO LnInventario (ccod_cia, id_cbinve, ccod_articulo, ccod_artSunat, cdsc_articulo, ncantidad, ncosto, ccod_alm, ccod_usuario) VALUES 
('EMP01', @id_inve1, 'ART001', '50201706', 'COCA COLA 500ML', 500.0000, 2.0000, 'ALM01', 'ADMIN'),
('EMP01', @id_inve1, 'ART002', '50201706', 'INCA KOLA 500ML', 500.0000, 2.0000, 'ALM01', 'ADMIN'),
('EMP01', @id_inve1, 'ART003', '50201706', 'AGUA MINERAL SAN LUIS 1L', 200.0000, 1.0000, 'ALM01', 'ADMIN');

-- Movimiento 2 (Ingreso COMP - Compra hace 20 días)
INSERT INTO CbInventario (ccod_cia, ccod_tienda, ccod_alm, dfecha, ctipo, vserie, nnumero, vobservacion, ccod_usuario, ccod_coa, ntotal) VALUES 
('EMP01', 'T001', 'ALM01', DATEADD(DAY, -20, GETDATE()), 'COMP', 'F002', 12, 'COMPRA SNACKS Y COMIDA', 'ADMIN', 'PROV02', 1900.00);
DECLARE @id_inve2 INT = SCOPE_IDENTITY();

INSERT INTO LnInventario (ccod_cia, id_cbinve, ccod_articulo, ccod_artSunat, cdsc_articulo, ncantidad, ncosto, ccod_alm, ccod_usuario) VALUES 
('EMP01', @id_inve2, 'ART004', '50101509', 'ARROZ EXTRA COSTEÑO 1KG', 200.0000, 3.5000, 'ALM01', 'ADMIN'),
('EMP01', @id_inve2, 'ART005', '50192109', 'PAPAS PRINGLES ORIGINAL 124G', 300.0000, 6.0000, 'ALM01', 'ADMIN');

-- Movimiento 3 (Salida AJER - Ajuste de desmedro hace 12 días)
INSERT INTO CbInventario (ccod_cia, ccod_tienda, ccod_alm, dfecha, ctipo, vserie, nnumero, vobservacion, ccod_usuario, ntotal) VALUES 
('EMP01', 'T001', 'ALM01', DATEADD(DAY, -12, GETDATE()), 'AJER', 'AJU', 1, 'PRODUCTOS ROTADOS / CADUCADOS', 'ADMIN', 22.00);
DECLARE @id_inve3 INT = SCOPE_IDENTITY();

INSERT INTO LnInventario (ccod_cia, id_cbinve, ccod_articulo, ccod_artSunat, cdsc_articulo, ncantidad, ncosto, ccod_alm, ccod_usuario) VALUES 
('EMP01', @id_inve3, 'ART001', '50201706', 'COCA COLA 500ML', 5.0000, 2.0000, 'ALM01', 'ADMIN'),
('EMP01', @id_inve3, 'ART005', '50192109', 'PAPAS PRINGLES ORIGINAL 124G', 2.0000, 6.0000, 'ALM01', 'ADMIN');

-- Movimiento 4 (Transferencia TRAN - Transferencia a Sucursal hace 7 días)
INSERT INTO CbInventario (ccod_cia, ccod_tienda, ccod_alm, dfecha, ctipo, vserie, nnumero, vobservacion, ccod_usuario, ntotal) VALUES 
('EMP01', 'T001', 'ALM01', DATEADD(DAY, -7, GETDATE()), 'TRAN', 'TRA', 1, 'REABASTECIMIENTO ALMACEN SECUNDARIO', 'ADMIN', 200.00);
DECLARE @id_inve4 INT = SCOPE_IDENTITY();

INSERT INTO LnInventario (ccod_cia, id_cbinve, ccod_articulo, ccod_artSunat, cdsc_articulo, ncantidad, ncosto, ccod_alm, ccod_alm_ingreso, ccod_usuario) VALUES 
('EMP01', @id_inve4, 'ART001', '50201706', 'COCA COLA 500ML', 50.0000, 2.0000, 'ALM01', 'ALM02', 'ADMIN'),
('EMP01', @id_inve4, 'ART002', '50201706', 'INCA KOLA 500ML', 50.0000, 2.0000, 'ALM01', 'ALM02', 'ADMIN');
GO

/* =========================================================================
   PASO 8: Ventas / Facturación (20 Documentos en total)
   ========================================================================= */
PRINT '-> Insertando Historial de Ventas y Cobranzas (20 Invoices)...';

-- Obtener dinámicamente los IDs de los turnos recién creados
DECLARE @id_t1 INT, @id_t2 INT, @id_t3 INT;
SELECT @id_t1 = MIN(id_turno) FROM Turno WHERE ccod_cia = 'EMP01';
SELECT @id_t2 = MIN(id_turno) FROM Turno WHERE ccod_cia = 'EMP01' AND id_turno > @id_t1;
SELECT @id_t3 = MAX(id_turno) FROM Turno WHERE ccod_cia = 'EMP01';

-- DECLARAR VARIABLES PARA DETALLES DE VENTA
DECLARE @id_f INT, @id_c INT;

-- -------------------------------------------------------------------------
-- GRUPO 1: Ventas en Turno 1 (Hace 15-20 días, cstatus_tributario='4' Aceptado)
-- -------------------------------------------------------------------------

-- VENTA 1: Boleta BV-00000001 (Hace 20 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 1, 'CLI000', 1.40, 0.00, 0.00, 9.20, 7.80, 0.80, 10.00, 2, @id_t1, 5.00, 'VENTA 01', 'P', '4', DATEADD(DAY, -20, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 2.00, 7.00, 1.07, 0.00, 0.00, 7.00, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 1.00, 2.20, 0.33, 0.00, 0.00, 2.20, 2, 'ADMIN', 'ALM01', 1.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 9.20, 10.00, 0.80, DATEADD(DAY, -20, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 9.20, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -20, GETDATE()));

-- VENTA 2: Factura FV-00000001 (Hace 19 días) - CLI003
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'FV', 'F001', 1, 'CLI003', 12.36, 0.00, 0.00, 81.00, 68.64, 0.00, 81.00, 2, @id_t1, 47.00, 'VENTA 02', 'P', '4', DATEADD(DAY, -19, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FV', 5.80, 10.00, 58.00, 8.85, 0.00, 0.00, 58.00, 1, 'ADMIN', 'ALM01', 3.50),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'FV', 11.50, 2.00, 23.00, 3.51, 0.00, 0.00, 23.00, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 81.00, 81.00, 0.00, DATEADD(DAY, -19, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 81.00, '441223', '4111********2233', 'VISA', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -19, GETDATE()));

-- VENTA 3: Boleta BV-00000002 (Hace 18 días) - CLI002
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 2, 'CLI002', 4.42, 0.00, 0.00, 29.00, 24.58, 1.00, 30.00, 2, @id_t1, 16.00, 'VENTA 03', 'P', '4', DATEADD(DAY, -18, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 5.00, 17.50, 2.67, 0.00, 0.00, 17.50, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'BV', 11.50, 1.00, 11.50, 1.75, 0.00, 0.00, 11.50, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 29.00, 30.00, 1.00, DATEADD(DAY, -18, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 29.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -18, GETDATE()));

-- VENTA 4: Boleta BV-00000003 (Hace 17 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 3, 'CLI000', 1.07, 0.00, 0.00, 7.00, 5.93, 3.00, 10.00, 2, @id_t1, 4.00, 'VENTA 04', 'P', '4', DATEADD(DAY, -17, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 1.00, 3.50, 0.53, 0.00, 0.00, 3.50, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 1.00, 3.50, 0.53, 0.00, 0.00, 3.50, 2, 'ADMIN', 'ALM01', 2.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 7.00, 10.00, 3.00, DATEADD(DAY, -17, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 7.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -17, GETDATE()));

-- VENTA 5: Factura FV-00000002 (Hace 16 días) - CLI003
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'FV', 'F001', 2, 'CLI003', 6.18, 0.00, 0.00, 40.50, 34.32, 0.00, 40.50, 2, @id_t1, 23.50, 'VENTA 05', 'P', '4', DATEADD(DAY, -16, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FV', 5.80, 5.00, 29.00, 4.42, 0.00, 0.00, 29.00, 1, 'ADMIN', 'ALM01', 3.50),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'FV', 11.50, 1.00, 11.50, 1.75, 0.00, 0.00, 11.50, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 40.50, 40.50, 0.00, DATEADD(DAY, -16, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 40.50, '552309', '5412********1100', 'MASTERCARD', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -16, GETDATE()));

-- VENTA 6: Boleta BV-00000004 (Hace 15 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 4, 'CLI000', 2.82, 0.00, 0.00, 18.50, 15.68, 1.50, 20.00, 2, @id_t1, 10.00, 'VENTA MOSTRADOR', 'P', '4', DATEADD(DAY, -15, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 2.00, 7.00, 1.07, 0.00, 0.00, 7.00, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'BV', 11.50, 1.00, 11.50, 1.75, 0.00, 0.00, 11.50, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 18.50, 20.00, 1.50, DATEADD(DAY, -15, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 18.50, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -15, GETDATE()));

-- VENTA 7: Factura FV-00000003 (Hace 14 días) - CLI003
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'FV', 'F001', 3, 'CLI003', 52.86, 0.00, 0.00, 346.50, 293.64, 0.00, 346.50, 2, @id_t1, 195.00, 'ATENCION OFICINA', 'P', '4', DATEADD(DAY, -14, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FV', 5.80, 30.00, 174.00, 26.54, 0.00, 0.00, 174.00, 1, 'ADMIN', 'ALM01', 3.50),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'FV', 11.50, 15.00, 172.50, 26.31, 0.00, 0.00, 172.50, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 346.50, 346.50, 0.00, DATEADD(DAY, -14, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 346.50, '334102', '4557********0041', 'VISA', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -14, GETDATE()));

-- VENTA 8: Boleta BV-00000005 (Hace 13 días) - CLI002
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 5, 'CLI002', 6.03, 0.00, 0.00, 39.50, 33.47, 0.50, 40.00, 2, @id_t1, 20.00, 'VENTA 08', 'P', '4', DATEADD(DAY, -13, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 10.00, 22.00, 3.36, 0.00, 0.00, 22.00, 1, 'ADMIN', 'ALM01', 1.00),
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 5.00, 17.50, 2.67, 0.00, 0.00, 17.50, 2, 'ADMIN', 'ALM01', 2.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 39.50, 40.00, 0.50, DATEADD(DAY, -13, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 39.50, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -13, GETDATE()));

-- VENTA 9: Boleta BV-00000006 (Hace 12 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 6, 'CLI000', 2.81, 0.00, 0.00, 18.40, 15.59, 1.60, 20.00, 2, @id_t1, 10.00, 'VENTA 09', 'P', '4', DATEADD(DAY, -12, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 4.00, 14.00, 2.14, 0.00, 0.00, 14.00, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 2.00, 4.40, 0.67, 0.00, 0.00, 4.40, 2, 'ADMIN', 'ALM01', 1.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 18.40, 20.00, 1.60, DATEADD(DAY, -12, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 18.40, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -12, GETDATE()));

-- VENTA 10: Factura FV-00000004 (Hace 11 días) - CLI003
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'FV', 'F001', 4, 'CLI003', 17.69, 0.00, 0.00, 116.00, 98.31, 0.00, 116.00, 1, @id_t1, 70.00, 'VENTA 10', 'P', '4', DATEADD(DAY, -11, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FV', 5.80, 20.00, 116.00, 17.69, 0.00, 0.00, 116.00, 1, 'ADMIN', 'ALM01', 3.50);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t1, 'T001', 'CAJ01', 'ADMIN', 116.00, 116.00, 0.00, DATEADD(DAY, -11, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 116.00, '410294', '4111********5500', 'VISA', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -11, GETDATE()));


-- -------------------------------------------------------------------------
-- GRUPO 2: Ventas en Turno 2 (Hace 3-10 días, cstatus_tributario='4' Aceptado)
-- -------------------------------------------------------------------------

-- VENTA 11: Boleta BV-00000007 (Hace 10 días) - CLI002
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 7, 'CLI002', 6.71, 0.00, 0.00, 44.00, 37.29, 6.00, 50.00, 2, @id_t2, 24.00, 'VENTA 11', 'P', '4', DATEADD(DAY, -10, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 6.00, 21.00, 3.20, 0.00, 0.00, 21.00, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'BV', 11.50, 2.00, 23.00, 3.51, 0.00, 0.00, 23.00, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 44.00, 50.00, 6.00, DATEADD(DAY, -10, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 44.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -10, GETDATE()));

-- VENTA 12: Boleta BV-00000008 (Hace 9 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 8, 'CLI000', 5.34, 0.00, 0.00, 35.00, 29.66, 5.00, 40.00, 1, @id_t2, 20.00, 'VENTA 12', 'P', '4', DATEADD(DAY, -9, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 10.00, 35.00, 5.34, 0.00, 0.00, 35.00, 1, 'ADMIN', 'ALM01', 2.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 35.00, 40.00, 5.00, DATEADD(DAY, -9, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 35.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -9, GETDATE()));

-- VENTA 13: Factura FV-00000005 (Hace 8 días) - CLI003
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'FV', 'F001', 5, 'CLI003', 22.04, 0.00, 0.00, 144.50, 122.46, 0.00, 144.50, 2, @id_t2, 82.50, 'VENTA 13', 'P', '4', DATEADD(DAY, -8, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FV', 5.80, 15.00, 87.00, 13.27, 0.00, 0.00, 87.00, 1, 'ADMIN', 'ALM01', 3.50),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'FV', 11.50, 5.00, 57.50, 8.77, 0.00, 0.00, 57.50, 2, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 144.50, 144.50, 0.00, DATEADD(DAY, -8, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 144.50, '229104', '5412********7788', 'MASTERCARD', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -8, GETDATE()));

-- VENTA 14: Boleta BV-00000009 (Hace 7 días) - CLI002
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 9, 'CLI002', 3.81, 0.00, 0.00, 25.00, 21.19, 5.00, 30.00, 2, @id_t2, 13.00, 'VENTA 14', 'P', '4', DATEADD(DAY, -7, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 5.00, 11.00, 1.68, 0.00, 0.00, 11.00, 1, 'ADMIN', 'ALM01', 1.00),
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 4.00, 14.00, 2.14, 0.00, 0.00, 14.00, 2, 'ADMIN', 'ALM01', 2.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 25.00, 30.00, 5.00, DATEADD(DAY, -7, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 25.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -7, GETDATE()));

-- VENTA 15: Boleta BV-00000010 (Hace 6 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 10, 'CLI000', 5.26, 0.00, 0.00, 34.50, 29.24, 5.50, 40.00, 1, @id_t2, 18.00, 'VENTA 15', 'P', '4', DATEADD(DAY, -6, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'BV', 11.50, 3.00, 34.50, 5.26, 0.00, 0.00, 34.50, 1, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 34.50, 40.00, 5.50, DATEADD(DAY, -6, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 34.50, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -6, GETDATE()));

-- VENTA 16: Boleta BV-00000011 (Hace 5 días) - CLI002
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 11, 'CLI002', 15.71, 0.00, 0.00, 103.00, 87.29, 7.00, 110.00, 3, @id_t2, 54.00, 'VENTA DELIVERY', 'P', '4', DATEADD(DAY, -5, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'BV', 3.50, 10.00, 35.00, 5.34, 0.00, 0.00, 35.00, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 10.00, 22.00, 3.36, 0.00, 0.00, 22.00, 2, 'ADMIN', 'ALM01', 1.00),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'BV', 11.50, 4.00, 46.00, 7.01, 0.00, 0.00, 46.00, 3, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 103.00, 110.00, 7.00, DATEADD(DAY, -5, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 103.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -5, GETDATE()));

-- VENTA 17: Factura FV-00000006 (Hace 4 días) - CLI003
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'FV', 'F001', 6, 'CLI003', 11.52, 0.00, 0.00, 75.50, 63.98, 0.00, 75.50, 2, @id_t2, 45.00, 'VENTA 17', 'P', '4', DATEADD(DAY, -4, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'FV', 5.80, 10.00, 58.00, 8.85, 0.00, 0.00, 58.00, 1, 'ADMIN', 'ALM01', 3.50),
('EMP01', @id_f, 'T001', 'ART001', 'COCA COLA 500ML', 'FV', 3.50, 5.00, 17.50, 2.67, 0.00, 0.00, 17.50, 2, 'ADMIN', 'ALM01', 2.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 75.50, 75.50, 0.00, DATEADD(DAY, -4, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 75.50, '119045', '4111********0011', 'VISA', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -4, GETDATE()));

-- VENTA 18: Boleta BV-00000012 (Hace 3 días) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 12, 'CLI000', 5.28, 0.00, 0.00, 34.60, 29.32, 5.40, 40.00, 2, @id_t2, 19.00, 'VENTA 18', 'P', '4', DATEADD(DAY, -3, GETDATE()));
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 8.00, 28.00, 4.27, 0.00, 0.00, 28.00, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 3.00, 6.60, 1.01, 0.00, 0.00, 6.60, 2, 'ADMIN', 'ALM01', 1.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t2, 'T001', 'CAJ01', 'ADMIN', 34.60, 40.00, 5.40, DATEADD(DAY, -3, GETDATE()));
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 34.60, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', DATEADD(DAY, -3, GETDATE()));


-- -------------------------------------------------------------------------
-- GRUPO 3: Ventas del Día de Hoy (Turno 3 - Abierto, cstatus_tributario='1' Pendiente)
-- -------------------------------------------------------------------------

-- VENTA 19: Boleta BV-00000013 (Hoy) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 13, 'CLI000', 6.10, 0.00, 0.00, 40.00, 33.90, 10.00, 50.00, 3, @id_t3, 21.00, 'VENTA RAPIDA', 'P', '1', GETDATE());
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART003', 'AGUA MINERAL SAN LUIS 1L', 'BV', 2.20, 5.00, 11.00, 1.68, 0.00, 0.00, 11.00, 1, 'ADMIN', 'ALM01', 1.00),
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 5.00, 17.50, 2.67, 0.00, 0.00, 17.50, 2, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART005', 'PAPAS PRINGLES ORIGINAL 124G', 'BV', 11.50, 1.00, 11.50, 1.75, 0.00, 0.00, 11.50, 3, 'ADMIN', 'ALM01', 6.00);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t3, 'T001', 'CAJ01', 'ADMIN', 40.00, 50.00, 10.00, GETDATE());
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 40.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', GETDATE());

-- VENTA 20: Boleta BV-00000014 (Hoy) - CLI000
INSERT INTO CbFactura (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario, cdoc, cserie, nnumero, ccod_coa, nimpuesto, nisc, ndescuento, ntotal, nsubtotal, nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo, cobs, cstatus, cstatus_tributario, fecha_emision) VALUES 
('EMP01', 'T001', 'CAJ01', 'ALM01', 'ADMIN', 'BV', 'B001', 14, 'CLI000', 2.44, 0.00, 0.00, 16.00, 13.56, 4.00, 20.00, 2, @id_t3, 9.50, 'VENTA EFECTIVO', 'P', '1', GETDATE());
SET @id_f = SCOPE_IDENTITY();
INSERT INTO LnFactura (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo, cdoc, nprecio, ncantidad, nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto, corden, ccod_usuario, ccod_almacen, ncosto) VALUES 
('EMP01', @id_f, 'T001', 'ART002', 'INCA KOLA 500ML', 'BV', 3.50, 3.00, 10.50, 1.60, 0.00, 0.00, 10.50, 1, 'ADMIN', 'ALM01', 2.00),
('EMP01', @id_f, 'T001', 'ART004', 'ARROZ EXTRA COSTEÑO 1KG', 'BV', 5.50, 1.00, 5.50, 0.84, 0.00, 0.00, 5.50, 2, 'ADMIN', 'ALM01', 3.50);
INSERT INTO CbCobranza (ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja, ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea) VALUES 
('EMP01', @id_f, @id_t3, 'T001', 'CAJ01', 'ADMIN', 16.00, 20.00, 4.00, GETDATE());
SET @id_c = SCOPE_IDENTITY();
INSERT INTO LnCobranza (ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto, cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea) VALUES 
('EMP01', @id_c, @id_f, 'T001', 16.00, '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01', GETDATE());
GO


-- -------------------------------------------------------------------------
-- PASO 9: VINCULAR VENTAS EN INVENTARIO (Usa ctipo='VENT' de acuerdo a TipoOperacion)
-- -------------------------------------------------------------------------
PRINT '-> Creando registros de salida en inventario (VENT) correspondientes a las ventas...';

-- Obtener todos los IDs de CbFactura creados dinámicamente para EMP01
DECLARE @ids TABLE (pos INT IDENTITY(1,1), id INT, cdoc VARCHAR(5), ntotal DECIMAL(18,4), ccod_coa VARCHAR(20), fecha DATETIME, costo DECIMAL(18,4), cserie VARCHAR(10));
INSERT INTO @ids (id, cdoc, ntotal, ccod_coa, fecha, costo, cserie)
SELECT id_cbfact, cdoc, ntotal, ccod_coa, fecha_emision, costo, cserie FROM CbFactura WHERE ccod_cia='EMP01' ORDER BY id_cbfact;

-- Declarar variables para recorrer
DECLARE @i INT = 1, @max INT;
SELECT @max = COUNT(*) FROM @ids;

DECLARE @curr_id INT, @curr_doc VARCHAR(5), @curr_tot DECIMAL(18,4), @curr_coa VARCHAR(20), @curr_fch DATETIME, @curr_cost DECIMAL(18,4), @curr_ser VARCHAR(10);
DECLARE @id_v INT;

WHILE @i <= @max
BEGIN
    SELECT 
        @curr_id  = id, 
        @curr_doc  = cdoc, 
        @curr_tot  = ntotal, 
        @curr_coa  = ccod_coa, 
        @curr_fch  = fecha,
        @curr_cost = costo,
        @curr_ser  = cserie
    FROM @ids WHERE pos = @i;

    -- Insertar Cabecera de Inventario VENT
    INSERT INTO CbInventario (ccod_cia, ccod_tienda, ccod_alm, dfecha, ctipo, vserie, nnumero, vobservacion, ccod_usuario, ccod_coa, ntotal) VALUES 
    ('EMP01', 'T001', 'ALM01', @curr_fch, 'VENT', @curr_ser, @i, 'VENTA GENERADA AUTOMATICAMENTE', 'ADMIN', @curr_coa, @curr_cost);
    SET @id_v = SCOPE_IDENTITY();

    -- Vincular CbFactura con este movimiento
    UPDATE CbFactura SET id_cbinve = @id_v WHERE id_cbfact = @curr_id;

    -- Insertar Detalle de Inventario (copia de LnFactura)
    INSERT INTO LnInventario (ccod_cia, id_cbinve, ccod_articulo, cdsc_articulo, ncantidad, ncosto, ccod_alm, ccod_usuario)
    SELECT ccod_cia, @id_v, id_articulo, cdsc_articulo, ncantidad, ncosto, ccod_almacen, ccod_usuario
    FROM LnFactura WHERE id_cbfact = @curr_id;

    SET @i = @i + 1;
END
GO


/* =========================================================================
   PASO 10: Saldos de Stock Coherente (Suma y resta matemática exacta para las 20 ventas)
   ========================================================================= */
PRINT '-> Calculando e Insertando Saldos de Stock Coherentes...';

-- ART001: Compra: +500 (ALM01), Desmedro: -5 (ALM01), Transfer: -50 (ALM01) a +50 (ALM02), Vendido: -35 (ALM01)
-- Net ALM01: 500 - 5 - 50 - 35 = 410
-- Net ALM02: 50
INSERT INTO Stock (ccod_cia, ccod_alm, ccod_articulo, ncantidad, ncosto) VALUES 
('EMP01', 'ALM01', 'ART001', 410.0000, 2.0000),
('EMP01', 'ALM02', 'ART001', 50.0000, 2.0000);

-- ART002: Compra: +500 (ALM01), Transfer: -50 (ALM01) a +50 (ALM02), Vendido: -36 (ALM01)
-- Net ALM01: 500 - 50 - 36 = 414
-- Net ALM02: 50
INSERT INTO Stock (ccod_cia, ccod_alm, ccod_articulo, ncantidad, ncosto) VALUES 
('EMP01', 'ALM01', 'ART002', 414.0000, 2.0000),
('EMP01', 'ALM02', 'ART002', 50.0000, 2.0000);

-- ART003: Compra: +200 (ALM01), Vendido: -36 (ALM01)
-- Net ALM01: 200 - 36 = 164
INSERT INTO Stock (ccod_cia, ccod_alm, ccod_articulo, ncantidad, ncosto) VALUES 
('EMP01', 'ALM01', 'ART003', 164.0000, 1.0000);

-- ART004: Compra: +200 (ALM01), Vendido: -91 (ALM01)
-- Net ALM01: 200 - 91 = 109
INSERT INTO Stock (ccod_cia, ccod_alm, ccod_articulo, ncantidad, ncosto) VALUES 
('EMP01', 'ALM01', 'ART004', 109.0000, 3.5000);

-- ART005: Compra: +300 (ALM01), Desmedro: -2 (ALM01), Vendido: -35 (ALM01)
-- Net ALM01: 300 - 2 - 35 = 263
INSERT INTO Stock (ccod_cia, ccod_alm, ccod_articulo, ncantidad, ncosto) VALUES 
('EMP01', 'ALM01', 'ART005', 263.0000, 6.0000);
GO

/* =========================================================================
   PASO 11: Usuario de Acceso ADMIN del Tenant
   ========================================================================= */
PRINT '-> Insertando Usuario de Acceso ADMIN del Tenant...';
INSERT INTO Usuarios(ccod_empresa,ccod_usuario,cdsc_usuario,cpassw,id_rol,ccod_tiend,ccod_almacen,ccod_caja,cperm_descn,id_estado)
VALUES('EMP01','ADMIN','ADMINISTRADOR','e10adc3949ba59abbe56e057f20f883e',1,'T001','ALM01','CAJ01','100',1);
-- cpassw = MD5('123456') = e10adc3949ba59abbe56e057f20f883e
GO

/* =========================================================================
   PASO 12: Re-habilitar todas las restricciones y llaves foráneas
   ========================================================================= */
PRINT '-> Re-habilitando constraints...';
EXEC sp_MSforeachtable "ALTER TABLE ? WITH CHECK CHECK CONSTRAINT ALL";
GO

/* =========================================================================
   PASO 13: Verificación de Resultados
   ========================================================================= */
PRINT '';
PRINT '=== VERIFICACIÓN DE DATOS INSERTADOS ===';
SELECT 'Almacenes creados' AS Concepto, COUNT(*) AS Cantidad FROM Almacenes WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Tiendas creadas', COUNT(*) FROM Tiendas WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Cajas creadas', COUNT(*) FROM Cajas WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Clientes/Proveedores', COUNT(*) FROM Coa WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Artículos insertados', COUNT(*) FROM Articulos WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Ventas registradas (Invoices)', COUNT(*) FROM CbFactura WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Cobranzas / Pagos registrados', COUNT(*) FROM CbCobranza WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Movimientos de Inventario', COUNT(*) FROM CbInventario WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Turnos de caja (Apertura)', COUNT(*) FROM Turno WHERE ccod_cia = 'EMP01'
UNION ALL
SELECT 'Registros de Stock total', COUNT(*) FROM Stock WHERE ccod_cia = 'EMP01';

PRINT '';
PRINT '✓ ¡LISTO! SEED_02 aplicado con éxito para DatPos_EMP01.';
PRINT '  Ahora tienes un conjunto robusto de 20 documentos distribuidos para tus reportes de ventas, caja y tributarios.';
PRINT '=========================================================================';
GO
