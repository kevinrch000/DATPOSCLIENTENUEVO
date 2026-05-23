

var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
 
 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "txtTienda"){
        DscTabla = "ad_tienda";
        DscColumna = "ccod_tienda";
        Nombre = "Código de tienda";
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
    }else if($("#NombreColumna").val() == "txtCliente"){
        DscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Código del cliente";
        Estado = "Opcional";
        TipoDato = "1 hasta"; 
    }else if($("#NombreColumna").val() == "txtCodArticulo"){
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de Artículo";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtVariante"){
        DscTabla = "al_lnvariante";
        DscColumna = "cdsc_lnvariante";
        Nombre = "Detalle de variante";
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

function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText); 
}

function ModalConsultarClientes() {
 
    
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();

    var obj = llenarobjeto('ConfigGeneral.aspx/CargarCliente');
 

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
                 {data: 'ccod_coa' },
                    { data: 'cdsc_coa' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });
        }


function PasaDatosCodArticulo() {
    var fila = $("#tableVisibleConsultaArticulo input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#tableVisibleConsultaArticulo")[0].rows[fila[0].rowIndex].cells[1].innerText);
}
 
function ModalConsultarArticulo() {

$('#modalConsultarArticulo').modal('show'); 

$('#tableVisibleConsultaArticulo').DataTable().destroy();
$('#table_secundariaConsultarArticulo').DataTable().destroy();

var obj = llenarobjeto('../Consultas/ConsultaArticulos.aspx/CargarArticulo'); 

    $('#tableVisibleConsultaArticulo').DataTable({
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
                    { data: 'cdsc_articulo' }
                    ]
    });
   
    $('#table_secundariaConsultarArticulo').DataTable({
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
                {data: 'ccod_articulo' },
                { data: 'cdsc_articulo' }],
                scrollX: "2000px",
                scrollCollapse: true,
    });
}

var objfactura=[];

function Ejecutar() {

    if(navigator.onLine) {

    if ($('#txtTienda').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }  

  $('#table_principalDoc').DataTable().destroy();
    $('#table_visibleDoc').DataTable().destroy();
     
     
     var obj = [
        {
            "ccod_articulo": $('#txtCodArticulo').val(),
            "ccod_tienda": $('#txtTienda').val(),
            "ccod_coa": $('#txtCliente').val(),
            "n_fchDesde": $('#txtfchDesde').val(),
            "n_fchHasta": $('#txtfchHasta').val(),
            "cobser_variante": $('#txtVariante').val()
        }
    ]

    $.ajax({
        type: "POST",
        url: 'ConsultasVenta.aspx/ConsultasVentaPricipal',
        data: JSON.stringify({ ConsultaArticulo: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {

        if (response.d=="-1"){
           MensajeFinSession();
           }else{
 
             objfactura = response.d;
            $('#hdd_numerofilas').val(objfactura.length);

            $('#table_visibleDoc').DataTable({ 
                data: objfactura,
                "ordering": false,
                columns: [ 
                { data: 'ccod_coa' },
                { data: 'ccod_articulo' },  
                { data: 'cdsc_articulo' }, 
                { data: 'ncantidad',className: "dt-body-right" },
                { data: 'nprecio',className: "dt-body-right" },
                { data: 'nimpuesto',className: "dt-body-right" },
                { data: 'nisc',className: "dt-body-right" },
                { data: 'ndescuento',className: "dt-body-right" }, 
                { data: 'nimporte_neto',className: "dt-body-right" }, 
                { data: 'dfch_doc' },
                { data: 'cobser_variante' },
                { data: 'cstatus', className: "dt-body-center" }]
            });

            $('#table_principalDoc').DataTable({
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
                 { data: 'ccod_coa' },
                { data: 'ccod_articulo' },  
                { data: 'cdsc_articulo' }, 
                { data: 'ncantidad',className: "dt-body-right" },
                { data: 'nprecio',className: "dt-body-right" },
                { data: 'nimpuesto',className: "dt-body-right" },
                { data: 'nisc',className: "dt-body-right" },
                { data: 'ndescuento',className: "dt-body-right" }, 
                { data: 'nimporte_neto',className: "dt-body-right" }, 
                { data: 'dfch_doc' },
                { data: 'cobser_variante' } ],
                 scrollX: "2000px",
                scrollCollapse: true
            });

            $('#txtCantTot').val('');
            $('#txtImpBrutoTot').val('');
            $('#txtIgvTot').val('');
            $('#txtIscTot').val('');
            $('#txtDescTot').val('');
            $('#txtImpNetoTot').val('');
             if(objfactura.length>0){ 
                $.ajax({  
                    type: "POST",
                    url: 'ConsultasVenta.aspx/DatosAdicionales',
                    data: JSON.stringify({ VentasPorArticulo: objfactura }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,
                    success: function (response) {
                        var  objDA = response.d;
                        $('#txtCantTot').val(objDA.ncantidad);
                        $('#txtImpBrutoTot').val(objDA.nimporte_bruto);
                        $('#txtIgvTot').val(objDA.nimpuesto);
                        $('#txtIscTot').val(objDA.nisc);
                        $('#txtDescTot').val(objDA.ndescuento);       
                        $('#txtImpNetoTot').val(objDA.nimporte_neto);  
                    },
                    
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }); 
            }
           

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
    $('#txtCodArticulo').val('');
    $('#txtTienda').val(''); 
    $('#txtCliente').val(''); 
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
    $('#txtVariante').val('');
     
    document.getElementById("txtTienda").setAttribute("value", "");
    document.getElementById("txtCliente").setAttribute("value", "");
    $('#table_visibleDoc').DataTable().destroy();

  var table = $('#table_visibleDoc').DataTable();
    table.clear().draw(); 
  
}
 
  

$(document).ready(function () {
     CargarMenu();

     ConsultaColumnas();
 
 
$("#ModalDatosPersonales").draggable();

   $("#modalConsultarClientes").draggable();

    $("#modalConsultarArticulo").draggable();

    $("#modalBuscarDoc").draggable();
    
   CargarMesActual();
    CargarTienda(); 
       document.getElementById("txtCliente").setAttribute("value", "");
     inicar_menu_nivel3('Consulta de Ventas por Articulo', '1_li_Ventas', '2_li_ConsultaVenta', '3_li_VentaS', '0');
    
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
    $('#table_visibleDoc').DataTable({
        "zeroRecords": "No se encontraron resultados."
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

    $("#thTablaConsultarArticulo").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tableExportarConsultarArticulo').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
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


function ModalBuscarDoc(row) {

    for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbfact ){
            $('#upComprobante').text(objfactura[i].cdoc_seri); 
            $('#upFecha').text(objfactura[i].dfch_doc);
            $('#upCodTienda').text(objfactura[i].ccod_tienda);
            $('#upNomTienda').text(objfactura[i].cdsc_tienda);
            $('#upCodCaja').text(objfactura[i].ccod_caja );
            $('#upCodVendedor').text(objfactura[i].cusu_crea ); 
            $('#upNomCaja').text(objfactura[i].cdsc_caja );
            $('#upNomVendedor').text(objfactura[i].cdsc_usuario);
            $('#upCodCliente').text(objfactura[i].ccod_coa);
            $('#upNomCliente').text(objfactura[i].ccoa_dsc); 
            $('#upTotal').text(objfactura[i].ntotal);
        }

    }

   
        
  $('#table_secundariaDetalleArticulo').DataTable().destroy();
  
     $.ajax({
        type: "POST",
        url: 'ConsultasVenta.aspx/ConsultaListArticulos',
        data: '{id_fact: "' + row.id + '" }',
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
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nimpuesto + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nisc + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ndescuento + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nimporte_neto + '</td>'))
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
                { data: 'ncantidad' }, 
                { data: 'nprecio' }, 
                 { data: 'nimpuesto' },
                  { data: 'nisc' },
                 { data: 'ndescuento' },
                  { data: 'nimporte_neto' }  ],
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