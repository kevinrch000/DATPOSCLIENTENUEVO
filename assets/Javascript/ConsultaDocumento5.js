 

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
        Nombre = "Código de cliente";
        Estado = "Opcional";
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

function PasaDatosCodUsuario() {
    var fila = $("#tableVisibleConsulUsuario input[name=radiob]:checked").closest('tr');
    $('#txtUsuario').val($("#tableVisibleConsulUsuario")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarUsuarios() {
     $('#tableVisibleConsulUsuario').DataTable().destroy();
    $('#table_secundariaConsultarUsuario').DataTable().destroy();

    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/CargarListaUsuario');
 
  $('#hdd_numerofilas').val(obj.length);
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
 

function ModalBuscarDoc(row) {

    try { $('#modalBuscarDoc').modal('show'); } catch (e) { console.error('No se pudo abrir modalBuscarDoc:', e); }

    var id_cbinve = "";
//     $.ajax({
//        type: "POST",
//        url: 'ConsultaDocumento.aspx/ConsultaDatosDocRef',
//        data: '{id_cbfact: "' + row.id + '" }',  
//        contentType: "application/json; charset=utf-8",
//        dataType: "json",
//        async: false,
//        success: function (response) { 
//            if (response.d=="-1"){
//                MensajeFinSession();
//            }else{  
//                objfactura = response.d;   
//            }
//        },
//        error: function (xhr, status, error) {
//            alert(error);
//        }
//    });

    for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbfact ){  
             id_cbinve = objfactura[i].id_cbinve; 
        }
    } 
 
 $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/DatosReferencia',
        data: '{id_cbfact: "' + row.id + '", id_cbinve: "' + id_cbinve + '"}',
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
            $('#upDocumentoCombranza').text(response.d[15]); 
            $('#upComprobante').text(response.d[1]);
            
            $('#upFechaInve').text(response.d[16]); 
            $('#upCodTiendaInve').text(response.d[19]); 
            $('#upNomTiendaInve').text(response.d[20]);  
            $('#upCodAlmacenInve').text(response.d[6]); 
            $('#upNomAlmacenInve').text(response.d[7]); 
            $('#upCodVendedorInve').text(response.d[8]); 
            $('#upNomVendedorInve').text(response.d[9]); 
            $('#upCodClienteInve').text(response.d[10]); 
            $('#upNomClienteInve').text(response.d[11]); 
            $('#upTotalInve').text(response.d[14]);  
            $('#upComprobanteInve').text(response.d[15]); 

            $('#tb_observacion').text(response.d[26]);
            
           }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    $('#table_secundariaDetalleArticulo').DataTable().destroy();
  

     $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/ConsultaListArticulosPorId',
        data: '{id_cbfact: "' + row.id + '"  }',
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


    $('#tableDetalleCobranza').DataTable().destroy(); 

 $.ajax({
    type: "POST",
    url: 'ConsultaDocumento.aspx/ConsultaListImventarioPorId',
    data: '{id_cbfact: "' + id_cbinve + '"  }',
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

}
 
 
 function Imprimir(row){
 
 $.ajax({
    type: "POST",
    url: 'ConsultaDocumento.aspx/ConsultaPDF',
    data: '{id_cbfact: "' + row.id + '"  }',
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    async: false,
    success: function (response) { 
     
     if(response.d){
        var obj = response.d;
        if (obj[0].impresion == ""){
            Mensaje('Advertencia','El documento pdf no se ha encontrado o esta dañado.','warning');
        }else{
            var objbuilder = '';
            objbuilder += ('<object width="100%" height="100%"  data="data:application/pdf;base64,');
            objbuilder += (obj[0].impresion);
            objbuilder += ('" type="application/pdf" class="internal">');
            objbuilder += ('</object>');
            var win = window.open();
            win.document.title = "My Title";
            win.document.write('<html><body>');
            win.document.write(objbuilder);
            win.document.write('</body></html>');
        }
//        var objbuilder = '';
//    objbuilder += ('<object width="100%" height="100%"  data="data:application/pdf;base64,');
//    objbuilder += (obj[0].impresion);
//    objbuilder += ('" type="application/pdf" class="internal">');
//    objbuilder += ('<embed src="data:application/pdf;base64,');
//    objbuilder += (obj[0].impresion);
//    objbuilder += ('" type="application/pdf" />');
//    objbuilder += ('</object>');
//var win = window.open();
//        win.document.title = "My Title";
//        win.document.write('<html><body>');
//        win.document.write(objbuilder);
//        win.document.write('</body></html>');
//        win.print()
         

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

   

     var obj = [  {
        "cdoc": $('#txtCodDocumento').val(),
        "cdoc_serie": $('#txtSerieDoc').val(),
        "cdoc_nro": $('#txtNroDoc').val(),
        "ccod_coa": $('#txtCliente').val(),
        "n_fchDesde": $('#txtfchDesde').val(),
        "n_fchHasta": $('#txtfchHasta').val(), 
        "ccod_tienda": $('#txtTienda').val(),
        "cusu_crea": $('#txtUsuario').val(),
        "cobs": $('#txtcobs').val(),
        "cobser_variante": $('#txtVariante').val(),
        "Opcion": $('#Opcion').val()
    } ] 
     
     if ($('#Opcion').val() == "TLista"){
     $('#table_principalDoc').DataTable().destroy();
    $('#table_visibleDoc').DataTable().destroy();
     } else {
     $('#table_visibleDocDetallado').DataTable().destroy(); 
     $('#table_DetalladoExpor').DataTable().destroy(); 
     }
   


    $.ajax({
        type: "POST",
        url: 'ConsultaDocumento.aspx/ConsultasDocumentoPricipal',
        data: JSON.stringify({ consultadocumentos: obj }),  
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
                    data: objfactura,
                      "ordering": false,
                    columns: [
                    { data: 'cdoc' },
                    { data: 'cdoc_serie' },
                    { data: 'cdoc_nro' },
                    { data: 'cusu_crea' },
                    { data: 'ccoa_dsc' },
                    { data: 'cdsc_tienda' },
                    { data: 'ntotal',className: "dt-body-right" },
                    { data: 'dfch_doc',className: "dt-body-right" },
                    { data: 'cstatus',className: "dt-body-center" },
                    { data: 'DocFact', className: "dt-body-center" },
                    { data: 'impresion', className: "dt-body-center" },
                    { data: 'ArmarHtml', className: "dt-body-center" } ] 
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
                    { data: 'cusu_crea' },
                    { data: 'ccoa_dsc' },
                    { data: 'ctelf' },
                    { data: 'cdsc_tienda' },
                    { data: 'ntotal',className: "dt-body-right" },
                    { data: 'dfch_doc',className: "dt-body-right" },
                    { data: 'cstatus',className: "dt-body-center" },
                    { data: 'cobs' } ],
                        scrollX: "2000px",
                    scrollCollapse: true
                }); 


                
                $('#txtITotalP').val('');

                 if(objfactura.length>0){ 
                    $.ajax({  
                        type: "POST",
                        url: 'ConsultaDocumento.aspx/DatosAdicionales',
                        data: JSON.stringify({ FormaPago: objfactura }),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        async: false,
                        success: function (response) {
                            var  objDA = response.d;
                            $('#txtITotalP').val(objDA.ntotal);
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
                    { data: 'cdsc_articulo' },
                    { data: 'ncantidad' },
                    { data: 'nprecio' },
                    { data: 'nimpuesto' },
                    { data: 'nimporte_neto' }, 
                    { data: 'dfch_doc' }, 
                    { data: 'cobser_variante' } ] 
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
                    { data: 'cusu_crea' },
                    { data: 'ccoa_dsc' },
                    { data: 'ctelf' },
                    { data: 'ccod_articulo' },
                    { data: 'cdsc_articulo' }, 
                    { data: 'ncantidad' },
                    { data: 'nprecio' },
                    { data: 'ndescuento' },
                    { data: 'nimpuesto' },
                    { data: 'nimporte_bruto' }, 
                    { data: 'nimporte_neto' },
                    { data: 'dfch_doc' },
                    { data: 'cobs' }, 
                    { data: 'cobser_variante' }  ],
                        scrollX: "2000px",
                    scrollCollapse: true
                }); 


                $('#txtITotalD').val('');
                if(objfactura.length>0){ 
                    $.ajax({  
                        type: "POST",
                        url: 'ConsultaDocumento.aspx/DatosAdicionales3',
                        data: JSON.stringify({ FormaPago: objfactura }),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        async: false,
                        success: function (response) {
                            var  objDA = response.d; 
                            $('#txtITotalD').val(objDA.ntotal); 
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
    $('#txtSerieDoc').val('');
    $('#txtNroDoc').val('');
    $('#txtUsuario').val('');
    $('#txtCliente').val('');
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
         

        if ($('#Opcion').val() == "TLista"){
            $('#table_principalDoc').DataTable().destroy();
            $('#table_visibleDoc').DataTable().destroy();
            var table = $('#table_visibleDoc').DataTable();
            table.clear().draw();

        } else {
            $('#table_visibleDocDetallado').DataTable().destroy(); 
            $('#table_DetalladoExpor').DataTable().destroy(); 
            var table = $('#table_visibleDocDetallado').DataTable();
            table.clear().draw();
        }

   
     

}
 


function ArmarHtml(idfact) {

    $("#zona-imprimir").append('<head><link href="/Styles/css/bootstrap.css" rel="stylesheet" type="text/css"></head>'); //va aqui para no causar conflicto con el bootstrap ya declarado, luego se borrara
    $("#nombre_empresa1")[0].innerText = ($('#hhd_empresa').val()).trim();
    $("#ruc_empresa")[0].innerText = "RUC: " + ($('#hdd_ruc').val()).trim();
    $("#direccion_empresa")[0].innerText = ($('#hhd_direccionE').val()).trim();
    $("#direccionubigeo_empresa")[0].innerText = ($('#hhd_ubigeoE').val()).trim();
    var nrodocumento = "";
    $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/DatosReferencia',
        data: '{id_cbfact: "' + idfact.id + '", id_cbinve: "' + '0' + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                $('#DivFechaVencimiento').text(": " + response.d[2]);
                $('#DivFechaEmision').text(": " + response.d[2]);
                $('#DivSenor').text(": " + response.d[11]);
                $('#DivrRuc').text(": " + response.d[27]);
                $('#DivDireccion').text(": " + response.d[28]);
                $("#son_documento")[0].innerText = "Son: " + NumeroALetras(response.d[3]) + " " + $("#lNomMoneda").text();
                $('#DivTotal').text(response.d[3]);
                $('#DivSubTotal').text(response.d[29]);
                $('#DivIGV').text(response.d[30]);

                if ((response.d[27]).trim().length == 11){ 
                    TipDoc = "6";
                }else if ((response.d[27]).trim().length == 8){ 
                    TipDoc = "1"; 
                }else { 
                    TipDoc = "-";
                }

                $('#DicSerieNro').text(response.d[31] + " - " + response.d[32]);
nrodocumento = (response.d[33]).trim() + "|" + (response.d[31]).trim() + "|" + (response.d[32]).trim() + "|" + (response.d[30]).trim() + "|" + (response.d[3]).trim() + "|" + (response.d[2]).trim() + "|" + TipDoc + "|" + (response.d[27]).trim();
                if(response.d[33] == "FV"){
                    $('#DicDoc').text("FACTURA ELECTRONICA");
                } else if(response.d[33] == "BV"){
                    $('#DicDoc').text("BOLETA ELECTRONICA");
                } else {
                    $('#DicDoc').text("NOTA DE VENTA");
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
        data: '{id_cbfact: "' + idfact.id + '"  }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var obj = response.d;
                $("#Table1 > tbody").html("");
                for (var i = 0; i < obj.length; i++) {
                    $("#Table1").find('tbody')
                .append($('<tr>')
                 .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ncantidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nprecio + '</td>')) 
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
 
    var qr = qrcode(6, "L");

    var cadena_qr = "";

    cadena_qr = ($('#hdd_ruc').val()).trim() + "|" + nrodocumento ;

//    switch ($('input:radio[name=tipo]:checked').val()) {
//        case "FV": cadena_qr = cadena_qr + "01" + "|"; break;
//        case "BV": cadena_qr = cadena_qr + "03" + "|"; break;
//        case "NV": cadena_qr = cadena_qr + "07" + "|"; break;
//    }

//    var doc = ((($('#lb_doc').text()).replace(' ', '|')).split("|", 2)[1]).replace(' ', '|');

//    cadena_qr = cadena_qr + 'FV' + "|" + '58' + "|" + '258' + "|" + '20' + "|" +
//    'FSV' + "|" + '2545856252' + "|";

    qr.addData(cadena_qr);
    qr.make();
    document.getElementById("qrcode").innerHTML = qr.createImgTag();


     $('#zona-imprimir').show();

    $objetivo = document.querySelector("#zona-imprimir");

    $contenedorCanvas = document.querySelector("#ponercanvas");

    html2canvas($objetivo)
    .then(canvas => {
        $contenedorCanvas.appendChild(canvas);
        GrabarPDF();
    });


}
        
function GrabarPDF() {

    $contenedorCanvas = document.querySelector("#ponercanvas");
    var canvas = $contenedorCanvas.children[0];

    var w = canvas.width;
    var h = canvas.height;

//    var k = (96/72);

    var imgData=canvas.toDataURL("image/jpeg", 1.0);
    
//    var doc = new jsPDF('p', 'px', [w*k, h*k]);         
    var doc = new jsPDF('l', 'pt', 'letter');
    doc.addImage(imgData,'JPEG',0,0,w,h);
//    $('#zona-imprimir').hide();
    $("#ponercanvas").html("");
//    doc.save('sample-file.pdf'); 
  var string = doc.output('datauristring');
 
  var objbuilder = '';
            objbuilder += ('<object width="100%" height="100%"  data="');
            objbuilder += (string);
            objbuilder += ('" type="application/pdf" class="internal">');
            objbuilder += ('</object>');
            var win = window.open();
            win.document.title = "My Title";
            win.document.write('<html><body>');
            win.document.write(objbuilder);
            win.document.write('</body></html>');
    
}
 
$(document).ready(function () {
    CargarMenu(); 
    ConsultaColumnas(); 
    $("#ModalCobranza").draggable();
    $("#modalConsultarClientes").draggable();
    $("#modalBuscarDoc").draggable();
    $("#modalBuscarCodInve").draggable(); 
    $("#ModalDatosPersonales").draggable();

  CargarTienda();  
  CargarMesActual();

  document.getElementById("txtCodDocumento").setAttribute("value", "Código Doc.*");
 
   inicar_menu_nivel3('Consulta de Documento de Ventas', '1_li_Ventas', '2_li_ConsultaVenta', '3_li_Documento', '0');
   
   
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

     var objLogo = llenarobjeto('../Interfaces/Home.aspx/CargarFotoUsuario');
    if (objLogo.length > 0) {
        document.getElementById("idlogoTicket").src = "data:image/png;base64," + objLogo[0].ilogo;
    }

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
