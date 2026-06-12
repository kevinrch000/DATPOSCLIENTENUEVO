

//Funcion para obtener los datos de la columnas de la Base de Datos
objColumnas = [];
var BLOQUER = "OK";

function ConsultaColumnas() {
    objColumnas = llenarobjeto('../Consultas/ConfigGeneral.aspx/ConsultaColumnas');
}

function DescargarManual() {
    window.open('/assets/manuales/ManualDatpos.pdf', '_blank');
}

function BloquearTiempo() {

    BLOQUER = "";

    Swal.fire({
        allowOutsideClick: false,
        title: 'Desbloquear',
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Aceptar',
    }).then(
        (result) => {
            if (result.isConfirmed) {
                BLOQUER = "OK";
            }
        });

}

//function DesbloquearTiempo() {
//    $.ajax({
//        type: "POST",
//        url: '../Interfaces/Home.aspx/DesbloquearTiempo',
//        data: '{contrasena: "' + $('#edtpassbloquear').val() + '" }',
//        contentType: "application/json; charset=utf-8",
//        dataType: "json",
//        async: false,
//        success: function (response) {
//            if (response.d=='OK'){
//                BLOQUER = response.d;
//                $('#ModalBloquear').modal('hide');  
//            } else {
//                Mensaje('Advertencia','Usuario o Contraseña incorrecta.','warning');
//            } 
//        },
//        error: function (xhr, status, error) {
//            alert(error);
//        }
//    });  
//     
//}


function ActualizarTimeOut() {

    $.ajax({
        type: "POST",
        url: '../Interfaces/Home.aspx/ActualizarTimeOut',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}


//$(document).ready(function () {
/**
 * Inicializa el menú lateral y los datos del usuario.
 * Idempotente: las partes que ya estén cargadas (menú, foto) se omiten
 * para que la navegación SPA no reconstruya el sidebar en cada cambio
 * de página (lo que provocaría que los submenús se colapsen y se
 * vuelvan a desplegar).
 */
function CargarMenu() {
    // 1) Solo construir el menú lateral si todavía no se cargó.
    //    Se ignora el ítem estático "dp-static-dashboard" porque siempre
    //    está presente desde el layout.
    var $menu = $('#menu-content');
    var alreadyHasItems = $menu.find('li').not('#dp-static-dashboard').length > 0;
    if (!alreadyHasItems) {
        CargarRoles();
    }

    // 2) Cargar foto/logo solo la primera vez (no cambia entre páginas).
    if (!window.__DATPOS_FOTO_LOADED) {
        try { CargarFotoUsuario(); } catch (e) { /* noop */ }
        window.__DATPOS_FOTO_LOADED = true;
    }

    // 3) draggable() se puede aplicar varias veces sin efectos colaterales,
    //    pero solo si el plugin está disponible.
    if (typeof $.fn.draggable === 'function') {
        try { $("#ModalDatosPersonales").draggable(); } catch (e) { /* noop */ }
    }
}
//});

//function VerificarAccesos() {
//    
//    $.ajax({
//        type: "POST",
//        url: '../Interfaces/Home.aspx/VerificarAccesos',
//        data: '{url: "' + $('#hdd_url').val() + '" }',
//        contentType: "application/json; charset=utf-8",
//        dataType: "json",
//        async: false,

//        success: function (response) { 
//            obj = response.d;
//            if ( 0 >= obj.length){  
//            window.location.replace("../Tablas/Almacenes.aspx");
//                 
//            } 

//        },
//        error: function (xhr, status, error) {
//            alert(error);
//        }
//    }); 
//}

function LimpiarCambiarContrasena() {
    $("#inContraActual").val("");
    $("#inContraNueva").val("");
    $("#inContraRepetir").val("");
}

function mostrarContrasenaActual() {
    //         Mensaje('Advertencia', 'Ingrese Estado de Usuario', 'warning');

    var tipo = document.getElementById("inContraActual");

    if (tipo.type == "password") {

        $("#AActual").hide();
        $("#AActual2").show();
        tipo.type = "text";
    } else {
        $("#AActual2").hide();
        $("#AActual").show();
        tipo.type = "password";
    }
}

function mostrarContrasenaNueva() {
    //         Mensaje('Advertencia', 'Ingrese Estado de Usuario', 'warning');

    var tipo = document.getElementById("inContraNueva");

    if (tipo.type == "password") {

        $("#ANueva").hide();
        $("#ANueva2").show();
        tipo.type = "text";
    } else {
        $("#ANueva2").hide();
        $("#ANueva").show();
        tipo.type = "password";
    }
}

function mostrarContrasenaBloqueada() {
    //         Mensaje('Advertencia', 'Ingrese Estado de Usuario', 'warning');

    var tipo = document.getElementById("edtpassbloquear");

    if (tipo.type == "password") {

        $("#ANuevaB").hide();
        $("#ANuevaB2").show();
        tipo.type = "text";
    } else {
        $("#ANuevaB2").hide();
        $("#ANuevaB").show();
        tipo.type = "password";
    }
}



function mostrarContrasenaRepetir() {
    //         Mensaje('Advertencia', 'Ingrese Estado de Usuario', 'warning');

    var tipo = document.getElementById("inContraRepetir");

    if (tipo.type == "password") {

        $("#ARepetir").hide();
        $("#ARepetir2").show();
        tipo.type = "text";
    } else {
        $("#ARepetir2").hide();
        $("#ARepetir").show();
        tipo.type = "password";
    }
}


function CambiarContrasena() {


    if ($('#inContraActual').val() == "") {
        MensajeError('', "Ingresar contraseña actual.", 'warning', 'Cancelar');
        return;
    } else if ($('#inContraNueva').val() == "") {
        MensajeError('', "Ingresar nueva contraseña.", 'warning', 'Cancelar');
        return;
    } else if ($('#inContraRepetir').val() == "") {
        MensajeError('', "Repetir la contraseña.", 'warning', 'Cancelar');
        return;
    } else if ($('#inContraNueva').val() != $("#inContraRepetir").val()) {
        MensajeError('', "Error: \n\n" + "La nueva contraseña con coincide.", 'warning', 'Cancelar');
        return;
    } else if ($('#inContraActual').val() == $("#inContraNueva").val() && $('#inContraActual').val() == $("#inContraRepetir").val()) {
        MensajeError('', "Error: \n\n" + "La contraseña es la misma.", 'warning', 'Cancelar');
        return;
    }

    var obj = [{
        "cpassw": $('#inContraActual').val(),
        "cpasswordnueva": $('#inContraNueva').val()
    }]
    $.ajax({
        type: "POST",
        url: '../Interfaces/Home.aspx/CambiarContrasena',
        data: JSON.stringify({
            CambioClave: obj
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d == false) {
                MensajeFinSession();
            } else {
                obj = response.d;
                if (obj[0].ccod_empresa == 'OK') {
                    Mensaje('Correcto', '', 'success');
                    $("#ModalCanbiarContrasena").modal('hide');//ocultamos el modal
                    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
                    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
                } else if (obj[0].ccod_empresa == 'ErrorContra') {
                    MensajeError('', "Error: \n\n" + "Contraseña invalida", 'warning', 'Cancelar');
                } else {
                    MensajeError('', "Error: \n\n" + obj[0].ccod_usuario, 'warning', 'Cancelar');
                }
            }
        },
        error: function (xhr, status, error) {
            alert(xhr.responseText);
        }
    });


}

var LogoEmpresa = "";

function CargarFotoUsuario() {
    var objLogo = llenarobjeto('../Interfaces/Home.aspx/CargarFotoUsuario');
    if (objLogo.length > 0) {

        if (objLogo[0].ifoto == "") {
            document.getElementById("idfoto").src = "";
            document.getElementById("idfoto").style.display = "none";
            $('#file-input').val('');
        } else {
            document.getElementById("idfoto").src = "data:image/png;base64," + objLogo[0].ifoto;
            document.getElementById("idfoto").style.display = "block";
        }

        if (objLogo[0].ilogo == "") {
            document.getElementById("idlogo").src = "";
            document.getElementById("idlogo").style.display = "none";

            $('#file-input').val('');
        } else {
            LogoEmpresa = objLogo[0].ilogo;
            document.getElementById("idlogo").src = "data:image/png;base64," + objLogo[0].ilogo;
            document.getElementById("idlogo").style.display = "block";

        }
    }
}



$(document).keydown(function (e) {

    if (e.ctrlKey && (e.which === 117)) {
        var WAS = $('#NombreColumna').val();
        if ($('#NombreColumna').val() != "") {
            $('#idInformeDepurador').modal('show');
            $("#idInformeDepurador").draggable();
            CargarDatosColumna();
            $('#NombreColumna').val("");
        }

    }
});




function ObtenerNombreColumna(row) {

    $('#NombreColumna').val(row.id);

}

function DatosPendientes(row) {
    if (!navigator.onLine) {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
        return;
    }

    // Traducir URL .aspx a .php usando el adapter
    var targetUrl = (typeof DATPOS_translateUrl === 'function') ? DATPOS_translateUrl(row.id) : '..' + row.id;

    if (row.id === '#') return;

    // Función que ejecuta la navegación (AJAX si está disponible, fallback a recarga)
    function go() {
        if (typeof window.DATPOS_spaNavigate === 'function' &&
            typeof window.DATPOS_isInternalPage === 'function' &&
            window.DATPOS_isInternalPage(targetUrl)) {
            window.DATPOS_spaNavigate(targetUrl, true);
        } else {
            window.open(targetUrl, '_self');
        }
    }

    if ($('#operacion').val() == 'editar' || $('#operacion').val() == 'nuevo') {
        Swal.fire({
            title: '¿Estás seguro de que quieres salir de esta página?\n\nSe perderá la información ingresada.',
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar',
        }).then(function (result) {
            if (result.isConfirmed) {
                // Limpiar flag de operación para evitar quedarse pegado en este estado
                $('#operacion').val('');
                go();
            }
        });
    } else {
        go();
    }
}


function CargarRoles() {

    var Modulos = "";
    var Cabecera = "";
    var Detalle = "";
    var bp = window.DATPOS_BASE_PATH || '';
    var fixIcon = (typeof DATPOS_fixIconPath === 'function') ? DATPOS_fixIconPath : function (p) { return bp + '/assets' + p; };
    var chevron = fixIcon('/Styles/img/icon/icon_chevronR.png');
    $('#menu-content').empty();
    var obj = llenarobjeto('../Interfaces/Home.aspx/CargarRoles');
    if (obj && obj.length > 0) {
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].corden < 100 && obj[i].cstatus == "1") {
                var iconSrc = fixIcon(obj[i].curl_src);
                Modulos = '<li id="' + obj[i].cli_menu + '" value="' + obj[i].corden + '" data-toggle="collapse" data-target="#' + obj[i].cul_menu + '" class="collapsed"><a href="#"><img src="' + iconSrc + '" style="width: 25px;">' + obj[i].cdsc_menu + '</a></li><ul class="sub-menu collapse" id="' + obj[i].cul_menu + '"></ul>'
                $("#menu-content").append(Modulos);
                for (var j = 0; j < obj.length; j++) {
                    if (obj[j].corden < 1000 && obj[i].id_menu == obj[j].nid_menupadre && obj[j].cstatus == "1") {
                        Cabecera = '<li id="' + obj[j].cli_menu + '" value="' + obj[j].corden + '" data-toggle="collapse" data-target="#' + obj[j].cul_menu + '" ><a  href="#" onclick="DatosPendientes(this)" id="' + obj[j].curl_href + '"  ><img src="' + chevron + '" style="width: 17px; top: 12px; left: 33px;">' + obj[j].cdsc_menu + '</a></li><ul id="' + obj[j].cul_menu + '" class="sub-menu collapse"></ul>'

                        $("#" + obj[i].cul_menu).append(Cabecera);
                        for (var k = 0; k < obj.length; k++) {
                            if (obj[k].corden > 1000 && obj[j].id_menu == obj[k].nid_menupadre && obj[k].cstatus == "1") {
                                Detalle = '<li id="' + obj[k].cli_menu + '"  ><a href="#" onclick="DatosPendientes(this)" id="' + obj[k].curl_href + '"  style="left: 32px;"><img src="' + chevron + '" style="width: 17px; top: 12px; left: 37px;">' + obj[k].cdsc_menu + '</a></li>'

                                $("#" + obj[j].cul_menu).append(Detalle);
                            }
                        }
                    }
                }
            }
        }
    }
}




function CerrarSeccion() {
    var bp = window.DATPOS_BASE_PATH || '';
    window.open(bp + '/pages/migcliente/LogOn.php');
}



//Captura click derecho para generar Menu context  
(function ($, window) {
    $.fn.contextMenu = function (settings) {
        return this.each(function () {
            // Open context menu
            $(this).on("contextmenu", function (e) {
                // return native menu if pressing control
                if (e.ctrlKey) return;
                //open menu
                var $menu = $(settings.menuSelector)
                    .data("invokedOn", $(e.target))
                    .show()
                    .css({
                        position: "absolute",
                        left: getMenuPosition(e.clientX, 'width', 'scrollLeft'),
                        top: getMenuPosition(e.clientY, 'height', 'scrollTop')
                    })
                    .off('click')
                    .on('click', 'a', function (e) {
                        $menu.hide();
                        var $invokedOn = $menu.data("invokedOn");
                        var $selectedMenu = $(e.target);

                        settings.menuSelected.call(this, $invokedOn, $selectedMenu);
                    });
                return false;
            });
            //make sure menu closes on any click
            $('body').click(function () {
                $(settings.menuSelector).hide();
            });
        });
        function getMenuPosition(mouse, direction, scrollDir) {
            var win = $(window)[direction](),
                scroll = $(window)[scrollDir](),
                menu = $(settings.menuSelector)[direction](),
                position = mouse + scroll;
            // opening menu would pass the side of the page
            if (mouse + menu > win && menu < mouse)
                position -= menu;
            return position;
        }
    };
})(jQuery, window);


function DatosGenerales() {
    var obj = llenarobjeto('../Interfaces/Home.aspx/DatosGenerales');
    if (obj.length > 0) {

        $('#sitNomTienda').text(obj[0].cdsc_tienda);
        $('#sitNomAlmacen').text(obj[0].cdsc_alm);
        $('#sitNomCaja').text(obj[0].cdsc_caja);
        $('#sitIdListPreNor').text(obj[0].nlista_pre_normal);
        $('#sitNomListPreNor').text(obj[0].cdsc_listpreNorm);
        $('#sitIdListPrePre').text(obj[0].nlista_pre_preferencial);
        $('#sitNomListPrePre').text(obj[0].cdsc_listprePref);
        $('#idRolDescripcion').text(obj[0].cdescripcion);
    }
}


function PerdidaFoco(obj) {
    if ($(obj).val().length == 0) $(obj).val("0.00");
    else {
        if (parseFloat($(obj).val()).toFixed(2) < 0) $(obj).val($(obj).val() * (-1));
    }
}

function PerdidaFocoNumEntero(obj) {
    if ($(obj).val().length == 0) $(obj).val("0");
    else {
        if (parseFloat($(obj).val()).toFixed(2) < 0) $(obj).val($(obj).val() * (-1));
    }
}

function MensajeFinSession() {
    var bp = window.DATPOS_BASE_PATH || '';
    Swal.fire({
        title: "Tiempo de sesión expirado.",
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Salir'
    }).then(
        (result) => {
            if (result.value) {
                window.location.replace(bp + "/pages/migcliente/LogOn.php");
            }
        });
}

function tab_listaclick() {

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    //        $('#btn_p_grabar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");

    if ($('#operacion').val() != '') {
        $("#table_id").prop("style", 'pointer-events: none;opacity: 0.4;');
    }
}

/**
 * Expande un submenú del sidebar SOLO si todavía no está abierto. Esto
 * evita que `trigger("click")` colapse un submenú que ya estaba
 * desplegado (lo que provocaba el "salto" visual al navegar en SPA).
 */
function _DATPOS_ensureMenuOpen(triggerId) {
    var $trigger = $('#' + triggerId);
    if (!$trigger.length) return;
    var targetSel = $trigger.attr('data-target') || $trigger.find('[data-target]').attr('data-target');
    if (targetSel) {
        var $target = $(targetSel);
        var isOpen = $target.hasClass('in') || $target.hasClass('show');
        if (!isOpen) $trigger.trigger('click');
    } else {
        // Sin data-target → fallback al comportamiento legacy
        $trigger.trigger('click');
    }
}

function inicar_menu_nivel2(titulo, menu_nivel1, menu_nivel2, numeromenuactivo) {
    $('#titulo').val('DATPOS | ' + titulo);
    $('#id_titulo').text(titulo);
    $('.nav-tabs li:eq(' + numeromenuactivo + ') a').tab('show');

    _DATPOS_ensureMenuOpen(menu_nivel1);
    $('#' + menu_nivel2).attr("class", "active");

    traducir_tabla();
}

function Desabilitar() {
    $(".readonl").prop("readonly", true);
    $(".disabled").prop("disabled", true);
    $("#operacion").val("");
    $('.fa_enabled').removeClass("fa_enabled").addClass("fa_disabled");

}

// FilterStatus(n) — Filtra la tabla principal (#table_id) por la columna "Estado"
// n=1: Mostrar Todos | n=2: Activos | n=3: Inactivos
function FilterStatus(status) {
    var table = $('#table_id').DataTable();
    var searchTerm = '';
    var useRegex = false;
    switch (status) {
        case 1: searchTerm = ''; useRegex = false; break; // Todos
        case 2: searchTerm = '^Activo$'; useRegex = true; break; // Activos
        case 3: searchTerm = '^Inactivo$'; useRegex = true; break; // Inactivos
        default: searchTerm = ''; useRegex = false; break;
    }
    // Busca en la columna cuyo encabezado sea "Estado"
    var found = false;
    table.columns().every(function () {
        if ($(this.header()).text().trim() === 'Estado') {
            this.search(searchTerm, useRegex, false).draw();
            found = true;
        }
    });
    // Fallback: si no encontró columna "Estado", aplica búsqueda global
    if (!found) {
        table.search(searchTerm, useRegex, false).draw();
    }
}

// FilterTipo(val) — Filtra la tabla principal (#table_id) por la columna "Tipo" u "Op."
// val='': Mostrar Todos | val='COMP': solo COMP, etc.
function FilterTipo(val) {
    var table = $('#table_id').DataTable();
    if (val === '') {
        // Limpiar filtro de todas las columnas
        table.columns().search('').draw();
    } else {
        // Buscar columna cuyo encabezado contenga "Tipo"
        var found = false;
        table.columns().every(function () {
            var header = $(this.header()).text().trim();
            if (header === 'Tipo' || header === 'Tipo Op.' || header === 'ctipo') {
                this.search('^' + val + '$', true, false).draw();
                found = true;
            }
        });
        if (!found) {
            table.search(val, false, false).draw();
        }
    }
}

function Deshacer() {
    $('.nav-tabs li:eq(' + $('#hdd_numeromenus').val() + ') a').tab('show');
    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_grabar').removeClass("botones_hab").addClass("botones_des");

    $('#operacion').val('');
    $("#table_id").prop("style", 'pointer-events: all; opacity: 100%;');
    $('#btn_p_back').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_nuevo').removeClass("botones_des").addClass("botones_hab");
}

function Editar() {
    $(".disabled").prop("disabled", false);
    $("#operacion").val("editar");
    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
}

function Mensaje(titulo, texto, icono) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        confirmButtonText: 'Ok'
    })
}

function MensajeError(titulo, texto, icono, ButtonText) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        confirmButtonColor: '#3085d6',
        confirmButtonText: ButtonText
    })
}



function llenarobjeto(st_url) {

    var obj;

    $.ajax({
        type: "POST",
        url: st_url,
        data: null,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d == "-1") MensajeFinSession();
            else obj = response.d;
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    return obj;
}

function inicar_menu_nivel3(titulo, menu_nivel1, menu_nivel2, menu_nivel3, numeromenuactivo) {

    $('#titulo').val(titulo);

    $('#id_titulo').text(titulo);
    $('.nav-tabs li:eq(' + numeromenuactivo + ') a').tab('show');

    _DATPOS_ensureMenuOpen(menu_nivel1);
    _DATPOS_ensureMenuOpen(menu_nivel2);
    $('#' + menu_nivel3).attr("class", "active");

    traducir_tabla();
}

function traducir_tabla() {

    $.extend(true, $.fn.dataTable.defaults, {
        "language": {
            "decimal": ",",
            "thousands": ".",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoPostFix": "",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "loadingRecords": "Cargando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "processing": "Procesando...",
            "search": "",
            "searchPlaceholder": "Buscar",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            //only works for built-in buttons, not for custom buttons
            "buttons": {
                "create": "Nuevo",
                "edit": "Cambiar",
                "remove": "Borrar",
                "copy": "Copiar",
                "csv": "fichero CSV",
                "excel": "tabla Excel",
                "pdf": "documento PDF",
                "print": "Imprimir",
                "colvis": "Visibilidad columnas",
                "collection": "Colección",
                "upload": "Seleccione fichero...."
            },
            "select": {
                "rows": {
                    _: '%d filas seleccionadas',
                    0: 'clic fila para seleccionar',
                    1: 'una fila seleccionada'
                }
            }
        }
    });
}

function exportarExcelGrid() {
    var $table = $('table:visible').first();
    if (!$table.length) {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            Swal.fire('Advertencia', 'No se encontró ninguna tabla activa para exportar.', 'warning');
        } else if (typeof alertify !== 'undefined' && typeof alertify.alert === 'function') {
            alertify.alert('Advertencia', 'No se encontró ninguna tabla activa para exportar.');
        } else {
            alert('No se encontró ninguna tabla activa para exportar.');
        }
        return;
    }

    var title = $('.modern-page h4, .modern-page h3, h3, h2, title, #nombre_documento').first().text().trim() || 'Reporte_DatPOS';
    title = title.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_ ]/g, '_').replace(/ /g, '_');

    var csvLines = [];

    // 1. Obtener cabeceras
    var headers = [];
    $table.find('thead th').each(function () {
        var $th = $(this);
        // Omitir columnas con inputs (como checkboxes) o vacías
        if ($th.find('input').length) {
            return;
        }
        var txt = $th.text().trim();
        if (txt !== '') {
            headers.push('"' + txt.replace(/"/g, '""') + '"');
        }
    });
    if (headers.length > 0) {
        csvLines.push(headers.join(';'));
    }

    // 2. Obtener filas
    $table.find('tbody tr').each(function () {
        var rowData = [];
        var hasVisibleData = false;

        $(this).find('td').each(function () {
            var $cell = $(this);
            // Omitir celdas que solo contengan botones de acción o inputs
            if ($cell.find('input, i, a.fa-pencil, a.fa-trash').length && !$cell.text().trim()) {
                return;
            }
            var txt = $cell.text().trim();
            txt = txt.replace(/"/g, '""');
            rowData.push('"' + txt + '"');
            hasVisibleData = true;
        });

        if (hasVisibleData && rowData.length > 0) {
            csvLines.push(rowData.join(';'));
        }
    });

    // 3. Obtener pie de página (totales)
    $table.find('tfoot tr').each(function () {
        var rowData = [];
        $(this).find('th, td').each(function () {
            var txt = $(this).text().trim().replace(/"/g, '""');
            rowData.push('"' + txt + '"');
        });
        if (rowData.length > 0) {
            csvLines.push(rowData.join(';'));
        }
    });

    var csvContent = csvLines.join('\r\n');

    // Usar la firma BOM UTF-8 (\uFEFF) y tipo ms-excel para descargar con extension .xls de forma limpia
    var blob = new Blob(['\uFEFF' + csvContent], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    var blobUrl = URL.createObjectURL(blob);

    var a = document.createElement("a");
    a.href = blobUrl;
    a.download = title + "_" + new Date().toISOString().slice(0, 10) + ".xls";
    document.body.appendChild(a);
    a.click();

    setTimeout(function () {
        document.body.removeChild(a);
        URL.revokeObjectURL(blobUrl);
    }, 100);
}

function ImprimirComprobante(zonaId) {
    var mywindow = window.open('', '_blank', 'width=320,height=600');
    if (!mywindow) {
        alert('Por favor, permita las ventanas emergentes (popups) para poder imprimir el comprobante.');
        return;
    }

    // Obtener el HTML del contenido de la factura/boleta
    var ticketHtml = document.getElementById(zonaId).innerHTML;
    var bp = window.DATPOS_BASE_PATH || '';

    // Construir un documento HTML completo con estilos incluidos para la impresión
    var htmlContent = '<html><head>' +
        '<title>Imprimir Comprobante</title>' +
        '<link href="' + bp + '/assets/Styles/css/bootstrap.css" rel="stylesheet" type="text/css" />' +
        '<style>' +
        '  body { margin: 0; padding: 0; font-family: "Courier New", Courier, monospace !important; color: #000 !important; background-color: #fff !important; }' +
        '  #zona-imprimir-content { width: 76mm !important; padding: 3mm !important; box-sizing: border-box !important; }' +
        '  .ticket-divider { border-top: 1px dashed #000000 !important; margin: 6px 0 !important; height: 1px !important; padding: 0 !important; display: block !important; width: 100% !important; float: left !important; }' +
        '  .col-xs-12, .col-xs-8, .col-xs-6, .col-xs-4, .col-xs-3 { box-sizing: border-box !important; padding-left: 2px !important; padding-right: 2px !important; margin: 0 !important; min-height: 1px !important; float: left !important; }' +
        '  .col-xs-12 { width: 100% !important; } .col-xs-8 { width: 66.66666667% !important; } .col-xs-6 { width: 50% !important; } .col-xs-4 { width: 33.33333333% !important; } .col-xs-3 { width: 25% !important; }' +
        '  #idlogoTicket { display: block !important; width: 50px !important; margin: 6px auto 8px !important; }' +
        '  #nombre_empresa1, #direccion_empresa, #direccionubigeo_empresa, #nombre_tienda, #direccion_tienda, #ubigeo_tienda, #nombre_documento, #codigo_documento, #son_documento, #vendedor, #codigo_caja { width: 100% !important; float: left !important; text-align: center !important; margin-bottom: 2px !important; }' +
        '  #ruc_empresa, #fecha_documento { text-align: left !important; }' +
        '  #telefono_tienda, #hora_documento { text-align: right !important; }' +
        '  #nombre_cliente, #direccion_cliente, #ruc_cliente { width: 100% !important; float: left !important; text-align: left !important; margin-bottom: 2px !important; }' +
        '  div[style*="text-align: center;"] .col-xs-3 { text-align: center !important; font-size: 10px !important; font-weight: 700 !important; }' +
        '  #div_articlosdocumento { width: 100% !important; float: left !important; clear: both !important; }' +
        '  #div_articlosdocumento > div, #div_articlosdocumento > .row { width: 100% !important; float: left !important; }' +
        '  #div_cobranzadocumento { width: 100% !important; float: left !important; clear: both !important; }' +
        '  #div_cobranzadocumento > div, #div_cobranzadocumento > .row { width: 100% !important; float: left !important; }' +
        '  @media print {' +
        '    @page { size: 80mm auto; margin: 0; }' +
        '    body { margin: 0; padding: 0; }' +
        '    #zona-imprimir-content { width: 80mm !important; padding: 4mm !important; }' +
        '  }' +
        '</style>' +
        '</head><body>' +
        '<div id="zona-imprimir-content">' + ticketHtml + '</div>' +
        '</body></html>';

    mywindow.document.write(htmlContent);
    mywindow.document.close();

    setTimeout(function () {
        mywindow.focus();
        mywindow.print();
        mywindow.close();
    }, 500);
}
function inicializarPrevenirAutofill() {
    var selector = 'input.prevent-autofill, .dataTables_filter input, input[name*="search"], input[id*="search"], input[x-model*="search"], input[x-model*="filtro"], input[x-model*="filter"]';
    $(selector).each(function () {
        var $el = $(this);
        // Si no está deshabilitado y no tiene ya un readonly propio
        if (!$el.prop('disabled') && !$el.attr('readonly')) {
            $el.attr('readonly', true);
            $el.data('is-autofill-readonly', true);
        }
    });
}

$(document).ready(function () {
    inicializarPrevenirAutofill();

    // Escuchar el evento de dibujo de DataTables para aplicar a campos dinámicos
    $(document).on('draw.dt', function () {
        inicializarPrevenirAutofill();
    });
});

$(document).on('focus', 'input.prevent-autofill, .dataTables_filter input, input[name*="search"], input[id*="search"], input[x-model*="search"], input[x-model*="filtro"], input[x-model*="filter"]', function () {
    var $el = $(this);
    if ($el.attr('readonly') && $el.data('is-autofill-readonly') === true) {
        $el.removeAttr('readonly');
    }
});

$(document).on('blur', 'input.prevent-autofill, .dataTables_filter input, input[name*="search"], input[id*="search"], input[x-model*="search"], input[x-model*="filtro"], input[x-model*="filter"]', function () {
    var $el = $(this);
    // Si no está deshabilitado, volver a poner readonly para mantener la prevención
    if (!$el.prop('disabled')) {
        $el.attr('readonly', true);
        $el.data('is-autofill-readonly', true);
    }
});