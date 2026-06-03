var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();


function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
    if ($("#NombreColumna").val() == "tb_codigo") {
        DscTabla = "ad_ctrol";
        DscColumna = "id_rol";
        Nombre = "Código del rol";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "tb_descripcion") {
        DscTabla = "ad_ctrol";
        DscColumna = "cdescripcion";
        Nombre = "Nombre del rol";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_estado") {
        DscTabla = "ad_ctrol";
        DscColumna = "cstatus";
        Nombre = "Estado del rol";
        Estado = "Obligatorio";
        TipoDato = "";
    }

    for (var i = 0; i < objColumnas.length; i++) {
        if (DscColumna == objColumnas[i].DscColumna && DscTabla == objColumnas[i].DscTabla) {
            $("#txt_nombreCampo").text(Nombre);
            $("#txt_TipoDato").text(objColumnas[i].TipoDato);
            $("#txt_estado").text(Estado);
            $("#txt_longitud").text(TipoDato + " " + objColumnas[i].longitud);
            $("#txt_cantidadEntero").text(objColumnas[i].CantEnteros);
            $("#txt_cantidadDecimales").text(objColumnas[i].CantDecimales);
        }
    }

}

function CkMarcarTodo() {
    var marcar = $('#idCkMarcarTodo').is(':checked');
    $('#ColumnaRoles input[type="checkbox"]').prop('checked', marcar).each(function () {
        _pintarFilaAcceso($(this));
    });
    if (marcar) $("#idCkDesmarcarTodo").prop("checked", false);
}

function CkDesmarcarTodo() {
    var desmarcar = $('#idCkDesmarcarTodo').is(':checked');
    if (desmarcar) {
        $('#ColumnaRoles input[type="checkbox"]').prop('checked', false).each(function () {
            _pintarFilaAcceso($(this));
        });
        $("#idCkMarcarTodo").prop("checked", false);
    }
}

// ----------------------------------------------------------------------
// Arbol de Accesos (generico). Antes el comportamiento estaba cableado a
// ids/clases fijos (101..108 -> limpiarChecked2/8/12...), por lo que los
// menus principales (TABLAS, OPERACIONES, ...) no propagaban ni se
// guardaban si el catalogo no coincidia con esos numeros. Ahora la
// relacion padre/hijo se resuelve por el DOM usando data-idmenu (id_menu
// propio) y name (id_menu del padre).
// ----------------------------------------------------------------------
function _pintarFilaAcceso($chk) {
    $chk.closest('.input-group').toggleClass('acc-sel', $chk.is(':checked'));
}

function marcarAncestrosAcceso($chk) {
    var padreId = $chk.attr('name');
    var guarda = 0;
    while (padreId && guarda++ < 50) {
        var $padre = $('#ColumnaRoles input[type="checkbox"][data-idmenu="' + padreId + '"]');
        if (!$padre.length) break;
        $padre.prop('checked', true);
        _pintarFilaAcceso($padre);
        padreId = $padre.attr('name');
    }
}

function _bindArbolAccesos() {
    $('#ColumnaRoles').off('change.acc').on('change.acc', 'input[type="checkbox"]', function () {
        var checked = this.checked;
        var idmenu = $(this).attr('data-idmenu');
        if (idmenu) {
            // Cascada hacia los hijos: marca/desmarca todo el contenedor.
            $('#' + idmenu).find('input[type="checkbox"]').prop('checked', checked).each(function () {
                _pintarFilaAcceso($(this));
            });
        }
        // Al marcar un hijo se marcan sus padres para mantener la rama completa.
        if (checked) marcarAncestrosAcceso($(this));
        _pintarFilaAcceso($(this));
    });
    $('#ColumnaRoles input[type="checkbox"]').each(function () { _pintarFilaAcceso($(this)); });
}

function Guardar() {

    if (navigator.onLine) {

        if ($('#tb_descripcion').val() == "") {
            Mensaje('Advertencia', 'Ingresar nombre del rol.', 'warning');
            return;
        } else if ($('#ddl_estado').val() == null) {
            Mensaje('Advertencia', 'Ingresar estado del rol.', 'warning');
            return;
        }

        // Recolecta el estado de TODAS las casillas del arbol de accesos
        // de forma generica, sin depender de cordenes/ids fijos. El value de
        // cada checkbox es su corden y su name es el id_menu del padre. De esta
        // forma funciona con cualquier catalogo de menus (no esta cableado a
        // 101..113 / 1001.. como antes, que era la causa de que los menus
        // principales como TABLAS u OPERACIONES no se guardaran).
        var obj_menu = [];
        $('#ColumnaRoles input[type="checkbox"]').each(function () {
            var corden = $(this).val();
            if (corden === undefined || corden === '') return;
            obj_menu.push({
                "corden": String(corden),
                "cstatus": $(this).is(':checked'),
                "nid_menupadre": $(this).attr("name") || '',
                "nivel": String($(this).attr('data-nivel') || '')
            });
        });

        // Los modulos (cabeceras de seccion) ya no se envian desde el cliente:
        // el SP webDatpos_cargarRol incluye automaticamente a los ancestros de
        // cada menu concedido, por lo que el modulo se resuelve en el servidor.
        var obj_modulo = [];

        var obj_rol = [
            {
                "id_rol": $('#tb_codigo').val(),
                "cdescripcion": $('#tb_descripcion').val(),
                "cstatus": $('#ddl_estado').val()
            }
        ]

        $.ajax({
            type: "POST",
            url: 'Roles.aspx/Guardar',
            data: JSON.stringify({
                rol: obj_rol,
                menu: obj_menu,
                modulo: obj_modulo,
                operacion: $('#operacion').val()
            }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if (response.d == "-1") MensajeFinSession();
                else {
                    obj = response.d;
                    if (response.d[1] == 'OK') {
                        Mensaje('Correcto', '', 'success');

                        CargarRoles();
                        Desabilitar();
                        Deshacer();
                        CargarTabla();
                        if ($('#hdd_numerofilas').val() > 0) {
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                        inicar_menu_nivel2('Roles', '1_li_Administracion', '2_li_Roles', '2');
                    } else {
                        MensajeError('', "Error: \n\n" + response.d[2], 'warning', 'Cancelar');

                    }
                }

            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}




function Nuevo() {
    $('#lb_codigo').text("");
    $('#ColumnaRoles input[type="checkbox"]').prop('checked', false).each(function () {
        _pintarFilaAcceso($(this));
    });
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    $('#tablanumerador > tbody').html('');

    $(".readonl").prop("readonly", false);
    $(".disabled").prop("disabled", false);
    $(".limpiar").val("");
    $("#operacion").val("nuevo");
    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
}

function tab_datosclick() {


    if ($('#operacion').val() == '') {
        if ($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Roles.aspx/ConsultarIdRol',
                data: '{id: "' + $('#hdd_ultimafila').val() + '" }',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,

                success: function (response) {
                    if (response.d === "-1") {
                        MensajeFinSession();
                    } else if (!response.d || response.d.length === 0) {
                        // Array vacío — el SP no devolvió datos
                        console.warn('[Roles] ConsultarIdRol sin resultados para id:', $('#hdd_ultimafila').val());
                        Desabilitar();
                    } else {
                        CompletarCampos(response.d);
                    }
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            });


            $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
            $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");

        }
        Desabilitar();
    }
    if ($('#table_id')[0].rows[1].cells[1].innerText == $('#hdd_ultimafila').val()) {
        $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
        $("#table_id tr:nth-child(" + 1 + ")").css('background', 'silver');

        $('#hdd_fila').val(1);

        $(".limpiar_item_checked").removeAttr("checked");
        $("#" + $('#tb_codigo').val()).prop('checked', true);
    }
}

function checked_click(row) {
    $(".limpiar_item_checked").removeAttr("checked");
    $(row).prop('checked', true);
    var currentRow = $(row).closest("tr");
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    $("#table_id tr:nth-child(" + currentRow[0].rowIndex + ")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);
}

function table_two_click(tbody) {

    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    var index = tbody.ondblclick.arguments[0].target.parentElement.rowIndex;
    $("#table_id tr:nth-child(" + index + ")").css('background', 'silver');
    $('#hdd_fila').val(index);

    var fila = tbody.ondblclick.arguments[0].target.parentElement.cells;
    $(".limpiar_item_checked").removeAttr("checked");
    $("#" + fila[1].innerText).prop('checked', true);


    $('#hdd_ultimafila').val(fila[1].innerText);

    if ($('#hdd_numerofilas').val() > 0) {
        $.ajax({
            type: "POST",
            url: 'Roles.aspx/ConsultarIdRol',
            data: '{id: "' + fila[1].innerText + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if (response.d === "-1") {
                    MensajeFinSession();
                } else if (!response.d || response.d.length === 0) {
                    console.warn('[Roles] table_two_click: sin datos');
                } else {
                    CompletarCampos(response.d);
                }
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    }

    $('.nav-tabs li:eq(0) a').tab('show');
    Desabilitar();

    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");

}

function Eliminar() {

    if (navigator.onLine) {

        Swal.fire({
            title: "¿Estas seguro?",
            text: "No podrás revertir el cambio",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, proceder'
        }).then((result) => {
            if (result.value) {

                var obj;

                $.ajax({
                    type: "POST",
                    url: 'Roles.aspx/Eliminar',
                    data: '{Id_rol: "' + $('#tb_codigo').val() + '" }',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,

                    success: function (response) {

                        if (response.d == "-1") MensajeFinSession();
                        else {
                            obj = response.d;
                            if (obj[0].ccod_rol == 'OK') {
                                $('.nav-tabs li:eq(1) a').tab('show');

                                Desabilitar();
                                Deshacer();
                                CargarTabla();
                                if ($('#hdd_numerofilas').val() > 0) {
                                    $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                                }
                                Mensaje('Correcto', '', 'success');
                            } else if (obj[0].ccod_rol == '547') {
                                MensajeError('', "El id rol (" + $('#tb_codigo').val() + ") no se puede eliminar porque se encuentra asignado.", 'warning', 'Cancelar');

                            } else {
                                MensajeError('', "Error: \n\n" + obj[0].ccod_cia, 'warning', 'Cancelar');
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                });

            }
        });
    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}




function CompletarCampos(obj) {
    if (!obj || obj.length === 0 || !obj[0]) {
        console.warn('[Roles] CompletarCampos: datos vacíos', obj);
        return;
    }
    $("#tb_codigo").val(obj[0].id_rol);
    $("#tb_descripcion").val(obj[0].cdescripcion);

    // Normalizar estado antes de buscar en el select
    var estadoRaw = (obj[0].cstatus || '').toString().toUpperCase().trim();
    var estadoNorm = (estadoRaw === 'A' || estadoRaw === '1') ? '1' : '0';

    var ddl = document.getElementById("ddl_estado");
    ddl.selectedIndex = [...ddl.options].findIndex(o => o.value === estadoNorm);

    $('#lb_codigo').text("Rol :" + obj[0].id_rol + " - " + obj[0].cdescripcion);
    ConsultaIdAccesos(obj[0].id_rol);
}
function ConsultaIdAccesos(id_rol) {

    var ModulosRoles = "";
    var CabeceraRoles = "";
    var DetalleRoles = "";
    $('#ColumnaRoles').empty();

    $.ajax({
        type: "POST",
        url: 'Roles.aspx/CargarTablaMenuIdAccesos',
        data: '{Id_rol: "' + id_rol + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            obj = response.d;
            for (var i = 0; i < obj.length; i++) {

                if (obj[i].corden < 100) {
                    ModulosRoles = '<h4 class="acc-modulo">' + obj[i].cdsc_menu + '</h4><div id="' + obj[i].id_menu + '" class="acc-modulo-body"></div>'
                    $("#ColumnaRoles").append(ModulosRoles);
                    for (var j = 0; j < obj.length; j++) {
                        if (obj[j].corden < 1000 && obj[i].id_menu == obj[j].nid_menupadre) {
                            CabeceraRoles = '<div class="input-group acc-cabecera"><input ' + obj[j].cstatus + ' name="' + obj[j].nid_menupadre + '" value="' + obj[j].corden + '" data-idmenu="' + obj[j].id_menu + '" class="limpiar_checked chk_acceso disabled" type="checkbox" id="' + obj[j].corden + '" disabled runat="server" /><label class="acc-cabecera-lb">' + obj[j].cdsc_menu + '</label></div><div id="' + obj[j].id_menu + '" class="acc-detalle-body"></div>'
                            $("#" + obj[j].nid_menupadre).append(CabeceraRoles);
                            for (var k = 0; k < obj.length; k++) {
                                if (obj[k].corden > 1000 && obj[j].id_menu == obj[k].nid_menupadre) {
                                    DetalleRoles = '<div class="row acc-detalle"><div class="col-sm-12"><div class="input-group"><input ' + obj[k].cstatus + ' name="' + obj[k].nid_menupadre + '" value="' + obj[k].corden + '" data-idmenu="' + obj[k].id_menu + '" class="limpiar_checked chk_acceso disabled" type="checkbox" id="' + obj[k].corden + '" disabled runat="server" /><label class="acc-detalle-lb moderno_lb">' + obj[k].cdsc_menu + '</label></div></div></div>'

                                    $("#" + obj[k].nid_menupadre).append(DetalleRoles);
                                }
                            }
                        }
                    }
                }

            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    _bindArbolAccesos();

}

function CargarTabla() {

    $('#table_id').DataTable().destroy();
    $('#tablaRoles').DataTable().destroy();
    var obj = llenarobjeto('Roles.aspx/ConsultarRoles');

    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
        data: obj,
        "ordering": false,
        columns: [
            {
                data: 'item',
                className: "dt-body-center"
            },
            { data: 'id_rol' },
            { data: 'cdescripcion' },
            {
                data: 'cstatus',
                render: function (data) {
                    var v = (data || '').toString().toUpperCase().trim();
                    var esActivo = (v === '1' || v === 'A');
                    return esActivo
                        ? 'Activo'
                        : 'Inactivo';
                }
            }
        ]
    });

    $('#tablaRoles').DataTable({
        "autoWidth": false,
        // "lengthMenu": [100],
        "paging": false,
        "ordering": false,
        "info": false,
        "searching": false,
        "language": {
            "lengthMenu": "Mostrar _MENU_ entradas",
            "zeroRecords": "No se encontraron resultados.",
            "info": "Total de registros : <b>_MAX_</b>",
            "infoEmpty": "",
            "infoFiltered": "",
            "search": "",
            "searchPlaceholder": " ",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        data: obj,
        columns: [
            { data: 'id_rol' },
            { data: 'cdescripcion' },
            { data: 'cstatus' }],
        scrollX: "2000px",
        scrollCollapse: true,
    });
    $('#table_id').attr("style", "width:100%");
}




function CargarTablaMenu() {

    var ModulosRoles = "";
    var CabeceraRoles = "";
    var DetalleRoles = "";
    $('#ColumnaRoles').empty();
    var obj = llenarobjeto('Roles.aspx/CargarTablaMenu');
    if (obj.length > 0) {
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].corden < 100 && obj[i].cstatus == "1") {
                ModulosRoles = '<h4 class="acc-modulo">' + obj[i].cdsc_menu + '</h4><div id="' + obj[i].id_menu + '" class="acc-modulo-body"></div>'
                $("#ColumnaRoles").append(ModulosRoles);
                for (var j = 0; j < obj.length; j++) {
                    if (obj[j].corden < 1000 && obj[i].id_menu == obj[j].nid_menupadre && obj[j].cstatus == "1") {
                        CabeceraRoles = '<div class="input-group acc-cabecera"><input name="' + obj[j].nid_menupadre + '" value="' + obj[j].corden + '" data-idmenu="' + obj[j].id_menu + '" class="limpiar_checked chk_acceso disabled" type="checkbox" id="' + obj[j].corden + '" disabled runat="server" /><label class="acc-cabecera-lb">' + obj[j].cdsc_menu + '</label></div><div id="' + obj[j].id_menu + '" class="acc-detalle-body"></div>'
                        $("#" + obj[j].nid_menupadre).append(CabeceraRoles);
                        for (var k = 0; k < obj.length; k++) {
                            if (obj[k].corden > 1000 && obj[j].id_menu == obj[k].nid_menupadre && obj[k].cstatus == "1") {
                                DetalleRoles = '<div class="row acc-detalle"><div class="col-sm-12"><div class="input-group"><input name="' + obj[k].nid_menupadre + '" value="' + obj[k].corden + '" data-idmenu="' + obj[k].id_menu + '" class="limpiar_checked chk_acceso disabled" type="checkbox" id="' + obj[k].corden + '" disabled runat="server" /><label class="acc-detalle-lb moderno_lb">' + obj[k].cdsc_menu + '</label></div></div></div>'

                                $("#" + obj[k].nid_menupadre).append(DetalleRoles);
                            }
                        }
                    }
                }
            }
        }
    }

    _bindArbolAccesos();

}



function CargarAccesos() {
    var obj = llenarobjeto('Roles.aspx/CargarAccesos');
}

$(document).ready(function () {
    CargarMenu();
    CargarTablaMenu();
    ConsultaColumnas();

    //Prueba
    CargarAccesos();

    $("#ModalDatosPersonales").draggable();

    //    Funcion para generar exel
    $("#thTablaRoles").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });


    inicar_menu_nivel2('Roles', '1_li_Administracion', '2_li_Roles', '2');

    CargarTabla();
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);

    $('#lb_codigo').text("Rol :" + $('#table_id')[0].rows[1].cells[1].innerText + " - " + $('#table_id')[0].rows[1].cells[2].innerText);
});