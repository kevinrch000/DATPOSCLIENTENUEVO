

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
    } else if ($("#NombreColumna").val() == "txtCodDocumento") {
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc";
        Nombre = "Código de operación";
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

function Ejecutar() {

    if (navigator.onLine) {

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
            "dfch_desde": $('#txtfchDesde').val(),
            "dfch_hasta": $('#txtfchHasta').val(),
            "cdoc": $('#txtCodDocumento').val(),
            "cdsc_tienda": $("#txtTienda option:selected").text(),
            "ilogo": LogoEmpresa
        }
    ]

     $.ajax({  
        type: "POST",
        url: 'ReporteVenta.aspx/ReporteVentaPrincipal',
        data: JSON.stringify({ ReportVenta: obj }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (typeof window.DATPOS_spaNavigate === 'function') {
                window.DATPOS_spaNavigate((window.DATPOS_BASE_PATH||'')+'/pages/Reportes/InformeVenta.php');
            } else {
                window.location.href = (window.DATPOS_BASE_PATH||'')+'/pages/Reportes/InformeVenta.php';
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}


function Limpiar() {
    $('#txtCodDocumento').val('');
    $('#txtTienda').val('');

    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');

    document.getElementById("txtCodDocumento").setAttribute("value", "");
    document.getElementById("txtTienda").setAttribute("value", "");
}



$(document).ready(function () {

//    VerificarAccesos(); 

    CargarMenu();

    ConsultaColumnas();

    CargarMesActual();
    // FIX 74 / BUG 3.28: si el usuario navego via SPA desde Home, la
    // funcion CargarTienda() global puede haber sido sobreescrita por
    // Bashboard.js (que llena #txtTiendaPorProducto, no #txtTienda).
    // Llamamos a una version local que SIEMPRE poblar #txtTienda.
    CargarTiendaReporteVenta();
//    CargarNumeradorFactura();

    $("#ModalDatosPersonales").draggable();
    document.getElementById("txtCodDocumento").setAttribute("value", "");

    inicar_menu_nivel3('Reporte de Ventas', '1_li_Ventas', '2_li_ReporteVenta', '3_li_ReporteVenta', '0');

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

// FIX 74 / BUG 3.28: version local de CargarTienda independiente del
// overriding global (Bashboard.js / AperturaCaja.js / CierreCaja.js).
function CargarTiendaReporteVenta() {
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
