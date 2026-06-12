(function ($, window) {
    'use strict';

    // Limpiar cualquier listener previo registrado con el namespace .mqr
    // para evitar acumulación durante la navegación SPA.
    $(document).off('.mqr');

    var BP = window.DATPOS_BASE_PATH || '';
    var PLACEHOLDER = BP + '/assets/Styles/img/icon/icon_LogoCircle.png';

    var _codigo = '';
    var _precio = 0;
    var _artObs = null;

    /* ═══════════════════════════════════════════════════
       FEATURE 1 — Modal de cantidad
    ═══════════════════════════════════════════════════ */
    window.PasarArticulo = function (codigo) {
        var $card = $('#div_articulos').find('[id]').filter(function () {
            return this.id == codigo;
        });
        var nombre = ($card.find('.cuadrado_desc').text().trim()
            || $card.find('p').last().text().trim()
            || 'Artículo');
        var precioTx = $card.find('.precio').first().text().replace(/[^0-9.,]/g, '').replace(',', '.');
        var precio = parseFloat(precioTx) || 0;
        var imgSrc = $card.find('img').first().attr('src') || '';

        if (!imgSrc || imgSrc === 'data:image/png;base64,' || imgSrc.length < 30) {
            imgSrc = PLACEHOLDER;
        }

        _codigo = codigo;
        _precio = precio;

        $('#mqr_nombre').text(nombre);
        $('#mqr_precio').text('S/ ' + precio.toFixed(2));
        $('#mqr_cantidad').val(1);
        $('#mqr_subtotal').text('S/ ' + precio.toFixed(2));
        $('#mqr_img')
            .off('error.mqr')
            .on('error.mqr', function () { $(this).attr('src', PLACEHOLDER); })
            .attr('src', imgSrc);

        $('#modalCantidadRapida').modal('show');
        $('#modalCantidadRapida').one('shown.bs.modal', function () {
            $('#mqr_cantidad').focus().select();
        });
    };

    window.mqrAjustar = function (delta) {
        var v = Math.max(1, (parseInt($('#mqr_cantidad').val(), 10) || 1) + delta);
        $('#mqr_cantidad').val(v).trigger('input');
    };

    $(document).on('input.mqr', '#mqr_cantidad', function () {
        var cant = Math.max(1, parseInt($(this).val(), 10) || 1);
        $(this).val(cant);
        $('#mqr_subtotal').text('S/ ' + (cant * _precio).toFixed(2));
    });

    $(document).on('keydown.mqr', '#mqr_cantidad', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            $('#mqr_confirmar').trigger('click');
        }
    });

    $(document).on('click.mqr', '#mqr_confirmar', function () {
        var cantidad = Math.max(1, parseInt($('#mqr_cantidad').val(), 10) || 1);
        $('#modalCantidadRapida').modal('hide');
        _confirmarArticulo(_codigo, cantidad);
    });

    function _confirmarArticulo(codigo, cantidad) {
        $.ajax({
            type: 'POST',
            url: 'Facturacion.aspx/ConsultarArticuloPrecio',
            data: '{"codigo": "' + String(codigo) + '" }',  // ← corregido: comillas en la clave
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            async: true,
            success: function (response) {
                // ── BUG 2 CORREGIDO ──────────────────────────────────────────────
                // Array vacío = artículo no encontrado, NO sesión expirada.
                // Solo llamamos MensajeFinSession si el servidor indica explícitamente
                // que la sesión expiró (por ejemplo con un código de error propio).
                if (!response.d || !response.d.length) {
                    if (typeof Mensaje === 'function') {
                        Mensaje('Artículo no encontrado',
                            'El código "' + codigo + '" no existe en el catálogo.',
                            'warning');
                    }
                    return;
                }
                // ─────────────────────────────────────────────────────────────────
                var d = response.d[0];
                var npre = parseFloat(d.npre_uni) || 0;
                var importe = (npre * cantidad).toFixed(2);
                var igv = d.igv || 0;
                var isc = d.isc || 0;
                var tasa = (typeof NumeroSeguro === 'function')
                    ? NumeroSeguro(igv) + NumeroSeguro(isc) : 0;
                var igvCant = (typeof CalcularImpuestoIncluido === 'function')
                    ? CalcularImpuestoIncluido(importe, igv, tasa).toFixed(2) : 0;
                var iscCant = (typeof CalcularImpuestoIncluido === 'function')
                    ? CalcularImpuestoIncluido(importe, isc, tasa).toFixed(2) : 0;

                var $tbody = $('#table_Articulos').find('tbody');
                $tbody.append(
                    $('<tr>')
                        .append($('<td>').text(d.cdsc_articulo))
                        .append($('<td>').text(cantidad))
                        .append($('<td>').text(d.npre_uni))
                        .append($('<td class="monto">').text(importe))
                        .append($('<td class="text-center">').html(
                            '<a class="fa fa-pencil" data-toggle="modal" ' +
                            'data-target="#modalEditarCantidad" onclick="EditarCantidad(this)"></a>'))
                        .append($('<td class="text-center">').html(
                            '<a class="fa fa-trash fa_enabled" onclick="Eliminar(this)"></a>'))
                        .append($('<td style="display:none">').text(d.ccod_articulo || codigo))
                        .append($('<td class="igv" style="display:none">').text(igv))
                        .append($('<td class="isc" style="display:none">').text(isc))
                        .append($('<td class="creainventario" style="display:none">').text(d.ctip_articulo))
                        .append($('<td class="costo" style="display:none">').text(d.npre_costo))
                        .append($('<td class="igv_por_cantidad" style="display:none">').text(igvCant))
                        .append($('<td class="isc_por_cantidad" style="display:none">').text(iscCant))
                        .append($('<td style="display:none">').text(d.state))
                        .append($('<td style="display:none">').text(d.ndes_max))
                        .append($('<td style="display:none" class="descuento">').text(0))
                        .append($('<td style="display:none">'))
                );

                if (typeof CalcularTotal === 'function') CalcularTotal();
                var $dv = $('#div_venta');
                $dv.scrollTop($dv.prop('scrollHeight'));
            },
            error: function () {
                if (typeof Mensaje === 'function') {
                    Mensaje('Error', 'No se pudo obtener el precio del artículo.', 'error');
                }
            }
        });
    }

    /* ═══════════════════════════════════════════════════
       FEATURE 2 — Imágenes placeholder
    ═══════════════════════════════════════════════════ */
    function _fixImagenes() {
        $('#div_articulos article img, #div_articulos img').each(function () {
            var src = $(this).attr('src') || '';
            var esVacio = !src || src === 'data:image/png;base64,' || src.length < 30;
            if (esVacio) {
                $(this).attr('src', PLACEHOLDER).css('display', 'block');
            }
        });
    }

    $(document).on('error.mqr', '#div_articulos article img, #div_articulos img', function () {
        $(this).attr('src', PLACEHOLDER).css('display', 'block');
    });

    // _flattenArticulosGrid() fue ELIMINADA.
    // CompletarArticulosCategoria() ahora genera HTML plano (divs directos,
    // sin .row/.col-md-3), así que ya no es necesario "aplanar" el grid.
    // Esto corrige el bug de artículos duplicados que ocurría cuando el
    // MutationObserver re-disparaba _flattenArticulosGrid al mover nodos.

    function _initObserver() {
        if (_artObs) { try { _artObs.disconnect(); } catch (e) { } }
        _artObs = null;
        var target = document.getElementById('div_articulos');
        if (!target) return;
        _artObs = new MutationObserver(function () {
            if (_artObs) _artObs.disconnect();
            _fixImagenes();
            if (_artObs && target) _artObs.observe(target, { childList: true, subtree: true });
        });
        _artObs.observe(target, { childList: true, subtree: true });
    }

    /* ═══════════════════════════════════════════════════
       FEATURE 3 — Botón guía
    ═══════════════════════════════════════════════════ */
    function _initGuia() {
        var $btn = $('#btnGuiaFact');
        if (!$btn.length) return;
        $btn.css({ display: 'flex', visibility: 'visible', opacity: '1' });
    }

    /* ═══════════════════════════════════════════════════
       INIT
    ═══════════════════════════════════════════════════ */
    function _initFeatures() {
        _initObserver();
        _initGuia();
        _fixImagenes();
    }

    $(document).ready(function () {
        _initFeatures();
    });

}(jQuery, window));