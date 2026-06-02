/* =====================================================================
   FIX 22 — Ampliar columnas ubigeo en tabla Almacenes
   
   Problema: cdepartamento VARCHAR(2), cprovincia VARCHAR(4), cdistrito VARCHAR(6)
   pero el JS envía el NOMBRE (ej: "LIMA", "MIRAFLORES") no el código.
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

ALTER TABLE Almacenes ALTER COLUMN cdepartamento VARCHAR(100) NULL;
GO
ALTER TABLE Almacenes ALTER COLUMN cprovincia    VARCHAR(100) NULL;
GO
ALTER TABLE Almacenes ALTER COLUMN cdistrito     VARCHAR(100) NULL;
GO

PRINT 'OK: Columnas cdepartamento, cprovincia, cdistrito ampliadas a VARCHAR(100)';
GO
