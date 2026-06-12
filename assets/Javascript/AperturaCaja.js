var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + " " + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds();

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

    if ($("#NombreColumna").val() == "SlcCodTienda") {
        DscTabla = "ad_tienda";
        DscColumna = "ccod_tienda";
        Nombre = "Codigo de la tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "lbFchInicial") {
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha inicial";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "tb_MontIni") {
        DscTabla = "fa_ctrlturno";
        DscColumna = "nmonto_ini";
        Nombre = "Monto inicial";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "SlcCodUsuario") {
        DscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Codigo de usuario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
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

function Nuevo() {
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    $(".readonl").prop("readonly", false);
    $(".disabled").prop("disabled", false);
    $(".limpiar").val("");
    $(".limpiar").text("");
    $("#operacion").val("nuevo");
    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");

    $('#SlcCodTienda').val('');
    $('#SlcCodUsuario').val('');

    var date = new Date();
    var day = date.getDate();
    var month = date.getMonth() + 1;
    var year = date.getFullYear();
    if (month < 10) { month = '0' + month; }
    if (day < 10) { day = '0' + day; }
    $("#lbFchInicial").val(hoy);
}

function tab_datosclick() {
    if ($('#operacion').val() == '') {
        if ($('#hdd_ultimafila').val() != '') {
            $.ajax({
                type: "POST",
                url: 'aperturacaja_api.php?method=ConsultarIdCierreCaja',
                data: JSON.stringify({ id_turno: $('#hdd_ultimafila').val() }),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,
                success: function (response) {
                    if (response.d) CompletarCampos(response.d);
                    else MensajeFinSession();
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            });
        }
        Desabilitar();

        if ($('#hdd_ultimafila').val() != '') {
            $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
            $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");
        }
    }
    if ($('#table_id')[0] && $('#table_id')[0].rows[1] && $('#table_id')[0].rows[1].cells[1] && ($('#table_id')[0].rows[1].cells[1].innerText || $('#table_id')[0].rows[1].cells[1].textContent) == $('#hdd_ultimafila').val()) {
        $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
        $("#table_id tr:nth-child(1)").css('background', 'silver');
        $('#hdd_fila').val(1);
        $(".limpiar_checked").removeAttr("checked");
        $("#" + $('#hdd_id_turno').val()).prop('checked', true);
    }
}

function Guardar() {
    if (navigator.onLine) {
        var SlcCodTienda = document.getElementById("SlcCodTienda");
        var SlcCodUsuario = document.getElementById("SlcCodUsuario");

        if (SlcCodTienda.options[SlcCodTienda.selectedIndex].text == "") {
            Mensaje('Advertencia', 'Ingresar codigo de tienda.', 'warning');
            return;
        } else if (SlcCodUsuario.options[SlcCodUsuario.selectedIndex].text == "") {
            Mensaje('Advertencia', 'Ingresar codigo de usuario.', 'warning');
            return;
        } else if ($('#tb_MontIni').val() == "") {
            Mensaje('Advertencia', 'Ingresar monto inicial.', 'warning');
            return;
        }

        var obj = [{
            "ccod_tienda": SlcCodTienda.options[SlcCodTienda.selectedIndex].text,
            "ccod_usuario": SlcCodUsuario.options[SlcCodUsuario.selectedIndex].text,
            "ccod_caja": document.getElementById("SlcCodCaja").textContent,
            "nmonto_ini": $('#tb_MontIni').val(),
            "dfchdoc_ini": $('#lbFchInicial').val(),
            "id_turno": $('#hdd_id_turno').val()
        }];

        var operacion = $('#operacion').val();
        var apiMethod = (operacion === 'editar') ? 'Editar' : 'Guardar';

        $.ajax({
            type: "POST",
            url: 'aperturacaja_api.php?method=' + apiMethod,
            data: JSON.stringify({ DatTurno: obj, operacion: operacion }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d == "-1") MensajeFinSession();
                else {
                    if (response.d) {
                        var result = response.d;
                        if (result[0] && result[0].id_turno == 'TurnoAperturado') {
                            Swal.fire({
                                title: "El usuario " + document.getElementById("lbcDscUsuario").textContent + " ya cuenta con caja aperturada.",
                                icon: 'warning',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Cancelar'
                            });
                        } else if (result[0] && result[0].id_turno == 'TurnoCerrado') {
                            Mensaje('Advertencia', 'No se puede editar un turno cerrado.', 'warning');
                        } else if (result[0] && (result[0].id_turno == 'OK' || result[0].id_turno > 0)) {
                            Mensaje('Correcto', operacion === 'editar' ? 'Turno editado correctamente.' : 'Turno aperturado correctamente.', 'success');
                            CargarTabla();
                            if ($('#hdd_numerofilas').val() > 0 && $('#table_id')[0].rows.length > 1) {
                                $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                            }
                            $('.nav-tabs li:eq(1) a').tab('show');
                            Desabilitar();
                            Deshacer();
                        }
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

function Eliminar() {
    if (navigator.onLine) {
        var idTurno = $('#hdd_id_turno').val() || $('#hdd_ultimafila').val();
        if (!idTurno || idTurno == '0') {
            Mensaje('Advertencia', 'Seleccione un turno para eliminar.', 'warning');
            return;
        }

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se eliminará el turno seleccionado. No podrás revertir el cambio.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    type: "POST",
                    url: 'aperturacaja_api.php?method=Eliminar',
                    data: JSON.stringify({ id_turno: idTurno }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,
                    success: function (response) {
                        if (response.d == "-1") MensajeFinSession();
                        else if (response.d === true) {
                            Mensaje('Correcto', 'Turno eliminado correctamente.', 'success');
                            CargarTabla();
                            if ($('#hdd_numerofilas').val() > 0 && $('#table_id')[0].rows.length > 1) {
                                $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                            }
                            Desabilitar();
                            Deshacer();
                        } else if (response.d === 'TieneFacturas') {
                            Mensaje('Advertencia', 'No se puede eliminar: el turno tiene facturas asociadas.', 'warning');
                        } else if (response.d === 'TurnoCerrado') {
                            Mensaje('Advertencia', 'No se puede eliminar: el turno ya está cerrado.', 'warning');
                        } else {
                            Mensaje('Error', 'No se pudo eliminar el turno.', 'error');
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
    var SlcCodTienda = document.getElementById("SlcCodTienda");
    var optionIndex = [...SlcCodTienda.options].findIndex(option => option.text === (obj[0].ccod_tienda).toString());
    if (optionIndex >= 0) SlcCodTienda.selectedIndex = optionIndex;

    document.getElementById("lbcDscTienda").innerHTML = obj[0].cdsc_tienda || obj[0].ccod_tienda;
    cambiartienda();

    var SlcCodUsuario = document.getElementById("SlcCodUsuario");
    var optionIndex2 = [...SlcCodUsuario.options].findIndex(option => option.text === (obj[0].ccod_usuario).toString());
    if (optionIndex2 >= 0) SlcCodUsuario.selectedIndex = optionIndex2;
    
    document.getElementById("lbcDscUsuario").innerHTML = obj[0].cdsc_usuario || obj[0].ccod_usuario;
    document.getElementById("SlcCodCaja").innerHTML = obj[0].ccod_caja || '';
    document.getElementById("lbcDscCaja").innerHTML = obj[0].cdsc_caja || '';

    $("#lbFchInicial").val(obj[0].dfchdoc_ini);
    $("#tb_MontIni").val(obj[0].nmonto_ini);
    $("#hdd_id_turno").val(obj[0].id_turno);
}

function checked_click(row) {
    $(".limpiar_checked").removeAttr("checked");
    $(row).prop('checked', true);
    var currentRow = $(row).closest("tr");
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    $("#table_id tr:nth-child(" + currentRow[0].rowIndex + ")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);
}

function table_two_click(tbody) {
    var event = window.event || (arguments.callee.caller ? arguments.callee.caller.arguments[0] : null);
    var target = (event && event.target) ? event.target : null;
    if (!target) return;
    
    var tr = $(target).closest("tr");
    if (!tr.length || tr.parent().is("thead")) return;
    var index = tr[0].rowIndex;

    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    tr.css('background', 'silver');
    $('#hdd_fila').val(index);

    var cells = tr.find("td");
    var idTurno = $(cells[1]).text().trim();

    $(".limpiar_checked").removeAttr("checked");
    $("#" + idTurno).prop('checked', true);

    $('#hdd_ultimafila').val(idTurno);

    if ($('#hdd_numerofilas').val() > 0 && idTurno !== '') {
        $.ajax({
            type: "POST",
            url: 'aperturacaja_api.php?method=ConsultarIdCierreCaja',
            data: JSON.stringify({ id_turno: idTurno }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d) CompletarCampos(response.d);
                else MensajeFinSession();
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

function CargarTabla() {
    var obj = llenarobjeto('aperturacaja_api.php?method=ConsultarCierreCaja');
    $('#tableCierreCaja').DataTable().destroy();
    $('#table_id').DataTable().destroy();
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
        data: obj,
        "ordering": false,
        columns: [
            { data: 'item', className: "dt-body-center" },
            { data: 'id_turno' },
            { data: 'cdsc_tienda' },
            { data: 'cdsc_usuario' },
            { data: 'cdsc_caja' },
            { data: 'nmonto_ini' },
            { data: 'dfecha_ini' },
            { data: 'cstatus', render: function(d){ return d==='A'?'Abierto': d==='C'?'Cerrado': d; } }
        ]
    });
    $('#tableCierreCaja').DataTable({
        "autoWidth": false,
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
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        data: obj,
        columns: [
            { data: 'id_turno' },
            { data: 'cdsc_tienda' },
            { data: 'cdsc_usuario' },
            { data: 'cdsc_caja' },
            { data: 'nmonto_ini' },
            { data: 'dfecha_ini' },
            { data: 'cstatus', render: function(d){ return d==='A'?'Abierto': d==='C'?'Cerrado': d; } }
        ],
        scrollX: "2000px",
        scrollCollapse: true
    });

    $('#table_id').attr("style", "width: -webkit-fill-available;");
}

function cambiartienda() {
    CargarUsuario();
    $("#lbcDscUsuario").text("");
    $("#SlcCodCaja").text("");
    $("#lbcDscCaja").text("");

    var x = document.getElementById('SlcCodTienda').value;
    document.getElementById("lbcDscTienda").innerHTML = x;
}

function SelectDscUsuario() {
    var SlcCodUsuario = document.getElementById("SlcCodUsuario");
    if ($('#SlcCodUsuario').val() != '' && $('#SlcCodUsuario').val() != null) {
        $.ajax({
            type: "POST",
            url: 'aperturacaja_api.php?method=CargarCajaDeUsuario',
            data: JSON.stringify({ ccod_usuario: SlcCodUsuario.options[SlcCodUsuario.selectedIndex].text }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d && response.d.length > 0) {
                    var obj = response.d;
                    document.getElementById("SlcCodCaja").innerHTML = obj[0].ccod_caja || '';
                    document.getElementById("lbcDscCaja").innerHTML = obj[0].cdsc_caja || '';
                    $('#lbcDscUsuario').text($('#SlcCodUsuario').val());
                }
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    } else {
        document.getElementById("SlcCodCaja").innerHTML = "";
        document.getElementById("lbcDscCaja").innerHTML = "";
        $('#lbcDscUsuario').text("");
    }
}

function CargarTienda() {
    var listBox = document.getElementById("SlcCodTienda");
    if (listBox) listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'aperturacaja_api.php?method=CargarTienda',
        data: JSON.stringify({ codigo: "cod" }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var $dropdown = $("#SlcCodTienda");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.cnombr).text(this.ccod_tiend));
                });
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarUsuario() {
    var listBox = document.getElementById("SlcCodUsuario");
    if (listBox) listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'aperturacaja_api.php?method=CargarIdUsuario',
        data: JSON.stringify({ codigo: $('#SlcCodTienda').find("option:selected").text() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var $dropdown = $("#SlcCodUsuario");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.cdsc_usuario).text(this.ccod_usuario));
                });
            } else {
                MensajeFinSession();
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarCajaDeUsuario() {}

$(document).ready(function () {
    if (typeof CargarMenu === 'function') CargarMenu();
    if (typeof ConsultaColumnas === 'function') ConsultaColumnas();
    if (typeof inicar_menu_nivel3 === 'function') {
        inicar_menu_nivel3('Apertura de Turno', '1_li_Ventas', '2_li_Ventas_Operaciones', '3_Li_Apertura', '1');
    }

    CargarTabla();
    CargarTienda();

    if ($('#hdd_numerofilas').val() > 0 && $('#table_id')[0] && $('#table_id')[0].rows[1]) {
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }
});

$.datepicker.regional['es'] = {
    closeText: 'Cerrar',
    prevText: '< Ant',
    nextText: 'Sig >',
    currentText: 'Hoy',
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
    dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Juv', 'Vie', 'Sab'],
    dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
    weekHeader: 'Sm',
    dateFormat: 'dd/mm/yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['es']);
$(function () {
    $("#lbFchInicial").datepicker();
});