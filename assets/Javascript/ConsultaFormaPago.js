 
var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
     if($("#NombreColumna").val() == "txtCaja"){
        DscTabla = "al_ctcaja";
        DscColumna = "ccod_caja";
        Nombre = "Código de caja";
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
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtCodDocumento"){
        DscTabla = "fi_cbcajac";
        DscColumna = "cdoc_tipo";
        Nombre = "Código de documento";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtSerieDoc"){
        DscTabla = "fi_cbcajac";
        DscColumna = "cdoc_serie";
        Nombre = "Serie de documento";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtNroDoc"){
        DscTabla = "fi_cbcajac";
        DscColumna = "cdoc_nro";
        Nombre = "Numerador de documento";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtTipoTarjeta"){
        DscTabla = "fi_lncajac";
        DscColumna = "cnom_tarje";
        Nombre = "Nombre tipo de pago";
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

function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText); 
}

function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();

    var obj = llenarobjeto('ConsultaFormasPago.aspx/CargarCliente');
 

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

 

function ModalBuscarDoc(row) {

   try { $('#MdFacturacion').modal('show'); } catch (e) { console.error('No se pudo abrir MdFacturacion:', e); }

   var id_cbcajac = "";
   for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbfact ){
            id_cbcajac = objfactura[i].id_cbcajac;
        }
    }

  $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/DatosReferencia',
        data: '{id_cbfact: "' + row.id + '", id_cbinve: "' + "" + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
            
            $('#upDocumentoFac').text(response.d[1]); 
            $('#upComprobante').text(response.d[23]); 
            $('#upFecha').text(response.d[2]);
            $('#upCodTienda').text(response.d[4]);
            $('#upNomTienda').text(response.d[5]);
            $('#upCodCaja').text(response.d[17]);
            $('#upNomCaja').text(response.d[18]);
            $('#upCodVendedor').text(response.d[24]);
            $('#upNomVendedor').text(response.d[25]);
            $('#upCodCliente').text(response.d[10]); 
            $('#upNomCliente').text(response.d[11]); 
            $('#upImporFac').text(response.d[3]);
            $('#upTotalEntregado').text(response.d[22]);
            $('#upVuelto').text(response.d[21]);
               
            $('#MdFactCodDoc').text(response.d[1]);  
            $('#MdFactFecha').text(response.d[2]);
            $('#MdFactCodTienda').text(response.d[4]);
            $('#MdFactNomTienda').text(response.d[5]);
            $('#MdFactCodCaja').text(response.d[17]);
            $('#MdFactNomCaja').text(response.d[18]);
            $('#MdFactCodVendedor').text(response.d[24]);
            $('#MdFactNomVendedor').text(response.d[25]);
            $('#MdFactCodCliente').text(response.d[10]); 
            $('#MdFactNomCliente').text(response.d[11]);  
            $('#MdFactTotal').text(response.d[3]);

           }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

 $.ajax({
    type: "POST",
    url: 'ConsultaFormasPago.aspx/ConsultaListCobranzaId',
    data: '{id_cbcajac: "' + id_cbcajac + '" }',
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    async: false,
    success: function (response) {  
        if(response.d){ 
            var obj = response.d; 
        $("#tbCobranza > tbody").html("");
            for (var i = 0; i < obj.length; i++) {
            $("#tbCobranza").find('tbody')
            .append($('<tr>')
            .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cnom_tarje + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cnum_opera + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cnum_tarje + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nmonto + '</td>'))
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
    url: '../Consultas/ConsultaOperAlmacen.aspx/ConsultaListArticulosPorId',
    data: '{id_cbfact: "' + row.id + '" }',
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    async: false,
    success: function (response) { 
    if(response.d){
        var obj = response.d; 
            $("#tbFactArticulo > tbody").html("");
            for (var i = 0; i < obj.length; i++) {
            $("#tbFactArticulo").find('tbody')
            .append($('<tr>')
            .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].cdsc_articulo + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].ncantidad + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nprecio + '</td>'))
            .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nimpuesto + '</td>'))
//            .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nisc + '</td>'))
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
 
 
function Ejecutar() {

    if(navigator.onLine) {

     if ($('#txtCaja').val() ==null) {
        Mensaje('Advertencia', 'Ingresar caja.', 'warning');
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

    var objFormaPago = [
        {
        "cnom_tarje": $('#txtTipoTarjeta').val(), 
        "ccod_coa": $('#txtCliente').val(),
        "cdoc": $('#txtCodDocumento').val(),
        "cdoc_serie": $('#txtSerieDoc').val(),
        "cdoc_nro": $('#txtNroDoc').val(),
        "ccod_caja": $('#txtCaja').val(),
        "fchDesde": $('#txtfchDesde').val(),
        "fchHasta": $('#txtfchHasta').val(),
        "cusu_crea": $('#txtUsuario').val(),
        "Opcion": $('#Opcion').val() 
        }
    ]

    if ($('#Opcion').val() == "TLista"){
        $('#table_principalDoc').DataTable().destroy();
        $('#table_visibleDoc').DataTable().destroy();
    } else {
        $('#table_visibleDocDetallado').DataTable().destroy(); 
        $('#table_DetalladoExpor').DataTable().destroy(); 
    }


   

   
    $.ajax({
        type: "POST",
        url: 'ConsultaFormasPago.aspx/ConsultaFormasPagoPricipal',
        data: JSON.stringify({ FormaPago: objFormaPago }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
        
        if (response.d=="-1"){
           MensajeFinSession();
           }else{
              objfactura = response.d; 
            if ($('#Opcion').val() == "TLista"){

            $('#table_visibleDoc').DataTable({
               "ordering": false,
                data: objfactura,
                columns: [
                { data: 'cdoc' },
                { data: 'cdoc_serie' },
                { data: 'cdoc_nro' },
                { data: 'cdsc_usuario' }, 
                { data: 'cdsc_coa' },
                { data: 'Efectivo' },
                { data: 'Tarjeta' }, 
                { data: 'NotaCredito' },
                { data: 'NotaDebito' },
                { data: 'ntotal' },
                { data: 'dfch_crea' },
                { data: 'DocFact', className: "dt-body-center" }]
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
                { data: 'cdsc_usuario' },
                { data: 'cdsc_coa' },
                { data: 'Efectivo' },
                { data: 'Tarjeta' }, 
                { data: 'NotaCredito' },
                { data: 'NotaDebito' },
                { data: 'ntotal' },
                { data: 'dfch_crea' }],
                    scrollX: "2000px",
                scrollCollapse: true
            });

                $('#txt_IPEfectivo').val('');
                $('#txt_IPTarjeta').val('');
                $('#txt_IPNC').val('');
                $('#txt_IPV').val('');
                $('#txt_IPND').val('');
                if(objfactura.length>0){ 
                    $.ajax({  
                        type: "POST",
                        url: 'ConsultaFormasPago.aspx/DatosAdicionales',
                        data: JSON.stringify({ FormaPago: objfactura }),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        async: false,
                        success: function (response) {
                            var  objDA = response.d;
                            $('#txt_IPEfectivo').val(objDA.Efectivo);
                            $('#txt_IPTarjeta').val(objDA.Tarjeta);
                            $('#txt_IPNC').val(objDA.NotaCredito);
                            $('#txt_IPND').val(objDA.NotaDebito);
                            $('#txt_IPV').val(objDA.ntotal); 
                        },
                        error: function (xhr, status, error) {
                            alert(error);
                        }
                    });
                }

                } else {

                    $('#table_visibleDocDetallado').DataTable({
                    data: objfactura,
                      "ordering": false,
                    columns: [
                    { data: 'cdoc' },
                    { data: 'cdoc_serie' },
                    { data: 'cdoc_nro' },
                    { data: 'cdsc_usuario' },
                    { data: 'cnom_tarje' },
                    { data: 'nmonto' },
                    { data: 'DocRef' },
                    { data: 'dfch_crea' } ] 
                });

                $('#table_DetalladoExpor').DataTable({
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
                    { data: 'cdsc_usuario' },
                    { data: 'cdsc_coa' },
                    { data: 'cnom_tarje' },
                    { data: 'nmonto' },
                    { data: 'nvuelto' },
                    { data: 'DocRef' },
                    { data: 'dfch_crea' }  ],
                        scrollX: "2000px",
                    scrollCollapse: true
                }); 

                 $('#txt_MontoTT').val(''); 
                if(objfactura.length>0){ 
                    $.ajax({  
                        type: "POST",
                        url: 'ConsultaFormasPago.aspx/DatosAdicionales',
                        data: JSON.stringify({ FormaPago: objfactura }),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json", 
                        async: false,
                        success: function (response) {
                            var  objDA = response.d; 
                            $('#txt_MontoTT').val(objDA.ntotal); 
                        },
                        error: function (xhr, status, error) {
                            alert(error);
                        }
                    });
                }
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

 
 function Opcion() { 
    $('#Opcion').val("TLista");
}
 
 function Opcion2() { 
    $('#Opcion').val("TDetallado");
}


function Limpiar() { 
  $('#txtfchHasta').val('');
    $('#txtfchDesde').val('');

    $('#txtCodDocumento').val('');
    $('#txtSerieDoc').val(''); 
    $('#txtNroDoc').val(''); 
    $('#txtTipoTarjeta').val(''); 
    $('#txtCliente').val('');
    $('#txtCaja').val(''); 
     document.getElementById("txtCaja").setAttribute("value", "");
     document.getElementById("txtTipoTarjeta").setAttribute("value", "");
     document.getElementById("txtCodDocumento").setAttribute("value", "");
 
  $('#table_visibleDoc').DataTable().destroy();
    var table = $('#table_visibleDoc').DataTable();
    table.clear().draw();
}
 

 function CargarNumeradorCobranza(){ 
    var listBox = document.getElementById("txtCodDocumento");
    listBox.options.length = 0; 
    $.ajax({
        type: "POST",
        url: 'ConsultaFormasPago.aspx/CargarNumeradorCobranza',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            if(response.d){
                var $dropdown = $("#txtCodDocumento");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.cdoc_tipo).text(this.cdsc_numer));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}
  

$(document).ready(function () {
    CargarMenu();
    ConsultaColumnas();

$("#ModalDatosPersonales").draggable();

 

  $("#MdFacturacion").draggable();

  $("#modalBuscarDoc").draggable();

     $("#modalConsultarClientes").draggable();
      
     CargarMesActual();
  CargarNumeradorCobranza();
   CargarCaja(); 
       document.getElementById("txtTipoTarjeta").setAttribute("value", "");
   document.getElementById("txtCodDocumento").setAttribute("value", "");
    inicar_menu_nivel3('Consulta de Cobranzas', '1_li_Ventas', '2_li_ConsultaVenta', '3_li_FormaPago', '0');
    
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

     $('#Opcion').val("TLista");
     
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

    $("#ThtableDetallado").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('Div_DetalladoExpor').innerHTML], {
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

  
 
     