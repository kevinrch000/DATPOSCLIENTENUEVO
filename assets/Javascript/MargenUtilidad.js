var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "txtfchDesde"){
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
    }else if($("#NombreColumna").val() == "txtCliente"){
        DscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Código del cliente";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtCodDocumento"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc";
        Nombre = "Código de documento";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtSerieDoc"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc_serie";
        Nombre = "Serie de documento";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtNroDoc"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc_nro";
        Nombre = "Numerador de documento";
        Estado = "Opcional";
        TipoDato = "1 hasta";
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

var tipoOper ="";
var serie ="";
var numero ="";
var AlmCod ="";
var AlmNom ="";

function ModalBuscarCodInve() {
 
    $('#table_visible_DatosInve').DataTable().destroy();
   $('#table_secundariaDatosInve').DataTable().destroy();

// var currentRow = $(row).closest("tr");  
  
  $('#upComprobanteInve').text(tipoOper+" "+serie+" "+numero); 
 
   
   $('#upCodTiendaInve').text($('#upCodTienda').text()); 
     $('#upNomTiendaInve').text($('#upNomTienda').text());   

      $('#upCodAlmacenInve').text(AlmCod); 
     $('#upNomAlmacenInve').text(AlmNom);   

 $('#upCodVendedorInve').text($('#upCodVendedor').text()); 
     $('#upNomVendedorInve').text($('#upNomVendedor').text());   

 $('#upCodClienteInve').text($('#upCodCliente').text()); 
     $('#upNomClienteInve').text($('#upNomCliente').text());   
      
         $('#upFechaInve').text($('#upFecha').text());
      

     $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/ConsultaListArticulosInveDat',
        data: '{tipoOper: "' + tipoOper + '",serie: "' + serie + '",numero: "' + numero + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
          
         if(response.d){
            obj = response.d;
            $("#table_visible_DatosInve > tbody").html("");

            var misuma=0.0; 

              for (var i = 0; i < obj.length; i++) {

          var CostoTot=parseFloat(obj[i].ncosto)*parseFloat(obj[i].ncantidad);

              misuma=parseFloat(misuma)+parseFloat(CostoTot);
                $("#table_visible_DatosInve").find('tbody')
                .append($('<tr>')
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].csim_unidadmedida + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncantidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncosto + '</td>')) 
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + CostoTot + '</td>')) 
                .append($('</tr>'))
                 ); 
                  
               
                 }
                         $('#upTotalInve').text(misuma); 
         
                    
                $('#table_secundariaDatosInve').DataTable({
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
                { data: 'ccod_articulo' },
                 { data: 'cdsc_articulo' },
                 { data: 'csim_unidadmedida' },
                 { data: 'ncantidad' },
                 { data: 'ncosto' }  ],
                    scrollX: "2000px",
                scrollCollapse: true
            });
            }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

}



function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();
    $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaDocumento.aspx/CargarClienteFacturar',
        data: '{tip_doc: "' + $('#txtCodDocumento').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            var obj = response.d;  
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

        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

 
}


function ModalBuscarDoc(row) {
  $('#upTotal').text(""); 
  $('#upFecha').text(""); 
  $('#upDocRef').text(""); 
  tipoOper ="";
  serie ="";
  numero ="";
   AlmCod ="";
    AlmNom ="";
  $('#upCodTienda').text("");
      $('#upNomTienda').text("");

     $('#upCodCaja').text(""); 
     $('#upNomCaja').text("");

     $('#upCodVendedor').text("");
       $('#upNomVendedor').text("");
     
      $('#upCodCliente').text("");
     $('#upNomCliente').text("");
     $('#upComprobante').text(""); 


   $('#tbArticulo').DataTable().destroy();
   $('#table_secundariaDetalleArticulo').DataTable().destroy();
 var currentRow = $(row).closest("tr");
      $('#upComprobante').text(currentRow.find("td:eq(0)").text()+" "+currentRow.find("td:eq(1)").text()+" "+currentRow.find("td:eq(2)").text()); 
    $('#upTotal').text(currentRow.find("td:eq(6)").text()); 
      $('#upFecha').text(currentRow.find("td:eq(9)").text()); 
  $('#upDocRef').text(currentRow.find("td:eq(8)").text()); 
    $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/ConsultarMargenUtilidadArticuloDatos',
        data: '{cdoc: "' + currentRow.find("td:eq(0)").text() + '",cdoc_serie: "' + currentRow.find("td:eq(1)").text() + '",cdoc_nro: "' + currentRow.find("td:eq(2)").text() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
            obj = response.d;
      $('#upCodTienda').text(obj[0].ccod_tienda); 
      $('#upNomTienda').text(obj[0].cdsc_tienda); 

     $('#upCodCaja').text(obj[0].ccod_caja); 
     $('#upNomCaja').text(obj[0].cdsc_caja); 

     $('#upCodVendedor').text(obj[0].cusu_crea); 
       $('#upNomVendedor').text(obj[0].cdsc_usuario); 
     
      $('#upCodCliente').text(obj[0].ccod_coa); 
     $('#upNomCliente').text(obj[0].ccoa_dsc);

          tipoOper =obj[0].n_tipoOper;
          serie =obj[0].n_serie;
          numero =obj[0].n_numero;
           AlmCod =obj[0].ccod_alm;
            AlmNom =obj[0].cdsc_alm;
          }   
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });


     $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/ConsultarMargenUtilidadArticulo',
        data: '{cdoc: "' + currentRow.find("td:eq(0)").text() + '",cdoc_serie: "' + currentRow.find("td:eq(1)").text() + '",cdoc_nro: "' + currentRow.find("td:eq(2)").text() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
            obj = response.d;
             $("#tbArticulo > tbody").html("");
              for (var i = 0; i < obj.length; i++) {
                $("#tbArticulo").find('tbody')
                .append($('<tr>')
                 .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncantidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nprecio + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncosto + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].n_margenUtilidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].n_marUtiPorcenta  + '</td>'))
                .append($('</tr>'))
                 ); 
                 }
                    
                $('#table_secundariaDetalleArticulo').DataTable({
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
                 { data: 'ccod_articulo' },
                 { data: 'cdsc_articulo' },
                 { data: 'ncantidad',className: "dt-body-right" },
                 { data: 'nprecio',className: "dt-body-right" },
                 { data: 'ncosto',className: "dt-body-right" }, 
                 { data: 'n_margenUtilidad' ,className: "dt-body-right" },
                 { data: 'n_marUtiPorcenta',className: "dt-body-right" },],
                    scrollX: "2000px",
                scrollCollapse: true
            });
             }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

}


 
function Ejecutar() {  

    if(navigator.onLine) {

     if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return; 
    } else if ($('#txtCodDocumento').val() == "") {
        Mensaje('Advertencia', 'Seleccionar tipo de documento.', 'warning');
        return;
    }

    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
      
     var objMargenUtilidad = [  {
                    "cdoc": $('#txtCodDocumento').val(),
                    "cdoc_serie": $('#txtSerieDoc').val(),
                    "cdoc_nro": $('#txtNroDoc').val(),
                    "n_fchDesde": $('#txtfchDesde').val(),
                    "n_fchHasta": $('#txtfchHasta').val(),
                    "ccoa_dsc": $('#txtCliente').val()
                     } ]  

    $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/MargenUtilidadPricipal',
        data: JSON.stringify({ 
            MargenUtilidad: objMargenUtilidad
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) { 
          if (response.d== "-1"){
           MensajeFinSession();
           }else{ 
           
            var obj = response.d; 
            $('#table_id').DataTable({
            "ordering": false,
               data: obj,
                columns: [
                        { data: 'cdoc' },
                        { data: 'cdoc_serie' },
                        { data: 'cdoc_nro' },
                        { data: 'ccoa_dsc' }, 
                        { data: 'nprecio' },
                        { data: 'ncosto' },
                        { data: 'n_margenUtilidad' },
                        { data: 'n_marUtiPorcenta' },
                        { data: 'n_docRef' },
                        { data: 'dfch_crea' }, 
                        { data: 'cbx',
                        render: function (data, type, row) {
                        if (1 == 1) { return '<td class="text-center"><a style="background-color: #ffffff;border:0px;margin:10px" class="disabled input-group-addon" data-toggle="modal" data-target="#modalBuscarDoc" onclick="ModalBuscarDoc(this)"><i class="fa fa-arrow-right color-popup-verde" aria-hidden="true"></i></a></td>'; }
                        return data;
                        },
                        className: "dt-body-center"
                        }
                    ]
            });
            $('#tableArticulo').DataTable({
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
                        { data: 'cdoc' },
                        { data: 'cdoc_serie' },
                        { data: 'cdoc_nro' },
                        { data: 'ccoa_dsc' }, 
                        { data: 'nprecio' },
                        { data: 'ncosto' },
                        { data: 'n_margenUtilidad' },
                        { data: 'n_marUtiPorcenta' },
                        { data: 'n_docRef' },
                        { data: 'dfch_crea' }],
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
// CargarEstadisticasMargenUtilidad();
}

 

function CargarEstadisticasMargenUtilidad() {
 document.getElementById('containerBarras').style.display = 'inline'; 
 var objMargenUtilidad = [  {
                    "cdoc": $('#txtCodDocumento').val(),
                    "cdoc_serie": $('#txtSerieDoc').val(),
                    "cdoc_nro": $('#txtNroDoc').val(),
                    "n_fchDesde": $('#txtfchDesde').val(),
                    "n_fchHasta": $('#txtfchHasta').val(),
                    "ccoa_dsc": $('#txtCliente').val()
                     } ]  
       
     $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/CargarEstadisticasMargenUtilidad',
        data: JSON.stringify({ 
            MargenUtilidad: objMargenUtilidad
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            objMargenUtilidad = response.d;
             $("#datatable tbody").html("");
    var cuerpo = ""
     
    for (var i = 0; i < objMargenUtilidad.length; i++) { 
        cuerpo += '<tr>' +
                    '<td>' + objMargenUtilidad[i].Tipo + '</td>' +
                    '<td>' + objMargenUtilidad[i].Cantidad + '</td>' ; 
    }
    $('#datatable tbody').append(cuerpo);
     
        },

        error: function (xhr, status, error) {
            alert(error);
        }
    });
    
 
     Highcharts.chart('containerBarras', {
        data: {
            table: 'datatable'
        },
        chart: {
            type: 'column'
        },
        title: {
            text: 'Margen de Utilidad por documentos'
        },
        yAxis: {
            allowDecimals: true,
            title: {
                text: 'Cantidad'
            }
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                this.point.y + ' ' + this.point.name.toLowerCase();
            }
        }
    });
 
 }

$(document).ready(function () {
     CargarMenu();
     ConsultaColumnas();

     CargarMesActual();  
  $("#modalConsultarArticulos").draggable();

  $("#ModalDatosPersonales").draggable();
   
    $("#modalBuscarDoc").draggable();

        $("#modalBuscarCodInve").draggable();
      $("#modalConsultarClientes").draggable();

  document.getElementById("txtCodDocumento").setAttribute("value", "Código Doc.*");
 

    inicar_menu_nivel3('Consulta de Margen de Utilidad por Documentos', '1_li_Ventas','2_li_ConsultaVenta', '3_li_MargenUtilidadDoc', '0');
 
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
    $('#table_id').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });

     

       //    Funcion para generar exel
    $("#thTablaDatosInve").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tableExportarDatosInve').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });

    //    Funcion para generar exel
    $("#thTablaArticulo").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });

$("#thTablaDetalleArticulos").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tableExportarDetalleArticulo').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

});

function PasaDatosCodEmpresa() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarArticulos() {
    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();

    var obj = llenarobjeto('MargenUtilidad.aspx/CargarArticulo');
   
    $('#table_visible_ConsultarArticulos').DataTable({
    "pageLength": 5,
        data: obj,
        columns: [
                    { data: 'cbx',
                    render: function (data, type, row) {
                    if (type === 'display') { return '<input type="radio" name="radiob">'; }
                    return data;
                    },
                    className: "dt-body-center"
                    },
                    {data: 'ccod_articulo' },
                    {data: 'cdsc_articulo' } ]
    });
    $('#table_secundariaConsultarArticulos').DataTable({
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
                { data: 'ccod_articulo' },
                { data: 'cdsc_articulo' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });
     
}
function CargarUnidadMedida(){
    var listBox = document.getElementById("slUniMedida");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/CargarUnidadMedida',
        data: '{codigo: "' + "" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d){
                var $dropdown = $("#slUniMedida");
                $dropdown.append($("<option />").val("").text(""));
                $dropdown.append($("<option />").val("%%%").text("Todos"));
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_unidadmedida).text(this.cdsc_unidadmedida));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarFamilia(){
    var listBox = document.getElementById("slFamilia");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'MargenUtilidad.aspx/CargarFamilia',
        data: '{codigo: "' + "" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d){
                var $dropdown = $("#slFamilia");
                $dropdown.append($("<option />").val("").text(""));
                $dropdown.append($("<option />").val("%%%").text("Todos"));
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_lin).text(this.cdsc_lin));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function Limpiar(){
   $ 
    $('#txtSerieDoc').val('');
    $('#txtCliente').val('');
    $('#txtNroDoc').val('');  
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val(''); 
     
    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
    var table = $('#table_id').DataTable();
    table.clear().draw();

//    document.getElementById('containerCemiCirculo').style.display = 'none';
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
