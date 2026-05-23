/* FIX 07 — Actualizar curl_src de los Menús con rutas de imagen correctas
   El JS usa <img src="curl_src"> para los módulos principales (nivel 1)
   Los submenús usan /Styles/img/icon/icon_chevronR.png fijo */
USE DatPos_EMP01;
GO

-- Módulos principales (nivel 1, corden 1-7) — usar iconos reales que existen
UPDATE Menus SET curl_src='/Styles/img/icon/icon_tablas.png' WHERE cdsc_menu='Dashboard' AND nid_menupadre=0;
UPDATE Menus SET curl_src='/Styles/img/icon/icono_ventas.png' WHERE cdsc_menu='Ventas' AND nid_menupadre=0;
UPDATE Menus SET curl_src='/Styles/img/icon/icono_almacen.png' WHERE cdsc_menu='Inventario' AND nid_menupadre=0;
UPDATE Menus SET curl_src='/Styles/img/icon/icon_regCart.png' WHERE cdsc_menu LIKE 'Art%culos' AND nid_menupadre=0;
UPDATE Menus SET curl_src='/Styles/img/icon/icon_user.png' WHERE cdsc_menu='Clientes' AND nid_menupadre=0;
UPDATE Menus SET curl_src='/Styles/img/icon/icon_consultar.png' WHERE cdsc_menu='Reportes' AND nid_menupadre=0;
UPDATE Menus SET curl_src='/Styles/img/icon/icono_adm.png' WHERE cdsc_menu LIKE 'Config%' AND nid_menupadre=0;

-- Verificar los cambios
SELECT id_menu, cdsc_menu, curl_src, corden FROM Menus WHERE nid_menupadre=0 ORDER BY corden;
GO

PRINT '✓ Iconos de menú corregidos con rutas de imágenes reales.';
GO
 
