var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

function FinalizarResumenDoc(){

    if( $('#ckb_Imprimir').prop('checked') ) {
        ArmarHtml();
        Imprimir();
    }
}

function ArmarHtml() {

    $("#zona-imprimir").append('<head><link href="/Styles/css/bootstrap.css" rel="stylesheet" type="text/css"></head>');

    var obj = llenarobjeto('Facturacion.aspx/ConsultarTienda');

    $("#nombre_empresa1")[0].innerText = ($('#hhd_empresa').val()).trim();
    $("#direccion_empresa")[0].innerText = ($('#hhd_direccionE').val()).trim();
    $("#direccionubigeo_empresa")[0].innerText = ($('#hhd_ubigeoE').val()).trim();
    $("#ruc_empresa")[0].innerText = "Ruc: "+($('#hdd_ruc').val()).trim();
    $("#telefono_tienda")[0].innerText = "Telf: "+($('#hdd_telefono_tienda').val()).trim();
    $("#nombre_tienda")[0].innerText = "Tienda: "+($('#hdd_nombre_tienda').val()).trim();
    $("#direccion_tienda")[0].innerText = "Direc.: "+(obj[0].cdirec).trim();
    $("#ubigeo_tienda")[0].innerText = ($('#hdd_ubigeo_tienda').val()).trim();

    $("#nombre_documento")[0].innerText = "NOTA DE DÉBITO ELECTRÓNICA";
    //$("#codigo_documento")[0].innerText = $('#lb_numdoc').text();
    $("#codigo_documento")[0].innerText = $('#lb_numdoc').text().substring($('#lb_numdoc').text().split(' ',1).length+2);

    var fecha = new Date();
    var dia = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear();
    var hora = fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds();

    $("#fecha_documento")[0].innerText = "Fecha: " + dia;
    $("#hora_documento")[0].innerText = "Hora: " + hora;

    var objCabecera;

    $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/ConsultarDocumentoCabecera',
        data: '{id_cbfact: "' + $('#idfact').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){
                objCabecera = response.d;
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    if($("#TipDoc").val()=='FV') {
        $("#ruc_cliente")[0].innerText = "Ruc: " + (objCabecera[0].cruc_coa).trim();
    }
    else {
        $("#ruc_cliente")[0].innerText = "DNI: " + (objCabecera[0].cdoc_coa).trim();
    }

    $("#nombre_cliente")[0].innerText = "Cliente: " + (objCabecera[0].ccoa_dsc).trim();
    $("#direccion_cliente")[0].innerText = "Direc.: " + (objCabecera[0].cdir_coa).trim();
    
//    var objDetalle;

//    $.ajax({
//        type: "POST",
//        url: 'NotaCredito.aspx/ConsultarDocumentoDetalle',
//        data: '{id_cbfact: "' + $('#idfact').val() + '" }',
//        contentType: "application/json; charset=utf-8",
//        dataType: "json",
//        async: false,

//        success: function (response) {

//            if(response.d){
//                objDetalle = response.d;
//            }
//            else MensajeFinSession();
//        },
//        error: function (xhr, status, error) {
//            alert(error);
//        }
//    });

    $("#div_articlosdocumento").html("");

    var div_string = "";

    div_string = div_string + "<div class='col-xs-12'>Aumento en el valor " + $('#lb_total').text() + "</div>";
    div_string = div_string + 
    "<div style='text-align: center;'>"+
        "<div class='col-xs-3'></div>"+
        "<div class='col-xs-3'>1</div>"+
        "<div class='col-xs-3' style='text-align: right;'>" + $('#lb_total').text() + "</div>"+
        "<div class='col-xs-3' style='text-align: right;'>" + $('#lb_total').text() + "</div>"+
    "</div>";

    $("#div_articlosdocumento").append($(div_string));

//    if (objDetalle.length > 0) {

//        $.each(objDetalle, function (index) {
//            div_string = div_string + "<div class='col-xs-12'>" + objDetalle[index].cdsc_articulo + "</div>";
//            div_string = div_string + 
//            "<div style='text-align: center;'>"+
//                "<div class='col-xs-3'></div>"+
//                "<div class='col-xs-3'>" + objDetalle[index].ncantidad + "</div>"+
//                "<div class='col-xs-3' style='text-align: right;'>" + (objDetalle[index].nprecio).replace(',', '.') + "</div>"+
//                "<div class='col-xs-3' style='text-align: right;'>" + (objDetalle[index].nimporte_bruto).replace(',', '.') + "</div>"+
//            "</div>"
//        });

//        $("#div_articlosdocumento").append($(div_string));
//    }
//    else{
//        $("#div_articlosdocumento").html("");
//    }

    var objimpuesto

    $.ajax({
        type: "POST",
        url: 'Facturacion.aspx/ConsultarImpuestos',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){
                objimpuesto = response.d;
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    $("#opgrabada_documento")[0].innerText = (parseFloat($("#lb_total").text())*100/(parseFloat(objimpuesto[0].nigv)+100)).toFixed(2);
    $("#igv_documento")[0].innerText = (parseFloat($('#lb_total').text())-parseFloat($("#lb_total").text())*100/118).toFixed(2);
    $("#isc_documento")[0].innerText = "0.00";
    $("#total_documento")[0].innerText = $('#lb_total').text();
    $("#son_documento")[0].innerText = "Son: " + NumeroALetras($('#lb_total').text());
    
//    var objCobranzaDetalle;

//    $.ajax({
//        type: "POST",
//        url: 'NotaCredito.aspx/ConsultarDocumentoCobranza',
//        data: '{id_cbfact: "' + $('#idfact').val() + '" }',
//        contentType: "application/json; charset=utf-8",
//        dataType: "json",
//        async: false,

//        success: function (response) {

//            if(response.d){
//                objCobranzaDetalle = response.d;
//            }
//            else MensajeFinSession();
//        },
//        error: function (xhr, status, error) {
//            alert(error);
//        }
//    });

    $("#div_cobranzadocumento").html("");

    div_string = "";

    div_string = div_string + 
    "<div style='text-align: center;'>"+
        "<div class='col-xs-4' style='text-align: left;'>Nota de débito</div>"+
        "<div class='col-xs-4'>S/</div>"+
        "<div class='col-xs-4' style='text-align: right;'>" + $('#lb_total').text() + "</div>"+
    "</div>";

    $("#div_cobranzadocumento").append($(div_string));

//    if (objCobranzaDetalle.length > 0) {

//        $.each(objCobranzaDetalle, function (index) {
//            div_string = div_string + 
//            "<div style='text-align: center;'>"+
//                "<div class='col-xs-4' style='text-align: left;'>" + objCobranzaDetalle[index].cnom_tarje + "</div>"+
//                "<div class='col-xs-4'>S/</div>"+
//                "<div class='col-xs-4' style='text-align: right;'>" + (objCobranzaDetalle[index].nmonto).replace(',', '.') + "</div>"+
//            "</div>"
//        });

//        $("#div_cobranzadocumento").append($(div_string));
//    }
//    else{
//        $("#div_articlosdocumento").html("");
//    }

    $("#vendedor")[0].innerText = "Vend.: "+($('#nombre_usuario')[0].innerText).trim();
    $("#codigo_caja")[0].innerText = "Caja: "+($('#td_caja')[0].innerText).trim();

    var qr = qrcode(6, "L");

    var cadena_qr = "";

    cadena_qr = ($('#hdd_ruc').val()).trim()+"|07|";

    var doc = ((($('#lb_numdoc').text()).replace(' ', '|')).split("|",2)[1]).replace(' ', '|');

    cadena_qr = cadena_qr + doc +"|" + objCabecera[0].nimpuesto + "|"+ objCabecera[0].ntotal +"|"+dia+"|"+
    (objCabecera[0].ctip_doc).trim()+"|"+(objCabecera[0].cdoc_coa).trim()+"|";

    qr.addData(cadena_qr);
    qr.make();
    document.getElementById("qrcode").innerHTML = qr.createImgTag(); 

}

function Imprimir(){

    var mywindow = window.open();
    mywindow.document.write(document.getElementById("zona-imprimir").innerHTML);
    setTimeout(function () { mywindow.print(); }, 500);//espera a que cargue el logo
    setTimeout(function () { mywindow.close(); }, 2000);//cierra la impresion, tiene que ser a 2000 milisegundos 

    $("#nombre_documento").val("");
    $("#codigo_documento").val("");
    $("#fecha_documento").val("");
    $("#hora_documento").val("");
    $("#nombre_cliente").val("");
    $("#direccion_cliente").val("");
    $("#ruc_cliente").val("");    
    $("#div_articlosdocumento").html("");    
    $("#opgrabada_documento").val("");
    $("#igv_documento").val("");
    $("#isc_documento").val("");
    $("#total_documento").val("");
    $("#son_documento").val("");    
    $("#div_cobranzadocumento").html(""); 
    $("#vendedor").val("");
    $("#codigo_caja").val("");
    $("#qrcode").val("");        
    
}

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "tb_doc"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc";
        Nombre = "Código de documentación";
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
    }else if($("#NombreColumna").val() == "tb_serie"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc_serie";
        Nombre = "Serie de documentación";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_numero"){
        DscTabla = "fa_cbfact";
        DscColumna = "cdoc_nro";
        Nombre = "Numerador de documentación";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_cliente"){
        DscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Código del cliente";
        Estado = "Obligatorio";
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
    } else if ($('#tb_doc').val() == "") {
        Mensaje('Advertencia', 'Ingresar código documento.', 'warning');
        return;
    }
     
    var obj = [  {
        "cdoc": $('#tb_doc').val(),
        "cdoc_serie": $('#tb_serie').val(),
        "cdoc_nro": $('#tb_numero').val(),
        "ccod_coa": $('#tb_cliente').val(),
        "n_fchDesde": $('#txtfchDesde').val(),
        "n_fchHasta": $('#txtfchHasta').val(), 
        "ccod_tienda": $('#txtTienda').val()
    } ] 

    $.ajax({
        type: "POST",
        url: 'NotaDebito.aspx/ConsultarDocumentos',
        data: JSON.stringify({ notacredito: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {

            if (response.d == "-1") MensajeFinSession(); 
            else { 
            objfactura = response.d;   
                $('#table_id').DataTable().destroy();
                $('#table_principalDoc').DataTable().destroy();
                $('#table_id').DataTable({
                    "ordering": false,
                    data: objfactura,
                    columns: [
                        { data: 'cdoc' },
                        { data: 'cdoc_serie' },
                        { data: 'cdoc_nro' },
                        { data: 'cdoc_coa' },
                        { data: 'ccoa_dsc' },
                        { data: 'ntotal', className: "dt-body-right" },
                        { data: 'dfch_doc', className: "dt-body-right" }, 
                        { data: 'ccod_alm', className: "dt-body-center"},
                        { data: 'DocFact', className: "dt-body-center"},
                        { data: 'NotaDebito', className: "dt-body-center"} 
                    ] 
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
                    { data: 'ccoa_dsc' },
                    { data: 'ntotal', className: "dt-body-right" },
                    { data: 'dfch_doc', className: "dt-body-right" },
                    { data: 'ccod_alm' } ],
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

function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#tb_cliente').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();

    var obj = llenarobjeto('NotaDebito.aspx/CargarCliente');
 
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
 

 

function ModalDocFac(row) {
 
   for (var i = 0; i < objfactura.length; i++) {

        if(row.id == objfactura[i].id_cbfact ){
            $('#upFecha').text(objfactura[i].dfch_doc);
            $('#CobranFecha').text(objfactura[i].dfch_doc);
             
            $('#upComprobante').text(objfactura[i].Doc);
            $('#upTotal').text(objfactura[i].ntotal);
            $('#upImpTotCobraza').text(objfactura[i].ntotal);
            $('#upCodTienda').text(objfactura[i].ccod_tienda); 
            $('#upNomTienda').text(objfactura[i].cdsc_tienda);  
            $('#CobranCodTienda').text(objfactura[i].ccod_tienda); 
            $('#CobranNomTienda').text(objfactura[i].cdsc_tienda); 
            $('#upCodCaja').text(objfactura[i].ccod_caja); 
            $('#upNomCaja').text(objfactura[i].cdsc_caja);  
            $('#CobranCodCaja').text(objfactura[i].ccod_caja); 
            $('#CobranNomCaja').text(objfactura[i].cdsc_caja);   
            $('#upCodVendedor').text(objfactura[i].cusu_crea); 
            $('#upNomVendedor').text(objfactura[i].cdsc_usuario); 
            $('#CobranCodVendedor').text(objfactura[i].cusu_crea); 
            $('#CobranNomVendedor').text(objfactura[i].cdsc_usuario); 
            $('#upCodCliente').text(objfactura[i].ccod_coa); 
            $('#upNomCliente').text(objfactura[i].ccoa_dsc);  
            $('#CobranCodCliente').text(objfactura[i].ccod_coa); 
            $('#CobranNomCliente').text(objfactura[i].ccoa_dsc);  

            $('#CobranDocumentoFac').text(objfactura[i].cdocCobr); 
            $('#upDocumentoCombranza').text(objfactura[i].cdocCobr);  
             
//            $('#upCodAlmacen').text(objfactura[i].ccod_alm); 
//            $('#upNomAlmacen').text(objfactura[i].cdsc_alm);  
             
            $('#upTotalEntregado').text(objfactura[i].ntot_entreg); 
            $('#upVuelto').text(objfactura[i].nvuelto); 
        }

    }
      
    $('#tbArticulo').DataTable().destroy();
    $('#table_secundariaDetalleArticulo').DataTable().destroy();
  
     $.ajax({
        type: "POST",
        url: 'NotaDebito.aspx/ConsultaListArticulosPorId',
        data: '{id_cbfact: "' + row.id + '" }',
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
             }
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });

     $.ajax({
        type: "POST",
        url: 'NotaDebito.aspx/ConsultaListCobranzaPorId',
        data: '{id_cbfact: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
         if(response.d){
            obj = response.d;
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

} 

function Limpiar() {
    $('#tb_doc').val('');
    $('#tb_serie').val('');
    $('#tb_numero').val('');

    $('#txtTienda').val('');
    $('#tb_cliente').val('');
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
        
   document.getElementById("tb_doc").setAttribute("value", "");
   document.getElementById("txtTienda").setAttribute("value", "");
    $('#table_id').DataTable().destroy(); 
    var table = $('#table_id').DataTable();
    table.clear().draw();
     
}


function ModalGenerarNotaDebito(row) {
  $('#txtMonto').val('');
  $('#TipDoc').val(''); 
  
  for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbfact ){ 
            $('#TipDoc').val(objfactura[i].ctip_doc);
            $('#txtDocRefNotaDebito').text("Nota de Debito del Documento : "+objfactura[i].cdoc+" "+objfactura[i].cdoc_serie+" "+objfactura[i].cdoc_nro);  
            $('#idfact').val(row.id);         
        }
    }
} 

function GenerarNotaDebito() {

    if ($('#txtMonto').val() ==null) {
        Mensaje('Advertencia', 'Ingrese monto de Nota de Débito.', 'warning');
        return; 
    } 

    var obj = [
        {
            "id_cbfact": $('#idfact').val(),
            "nimp_aplicado": $('#txtMonto').val()
        }
    ]

    Swal.fire({
        title: "¿Desea generar una Nota de Débito?",
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Aceptar',
        showCancelButton: true,
        cancelButtonColor: '#f7505a',
        cancelButtonText: "Cancelar"
    }).then(
        (result) => {

        if (result.isConfirmed) {
       
    $.ajax({
        type: "POST",
        url: 'NotaDebito.aspx/GenerarNotaDebito',
        data: JSON.stringify({ notadebito: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d=="-1") MensajeFinSession();
                else{
                var obje = response.d;
                    if(obje[0].Doc == 'OK'){ 
                          
                        $("#modalNotaDebito").modal('hide');  
                        $('#lb_numdoc').text(obje[0].cdoc+' '+obje[0].cdoc_serie+' '+obje[0].cdoc_nro); 
                        $('#lb_total').text(parseFloat(($('#txtMonto').val()).replace(',', '.')).toFixed(2)); 
                        $('#lb_entregado').text(parseFloat(($('#txtMonto').val()).replace(',', '.')).toFixed(2)); 
                        $('#modalResumenVenta').modal('show');  
                    }else if(obje[0].Doc == 'CodOperND'){ 
                        Mensaje('Error','El código del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración del código del documento de nota de débito  al administrador.','error'); 
                    }else if(obje[0].Doc == 'SerOperND'){ 
                        Mensaje('Error','La serie del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de nota de débito  al administrador.','error'); 
                    }else if(obje[0].Doc == 'NroOperND'){ 
                        Mensaje('Error','El correlativo del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de nota de débito  al administrador.','error');
                    }else if(obje[0].Doc == 'CodOperCobr'){ 
                        Mensaje('Error','El código del documento de cobranza no esta asignado o no es valido.\n\n Solicite configuración del código del documento de cobranza al administrador.','error'); 
                    }else if(obje[0].Doc == 'SerOperCobr'){ 
                        Mensaje('Error','La serie del documento de cobranza no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de cobranza al administrador.','error'); 
                    }else if(obje[0].Doc == 'NroOperCobr'){ 
                        Mensaje('Error','El correlativo del documento de cobranza no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de cobranza al administrador.','error');
                    }else if(obje[0].Doc == 'CodCaja'){ 
                        Mensaje('Error','El código de caja del usuario no esta asignado o no es valido.\n\n Solicite configuración del código de caja al administrador.','error');
                    }else if(obje[0].Doc == 'CodTie'){ 
                        Mensaje('Error','El código de tienda del usuario no esta asignado o no es valido.\n\n Solicite configuración del código de tienda al administrador.','error');
                    }else if(obje[0].Doc == 'CodAlm'){ 
                        Mensaje('Error','El código de almacén del usuario no esta asignado o no es valido.\n\n Solicite configuración del código de almacén al administrador.','error');
                    }else if(obje[0].Doc == 'IdCaja'){ 
                        Mensaje('Error','La caja no esta apeturada.','error'); 
                    }else if(obje[0].Doc == 'ErrorDocFactura'){ 
                        Mensaje('Error',obje[0].cdoc,'error'); 
                    }else if(obje[0].Doc == 'ErrorDocCobranza'){ 
                        Mensaje('Error',obje[0].cdoc,'error'); 
                    }
                }  
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });
      
    }
  });
}

$(document).ready(function () {

    CargarMenu();
    CargarMesActual();
    ConsultaColumnas();
      CargarTienda(); 
    document.getElementById("tb_doc").setAttribute("value", "");
    inicar_menu_nivel3('Nota de Debito', '1_li_Ventas', '2_li_Ventas_Operaciones', '3_li_NotaDebito', '0');

    // FIX BUG 3.2.7: habilitar botón Nuevo al cargar
    $('#btn_p_nuevo').removeClass("botones_des").addClass("botones_hab");

    traducir_tabla();
    $('#table_id').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });
     

     $(function(){ $.switcher('input[type=checkbox]'); });

    $("#modalAnulacion").draggable();
    $("#modalDescuento").draggable();
    $("#modalResumenVenta").draggable();
    $("#ModalCobranza").draggable();

    $("#modalConsultarClientes").draggable(); 
    $("#modalBuscarDoc").draggable();
    $("#ModalDatosPersonales").draggable(); 
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
 