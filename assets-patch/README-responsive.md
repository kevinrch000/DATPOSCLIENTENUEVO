# DatPOS — Responsive patch (changelog)

Última actualización: 2026-05-22

## Bug crítico #1 resuelto: click del hamburguesa en móvil

**Síntoma original**: en móvil (`<= 991px`) el click sobre el ícono ≡
(`#btnMenu` en `includes/layout_master.php`) no abría el sidebar
off-canvas. El sidebar sólo respondía a la llamada manual desde la
consola: `DATPOS_Responsive.openSidebar()`.

**Causa raíz**:

1. El listener original se enganchaba **directamente** sobre
   `#btnMenu` en `DOMContentLoaded`. Si por cualquier razón el header
   se re-inyectaba (o un plugin clavaba un handler que llamaba
   `e.stopImmediatePropagation()` antes), el listener desaparecía y el
   click llegaba al `onclick="mostrar();"` inline — que sólo toggla la
   clase `hiddenmenuvertical-menu` (comportamiento desktop) y no
   abría el off-canvas.
2. El span `.c-menu-user` (avatar + settings) tenía `right: 0` y, con
   anchos de pantalla agresivos, podía cubrir parcialmente al
   `#btnMenu`, dejándolo inalcanzable al click.

**Fix aplicado** (todo en `assets-patch/`):

### `datpos-responsive.js`
- Listener delegado a nivel `document` en **fase de captura** — corre
  ANTES de `mostrar()` inline y sobrevive cualquier re-render de SPA.
- Listener directo sobre `#btnMenu` (idempotente) como refuerzo.
- `MutationObserver` sobre `#content` para re-bindear el listener
  directo si algún plugin reemplaza el header.
- Hook `spa:loaded` (jQuery) que cierra el sidebar móvil después de
  cada navegación SPA (preserva la UX de tap → menú → tap item → nav).
- Soporte de `Escape` para cerrar, `resize` que cierra al salir de
  móvil, y `touchstart` en el backdrop (iOS).
- Exposición pública: `window.DATPOS_Responsive.{open,close,toggle}Sidebar`.

### `datpos-responsive.css`
- `#btnMenu, span.c-menu-toggle { cursor: pointer; user-select: none; }`
- `@media (max-width: 991px)`: `#btnMenu { z-index: 101 !important;
  pointer-events: auto !important; touch-action: manipulation; }`
  y `.c-menu-user { z-index: 100 !important; right: 8px !important; }`
  para garantizar que el área del hamburguesa nunca sea cubierta.

## Verificación

| Viewport         | Módulos probados                                    | Resultado |
|------------------|------------------------------------------------------|-----------|
| 360x740 (mobile) | Home, Facturación, Clientes, Artículos, Familias, Kardex, ConsultasVenta, Ingresos | ≡ abre sidebar OK · sin overflow |
| 414x896 (mobile) | (mismos)                                             | ≡ abre sidebar OK · sin overflow |
| 768x1024 (tablet)| (mismos)                                             | ≡ abre sidebar OK · sin overflow |
| 1024x768 (laptop)| (mismos)                                             | sidebar fijo · sin overflow      |
| 1366x768 (desktop)|(mismos)                                             | sidebar fijo · sin overflow      |

Modales grandes (`modalConsultarClientes`, `modalObtenerCuenta`):
- Mobile ≤ 480px: fullscreen (100vh) con `border-radius: 0`.
- Mobile 481–991px: 92vw centrado, `.modal-content` scrollea.
- Desktop: tamaño original (600px o `.modal-lg`).

SPA navigation (`assets/Javascript/spa_navigation.js`) intacto:
- Cambiar de módulo desde el sidebar NO recarga la página completa
  (`pushState` + AJAX a `#spa-content-area`).
- Tras navegar en móvil, el sidebar se cierra automáticamente vía el
  hook `spa:loaded`.

## Helpers para testing local (sin SQL Server)

Estos archivos viven en la raíz del repo y **no se usan en producción**.

### `test_mock_session.php`
Inyecta un `BEUsuario` falso en `$_SESSION['objBEUsuario']` y redirige
a `pages/Interfaces/Home.php`. Solo responde a peticiones desde
`localhost / 127.0.0.1`. Con `?stay=1` muestra una pantalla con links
a las páginas comunes en lugar de redirigir.

### `test_sqlsrv_stubs.php`
Define stubs no-op para todas las funciones `sqlsrv_*` cuando la
extensión nativa de Microsoft SQL Server no está cargada. Permite
que las páginas se rendericen aunque las queries fallen.

### `test_router.php`
Router wrapper de `router.php` que carga los stubs si la extensión
no está disponible y pasa el control al router original.

Uso:

```
php -S 127.0.0.1:8080 test_router.php
# Luego en el browser: http://127.0.0.1:8080/test_mock_session.php
```

Importante: estos tres archivos solo son útiles para development.
No los subas a producción.

## No se modificó

- `assets/Styles/MenuVer.css`, `Site.css`, `Moderno.css`, `Modern-UI.css`
- `assets-patch/datpos-theme-dark.css`
- `assets/Javascript/Comun.js`, `spa_navigation.js`, `modal_fix.js`
- Estructura HTML de `includes/layout_master.php`
- `assets-patch/operaciones_patch.js`

Todo override está en `assets-patch/datpos-responsive.css` con
`!important` cuando hace falta ganar especificidad contra
`Site.css` / `MenuVer.css`.
