
$(document).ready(function () {
    ArmarHtml('2694');

    var objLogo = llenarobjeto('../Interfaces/Home.aspx/CargarFotoUsuario');
    if (objLogo.length > 0) {
        document.getElementById("idlogoTicket").src = "data:image/png;base64," + objLogo[0].ilogo;
    }

    $('#btn_p_nuevo').hide();
    $('#btn_p_editar').hide();
    $('#btn_p_grabar').hide();
    $('#btn_p_eliminar').hide();
    $('#btn_p_back').hide();
    $('#btn_p_imprimir').hide();

   
});

 

function ArmarHtml(idfact) {

    $("#zona-imprimir").append('<head><link href="/Styles/css/bootstrap.css" rel="stylesheet" type="text/css"></head>'); //va aqui para no causar conflicto con el bootstrap ya declarado, luego se borrara
    $("#nombre_empresa1")[0].innerText = ($('#hhd_empresa').val()).trim();
    $("#ruc_empresa")[0].innerText = "RUC: " + ($('#hdd_ruc').val()).trim();
    $("#direccion_empresa")[0].innerText = ($('#hhd_direccionE').val()).trim();
    $("#direccionubigeo_empresa")[0].innerText = ($('#hhd_ubigeoE').val()).trim();

    $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/DatosReferencia',
        data: '{id_cbfact: "' + idfact + '", id_cbinve: "' + '0' + '"}',
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
                $('#DicSerieNro').text(response.d[31] + " - " + response.d[32]);
                 
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });


    $.ajax({
        type: "POST",
        url: '../Consultas/ConsultaOperAlmacen.aspx/ConsultaListArticulosPorId',
        data: '{id_cbfact: "' + idfact + '"  }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var obj = response.d;
                $("#tbArticulo > tbody").html("");
                for (var i = 0; i < obj.length; i++) {
                    $("#tbArticulo").find('tbody')
                .append($('<tr>')
                 .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;" >' + obj[i].ncantidad + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nprecio + '</td>'))
                .append($('<td style="padding: 5px;border: solid 1px #b99090;text-align: right;" >' + obj[i].nimpuesto + '</td>'))
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

    cadena_qr = ($('#hdd_ruc').val()).trim() + "|";

    switch ($('input:radio[name=tipo]:checked').val()) {
        case "FV": cadena_qr = cadena_qr + "01" + "|"; break;
        case "BV": cadena_qr = cadena_qr + "03" + "|"; break;
        case "NV": cadena_qr = cadena_qr + "07" + "|"; break;
    }

//    var doc = ((($('#lb_doc').text()).replace(' ', '|')).split("|", 2)[1]).replace(' ', '|');

    cadena_qr = cadena_qr + 'FV' + "|" + '58' + "|" + '258' + "|" + '20' + "|" +
    'FSV' + "|" + '2545856252' + "|";

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


}