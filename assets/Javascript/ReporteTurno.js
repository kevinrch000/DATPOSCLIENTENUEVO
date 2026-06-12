

var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

    if ($("#NombreColumna").val() == "txtTienda") {
        DscTabla = "ad_tienda";
        DscColumna = "ccod_tienda";
        Nombre = "Código de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtfchDesde") {
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha desde";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "txtfchHasta") {
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha hasta";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "txtUsuario") {
        DscTabla = "ad_usuario";
        DscColumna = "ccod_usuario";
        Nombre = "Código de usuario";
        Estado = "Opcional";
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

function CargarUsuario() {

    if ($('#txtTienda').find("option:selected").text() == "") {
        var listBox = document.getElementById("txtUsuario");
        listBox.options.length = 0;
    } else {
        var listBox = document.getElementById("txtUsuario");
        listBox.options.length = 0;

        $.ajax({
            type: "POST",
            url: 'ReporteTurno.aspx/CargarTurnoUsuario',
            data: '{id_usuario: "' + $('#txtTienda').find("option:selected").val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if (response.d) {
                    var $dropdown = $("#txtUsuario");
                    $dropdown.append($("<option />").val("").text(""));
                    $.each(response.d, function (item) {
                        $dropdown.append($("<option />").val(this.ccod_usuario).text(this.cdsc_usuario));
                    });
                }
                else MensajeFinSession();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    }
    
}

function Ejecutar() {

    if(navigator.onLine) {

    if ($('#txtTienda').val() == null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }
     
    var obj = [
        {
            "ccod_tienda": $('#txtTienda').val(),
            "ccod_usuario": $('#txtUsuario').val(),
            "dfecha_ini": $('#txtfchDesde').val(),
            "dfecha_fin": $('#txtfchHasta').val(),
            "cdsc_tienda": $("#txtTienda option:selected").text(),
            "ilogo": LogoEmpresa
        }
    ]

    $.ajax({
        type: "POST",
        url: 'ReporteTurno.aspx/ReporteTurnoPrincipal',
        data: JSON.stringify({ ReporteTurno: obj }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (typeof window.DATPOS_spaNavigate === 'function') {
                window.DATPOS_spaNavigate((window.DATPOS_BASE_PATH||'')+'/pages/Reportes/InformeTurno.php');
            } else {
                window.location.href = (window.DATPOS_BASE_PATH||'')+'/pages/Reportes/InformeTurno.php';
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


function Limpiar() {
    $('#txtUsuario').val('');
    $('#txtTienda').val('');

    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
    document.getElementById("txtTienda").setAttribute("value", "");  
    document.getElementById("txtUsuario").setAttribute("value", "");
}



$(document).ready(function () {

    CargarMenu();

    ConsultaColumnas();

    CargarMesActual();
    // FIX 74 / BUG 3.29: ver comentario homologo en ReporteVenta.js
    CargarTiendaReporteTurno();
    CargarUsuario();

    $("#ModalDatosPersonales").draggable();

    inicar_menu_nivel3('Reporte de Turno', '1_li_Ventas', '2_li_ReporteVenta', '3_li_ReporteTurno', '0');

    $('#btn_p_nuevo').hide();
    $('#btn_p_editar').hide();
    $('#btn_p_grabar').hide();
    $('#btn_p_eliminar').hide();
    $('#btn_p_back').hide();
    $('#btn_p_imprimir').hide();
    document.getElementById("divColsulta").style.visibility = "visible";
    $('#btn_p_ejecutar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_limpiar').removeClass("botones_des").addClass("botones_hab");


});

// FIX 74 / BUG 3.29: version local de CargarTienda independiente del
// overriding global (Bashboard.js / AperturaCaja.js / CierreCaja.js).
function CargarTiendaReporteTurno() {
    var listBox = document.getElementById("txtTienda");
    if (!listBox) return;
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarTienda',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var $dropdown = $("#txtTienda");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />")
                        .val(this.ccod_tiend)
                        .text(this.cnombr + " (" + this.ccod_tiend + ")"));
                });
            }
        },
        error: function (xhr, status, error) { console.error(error); }
    });
    var obj = (typeof llenarobjeto === 'function')
        ? llenarobjeto('../Consultas/ConfigGeneral.aspx/TiendaAsignada')
        : '';
    if (obj && obj.trim && obj.trim() !== "") {
        document.getElementById("txtTienda").setAttribute("value", "Tienda*");
        (document.getElementById("txtTienda")).selectedIndex =
            [...(document.getElementById("txtTienda")).options]
                .findIndex(option => option.value === (obj).toString());
    }
}

$.datepicker.regional['es'] = {
    closeText: 'Cerrar',
    prevText: '< Ant',
    nextText: 'Sig >',
    currentText: 'Hoy',
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
    dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
    weekHeader: 'Sm',
    dateFormat: 'dd/mm/yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['es']);
$(function () {
    $("#txtfchDesde").datepicker();
    $("#txtfchHasta").datepicker();
});
