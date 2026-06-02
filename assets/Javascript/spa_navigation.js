/**
 * DatPOS - SPA Navigation
 * ---------------------------------------------------------------------
 * Convierte la navegación tradicional (recarga completa) a navegación
 * AJAX sin recargar la página. Reemplaza el contenido de
 * #spa-content-area con el HTML devuelto por el servidor.
 *
 * Funcionamiento:
 *   1. Intercepta clicks en enlaces del menú lateral (#listOpciones a) y
 *      enlaces marcados con data-spa-link.
 *   2. Lanza una petición GET con header `X-SPA-Nav: 1`.
 *   3. layout_master.php detecta ese header y devuelve JSON con:
 *        { html, title, pageScript, pageScriptPatch,
 *          showCrudButtons, showConsultButtons }
 *   4. Inyecta el HTML, ejecuta los <script> inline, carga el script
 *      específico de la página y actualiza history vía pushState.
 *
 * Diseño/Buenas prácticas:
 *   - Cero dependencias nuevas (usa jQuery ya cargado).
 *   - Anti-doble-disparo: si ya hay una navegación en curso, se ignora.
 *   - Limpieza: destruye DataTables previas y elimina <script> de página
 *     anterior antes de inyectar la nueva.
 *   - Soporta back/forward del navegador (popstate).
 * ---------------------------------------------------------------------
 */
(function ($) {
    'use strict';

    var navigating = false;
    var initialHistoryReplaced = false;

    /**
     * Reemplaza el estado inicial del history para que popstate funcione
     * sobre la página actual cuando el usuario empieza a navegar.
     */
    function replaceInitialState() {
        if (initialHistoryReplaced) return;
        try {
            history.replaceState({ spa: true, url: location.href }, '', location.href);
            initialHistoryReplaced = true;
        } catch (e) { /* noop */ }
    }

    /**
     * Determina si una URL apunta a una página del propio sitio que
     * debería cargarse vía AJAX.
     */
    function isInternalPage(href) {
        if (!href) return false;
        // Excluir ancla pura, javascript:, mailto:, etc.
        if (href === '#' || href.charAt(0) === '#') return false;
        if (/^(javascript|mailto|tel):/i.test(href)) return false;

        // Construir URL absoluta para comparar host
        var a = document.createElement('a');
        a.href = href;
        if (a.host && a.host !== location.host) return false;

        // Solo páginas dentro de /pages/
        if (a.pathname.indexOf('/pages/') === -1) return false;

        // Excluir login/logout — esos deben recargar de verdad
        if (/\/(LogOn|logout)\.php$/i.test(a.pathname)) return false;

        return true;
    }

    /**
     * Limpia el estado de la página anterior antes de inyectar la nueva.
     * - Destruye DataTables.
     * - Cierra modales abiertos.
     * - Quita scripts antiguos marcados como data-spa-page-script.
     */
    function teardownPreviousPage() {
        // Destruir DataTables (evita memory leaks)
        try {
            if ($.fn.dataTable && $.fn.dataTable.isDataTable) {
                $('table').each(function () {
                    if ($.fn.dataTable.isDataTable(this)) {
                        try { $(this).DataTable().destroy(true); } catch (e) { /* noop */ }
                    }
                });
            }
        } catch (e) { /* noop */ }

        // Cerrar modales bootstrap activos y limpiar backdrops
        try {
            $('.modal.in, .modal.show').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        } catch (e) { /* noop */ }

        // Cerrar overlays jquery-confirm / SweetAlert si quedaron abiertos
        try { if (window.Swal && Swal.close) Swal.close(); } catch (e) { /* noop */ }
        try { $('.jconfirm').remove(); } catch (e) { /* noop */ }

        // Eliminar scripts de la página anterior
        $('script[data-spa-page-script], script[data-spa-page-script-patch], script[data-spa-inline]').remove();
    }

    /**
     * Extrae todos los <script> de un contenedor (los quita del DOM)
     * y los devuelve clasificados: external = los que tienen src,
     * inline = código embebido. Mantiene el orden original.
     */
    function extractScriptsFromContainer(container) {
        var scripts = container.querySelectorAll('script');
        var result = [];
        scripts.forEach(function (oldScript) {
            if (oldScript.src) {
                result.push({ type: 'external', src: oldScript.src, code: '' });
            } else {
                result.push({ type: 'inline', src: '', code: oldScript.textContent || '' });
            }
            oldScript.parentNode.removeChild(oldScript);
        });
        return result;
    }

    /**
     * Inyecta y ejecuta scripts en orden secuencial. Devuelve una promesa
     * que resuelve cuando todos terminaron de cargarse / ejecutarse.
     *
     * - External scripts: se insertan con async=false y se espera onload.
     * - Inline scripts: se evalúan inmediatamente y se sigue.
     */
    function runScriptsSequentially(scripts) {
        var p = Promise.resolve();
        scripts.forEach(function (sc) {
            p = p.then(function () {
                return new Promise(function (resolve, reject) {
                    var s = document.createElement('script');
                    s.async = false;
                    s.setAttribute('data-spa-inline', '');
                    if (sc.type === 'external') {
                        s.src = sc.src;
                        s.onload = function () { resolve(); };
                        s.onerror = function () {
                            // No bloquear toda la cadena por un script externo perdido
                            console.warn('SPA: no se pudo cargar', sc.src);
                            resolve();
                        };
                        document.body.appendChild(s);
                    } else {
                        s.textContent = sc.code;
                        try {
                            document.body.appendChild(s);
                        } catch (e) {
                            console.error('SPA: error ejecutando script inline', e);
                        }
                        resolve();
                    }
                });
            });
        });
        return p;
    }

    /**
     * Resalta el ítem activo en el menú lateral según la URL actual.
     * IMPORTANTE: NO toca el estado abierto/cerrado de los submenús —
     * de eso se encargan las funciones `inicar_menu_nivel2/3` del JS de
     * cada página, que ya hacen lo correcto sin colapsar submenús ya
     * abiertos. Esta función solo gestiona la clase `.dp-active` que da
     * el resaltado visual del tema moderno.
     */
    function highlightActiveMenuItem(url) {
        try {
            var a = document.createElement('a');
            a.href = url;
            var pathname = a.pathname.toLowerCase();
            var fileName = pathname.split('/').pop();

            $('#listOpciones .dp-active').removeClass('dp-active');

            if (fileName === 'home.php') {
                $('#dp-static-dashboard').addClass('dp-active');
                return;
            }

            $('#listOpciones a').each(function () {
                var href = $(this).attr('href') || $(this).attr('id') || '';
                if (!href) return;
                var b = document.createElement('a');
                b.href = href;
                if (b.pathname.toLowerCase() === pathname) {
                    $(this).closest('li').addClass('dp-active');
                }
            });
        } catch (e) { /* noop */ }
    }

    /**
     * Actualiza la visibilidad de los botones CRUD y de consulta.
     */
    function updateActionButtons(data) {
        if (data.showCrudButtons) {
            $('#spa-crud-buttons').show();
        } else {
            $('#spa-crud-buttons').hide();
        }
        if (data.showConsultButtons) {
            $('#spa-consult-buttons').show();
        } else {
            $('#spa-consult-buttons').hide();
        }
    }

    /**
     * Navega a una URL vía AJAX y reemplaza el contenido.
     */
    function spaNavigate(url, pushHistory) {
        if (navigating) return;
        if (pushHistory === undefined) pushHistory = true;

        replaceInitialState();
        navigating = true;

        $.ajax({
            url: url,
            type: 'GET',
            headers: { 'X-SPA-Nav': '1' },
            cache: false,
            dataType: 'json'
        })
        .done(function (data) {
            if (!data || typeof data !== 'object' || typeof data.html !== 'string') {
                // Respuesta no válida: hacer fallback a navegación normal
                window.location.href = url;
                return;
            }

            // Limpieza antes de reemplazar
            teardownPreviousPage();

            // Limpiar marcas de "ítem activo" del sidebar antes de que el
            // pageScript las vuelva a aplicar para la nueva URL. Con esto
            // evitamos que queden dos ítems marcados como activos a la vez.
            $('#listOpciones li.active').removeClass('active');
            $('#listOpciones .dp-active').removeClass('dp-active');

            // Actualizar título de pestaña
            if (data.title) document.title = data.title;

            // Actualizar visibilidad de botones CRUD/Consulta
            updateActionButtons(data);

            // Crear un contenedor temporal para parsear el HTML y extraer
            // los scripts (los <script> dentro de innerHTML no se ejecutan,
            // y necesitamos controlar su orden de ejecución).
            var tmp = document.createElement('div');
            tmp.innerHTML = data.html;

            // 1) Extraer todos los <script> del contenido (los quita del DOM temp)
            var contentScripts = extractScriptsFromContainer(tmp);

            // 2) Inyectar HTML (sin scripts) en el área SPA
            var $pageHtml = $('#spa-page-html');
            $pageHtml.empty();
            // Mover los nodos del tmp al contenedor real
            while (tmp.firstChild) $pageHtml[0].appendChild(tmp.firstChild);

            // 3) Construir cadena de ejecución de scripts respetando
            //    el orden original de carga del navegador:
            //      a) <script src> externos del contenido (librerías)
            //      b) pageScript y pageScriptPatch
            //      c) <script> inline del contenido (suelen usar funciones
            //         de pageScript dentro de $(document).ready)
            var externalContent = [];
            var inlineContent = [];
            contentScripts.forEach(function (sc) {
                if (sc.type === 'external') externalContent.push(sc);
                else inlineContent.push(sc);
            });

            var allScripts = externalContent.slice();
            if (data.pageScript) {
                allScripts.push({ type: 'external', src: data.pageScript, code: '' });
            }
            if (data.pageScriptPatch) {
                allScripts.push({ type: 'external', src: data.pageScriptPatch, code: '' });
            }
            allScripts = allScripts.concat(inlineContent);

            runScriptsSequentially(allScripts).then(function () {
                // Scroll al inicio
                window.scrollTo(0, 0);

                // Resaltar ítem activo en el menú
                highlightActiveMenuItem(url);

                // Actualizar history
                if (pushHistory) {
                    try {
                        history.pushState({ spa: true, url: url }, data.title || '', url);
                    } catch (e) { /* noop */ }
                }

                // Emitir evento para que otras partes del código se enteren
                try {
                    $(document).trigger('spa:loaded', [{ url: url, data: data }]);
                } catch (e) { /* noop */ }
            }).catch(function (err) {
                console.error('SPA: error cargando scripts de página', err);
            }).then(function () {
                navigating = false;
            });
        })
        .fail(function (xhr) {
            navigating = false;
            // Si la sesión expiró el servidor probablemente redirige a LogOn.
            // Hacemos un reload normal como fallback seguro.
            console.warn('SPA: fallo de petición, fallback a navegación normal', xhr.status);
            window.location.href = url;
        });
    }

    // Exponer globalmente
    window.DATPOS_spaNavigate = spaNavigate;
    window.DATPOS_isInternalPage = isInternalPage;

    // ------------------------------------------------------------------
    // Interceptar clicks en el menú lateral
    // ------------------------------------------------------------------
    // Nota: los ítems generados por CargarRoles() ya tienen
    // onclick="DatosPendientes(this)" que ahora usa spaNavigate
    // internamente. Para evitar doble navegación, solo interceptamos
    // enlaces SIN onclick inline (p. ej. el ítem estático Dashboard).
    $(document).on('click', '#listOpciones a', function (e) {
        var $a = $(this);
        var $li = $a.closest('li');

        // Si el <a> es para abrir un submenú (data-toggle="collapse"), no interceptar
        if ($li.attr('data-toggle') === 'collapse') return;

        // Si el enlace ya tiene onclick inline (DatosPendientes), dejar que
        // ese handler se encargue para no duplicar la navegación.
        var inlineOnclick = $a.attr('onclick');
        if (inlineOnclick && inlineOnclick.indexOf('DatosPendientes') !== -1) return;

        var href = $a.attr('href');
        if (!href || !isInternalPage(href)) return;

        // Si el JS de la página marcó "operación pendiente" (nuevo/editar),
        // pedir confirmación antes de descartar cambios.
        var op = $('#operacion').val();
        if (op === 'editar' || op === 'nuevo') {
            e.preventDefault();
            Swal.fire({
                title: '¿Estás seguro de que quieres salir de esta página?\n\nSe perderá la información ingresada.',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar',
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#operacion').val('');
                    spaNavigate(href, true);
                }
            });
            return;
        }

        e.preventDefault();
        spaNavigate(href, true);
    });

    // ------------------------------------------------------------------
    // Manejar back / forward del navegador
    // ------------------------------------------------------------------
    window.addEventListener('popstate', function (ev) {
        if (ev.state && ev.state.spa) {
            spaNavigate(ev.state.url || location.href, false);
        }
    });

    // ------------------------------------------------------------------
    // Inicialización: guardar el estado inicial
    // ------------------------------------------------------------------
    $(function () { replaceInitialState(); });

})(jQuery);
