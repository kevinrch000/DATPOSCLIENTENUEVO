 

var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
 
 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "txtAlmacen"){
        DscTabla = "al_ctalmac";
        DscColumna = "ccod_alm";
        Nombre = "Código de almacén";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtfchDesde"){
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha desde";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "txtfchHasta"){
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha hasta";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "txtTipoOperacion"){
        DscTabla = "al_cbinve";
        DscColumna = "ctipo";
        Nombre = "Tipo de operación";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtSerie"){
        DscTabla = "al_cbinve";
        DscColumna = "cserie";
        Nombre = "Serie";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtNumero"){
        DscTabla = "al_cbinve";
        DscColumna = "nnumero";
        Nombre = "Numerador";
        Estado = "Opcional";
        TipoDato = "";
    }      

    for (var i = 0; i < objColumnas.length; i++) {
        if(DscColumna == objColumnas[i].DscColumna && DscTabla == objColumnas[i].DscTabla){
            $("#txt_nombreCampo").text(Nombre);
            $("#txt_TipoDato").text(objColumnas[i].TipoDato);
            $("#txt_estado").text(Estado);
            $("#txt_longitud").text(TipoDato +" "+objColumnas[i].longitud);
            $("#txt_cantidadEntero").text(objColumnas[i].CantEnteros);
            $("#txt_cantidadDecimales").text(objColumnas[i].CantDecimales);
        }
    }
      
 }
  
 var objfactura=[];

 
function CargarAlmacenes() {
    var listBox = document.getElementById("txtAlmacen");
    listBox.options.length = 0;

    var obj = llenarobjeto('ConsultaOperAlmacen.aspx/CargarAlmacenes');
    if (!obj) return;

    $('#txtAlmacen').append('<option value=""> </option>');
    for (var i = 0; i < obj.length; i++) {
        $('#txtAlmacen').append('<option value="' + obj[i].ccod_alm + '">' + obj[i].ccod_alm + ' - ' + obj[i].cdsc_alm + '</option>');
    }
    document.getElementById("txtAlmacen").setAttribute("value", "");
}

function CargarNumeradorTipoOper() {
    var listBox = document.getElementById("txtTipoOperacion");
    listBox.options.length = 0;

    var obj = llenarobjeto('ConsultaOperAlmacen.aspx/CargarTiposOperacionAlmacen');
    if (!obj) return;

    $('#txtTipoOperacion').append('<option value=""> (Todos) </option>');
    for (var i = 0; i < obj.length; i++) {
        $('#txtTipoOperacion').append('<option value="' + obj[i].ccod_tipoper + '">' + obj[i].cdsc_tipoper + '</option>');
    }
    document.getElementById("txtTipoOperacion").setAttribute("value", "");
}

function CargarMesActual() {
    var hoy = new Date();
    var d = function(n) { return (n < 10 ? '0' : '') + n; };
    var primerDia = d(1) + '/' + d(hoy.getMonth() + 1) + '/' + hoy.getFullYear();
    var ultimoDia  = d(hoy.getDate()) + '/' + d(hoy.getMonth() + 1) + '/' + hoy.getFullYear();
    $('#txtfchDesde').val(primerDia);
    $('#txtfchHasta').val(ultimoDia);
}


function ModalDocFac(row) {
   
   var id_cbfact = row.id;
   for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbinve ){

            id_cbfact = objfactura[i].id_cbinve;
            if(objfactura[i].DocRef==''){
                $('#btnIrComprobante').hide();
            }else{
                $('#btnIrComprobante').show();
            }
        }
    }
     
      $.ajax({
        type: "POST",
        url: 'ConsultaOperAlmacen.aspx/DatosReferencia',
        data: '{id_cbfact: "' + id_cbfact + '", id_cbinve: "' + row.id + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
    
            $('#upComprobanteInve').text(response.d[15]); 
            $('#upFechaInve').text(response.d[16]);  
            $('#upDocumentoInve').text(response.d[1]); 
//            $('#upCodTiendaInve').text(response.d[19]); 
//            $('#upNomTiendaInve').text(response.d[20]);  
            $('#upCodAlmacenInve').text(response.d[6]); 
            $('#upNomAlmacenInve').text(response.d[7]);  
            $('#upCodVendedorInve').text(response.d[8]); 
            $('#upNomVendedorInve').text(response.d[9]);  
            $('#upCodClienteInve').text(response.d[10]); 
            $('#upNomClienteInve').text(response.d[11]);
            $('#upTotalInve').text(response.d[14]);
             
            $('#upComprobante').text(response.d[1]);  
            $('#upFecha').text(response.d[2]);  
            $('#upCodTienda').text(response.d[4]); 
            $('#upNomTienda').text(response.d[5]);  
            $('#upCodCaja').text(response.d[17]); 
            $('#upNomCaja').text(response.d[18]); 
            $('#upCodVendedor').text(response.d[24]); 
            $('#upNomVendedor').text(response.d[25]);  
            $('#upCodCliente').text(response.d[10]);  
            $('#upNomCliente').text(response.d[11]);
            $('#upTotal').text(response.d[3]);  
           

           }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

     $.ajax({
        type: "POST",
        url: 'ConsultaOperAlmacen.aspx/ConsultaListImventarioPorId',
        data: '{id_cbfact: "' + row.id + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
            var obj = response.d;
            $("#table_visible_DatosInve > tbody").html("");
              for (var i = 0; i < obj.length; i++) {
              $("#table_visible_DatosInve").find('tbody')
                .append($('<tr>')
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].csim_unidadmedida + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncantidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncosto + '</td>')) 
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncosto_tot + '</td>')) 
                .append($('</tr>'))
                 );  
                 } 
            }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

        $.ajax({
        type: "POST",
        url: 'ConsultaOperAlmacen.aspx/ConsultaListArticulosPorId',
        data: '{id_cbfact: "' + id_cbfact + '"  }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
        if(response.d){
            var obj = response.d;
             $("#tbArticulo > tbody").html("");
              for (var i = 0; i < obj.length; i++) {
                $("#tbArticulo").find('tbody')
                .append($('<tr>')
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncantidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nprecio + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nimpuesto + '</td>'))
//                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nisc + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ndescuento + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nimporte_neto + '</td>'))
                .append($('</tr>'))
                 );  
                }  
            }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    $('#modalBuscarCodInve').modal('show');
}
  
  

function Ejecutar() {
    
    if(navigator.onLine) {

    if ($('#txtAlmacen').val() == null || $('#txtAlmacen').val() == "") {
        Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
        return;
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }

    $('#table_principal').DataTable().destroy();
    $('#table_visible').DataTable().destroy();

    var obj = [  {
        "ctipo": $('#txtTipoOperacion').val(),
        "cserie": $('#txtSerie').val(),
        "nnumero": $('#txtNumero').val(),
        "ccod_alm": $('#txtAlmacen').val(),
        "fchDesde": $('#txtfchDesde').val(),
        "fchHasta": $('#txtfchHasta').val(), 
        "ccoa_dsc": $('#txtCliente').val(),
        "cdsc_usuario": $('#txtUsuario').val()
    } ]

    
    $.ajax({
        type: "POST",
        url: 'ConsultaOperAlmacen.aspx/ConsultaOperAlmacenPricipal',
        data: JSON.stringify({ consultaoperalmacen: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         
         if (response.d=="-1"){
           MensajeFinSession();
           }else{ 
           objfactura = response.d; 

            $('#table_visible').DataTable({
                data: objfactura,     
                 "ordering": false,
                columns: [
                { data: 'ctipo' },
                { data: 'cserie' },
                { data: 'nnumero' }, 
                { data: 'ntotal'  },
                { data: 'dfecha'  },
                { data: 'ccod_alm_ing' },  
                { data: 'cdsc_usuario' },
                { data: 'ccoa_dsc' }, 
                { data: 'DocRef' },
                { data: 'DocFact' , className: "dt-body-center"}]   
            });
             
            $('#table_principal').DataTable({
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
                data: objfactura,
                columns: [ 
                { data: 'ctipo' },
                { data: 'cserie' },
                { data: 'nnumero' }, 
                { data: 'ntotal'  },
                { data: 'dfecha'  },
                { data: 'ccod_alm_ing' },  
                { data: 'cdsc_usuario' },
                { data: 'ccoa_dsc' }, 
                { data: 'DocRef' }],
                    scrollX: "2000px",
                scrollCollapse: true,
            });
            
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
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
    $('#txtSerie').val('');
    $('#txtNumero').val('');
    $('#txtCliente').val('');
    $('#txtUsuario').val('');
    document.getElementById("txtTipoOperacion").setAttribute("value", "");
    $('#txtTipoOperacion').val(''); 
    document.getElementById("txtAlmacen").setAttribute("value", "");
    $('#txtAlmacen').val('');
    var table = $('#table_visible').DataTable();
    table.clear().draw();
     
     
}
function PasaDatosCodUsuario() {
    var fila = $("#tableVisibleConsulUsuario input[name=radiob]:checked").closest('tr');
    $('#txtUsuario').val($("#tableVisibleConsulUsuario")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarUsuarios() {
     $('#tableVisibleConsulUsuario').DataTable().destroy();
    $('#table_secundariaConsultarUsuario').DataTable().destroy();

    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/CargarListaUsuario');

     $('#tableVisibleConsulUsuario').DataTable({
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
                {data: 'ccod_usuario' },
                {data: 'cdsc_usuario' }
                ]
    });
   
      $('#table_secundariaConsultarUsuario').DataTable({
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
                { data: 'ccod_usuario' },
                { data: 'cdsc_usuario' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });
}

function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText);
}
 
function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();
 

      var obj = llenarobjeto('../Operaciones/Ingresos.aspx/ConsultarProveedor');
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

 
 

 

$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas();
  $("#ModalDatosPersonales").draggable();

  $("#modalBuscarCodInve").draggable();

  $("#modalDetalleComprobante").draggable();

    CargarAlmacenes();
   CargarNumeradorTipoOper();
 

   CargarMesActual(); 
   document.getElementById("txtTipoOperacion").setAttribute("value", "");
     inicar_menu_nivel3('Consulta Movimientos de Almacen', '1_li_Almacen', '2_li_ConsultaAlmacen', '3_li_Almacen', '0');
    $('#btn_p_nuevo').hide();
    $('#btn_p_editar').hide();
    $('#btn_p_grabar').hide();
    $('#btn_p_eliminar').hide();
    $('#btn_p_back').hide();
    $('#btn_p_imprimir').hide();
    document.getElementById("divColsulta").style.visibility = "visible";
    $('#btn_p_ejecutar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_limpiar').removeClass("botones_des").addClass("botones_hab");

    traducir_tabla();
    $('#table_visible').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });
 
 
   $("#thTablaConsultarCliente").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tableExportarConsultarCliente').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

      $("#thTablaConsultarUsuario").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tableExportarConsultarUsuario').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

 $("#thTablaVisible").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tableExport').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });
   

  
});