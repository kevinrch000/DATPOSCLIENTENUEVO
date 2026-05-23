var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

jQuery(function($) {
	$('.radio-toggle').toggleInput();
});

function FinalizarResumenDoc(){

    if( $('#ckb_Imprimir').prop('checked') ) {
        ArmarHtml();
        HtmlPdf(); 
        Imprimir();
    }
    
//    $('#modalResumenVenta').modal('hide');

//    Limpiar();

}

 function ImprimirPDF(row){
  
 $.ajax({
    type: "POST",
    url: '../Consultas/ConsultaDocumento.aspx/ConsultaPDF',
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

        }
    },
    error: function (xhr, status, error) {
        alert(error);
    }
    });

 
     
}

function ArmarHtml() {
    Swal.fire({
        title: 'Advertencia',
        text: 'Por favor, no cierre el navegador se esta guardando el pdf.',
        icon: 'warning',
        showConfirmButton: false
    });

     
    $("#zona-imprimir").append('<head><link href="/Styles/css/bootstrap.css" rel="stylesheet" type="text/css"></head>');

    $("#nombre_empresa1")[0].innerText = ($('#hhd_empresa').val()).trim();
    $("#direccion_empresa")[0].innerText = ($('#hhd_direccionE').val()).trim();
    $("#direccionubigeo_empresa")[0].innerText = ($('#hhd_ubigeoE').val()).trim();
    $("#ruc_empresa")[0].innerText = "Ruc: "+($('#hdd_ruc').val()).trim();
    $("#telefono_tienda")[0].innerText = "Telf: "+($('#hdd_telefono_tienda').val()).trim();
    $("#nombre_tienda")[0].innerText = "Tienda: "+($('#hdd_nombre_tienda').val()).trim(); 
    $("#ubigeo_tienda")[0].innerText = ($('#hdd_ubigeo_tienda').val()).trim();

     
    if($("#ddl_tip_nota").val()=='02') {
        $("#nombre_documento")[0].innerText = "NOTA DE DEBITO ELECTRONICA";
        $("#nomTotal")[0].innerText = "TOTAL";
    }else{
        $("#nombre_documento")[0].innerText = "NOTA DE CREDITO ELECTRONICA";
        $("#nomTotal")[0].innerText = "NOTA DE DEBITO ELECTRONICA";
        $("#nomTotal")[0].innerText = "CREDITO TOTAL";
    } 

    $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/DetalleNotaCredito',
        data: '{id_cbfact: "' + $('#hhd_id_nc').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            if(response.d){
               $("#direccion_tienda")[0].innerText = "Direc.: "+(response.d[1]).trim();
               $("#codigo_documento")[0].innerText = response.d[2] + " " + response.d[3]
               $("#fecha_documento")[0].innerText = "Fecha: " + response.d[4];
               $("#hora_documento")[0].innerText = "Hora: " + response.d[5];
               $("#nombre_cliente")[0].innerText = "Cliente: " + response.d[6];
               $("#direccion_cliente")[0].innerText = "Direc.: " + response.d[7];
               var codigoclientes = '';
               if(response.d[13]=='FV') {
                    $("#ruc_cliente")[0].innerText = "RUC: " + response.d[8];
                    codigoclientes = '6';
               }else {
                    $("#ruc_cliente")[0].innerText = "DNI: " + response.d[8];
                    codigoclientes = '1';
               }
               $("#opgrabada_documento")[0].innerText = response.d[9];
               $("#igv_documento")[0].innerText = response.d[10];
               $("#isc_documento")[0].innerText = response.d[11];  
               $("#credito_monto")[0].innerText = response.d[12];
               $("#documento_referencia")[0].innerText = "Doc. Ref. " + response.d[14]+ " " +response.d[15];
               $("#son_documento")[0].innerText = "Son: " + NumeroALetras(response.d[12])+ " " + $("#lNomMoneda").text();
               $("#vendedor")[0].innerText = "Vend.: " + response.d[16];
               $("#codigo_caja")[0].innerText = "Caja: " + response.d[17];

               //   codigo qr
                var qr = qrcode(6, "L"); 
                var cadena_qr = ""; 
                cadena_qr = ($('#hdd_ruc').val()).trim()+"|"+ response.d[18] +"|"+ response.d[2] +"|"+ response.d[3] +"|"+ response.d[10] + "|"+ 
                response.d[12] +"|"+ response.d[4] +"|"+ codigoclientes +"|"+ response.d[8]; 
                qr.addData(cadena_qr);
                qr.make();
                document.getElementById("qrcode").innerHTML = qr.createImgTag(); 
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    if($('#ddl_tip_nota').val()=="05"){
//Detalle de los productos
        var objDetalle;
        $("#div_articlosdocumento").html("");
        $.ajax({
            type: "POST",
            url: 'NotaCredito.aspx/ConsultarDocumentoDetalle',
            data: '{id_cbfact: "' + $('#hhd_id_nc').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if(response.d){
                    objDetalle = response.d;
                }
                else MensajeFinSession();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

        var div_string = "";

        if (objDetalle.length > 0) {
            $.each(objDetalle, function (index) {
                div_string = div_string + "<div class='col-xs-12'>" + objDetalle[index].cdsc_articulo + "</div>";
                div_string = div_string + 
                "<div style='text-align: center;'>"+
                    "<div class='col-xs-3'></div>"+
                    "<div class='col-xs-3'>" + objDetalle[index].ncantidad + "</div>"+
                    "<div class='col-xs-3' style='text-align: right;'>" + (objDetalle[index].nprecio).replace(',', '.') + "</div>"+
                    "<div class='col-xs-3 montonc' style='text-align: right;'>" + (objDetalle[index].nimporte_bruto).replace(',', '.') + "</div>"+
                "</div>"
            });
            $("#div_articlosdocumento").append($(div_string));
        }
        else{
            $("#div_articlosdocumento").html("");
        }
//    }

    var objCobranzaDetalle;
    $("#div_cobranzadocumento").html("");
    $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/ConsultarDocumentoCobranza',
        data: '{id_cbfact: "' + $('#hhd_id_nc').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){
                objCobranzaDetalle = response.d;
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
    var div_cobranza = "";

    if (objCobranzaDetalle.length > 0) {
        $.each(objCobranzaDetalle, function (index) { 
            div_cobranza = div_cobranza + 
            "<div style='text-align: center;'>"+ 
                "<div class='col-xs-4'>" + objCobranzaDetalle[index].cnom_tarje + "</div>"+
                "<div class='col-xs-4' style='text-align: center;'>" + $("#lSimMoneda").text() + "</div>"+
                "<div class='col-xs-4' style='text-align: right;'>" + objCobranzaDetalle[index].nmonto + "</div>"+
            "</div>"
        });
        $("#div_cobranzadocumento").append($(div_cobranza));
    } else {
        $("#div_cobranzadocumento").html("");
    }
   


}

function HtmlPdf() {

    $('#zona-imprimir').show();

    $objetivo = document.querySelector("#zona-imprimir");

    $contenedorCanvas = document.querySelector("#ponercanvas");
    
    html2canvas($objetivo)
    .then(canvas => {
        $contenedorCanvas.appendChild(canvas);
        GrabarPDF();
           
    });
     
     
    $('#zona-imprimir').hide();
}

function GrabarPDF() {

    $contenedorCanvas = document.querySelector("#ponercanvas");
    var canvas = $contenedorCanvas.children[0];

    var w = canvas.width;
    var h = canvas.height;

    var k = (96/72);

    var imgData=canvas.toDataURL("image/jpeg", 1.0);
    
    var doc = new jsPDF('p', 'px', [w*k, h*k]);         
    
    doc.addImage(imgData,'JPEG',0,0,w,h);

//    doc.save('sample-file.pdf'); 
    var string = doc.output('datauristring');
    
 
    
    $.ajax({
        type: "POST",
        url: '../Ventas/Facturacion.aspx/RegistrarPdf',
        data: '{id_cbfact: "' + $('#hhd_id_nc').val() + '", pdf: "' + string+ '" }',

        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {
                $('#hhd_id_nc').val("");
            window.open((window.DATPOS_BASE_PATH||'')+"/pages/Ventas/NotaCredito.php","_self")
            }
 

            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
   
    $("#ponercanvas").html("");

}

function Imprimir(){

    var mywindow = window.open();
    mywindow.document.write(document.getElementById("zona-imprimir").innerHTML);
    setTimeout(function () { mywindow.print(); }, 500);//espera a que cargue el logo
    setTimeout(function () { mywindow.close(); }, 2000);//cierra la impresion, tiene que ser a 2000 milisegundos 
     
     
    $("#nombre_documento").val("");
    $("#nomTotal").val("");
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

 

function EjecutarNC() {

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
        url: 'NotaCredito.aspx/ConsultarDocumentosNotaCredito',
        data: JSON.stringify({ notacredito: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            
            if (response.d == "-1") MensajeFinSession(); 
            else { 
             
            objfactura = response.d;  
            $('#hdd_numerofilas').val(objfactura.length); 
                $('#table_id').DataTable().destroy();
                $('#table_principalDoc').DataTable().destroy();
                $('#table_id').DataTable({
                    "ordering": false,
                    data: objfactura,
                    columns: [
                        { data: 'DocFact', className: "dt-body-center"},
                        { data: 'cdoc' },
                        { data: 'cdoc_serie' },
                        { data: 'cdoc_nro' },
                        { data: 'cdoc_coa' },
                        { data: 'ccoa_dsc' },
                        { data: 'ntotal', className: "dt-body-right" },
                        { data: 'dfch_doc', className: "dt-body-right" }, 
                        { data: 'cdsc_usuario', className: "dt-body-center"},
                        { data: 'impresion', className: "dt-body-center"}
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
                    { data: 'cdsc_usuario' } ],
                    scrollX: "2000px",
                scrollCollapse: true
            });

            if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(objfactura[0].id_cbfact); 

  
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

    var obj = llenarobjeto('NotaCredito.aspx/CargarCliente');
 
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

 
var objListArticulos = [];
var objListArticulosAceptados = [];






function CkMarcarTodo() { 
     if ($('#idCkMarcarTodo').is(':checked') == true){  
        $('.limpiar_checked1').prop('checked', true);
        $("#idCkDesmarcarTodo").prop("checked",false); 
     } else {
        $("#idCkDesmarcarTodo").prop("checked",false); 
     } 
}

function CkDesmarcarTodo() { 
     if ($('#idCkDesmarcarTodo').is(':checked') == true){  
        $(".limpiar_checked1").removeAttr("checked");
        $("#idCkMarcarTodo").prop("checked",false); 
     } else {
       $("#idCkMarcarTodo").prop("checked",false); 
     } 
}


 

function ModalDescuento(row) {
 
     $('#cod_motivo').val('04');  
     $('#txtMontDescuento').val('');  
     $('#txtDscDescuento').val('');  
     $('#txtImportTot').val('');  
      
    $('#txtDocRefDescuento').text(''); 
    $('#idfact').val('');      
    $('#ntotal').val('');  
    $('#DocRef').val('');  
        $('#TipDoc').val(''); 
  

    for (var i = 0; i < objfactura.length; i++) { 
        if(row.id == objfactura[i].id_cbfact ){
            if("05" == objfactura[i].cod_motivo ){ 
                Mensaje('Advertencia','No se puede generar una Nota de Crédito por Descuento a este documento porque ya cuenta con una Nota de Crédito por Devolucion.','warning');
                return;
            }else if(0.00 >= parseFloat(objfactura[i].montodisponible).toFixed(2) ){ 
                Mensaje('Advertencia','No se puede generar una Nota de Crédito por Descuento a este documento porque ya no tiene saldo disponible.','warning');
                return;

            }
            $('#modalDescuento').modal('show');  
              
            $('#txtDocRefDescuento').text('Descuento del Documento : '+objfactura[i].Doc); 
            $('#idfact').val(row.id);    
            $('#ntotal').val(objfactura[i].ntotal); 
            $('#txtImportTot').val(objfactura[i].montodisponible); 
            $('#DocRef').val(objfactura[i].Doc);   
               $('#TipDoc').val(objfactura[i].ctip_doc); 
        }
    }

    $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/NCMontoRestante',
        data: '{id_cbfact: "' +  row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) {
            var obj = response.d;
               $('#txtImportTot').val(obj[0].ntotal); 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
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
        url: 'NotaCredito.aspx/ConsultaListArticulosPorId',
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
        url: 'NotaCredito.aspx/ConsultaListCobranzaPorId',
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

function LimpiarNC() {
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

$(document).ready(function () {

    CargarMenu();
    CargarMesActual();
    ConsultaColumnas();
      CargarTienda(); 
    document.getElementById("tb_doc").setAttribute("value", "Código Doc.*");
    inicar_menu_nivel3('Nota Crédito y Débito', '1_li_Ventas', '2_li_Ventas_Operaciones', '3_li_NotaCredito', '2');

    traducir_tabla(); 
    $('#table_id').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });

     
   
     $(function(){ $.switcher('input[type=checkbox]'); });

    $("#modalAnulacion").draggable();
    $("#modalDescuento").draggable();
      
     $("#ModalCobranza").draggable();
      $("#modalListaDeBienes").draggable();
      
    $("#modalConsultarClientes").draggable(); 
    $("#modalBuscarDoc").draggable();
    $("#ModalDatosPersonales").draggable();
     $("#modalResumenVenta").draggable();
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

    

});

 
function TipoOperacion() {
    if($("#ddl_tip_nota").val() == "07"){ 
    $(".limpiar").val("");
      $(".disabled").prop("disabled", true)
      $("#ddl_tip_nota").prop("disabled", false);
      $("#txt_cdoc").prop("disabled", false);
      $("#txt_cdoc_serie").prop("disabled", false);
      $("#txt_cdoc_nro").prop("disabled", false);  
    }else if($("#ddl_tip_nota").val() == "04"){
    $(".limpiar").val("");
      $(".disabled").prop("disabled", true)
      $("#ddl_tip_nota").prop("disabled", false);
      $("#txt_cdoc").prop("disabled", false);
      $("#txt_cdoc_serie").prop("disabled", false);
      $("#txt_cdoc_nro").prop("disabled", false); 
      $("#txt_nimp_aplicado").prop("disabled", false); 
      $("#txt_cdsc_movito").prop("disabled", false); 
    }else if($("#ddl_tip_nota").val() == "02"){
    $(".limpiar").val("");
     $(".disabled").prop("disabled", true)
      $("#ddl_tip_nota").prop("disabled", false);
      $("#txt_cdoc").prop("disabled", false);
      $("#txt_cdoc_serie").prop("disabled", false);
      $("#txt_cdoc_nro").prop("disabled", false);  
      $("#txt_nimp_aplicado").prop("disabled", false); 
    }
}

function LimpiarRef() {
 
   
    if($("#operacion").val()=="nuevo"){
        $(".limpiar").val("");
        document.getElementById("ddl_tip_nota").setAttribute("value", "");
    }
     
 
}

function EjecutarRef() {

  if($("#operacion").val()=="nuevo"){
    if ($('#txt_cdoc').val() == "") {
        Mensaje('Advertencia', 'Ingresar el código del documento de referencia de 2 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc_serie').val() == "") {
        Mensaje('Advertencia', 'Ingresar la serie del documento de referencia de 4 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc_nro').val() == "") {
        Mensaje('Advertencia', 'Ingresar el correlativo del documento de referencia de 8 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc').val().length != 2 && $('#txt_cdoc').val() != "") {
        Mensaje('Advertencia', 'Ingresar el código del documento de referencia de 2 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc_serie').val().length != 4 && $('#txt_cdoc_serie').val() != "") {
        Mensaje('Advertencia', 'Ingresar la serie del documento de referencia de 4 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc_nro').val().length != 8 && $('#txt_cdoc_nro').val() != "") {
        Mensaje('Advertencia', 'Ingresar el correlativo del documento de referencia de 8 digitos.', 'warning');
        return;
    }

     $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/BuscarDocRef',
        data: '{codigo: "' + $('#txt_cdoc').val() + '", serie: "' + $('#txt_cdoc_serie').val() + '", correlativo: "' + $('#txt_cdoc_nro').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) {
        var obje = response.d;
        if(obje.length > 0){
            if($("#ddl_tip_nota").val() == "07"){ 
                if(obje[0].cod_motivo == "04"){ 
                    Mensaje('Advertencia','No se puede generar una Nota de Crédito por Devolución a este documento porque ya cuenta con una Nota de Crédito por Descuento.','warning');
                    return;
                }else if(0.00 >= parseFloat(obje[0].montodisponible).toFixed(2) ){ 
                    Mensaje('Advertencia','No se puede generar una Nota de Crédito por Devolución a este documento porque ya no tiene saldo disponible.','warning');
                    return; 
                }
            }else if($("#ddl_tip_nota").val() == "04"){
                if(obje[0].cod_motivo == "05"){ 
                    Mensaje('Advertencia','No se puede generar una Nota de Crédito por Descuento a este documento porque ya cuenta con una Nota de Crédito por Devolucion.','warning');
                    return;
                }else if(0.00 >= parseFloat(obje[0].montodisponible).toFixed(2) ){ 
                    Mensaje('Advertencia','No se puede generar una Nota de Crédito por Descuento a este documento porque ya no tiene saldo disponible.','warning');
                    return; 
                }
            } 

             $('#txt_ntotal').val(obje[0].ntotal)
             $('#txt_dfch_crea').val(obje[0].dfch_crea)
             $('#txt_ccoa_dsc').val(obje[0].ccoa_dsc)
             $('#txt_ccod_coa').val(obje[0].ccod_coa)   
             CargarTablaDetalle(obje[0].id_cbfact,obje[0].id_cbinve);
             $('#idfact').val(obje[0].id_cbfact)
             $('#montodisponible').text(obje[0].montodisponible)
              
        }
                
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
 }
}
 

 
function Nuevo() {
    $('.nav-tabs li:eq(0) a').tab('show');
    $('#tbListArticulo > tbody').html('');
     
    $("#lb_codigo").val("");
      $(".disabled").prop("disabled", true)
      $("#ddl_tip_nota").prop("disabled", false);
    $(".limpiar").val("");
    $("#ddl_tip_nota").val("");
   document.getElementById("ddl_tip_nota").setAttribute("value", "");
    $("#operacion").val("nuevo");
    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");

     
}

function table_two_click(tbody) {

    $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', ''); 
    var index = tbody.ondblclick.arguments[0].target.parentElement.cells[0].firstChild.id; 
    $("#table_id tr:nth-child("+index+")").css('background', 'silver'); 
    $('#hdd_fila').val(index);
     
    var fila = tbody.ondblclick.arguments[0].target.parentElement.cells;
    $(".limpiar_checked").removeAttr("checked");
    $("#"+index).prop('checked', true);
   
    
   $('#hdd_ultimafila').val(index);
    
    if($('#hdd_numerofilas').val()>0){ 
        $.ajax({
            type: "POST",
            url: 'NotaCredito.aspx/ConsultarNotaCredito',
            data: '{codigo: "' + index + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

                    success: function (response) {
                        if (response.d) CompletarCampos(response.d);
                        else MensajeFinSession();
                    },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    }

    $('.nav-tabs li:eq(0) a').tab('show');
    Desabilitar();

    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");

    //CargarTablaNumerador(fila[0].innerText);se hace en CompletarCampos

//    $('#lb_codigo').text(fila[0].innerText + " - " + fila[1].innerText);
}

function CompletarCampos(obj){

    $("#txt_cdoc").val(obj[0].cdocFac);
    $("#txt_cdoc_serie").val(obj[0].cdoc_serieFac);
    $("#txt_cdoc_nro").val(obj[0].cdoc_nroFac);
    $("#txt_dfch_crea").val(obj[0].dfch_crea);
    $("#txt_ntotal").val(obj[0].ntotal);
    $("#txt_ccoa_dsc").val(obj[0].ccoa_dsc);
    $("#txt_ccod_coa").val(obj[0].ccod_coa);
    $("#txt_cdocFac").val(obj[0].cdoc);
    $("#txt_cdoc_serieFac").val(obj[0].cdoc_serie);
    $("#txt_cdoc_nroFac").val(obj[0].cdoc_nro);
    $("#txt_cdsc_movito").val(obj[0].cdsc_movito);
    $("#txt_nimp_aplicado").val(obj[0].nimp_aplicado);
    $("#txt_dfch_doc").val(obj[0].dfch_doc);
    $("#txt_cdsc_usuario").val(obj[0].cdsc_usuario);
    $("#idfact").val(obj[0].id_cbfact);
     

    (document.getElementById("ddl_tip_nota")).selectedIndex = 
    [...(document.getElementById("ddl_tip_nota")).options].findIndex(option => option.value === (obj[0].cod_motivo).toString());   
    
    if($("#ddl_tip_nota").val() == "07" || obj[0].cod_motivo == "05"){ 
        CargarTablaDetalle(obj[0].id_cbfact, obj[0].id_cbinve);
    }else {
         CargarTablaDetalleVer(obj[0].id_cbfact);
    }
     
     

    $('#lb_codigo').text(obj[0].cdoc + " " + obj[0].cdoc_serie+ " " + obj[0].cdoc_nro);

}

function CargarTablaDetalleVer(id_cbfact) {
 
$.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/ListaDeArticulo',
        data: '{id_cbfact: "' +  id_cbfact + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) {
            objListArticulos = response.d;
            $("#tbListArticulo > tbody").html("");
            for (var i = 0; i < objListArticulos.length; i++) {
            $("#tbListArticulo").find('tbody')
                .append($('<tr>')
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;text-align: center;" ><input class="limpiar_checked1" id="Radio'+objListArticulos[i].id_lnfact+'"  type="checkbox"   /></td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].ccod_articulo + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].cdsc_articulo + '</td>')) 
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" ><input id="Input'+objListArticulos[i].id_lnfact+'"  value="' + objListArticulos[i].ncantidad + '" type="number" min="0" class="limpiar form-control moderno_tb" placeholder=" "  /></td>')) 
                .append($('</tr>'))
                );  
            }
             
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });  

           
 
}

function CargarTablaDetalle(id_cbfact, id_cbinve) {
 
$.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/ListaDeBienes',
        data: '{id_cbfact: "' +  id_cbfact + '", id_cbinve: "' +  id_cbinve + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) {
            objListArticulos = response.d;
            $("#tbListArticulo > tbody").html("");
            for (var i = 0; i < objListArticulos.length; i++) {
            $("#tbListArticulo").find('tbody')
                .append($('<tr>')
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;text-align: center;" ><input class="limpiar_checked1" id="Radio'+objListArticulos[i].id_lnfact+'"  type="checkbox"   /></td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].ccod_articulo + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].cdsc_articulo + '</td>')) 
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" ><input id="Input'+objListArticulos[i].id_lnfact+'"  value="' + objListArticulos[i].ncantidad + '" type="number" min="0" class="limpiar form-control moderno_tb" placeholder=" "  /></td>')) 
                .append($('</tr>'))
                );  
            }
             
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });  

           
 
}
 
function checked_click(row) {
    $(".limpiar_checked").removeAttr("checked");
    $(row).prop('checked', true); 
    var currentRow = $(row).closest("tr"); 
    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(row.id);
        $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', ''); 
    $("#table_id tr:nth-child("+currentRow[0].rowIndex+")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);
 }
 

 function tab_datosclick() {
    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'NotaCredito.aspx/ConsultarNotaCredito',
                data: '{codigo: "' + $('#hdd_ultimafila').val() + '" }',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,

                success: function (response) {
                    if (response.d) CompletarCampos(response.d);
                    else MensajeFinSession();
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            });


            $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
            $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");

        }

        Desabilitar();

    }

    // FIX 63: defensive - antes lanzaba TypeError 'innerText of undefined' cuando la Lista estaba vacia.
    var __t = $('#table_id')[0];
    if (__t && __t.rows && __t.rows.length > 1 && __t.rows[1].cells.length > 1) {
        if (__t.rows[1].cells[1].innerText ==  $('#hdd_ultimafila').val()){
            $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');
            $("#table_id tr:nth-child("+1+")").css('background', 'silver');
            $('#hdd_fila').val(1);
            $(".limpiar_checked").removeAttr("checked");
            $("#"+$('#hdd_ultimafila').val()).prop('checked', true);
        }
    }
}

function Guardar(){

    if(navigator.onLine) {
     
    if ($('#hhd_vTienda').val() == "") {
        Mensaje('Advertencia','El código de la tienda no esta asignado o no es valido.\n\n Solicite configuración del código de la tienda al administrador.', 'warning');
        return;
    } else if ($('#hhd_vAlmacen').val() == "") {
        Mensaje('Advertencia','El código del almacen no esta asignado o no es valido.\n\n Solicite configuración del código del almacen al administrador.', 'warning');
        return;
    } else if ($('#hhd_vCaja').val() == "") {
        Mensaje('Advertencia','El código de la caja no esta asignado o no es valido.\n\n Solicite configuración del código de la caja al administrador.', 'warning');
        return;
    } else if ($('#txt_cdoc').val() == "") {
        Mensaje('Advertencia','Seleccione el tipo de documento.', 'warning');
        return;
    } else if ($('#txt_cdoc').val().length != 2 && $('#txt_cdoc').val() != "") {
        Mensaje('Advertencia', 'Ingresar el código del documento de referencia de 2 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc_serie').val().length != 4 && $('#txt_cdoc_serie').val() != "") {
        Mensaje('Advertencia', 'Ingresar la serie del documento de referencia de 4 digitos.', 'warning');
        return;
    } else if ($('#txt_cdoc_nro').val().length != 8 && $('#txt_cdoc_nro').val() != "") {
        Mensaje('Advertencia', 'Ingresar el correlativo del documento de referencia de 8 digitos.', 'warning');
        return;
    } else if ($('#txt_ntotal').val() == "") {
        Mensaje('Advertencia', 'Para continuar buscar los datos de referencia.', 'warning');
        return;
    }
      

    if($("#ddl_tip_nota").val() == "07"){ 


     $("#tbListArticulosSelect > tbody").html("");
    for (var i = 0; i < objListArticulos.length; i++) {
        if($('#Input'+objListArticulos[i].id_lnfact).attr('id') == 'Input'+objListArticulos[i].id_lnfact && $('#Radio'+objListArticulos[i].id_lnfact).is(':checked') == true){
      
            if(parseFloat($('#Input'+objListArticulos[i].id_lnfact).val()) > parseFloat(objListArticulos[i].ncantidad) ){
                Mensaje('Advertencia','La cantidad del articulo '+objListArticulos[i].cdsc_articulo+' no debe ser mayor a '+objListArticulos[i].ncantidad,'warning');
                return;
            } else if(parseFloat($('#Input'+objListArticulos[i].id_lnfact).val()) <= 0 ){
                Mensaje('Advertencia','La cantidad del articulo '+objListArticulos[i].cdsc_articulo+' no debe ser menor o igual a 0 y no debe ser mayor a '+objListArticulos[i].ncantidad,'warning');
                return;
            } 

            $("#tbListArticulosSelect").find('tbody')
                .append($('<tr>')
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].ccod_articulo + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].cdsc_articulo + '</td>')) 
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + $('#Input'+objListArticulos[i].id_lnfact).val() + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].id_lnfact + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].cobser_variante + '</td>')) 
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].ndescuento + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].nprecio + '</td>'))  
                                                                          .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].nisc + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].nimpuesto + '</td>'))
                .append($('<td class="disabled" style="padding: 5px;border: solid 1px #b99090;" >' + objListArticulos[i].ncosto + '</td>'))
                .append($('</tr>'))
                );   

        }
    }

  objListArticulosAceptados = $('#tbListArticulosSelect tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            ccod_articulo: $td.eq(0).text(),
            cdsc_articulo: $td.eq(1).text(),
            ncantidad: $td.eq(2).text(),
            id_lnfact: $td.eq(3).text(),
            cobser_variante: $td.eq(4).text(),
            ndescuento: $td.eq(5).text(),
            nprecio: $td.eq(6).text(),
            nisc: $td.eq(7).text(),
            nimpuesto: $td.eq(8).text(),
            ncosto: $td.eq(9).text()
        }
    }).get();

    if(objListArticulosAceptados.length == 0){
        Mensaje('Advertencia','No se ha seleccionado ningún artículo para devolver','warning');
                return;
    }

    Swal.fire({
        title: "¿Desea generar una Nota de Crédito por Devolución del documento " + $('#txt_cdoc').val()+" "+ $('#txt_cdoc_serie').val()+" "+$('#txt_cdoc_nro').val()+"?",
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Aceptar',
        showCancelButton: true,
        cancelButtonColor: '#f7505a',
        cancelButtonText: "Cancelar"
    }).then(
        (result) => { 
        if (result.isConfirmed) { 

     var obj = [
        {
            "id_cbfact": $('#idfact').val(),
            "cdsc_movito": $('#txt_cdsc_movito').val(),
            "nimp_aplicado": "0"  
        }
    ]

    $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/GenerarNotaCreditoDevolucion',
        data: JSON.stringify({ AnulacionNC: obj, operacion: $('#ddl_tip_nota').val(), ListArticulo: objListArticulosAceptados }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d=="-1") MensajeFinSession();
                else{
                var obje = response.d;
                    if(response.d[1] == 'OK'){ 
                          
                         $('#lb_importetotal').text($('#txt_ntotal').val()); 
                         $('#lb_credito').text(response.d[3]); 
                         $('#lb_numdoc').text(response.d[2]);  
                         Deshacer();
                        $(".limpiar").val("");
                        traducir_tabla(); 
                        $('#table_id').DataTable().destroy();
                        $('#table_id').DataTable({
                            "zeroRecords": "No se encontraron resultados."
                        });
                        $('#modalResumenVenta').modal('show');  

                        $('#hhd_id_nc').val(response.d[4]);
                         
                    }else if(response.d[1] == 'CodOperND'){ 
                        Mensaje('Error','El código del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración del código del documento de nota de débito  al administrador.','error'); 
                    }else if(response.d[1] == 'SerOperND'){ 
                        Mensaje('Error','La serie del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de nota de débito  al administrador.','error'); 
                    }else if(response.d[1] == 'NroOperND'){ 
                        Mensaje('Error','El correlativo del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de nota de débito  al administrador.','error');
                    }else if(response.d[1] == 'CodOperInve'){ 
                        Mensaje('Error','El código del documento de inventario no esta asignado o no es valido.\n\n Solicite configuración del código del documento de inventario al administrador.','error'); 
                    }else if(response.d[1] == 'SerOperInve'){ 
                        Mensaje('Error','La serie del documento de inventario no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de inventario al administrador.','error'); 
                    }else if(response.d[1] == 'NroOperInve'){ 
                        Mensaje('Error','El correlativo del documento de inventario no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de inventario al administrador.','error');
                    }else if(response.d[1] == 'CodOperCobr'){ 
                        Mensaje('Error','El código del documento de cobranza no esta asignado o no es valido.\n\n Solicite configuración del código del documento de cobranza al administrador.','error'); 
                    }else if(response.d[1] == 'SerOperCobr'){ 
                        Mensaje('Error','La serie del documento de cobranza no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de cobranza al administrador.','error'); 
                    }else if(response.d[1] == 'NroOperCobr'){ 
                        Mensaje('Error','El correlativo del documento de cobranza no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de cobranza al administrador.','error');
                    }else if(response.d[1] == 'CodCaja'){ 
                        Mensaje('Error','El código de caja del usuario no esta asignado o no es valido.\n\n Solicite configuración del código de caja al administrador.','error');
                    }else if(response.d[1] == 'CodTie'){ 
                        Mensaje('Error','El código de tienda del usuario no esta asignado o no es valido.\n\n Solicite configuración del código de tienda al administrador.','error');
                    }else if(response.d[1] == 'CodAlm'){ 
                        Mensaje('Error','El código de almacén del usuario no esta asignado o no es valido.\n\n Solicite configuración del código de almacén al administrador.','error');
                    }else if(response.d[1] == 'IdCaja'){ 
                        Mensaje('Error','La caja no esta apeturada.','error'); 
                    }else if(response.d[1] == 'ErrorDocFactura'){ 
                        Mensaje('Error',obje[0].cdoc,'error'); 
                    }else if(response.d[1] == 'ErrorDocCobranza'){ 
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


    }else if($("#ddl_tip_nota").val() == "04"){
       var MontDes  = parseFloat($('#txtMontDescuento').val()).toFixed(2);
       var MontTot  = parseFloat($('#txtImportTot').val()).toFixed(2);
       var Total =  MontDes - MontTot; 
       if($("#txt_nimp_aplicado").val() == ""){
            Mensaje('Advertencia','Ingresar monto de descuento','warning');
            return;
        }else if($("#txt_cdsc_movito").val() == ""){
            Mensaje('Advertencia','Ingresar motivo de descuento','warning');
            return;
         }else if(parseFloat($("#txt_nimp_aplicado").val()).toFixed(2) > parseFloat($("#montodisponible").text()).toFixed(2)){
            Mensaje('Advertencia','El monto ingresado debe ser menor al importe disponible.','warning');
            return; 
        }  
        Swal.fire({
        title: "¿Desea generar una Nota de Crédito por Descuento del documento " + $('#txt_cdoc').val()+" "+ $('#txt_cdoc_serie').val()+" "+$('#txt_cdoc_nro').val()+"?",
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Aceptar',
        showCancelButton: true,
        cancelButtonColor: '#f7505a',
        cancelButtonText: "Cancelar"
    }).then(
        (result) => {

        if (result.isConfirmed) {
          

     var obj = [
        {
            "id_cbfact": $('#idfact').val(),
            "cdsc_movito": $("#txt_cdsc_movito").val(),
            "nimp_aplicado": $("#txt_nimp_aplicado").val()  
        }
    ]

    $.ajax({
        type: "POST",
        url: 'NotaCredito.aspx/GenerarNotaCredito',
        data: JSON.stringify({ AnulacionNC: obj, operacion: "", ListArticulo: objListArticulosAceptados }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d=="-1") MensajeFinSession();
                else{
                var obje = response.d;
                    if(obje[0].Doc == 'OK'){ 
                         $('#lb_numdoc').text(obje[0].cdoc+' '+obje[0].cdoc_serie+' '+obje[0].cdoc_nro); 
                        $('#lb_importetotal').text($('#txt_ntotal').val()); 
                        $('#lb_credito').text(parseFloat(($('#txt_nimp_aplicado').val()).replace(',', '.')).toFixed(2)); 
                        Deshacer();
                        $(".limpiar").val("");
                        traducir_tabla(); 
                        $('#table_id').DataTable().destroy();
                        $('#table_id').DataTable({
                            "zeroRecords": "No se encontraron resultados."
                        });
                        $('#modalResumenVenta').modal('show');  

                        $('#hhd_id_nc').val(obje[0].id_cbfact);
                         
                    }else if(obje[0].Doc == 'CodOperND'){ 
                        Mensaje('Error','El código del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración del código del documento de nota de débito  al administrador.','error'); 
                    }else if(obje[0].Doc == 'SerOperND'){ 
                        Mensaje('Error','La serie del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de nota de débito  al administrador.','error'); 
                    }else if(obje[0].Doc == 'NroOperND'){ 
                        Mensaje('Error','El correlativo del documento de nota de débito no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de nota de débito  al administrador.','error');
                    }else if(obje[0].Doc == 'CodOperInve'){ 
                        Mensaje('Error','El código del documento de inventario no esta asignado o no es valido.\n\n Solicite configuración del código del documento de inventario al administrador.','error'); 
                    }else if(obje[0].Doc == 'SerOperInve'){ 
                        Mensaje('Error','La serie del documento de inventario no esta asignado o no es valido.\n\n Solicite configuración de la serie del documento de inventario al administrador.','error'); 
                    }else if(obje[0].Doc == 'NroOperInve'){ 
                        Mensaje('Error','El correlativo del documento de inventario no esta asignado o no es valido.\n\n Solicite configuración del correlativo del documento de inventario al administrador.','error');
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

    }else if($("#ddl_tip_nota").val() == "02"){
     if ($('#txt_nimp_aplicado').val() ==null || $('#txt_nimp_aplicado').val() =="") {
        Mensaje('Advertencia', 'Ingrese el importer de la Nota de Débito.', 'warning');
        return; 
    } 

    var obj = [
        {
            "id_cbfact": $('#idfact').val(),
            "nimp_aplicado": $('#txt_nimp_aplicado').val()
        }
    ]

    Swal.fire({
        title: "¿Desea generar una Nota de Débito del documento " + $('#txt_cdoc').val()+" "+ $('#txt_cdoc_serie').val()+" "+$('#txt_cdoc_nro').val()+"?",
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
        url: '../Ventas/NotaDebito.aspx/GenerarNotaDebito',
        data: JSON.stringify({ notadebito: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d=="-1") MensajeFinSession();
                else{
                var obje = response.d;
                    if(obje[0].Doc == 'OK'){ 
                          
                        $('#lb_numdoc').text(obje[0].cdoc+' '+obje[0].cdoc_serie+' '+obje[0].cdoc_nro); 
                        $('#lb_importetotal').text($('#txt_ntotal').val()); 
                        $('#lb_credito').text(parseFloat(($('#txt_nimp_aplicado').val()).replace(',', '.')).toFixed(2)); 

                        Deshacer();
                        $(".limpiar").val("");
                        traducir_tabla(); 
                        $('#table_id').DataTable().destroy();
                        $('#table_id').DataTable({
                            "zeroRecords": "No se encontraron resultados."
                        });
                        $('#modalResumenVenta').modal('show');  
                        $('#hhd_id_nc').val(obje[0].id_cbfact);

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
      } else {
     Mensaje('Error','Sin acceso a internet.','error');
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
 