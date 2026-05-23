// DatPOS - Fix global de modales Bootstrap
// Garantiza que las "lupas" / botones data-toggle="modal" abran su contenido aunque
// el modal este dentro de un contenedor con transform / overflow / position relative
// que rompe el position:fixed. Tambien evita que se quede el backdrop atrapando los clicks.
(function () {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;

    function cleanupOrphanBackdrops() {
        var anyOpen = $('.modal.in, .modal.show').filter(function () {
            return $(this).is(':visible');
        }).length > 0;
        if (!anyOpen) {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        }
    }

    // 1. Mover el modal a <body> al abrirlo para escapar transform/overflow ancestrales.
    $(document).on('show.bs.modal', '.modal', function () {
        var $m = $(this);
        if ($m.parent('body').length === 0) {
            $m.appendTo('body');
        }
        // Asegurar visibilidad por encima del menu lateral.
        $m.css('z-index', 1000000010);
    });

    // 2. Forzar reflujo del modal al mostrarse (algunos casos quedan en opacity 0 sin .in).
    $(document).on('shown.bs.modal', '.modal', function () {
        var $m = $(this);
        if (!$m.hasClass('in') && !$m.hasClass('show')) {
            $m.addClass('in');
        }
        $m.css({ display: 'block' });
        var $backdrop = $('.modal-backdrop').last();
        $backdrop.css('z-index', 1000000000);
    });

    // 3. Limpieza al cerrar.
    $(document).on('hidden.bs.modal', '.modal', function () {
        cleanupOrphanBackdrops();
    });

    // 4. Click sobre el backdrop o ESC siempre cierra cualquier modal abierto.
    $(document).on('click', '.modal-backdrop', function () {
        $('.modal.in, .modal.show').modal('hide');
        setTimeout(cleanupOrphanBackdrops, 350);
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            if ($('.modal.in, .modal.show').length) {
                $('.modal.in, .modal.show').modal('hide');
            }
            setTimeout(cleanupOrphanBackdrops, 350);
        }
    });

    // 5. Boton de seguridad: doble click sobre el body con backdrop visible y sin modal en pantalla -> limpia.
    $(document).on('dblclick', 'body', function () {
        if ($('.modal-backdrop').length && !$('.modal.in:visible, .modal.show:visible').length) {
            cleanupOrphanBackdrops();
        }
    });

    // 6. Patch para llamadas legacy $('#xxx').modal('show') que apunten a un id inexistente:
    //    si el usuario hace click en data-toggle="modal" cuyo target no existe, no dejamos backdrop fantasma.
    $(document).on('click', '[data-toggle="modal"]', function () {
        var target = $(this).data('target') || $(this).attr('href');
        if (target && $(target).length === 0) {
            console.warn('[modal_fix] target no existe:', target);
            setTimeout(cleanupOrphanBackdrops, 200);
        }
    });

    // 7. Tab por defecto = "Lista" en lugar de "Datos" -- SOLO para paginas CRUD
    //    donde Lista es el listado primario. En paginas POS (Facturacion,
    //    FacturaListaPrecio, NotaCredito, NotaDebito, Anulacion) la tab Lista
    //    aparece marcada con style="display:none" en su <a> y NO debe activarse:
    //    Datos es la pantalla real de trabajo (selector de articulos / cobranza).
    //    Se ejecuta tras un timeout para no pisar las llamadas internas que cada page hace
    //    en su document.ready (CargarTabla, etc.) -- y tampoco si la URL trae #Datos.
    $(document).ready(function () {
        setTimeout(function () {
            if (window.location.hash === '#Datos') return;
            var $listaLink = $('.nav-tabs a[href="#Lista"]');
            var $datosLink = $('.nav-tabs a[href="#Datos"]');
            if (!$listaLink.length || !$datosLink.length) return;
            // Si la tab Lista esta oculta (display:none en el <a> o su <li>),
            // significa que la pagina la mantiene escondida intencionalmente --
            // no la forzamos como tab activa.
            var linkHidden = $listaLink.css('display') === 'none' ||
                             $listaLink.parent('li').css('display') === 'none';
            if (linkHidden) {
                try { $datosLink.tab('show'); } catch (e) {}
                return;
            }
            // Activar Lista (CRUD por defecto)
            try { $listaLink.tab('show'); } catch (e) {}
            // Asegurar que el pane Datos quede oculto y Lista visible
            $('#Datos').removeClass('in active').removeClass('active');
            $('#Lista').addClass('active');
        }, 400);
    });
})();
