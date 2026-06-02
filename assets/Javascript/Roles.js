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
    if ($('#idCkMarcarTodo').is(':checked') == true) {
        $('.limpiar_checked').prop('checked', true);
        var objIdMenuPadre = llenarobjeto('Roles.aspx/ObtenerIdPadre');
        for (var i = 0; i < objIdMenuPadre.length; i++) {
            $('.' + objIdMenuPadre[i].nid_menupadre).prop('checked', true);
        }
        $("#idCkDesmarcarTodo").prop("checked", false);
    } else {

    }
}

function CkDesmarcarTodo() {
    if ($('#idCkDesmarcarTodo').is(':checked') == true) {
        $(".limpiar_checked").removeAttr("checked");
        var objIdMenuPadre = llenarobjeto('Roles.aspx/ObtenerIdPadre');
        for (var i = 0; i < objIdMenuPadre.length; i++) {
            $("." + objIdMenuPadre[i].nid_menupadre).removeAttr("checked");
        }

    } else {

    }
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

        var obj_menu = [
            {
                "corden": "101",
                "cstatus": $('#101').is(':checked'),
                "nid_menupadre": $('#101').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "102",
                "cstatus": $('#102').is(':checked'),
                "nid_menupadre": $('#102').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "103",
                "cstatus": $('#103').is(':checked'),
                "nid_menupadre": $('#103').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "104",
                "cstatus": $('#104').is(':checked'),
                "nid_menupadre": $('#104').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "105",
                "cstatus": $('#105').is(':checked'),
                "nid_menupadre": $('#105').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "106",
                "cstatus": $('#106').is(':checked'),
                "nid_menupadre": $('#106').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "107",
                "cstatus": $('#107').is(':checked'),
                "nid_menupadre": $('#107').attr("name"),
                "nivel": "No"
            },
            {
                "corden": "108",
                "cstatus": $('#108').is(':checked'),
                "nid_menupadre": $('#108').attr("name"),
                "nivel": "No"
            },

            {
                "corden": "109",
                "cstatus": $('#109').is(':checked'),
                "nid_menupadre": $('#109').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "110",
                "cstatus": $('#110').is(':checked'),
                "nid_menupadre": $('#110').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "111",
                "cstatus": $('#111').is(':checked'),
                "nid_menupadre": $('#111').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "112",
                "cstatus": $('#112').is(':checked'),
                "nid_menupadre": $('#112').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "113",
                "cstatus": $('#113').is(':checked'),
                "nid_menupadre": $('#113').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1001",
                "cstatus": $('#1001').is(':checked'),
                "nid_menupadre": $('#1001').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1002",
                "cstatus": $('#1002').is(':checked'),
                "nid_menupadre": $('#1002').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1003",
                "cstatus": $('#1003').is(':checked'),
                "nid_menupadre": $('#1003').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1004",
                "cstatus": $('#1004').is(':checked'),
                "nid_menupadre": $('#1004').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1005",
                "cstatus": $('#1005').is(':checked'),
                "nid_menupadre": $('#1005').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1006",
                "cstatus": $('#1006').is(':checked'),
                "nid_menupadre": $('#1006').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1007",
                "cstatus": $('#1007').is(':checked'),
                "nid_menupadre": $('#1007').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1008",
                "cstatus": $('#1008').is(':checked'),
                "nid_menupadre": $('#1008').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1009",
                "cstatus": $('#1009').is(':checked'),
                "nid_menupadre": $('#1009').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1010",
                "cstatus": $('#1010').is(':checked'),
                "nid_menupadre": $('#1010').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1011",
                "cstatus": $('#1011').is(':checked'),
                "nid_menupadre": $('#1011').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1012",
                "cstatus": $('#1012').is(':checked'),
                "nid_menupadre": $('#1012').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1013",
                "cstatus": $('#1013').is(':checked'),
                "nid_menupadre": $('#1013').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1014",
                "cstatus": $('#1014').is(':checked'),
                "nid_menupadre": $('#1014').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1015",
                "cstatus": $('#1015').is(':checked'),
                "nid_menupadre": $('#1015').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1016",
                "cstatus": $('#1016').is(':checked'),
                "nid_menupadre": $('#1016').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1017",
                "cstatus": $('#1017').is(':checked'),
                "nid_menupadre": $('#1017').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1018",
                "cstatus": $('#1018').is(':checked'),
                "nid_menupadre": $('#1018').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1019",
                "cstatus": $('#1019').is(':checked'),
                "nid_menupadre": $('#1019').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1020",
                "cstatus": $('#1020').is(':checked'),
                "nid_menupadre": $('#1020').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1021",
                "cstatus": $('#1021').is(':checked'),
                "nid_menupadre": $('#1021').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1022",
                "cstatus": $('#1022').is(':checked'),
                "nid_menupadre": $('#1022').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1023",
                "cstatus": $('#1023').is(':checked'),
                "nid_menupadre": $('#1023').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1024",
                "cstatus": $('#1024').is(':checked'),
                "nid_menupadre": $('#1024').attr("name"),
                "nivel": "Si"
            },

            {
                "corden": "1026",
                "cstatus": $('#1026').is(':checked'),
                "nid_menupadre": $('#1026').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1027",
                "cstatus": $('#1027').is(':checked'),
                "nid_menupadre": $('#1027').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1028",
                "cstatus": $('#1028').is(':checked'),
                "nid_menupadre": $('#1028').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1029",
                "cstatus": $('#1029').is(':checked'),
                "nid_menupadre": $('#1029').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1030",
                "cstatus": $('#1030').is(':checked'),
                "nid_menupadre": $('#1030').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1031",
                "cstatus": $('#1031').is(':checked'),
                "nid_menupadre": $('#1031').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1032",
                "cstatus": $('#1032').is(':checked'),
                "nid_menupadre": $('#1032').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1033",
                "cstatus": $('#1033').is(':checked'),
                "nid_menupadre": $('#1033').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1034",
                "cstatus": $('#1034').is(':checked'),
                "nid_menupadre": $('#1034').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1035",
                "cstatus": $('#1035').is(':checked'),
                "nid_menupadre": $('#1035').attr("name"),
                "nivel": "Si"
            },
            {
                "corden": "1036",
                "cstatus": $('#1036').is(':checked'),
                "nid_menupadre": $('#1036').attr("name"),
                "nivel": "Si"
            }
        ]


        var ModuloAlmacen = 'false';
        var ModuloVentas = 'false';
        var ModuloAdministracion = 'false';

        var OpcionAlmacenTablas = 'false';
        var OpcionAlmacenOperaciones = 'false';
        var OpcionAlmacenConsulta = 'false';
        var OpcionAlmacenReporte = 'false';

        var OpcionVentaTablas = 'false';
        var OpcionVentaOperaciones = 'false';
        var OpcionVentaConsulta = 'false';
        var OpcionVentaReporte = 'false';


        for (var i = 0; i < obj_menu.length; i++) {
            if (obj_menu[i].cstatus == true) {
                if (obj_menu[i].nid_menupadre == '1') {
                    ModuloAlmacen = 'True';
                } else if (obj_menu[i].nid_menupadre == '17') {
                    ModuloVentas = 'True';
                } else if (obj_menu[i].nid_menupadre == '33') {
                    ModuloAdministracion = 'True';

                } else if (obj_menu[i].nid_menupadre == '2') {
                    OpcionAlmacenTablas = 'True';
                    ModuloAlmacen = 'True';
                } else if (obj_menu[i].nid_menupadre == '8') {
                    OpcionAlmacenOperaciones = 'True';
                    ModuloAlmacen = 'True';
                } else if (obj_menu[i].nid_menupadre == '12') {
                    OpcionAlmacenConsulta = 'True';
                    ModuloAlmacen = 'True';
                } else if (obj_menu[i].nid_menupadre == '16') {
                    OpcionAlmacenReporte = 'True';
                    ModuloAlmacen = 'True';
                } else if (obj_menu[i].nid_menupadre == '18') {
                    OpcionVentaTablas = 'True';
                    ModuloVentas = 'True';
                } else if (obj_menu[i].nid_menupadre == '21') {
                    OpcionVentaOperaciones = 'True';
                    ModuloVentas = 'True';
                } else if (obj_menu[i].nid_menupadre == '28') {
                    OpcionVentaConsulta = 'True';
                    ModuloVentas = 'True';
                } else if (obj_menu[i].nid_menupadre == '32') {
                    OpcionVentaReporte = 'True';
                    ModuloVentas = 'True';
                }
            }
        }
        /// <reference path="../Administracion/Roles.aspx" />

        var obj_modulo = [
            {
                "corden": "1",
                "cstatus": ModuloAlmacen
            },
            {
                "corden": "2",
                "cstatus": ModuloVentas
            },
            {
                "corden": "3",
                "cstatus": ModuloAdministracion
            },
            {
                "corden": "101",
                "cstatus": OpcionAlmacenTablas
            },
            {
                "corden": "102",
                "cstatus": OpcionAlmacenOperaciones
            },
            {
                "corden": "103",
                "cstatus": OpcionAlmacenConsulta
            },
            {
                "corden": "104",
                "cstatus": OpcionAlmacenReporte
            },
            {
                "corden": "105",
                "cstatus": OpcionVentaTablas
            },
            {
                "corden": "106",
                "cstatus": OpcionVentaOperaciones
            },
            {
                "corden": "107",
                "cstatus": OpcionVentaConsulta
            },
            {
                "corden": "108",
                "cstatus": OpcionVentaReporte
            }
        ]

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
    $(".limpiar_checked").removeAttr("checked");
    var objIdMenuPadre = llenarobjeto('Roles.aspx/ObtenerIdPadre');
    for (var i = 0; i < objIdMenuPadre.length; i++) {
        $("." + objIdMenuPadre[i].nid_menupadre).removeAttr("checked");
    }



    $(".limpiar_checked").removeAttr("checked");
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
                    ModulosRoles = '<h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width:60%;">' + obj[i].cdsc_menu + '</h4> <div id="' + obj[i].id_menu + '" ></div>'
                    $("#ColumnaRoles").append(ModulosRoles);
                    for (var j = 0; j < obj.length; j++) {
                        if (obj[j].corden < 1000 && obj[i].id_menu == obj[j].nid_menupadre) {
                            CabeceraRoles = '<div class="input-group"><input ' + obj[j].cstatus + ' name="' + obj[j].nid_menupadre + '" value="' + obj[j].corden + '" class="limpiar_checked disabled" style="margin-top:10px;cursor: default;"   type="checkbox" id="' + obj[j].corden + '" disabled runat="server" /><label style="padding-left:10px;" id="Label5">' + obj[j].cdsc_menu + '</label></div><div id="' + obj[j].id_menu + '" ></div>'
                            $("#" + obj[j].nid_menupadre).append(CabeceraRoles);
                            for (var k = 0; k < obj.length; k++) {
                                if (obj[k].corden > 1000 && obj[j].id_menu == obj[k].nid_menupadre) {
                                    DetalleRoles = '<div class="row"><div class="col-sm-10"><div class="input-group"><input ' + obj[k].cstatus + ' name="' + obj[k].nid_menupadre + '" disabled  value="' + obj[k].corden + '"  class="limpiarChecked' + obj[j].id_menu + ' disabled" style="margin-top:10px;cursor: default;" type="checkbox"  id="' + obj[k].corden + '" runat="server" /><label style="padding-left:10px;" class="moderno_lb">' + obj[k].cdsc_menu + '</label></div></div></div>'

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

    $('input[type="checkbox"]').on('change', function (e) {
        if (this.checked) {

            if (e.currentTarget.id == '101') {
                $('.limpiarChecked2').prop('checked', true);
            } else if (e.currentTarget.id == '102') {
                $('.limpiarChecked8').prop('checked', true);
            } else if (e.currentTarget.id == '103') {
                $('.limpiarChecked12').prop('checked', true);
            } else if (e.currentTarget.id == '104') {
                $('.limpiarChecked16').prop('checked', true);
            } else if (e.currentTarget.id == '105') {
                $('.limpiarChecked18').prop('checked', true);
            } else if (e.currentTarget.id == '106') {
                $('.limpiarChecked21').prop('checked', true);
            } else if (e.currentTarget.id == '107') {
                $('.limpiarChecked28').prop('checked', true);
            } else if (e.currentTarget.id == '108') {
                $('.limpiarChecked32').prop('checked', true);
            }

        } else {
            if (e.currentTarget.id == '101') {
                $(".limpiarChecked2").removeAttr("checked");
            } else if (e.currentTarget.id == '102') {
                $('.limpiarChecked8').removeAttr("checked");
            } else if (e.currentTarget.id == '103') {
                $('.limpiarChecked12').removeAttr("checked");
            } else if (e.currentTarget.id == '104') {
                $('.limpiarChecked16').removeAttr("checked");
            } else if (e.currentTarget.id == '105') {
                $('.limpiarChecked18').removeAttr("checked");
            } else if (e.currentTarget.id == '106') {
                $('.limpiarChecked21').removeAttr("checked");
            } else if (e.currentTarget.id == '107') {
                $('.limpiarChecked28').removeAttr("checked");
            } else if (e.currentTarget.id == '108') {
                $('.limpiarChecked32').removeAttr("checked");
            }
        }
    });

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
                ModulosRoles = '<h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width:60%;">' + obj[i].cdsc_menu + '</h4> <div id="' + obj[i].id_menu + '" ></div>'
                $("#ColumnaRoles").append(ModulosRoles);
                for (var j = 0; j < obj.length; j++) {
                    if (obj[j].corden < 1000 && obj[i].id_menu == obj[j].nid_menupadre && obj[j].cstatus == "1") {
                        CabeceraRoles = '<div class="input-group"><input  name="' + obj[j].nid_menupadre + '" value="' + obj[j].corden + '" class="limpiar_checked disabled" style="margin-top:10px;cursor: default;"   type="checkbox" id="' + obj[j].corden + '" disabled runat="server" /><label style="padding-left:10px;" id="Label5">' + obj[j].cdsc_menu + '</label></div><div id="' + obj[j].id_menu + '" ></div>'
                        $("#" + obj[j].nid_menupadre).append(CabeceraRoles);
                        for (var k = 0; k < obj.length; k++) {
                            if (obj[k].corden > 1000 && obj[j].id_menu == obj[k].nid_menupadre && obj[k].cstatus == "1") {
                                DetalleRoles = '<div class="row"><div class="col-sm-10"><div class="input-group"><input name="' + obj[k].nid_menupadre + '" value="' + obj[k].corden + '" disabled class="limpiarChecked' + obj[j].id_menu + ' disabled" style="margin-top:10px;cursor: default;" type="checkbox"  id="' + obj[k].corden + '" runat="server" /><label style="padding-left:10px;" class="moderno_lb">' + obj[k].cdsc_menu + '</label></div></div></div>'

                                $("#" + obj[k].nid_menupadre).append(DetalleRoles);
                            }
                        }
                    }
                }
            }
        }
    }

    $('input[type="checkbox"]').on('change', function (e) {
        if (this.checked) {

            if (e.currentTarget.id == '101') {
                $('.limpiarChecked2').prop('checked', true);
            } else if (e.currentTarget.id == '102') {
                $('.limpiarChecked8').prop('checked', true);
            } else if (e.currentTarget.id == '103') {
                $('.limpiarChecked12').prop('checked', true);
            } else if (e.currentTarget.id == '104') {
                $('.limpiarChecked16').prop('checked', true);
            } else if (e.currentTarget.id == '105') {
                $('.limpiarChecked18').prop('checked', true);
            } else if (e.currentTarget.id == '106') {
                $('.limpiarChecked21').prop('checked', true);
            } else if (e.currentTarget.id == '107') {
                $('.limpiarChecked28').prop('checked', true);
            } else if (e.currentTarget.id == '108') {
                $('.limpiarChecked32').prop('checked', true);
            }

        } else {
            if (e.currentTarget.id == '101') {
                $(".limpiarChecked2").removeAttr("checked");
            } else if (e.currentTarget.id == '102') {
                $('.limpiarChecked8').removeAttr("checked");
            } else if (e.currentTarget.id == '103') {
                $('.limpiarChecked12').removeAttr("checked");
            } else if (e.currentTarget.id == '104') {
                $('.limpiarChecked16').removeAttr("checked");
            } else if (e.currentTarget.id == '105') {
                $('.limpiarChecked18').removeAttr("checked");
            } else if (e.currentTarget.id == '106') {
                $('.limpiarChecked21').removeAttr("checked");
            } else if (e.currentTarget.id == '107') {
                $('.limpiarChecked28').removeAttr("checked");
            } else if (e.currentTarget.id == '108') {
                $('.limpiarChecked32').removeAttr("checked");
            }
        }
    });


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