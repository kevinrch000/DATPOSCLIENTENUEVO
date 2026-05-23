/* =====================================================================
   FIX 39 — CREAR FOREIGN KEYS en DatPos_EMP01 y DatPosAdmin
   
   Establece todas las relaciones entre tablas del sistema DatPOS.
   
   PREREQUISITO: ejecutar 670_FIX_38 (id_rol del admin) antes de esto.
   
   Diagrama de relaciones (DatPos_EMP01):
   
   Roles ──────────────────┬──► Accesos
                           └──► Usuarios
   Tiendas ────────────────┬──► TiendaAlmacen
                           ├──► TiendaCaja
                           ├──► Usuarios
                           ├──► CbFactura
                           ├──► CbCobranza
                           ├──► CbCuenta
                           ├──► CbInventario
                           └──► Turno
   Almacenes ──────────────┬──► TiendaAlmacen
                           ├──► Usuarios
                           ├──► NumeradorAlmacen
                           ├──► Stock
                           ├──► CbFactura
                           ├──► LnFactura
                           ├──► CbInventario
                           ├──► LnInventario
                           └──► CbGuia
   Cajas ──────────────────┬──► TiendaCaja
                           ├──► Usuarios
                           ├──► NumeradorCaja
                           ├──► CbFactura
                           ├──► CbCobranza
                           ├──► CbCuenta
                           └──► Turno
   Familias ───────────────└──► Articulos
   UnidadMedida ───────────└──► Articulos
   Articulos ──────────────┬──► Stock
                           ├──► LnListaPrecio
                           ├──► CbVariante
                           ├──► LnFactura
                           ├──► LnCuenta
                           └──► LnInventario
   CbListaPrecio ──────────└──► LnListaPrecio
   CbVariante ─────────────└──► LnVariante
   Coa ────────────────────┬──► CbFactura
                           ├──► CbCuenta
                           └──► CbGuia
   Turno ──────────────────┬──► CbFactura
                           └──► CbCobranza
   CbFactura ──────────────┬──► LnFactura
                           └──► CbCobranza
   CbCobranza ─────────────└──► LnCobranza
   CbCuenta ───────────────└──► LnCuenta
   CbInventario ───────────┬──► LnInventario
                           └──► CbGuia (nullable)
   
   Diagrama (DatPosAdmin):
   Empresas ───────────────└──► Usuarios
   Roles ──────────────────┬──► Usuarios
                           └──► Accesos
   Menus ──────────────────└──► Accesos
   Estados ────────────────└──► Usuarios
   Departamentos ──────────└──► Provincias
   Provincias ─────────────└──► Distritos
===================================================================== */

/* ============================================================
   PARTE 1 — DatPos_EMP01
   ============================================================ */
USE DatPos_EMP01;
GO

PRINT '=== DatPos_EMP01: creando FKs ===';
GO

/* ----------------------------------------------------------
   BLOQUE 1: Roles (tabla maestra de roles del tenant)
   ---------------------------------------------------------- */

-- Accesos.id_rol → Roles.id_rol
IF OBJECT_ID('FK_Accesos_Roles','F') IS NULL
    ALTER TABLE Accesos ADD CONSTRAINT FK_Accesos_Roles
        FOREIGN KEY (id_rol) REFERENCES Roles(id_rol);
PRINT 'FK_Accesos_Roles OK';
GO

-- Usuarios.id_rol → Roles.id_rol
IF OBJECT_ID('FK_Usuarios_Roles','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usuarios_Roles
        FOREIGN KEY (id_rol) REFERENCES Roles(id_rol);
PRINT 'FK_Usuarios_Roles OK';
GO

/* ----------------------------------------------------------
   BLOQUE 2: Usuarios → Tiendas / Almacenes / Cajas
   ---------------------------------------------------------- */

-- Usuarios.(ccod_empresa, ccod_tiend) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_Usuarios_Tiendas','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usuarios_Tiendas
        FOREIGN KEY (ccod_empresa, ccod_tiend) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_Usuarios_Tiendas OK';
GO

-- Usuarios.(ccod_empresa, ccod_almacen) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_Usuarios_Almacenes','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usuarios_Almacenes
        FOREIGN KEY (ccod_empresa, ccod_almacen) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_Usuarios_Almacenes OK';
GO

-- Usuarios.(ccod_empresa, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_Usuarios_Cajas','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usuarios_Cajas
        FOREIGN KEY (ccod_empresa, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_Usuarios_Cajas OK';
GO

/* ----------------------------------------------------------
   BLOQUE 3: TiendaAlmacen / TiendaCaja
   ---------------------------------------------------------- */

-- TiendaAlmacen.(ccod_cia, ccod_tiend) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_TiendaAlm_Tiendas','F') IS NULL
    ALTER TABLE TiendaAlmacen ADD CONSTRAINT FK_TiendaAlm_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tiend) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_TiendaAlm_Tiendas OK';
GO

-- TiendaAlmacen.(ccod_cia, ccod_alm) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_TiendaAlm_Almacenes','F') IS NULL
    ALTER TABLE TiendaAlmacen ADD CONSTRAINT FK_TiendaAlm_Almacenes
        FOREIGN KEY (ccod_cia, ccod_alm) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_TiendaAlm_Almacenes OK';
GO

-- TiendaCaja.(ccod_cia, ccod_tiend) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_TiendaCaja_Tiendas','F') IS NULL
    ALTER TABLE TiendaCaja ADD CONSTRAINT FK_TiendaCaja_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tiend) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_TiendaCaja_Tiendas OK';
GO

-- TiendaCaja.(ccod_cia, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_TiendaCaja_Cajas','F') IS NULL
    ALTER TABLE TiendaCaja ADD CONSTRAINT FK_TiendaCaja_Cajas
        FOREIGN KEY (ccod_cia, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_TiendaCaja_Cajas OK';
GO

/* ----------------------------------------------------------
   BLOQUE 4: Numeradores → Cajas / Almacenes
   ---------------------------------------------------------- */

-- NumeradorCaja.(ccod_cia, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_NumerCaja_Cajas','F') IS NULL
    ALTER TABLE NumeradorCaja ADD CONSTRAINT FK_NumerCaja_Cajas
        FOREIGN KEY (ccod_cia, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_NumerCaja_Cajas OK';
GO

-- NumeradorAlmacen.(ccod_cia, ccod_alm) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_NumerAlm_Almacenes','F') IS NULL
    ALTER TABLE NumeradorAlmacen ADD CONSTRAINT FK_NumerAlm_Almacenes
        FOREIGN KEY (ccod_cia, ccod_alm) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_NumerAlm_Almacenes OK';
GO

/* ----------------------------------------------------------
   BLOQUE 5: Articulos → Familias / UnidadMedida
   ---------------------------------------------------------- */

-- Articulos.(ccod_cia, ccod_lin) → Familias.(ccod_cia, ccod_lin)
IF OBJECT_ID('FK_Articulos_Familias','F') IS NULL
    ALTER TABLE Articulos ADD CONSTRAINT FK_Articulos_Familias
        FOREIGN KEY (ccod_cia, ccod_lin) REFERENCES Familias(ccod_cia, ccod_lin);
PRINT 'FK_Articulos_Familias OK';
GO

-- Articulos.(ccod_cia, uni_medi) → UnidadMedida.(ccod_cia, ccod_unimed)
UPDATE A
SET uni_medi = 'NIU'
FROM Articulos A
WHERE NOT EXISTS (
    SELECT 1 FROM UnidadMedida U
    WHERE U.ccod_cia = A.ccod_cia AND U.ccod_unimed = A.uni_medi
);
GO
IF OBJECT_ID('FK_Articulos_UnidadMedida','F') IS NULL
    ALTER TABLE Articulos ADD CONSTRAINT FK_Articulos_UnidadMedida
        FOREIGN KEY (ccod_cia, uni_medi) REFERENCES UnidadMedida(ccod_cia, ccod_unimed);
PRINT 'FK_Articulos_UnidadMedida OK';
GO

/* ----------------------------------------------------------
   BLOQUE 6: Stock → Almacenes / Articulos
   ---------------------------------------------------------- */

-- Stock.(ccod_cia, ccod_alm) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_Stock_Almacenes','F') IS NULL
    ALTER TABLE Stock ADD CONSTRAINT FK_Stock_Almacenes
        FOREIGN KEY (ccod_cia, ccod_alm) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_Stock_Almacenes OK';
GO

-- Stock.(ccod_cia, ccod_articulo) → Articulos.(ccod_cia, ccod_articulo)
IF OBJECT_ID('FK_Stock_Articulos','F') IS NULL
    ALTER TABLE Stock ADD CONSTRAINT FK_Stock_Articulos
        FOREIGN KEY (ccod_cia, ccod_articulo) REFERENCES Articulos(ccod_cia, ccod_articulo);
PRINT 'FK_Stock_Articulos OK';
GO

/* ----------------------------------------------------------
   BLOQUE 7: Listas de Precio
   ---------------------------------------------------------- */

-- LnListaPrecio.(ccod_cia, ccod_cblistpre) → CbListaPrecio.(ccod_cia, ccod_cblistpre)
IF OBJECT_ID('FK_LnListaP_CbListaP','F') IS NULL
    ALTER TABLE LnListaPrecio ADD CONSTRAINT FK_LnListaP_CbListaP
        FOREIGN KEY (ccod_cia, ccod_cblistpre) REFERENCES CbListaPrecio(ccod_cia, ccod_cblistpre);
PRINT 'FK_LnListaP_CbListaP OK';
GO

-- LnListaPrecio.(ccod_cia, ccod_articulo) → Articulos.(ccod_cia, ccod_articulo)
IF OBJECT_ID('FK_LnListaP_Articulos','F') IS NULL
    ALTER TABLE LnListaPrecio ADD CONSTRAINT FK_LnListaP_Articulos
        FOREIGN KEY (ccod_cia, ccod_articulo) REFERENCES Articulos(ccod_cia, ccod_articulo);
PRINT 'FK_LnListaP_Articulos OK';
GO

/* ----------------------------------------------------------
   BLOQUE 8: Variantes
   ---------------------------------------------------------- */

-- CbVariante.(ccod_cia, ccod_articulo) → Articulos.(ccod_cia, ccod_articulo)
IF OBJECT_ID('FK_CbVariante_Articulos','F') IS NULL
    ALTER TABLE CbVariante ADD CONSTRAINT FK_CbVariante_Articulos
        FOREIGN KEY (ccod_cia, ccod_articulo) REFERENCES Articulos(ccod_cia, ccod_articulo);
PRINT 'FK_CbVariante_Articulos OK';
GO

-- LnVariante.id_cbvariante → CbVariante.id_cbvariante
IF OBJECT_ID('FK_LnVariante_CbVariante','F') IS NULL
    ALTER TABLE LnVariante ADD CONSTRAINT FK_LnVariante_CbVariante
        FOREIGN KEY (id_cbvariante) REFERENCES CbVariante(id_cbvariante);
PRINT 'FK_LnVariante_CbVariante OK';
GO

/* ----------------------------------------------------------
   BLOQUE 9: Turno → Tiendas / Cajas
   ---------------------------------------------------------- */

-- Turno.(ccod_cia, ccod_tienda) → Tiendas.(ccod_cia, ccod_tiend)
-- Nota: la columna se llama ccod_tienda (con 'a') en Turno pero ccod_tiend en Tiendas
IF OBJECT_ID('FK_Turno_Tiendas','F') IS NULL
    ALTER TABLE Turno ADD CONSTRAINT FK_Turno_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tienda) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_Turno_Tiendas OK';
GO

-- Turno.(ccod_cia, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_Turno_Cajas','F') IS NULL
    ALTER TABLE Turno ADD CONSTRAINT FK_Turno_Cajas
        FOREIGN KEY (ccod_cia, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_Turno_Cajas OK';
GO

/* ----------------------------------------------------------
   BLOQUE 10: CbInventario → Tiendas / Almacenes
   ---------------------------------------------------------- */

-- CbInventario.(ccod_cia, ccod_tienda) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_CbInve_Tiendas','F') IS NULL
    ALTER TABLE CbInventario ADD CONSTRAINT FK_CbInve_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tienda) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_CbInve_Tiendas OK';
GO

-- CbInventario.(ccod_cia, ccod_alm) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_CbInve_Almacenes','F') IS NULL
    ALTER TABLE CbInventario ADD CONSTRAINT FK_CbInve_Almacenes
        FOREIGN KEY (ccod_cia, ccod_alm) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_CbInve_Almacenes OK';
GO

/* ----------------------------------------------------------
   BLOQUE 11: LnInventario → CbInventario / Articulos / Almacenes
   ---------------------------------------------------------- */

-- LnInventario.id_cbinve → CbInventario.id_cbinve
IF OBJECT_ID('FK_LnInve_CbInve','F') IS NULL
    ALTER TABLE LnInventario ADD CONSTRAINT FK_LnInve_CbInve
        FOREIGN KEY (id_cbinve) REFERENCES CbInventario(id_cbinve);
PRINT 'FK_LnInve_CbInve OK';
GO

-- LnInventario.(ccod_cia, ccod_articulo) → Articulos.(ccod_cia, ccod_articulo)
IF OBJECT_ID('FK_LnInve_Articulos','F') IS NULL
    ALTER TABLE LnInventario ADD CONSTRAINT FK_LnInve_Articulos
        FOREIGN KEY (ccod_cia, ccod_articulo) REFERENCES Articulos(ccod_cia, ccod_articulo);
PRINT 'FK_LnInve_Articulos OK';
GO

-- LnInventario.(ccod_cia, ccod_alm) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_LnInve_Almacenes','F') IS NULL
    ALTER TABLE LnInventario ADD CONSTRAINT FK_LnInve_Almacenes
        FOREIGN KEY (ccod_cia, ccod_alm) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_LnInve_Almacenes OK';
GO

/* ----------------------------------------------------------
   BLOQUE 12: CbFactura → Tiendas / Cajas / Almacenes / Coa / Turno / CbInventario
   ---------------------------------------------------------- */

-- CbFactura.(ccod_cia, ccod_tiend) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_CbFact_Tiendas','F') IS NULL
    ALTER TABLE CbFactura ADD CONSTRAINT FK_CbFact_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tiend) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_CbFact_Tiendas OK';
GO

-- CbFactura.(ccod_cia, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_CbFact_Cajas','F') IS NULL
    ALTER TABLE CbFactura ADD CONSTRAINT FK_CbFact_Cajas
        FOREIGN KEY (ccod_cia, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_CbFact_Cajas OK';
GO

-- CbFactura.(ccod_cia, ccod_almacen) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_CbFact_Almacenes','F') IS NULL
    ALTER TABLE CbFactura ADD CONSTRAINT FK_CbFact_Almacenes
        FOREIGN KEY (ccod_cia, ccod_almacen) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_CbFact_Almacenes OK';
GO

-- CbFactura.(ccod_cia, ccod_coa) → Coa.(ccod_cia, ccod_coa)
IF OBJECT_ID('FK_CbFact_Coa','F') IS NULL
    ALTER TABLE CbFactura ADD CONSTRAINT FK_CbFact_Coa
        FOREIGN KEY (ccod_cia, ccod_coa) REFERENCES Coa(ccod_cia, ccod_coa);
PRINT 'FK_CbFact_Coa OK';
GO

-- CbFactura.id_turno → Turno.id_turno
IF OBJECT_ID('FK_CbFact_Turno','F') IS NULL
    ALTER TABLE CbFactura ADD CONSTRAINT FK_CbFact_Turno
        FOREIGN KEY (id_turno) REFERENCES Turno(id_turno);
PRINT 'FK_CbFact_Turno OK';
GO

-- CbFactura.id_cbinve → CbInventario.id_cbinve  (nullable: una factura puede no generar movimiento de inventario en tiempo real)
IF OBJECT_ID('FK_CbFact_CbInve','F') IS NULL
    ALTER TABLE CbFactura ADD CONSTRAINT FK_CbFact_CbInve
        FOREIGN KEY (id_cbinve) REFERENCES CbInventario(id_cbinve);
PRINT 'FK_CbFact_CbInve OK';
GO

/* ----------------------------------------------------------
   BLOQUE 13: LnFactura → CbFactura / Articulos / Almacenes / CbInventario
   ---------------------------------------------------------- */

-- LnFactura.id_cbfact → CbFactura.id_cbfact
IF OBJECT_ID('FK_LnFact_CbFact','F') IS NULL
    ALTER TABLE LnFactura ADD CONSTRAINT FK_LnFact_CbFact
        FOREIGN KEY (id_cbfact) REFERENCES CbFactura(id_cbfact);
PRINT 'FK_LnFact_CbFact OK';
GO

-- LnFactura.(ccod_cia, id_articulo) → Articulos.(ccod_cia, ccod_articulo)
-- id_articulo en LnFactura almacena el código del artículo (VARCHAR 50)
IF OBJECT_ID('FK_LnFact_Articulos','F') IS NULL
    ALTER TABLE LnFactura ADD CONSTRAINT FK_LnFact_Articulos
        FOREIGN KEY (ccod_cia, id_articulo) REFERENCES Articulos(ccod_cia, ccod_articulo);
PRINT 'FK_LnFact_Articulos OK';
GO

-- LnFactura.(ccod_cia, ccod_almacen) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_LnFact_Almacenes','F') IS NULL
    ALTER TABLE LnFactura ADD CONSTRAINT FK_LnFact_Almacenes
        FOREIGN KEY (ccod_cia, ccod_almacen) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_LnFact_Almacenes OK';
GO

-- LnFactura.id_cbinve → CbInventario.id_cbinve (nullable)
IF OBJECT_ID('FK_LnFact_CbInve','F') IS NULL
    ALTER TABLE LnFactura ADD CONSTRAINT FK_LnFact_CbInve
        FOREIGN KEY (id_cbinve) REFERENCES CbInventario(id_cbinve);
PRINT 'FK_LnFact_CbInve OK';
GO

/* ----------------------------------------------------------
   BLOQUE 14: CbCobranza → CbFactura / Turno / Tiendas / Cajas
   ---------------------------------------------------------- */

-- CbCobranza.id_cbfact → CbFactura.id_cbfact
IF OBJECT_ID('FK_CbCobr_CbFact','F') IS NULL
    ALTER TABLE CbCobranza ADD CONSTRAINT FK_CbCobr_CbFact
        FOREIGN KEY (id_cbfact) REFERENCES CbFactura(id_cbfact);
PRINT 'FK_CbCobr_CbFact OK';
GO

-- CbCobranza.id_turno → Turno.id_turno
IF OBJECT_ID('FK_CbCobr_Turno','F') IS NULL
    ALTER TABLE CbCobranza ADD CONSTRAINT FK_CbCobr_Turno
        FOREIGN KEY (id_turno) REFERENCES Turno(id_turno);
PRINT 'FK_CbCobr_Turno OK';
GO

-- CbCobranza.(ccod_cia, ccod_tiend) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_CbCobr_Tiendas','F') IS NULL
    ALTER TABLE CbCobranza ADD CONSTRAINT FK_CbCobr_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tiend) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_CbCobr_Tiendas OK';
GO

-- CbCobranza.(ccod_cia, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_CbCobr_Cajas','F') IS NULL
    ALTER TABLE CbCobranza ADD CONSTRAINT FK_CbCobr_Cajas
        FOREIGN KEY (ccod_cia, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_CbCobr_Cajas OK';
GO

/* ----------------------------------------------------------
   BLOQUE 15: LnCobranza → CbCobranza / CbFactura
   ---------------------------------------------------------- */

-- LnCobranza.id_cbcajac → CbCobranza.id_cbcajac
IF OBJECT_ID('FK_LnCobr_CbCobr','F') IS NULL
    ALTER TABLE LnCobranza ADD CONSTRAINT FK_LnCobr_CbCobr
        FOREIGN KEY (id_cbcajac) REFERENCES CbCobranza(id_cbcajac);
PRINT 'FK_LnCobr_CbCobr OK';
GO

-- LnCobranza.id_cbfact → CbFactura.id_cbfact
IF OBJECT_ID('FK_LnCobr_CbFact','F') IS NULL
    ALTER TABLE LnCobranza ADD CONSTRAINT FK_LnCobr_CbFact
        FOREIGN KEY (id_cbfact) REFERENCES CbFactura(id_cbfact);
PRINT 'FK_LnCobr_CbFact OK';
GO

/* ----------------------------------------------------------
   BLOQUE 16: CbCuenta → Coa / Tiendas / Cajas
   ---------------------------------------------------------- */

-- CbCuenta.(ccod_cia, ccod_coa) → Coa.(ccod_cia, ccod_coa)
IF OBJECT_ID('FK_CbCuenta_Coa','F') IS NULL
    ALTER TABLE CbCuenta ADD CONSTRAINT FK_CbCuenta_Coa
        FOREIGN KEY (ccod_cia, ccod_coa) REFERENCES Coa(ccod_cia, ccod_coa);
PRINT 'FK_CbCuenta_Coa OK';
GO

-- CbCuenta.(ccod_cia, ccod_tiend) → Tiendas.(ccod_cia, ccod_tiend)
IF OBJECT_ID('FK_CbCuenta_Tiendas','F') IS NULL
    ALTER TABLE CbCuenta ADD CONSTRAINT FK_CbCuenta_Tiendas
        FOREIGN KEY (ccod_cia, ccod_tiend) REFERENCES Tiendas(ccod_cia, ccod_tiend);
PRINT 'FK_CbCuenta_Tiendas OK';
GO

-- CbCuenta.(ccod_cia, ccod_caja) → Cajas.(ccod_cia, ccod_caja)
IF OBJECT_ID('FK_CbCuenta_Cajas','F') IS NULL
    ALTER TABLE CbCuenta ADD CONSTRAINT FK_CbCuenta_Cajas
        FOREIGN KEY (ccod_cia, ccod_caja) REFERENCES Cajas(ccod_cia, ccod_caja);
PRINT 'FK_CbCuenta_Cajas OK';
GO

/* ----------------------------------------------------------
   BLOQUE 17: LnCuenta → CbCuenta / Articulos
   ---------------------------------------------------------- */

-- LnCuenta.id_cbcuenta → CbCuenta.id_cbcuenta
IF OBJECT_ID('FK_LnCuenta_CbCuenta','F') IS NULL
    ALTER TABLE LnCuenta ADD CONSTRAINT FK_LnCuenta_CbCuenta
        FOREIGN KEY (id_cbcuenta) REFERENCES CbCuenta(id_cbcuenta);
PRINT 'FK_LnCuenta_CbCuenta OK';
GO

-- LnCuenta.(ccod_cia, id_articulo) → Articulos.(ccod_cia, ccod_articulo)
-- id_articulo en LnCuenta almacena el código del artículo (VARCHAR 50)
IF OBJECT_ID('FK_LnCuenta_Articulos','F') IS NULL
    ALTER TABLE LnCuenta ADD CONSTRAINT FK_LnCuenta_Articulos
        FOREIGN KEY (ccod_cia, id_articulo) REFERENCES Articulos(ccod_cia, ccod_articulo);
PRINT 'FK_LnCuenta_Articulos OK';
GO

/* ----------------------------------------------------------
   BLOQUE 18: CbGuia → CbInventario / Almacenes / Coa
   ---------------------------------------------------------- */

-- CbGuia.id_cbinve → CbInventario.id_cbinve (nullable: guía puede no tener movimiento de stock directo)
IF OBJECT_ID('FK_CbGuia_CbInve','F') IS NULL
    ALTER TABLE CbGuia ADD CONSTRAINT FK_CbGuia_CbInve
        FOREIGN KEY (id_cbinve) REFERENCES CbInventario(id_cbinve);
PRINT 'FK_CbGuia_CbInve OK';
GO

-- CbGuia.(ccod_cia, ccod_alm) → Almacenes.(ccod_cia, ccod_alm)
IF OBJECT_ID('FK_CbGuia_Almacen','F') IS NULL
    ALTER TABLE CbGuia ADD CONSTRAINT FK_CbGuia_Almacen
        FOREIGN KEY (ccod_cia, ccod_alm) REFERENCES Almacenes(ccod_cia, ccod_alm);
PRINT 'FK_CbGuia_Almacen OK';
GO

-- CbGuia.(ccod_cia, ccod_coa) → Coa.(ccod_cia, ccod_coa)  (cliente/destinatario de la guía)
IF OBJECT_ID('FK_CbGuia_Coa','F') IS NULL
    ALTER TABLE CbGuia ADD CONSTRAINT FK_CbGuia_Coa
        FOREIGN KEY (ccod_cia, ccod_coa) REFERENCES Coa(ccod_cia, ccod_coa);
PRINT 'FK_CbGuia_Coa OK';
GO

PRINT '';
PRINT '=== DatPos_EMP01: FKs creadas correctamente ===';
GO

/* ============================================================
   PARTE 2 — DatPosAdmin
   ============================================================ */
USE DatPosAdmin;
GO

PRINT '=== DatPosAdmin: creando FKs ===';
GO

-- Usuarios.ccod_empresa → Empresas.ccod_empresa
IF OBJECT_ID('FK_Usu_Empresas','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usu_Empresas
        FOREIGN KEY (ccod_empresa) REFERENCES Empresas(ccod_empresa);
PRINT 'FK_Usu_Empresas OK';
GO

-- Usuarios.id_rol → Roles.id_rol
IF OBJECT_ID('FK_Usu_Roles','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usu_Roles
        FOREIGN KEY (id_rol) REFERENCES Roles(id_rol);
PRINT 'FK_Usu_Roles OK';
GO

-- Usuarios.id_estado → Estados.id_estado
IF OBJECT_ID('FK_Usu_Estados','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usu_Estados
        FOREIGN KEY (id_estado) REFERENCES Estados(id_estado);
PRINT 'FK_Usu_Estados OK';
GO

-- Accesos.id_rol → Roles.id_rol
IF OBJECT_ID('FK_Acc_Roles','F') IS NULL
    ALTER TABLE Accesos ADD CONSTRAINT FK_Acc_Roles
        FOREIGN KEY (id_rol) REFERENCES Roles(id_rol);
PRINT 'FK_Acc_Roles OK';
GO

-- Accesos.id_menu → Menus.id_menu
IF OBJECT_ID('FK_Acc_Menus','F') IS NULL
    ALTER TABLE Accesos ADD CONSTRAINT FK_Acc_Menus
        FOREIGN KEY (id_menu) REFERENCES Menus(id_menu);
PRINT 'FK_Acc_Menus OK';
GO

-- Provincias.id_departamento → Departamentos.id_departamento
IF OBJECT_ID('FK_Prov_Depto','F') IS NULL
    ALTER TABLE Provincias ADD CONSTRAINT FK_Prov_Depto
        FOREIGN KEY (id_departamento) REFERENCES Departamentos(id_departamento);
PRINT 'FK_Prov_Depto OK';
GO

-- Distritos.id_provincia → Provincias.id_provincia
IF OBJECT_ID('FK_Dist_Prov','F') IS NULL
    ALTER TABLE Distritos ADD CONSTRAINT FK_Dist_Prov
        FOREIGN KEY (id_provincia) REFERENCES Provincias(id_provincia);
PRINT 'FK_Dist_Prov OK';
GO

-- Usuarios.id_estado → Estados.id_estado
IF OBJECT_ID('FK_Usu_Estados','F') IS NULL
    ALTER TABLE Usuarios ADD CONSTRAINT FK_Usu_Estados
        FOREIGN KEY (id_estado) REFERENCES Estados(id_estado);
PRINT 'FK_Usu_Estados OK';
GO

PRINT '';
PRINT '=== DatPosAdmin: FKs creadas correctamente ===';
PRINT '';
PRINT '=== FIX 39 completado: todas las FKs aplicadas (50 EMP01 + 7 Admin = 57 total) ===';
GO
