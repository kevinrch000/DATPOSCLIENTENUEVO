/* =====================================================================
   FIX 40 — FOREIGN KEYS COMPLEMENTARIAS: TipoOperacion y Menus
   
   Conecta las 3 tablas que quedaron aisladas tras FIX 39:
   
   1. TipoOperacion  ◄── CbInventario.ctipo
                     ◄── CbGuia.ctipo
                     ◄── ConfigGeneral.coper_ingreso
                     ◄── ConfigGeneral.coper_salida
   
   2. Menus          ◄── Menus.nid_menupadre  (auto-referencia jerárquica)
   
   PRE-REQUISITO: FIX 39 ya aplicado (680_FIX_39_ForeignKeys.sql)
   
   CAMBIO DE DATOS:
   - Menus.nid_menupadre = 0  →  NULL  (0 no existe como id_menu;
     NULL es la forma correcta de indicar "sin padre" en SQL)
   ===================================================================== */

USE DatPos_EMP01;
GO

PRINT '=== FIX 40: FK TipoOperacion + Menus auto-referencia ==='
PRINT ''

/* ---------------------------------------------------------------------
   PASO 1: UNIQUE constraint en TipoOperacion.ccod_tipoper
           (necesario para que otras tablas hagan FK a este campo)
   --------------------------------------------------------------------- */
PRINT 'PASO 1: Unique constraint en TipoOperacion.ccod_tipoper ...'

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes 
    WHERE name = 'UQ_TipoOperacion_ccod_tipoper' 
      AND object_id = OBJECT_ID('TipoOperacion')
)
BEGIN
    ALTER TABLE TipoOperacion
        ADD CONSTRAINT UQ_TipoOperacion_ccod_tipoper UNIQUE (ccod_tipoper);
    PRINT '  -> UQ_TipoOperacion_ccod_tipoper creada.'
END
ELSE
    PRINT '  -> UQ ya existe, se omite.'
GO

/* ---------------------------------------------------------------------
   PASO 2: FK de CbInventario.ctipo → TipoOperacion.ccod_tipoper
   
   NOTA: ctipo debe ser VARCHAR(20) para coincidir con ccod_tipoper(20).
         SQL Server requiere la misma longitud para FKs entre varchar.
   --------------------------------------------------------------------- */
PRINT 'PASO 2: FK CbInventario → TipoOperacion ...'

-- Ampliar ctipo a varchar(20) si es necesario
IF EXISTS (
    SELECT 1 FROM sys.columns c JOIN sys.tables t ON c.object_id=t.object_id
    WHERE t.name='CbInventario' AND c.name='ctipo' AND c.max_length < 20
)
BEGIN
    ALTER TABLE CbInventario ALTER COLUMN ctipo VARCHAR(20);
    PRINT '  -> CbInventario.ctipo ampliado a varchar(20).'
END

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_CbInve_TipoOper')
BEGIN
    ALTER TABLE CbInventario
        ADD CONSTRAINT FK_CbInve_TipoOper
        FOREIGN KEY (ctipo) REFERENCES TipoOperacion(ccod_tipoper);
    PRINT '  -> FK_CbInve_TipoOper creada.'
END
ELSE
    PRINT '  -> FK ya existe, se omite.'
GO

/* ---------------------------------------------------------------------
   PASO 3: FK de CbGuia.ctipo → TipoOperacion.ccod_tipoper
   --------------------------------------------------------------------- */
PRINT 'PASO 3: FK CbGuia → TipoOperacion ...'

-- Ampliar ctipo a varchar(20) si es necesario
IF EXISTS (
    SELECT 1 FROM sys.columns c JOIN sys.tables t ON c.object_id=t.object_id
    WHERE t.name='CbGuia' AND c.name='ctipo' AND c.max_length < 20
)
BEGIN
    ALTER TABLE CbGuia ALTER COLUMN ctipo VARCHAR(20);
    PRINT '  -> CbGuia.ctipo ampliado a varchar(20).'
END

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_CbGuia_TipoOper')
BEGIN
    ALTER TABLE CbGuia
        ADD CONSTRAINT FK_CbGuia_TipoOper
        FOREIGN KEY (ctipo) REFERENCES TipoOperacion(ccod_tipoper);
    PRINT '  -> FK_CbGuia_TipoOper creada.'
END
ELSE
    PRINT '  -> FK ya existe, se omite.'
GO

/* ---------------------------------------------------------------------
   PASO 4: FK de ConfigGeneral.coper_ingreso → TipoOperacion.ccod_tipoper
   --------------------------------------------------------------------- */
PRINT 'PASO 4: FK ConfigGeneral.coper_ingreso → TipoOperacion ...'

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_Config_TipoOperIngreso')
BEGIN
    ALTER TABLE ConfigGeneral
        ADD CONSTRAINT FK_Config_TipoOperIngreso
        FOREIGN KEY (coper_ingreso) REFERENCES TipoOperacion(ccod_tipoper);
    PRINT '  -> FK_Config_TipoOperIngreso creada.'
END
ELSE
    PRINT '  -> FK ya existe, se omite.'
GO

/* ---------------------------------------------------------------------
   PASO 5: FK de ConfigGeneral.coper_salida → TipoOperacion.ccod_tipoper
   --------------------------------------------------------------------- */
PRINT 'PASO 5: FK ConfigGeneral.coper_salida → TipoOperacion ...'

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_Config_TipoOperSalida')
BEGIN
    ALTER TABLE ConfigGeneral
        ADD CONSTRAINT FK_Config_TipoOperSalida
        FOREIGN KEY (coper_salida) REFERENCES TipoOperacion(ccod_tipoper);
    PRINT '  -> FK_Config_TipoOperSalida creada.'
END
ELSE
    PRINT '  -> FK ya existe, se omite.'
GO

/* ---------------------------------------------------------------------
   PASO 6: Menus auto-referencia (jerarquía padre → hijo)
   
   Menus raíz usan nid_menupadre=0, pero id_menu=0 no existe.
   El estándar SQL para "sin padre" es NULL, no 0.
   Actualizamos 0 → NULL antes de crear la FK.
   --------------------------------------------------------------------- */
PRINT 'PASO 6: Menus auto-referencia (nid_menupadre) ...'

-- 6a: Cambiar nid_menupadre=0 a NULL (menús raíz)
DECLARE @menus_raiz INT
SELECT @menus_raiz = COUNT(*) FROM Menus WHERE nid_menupadre = 0

IF @menus_raiz > 0
BEGIN
    UPDATE Menus SET nid_menupadre = NULL WHERE nid_menupadre = 0;
    PRINT '  -> ' + CAST(@menus_raiz AS VARCHAR) + ' menu(s) raiz: nid_menupadre 0 → NULL.'
END
ELSE
    PRINT '  -> No hay nid_menupadre=0, no se requiere update.'

-- 6b: Crear FK auto-referencia
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_Menus_MenuPadre')
BEGIN
    ALTER TABLE Menus
        ADD CONSTRAINT FK_Menus_MenuPadre
        FOREIGN KEY (nid_menupadre) REFERENCES Menus(id_menu);
    PRINT '  -> FK_Menus_MenuPadre creada.'
END
ELSE
    PRINT '  -> FK ya existe, se omite.'
GO

/* ---------------------------------------------------------------------
   VERIFICACION FINAL
   --------------------------------------------------------------------- */
PRINT ''
PRINT '=== VERIFICACION FINAL ==='

SELECT 
    fk.name AS foreign_key,
    OBJECT_NAME(fk.parent_object_id) AS tabla,
    c_hijo.name AS columna,
    OBJECT_NAME(fk.referenced_object_id) AS referencia,
    c_padre.name AS col_referencia
FROM sys.foreign_keys fk
JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
JOIN sys.columns c_hijo  ON fkc.parent_object_id  = c_hijo.object_id  AND fkc.parent_column_id  = c_hijo.column_id
JOIN sys.columns c_padre ON fkc.referenced_object_id = c_padre.object_id AND fkc.referenced_column_id = c_padre.column_id
WHERE OBJECT_NAME(fk.parent_object_id) IN ('CbInventario','CbGuia','ConfigGeneral','Menus')
ORDER BY tabla, foreign_key

SELECT COUNT(*) AS total_fks_emp01 FROM sys.foreign_keys
GO

PRINT '=== FIX 40 COMPLETADO ==='
