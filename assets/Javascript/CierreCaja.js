var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

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
    } else if ($("#NombreColumna").val() == "lbFechaTurno") {
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha Apertura";
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
    } else if ($("#NombreColumna").val() == "lbIdTurno") {
        DscTabla = "fa_ctrlturno";
        DscColumna = "id_turno";
        Nombre = "Turno";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "lbcDscTienda") {
        DscTabla = "ad_tienda";
        DscColumna = "cdsc_tienda";
        Nombre = "Descripcion de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "lbcDscUsuario") {
        DscTabla = "co_ctcoa";
        DscColumna = "cdsc_coa";
        Nombre = "Descripcion de usuario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "SlcCodCaja") {
        DscTabla = "fa_ctrlturno";
        DscColumna = "ccod_caja";
        Nombre = "Codigo de caja";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "lbcDscCaja") {
        DscTabla = "al_ctcaja";
        DscColumna = "cdsc_caja";
        Nombre = "Descripcion de caja";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_MontFin") {
        DscTabla = "fa_ctrlturno";
        DscColumna = "nmonto_fin";
        Nombre = "Monto facturado";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "tb_MontEntre") {
        DscTabla = "fa_ctrlturno";
        DscColumna = "ntot_entreg";
        Nombre = "Monto Entregado";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "tb_MontDifer") {
        DscTabla = "fa_ctrlturno";
        DscColumna = "ndiferencia";
        Nombre = "Diferencia";
        Estado = "Opcional";
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

function tab_datosclick() {
    if ($('#operacion').val() == '') {
        if ($('#hdd_ultimafila').val() != '') {
            $.ajax({
                type: "POST",
                url: 'cierrecaja_api.php?method=ConsultarIdCierreCaja',
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
    }

    if ($('#table_id')[0] && $('#table_id')[0].rows[1] && $('#table_id')[0].rows[1].cells[1].innerText == $('#hdd_ultimafila').val()) {
        $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
        $("#table_id tr:nth-child(1)").css('background', 'silver');
        $('#hdd_fila').val(1);
        $(".limpiar_checked").removeAttr("checked");
        $("#" + $('#hdd_ultimafila').val()).prop('checked', true);
    }
}

function Guardar() {
    if (navigator.onLine) {
        if ($('#tb_MontEntre').val() == "") {
            Mensaje('Advertencia', 'Ingresar monto entregado.', 'warning');
            return;
        }

        var obj = [{
            "id_turno": $('#lbIdTurno').val(),
            "ntot_entreg": $('#tb_MontEntre').val(),
            "nmonto_fin": $('#tb_MontFin').val(),
            "ndiferencia": $('#tb_MontDifer').val()
        }];

        $.ajax({
            type: "POST",
            url: 'cierrecaja_api.php?method=Guardar',
            data: JSON.stringify({ DatTurno: obj, operacion: $('#operacion').val() }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d == "-1") MensajeFinSession();
                else {
                    if (response.d) {
                        var result = response.d;
                        if (result[0] && result[0].id_turno == 'OK') {
                            Mensaje('Correcto', '', 'success');
                            CargarTabla();
                            if ($('#hdd_numerofilas').val() > 0) {
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
        $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}

function CompletarCampos(obj) {
    $("#lbIdTurno").val(obj[0].id_turno);
    $("#SlcCodTienda").val(obj[0].ccod_tienda);
    $("#lbcDscTienda").val(obj[0].cdsc_tienda);
    $("#SlcCodUsuario").val(obj[0].ccod_usuario);
    $("#lbcDscUsuario").val(obj[0].cdsc_usuario);
    $("#SlcCodCaja").val(obj[0].ccod_caja || '');
    $("#lbcDscCaja").val(obj[0].cdsc_caja || '');
    $("#tb_MontIni").val(obj[0].nmonto_ini);
    $("#tb_MontFin").val(obj[0].nmonto_fin);
    $("#tb_MontEntre").val(obj[0].ntot_entreg);
    $("#tb_MontDifer").val(obj[0].ndiferencia);
    $("#lbFechaTurno").val(obj[0].dfecha_ini);

    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    if (obj[0].cstatus == 'A') {
        $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
        $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");
    } else {
        $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
        $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    }
}

// FIX BUG 3.2.5: CierreCaja no define Nuevo/Desabilitar/Deshacer
// pero el layout llama a estos. Los definimos aquí con la lógica adecuada.
function Nuevo() {
    var targetUrl = '/pages/Ventas/AperturaCaja.php';
    if (typeof window.DATPOS_spaNavigate === 'function') {
        window.DATPOS_spaNavigate(targetUrl, true);
    } else {
        window.location.replace("AperturaCaja.php");
    }
}

function Eliminar() {
    if (navigator.onLine) {
        var idTurno = $('#lbIdTurno').val() || $('#hdd_ultimafila').val();
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
                    url: 'cierrecaja_api.php?method=Eliminar',
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

function Desabilitar() {
    $(".disabled").prop("disabled", true);
    $('#btn_p_grabar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_hab").addClass("botones_des");
}

function Deshacer() {
    $(".limpiar").val("");
    Desabilitar();
    $('#btn_p_nuevo').removeClass("botones_des").addClass("botones_hab");
}

// Editar = iniciar flujo de cierre para el turno seleccionado
function Editar() {
    if ($('#hdd_ultimafila').val() == '' || $('#hdd_ultimafila').val() == '0') {
        Mensaje('Advertencia', 'Seleccione primero un turno de la lista.', 'warning');
        return;
    }
    $(".disabled").prop("disabled", false);
    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#operacion').val('editar');
    $('.nav-tabs li:eq(0) a').tab('show');
}

function checked_click(row) {
    $(".limpiar_checked").removeAttr("checked");
    $(row).prop('checked', true);
    var currentRow = $(row).closest("tr");
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    $("#table_id tr:nth-child(" + currentRow[0].rowIndex + ")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);

    // Habilitar/deshabilitar botones Editar y Eliminar en base al estado de la fila seleccionada
    var statusText = currentRow.find("td:eq(9)").text().trim(); // "Abierto" o "Cerrado"
    if (statusText === 'Abierto') {
        $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
        $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");
    } else {
        $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
        $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    }
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
            url: 'cierrecaja_api.php?method=ConsultarIdCierreCaja',
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
}

function CargarTabla() {
    var obj = llenarobjeto('cierrecaja_api.php?method=ConsultarCierreCaja');
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
            { data: 'nmonto_fin' },
            { data: 'dfecha_ini' },
            { data: 'dfecha_fin' },
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
            { data: 'nmonto_fin' },
            { data: 'dfecha_ini' },
            { data: 'dfecha_fin' },
            { data: 'cstatus', render: function(d){ return d==='A'?'Abierto': d==='C'?'Cerrado': d; } }
        ],
        scrollX: "2000px",
        scrollCollapse: true
    });

    $('#table_id').attr("style", "width: -webkit-fill-available;");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
}

function CalcularDiferencia() {
    var entregado = parseFloat($('#tb_MontEntre').val()) || 0;
    var inicial = parseFloat($('#tb_MontIni').val()) || 0;
    var fin = parseFloat($('#tb_MontFin').val()) || 0;
    var Diferencia = entregado - (inicial + fin);
    $('#tb_MontDifer').val(Diferencia.toFixed(2));
}

function SelectDscTienda() {
    var x = $('#SlcCodTienda').val();
    $('#lbcDscTienda').val(x);
}

function SelectDscUsuario() {
    var x = $('#SlcCodUsuario').val();
    $('#lbcDscUsuario').val(x);
}

function CargarTienda() {
    var listBox = document.getElementById("SlcCodTienda");
    if (listBox) listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'cierrecaja_api.php?method=CargarTienda',
        data: JSON.stringify({ codigo: "cod" }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var $dropdown = $("#SlcCodTienda");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function (item) {
                    // FIX: val = código, text = nombre (estaba invertido)
                    $dropdown.append($("<option />").val(this.ccod_tiend).text(this.cnombr));
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
        url: 'cierrecaja_api.php?method=CargarIdUsuario',
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
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarCajaDeUsuario() {
    var SlcCodUsuario = document.getElementById("SlcCodUsuario");
    if (SlcCodUsuario && $('#SlcCodUsuario').val() != '' && $('#SlcCodUsuario').val() != null && SlcCodUsuario.options) {
        $.ajax({
            type: "POST",
            url: 'cierrecaja_api.php?method=CargarCajaDeUsuario',
            data: JSON.stringify({ ccod_usuario: SlcCodUsuario.options[SlcCodUsuario.selectedIndex].text }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d && response.d.length > 0) {
                    var obj = response.d;
                    $("#SlcCodCaja").val(obj[0].ccod_caja || '');
                    $("#lbcDscCaja").val(obj[0].cdsc_caja || '');
                }
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    } else {
        document.getElementById("SlcCodCaja").innerHTML = "";
        document.getElementById("lbcDscCaja").innerHTML = "";
    }
}

$(document).ready(function () {
    if (typeof CargarMenu === 'function') CargarMenu();
    if (typeof ConsultaColumnas === 'function') ConsultaColumnas();
    if (typeof inicar_menu_nivel3 === 'function') {
        inicar_menu_nivel3('Cierre de Turno', '1_li_Ventas', '2_li_Ventas_Operaciones', '3_Li_CierreCaja', '1');
    }
    CargarTabla();
    if ($('#hdd_numerofilas').val() > 0 && $('#table_id')[0] && $('#table_id')[0].rows[1] && $('#table_id')[0].rows[1].cells[1]) {
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText || $('#table_id')[0].rows[1].cells[1].textContent);
    }
});