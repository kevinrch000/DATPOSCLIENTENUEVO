jQuery(function($) {
	$('.radio-toggle').toggleInput();
});

function Ejecutar() {
    
    if(navigator.onLine) {

//        if ($('#txtTienda').val() ==null) {
//            Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
//            return;
//        } else if ($('#txtAlmacen').val() ==null) {
//            Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
//            return;
//        } else if ($('#txtfchDesde').val() == "") {
//            Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
//            return;
//        } else if ($('#txtfchHasta').val() == "") {
//            Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
//            return;
//        } else if ($('#txtTipoOperacion').val() == "") {
//            Mensaje('Advertencia', 'Seleccionar tipo de operación.', 'warning');
//            return;
//        }

//        $('#table_principal').DataTable().destroy();
        $('#table_id').DataTable().destroy();
    
        var obj = [  {
            "ccoa_dsc": $('#tb_cliente').val(),
            "ccod_coa": $('#tb_ruc').val(),
            "cdoc": $('#ddl_tipodoc').val(),
            "cdoc_serie": $('#tb_serie').val(),
            "dfch_doc": $('#dp_fechadoc').val(),
            "estadodoc": $('#ddl_estado').val(), 
            "fechsunat": $('#dp_fechasunat').val()
        } ]

        $.ajax({
            type: "POST",
            url: 'FacturaListaPrecio.aspx/ConsultaDocumentosSunat',
            data: JSON.stringify({ objconsulta: obj }), 
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {

                objfactura = response.d; 

                 if (response.d=="-1"){ MensajeFinSession(); }
                 
                 else{ 

//                    document.getElementById('container').style.display = 'inline'; 

//                    $('#table_visible').DataTable({
//                        data: objfactura,     
//                         "ordering": false,
//                        columns: [
//                        { data: 'ccod_alm' },
//                        { data: 'cdsc_alm' },
//                        { data: 'ctipo' },
//                        { data: 'cserie' },
//                        { data: 'nnumero' }, 
//                        { data: 'nimporte_tot'  },
//                        { data: 'dfecha'  },
//                        { data: 'ccod_alm_ing' },   
//                        { data: 'DocRef' },
//                        { data: 'DocFact' , className: "dt-body-center"}]   
//                    });
//             
//                    $('#table_principal').DataTable({
//                        "autoWidth": false,
//                        // "lengthMenu": [100],
//                        "paging": false,
//                        "ordering": false,
//                        "info": false,
//                        "searching": false,
//                        "language": {
//                            "lengthMenu": "Mostrar _MENU_ entradas",
//                            "zeroRecords": "No se encontraron resultados.",
//                            "info": "Total de registros : <b>_MAX_</b>",
//                            "infoEmpty": "",
//                            "infoFiltered": "",
//                            "search": "",
//                            "searchPlaceholder": " ",
//                            "paginate": {
//                                "first": "Primero",
//                                "last": "Último",
//                                "next": "Siguiente",
//                                "previous": "Anterior"
//                            }
//                        },
//                        data: objfactura,
//                        columns: [ 
//                        { data: 'ccod_alm' },
//                        { data: 'cdsc_alm' },
//                        { data: 'ctipo' },
//                        { data: 'cserie' },
//                        { data: 'nnumero' }, 
//                        { data: 'nimporte_tot'  },
//                        { data: 'dfecha'  },
//                        { data: 'ccod_alm_ing' },   
//                        { data: 'DocRef' }],
//                            scrollX: "2000px",
//                        scrollCollapse: true,
//                    });
                }
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        }); 

    } 
    else {Mensaje('Error','Sin acceso a internet.','error');}
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
    $("#dp_fechadoc").datepicker();
    $("#dp_fechasunat").datepicker();
});

function ClientePorDefecto(){

   var obj = llenarobjeto('FacturaListaPrecio.aspx/ClientePorDefecto');
   
   $('#hdd_ctip_doc').val(obj[0].ctip_doc);
   $('#hdd_rucC').val(obj[0].cdoc_coa);
   $('#hdd_cdsc_coa').val(obj[0].cdsc_coa);
   $('#hdd_direc').val(obj[0].cdirc_coa);
   $('#hdd_coa').val(obj[0].ccod_coa || obj[0].id_coa || 'CLI000');
}

function PasarCuenta(obj){
  
    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSConsultarCuentaDetalles',
        data: '{id_cbcuenta: "' + obj.firstChild.innerText + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){

                Limpiar(); 

                var obj_ncuenta = $('#tablacuentas tr:has(td)').map(function(i, v) {
                var $td =  $('td', this);
                    return { 
                                id_cuenta: $td.eq(0).text(),
                                ntot_desct: $td.eq(3).text(),
                                ntot_impbruto: $td.eq(4).text(),
                                ntot_igv: $td.eq(5).text(),
                                ntot_impneto: $td.eq(6).text()                 
                            }
                }).get();
                   
                 for (var i = 0; i < obj_ncuenta.length; i++) {
                    if (obj_ncuenta[i].id_cuenta == obj.firstChild.innerText){

                        $("#div_subtotal").text(parseFloat(obj_ncuenta[i].ntot_impbruto).toFixed(2));
                        $("#div_igv").text(parseFloat(obj_ncuenta[i].ntot_igv).toFixed(2));
                        $("#div_isc").text('0.00');
                        $("#div_desc").text(parseFloat(obj_ncuenta[i].ntot_desct).toFixed(2)); 
                        $("#div_total").text(parseFloat(obj_ncuenta[i].ntot_impneto).toFixed(2)); 
                        $("#hdd_total").val(parseFloat(obj_ncuenta[i].ntot_impneto).toFixed(2)); 

                    }
                 }

                 
                var objcuenta = response.d;
                $("#table_Articulos > tbody").html("");

                for (var i = 0; i < objcuenta.length; i++) {
                    var dsc_articulo  = "";
                    if (objcuenta[i].ctip_desc == ""){
                        dsc_articulo = '<div>' + objcuenta[i].cdsc_articulo + '</div><div style="color: darkgrey;font-style: italic;">' + objcuenta[i].cobser_variante + '</div><div style="color: darkgrey;font-style: italic;"></div>'
                    } else {
                        if (objcuenta[i].ctip_desc == "%"){
                            dsc_articulo = '<div>' + objcuenta[i].cdsc_articulo + '</div><div style="color: darkgrey;font-style: italic;">' + objcuenta[i].cobser_variante + '</div><div style="color: darkgrey;font-style: italic;">' + objcuenta[i].cdsc_desc + '</div>'
                   
                        } else {
                            dsc_articulo = '<div>' + objcuenta[i].cdsc_articulo + '</div><div style="color: darkgrey;font-style: italic;">' + objcuenta[i].cobser_variante + '</div><div style="color: darkgrey;font-style: italic;">' + objcuenta[i].cdsc_desc + '</div>'
         
                        }
                    }

                    $("#table_Articulos").find('tbody')
                    .append($('<tr>')
                    .append($('<td>' + dsc_articulo + '</td>'))
                    .append($('<td>' + objcuenta[i].ncantidad + '</td>'))
                    .append($('<td>' + objcuenta[i].nprecio + '</td>'))
                    .append($('<td class="monto" >' + objcuenta[i].nimporte_neto + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalEditarCantidad" onclick="EditarCantidad(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="Eliminar(this)"></a></td>'))
                    .append($('<td style="display: none" >' + objcuenta[i].id_articulo + '</td>'))
                    .append($('<td class="igv" style="display: none" >' + objcuenta[i].nigv_uni + '</td>'))
                    .append($('<td class="isc" style="display: none">0.00</td>'))
                    .append($('<td class="creainventario" style="display: none">' + objcuenta[i].ctip_art + '</td>'))
                    .append($('<td class="costo" style="display: none">' + objcuenta[i].ncosto + '</td>'))
                    .append($('<td class="igv_por_cantidad" style="display: none">' + objcuenta[i].nimpuesto + '</td>'))
                    .append($('<td class="isc_por_cantidad" style="display: none">0.00</td>'))
                    .append($('<td style="display: none">' + objcuenta[i].id_variante + '</td>'))
                    .append($('<td style="display: none">' + objcuenta[i].cdescn_max + '</td>'))
                    .append($('<td style="display: none" class="descuento" >' + objcuenta[i].ndescuento + '</td>'))
                    .append($('<td style="display: none">' + objcuenta[i].ctip_desc + '</td>'))
                    .append($('</tr>'))
                   );
                   }
            } else { 
              
              MensajeFinSession();

            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < lista.length; i++) {
//        
//        PasarArticuloCuenta(lista[i])
//        
//    }

    $('#modalObtenerCuenta').modal('hide');


}

function PasarArticuloCuenta(lista) {

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSConsultarArticuloPrecio',
        data: '{codigo: "' + lista.id_articulo + '",ccod_cblistpre: "' + $('#ddl_lpn').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {

                var cadena = '<td><div>' + response.d[0].cdsc_articulo + '</div>';
                if(lista.cobser_variante!="-") cadena = cadena + '<div style="color: darkgrey;font-style: italic;">'+ lista.cobser_variante + '</div>';
                if(lista.ctip_desc!="") cadena = cadena + '<div style="color: darkgrey;font-style: italic;">'+ lista.ctip_desc + '</div></td>';

                var a = parseFloat((response.d[0].npre_uni)*(lista.ncantidad)-lista.ndescuento).toFixed(2);
                var tasa_total = NumeroSeguro(response.d[0].igv) + NumeroSeguro(response.d[0].isc);
                var igv_linea = CalcularImpuestoIncluido(a, response.d[0].igv, tasa_total);
                var isc_linea = CalcularImpuestoIncluido(a, response.d[0].isc, tasa_total);


                $("#table_Articulos").find('tbody')
                    .append($('<tr>')
                    .append($(cadena))
                    .append($('<td>' + lista.ncantidad + '</td>'))
                    .append($('<td>' + response.d[0].npre_uni + '</td>'))
                    .append($('<td class="monto">' + a + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalEditarCantidad" onclick="EditarCantidad(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="Eliminar(this)"></a></td>'))
                    .append($('<td style="display: none">' + lista.id_articulo + '</td>'))
                    .append($('<td class="igv" style="display: none">' + response.d[0].igv + '</td>'))
                    .append($('<td class="isc" style="display: none">' + response.d[0].isc + '</td>'))
                    .append($('<td class="creainventario" style="display: none">' + response.d[0].ctip_articulo + '</td>'))
                    .append($('<td class="costo" style="display: none">' + response.d[0].npre_costo + '</td>'))
                    .append($('<td class="igv_por_cantidad" style="display: none">' + igv_linea.toFixed(2) + '</td>'))
                    .append($('<td class="isc_por_cantidad" style="display: none">' + isc_linea.toFixed(2) + '</td>'))
                    .append($('<td style="display: none">' + response.d[0].state + '</td>'))
                    .append($('<td style="display: none">' + response.d[0].ndes_max + '</td>'))
                    .append($('<td style="display: none" class="descuento">' + parseFloat(lista.ndescuento).toFixed(2) + '</td>'))
                    .append($('<td style="display: none">' + lista.ctip_descn + '</td>'))
                );

                CalcularTotal();

                $('#div_venta').scrollTop($('#div_venta').prop("scrollHeight"));

            }

            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarCuentas(){
    
   var obj = llenarobjeto('FacturaListaPrecio.aspx/ConsultarCuentas');

   $("#tablacuentas > tbody").html("");
   
    for (var i = 0; i < obj.length; i++) {
        $("#tablacuentas").find('tbody')
        .append($('<tr ondblclick="PasarCuenta(this)">')
        .append($('<td style="display:none;">' + obj[i].id_cbcuenta + '</td>'))
        .append($('<td>' + obj[i].cetiqueta + '</td>'))
        .append($('<td>' + obj[i].fechacreacion + '</td>'))
        .append($('<td style="display:none;">' + obj[i].ntot_desct + '</td>'))
        .append($('<td style="display:none;">' + obj[i].ntot_impbruto + '</td>'))
        .append($('<td style="display:none;">' + obj[i].ntot_igv + '</td>'))
        .append($('<td style="display:none;">' + obj[i].ntot_impneto + '</td>'))

        );
    }
}

function GuardarCuenta(){

    var objMovimientoDetalle = $('#table_Articulos tr:has(td)').map(function (i, v) {
        var $td = $('td', this);

        var cobser_variante_ant;
        var cdsc_articulo_ant;
        var desc_descuento = "";

        if($td.eq(0)[0].childNodes.length>1) {
            cobser_variante_ant = $td.eq(0)[0].childNodes[1].innerText;
            cdsc_articulo_ant = $td.eq(0)[0].childNodes[0].innerText;
            desc_descuento = $td.eq(0)[0].childNodes[2].innerText;
        }
        else { 
            cobser_variante_ant = '-';
            cdsc_articulo_ant = $td.eq(0).text();
        }

        return {
            ncantidad: $td.eq(1).text(),
            nprecio: $td.eq(2).text(),
            nimporte_neto: $td.eq(3).text(),
            id_articulo: $td.eq(6).text(),
            nigv_uni: $td.eq(7).text(),
            ctip_art: $td.eq(9).text(),
            ncosto:  $td.eq(10).text(),
            nimpuesto: $td.eq(11).text(),
            nisc: $td.eq(12).text(),
            id_variante: $td.eq(13).text(),
            cdescn_max: $td.eq(14).text(),
            ndescuento: $td.eq(15).text(),
            ctip_descn: $td.eq(16).text(),
            cdsc_articulo: cdsc_articulo_ant,
            cobser_variante: cobser_variante_ant,
            ctip_desc: desc_descuento
        }
    }).get();

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSGuardarCuenta',
        data: JSON.stringify({ cliente: $('#hdd_coa').val(), etiqueta: $('#tb_etiqueta').val(), ntot_desct: $('#div_desc').text(), ntot_impbruto: $('#div_subtotal').text(), ntot_igv: $('#div_igv').text(), ntot_impneto: $('#div_total').text(), detalle: objMovimientoDetalle }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d == "-1") MensajeFinSession();
            else {
                if (response.d == true) {
                    $("#table_Articulos > tbody").html("");
                    Mensaje('', 'Se guardo la cuenta con éxito', 'success');
                    Limpiar();
                }
            }

            if (response.d[0] == false) Mensaje('Error', 'No se realizó la operación', 'error');
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

}

function ValidarCuenta(){

    var objArticulos = $('#table_Articulos tr:has(td)').map(function (i, v) {
    var $td = $('td', this);
        return {
            cdsc_articulo: $td.eq(0).text(),
            ncantidad: $td.eq(1).text(),
            nimporte_bruto: $td.eq(3).text()
        }
    }).get();

    if(objArticulos.length>0)
        $('#modalGuardarCuenta').modal('show');
    else
       Mensaje('No se ha seleccionado ningún artículo','','warning');
        
}


function PasarVariante(){
    
    if ($("#cadena_variantes").text().length==0) {
        $("#cadena_variantes").text($("#ddl_subvariante option:selected").text());
        $("#hdd_subvariantes").val($("#ddl_subvariante").val());
    }
    else {
        $("#cadena_variantes").text($("#cadena_variantes").text()+','+$("#ddl_subvariante option:selected").text());
        $("#hdd_subvariantes").val($("#hdd_subvariantes").val()+','+$("#ddl_subvariante").val());
    }

    $("#div_eraser").show();

}

function CargarSubvariantes(){

    var listBox = document.getElementById("ddl_subvariante");
    listBox.options.length = 0;


    if($("#ddl_variante").val()!=""){

        $.ajax({
            type: "POST",
            url: 'FacturaListaPrecio.aspx/ConsultarSubVariantesActivas',
            data: '{id_variante: "' + $("#ddl_variante").val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if(response.d){
                    var $dropdown = $("#ddl_subvariante");

                    $.each(response.d, function(item) {
                        $dropdown.append($("<option />").val(this.id_cbvariante).text(this.cdsc_variante));
                    });            
                }
                else MensajeFinSession();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    }
}

function CargarVariantes(id_articulo){

    var listBox = document.getElementById("ddl_variante");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/ConsultarVariantesActivas',
        data: '{id_articulo: "' + id_articulo + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){
                var $dropdown = $("#ddl_variante");

                $dropdown.append($("<option />").val("").text(""));

                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.id_cbvariante).text(this.cdsc_variante));
                });            
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function TarjetaNuevo(){

    if ($("#div_faltante").text() == '0.00') {
            Mensaje('Advertencia', 'Monto ingresado es suficiente.', 'warning');
            return; 
    } 
    $('#modalTarjetaNuevo').modal('show'); 

    $('#tb_montonuevotarjeta').val('');
    $('#tb_tarjeta').val('');
    $('#tb_referencia').val('');
    $('#div_tarjetanuevo').hide();
    $('.sombreado_mp').removeClass('sombreado_mp');
    (document.getElementById("ddl_tarjetanuevo")).selectedIndex = 0;

    $('#tb_montonuevotarjeta').val($("#div_faltante")[0].innerText);
}

function Limpiar(){
    $("#tb_articulo").val("");
    $("#tb_anadir").val("");
    $("#table_Articulos > tbody").html("");
    $("#div_articulos").html("");
    $('#input_fav').addClass("sombreado");

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSCargarFavoritos',
        data: '{ccod_cblistpre: "' + $('#ddl_lpn').val() + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,  
        success: function (response) {
            if (response.d) { 
                CompletarArticulosCategoria(response.d); 
            } 
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
     

    $("#tb_nombre").val("");
    $("#tb_precio").val("");
    $("#tb_cantidad").val("");

    $("#div_desc")[0].innerText = "0.00";
    $("#div_subtotal")[0].innerText = "0.00";
    $("#div_igv")[0].innerText = "0.00";
    $("#div_isc")[0].innerText = "0.00";
    $("#div_total")[0].innerText = "0.00";

    $("#tb_clientes").val("");

    (document.getElementById("rb_boleta")).checked = true;

    $("#tabla_pago > tbody").html("");

    $("#modal_divTotalEfectivoNuevo")[0].innerText = "";
    $('#tb_montonuevoefectivo').val("");
         
    $("#modal_divTotalEfectivoEditar")[0].innerText = "";
    $('#tb_montoeditarefectivo').val("");

    $("#modal_divTotalNuevoTarjeta")[0].innerText = "";
    $("#txtITNC")[0].innerText = "";
    $('#div_tarjetanuevo').hide();
    $('#tb_montonuevotarjeta').val("");
    $('#tb_tarjeta').val("");
    $('#tb_referencia').val("");
         
    $("#modal_divTotalEditarTarjeta")[0].innerText = "";
    $('#div_tarjetaeditar').hide();
    $('#tb_montoeditartarjeta').val("");
    $('#tb_tarjetaeditar').val("");
    $('#tb_referenciaeditar').val("");     

    (document.getElementById("ddl_tarjetas")).selectedIndex = 0;

    $("#div_totalcobranza")[0].innerText = "0.00";
    $("#div_faltante")[0].innerText = "0.00";
    $("#div_vuelto")[0].innerText = "0.00";

    $('.nav-tabs li:eq(0) a').tab('show');

    $('#hdd_metodopago').val('Visa');
    $('#hdd_total').val('0.00');
    $('#hdd_coa').val("");
    $('#hdd_direc').val("");
    $('#hdd_rucC').val("");
    ClientePorDefecto();

    $('.sombreado').removeClass("sombreado");
    $('.sombreado_mp').removeClass("sombreado_mp");
}


function Imprimir(){

//    GenerarTicket();

//    pdftemp.autoPrint(); 
//    var newWin = window.open(pdftemp.output('bloburl'), '_blank');
//    //ambos ejecutan la impresion

//    setTimeout(function(){newWin.close();},2000);

//    ArmarHtml();
    var mywindow = window.open();
    mywindow.document.write(document.getElementById("zona-imprimir").innerHTML);
    setTimeout(function () { mywindow.print(); }, 500);//espera a que cargue el logo
    setTimeout(function () { mywindow.close(); }, 2000);//cierra la impresion, tiene que ser a 2000 milisegundos 

//    $("#zona-imprimir").html("");
    
    LimpiarHtmlTicket();
}

function LimpiarHtmlTicket(){
    $("#nombre_documento").html("");
    $("#codigo_documento").html("");
    $("#fecha_documento").html("");
    $("#hora_documento").html("");
    $("#nombre_cliente").html("");
    $("#direccion_cliente").html("");
    $("#ruc_cliente").html("");    
    $("#div_articlosdocumento").html("");    
    $("#opgrabada_documento").html("");
    $("#igv_documento").html("");
    $("#isc_documento").html("");
    $("#total_documento").html("");
    $("#son_documento").html("");    
    $("#div_cobranzadocumento").html(""); 
    $("#vendedor").html("");
    $("#codigo_caja").html("");
    $("#qrcode").html("");   
    $("#vuelto").html(""); 
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

//    $('#zona-imprimir').hide();
}

function ArmarHtmlPrevio() {

    var obj = llenarobjeto('FacturaListaPrecio.aspx/ConsultarTienda');

    $("#nombre_empresa1")[0].innerText = ($('#hhd_empresa').val()).trim();
    $("#direccion_empresa")[0].innerText = ($('#hhd_direccionE').val()).trim();
    $("#direccionubigeo_empresa")[0].innerText = ($('#hhd_ubigeoE').val()).trim();
    $("#ruc_empresa")[0].innerText = "Ruc: "+($('#hdd_ruc').val()).trim();
    $("#telefono_tienda")[0].innerText = "Telf: "+($('#hdd_telefono_tienda').val()).trim();
    $("#nombre_tienda")[0].innerText = "Tienda: "+($('#hdd_nombre_tienda').val()).trim();
    $("#direccion_tienda")[0].innerText = "Direc.: "+(obj[0].cdirec).trim();
    $("#ubigeo_tienda")[0].innerText = ($('#hdd_ubigeo_tienda').val()).trim();
    
    var objArticulos = $('#table_Articulos tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_articulo: $td.eq(0).text(),
            ncantidad: $td.eq(1).text(),
            preciounitario: $td.eq(2).text(),
            nimporte_bruto: $td.eq(3).text()
        }
    }).get();

    $("#div_articlosdocumento").html("");

    var div_string = "";

    if (objArticulos.length > 0) {

        $.each(objArticulos, function (index) {
            div_string = div_string + "<div class='col-xs-12'>" + objArticulos[index].cdsc_articulo + "</div>";
            div_string = div_string + 
            "<div style='text-align: center;'>"+
                "<div class='col-xs-3'></div>"+
                "<div class='col-xs-3'>" + objArticulos[index].ncantidad + "</div>"+
                "<div class='col-xs-3' style='text-align: right;'>" + objArticulos[index].preciounitario + "</div>"+
                "<div class='col-xs-3' style='text-align: right;'>" + objArticulos[index].nimporte_bruto + "</div>"+
            "</div>"
        });

        $("#div_articlosdocumento").append($(div_string));
    }
    else{
        $("#div_articlosdocumento").html("");
    }

    $("#opgrabada_documento")[0].innerText = $('#div_subtotal')[0].innerText;
    $("#igv_documento")[0].innerText = $('#div_igv')[0].innerText;
    $("#isc_documento")[0].innerText = $('#div_isc')[0].innerText;
    $("#total_documento")[0].innerText = $('#div_total')[0].innerText;
    $("#son_documento")[0].innerText = "Son: " + NumeroALetras($('#div_total')[0].innerText) + " " + $("#lNomMoneda").text();

    $("#vendedor")[0].innerText = "Vend.: " + ($('#nombre_usuario')[0].innerText).trim();
    $("#codigo_caja")[0].innerText = "Caja: " + ($('#td_caja')[0].innerText).trim();

}

function ArmarHtml() {

    $("#zona-imprimir").append('<head><link href="/Styles/css/bootstrap.css" rel="stylesheet" type="text/css"></head>');//va aqui para no causar conflicto con el bootstrap ya declarado, luego se borrara

    var obj = llenarobjeto('FacturaListaPrecio.aspx/ConsultarTienda');

    $("#nombre_empresa1")[0].innerText = ($('#hhd_empresa').val()).trim();
    $("#direccion_empresa")[0].innerText = ($('#hhd_direccionE').val()).trim();
    $("#direccionubigeo_empresa")[0].innerText = ($('#hhd_ubigeoE').val()).trim();
    $("#ruc_empresa")[0].innerText = "Ruc: "+($('#hdd_ruc').val()).trim();
    $("#telefono_tienda")[0].innerText = "Telf: "+($('#hdd_telefono_tienda').val()).trim();
    $("#nombre_tienda")[0].innerText = "Tienda: "+($('#hdd_nombre_tienda').val()).trim();
    $("#direccion_tienda")[0].innerText = "Direc.: "+(obj[0].cdirec).trim();
    $("#ubigeo_tienda")[0].innerText = ($('#hdd_ubigeo_tienda').val()).trim();

    switch ($('input:radio[name=tipo]:checked').val()) {
      case "FV": $("#nombre_documento")[0].innerText = "FACTURA DE VENTA ELECTRÓNICA"; break;
      case "BV": $("#nombre_documento")[0].innerText = "BOLETA DE VENTA ELECTRÓNICA"; break;
      case "NV": $("#nombre_documento")[0].innerText = "NOTA DE VENTA"; break;
    }    

    $("#codigo_documento")[0].innerText = $('#lb_doc').text().substring($('#lb_doc').text().split(' ',1).length+2);
    var fecha = new Date();
    var dia = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear();
    var hora = fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds();

    $("#fecha_documento")[0].innerText = "Fecha: " + dia;
    $("#hora_documento")[0].innerText = "Hora: " + hora;

    if($('input:radio[name=tipo]:checked').val()=='FV') {

        $("#ruc_cliente")[0].innerText = "Ruc: " + ($('#hdd_rucC').val()).trim();
        $("#direccion_cliente")[0].innerText = "Direc.: " + ($('#hdd_direc').val()).trim();
        $("#nombre_cliente")[0].innerText = "Cliente: " + ($('#hdd_cdsc_coa').val()).trim();
    }
    else {
            
        $("#ruc_cliente")[0].innerText = "DNI: " + ($('#hdd_rucC').val()).trim();
        $("#direccion_cliente")[0].innerText = "Direc.: " + ($('#hdd_direc').val()).trim();
        $("#nombre_cliente")[0].innerText = "Cliente: " + ($('#hdd_cdsc_coa').val()).trim();
    }


//    $("#nombre_cliente")[0].innerText = "Cliente: " + ($('#hdd_cdsc_coa').val()).trim();
//    $("#ruc_cliente")[0].innerText = "Ruc: " + ($('#hdd_rucC').val()).trim();
//    $("#direccion_cliente")[0].innerText = "Direc.: " + ($('#hdd_direc').val()).trim();
    
    var objArticulos = $('#table_Articulos tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_articulo: $td.eq(0).text(),
            ncantidad: $td.eq(1).text(),
            preciounitario: $td.eq(2).text(),
            nimporte_bruto: $td.eq(3).text()
        }
    }).get();

    $("#div_articlosdocumento").html("");

    var div_string = "";

    if (objArticulos.length > 0) {

        $.each(objArticulos, function (index) {
            div_string = div_string + "<div class='col-xs-12'>" + objArticulos[index].cdsc_articulo + "</div>";
            div_string = div_string + 
            "<div style='text-align: center;'>"+
                "<div class='col-xs-3'></div>"+
                "<div class='col-xs-3'>" + objArticulos[index].ncantidad + "</div>"+
                "<div class='col-xs-3' style='text-align: right;'>" + objArticulos[index].preciounitario + "</div>"+
                "<div class='col-xs-3' style='text-align: right;'>" + objArticulos[index].nimporte_bruto + "</div>"+
            "</div>"
        });

        $("#div_articlosdocumento").append($(div_string));
    }
    else{
        $("#div_articlosdocumento").html("");
    }

    $("#opgrabada_documento")[0].innerText = $('#div_subtotal')[0].innerText;
    $("#igv_documento")[0].innerText = $('#div_igv')[0].innerText;
    $("#isc_documento")[0].innerText = $('#div_isc')[0].innerText;
    $("#total_documento")[0].innerText = $('#div_total')[0].innerText;
    $("#son_documento")[0].innerText = "Son: " + NumeroALetras($('#div_total')[0].innerText)+ " " + $("#lNomMoneda").text();

    var objCobranzaDetalle = $('#tabla_pago tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cnom_tarje: $td.eq(0).text(),
            nmonto: $td.eq(3).text()
        }
    }).get();

    $("#div_cobranzadocumento").html("");

    div_string = "";

    if (objCobranzaDetalle.length > 0) {

        $.each(objCobranzaDetalle, function (index) {
            div_string = div_string + 
            "<div style='text-align: center;'>"+
                "<div class='col-xs-4' style='text-align: left;'>" + objCobranzaDetalle[index].cnom_tarje + "</div>"+
                "<div class='col-xs-4' style='text-align: right;'>" + $("#lSimMoneda").text() + "</div>"+
                "<div class='col-xs-4' style='text-align: right;'>" + objCobranzaDetalle[index].nmonto + "</div>"+
            "</div>"
        });

        $("#div_cobranzadocumento").append($(div_string));
    }
    else{
        $("#div_articlosdocumento").html("");
    }

    $("#vuelto")[0].innerText = $('#div_vuelto')[0].innerText;

    $("#vendedor")[0].innerText = "Vend.: "+($('#nombre_usuario')[0].innerText).trim();
    $("#codigo_caja")[0].innerText = "Caja: "+($('#td_caja')[0].innerText).trim();

    var qr = qrcode(6, "L");

    var cadena_qr = "";

    cadena_qr = ($('#hdd_ruc').val()).trim()+"|";
     
    switch ($('input:radio[name=tipo]:checked').val()) {
      case "FV": cadena_qr = cadena_qr + "01"+"|"; break;
      case "BV": cadena_qr = cadena_qr + "03"+"|"; break;
      case "NV": cadena_qr = cadena_qr + "07"+"|"; break;
    }   

    var doc = ((($('#lb_doc').text()).replace(' ', '|')).split("|",2)[1]).replace(' ', '|');

    cadena_qr = cadena_qr + doc +"|"+$('#div_igv')[0].innerText+"|"+$('#div_total')[0].innerText+"|"+dia+"|"+
    ($('#hdd_ctip_doc').val()).trim()+"|"+($('#hdd_rucC').val()).trim()+"|";

    qr.addData(cadena_qr);
    qr.make();
    document.getElementById("qrcode").innerHTML = qr.createImgTag(); 

}

//function GenerarTicket() {

//    var objCobranzaDetalle = $('#tabla_pago tr:has(td)').map(function (i, v) {
//        var $td = $('td', this);
//        return {
//            cnom_tarje: $td.eq(0).text(),
//            nmonto: $td.eq(3).text()
//        }
//    }).get();

//    var fecha = new Date();
//    var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + " " + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds();

//    var obj = llenarobjeto('Facturacion.aspx/ConsultarTienda');

//    var objArticulos = $('#table_Articulos tr:has(td)').map(function (i, v) {
//        var $td = $('td', this);
//        return {
//            cdsc_articulo: $td.eq(0).text(),
//            ncantidad: $td.eq(1).text(),
//            nimporte_bruto: $td.eq(3).text()
//        }
//    }).get();

//    var filas = (objArticulos.length) * 4 + 80//cantidad de articulos * el espacio de cada linea + el espacio que ocupa hasta la segunda linea;
//    var largo = (5 + 6 + filas + 16) * 4;

//    if (largo > 250) var doc = new jsPDF('p', 'mm', [200, largo]);
//    else var doc = new jsPDF('p', 'mm', [200, 250]);

//    var img = new Image()
//    img.src = "../Styles/img/IconMicrosig.png";
//    doc.addImage(img, 'png', 30, 1.66, 10, 10, 'alias', 'FAST');

//    doc.setFontSize(8);

//    doc.text(($('#hhd_empresa').val()).trim(), 35, 16, 'center');
//    doc.text((obj[0].cdirec).trim(), 35, 20, 'center');
//    doc.text("Telf: " + (obj[0].ctelef).trim(), 35, 24, 'center');
//    doc.text("Ruc: " + ($('#hdd_ruc').val()).trim(), 35, 28, 'center');
//    doc.text("----------------------------------------------------------------", 35, 32, 'center');

//    doc.text("Comprobante:", 3, 36);doc.text($('#lb_doc').text(), 66, 36,'right');
//    doc.text("--------------------------------------------------------------------", 35, 40,'center');
//    doc.text("Cliente:", 3, 44);doc.text(($('#hdd_cdsc_coa').val()).trim(), 66, 44,'right');
//    doc.text("Ruc:", 3, 48);doc.text(($('#hdd_rucC').val()).trim(), 66, 48,'right');
//    doc.text("Direccion:", 3, 52);doc.text(($('#hdd_direc').val()).trim(), 66, 52,'right');
//    doc.text("--------------------------------------------------------------------", 35, 56,'center');

//    doc.text("Fecha:", 3, 60); doc.text(hoy, 66, 60, 'right');
//    doc.text("Tienda:", 3, 64); doc.text(($('#td_codtienda')[0].outerText).trim(), 66, 64, 'right');
//    doc.text("Caja:", 3, 68); doc.text(($('#td_caja')[0].outerText).trim(), 66, 68, 'right');
//    doc.text("Vendedor:", 3, 72); doc.text(($('#nombre_usuario')[0].innerText).trim(), 66, 72, 'right');
//    doc.text("----------------------------------------------------------------", 35, 76, 'center');

//    var filarticulo = 80;

//    if (objArticulos.length > 0) {
//        $.each(objArticulos, function (index) {

//            var nombrearticulo = doc.splitTextToSize((objArticulos[index].cdsc_articulo).trim(), 41);
//            doc.text(nombrearticulo, 3, filarticulo);

//            doc.text((objArticulos[index].ncantidad).trim(), 48, filarticulo, 'center');
//            doc.text((objArticulos[index].nimporte_bruto).trim(), 66, filarticulo, 'right');

//            filarticulo = filarticulo + 4;
//        });
//    };

//    doc.text("----------------------------------------------------------------", 35, filas, 'center'); filas = filas + 4;
//    doc.text("Descuento:", 3, filas); doc.text($('#div_desc')[0].innerText, 66, filas, 'right'); filas = filas + 4;
//    doc.text("Sub Total:", 3, filas); doc.text($('#div_subtotal')[0].innerText, 66, filas, 'right'); filas = filas + 4;
//    doc.text("IGV:", 3, filas); doc.text($('#div_igv')[0].innerText, 66, filas, 'right'); filas = filas + 4;
//    doc.text("ISC:", 3, filas); doc.text($('#div_isc')[0].innerText, 66, filas, 'right'); filas = filas + 4;
//    doc.text("Total:", 3, filas); doc.text($('#div_total')[0].innerText, 66, filas, 'right'); filas = filas + 4;

//    doc.text("----------------------------------------------------------------", 35, filas, 'center'); filas = filas + 4;

//    var splitTitle = doc.splitTextToSize("Son: " + NumeroALetras($('#div_total')[0].innerText), 63);
//    doc.text(splitTitle, 4, filas + 4);

//    filas = filas + 4;filas = filas + 4;filas = filas + 4;

//    doc.text("----------------------------------------------------------------", 35, filas, 'center');

//    var filapagos = filas + 4;

//    if (objCobranzaDetalle.length > 0) {
//        $.each(objCobranzaDetalle, function (index) {

//            doc.text((objCobranzaDetalle[index].cnom_tarje).trim(), 3, filapagos);
//            doc.text((objCobranzaDetalle[index].nmonto).trim(), 66, filapagos,'right');

//            filapagos = filapagos + 4;
//    });};

//    doc.text("Vuelto:", 3, filapagos); doc.text($('#lb_vuelto').text(), 66, filapagos, 'right');filapagos = filapagos + 4;

//    doc.text("Cuéntanos tu experiencia en", 35, filapagos, 'center');filapagos = filapagos + 4;
//    doc.text("www.datpos.com", 35, filapagos, 'center');

//    pdftemp = doc;
//}

function GenerarDocumento(){ 

    var fecha = new Date();
    var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + " " + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds();
     
    var obj = llenarobjeto('FacturaListaPrecio.aspx/ConsultarTienda');

    var objArticulos = $('#table_Articulos tr:has(td)').map(function (i, v) {
    var $td = $('td', this);
        return {
            cdsc_articulo: $td.eq(0).text(),
            ncantidad: $td.eq(1).text(),
            nimporte_bruto: $td.eq(3).text()
        }
    }).get();

    var filas = (objArticulos.length)*11 + 28*5.2;
    var largo = (5 + 6 + filas + 16)*4;

    if(largo>750) var doc = new jsPDF('p','mm',[600, largo]);
    else var doc = new jsPDF('p','mm',[600, 750]);

    var img = new Image()
    img.src = "../Styles/img/IconMicrosig.png";  
    doc.addImage(img,'png', 92, 5, 30, 30,'alias','FAST');

    doc.setFontSize(25);

    doc.text(($('#hhd_empresa').val()).trim(),105, 50,'center');
    doc.text((obj[0].cdirec).trim(),105, 60,'center');
    doc.text("Telf: " + (obj[0].ctelef).trim(), 105, 70,'center');
    doc.text("Ruc: " + ($('#hdd_ruc').val()).trim(), 105, 80,'center');
    doc.text("-----------------------------------------------------------------", 105, 90,'center');
    doc.text("Fecha:", 10, 100);doc.text(hoy, 200, 100,'right');
    doc.text("Tienda:", 10, 110);doc.text(($('#td_codtienda')[0].outerText).trim(),200,110,'right');
    doc.text("Caja:", 10, 120);doc.text(($('#td_caja')[0].outerText).trim(), 200, 120,'right');
    doc.text("Vendedor:", 10, 130);doc.text(($('#nombre_usuario')[0].innerText).trim(), 200, 130,'right');
    doc.text("-----------------------------------------------------------------", 105, 140,'center');
   
    var filarticulo = 150;

    if (objArticulos.length > 0) {
        $.each(objArticulos, function (index) {
            
            var nombrearticulo = doc.splitTextToSize((objArticulos[index].cdsc_articulo).trim(), 125);
            doc.text(nombrearticulo, 10, filarticulo);

            doc.text((objArticulos[index].ncantidad).trim(), 145, filarticulo,'center');
            doc.text((objArticulos[index].nimporte_bruto).trim(), 200, filarticulo,'right');

            if(((objArticulos[index].cdsc_articulo).trim()).length>36) {
                filarticulo = filarticulo + 10;
                filas = filas + 10
            }

            filarticulo = filarticulo + 10;
    });};
    
    doc.text("-----------------------------------------------------------------", 105, filas,'center');filas = filas + 10;
    doc.text("Descuento:", 10, filas);doc.text($('#div_desc')[0].innerText, 200, filas,'right');filas = filas + 10;
    doc.text("Sub Total:", 10, filas);doc.text($('#div_subtotal')[0].innerText, 200, filas,'right');filas = filas + 10;
    doc.text("IGV:", 10, filas);doc.text($('#div_igv')[0].innerText, 200, filas,'right');filas = filas + 10;
    doc.text("ISC:", 10, filas);doc.text($('#div_isc')[0].innerText, 200, filas,'right');filas = filas + 10;
    doc.text("Total:", 10, filas);doc.text($('#div_total')[0].innerText, 200, filas,'right');filas = filas + 10;
    
    var splitTitle = doc.splitTextToSize(NumeroALetras($('#div_total')[0].innerText), 190);
    doc.text(splitTitle, 10, filas+10);

    var string = doc.output('datauristring');

    $('#ifrm').attr('src', string);
}



//function BuscarClientes() {

//    var availableTags =[
//      {"label": "Afghanistan", "value": "AF"}
//    ];

//        $.ajax({
//            type: "POST",
//            url: 'Facturacion.aspx/ConsultarClientesTodos',
//            data: '{texto: "' + $('#tb_clientes').val() + '" }',
//            contentType: "application/json; charset=utf-8",
//            dataType: "json",
//            async: false,

//            success: function (response) {
//                if (response.d) {
//                    $.each(response.d, function (index) {
//                        availableTags.push("'label': '" + response.d[index].cdsc_coa + "', 'value': '" + response.d[index].id_coa + "'")
//                    });
//                }
//                else MensajeFinSession();
//            },
//            error: function (xhr, status, error) {
//                alert(error);
//            }
//        });

//    $( "#tb_clientes" ).autocomplete({
//            source: availableTags,
//            select: function (event, ui) {
//                $("#tb_clientes").val(ui.item.cdsc_coa); // display the selected text
//                $("#hdd_coa").val(ui.item.id_coa); // save selected id to hidden input
//                return false;
//            }
//        }
//    );
//} ;

function CambiarCantidad() {

    var descuento = 0;
    
    var descuento_ingresado = 0;

    var cb_seleccionado="";

    if($("#tb_descuento").val() > 0) descuento_ingresado = $("#tb_descuento").val();

    if( $('#cb_porcentaje').prop('checked') ) {
        descuento = parseFloat($('#hdd_precio').val()*descuento_ingresado/100);
//        cb_seleccionado = $('#lb_porcentaje')[0].innerText;
        cb_seleccionado = '%';
    }
    else
    {
        descuento = parseFloat(descuento_ingresado);
        //cb_seleccionado = $('#lb_moneda')[0].innerText;
        cb_seleccionado = 'S';
    }

    if( descuento > parseFloat($("#hdd_descmax").val()))
    {
        Mensaje('El monto de descuento indicado es mayor que el máximo permitido.','','warning');
        return false;
    }
    else{
     
        var cadena_descuento = "";

        if(descuento>0)
        {
            if( $('#cb_porcentaje').prop('checked') ) {
                cadena_descuento = "Descuento " + $("#tb_descuento").val() + " %";
            }
            else
            {
                cadena_descuento = "Descuento " + $("#lSimMoneda").text()+" " + $("#tb_descuento").val();
            }
        }

       $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[0].innerHTML = "<div>"+$("#tb_nombre").val()+"</div>" +
        "<div style='color: darkgrey;font-style: italic;'>"+$("#cadena_variantes").text()+"</div>" +
        "<div style='color: darkgrey;font-style: italic;'>"+cadena_descuento+"</div>"  
    
        $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = $("#tb_cantidad").val();


        //$("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = parseFloat($("#tb_cantidad").val()*$("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML).toFixed(2);

        var importe_linea = parseFloat(($("#tb_cantidad").val()*$("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML)-($("#tb_cantidad").val()*descuento)).toFixed(2);
        var tasa_total = NumeroSeguro($("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[7].innerHTML) + NumeroSeguro($("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[8].innerHTML);
        $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = importe_linea;

        //$("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[11].innerHTML = parseFloat((($("#tb_cantidad").val()*($("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML)-($("#tb_cantidad").val()*descuento))*($("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[7].innerHTML)/($("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML))).toFixed(2);

        $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[11].innerHTML = CalcularImpuestoIncluido(importe_linea, $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[7].innerHTML, tasa_total).toFixed(2);


        //$("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[12].innerHTML = parseFloat($("#tb_cantidad").val()*$("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[8].innerHTML).toFixed(2);
    
        $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[12].innerHTML = CalcularImpuestoIncluido(importe_linea, $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[8].innerHTML, tasa_total).toFixed(2);


        $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[15].innerHTML = $("#tb_cantidad").val()*descuento;

        $("#table_Articulos")[0].rows[$("#hdd_rv").val()].cells[16].innerHTML = cb_seleccionado;
        
        CalcularTotal();
    }

}

function EditarCantidad(row) {

    var listBox = document.getElementById("ddl_subvariante");
    listBox.options.length = 0;

    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_nombre").val(currentRow[0].childNodes[0].childNodes[0].innerText);
    $("#tb_precio").val(currentRow.find("td:eq(2)").text());
    $("#tb_cantidad").val(currentRow.find("td:eq(1)").text());
    CargarVariantes(currentRow.find("td:eq(6)").text());

    $("#hdd_precio").val(currentRow.find("td:eq(2)").text());

    $("#hdd_descmax").val(currentRow.find("td:eq(14)").text());

//    $("#tb_nombre").val(currentRow.find("td:eq(0)").text());

//    $("#cadena_variantes").text(currentRow[0].childNodes[0].childNodes[1].innerText)

    if(currentRow[0].childNodes[0].childNodes.length>1) {
        $("#cadena_variantes").text(currentRow[0].childNodes[0].childNodes[1].innerText);
        var vari = currentRow[0].childNodes[0].childNodes[1].innerText;
        $("#tb_nombre").val(currentRow[0].childNodes[0].childNodes[0].innerText);
        var desc = currentRow[0].childNodes[0].childNodes[0].innerText;
    }
    else { 
        $("#tb_nombre").val(currentRow.find("td:eq(0)").text());
        $("#cadena_variantes").text("");
    }

    if(currentRow.find("td:eq(13)").text()>0) $('#div_variantes').show();
    else  
    {$('#div_variantes').hide();$("#cadena_variantes").text("");}

}

function BuscarClientes() {

    $('#hdd_coa').val("");
    
    if ($('#tb_clientes').val().length > 2) {

        $.ajax({
            type: "POST",
            url: 'FacturaListaPrecio.aspx/ConsultarClientesTodos',
//            data: '{texto: "' + $('#tb_clientes').val() + '" }',
            data: '{texto: "' + $("#tb_clientes").val() + '", tipodoc: "' + $('input:radio[name=tipo]:checked').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if (response.d) CompletarClientes(response.d);
                else MensajeFinSession();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    }
    else {
        $('#sugerencias_clientes').html("");

    }

}

function CompletarClientes(obj) {

    $("#sugerencias_clientes").html("");
    $("#sugerencias_clientes").show();
    

    var div_string = "";

    if (obj.length > 0) {

        $.each(obj, function (index) {

            if(obj[index].ctipo_coa == 1)//persona natural
            {
                div_string = div_string + "<div class='col-md-12' onclick='PasarCliente(&apos;" + (obj[index].cdsc_coa).trim() + "&apos;,&apos;" + obj[index].id_coa + "&apos;,&apos;" + obj[index].cdirc_coa + "&apos;,&apos;" + obj[index].cdoc_coa + "&apos;,&apos;" + (obj[index].ctip_doc).trim() + "&apos;)'><div class='col-md-9'>" + obj[index].cdsc_coa + "</div><div class='col-md-1'><a class='fa fa-user'></a></div><div class='col-md-2'>" + obj[index].cdoc_coa + "</div><div style='display: none;'>" + obj[index].ctip_doc +"</div></div>";
            }

            if(obj[index].ctipo_coa == 2)//persona juridica
            {
                div_string = div_string + "<div class='col-md-12' onclick='PasarCliente(&apos;" + (obj[index].cdsc_coa).trim() + "&apos;,&apos;" + obj[index].id_coa + "&apos;,&apos;" + obj[index].cdirc_coa + "&apos;,&apos;" + obj[index].cdoc_coa + "&apos;,&apos;" + (obj[index].ctip_doc).trim() + "&apos;)'><div class='col-md-9'>" + obj[index].cdsc_coa + "</div><div class='col-md-1'><a class='fa fa-building'></a></div><div class='col-md-2'>" + obj[index].cdoc_coa + "</div><div style='display: none;'>" + obj[index].ctip_doc +"</div></div>";
//                div_string = div_string + "<div class=''>" + obj[index].cdsc_coa + "</div><a class='fa fa-building'></a><div class=''>" + obj[index].cdoc_coa + "</div>";
            }

        });

        div_string = div_string + "</div>";
        $("#sugerencias_clientes").append($(div_string));
    }
    else{
        $("#sugerencias_clientes").html("");
        $("#sugerencias_clientes").hide();
    }
}

function PasarCliente(cliente, codigo, direccion, ruc, ctip_doc) {
    $('#tb_clientes').val(cliente);
    $("#hdd_coa").val(codigo);
    $("#hdd_direc").val(direccion);
    $("#hdd_rucC").val(ruc);
    $("#hdd_cdsc_coa").val(cliente);
    $("#sugerencias_clientes").html("");
    $("#sugerencias_clientes").hide();

    $("#hdd_ctip_doc").val(ctip_doc);

    var objNC = $('#tabla_pago tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return { 
            NomTar: $td.eq(0).text(),
            NumTar: $td.eq(1).text(),
            UltDic: $td.eq(2).text(),
            MonTar: $td.eq(3).text()            
        }
    }).get();  
    $("#tabla_pago > tbody").html("");
    for (var i = 0; i < objNC.length; i++) { 
        if (objNC[i].NomTar == "Efectivo" ){
             $("#tabla_pago").find('tbody')
                    .append($('<tr>')
                    .append($('<td>Efectivo</td>'))
                    .append($('<td class="text-center">-</td>'))
                    .append($('<td class="text-center">-</td>'))
                    .append($('<td class="text-right montocobranza">' + objNC[i].MonTar + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalEfectivoEditar" onclick="EditarModalEfectivo(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash" onclick="EliminarFila(this)"></a></td>'))
                    .append($('<td style="display:none;">-</td>'))
                );
        }else if (objNC[i].NomTar != "Efectivo" && objNC[i].NomTar != "NotaCredito"){
             $("#tabla_pago").find('tbody')
                    .append($('<tr>')
                    .append($('<td>' + objNC[i].NomTar + '</td>'))
                    .append($('<td>' + objNC[i].NumTar + '</td>'))
                    .append($('<td>' + objNC[i].UltDic + '</td>')) 
                    .append($('<td class="text-right montocobranza">' + objNC[i].MonTar + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalTarjetaEditar" onclick="EditarModalTarjeta(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash" onclick="EliminarFila(this)"></a></td>'))
                    .append($('<td style="display:none;">-</td>')) 
                );
        }
    } 
    CalcularTotalCobranza();
}

function Cobrar(obj) {

    if($('input:radio[name=tipo]:checked').val()=="FV" && $('#hdd_coa').val() == "") 
        Mensaje('No se ha indicado un cliente.','','warning');
    
    else if ($('input:radio[name=tipo]:checked').val()=="BV" && parseFloat($("#div_total")[0].innerText)>=700 && $('#hdd_coa').val() == "")
        Mensaje('El total de la venta es mayor o igual a 700, debe indicar un cliente.','','warning');
    
    else
    { 
        if(parseFloat($("#div_faltante")[0].innerText)>0) Mensaje('Los medios de pago ingresados son insuficientes para realizar el pago','','warning');

        else
        {
            Swal.fire({
              title: "¿Estas seguro?",
              text: "No podrás revertir el cambio",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Sí, proceder'
            }).then((result) => {
              if (result.value) {

                var cantbienes = 0;

                $(".creainventario").each(function () {
                    if ($(this)[0].innerText == 'B') cantbienes = cantbienes +1;
                });

                var costo = 0;

                $(".costo").each(function () {
                    costo = parseFloat(($(this)[0].innerText).replace(',', '.')) + costo;
                });

                var objMovimientoCabecera = [
                    {
                        "cdoc": $('input:radio[name=tipo]:checked').val(),
                        "ccod_coa": $('#hdd_coa').val(),
                        "nimpuesto": $("#div_igv")[0].innerText,
                        "nisc": $("#div_isc")[0].innerText,
                        "ndescuento": $("#div_desc")[0].innerText,
                        "ntotal": $("#div_total")[0].innerText,
                        "nsubtotal": $("#div_subtotal")[0].innerText,
                        "nvuelto": $("#div_vuelto")[0].innerText,
                        "ntot_entreg": $("#div_totalcobranza")[0].innerText,
                        "costo": costo
                    }
                ]

                var objMovimientoDetalle = $('#table_Articulos tr:has(td)').map(function (i, v) {
                    var $td = $('td', this);

                    var cobser_variante_ant;
                    var cdsc_articulo_ant;

                    if($td.eq(0)[0].childNodes.length>1) {
                        cobser_variante_ant = $td.eq(0)[0].childNodes[1].innerText;
                        cdsc_articulo_ant = $td.eq(0)[0].childNodes[0].innerText;
                    }
                    else { 
                        cobser_variante_ant = '-';
                        cdsc_articulo_ant = $td.eq(0).text();
                    }

                    return {
                        id_articulo: $td.eq(6).text(),  
                        cdsc_articulo: cdsc_articulo_ant,
                        nprecio: $td.eq(2).text(),
                        ncantidad: $td.eq(1).text(),
                        nimporte_bruto: parseFloat((parseFloat(($td.eq(3).text()).replace(',', '.')) - parseFloat(($td.eq(11).text()).replace(',', '.')))), 
                        nimpuesto: $td.eq(11).text(),
                        nisc: $td.eq(12).text(),
                        ndescuento: $td.eq(15).text(),
                        nimporte_neto: $td.eq(3).text(),
                        cobser_variante: cobser_variante_ant,
                        ctip_desc: $td.eq(9).text(),
                        ctip_descn: $td.eq(16).text()
                    }
                }).get();

                var objCobranzaDetalle = $('#tabla_pago tr:has(td)').map(function (i, v) {
                    var $td = $('td', this);
                    return {
                        cnom_tarje: $td.eq(0).text(),
                        cnum_tarje: $td.eq(1).text(),
                        cnum_opera: $td.eq(2).text(),
                        nmonto: $td.eq(3).text(),
                        id_cbfact: $td.eq(6).text()
                    }
                }).get();

                $.ajax({
                    type: "POST",
                    url: 'FacturaListaPrecio.aspx/Cobrar',
                    data: JSON.stringify({ cabecera: objMovimientoCabecera, detalle: objMovimientoDetalle, cantidad_bienes: cantbienes, CobranzaDetalle : objCobranzaDetalle }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,

                    success: function (response) {

                        if (response.d == "-1") MensajeFinSession();
                        else {
                            if (response.d[0] == true) {
                                $('#lb_doc').text(response.d[1]);
                                $('#lb_total').text($("#div_total")[0].innerText);
                                $('#lb_entregado').text($("#div_totalcobranza")[0].innerText);
                                $('#lb_vuelto').text($("#div_vuelto")[0].innerText);
                                $('#modalResumenVenta').modal('show');

                                $("#hdd_id_cbfact").val("");
                                $("#hdd_id_cbfact").val(response.d[2]);
                                $("#tb_observacion").val("");
                                ArmarHtml();
                                HtmlPdf();
                            }
                        }

                        if (response.d[0] == false) 
                        {
                            if(response.d[1]!="") Mensaje('', response.d[1], 'warning');
                            else Mensaje('Error', 'No se realizó la operación', 'error');
                        }
                    
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                });

              }
            });
        }
    }
}

function FinalizarResumenDoc(){


    if( $('#ckb_Imprimir').prop('checked') ) {
        Imprimir();
    }

    $('#modalResumenVenta').modal('hide');

    Limpiar();
    LimpiarHtmlTicket();
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
        url: 'FacturaListaPrecio.aspx/RegistrarPdf',
        data: '{id_cbfact: "' + $("#hdd_id_cbfact").val() + '", pdf: "' + string+ '" }',

        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {
                
            }

            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
    
    $("#ponercanvas").html("");

}

function CambiarTarjeta(obj) {
    $('#hdd_metodopago').val(obj.value);
}

function CalcularTotalCobranza() {

    var total = 0;

    $(".montocobranza").each(function () {
        total = parseFloat(($(this)[0].innerText).replace(',', '.')) + total;
    });

    $("#div_totalcobranza")[0].innerText = parseFloat(total).toFixed(2);

    if (total>$("#hdd_total").val()){

        $("#div_faltante")[0].innerText = '0.00'
        $("#div_vuelto")[0].innerText = parseFloat(total-$("#hdd_total").val()).toFixed(2);       
    }
    else{
        $("#div_vuelto")[0].innerText = '0.00'
        $("#div_faltante")[0].innerText = parseFloat($("#hdd_total").val()-total).toFixed(2);
    }
}

function EditarPagoTarjeta() {

    var monto = 0;

    if (parseFloat($('#tb_montoeditartarjeta').val()) > parseFloat($("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML)) monto = parseFloat($("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML );
    else monto = parseFloat($('#tb_montoeditartarjeta').val())

    $("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[0].innerHTML = $("#hdd_metodopago").val();
    $("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = $("#tb_tarjetaeditar").val();
    $("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML = $("#tb_referenciaeditar").val();
    $("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML =  parseFloat(monto).toFixed(2);
    CalcularTotalCobranza();
}

function PasarPagoTarjeta() {
        
//        if ($('#tb_tarjeta').val().length != 11 && $('#tb_tarjeta').val() != "") { 
//             Mensaje('Advertencia','Ingresar los últimos 4 digitos.','warning');
//         return; 
//        }else if ($('#tb_referencia').val() != "") { 
//         Mensaje('Advertencia','Ingresar el número de referencia.','warning');
//         return; 
//        }



    var monto = 0;

//    if(parseFloat($("#div_totalcobranza")[0].innerText)>0){
        if (parseFloat($('#tb_montonuevotarjeta').val()) > parseFloat($("#div_faltante")[0].innerText)) monto = parseFloat($("#div_faltante")[0].innerText);
        else monto = parseFloat($('#tb_montonuevotarjeta').val())
//    }
//    else monto = parseFloat($('#tb_montonuevotarjeta').val())


    $("#tabla_pago").find('tbody')
                    .append($('<tr>')
                    .append($('<td>' + $('#hdd_metodopago').val() + '</td>'))
                    .append($('<td>' + $('#tb_tarjeta').val() + '</td>'))
                    .append($('<td>' + $('#tb_referencia').val() + '</td>'))
                    //.append($('<td class="text-right montocobranza">' + $('#tb_montonuevotarjeta').val() + '</td>'))
                    .append($('<td class="text-right montocobranza">' + parseFloat(monto).toFixed(2) + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalTarjetaEditar" onclick="EditarModalTarjeta(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash" onclick="EliminarFila(this)"></a></td>'))
                    .append($('<td style="display:none;">-</td>')) 
                );
    $("#modalTarjetaNuevo").modal('hide'); 
    CalcularTotalCobranza();
}

function EditarModalTarjeta(row) {
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_tarjetaeditar").val(currentRow.find("td:eq(1)").text());
    $("#tb_referenciaeditar").val(currentRow.find("td:eq(2)").text());
    $("#tb_montoeditartarjeta").val(currentRow.find("td:eq(3)").text());

    var tarjeta = currentRow.find("td:eq(0)").text();

    switch (tarjeta) {
        case 'Visa':
            PagoVisa($('#img_visa'));break;
        case 'MasterCard':
            PagoMasterCard($('#img_mastercard')); break;
        case 'Diners Club':
            PagoOtraTarjeta($('#img_otra'));
            (document.getElementById("ddl_tarjetas")).selectedIndex = 
            [...(document.getElementById("ddl_tarjetas")).options].findIndex(option => option.value === ('Diners Club').trim());            
            break;
        case 'American Express':
            (document.getElementById("ddl_tarjetas")).selectedIndex = 
            [...(document.getElementById("ddl_tarjetas")).options].findIndex(option => option.value === ('American Express').trim()); 
            PagoOtraTarjeta($('#img_otra')); break;
    }


}

function EditarPagoEfectivo() {
    $("#tabla_pago")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = parseFloat($("#tb_montoeditarefectivo").val()).toFixed(2);
    CalcularTotalCobranza();
}

function EditarModalEfectivo(row) {
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_montoeditarefectivo").val(currentRow.find("td:eq(3)").text());
}

function PasarPagoEfectivo() {

    $("#tabla_pago").find('tbody')
                    .append($('<tr>')
                    .append($('<td>Efectivo</td>'))
                    .append($('<td class="text-center">-</td>'))
                    .append($('<td class="text-center">-</td>'))
                    .append($('<td class="text-right montocobranza">' + parseFloat($('#tb_montonuevoefectivo').val()).toFixed(2) + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalEfectivoEditar" onclick="EditarModalEfectivo(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash" onclick="EliminarFila(this)"></a></td>'))
                    .append($('<td style="display:none;">-</td>'))
                );
     $("#modalEfectivoNuevo").modal('hide');//ocultamos el modal   
    CalcularTotalCobranza();
}



function EliminarFila(row) {


    $(row).closest("tr").remove();
    CalcularTotalCobranza();
}

function NumeroSeguro(valor) {
    var numero = parseFloat(String(valor || '0').replace(',', '.'));
    return isNaN(numero) ? 0 : numero;
}

function CalcularImpuestoIncluido(importe, tasa, tasa_total) {
    var importe_num = NumeroSeguro(importe);
    var tasa_num = NumeroSeguro(tasa);
    var tasa_total_num = NumeroSeguro(tasa_total);
    if (tasa_total_num <= 0) tasa_total_num = tasa_num;
    if (importe_num <= 0 || tasa_num <= 0 || tasa_total_num <= 0) return 0;
    return importe_num * tasa_num / (100 + tasa_total_num);
}

function CalcularTotal() {

    var total = 0;
    var sub_total = 0;
    var igv = 0;
    var isc = 0;
    var descuento = 0;

    $(".monto").each(function () {
        sub_total = parseFloat(($(this)[0].innerText).replace(',', '.')) + sub_total;
    });

    $(".igv_por_cantidad").each(function () {
        sub_total = sub_total - parseFloat(($(this)[0].innerText).replace(',', '.'));
    });

    $(".igv_por_cantidad").each(function () {
        igv = parseFloat(($(this)[0].innerText).replace(',', '.')) + igv;
    });





    $(".isc_por_cantidad").each(function () {
        isc = parseFloat(($(this)[0].innerText).replace(',', '.')) + isc;

    });

    $(".descuento").each(function () {
        descuento = parseFloat(($(this)[0].innerText).replace(',', '.')) + descuento;
    });

    total = sub_total + igv + isc;

    $("#div_subtotal")[0].innerText = parseFloat(sub_total).toFixed(2);  

    $("#div_igv")[0].innerText = parseFloat(igv).toFixed(2);

    $("#div_isc")[0].innerText = parseFloat(isc).toFixed(2);

    $("#div_desc")[0].innerText = parseFloat(descuento).toFixed(2);

    $("#div_total")[0].innerText = parseFloat(total).toFixed(2);
    $("#hdd_total").val(parseFloat(total).toFixed(2));
}


function BuscarArticulos() {

    if ($('#tb_articulo').val().length == 15) {
     
        $.ajax({
            type: "POST",
            url: 'Facturacion.aspx/ConsultarArticulosTodos',
            data: '{texto: "' + $('#tb_articulo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if (response.d) CompletarArticulosCategoria(response.d);
                else MensajeFinSession();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    } else if ($('#tb_articulo').val().length > 2) {

        $.ajax({
            type: "POST",
            url: 'Facturacion.aspx/ConsultarArticulosTodos',
            data: '{texto: "' + $('#tb_articulo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if (response.d) CompletarArticulosCategoria(response.d);
                else MensajeFinSession();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
    } else {
        $("#div_articulos").html("");
    }

    $('.sombreado').removeClass("sombreado");
}

function Eliminar(row) {
    $(row).closest("tr").remove();
    CalcularTotal();
}

 


function Favoritos(obj) {
    $('.sombreado').removeClass("sombreado");
    $('#input_fav').addClass("sombreado");

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSCargarFavoritos',
        data: '{ccod_cblistpre: "' + $('#ddl_lpn').val() + '"}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,  
        success: function (response) {
            if (response.d) { 
                CompletarArticulosCategoria(response.d); 
            } 
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    }); 
}

function PagoOtraTarjeta(obj) {
    $('.sombreado_mp').removeClass("sombreado_mp");
    $(obj).addClass("sombreado_mp");
    $("#div_tarjetanuevo").show();
    $("#div_tarjetaeditar").show();
}

function PagoVisa(obj) {
    $('.sombreado_mp').removeClass("sombreado_mp");
    $(obj).addClass("sombreado_mp");
    $("#div_tarjetanuevo").hide();
    $("#div_tarjetaeditar").hide();
    $('#hdd_metodopago').val('Visa');
}

function PagoMasterCard(obj) {
    $('.sombreado_mp').removeClass("sombreado_mp");
    $(obj).addClass("sombreado_mp");
    $("#div_tarjetanuevo").hide();
    $("#div_tarjetaeditar").hide();
    $('#hdd_metodopago').val('MasterCard');
}



function CategoriaClick(obj) {
    $('.sombreado').removeClass("sombreado");
    $(obj).addClass("sombreado");

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSConsultarArticulosCategoria',
        data: '{id_familia: "' + obj.id + '", ccod_cblistpre: "' + $('#ddl_lpn').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) CompletarArticulosCategoria(response.d);
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

}

function CargarCategorias()
{
    var obj = llenarobjeto('FacturaListaPrecio.aspx/ConsultarCategoriasDisponibles');

    if (obj.length > 0) {

//        var color1 = Math.floor((Math.random() * 256)) + "," + Math.floor((Math.random() * 256)) + "," + Math.floor((Math.random() * 256))
//        $("#div_favoritos").append($("<input id='input_fav' value='Favoritos' class='cuadrado btn btn-sq btn-primary' onclick='Favoritos(this)' style='margin-left: 10px;font-size: 11px;' type='button'/>"));
        $("#div_favoritos").append($("<input id='input_fav' value='Favoritos' class='cuadrado btn btn-sq btn-primary' onclick='Favoritos(this)' style='margin-left: -1px;font-size: 11px;' type='button'/>"));
          $.each(obj, function (index) {
//            var color = Math.floor((Math.random() * 256)) + "," + Math.floor((Math.random() * 256)) + "," + Math.floor((Math.random() * 256))
//            $("#div_categorias").append($("<input id='" + obj[index].id_ctlin + "' value='" + (obj[index].cdsc_lin).trim() + "' style='background-color: " + obj[index].ccolor + ";margin-left: 10px;font-size: 11px;' class='cuadrado btn btn-sq btn-primary' onclick='CategoriaClick(this)' type='button'/>"));
         $("#div_categorias").append($("<tr><td id='" + obj[index].id_ctlin + "'  style='background-color: " + obj[index].ccolor + ";margin-left: 10px;font-size: 11px;vertical-align: middle;text-align: center;' class='cuadrado btn-sq btn-primary' onclick='CategoriaClick(this)' > " + (obj[index].cdsc_lin).trim() + " </td></tr><tr style='height: 5px;'></tr>"));
       
        });
    }
}

function PasarArticulo(codigo) {

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSConsultarArticuloPrecio',
        data: '{codigo: "' + codigo + '",ccod_cblistpre: "' + $('#ddl_lpn').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {
                $("#table_Articulos").find('tbody')
                    .append($('<tr>')
                    .append($('<td>' + response.d[0].cdsc_articulo + '</td>'))
                    .append($('<td>1</td>'))
                    .append($('<td>' + response.d[0].npre_uni + '</td>'))
                    .append($('<td class="monto">' + response.d[0].npre_uni + '</td>'))
                    .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalEditarCantidad" onclick="EditarCantidad(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="Eliminar(this)"></a></td>'))
                    .append($('<td style="display: none">' + (response.d[0].ccod_articulo || codigo) + '</td>'))
                    .append($('<td class="igv" style="display: none">' + response.d[0].igv + '</td>'))
                    .append($('<td class="isc" style="display: none">' + response.d[0].isc + '</td>'))
                    .append($('<td class="creainventario" style="display: none">' + response.d[0].ctip_articulo + '</td>'))
                    .append($('<td class="costo" style="display: none">' + response.d[0].npre_costo + '</td>'))
                    .append($('<td class="igv_por_cantidad" style="display: none">' + CalcularImpuestoIncluido(response.d[0].npre_uni, response.d[0].igv, NumeroSeguro(response.d[0].igv) + NumeroSeguro(response.d[0].isc)).toFixed(2) + '</td>'))
                    .append($('<td class="isc_por_cantidad" style="display: none">' + CalcularImpuestoIncluido(response.d[0].npre_uni, response.d[0].isc, NumeroSeguro(response.d[0].igv) + NumeroSeguro(response.d[0].isc)).toFixed(2) + '</td>'))
                    .append($('<td style="display: none">' + response.d[0].state + '</td>'))
                    .append($('<td style="display: none">' + response.d[0].ndes_max + '</td>'))
                    .append($('<td style="display: none" class="descuento">0</td>'))
                    .append($('<td style="display: none"></td>'))
                );

                CalcularTotal();

                $('#div_venta').scrollTop($('#div_venta').prop("scrollHeight"));


            }

            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function PasarArticuloCodigo(codigo) {

    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSConsultarArticuloPrecioCodigo',
        data: '{codigo: "' + codigo + '",ccod_cblistpre: "' + $('#ddl_lpn').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {

                if (response.d[0].id_cblistpre > 0) {

                    $("#table_Articulos").find('tbody')
                        .append($('<tr>')
                        .append($('<td>' + response.d[0].cdsc_articulo + '</td>'))
                        .append($('<td>1</td>'))
                        .append($('<td>' + response.d[0].npre_uni + '</td>'))
                        .append($('<td class="monto">' + response.d[0].npre_uni + '</td>'))
                        .append($('<td class="text-center"><a class="fa fa-pencil" data-toggle="modal" data-target="#modalEditarCantidad" onclick="EditarCantidad(this)"></a></td>'))
                        .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="Eliminar(this)"></a></td>'))
                        .append($('<td style="display: none">' + (response.d[0].ccod_articulo || codigo) + '</td>'))
                        .append($('<td class="igv" style="display: none">' + response.d[0].igv + '</td>'))
                        .append($('<td class="isc" style="display: none">' + response.d[0].isc + '</td>'))
                        .append($('<td class="creainventario" style="display: none">' + response.d[0].ctip_articulo + '</td>'))
                        .append($('<td class="costo" style="display: none">' + response.d[0].npre_costo + '</td>'))
                        .append($('<td class="igv_por_cantidad" style="display: none">' + CalcularImpuestoIncluido(response.d[0].npre_uni, response.d[0].igv, NumeroSeguro(response.d[0].igv) + NumeroSeguro(response.d[0].isc)).toFixed(2) + '</td>'))
                        .append($('<td class="isc_por_cantidad" style="display: none">' + CalcularImpuestoIncluido(response.d[0].npre_uni, response.d[0].isc, NumeroSeguro(response.d[0].igv) + NumeroSeguro(response.d[0].isc)).toFixed(2) + '</td>'))
                        .append($('<td style="display: none">' + response.d[0].state + '</td>'))
                        .append($('<td style="display: none">' + response.d[0].ndes_max + '</td>'))
                        .append($('<td style="display: none" class="descuento">0</td>'))
                        .append($('<td style="display: none"></td>'))
                    );

                    CalcularTotal();

                    $('#div_venta').scrollTop($('#div_venta').prop("scrollHeight"));

                }

                else   Mensaje('Advertencia', 'No se encontro el artículo.', 'warning');


            }

            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}


$(document).keydown(function (e) {
    
    if (e.ctrlKey && (e.which === 81)) {
        Cambiar_Cobranza();
    }

//    if (e.which === 113) {
//        editartable();
//    }
});

function Cambiar_Cobranza(){

        $('.nav-tabs li:eq(1) a').tab('show');

        $("#modal_divTotalEfectivoNuevo")[0].innerText = $("#div_total")[0].innerText;
        $("#modal_divTotalEfectivoEditar")[0].innerText = $("#div_total")[0].innerText;
        $("#modal_divTotalNuevoTarjeta")[0].innerText = $("#div_total")[0].innerText; 
        $("#modal_divTotalEditarTarjeta")[0].innerText = $("#div_total")[0].innerText;
        $("#txtITNC")[0].innerText = $("#div_total")[0].innerText;

        if (parseFloat($("#div_total")[0].innerText) > parseFloat($("#div_totalcobranza")[0].innerText))
        {
            $("#div_faltante")[0].innerText = parseFloat($("#div_total")[0].innerText - $("#div_totalcobranza")[0].innerText).toFixed(2);
            $("#div_vuelto")[0].innerText = '0.00';
        }
        else
            $("#div_faltante")[0].innerText = '0.00';
        
//        GenerarDocumento();
        ArmarHtmlPrevio();
}

function cambiocelda(x, y) {

    var id_cell = 'cell_' + x + y;

    if (typeof $('#' + id_cell).val() === "undefined") var a = '';
    else

        $("#tabla_pago")[0].rows[y + 1].cells[x].innerHTML = $('#' + id_cell).val();

}

//function editartable() { 

//    var start = document.getElementById('start');
//    start.focus();
//    start.style.backgroundColor = '#dcd5d5';
//    start.style.color = 'white';

//    function dotheneedful(sibling, x, y) {
//        if (sibling != null) {
//            start.focus();
//            start.style.backgroundColor = '';
//            start.style.color = '';
//            sibling.focus();
//            sibling.style.backgroundColor = '#dcd5d5';
////            sibling.style.color = 'white';
//            start = sibling;
//            cambiocelda(x, y);
//        }
//    }

//    document.onkeydown = checkKey;

//    function checkKey(e) {

//        var idx = start.cellIndex;
//        var idy = start.parentElement.sectionRowIndex;

//        e = e || window.event;
//        if (e.keyCode == '38') {
//            // up arrow
//            var idx = start.cellIndex;
//            var nextrow = start.parentElement.previousElementSibling;
//            if (nextrow != null) {
//                var sibling = nextrow.cells[idx];
//                dotheneedful(sibling, idx, idy);
//            }
//        } else if (e.keyCode == '40') {
//            // down arrow
//            var idx = start.cellIndex;
//            var nextrow = start.parentElement.nextElementSibling;
//            if (nextrow != null) {
//                var sibling = nextrow.cells[idx];
//                dotheneedful(sibling, idx, idy);
//            }

//            $('#start').focus();

//        } else if (e.keyCode == '37') {
//            // left arrow
//            var sibling = start.previousElementSibling;
//            dotheneedful(sibling, idx, idy);
//        } else if (e.keyCode == '39') {
//            // right arrow
//            var sibling = start.nextElementSibling;
//            dotheneedful(sibling, idx, idy);
//        } else if (e.keyCode == '13') {
//            // enter
//            var id_cell = 'cell_' + idx + idy;

//            if (idx == 0)
//                $("#tabla_pago")[0].rows[idy + 1].cells[idx].innerHTML =
//            '<select id="' + id_cell + '" class="disabled limpiar form-control moderno_tb" >' +
//            '<option value ="Efectivo">Efectivo</option>' +
//            '<option value ="Tarjeta Visa">Tarjeta Visa</option>' +
//            '<option value ="Tarjeta MasterCard">Tarjeta MasterCard</option>' +
//            '</select>';
//            else
//                $("#tabla_pago")[0].rows[idy + 1].cells[idx].innerHTML =
//                '<input id="' + id_cell + '" type="text" class="moderno_tb"/>';

//            $('#' + id_cell).focus();

//        } else if (e.keyCode == '27' || e.keyCode == '9') {
//            start.style.backgroundColor = '';
//            start.style.color = '';
//            cambiocelda(idx, idy);
//        }
//    }
//}

function MensajeTurno(){
    Swal.fire({
          title: 'No existe un turno aperturado.',
          text: 'Se debe ingresar a la opción de apertura de turno.',
          icon: 'warning',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Continuar'
        }).then((result) => {
          if (result.value) { window.location.replace("../Interfaces/Home.aspx");}
	});
}

function MensajeValidacionFacturacion(resp){
    Swal.fire({
          title: resp,
          text: '',
          icon: 'warning',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Continuar'
        }).then((result) => {
          if (result.value) { window.location.replace("../Interfaces/Home.aspx");}
	});
}

function ActualizarFavorito(id,bprefer){
    
    $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/ActualizarFavorito',
        data: '{id_articulo: "' + id + '", bprefer: "' + bprefer+ '" }',


        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {
                if(bprefer == 1) {
                    $('#' + id).attr("oncontextmenu", "clickfavoritos(this.id,1)");
                }

                if(bprefer == 0) {
                    $('#' + id).attr("oncontextmenu", "clickfavoritos(this.id,0)");
                }

                $.ajax({
                    type: "POST",
                    url: 'FacturaListaPrecio.aspx/LSCargarFavoritos',
                    data: '{ccod_cblistpre: "' + $('#ddl_lpn').val() + '"}',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,  
                    success: function (response) {
                        if (response.d) { 
                            if($('#input_fav').attr('class')=="cuadrado btn btn-sq btn-primary sombreado") CompletarArticulosCategoria(response.d);;
                        } 
                        else MensajeFinSession();
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                });

               }

            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    $('#modalfavoritos').modal('hide');
}

function clickfavoritos(id,bprefer){

    event.preventDefault();

    if(bprefer == 1) {
        $('#div_favtext')[0].innerText = "Quitar de favoritos";
        $('#div_favtext').attr("onclick", "ActualizarFavorito(" + id + ",0)");
    }

    if(bprefer == 0) {
        $('#div_favtext')[0].innerText = "Añadir a favoritos";
        $('#div_favtext').attr("onclick", "ActualizarFavorito(" + id + ",1)");
    }

    $('#modalfavoritos').modal('show');

    $('#modalfavoritos_position').attr("style", "right: " + ($(window).width()/2-event.clientX-156).toString() +"px");
    $('#modalfavoritos').css("margin-top", event.clientY-140);
}

$(document).ready(function () {
    CargarMenu();

    $('#btn_p_nuevo').hide();
    $('#btn_p_editar').hide();
    $('#btn_p_grabar').hide();
    $('#btn_p_eliminar').hide();
    $('#btn_p_back').hide();
    $('#btn_p_imprimir').hide();
     

    CargarMenu();
    $(function(){ $.switcher('input[type=checkbox]'); });

    inicar_menu_nivel3('Factura Lista Precio', '1_li_Ventas', '2_li_Ventas_Operaciones', '3_li_FacturaListaPrecio', '0');
    CargarCategorias();
    $('#input_fav').addClass("sombreado");


  $.ajax({
        type: "POST",
        url: 'FacturaListaPrecio.aspx/LSCargarFavoritos',
        data: '{ccod_cblistpre: "' + $('#ddl_lpn').val() + '"}', 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {
              CompletarArticulosCategoria(response.d);
            } 
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

     

    ClientePorDefecto();

    $("#modalConsultarClientes").draggable();
    $("#modalNCNuevo").draggable();
     
    // FIX BUG 3.2.6: bloquear submit/salto del campo al presionar Enter
    $("#tb_anadir").on('keydown keypress', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
    $("#tb_anadir").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            PasarArticuloCodigo($('#tb_anadir').val());
            $('#tb_anadir').val("");
        }
    });

    $("#modalEfectivoNuevo").on('shown.bs.modal', function(){$(this).find('#tb_montonuevoefectivo').focus();});
    $("#modalEfectivoEditar").on('shown.bs.modal', function(){$(this).find('#tb_montoeditarefectivo').focus();});
    $("#modalTarjetaNuevo").on('shown.bs.modal', function(){$(this).find('#tb_montonuevotarjeta').focus();});
    $("#modalTarjetaEditar").on('shown.bs.modal', function(){$(this).find('#tb_montoeditartarjeta').focus();});
    $("#modalEditarCantidad").on('shown.bs.modal', function(){$(this).find('#tb_cantidad').focus();});

  $('#modalEfectivoNuevo').keypress(function(e){if(e.which == 13) {PasarPagoEfectivo();$('#modalEfectivoNuevo').modal('hide');}})
  $('#modalEfectivoEditar').keypress(function(e){if(e.which == 13) {EditarPagoEfectivo();$('#modalEfectivoEditar').modal('hide');}})
  $('#modalTarjetaNuevo').keypress(function(e){if(e.which == 13) {PasarPagoTarjeta();$('#modalTarjetaNuevo').modal('hide');}})
  $('#modalTarjetaEditar').keypress(function(e){if(e.which == 13) {EditarPagoTarjeta();$('#modalTarjetaEditar').modal('hide');}})
  $('#modalEditarCantidad').keypress(function(e){if(e.which == 13) {CambiarCantidad();$('#modalEditarCantidad').modal('hide');}})

  var FactElectronica = document.getElementById("FactElectronica").textContent;
  if (FactElectronica == "Deshabilitado"){ 
      document.getElementById("rb_boleta").style.display = "none"; 
      document.getElementById("rb_factura").style.display = "none"; 
      document.getElementById("ll_boleta").style.display = "none"; 
      document.getElementById("ll_factura").style.display = "none"; 
      $('#rb_notaventa').prop('checked', true); 
  }

});

function CompletarArticulosCategoria(obj) {

    $("#div_articulos").html("");

    var div_string = "";

    if (obj.length > 0) {

        $.each(obj, function (index) {
        
            if (index == 0) {
                if(obj[index].ctip_articulo=='S') div_string = "<div class='row' style='margin-bottom: 3.5px;'><div class='col-md-3' style='padding-left: 0px; padding-right: 3.5px;'><div oncontextmenu='clickfavoritos(this.id," + obj[index].bprefer + ")' id='" + obj[index].id_articulo + "'style='border: ridge;background-color: white;height: 150px;padding: 0px;' onclick='PasarArticulo(this.id)'><article style='text-align: -webkit-center;height: 110px;'><h1><a class='precio' title='' style='background-color: #3c9bd6;'>" + obj[index].precio + "</a></h1><img style='height: initial;width: initial;max-width: -webkit-fill-available;max-height: -webkit-fill-available;' src ='data:image/png;base64," + obj[index].iimage + "' height='50' alt=''/></article><p class='cuadrado_desc' style='text-align: -webkit-center;line-height: normal;'>" + obj[index].cdsc_articulo + "</p></div></div>";
                else div_string = "<div class='row' style='margin-bottom: 3.5px;'><div class='col-md-3' style='padding-left: 0px; padding-right: 3.5px;'><div oncontextmenu='clickfavoritos(this.id," + obj[index].bprefer + ")' id='" + obj[index].id_articulo + "'style='border: ridge;background-color: white;height: 150px;padding: 0px;' onclick='PasarArticulo(this.id)'><article style='text-align: -webkit-center;height: 110px;'><h1><a class='precio' title=''>" + obj[index].precio + "</a></h1><img style='height: initial;width: initial;max-width: -webkit-fill-available;max-height: -webkit-fill-available;' src ='data:image/png;base64," + obj[index].iimage + "' height='50' alt=''/></article></article><p class='cuadrado_desc' style='text-align: -webkit-center;line-height: normal;'>" + obj[index].cdsc_articulo + "</p></div></div>";
            }
            else {
                if (index == obj.length) {
                    div_string = div_string + "</div>";
                }
                else {
                    if ((index + 1) % 4 != 0) {
                        if(obj[index].ctip_articulo=='S') div_string = div_string + "<div class='col-md-3 favoritos' style='padding-left: 0px; padding-right: 3.5px;'><div oncontextmenu='clickfavoritos(this.id," + obj[index].bprefer + ")' id='" + obj[index].id_articulo + "'style='border: ridge;background-color: white;height: 150px;' onclick='PasarArticulo(this.id)'><article style='text-align: -webkit-center;height: 110px;'><h1><a class='precio' title='' style='background-color: #3c9bd6;'>" + obj[index].precio + "</a></h1><img style='height: initial;width: initial;max-width: -webkit-fill-available;max-height: -webkit-fill-available;' src ='data:image/png;base64," + obj[index].iimage + "' height='50' alt=''/></article><p class='cuadrado_desc' style='text-align: -webkit-center;line-height: normal;'>" + obj[index].cdsc_articulo + "</p></div></div>";
                        else div_string = div_string + "<div class='col-md-3 favoritos' style='padding-left: 0px; padding-right: 3.5px;'><div oncontextmenu='clickfavoritos(this.id," + obj[index].bprefer + ")' id='" + obj[index].id_articulo + "'style='border: ridge;background-color: white;height: 150px;padding: 0px;' onclick='PasarArticulo(this.id)'><article style='text-align: -webkit-center;height: 110px;'><h1><a class='precio' title=''>" + obj[index].precio + "</a></h1><img style='height: initial;width: initial;max-width: -webkit-fill-available;max-height: -webkit-fill-available;' src ='data:image/png;base64," + obj[index].iimage + "' height='50' alt=''/></article><p class='cuadrado_desc' style='text-align: -webkit-center;line-height: normal;'>" + obj[index].cdsc_articulo + "</p></div></div>";
                    }
                    else {
                        if(obj[index].ctip_articulo=='S') div_string = div_string + "<div class='col-md-3' style='padding-left: 0px; padding-right: 3.5px;'><div oncontextmenu='clickfavoritos(this.id," + obj[index].bprefer + ")' id='" + obj[index].id_articulo + "'style='border: ridge;background-color: white;height: 150px;' onclick='PasarArticulo(this.id)'><article style='text-align: -webkit-center;height: 110px;'><h1><a class='precio' title='' style='background-color: #3c9bd6;'>" + obj[index].precio + "</a></h1><img style='height: initial;width: initial;max-width: -webkit-fill-available;max-height: -webkit-fill-available;' src ='data:image/png;base64," + obj[index].iimage + "' height='50' alt=''/></article><p class='cuadrado_desc' style='text-align: -webkit-center;line-height: normal;'>" + obj[index].cdsc_articulo + "</p></div></div></div><div class='row' style='margin-bottom: 3.5px;'>";
                        else div_string = div_string + "<div class='col-md-3' style='padding-left: 0px; padding-right: 3.5px;'><div oncontextmenu='clickfavoritos(this.id," + obj[index].bprefer + ")' id='" + obj[index].id_articulo + "'style='border: ridge;background-color: white;height: 150px;padding: 0px;' onclick='PasarArticulo(this.id)'><article style='text-align: -webkit-center;height: 110px;'><h1><a class='precio' title=''>" + obj[index].precio + "</a></h1><img style='height: initial;width: initial;max-width: -webkit-fill-available;max-height: -webkit-fill-available;'  src ='data:image/png;base64," + obj[index].iimage + "' height='50' alt=''/></article><p class='cuadrado_desc' style='text-align: -webkit-center;line-height: normal;'>" + obj[index].cdsc_articulo + "</p></div></div></div><div class='row' style='margin-bottom: 3.5px;'>";
                    }
                }
            }
        });

        div_string = div_string + "</div>";
        $("#div_articulos").append($(div_string));
    }
}

var objCliente=[];

function PasaDatosCodCliente() {

    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr'); 

    var id_cliente = $("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[0].lastChild.id;
    
    for (var i = 0; i < objCliente.length; i++) {

	    if(id_cliente == objCliente[i].id_coa ){
	        
            PasarCliente(objCliente[i].cdsc_coa, objCliente[i].id_coa.toString(), objCliente[i].cdirc_coa, objCliente[i].cdoc_coa, objCliente[i].ctip_doc);
            //PasarCliente($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[2].innerText, $("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[0].lastChild.id);	    
	    }
    }     
}


function ModalConsultarClientes() {
    var tip_doc = "";
    if($('#rb_boleta').is(':checked') == true ){
        tip_doc = "BV";
    }else if($('#rb_factura').is(':checked') == true ){
        tip_doc = "FV";
    }else if($('#rb_notaventa').is(':checked') == true ){
        tip_doc = "NV";
    }

     $('#tableVisibleConsulClientes').DataTable().destroy();  
     $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaDocumento.aspx/CargarClienteFacturar',
        data: '{tip_doc: "' + tip_doc + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            objCliente = response.d;
            DatosClienteFact = response.d; 
            $('#tableVisibleConsulClientes').DataTable({
                "pageLength": 5,
                data: DatosClienteFact,
                columns: [
                    { data: 'item', className: "dt-body-center" },
//                    { data: 'ccod_coa' },
                    { data: 'cdsc_coa' }
                ]
            });

        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
     
}
 
 function EfectivoNuevo(){
    if ($("#div_faltante").text() == '0.00') {
        Mensaje('Advertencia', 'Monto ingresado es suficiente.', 'warning');
        return; 
    } 
     
    $('#modalEfectivoNuevo').modal('show'); 
    $('#tb_montonuevoefectivo').val('');
       
 }

 function PasarPagoNC(){
    
    var fila = $("#tableNC input[name=radiob]:checked").closest('tr'); 
     
    var monto = ""; 
    if (parseFloat($("#tableNC")[0].rows[fila[0].rowIndex].cells[3].innerText) > parseFloat($("#div_faltante")[0].innerText)){
        monto = parseFloat($("#div_faltante")[0].innerText);
    } else {
        var resmonto = $("#tableNC")[0].rows[fila[0].rowIndex].cells[3].innerText; 
        monto = resmonto.replace(",", "."); 
    }
    
    var objNC = $('#tabla_pago tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return { 
                IdDocNCS: $td.eq(6).text()             
            }
    }).get();  
    for (var i = 0; i < objNC.length; i++) { 
        if (objNC[i].IdDocNCS == $("#tableNC")[0].rows[fila[0].rowIndex].cells[0].innerText ){
            Mensaje('Advertencia', 'La Nota de cretido ('+$("#tableNC")[0].rows[fila[0].rowIndex].cells[4].innerText+') ya esta seleccionada.', 'warning');
            return; 
        } 
    } 


    $("#tabla_pago").find('tbody')
        .append($('<tr>')
        .append($('<td>NotaCredito</td>'))
        .append($('<td class="text-center">-</td>'))
        .append($('<td class="text-center">-</td>'))
        .append($('<td class="text-right montocobranza">' + parseFloat(monto).toFixed(2) + '</td>'))
        .append($('<td class="text-center"><a class="fa fa-pencil"  ></a></td>'))
        .append($('<td class="text-center"><a class="fa fa-trash" onclick="EliminarFila(this)"></a></td>'))
        .append($('<td style="display:none;">' + $("#tableNC")[0].rows[fila[0].rowIndex].cells[0].innerText + '</td>'))
    ); 
     $("#modalNCNuevo").modal('hide');//ocultamos el modal    
    CalcularTotalCobranza(); 
}


 function NuevoNC() {

    if ($("#div_faltante").text() == '0.00') {
        Mensaje('Advertencia', 'Monto ingresado es suficiente.', 'warning');
        return; 
    }else if ($('#hdd_coa').val() == '') {
        Mensaje('Advertencia', 'Ingresar cliente.', 'warning');
        return; 
    }else if ($('#hdd_coa').val() == null) {
        Mensaje('Advertencia', 'Ingresar cliente.', 'warning');
        return; 
    }   
     
    $('#modalNCNuevo').modal('show'); 
   

    $("#tableNC > tbody").html("");
    $.ajax({
        type: "POST",
        url: '../Ventas/FacturaListaPrecio.aspx/BuscarNCIdCliente',
        data: '{id_coa: "' +  $('#hdd_coa').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
            var obj = response.d;  
            for (var i = 0; i < obj.length; i++) {
                var k = parseInt(i) + 1;
                $("#tableNC").find('tbody')
                .append($('<tr>')
                .append($('<td style="display:none;">' + obj[i].id_cbfact + '</td>'))
                .append($('<td class="text-center">' + obj[i].Doc + '</td>'))
                .append($('<td>' + obj[i].dfch_doc + '</td>'))
                .append($('<td>' + obj[i].nimp_aplicado + '</td>'))
                .append($('<td>' + obj[i].cdoc + '</td>'))
                .append($('<td style="display:none;">' + k + '</td>'))
                );
                
            }

            }else{ MensajeFinSession()};
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
     

    var objNC = $('#tabla_pago tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return { 
                IdDocNCS: $td.eq(6).text()             
            }
    }).get(); 
    var objListNC = $('#tableNC tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return { 
                IdDocNCN: $td.eq(0).text(),
                Orden: $td.eq(5).text()        
                       
            }
    }).get(); 
    for (var i = 0; i < objNC.length; i++) {
        for (var k = 0; k < objListNC.length; k++) {
            if (objNC[i].IdDocNCS == objListNC[k].IdDocNCN ){
                $("#tableNC tr:nth-child("+objListNC[k].Orden+")").css('background', 'silver'); 
            }
        }
    } 
}

$(document).keyup(function(e) {
  if (e.keyCode === 27) {
    $("#sugerencias_clientes").html("");
    $("#sugerencias_clientes").hide();
  }
});



function llenarobjetoParametros(st_url) {

    var obj;

    $.ajax({
        type: "POST",
        url: st_url,
        data: null,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d == "-1") MensajeFinSession();
            else obj = response.d;
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    return obj;
}

function CargarTabla(){

    var parameters;

    $('#table_id').DataTable({
            
        processing: true,
        serverSide: true,

        ajax: {
                type: "POST",
                async: false,
                contentType: "application/json; charset=utf-8",
                url: 'FacturaListaPrecio.aspx/ConsultarUsuarios',
                data: function (d) {
                    return JSON.stringify({ parameters: d });
                }
        },

//        data: llenarobjeto('Facturacion.aspx/ConsultarUsuarios'),
//            
//        columns: [
//                    { data: 'ccod_usuario' },
//                    { data: 'cdsc_usuario' },
//                    { data: 'cdirec' },
//                    { data: 'cdsc_rol' },
//                    { data: 'cdsc_tienda' },
//                    { data: 'estado' }
//            ]

    });


    $('#table_id').attr("style", "width: -webkit-fill-available;");
}

