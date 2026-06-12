

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
    } else if ($("#NombreColumna").val() == "txtCliente") {
        DscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Código de cliente";
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


function descargarBase64(base64Data, filename, contentType) {
    if (!base64Data) return;
    var byteCharacters = atob(base64Data);
    var byteArrays = [];
    for (var offset = 0; offset < byteCharacters.length; offset += 512) {
        var slice = byteCharacters.slice(offset, offset + 512);
        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }
        var byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
    }
    var blob = new Blob(byteArrays, {type: contentType});
    var blobUrl = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = blobUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    setTimeout(function() {
        document.body.removeChild(a);
        URL.revokeObjectURL(blobUrl);
    }, 100);
}

function DescargarArchivoPDF(row) { 

    $.ajax({
        type: "POST",
        url: 'ReporteTributario.aspx/DescargarArchivoPDF',
        data: '{codigo: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
                var obj = response.d; 
                var filename = obj[0].cdoc + '-' + obj[0].cdoc_serie + '-' + obj[0].cdoc_nro + '.pdf';
                descargarBase64(obj[0].ipdf_datpos, filename, 'application/pdf');
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < objTributario.length; i++) { 
//        if(row.id == objTributario[i].id_cbfact ){ 
//            var a = document.createElement("a");
//            a.href = 'data:application/octet-stream;base64,' + objTributario[i].ipdf_datpos;
//            a.download = objTributario[i].cdoc + '-' +objTributario[i].cdoc_serie + '-' +objTributario[i].cdoc_nro + '.pdf';
//            a.click();
//        }
//    }    
};

function DescargarArchivoXML(row) { 

        $.ajax({
        type: "POST",
        url: 'ReporteTributario.aspx/DescargarArchivoXML',
        data: '{codigo: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
                var obj = response.d; 
                var filename = obj[0].cdoc + '-' + obj[0].cdoc_serie + '-' + obj[0].cdoc_nro + '.xml';
                descargarBase64(obj[0].contentxml, filename, 'application/xml');
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < objTributario.length; i++) { 
//        if(row.id == objTributario[i].id_cbfact ){ 
//            var a = document.createElement("a");
//            a.href = 'data:application/octet-stream;base64,' + objTributario[i].contentxml;
//            a.download = objTributario[i].cdoc + '-' +objTributario[i].cdoc_serie + '-' +objTributario[i].cdoc_nro + '.xml';
//            a.click();
//        }
//    }    
};

function DescargarArchivoXMLCDR(row) { 
    
    $.ajax({
        type: "POST",
        url: 'ReporteTributario.aspx/DescargarArchivoXMLCDR',
        data: '{codigo: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
                var obj = response.d; 
                var filename = obj[0].cdoc + '-' + obj[0].cdoc_serie + '-' + obj[0].cdoc_nro + '.xmlcdr';
                descargarBase64(obj[0].contentzipcdr, filename, 'application/zip');
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < objTributario.length; i++) { 
//        if(row.id == objTributario[i].id_cbfact ){ 
//            var a = document.createElement("a");
//            a.href = 'data:application/octet-stream;base64,' + objTributario[i].contentxml;
//            a.download = objTributario[i].cdoc + '-' +objTributario[i].cdoc_serie + '-' +objTributario[i].cdoc_nro + '.xmlcdr';
//            a.click();
//        }
//    }    
};

var objTributario = [];

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
        } else if ($('#txtCodDocumento').val() == "") {
            Mensaje('Advertencia', 'Seleccionar tipo de documento.', 'warning');
            return;
        }


        var obj = [
        {
            "ccod_tienda": $('#txtTienda').val(),
            "dfch_desde": $('#txtfchDesde').val(),
            "dfch_hasta": $('#txtfchHasta').val(),
            "cdoc": $('#txtCodDocumento').val(),
            "cdoc_serie": $('#txtSerieDoc').val(),
            "cdoc_nro": $('#txtNroDoc').val(),
            "cstatus_tributario": $('#txtEstTributario').val(),
            "ccod_coa": $('#txtCliente').val()
        }
    ]

    $('#table_id').DataTable().destroy();
        $.ajax({
            type: "POST",
            url: 'ReporteTributario.aspx/ConsultaTributarioPrincipal',
            data: JSON.stringify({ ReporteTributario: obj }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                  objTributario = response.d;
               $('#table_id').DataTable({ 
                data: objTributario,
                "ordering": false,
                columns: [
                        { data: 'ccod_coa' },
                        { data: 'cdoc' },
                        { data: 'cdoc_serie' },
                        { data: 'cdoc_nro' },
                        { data: 'ntotal' },
                        { data: 'dfch_doc' },
                        { data: 'cstatus_tributario' },
                        { data: 'pdf' , className: "dt-body-center" },
                        { data: 'xml' , className: "dt-body-center" },
                        { data: 'zip' , className: "dt-body-center" }
                    ]
              });

            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}

function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();

    var obj = llenarobjeto('ReporteTributario.aspx/CargarCliente');
 
  $('#hdd_numerofilas').val(obj.length);
     $('#tableVisibleConsulClientes').DataTable({
        "pageLength": 5,
        data: obj,
        columns: [
                    { data: 'cbx',
                    render: function (data, type, row) {
                    if (1 == 1) { return '<input type="radio" name="radiob">'; }
                    return data;
                    },
                    className: "dt-body-center"
                    },
                    {data: 'ccod_coa' },
                    { data: 'cdsc_coa' }
                    ]
    });
   
      $('#table_secundariaConsultarCliente').DataTable({
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
                { data: 'ccod_coa' },
                { data: 'cdsc_coa' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });
}

function Limpiar() {
    
    $('#txtCliente').val(''); 
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
    $('#txtTienda').val(''); 
    $('#txtEstTributario').val(''); 
     
     $('#txtCodDocumento').val(''); 
     $('#txtSerieDoc').val(''); 
     $('#txtNroDoc').val(''); 
    document.getElementById("txtCodDocumento").setAttribute("value", "");
    document.getElementById("txtTienda").setAttribute("value", "");
    document.getElementById("txtEstTributario").setAttribute("value", "");
}



$(document).ready(function () {
 
    CargarMenu(); 
    ConsultaColumnas(); 
    CargarMesActual();
    CargarTienda(); 
    document.getElementById("txtEstTributario").setAttribute("value", "");
    document.getElementById("txtCodDocumento").setAttribute("value", "Código Doc.*");
 

    traducir_tabla();
    $('#table_id').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });

    $("#ModalDatosPersonales").draggable(); 

    inicar_menu_nivel3('Consulta Documentos Electronicos', '1_li_Ventas', '2_li_ConsultaVenta', '3_li_ReporteTributario', '0');

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

// Handlers para descarga de documentos con validación previa check=1
$(document).on('click', '.btn-descargar-pdf', function (e) {
    e.preventDefault();
    var serie = $(this).attr('data-serie');
    var corr = $(this).attr('data-correlativo');
    var url = '/api/documentos/descargar_pdf.php?serie=' + serie + '&correlativo=' + corr;
    
    $.ajax({
        url: url + '&check=1',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res && res.success === false) {
                Mensaje('Error', res.error || 'Archivo no encontrado', 'error');
            } else {
                window.location.href = url;
            }
        },
        error: function() {
            Mensaje('Error', 'Error al procesar la descarga', 'error');
        }
    });
});

$(document).on('click', '.btn-descargar-xml', function (e) {
    e.preventDefault();
    var serie = $(this).attr('data-serie');
    var corr = $(this).attr('data-correlativo');
    var url = '/api/documentos/descargar_xml.php?serie=' + serie + '&correlativo=' + corr;
    
    $.ajax({
        url: url + '&check=1',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res && res.success === false) {
                Mensaje('Error', res.error || 'Archivo no encontrado', 'error');
            } else {
                window.location.href = url;
            }
        },
        error: function() {
            Mensaje('Error', 'Error al procesar la descarga', 'error');
        }
    });
});

$(document).on('click', '.btn-descargar-cdr', function (e) {
    e.preventDefault();
    var serie = $(this).attr('data-serie');
    var corr = $(this).attr('data-correlativo');
    var url = '/api/documentos/descargar_cdr.php?serie=' + serie + '&correlativo=' + corr;
    
    $.ajax({
        url: url + '&check=1',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res && res.success === false) {
                Mensaje('Error', res.error || 'Archivo no encontrado', 'error');
            } else {
                window.location.href = url;
            }
        },
        error: function() {
            Mensaje('Error', 'Error al procesar la descarga', 'error');
        }
    });
});
