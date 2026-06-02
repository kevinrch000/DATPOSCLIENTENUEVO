/* ================================================================
 * datpos-responsive.js
 * --------------------------------------------------------------
 * Maneja el sidebar off-canvas en pantallas <= 991px:
 *  - Inserta un backdrop oscuro detras del sidebar.
 *  - Toggle de la clase `dp-sidebar-open` sobre <body>.
 *  - Cierra al click en backdrop o al elegir un item del menu.
 *  - Cierra automaticamente al hacer resize por encima de 991px.
 *
 * Estrategia anti-fragil (Fix bug #1):
 *  - Event delegation a nivel `document` en fase de captura. Asi
 *    sobrevive re-renders del SPA, reinyecciones del header, y
 *    pelea contra cualquier handler intermedio que pretenda
 *    interceptar el click del hamburguesa (#btnMenu).
 *  - Tambien se mantiene un listener directo sobre #btnMenu como
 *    refuerzo (no es estrictamente necesario, pero suma).
 *  - Se ejecuta tanto si el script carga antes como despues del
 *    DOMContentLoaded.
 *
 * No interfiere con el comportamiento desktop (la clase legacy
 * `hiddenmenuvertical-menu` sigue funcionando para colapsar/expandir
 * el sidebar fijo) ni con SPA navigation.
 * ================================================================ */
(function () {
    'use strict';

    var MOBILE_BREAKPOINT = 991;
    var DEBUG = false; // poner en true para inspeccionar en consola

    function log() {
        if (DEBUG && window.console && console.log) {
            console.log.apply(console, ['[datpos-responsive]'].concat([].slice.call(arguments)));
        }
    }

    function isMobile() {
        return window.innerWidth <= MOBILE_BREAKPOINT;
    }

    function ensureBackdrop() {
        var bd = document.querySelector('.dp-sidebar-backdrop');
        if (!bd) {
            bd = document.createElement('div');
            bd.className = 'dp-sidebar-backdrop';
            bd.addEventListener('click', closeSidebar);
            // Soporta touch en iOS/Android donde el click puede llegar tarde.
            bd.addEventListener('touchstart', function (e) {
                e.preventDefault();
                closeSidebar();
            }, { passive: false });
            (document.body || document.documentElement).appendChild(bd);
        }
        return bd;
    }

    function openSidebar() {
        if (!isMobile()) return;
        ensureBackdrop();
        document.body.classList.add('dp-sidebar-open');
        // Quitar la clase legacy que oculta el sidebar para que se vea
        var menu = document.getElementById('menuver');
        if (menu) menu.classList.remove('hiddenmenuvertical-menu');
        var content = document.getElementById('content');
        if (content) content.classList.remove('hiddenmenuvertical-header');
        log('openSidebar()');
    }

    function closeSidebar() {
        document.body.classList.remove('dp-sidebar-open');
        log('closeSidebar()');
    }

    function toggleSidebar() {
        if (document.body.classList.contains('dp-sidebar-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    /**
     * Handler central para clicks/touches sobre el boton hamburguesa.
     * Funciona via delegacion en `document` con captura para correr
     * ANTES de cualquier otro listener (incluido el onclick="mostrar()"
     * inline del HTML legacy).
     */
    function handleHamburgerActivation(e) {
        if (!isMobile()) return; // En desktop el comportamiento legacy se respeta
        var target = e.target;
        if (!target) return;
        var hit = (target.closest)
            ? target.closest('#btnMenu, .c-menu-toggle')
            : null;
        if (!hit) return;

        // Bloquear el comportamiento legacy `mostrar()` en movil.
        e.preventDefault();
        if (typeof e.stopImmediatePropagation === 'function') {
            e.stopImmediatePropagation();
        } else {
            e.stopPropagation();
        }
        // Solo togglea en `click` (touch-end suele ser seguido por
        // un click sintetico; respondiendo a ambos provocariamos un
        // doble toggle que cancela la accion).
        if (e.type === 'click') {
            toggleSidebar();
        }
        log('hamburger activated via', e.type);
    }

    /**
     * Handler para cerrar el sidebar cuando el usuario hace click
     * en un link del menu lateral (en movil).
     */
    function handleMenuLinkClick(e) {
        if (!isMobile()) return;
        var target = e.target;
        if (!target || !target.closest) return;
        var link = target.closest('#listOpciones a');
        if (!link) return;
        var href = link.getAttribute('href') || '';
        var hasId = link.getAttribute('id') || '';
        var isToggleOnly = (href === '#' && !hasId);
        if (isToggleOnly) return;
        setTimeout(closeSidebar, 80);
    }

    /**
     * Listener directo sobre #btnMenu como refuerzo (no estrictamente
     * necesario al estar la delegacion en document, pero ayuda con
     * algunos plugins que clavan handlers en captura mas profunda).
     */
    function attachDirectListener() {
        var btnMenu = document.getElementById('btnMenu');
        if (!btnMenu) return;
        if (btnMenu.__dpResponsiveBound) return; // idempotente
        btnMenu.__dpResponsiveBound = true;
        btnMenu.addEventListener('click', function (e) {
            if (!isMobile()) return;
            e.preventDefault();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            } else {
                e.stopPropagation();
            }
            toggleSidebar();
        }, true);
        log('direct listener attached on #btnMenu');
    }

    function init() {
        ensureBackdrop();
        attachDirectListener();

        // Delegacion global en captura (sobrevive cualquier SPA
        // re-render y corre ANTES de los onclick inline del HTML).
        // Solo enganchamos `click`: el browser sintetiza un click
        // tras touchend con viewport correcto, asi que cubrimos
        // mouse + touch sin riesgo de doble toggle.
        document.addEventListener('click', handleHamburgerActivation, true);

        // Cerrar al elegir un item del menu.
        document.addEventListener('click', handleMenuLinkClick, false);

        // Re-bind directo si por alguna razon el header se reinyecta
        // (defensivo: SPA solo cambia #spa-content-area, pero hay
        // codigo legacy que toca el header en algunos modulos).
        if (typeof window.MutationObserver === 'function') {
            try {
                var headerHost = document.getElementById('content');
                if (headerHost) {
                    var mo = new MutationObserver(function () {
                        attachDirectListener();
                    });
                    mo.observe(headerHost, { childList: true, subtree: true });
                }
            } catch (_) { /* noop */ }
        }

        // Cerrar al redimensionar a desktop
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (!isMobile()) closeSidebar();
            }, 120);
        });

        // Cerrar al presionar Escape
        document.addEventListener('keydown', function (e) {
            if ((e.key === 'Escape' || e.keyCode === 27)
                && document.body.classList.contains('dp-sidebar-open')) {
                closeSidebar();
            }
        });

        // Hook para que SPA navigation cierre el sidebar al cargar otro modulo.
        try {
            if (window.jQuery) {
                window.jQuery(document).on('spa:loaded', function () {
                    if (isMobile()) closeSidebar();
                });
            }
        } catch (_) { /* noop */ }
    }

    // Soportar carga antes o despues del DOMContentLoaded.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // API publica por si alguien quiere controlarlo desde codigo.
    window.DATPOS_Responsive = {
        openSidebar: openSidebar,
        closeSidebar: closeSidebar,
        toggleSidebar: toggleSidebar,
        isMobile: isMobile
    };
})();
