
 

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
        DDscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Código del cliente";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtCodDocumento"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc";
        Nombre = "Código de documentación";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
         
    }else if($("#NombreColumna").val() == "txtSerieDoc"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc_serie";
        Nombre = "Serie de documentación";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtNroDoc"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc_nro";
        Nombre = "Numerador de documentación";
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
   
 var objfactura=[];


function ModalAnularDoc(row) {
    $('#txtMotivoDarDeBaja').val('');
      
  for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbfact ){ 
            $('#txtDocRefDarDeBaja').text("Anular el Documento : "+objfactura[i].cdoc+" "+objfactura[i].cdoc_serie+" "+objfactura[i].cdoc_nro);  
            $('#idfact').val(row.id);         
        }
    }
}


function GenerarDarDeBaja() {

    if ($('#hhd_vTienda').val() == "") {
        Mensaje('Advertencia','El código de la tienda no esta asignado o no es valido.\n\n Solicite configuración del código de la tienda al administrador.', 'warning');
        return;
    } else if ($('#hhd_vAlmacen').val() == "") {
        Mensaje('Advertencia','El código del almacen no esta asignado o no es valido.\n\n Solicite configuración del código del almacen al administrador.', 'warning');
        return;
    } else if ($('#hhd_vCaja').val() == "") {
        Mensaje('Advertencia','El código de la caja no esta asignado o no es valido.\n\n Solicite configuración del código de la caja al administrador.', 'warning');
        return;
    }else if ($('#txtMotivoDarDeBaja').val() == "") {
        Mensaje('Advertencia', 'Ingrese el motivo de la anulación.', 'warning');
        return; 
    } 

    var CodDocAnular = '';

    for (var i = 0; i < objfactura.length; i++) { 
        if($('#idfact').val() == objfactura[i].id_cbfact ){ 
            CodDocAnular = (objfactura[i].cdoc+" "+objfactura[i].cdoc_serie+" "+objfactura[i].cdoc_nro);   
        }
    }
 

    Swal.fire({
        title: "Deseas anular el \n\nDoc: "+CodDocAnular,
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Aceptar',
        showCancelButton: true,
        cancelButtonColor: '#f7505a',
        cancelButtonText: "Cancelar"
    }).then(
        (result) => {
        if (result.isConfirmed) {

    if(navigator.onLine) {

    $.ajax({
    type: "POST",
    url: 'Anulacion.aspx/AnulacionDoc',
    data: '{id_cbfact: "' + $('#idfact').val() + '", motivo: "' + $('#txtMotivoDarDeBaja').val()+ '"  }',
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    async: false,
    success: function (response) { 
    if(response.d){
    var obj = response.d;
        if(obj[0].cdoc_seri == 'ErrorCodOper'){
            Swal.fire({
                title: "El codigo de operación de ingreso para anular no esta configurado o no es valido.\n\nSolicite configuración al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 
        }else if(obj[0].cdoc_seri == 'ErrorNumeradorInve'){
            Swal.fire({
                title: "El numerador del documento de ingreso de almacen ("+obj[0].cdoc_nro+") ya esta registrado.\n\nSolicite configuración del numerador al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 

         }else if(obj[0].cdoc_seri == 'ErrorTienda'){
            Swal.fire({
                title: "El código de la tienda asignado al usuario no esta configurado o no es valido.\n\nSolicite configuración de tienda al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 
            }else if(obj[0].cdoc_seri == 'ErrorEstadoTienda'){
            Swal.fire({
                title: "El código de la tienda asignado al usuario esta inactivo.\n\nSolicite activación del codigo de la tienda al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 
            }else if(obj[0].cdoc_seri == 'ErrorAlmacen'){
            Swal.fire({
                title: "El código del almacen asignado al usuario no esta configurado o no es valido.\n\nSolicite configuracion de almacen al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 
            }else if(obj[0].cdoc_seri == 'ErrorEstadoAlmacen'){
            Swal.fire({
                title: "El código del almacen asignado al usuario esta inactivo.\n\nSolicite activación del codigo de almacen al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 
            }else if(obj[0].cdoc_seri == 'ErrorEstadoUsuario'){
            Swal.fire({
                title: "El código del usuario esta inactivo.\n\nSolicite activación del codigo del usuario al administrador.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Cancelar'
            }); 
        }else if(obj[0].cdoc_seri == 'OK'){
           Mensaje('Correcto','','success'); 
           $("#modalDarDeBaja").modal('hide');
           Ejecutar();
//          $(row).closest("tr").attr("style", "display: none;");
//          $(row).closest("tr")[0].children[1].innerHTML = '3';
      
              
             
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
});  


} 
 


function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText);
}
 
function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();

    var obj = llenarobjeto('Anulacion.aspx/CargarCliente');
 
  $('#hdd_numerofilas').val(obj.length);
     $('#tableVisibleConsulClientes').DataTable({
        "pageLength": 5,
        data: obj,
        columns: [
                    { data: 'cbx',
                    render: function (data, type, row) {
                    if (1 == 1) { return '<input type="radio" name="radiob">'; }
                    return data;},
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

function ModalBuscarDoc(row) {
 
    
    $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/DatosReferencia',
        data: '{id_cbfact: "' + row.id + '", id_cbinve: "' + "" + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
            
            $('#upFecha').text(response.d[2]);  
            $('#upCodTienda').text(response.d[4]); 
            $('#upNomTienda').text(response.d[5]); 
            $('#upCodCaja').text(response.d[17]);  
            $('#upCodVendedor').text(response.d[24]);   
            $('#upCodCliente').text(response.d[10]); 
            $('#upNomCliente').text(response.d[11]); 
            $('#upTotal').text(response.d[3]);   
            $('#upNomCaja').text(response.d[18]);   
            $('#upNomVendedor').text(response.d[25]);   
            $('#upComprobante').text(response.d[1]);
             

           }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });



   $('#tbArticulo').DataTable().destroy();
   $('#table_secundariaDetalleArticulo').DataTable().destroy();
  
     $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/ConsultaListArticulosPorId',
        data: '{id_cbfact: "' +  row.id + '" }',
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
//                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nisc + '</td>'))
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
                { data: 'ncantidad',className: "dt-body-right" },
                 { data: 'nprecio',className: "dt-body-right" },
                 { data: 'nimporte_neto',className: "dt-body-right" }  ],
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

     if ($('#txtTienda').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return; 
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    } else if ($('#txtCodDocumento').val() == "") {
        Mensaje('Advertencia', 'Ingresar código documento.', 'warning');
        return;
    }

    $('#table_principalDoc').DataTable().destroy();
    $('#table_visibleDoc').DataTable().destroy();
 
   var obj = [  {
        "cdoc": $('#txtCodDocumento').val(),
        "cdoc_serie": $('#txtSerieDoc').val(),
        "cdoc_nro": $('#txtNroDoc').val(),
        "ccod_coa": $('#txtCliente').val(),
        "n_fchDesde": $('#txtfchDesde').val(),
        "n_fchHasta": $('#txtfchHasta').val(), 
        "ccod_tienda": $('#txtTienda').val()
    } ] 

    $.ajax({
        type: "POST",
        url: 'Anulacion.aspx/AnulacionPricipal',
        data: JSON.stringify({ anulacion: obj }),  
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
          objfactura = response.d;  

        if (response.d=="-1"){
           MensajeFinSession(); 
           }else{   
            $('#table_visibleDoc').DataTable({
                data: objfactura,
                 "ordering": false,
                columns: [
                { data: 'cdoc' },
                { data: 'cdoc_serie' },
                { data: 'cdoc_nro' },
                { data: 'cdoc_coa' },
                { data: 'cdsc_coa' },
                { data: 'ntotal',className: "dt-body-right" },
                { data: 'dfch_doc',className: "dt-body-right" }, 
                { data: 'DocFact', className: "dt-body-center" },
                { data: 'Anulacion', className: "dt-body-center" } ]
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
                 { data: 'cdoc' },
                { data: 'cdoc_serie' },
                { data: 'cdoc_nro' },
                { data: 'cdoc_coa' },
                { data: 'cdsc_coa' },
                { data: 'ntotal',className: "dt-body-right" },
                { data: 'dfch_doc',className: "dt-body-right" } ],
                    scrollX: "2000px",
                scrollCollapse: true
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
    $('#txtCodDocumento').val('');
    $('#txtSerieDoc').val('');
    $('#txtNroDoc').val('');

    $('#txtTienda').val('');
    $('#txtCliente').val('');
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
        
        document.getElementById("txtCodDocumento").setAttribute("value", "");
  document.getElementById("txtTienda").setAttribute("value", "");
    $('#table_visibleDoc').DataTable().destroy();

  var table = $('#table_visibleDoc').DataTable();
    table.clear().draw();



}
 

 
  

$(document).ready(function () {
  
    CargarMenu();  

    ConsultaColumnas();
   $("#modalConsultarClientes").draggable();

   $("#modalBuscarDoc").draggable();
    
   $("#ModalDatosPersonales").draggable();
   $("#modalDarDeBaja").draggable();
  CargarTienda();
   CargarMesActual(); 
  document.getElementById("txtCodDocumento").setAttribute("value", "Código Doc.*");
   
  
  inicar_menu_nivel3('Anulación de documentos', '1_li_Ventas', '2_li_Ventas_Operaciones', '3_Li_Anulacion', '');
    
     
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
