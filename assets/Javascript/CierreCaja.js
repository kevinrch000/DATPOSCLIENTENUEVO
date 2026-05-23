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
    $("#lbcDscTienda").text(obj[0].cdsc_tienda);
    $("#SlcCodUsuario").val(obj[0].ccod_usuario);
    $("#lbcDscUsuario").text(obj[0].cdsc_usuario);
    $("#SlcCodCaja").text(obj[0].ccod_caja || '');
    $("#lbcDscCaja").text(obj[0].cdsc_caja || '');
    $("#tb_MontIni").val(obj[0].nmonto_ini);
    $("#tb_MontFin").val(obj[0].nmonto_fin);
    $("#tb_MontEntre").val(obj[0].ntot_entreg);
    $("#tb_MontDifer").val(obj[0].ndiferencia);
    $("#lbFechaTurno").val(obj[0].dfecha_ini);

    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    if (obj[0].cstatus == 'A') {
        $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
    } else {
        $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    }
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
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    var index = tbody.ondblclick.arguments[0].target.parentElement.rowIndex;
    $("#table_id tr:nth-child(" + index + ")").css('background', 'silver');
    $('#hdd_fila').val(index);

    var fila = tbody.ondblclick.arguments[0].target.parentElement.cells;
    $(".limpiar_checked").removeAttr("checked");
    $("#" + fila[1].innerText).prop('checked', true);

    $('#hdd_ultimafila').val(fila[1].innerText);

    if ($('#hdd_numerofilas').val() > 0) {
        $.ajax({
            type: "POST",
            url: 'cierrecaja_api.php?method=ConsultarIdCierreCaja',
            data: JSON.stringify({ id_turno: fila[1].innerText }),
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
            { data: 'cstatus' }
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
            { data: 'cstatus' }
        ],
        scrollX: "2000px",
        scrollCollapse: true
    });

    $('#table_id').attr("style", "width: -webkit-fill-available;");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
}

function CalcularDiferencia() {
    var Diferencia = (parseFloat($('#tb_MontEntre').val()) - (parseFloat($('#tb_MontIni').val()) + parseFloat($('#tb_MontFin').val())));
    $('#tb_MontDifer').val(parseFloat(Diferencia).toFixed(2));
}

function SelectDscTienda() {
    var x = document.getElementById('SlcCodTienda').value;
    document.getElementById("lbcDscTienda").innerHTML = x;
}

function SelectDscUsuario() {
    var x = document.getElementById('SlcCodUsuario').value;
    document.getElementById("lbcDscUsuario").innerHTML = x;
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
                    document.getElementById("SlcCodCaja").innerHTML = obj[0].ccod_caja || '';
                    document.getElementById("lbcDscCaja").innerHTML = obj[0].cdsc_caja || '';
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
    if ($('#hdd_numerofilas').val() > 0 && $('#table_id')[0] && $('#table_id')[0].rows[1]) {
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }
});