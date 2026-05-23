/**
 * DatPOS - Patch JS común para módulos de Operaciones.
 *
 * Corrige bugs del JS original sin tocar los archivos en DatPOS1Web/Javascript/:
 *
 *   1. table_two_click NO habilitaba los botones Editar/Eliminar (líneas comentadas
 *      en Ingresos.js, Salida.js, Transferencias.js, GuiaRemision.js).
 *      → Lo parcheamos envolviendo la función original.
 *
 *   2. Eliminar() NO está definida en esos JS de Operaciones; agregamos una
 *      implementación genérica que llama al endpoint Eliminar del módulo.
 */
(function () {
    if (typeof window === 'undefined') return;

    // ─────────────────────────────────────────────────────────────────────
    // 1) Wrap table_two_click para habilitar botones Editar / Eliminar
    // ─────────────────────────────────────────────────────────────────────
    var _orig_table_two_click = window.table_two_click;
    if (typeof _orig_table_two_click === 'function') {
        window.table_two_click = function (tbody) {
            try { _orig_table_two_click.apply(this, arguments); }
            catch (e) { console.error('table_two_click error:', e); }
            // Habilitar botones del top bar (lo que el JS original tiene comentado)
            $('#btn_p_editar').removeClass('botones_des').addClass('botones_hab');
            $('#btn_p_eliminar').removeClass('botones_des').addClass('botones_hab');
            $('#btn_p_back').removeClass('botones_des').addClass('botones_hab');
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2) Eliminar() genérico para Operaciones (Ingresos / Salida / etc.)
    //    Si la página ya define su propio Eliminar(), no lo pisamos.
    // ─────────────────────────────────────────────────────────────────────
    if (typeof window.Eliminar !== 'function') {
        window.Eliminar = function () {
            if (!navigator.onLine) {
                Mensaje('Error', 'Sin acceso a internet.', 'error');
                return;
            }
            var id = $('#hdd_id_cbinve').val();
            if (!id || id === '0' || id === '') {
                Mensaje('Advertencia', 'Seleccione primero un registro de la lista.', 'warning');
                return;
            }
            // Confirmación
            Swal.fire({
                title: '¿Eliminar registro?',
                text: 'Esta acción es irreversible.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                // Detectar la URL del endpoint según la página actual.
                // Buscamos en el history una llamada previa, o usamos el patrón <Modulo>.aspx/Eliminar
                var endpoint = (window.OPERACION_ASPX || (location.pathname.split('/').pop().replace('.php', '.aspx'))) + '/Eliminar';

                $.ajax({
                    type: 'POST',
                    url: endpoint,
                    data: JSON.stringify({ id: id }),
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    async: false,
                    success: function (response) {
                        if (response.d === '-1') { MensajeFinSession(); return; }
                        if (response.d === true) {
                            Mensaje('Correcto', 'Registro eliminado.', 'success');
                            $(".limpiar").val("");
                            $("#hdd_id_cbinve").val("0");
                            $("#tabla > tbody").html("");
                            if (typeof Deshacer === 'function') Deshacer();
                            if (typeof CargarTabla === 'function') CargarTabla();
                        } else {
                            Mensaje('Error', 'No se pudo eliminar el registro.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Mensaje('Error', 'Error al eliminar: ' + error, 'error');
                    }
                });
            });
        };
    }
})();
