
var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

 var objAlmacenSalida=[];
 var objAlmacenIngreso=[];

 var objAlmacen=[];

 var objNumerador=[];



  function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

//    if($("#NombreColumna").val() == "ddl_tienda"){
//        DscTabla = "al_cbinve";
//        DscColumna = "ccod_tienda";
//        Nombre = "Código de la tienda";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "dp_fecha"){
//        DscTabla = "al_cbinve";
//        DscColumna = "dfecha";
//        Nombre = "Fecha del movimiento";
//        Estado = "Obligatorio";
//        TipoDato = "";
//    }else if($("#NombreColumna").val() == "ddl_almacenOrig"){
//        DscTabla = "al_cbinve";
//        DscColumna = "ccod_alm";
//        Nombre = "Código del almacen de origen";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "ddl_tipOperSalida"){
//        DscTabla = "al_cbinve";
//        DscColumna = "ctipo";
//        Nombre = "Código de operación de origen";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "tb_serieOrigen"){
//        DscTabla = "al_cbinve";
//        DscColumna = "cserie";
//        Nombre = "Serie de origen";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "tb_numOrigen"){
//        DscTabla = "al_cbinve";
//        DscColumna = "nnumero";
//        Nombre = "Numerador de origen";
//        Estado = "Opcional";
//        TipoDato = ""; 
//    }else if($("#NombreColumna").val() == "ddl_almacenDest"){
//        DscTabla = "al_cbinve";
//        DscColumna = "ccod_alm";
//        Nombre = "Código del almacen de destino";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "ddl_tipOperIngreso"){
//        DscTabla = "al_cbinve";
//        DscColumna = "ctipo";
//        Nombre = "Código de operación de destino";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "tb_serieDest"){
//        DscTabla = "al_cbinve";
//        DscColumna = "cserie";
//        Nombre = "Serie de destino";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "tb_numDest"){
//        DscTabla = "al_cbinve";
//        DscColumna = "nnumero";
//        Nombre = "Numerador de destino";
//        Estado = "Opcional";
//        TipoDato = "";
//    }else if($("#NombreColumna").val() == "tb_observacion"){
//        DscTabla = "al_cbinve";
//        DscColumna = "cobservacion";
//        Nombre = "Observación";
//        Estado = "Opcional";
//        TipoDato = "1 hasta"; 
//    }else if($("#NombreColumna").val() == "tb_cod" || $("#NombreColumna").val() == "tb_cod_editar" ){
//        DscTabla = "al_lninve";
//        DscColumna = "ccod_articulo";
//        Nombre = "Código de artículo";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "tb_articulo" || $("#NombreColumna").val() == "tb_articulo_editar" ){
//        DscTabla = "al_articulo";
//        DscColumna = "cdsc_articulo";
//        Nombre = "Nombre de artículo";
//        Estado = "Obligatorio";
//        TipoDato = "1 hasta";
//    }else if($("#NombreColumna").val() == "tb_cantActual"   ){
//        DscTabla = "al_lninve";
//        DscColumna = "ncantidad";
//        Nombre = "Cantidad actual de artículo";
//        Estado = "Obligatorio";
//        TipoDato = "";
//    }else if($("#NombreColumna").val() == "tb_cantidad" || $("#NombreColumna").val() == "tb_cantidad_editar" ){
//        DscTabla = "al_lninve";
//        DscColumna = "ncantidad";
//        Nombre = "Cantidad de artículo";
//        Estado = "Obligatorio";
//        TipoDato = "";
//    }else if($("#NombreColumna").val() == "tb_costo" || $("#NombreColumna").val() == "tb_costo_editar" ){
//        DscTabla = "al_lninve";
//        DscColumna = "ncosto";
//        Nombre = "Costo de artículo";
//        Estado = "Obligatorio";
//        TipoDato = "";
//    }  
//      

//    for (var i = 0; i < objColumnas.length; i++) {
//        if(DscColumna == objColumnas[i].DscColumna && DscTabla == objColumnas[i].DscTabla){
//            $("#txt_nombreCampo").text(Nombre);
//            $("#txt_TipoDato").text(objColumnas[i].TipoDato);
//            $("#txt_estado").text(Estado);
//            $("#txt_longitud").text(TipoDato +" "+objColumnas[i].longitud);
//            $("#txt_cantidadEntero").text(objColumnas[i].CantEnteros);
//            $("#txt_cantidadDecimales").text(objColumnas[i].CantDecimales);
//        }
//    }
      
 }

 function DescargarArchivoPDF(row) { 

    $.ajax({
        type: "POST",
        url: 'ReporteTributario.aspx/DescargarArchivoPDF',
        data: '{codigo: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
                var obj = response.d; 
                var a = document.createElement("a");
                a.href = 'data:application/octet-stream;base64,' + obj[0].ipdf_datpos;
                a.download = obj[0].cdoc + '-' +obj[0].cdoc_serie + '-' +obj[0].cdoc_nro + '.pdf';
                a.click();
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < objTributario.length; i++) { 
//        if(row.id == objTributario[i].id_cbfact ){ 
//            var a = document.createElement("a");
//            a.href = 'data:application/octet-stream;base64,' + objTributario[i].ipdf_datpos;
//            a.download = objTributario[i].cdoc + '-' +objTributario[i].cdoc_serie + '-' +objTributario[i].cdoc_nro + '.pdf';
//            a.click();
//        }
//    }    
};

function DescargarArchivoXML(row) { 

        $.ajax({
        type: "POST",
        url: 'ReporteTributario.aspx/DescargarArchivoXML',
        data: '{codigo: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
                var obj = response.d; 
                var a = document.createElement("a");
                a.href = 'data:application/octet-stream;base64,' + obj[0].contentxml;
                a.download = obj[0].cdoc + '-' +obj[0].cdoc_serie + '-' +obj[0].cdoc_nro + '.xml';
                a.click();
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < objTributario.length; i++) { 
//        if(row.id == objTributario[i].id_cbfact ){ 
//            var a = document.createElement("a");
//            a.href = 'data:application/octet-stream;base64,' + objTributario[i].contentxml;
//            a.download = objTributario[i].cdoc + '-' +objTributario[i].cdoc_serie + '-' +objTributario[i].cdoc_nro + '.xml';
//            a.click();
//        }
//    }    
};

function DescargarArchivoXMLCDR(row) { 
    
    $.ajax({
        type: "POST",
        url: 'ReporteTributario.aspx/DescargarArchivoXMLCDR',
        data: '{codigo: "' + row.id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d){
                var obj = response.d; 
                var a = document.createElement("a");
                a.href = 'data:application/octet-stream;base64,' + obj[0].contentzipcdr;
                a.download = obj[0].cdoc + '-' +obj[0].cdoc_serie + '-' +obj[0].cdoc_nro + '.xmlcdr';
                a.click();
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

//    for (var i = 0; i < objTributario.length; i++) { 
//        if(row.id == objTributario[i].id_cbfact ){ 
//            var a = document.createElement("a");
//            a.href = 'data:application/octet-stream;base64,' + objTributario[i].contentxml;
//            a.download = objTributario[i].cdoc + '-' +objTributario[i].cdoc_serie + '-' +objTributario[i].cdoc_nro + '.xmlcdr';
//            a.click();
//        }
//    }    
};


function ObtenerNumerador() { 

    var listBox = document.getElementById("txtccod_guia");
    listBox.options.length = 0;
 
    $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/ObtenerNumerador',
        data: '{tipo: "' + "RT" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
            var obj = response.d;
                for (var i = 0; i < obj.length; i++) {
                    if (obj[i].cstatus == '1'){ 
                        var objAl =   {
                        cdoc_tipo: obj[i].cdoc_tipo,
                        cdoc_serie: obj[i].cdoc_serie,
                        cstatus: obj[i].cstatus } 
                        objNumerador.push(objAl);
                    }
                }
                    $('#txtccod_guia').append('<option  class="disabled" disabled value=""></option>'); 
                for (var i = 0; i < objNumerador.length; i++) {
                    $('#txtccod_guia').append('<option  class="disabled" disabled value="' + objNumerador[i].cdoc_tipo + '">'+ objNumerador[i].cdoc_tipo +'</option>'); 
                } 
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
      
}


 function ConsultarOperaciones(tipo) {
    
    var obj = llenarobjeto('GuiaRemision.aspx/ConsultarOperaciones');

    var listBox = document.getElementById("ddl_tipOperSalida");
    listBox.options.length = 0;

    var listBox = document.getElementById("ddl_tipOperIngreso");
    listBox.options.length = 0;

    if (obj.length > 0){
        for (var i = 0; i < obj.length; i++) {
            if (tipo == "ACTIVO"){
                if (obj[i].cstatus == '1' && obj[i].ctipo_flag == 'I'){ 
                var objAl =   {
                                id_ctoper: obj[i].id_ctoper,
                                ccod_toper: obj[i].ccod_toper,
                                cdsc_toper: obj[i].cdsc_toper,
                                ctipo_flag: obj[i].ctipo_flag,
                                ctipo_transferencia: obj[i].ctipo_transferencia,
                                cstatus: obj[i].cstatus      
                            } 
                objAlmacenIngreso.push(objAl);
                }else if (obj[i].cstatus == '1' && obj[i].ctipo_flag == 'S'){ 
                    var objAl =   {
                                    id_ctoper: obj[i].id_ctoper,
                                    ccod_toper: obj[i].ccod_toper,
                                    cdsc_toper: obj[i].cdsc_toper,
                                    ctipo_flag: obj[i].ctipo_flag,
                                    ctipo_transferencia: obj[i].ctipo_transferencia,
                                    cstatus: obj[i].cstatus      
                                } 
                    objAlmacenSalida.push(objAl);
                } 
            }else{
                if (obj[i].ctipo_flag == 'I'){ 
                var objAl =   {
                                id_ctoper: obj[i].id_ctoper,
                                ccod_toper: obj[i].ccod_toper,
                                cdsc_toper: obj[i].cdsc_toper,
                                ctipo_flag: obj[i].ctipo_flag,
                                ctipo_transferencia: obj[i].ctipo_transferencia,
                                cstatus: obj[i].cstatus      
                            } 
                objAlmacenIngreso.push(objAl);
                }else if (obj[i].ctipo_flag == 'S'){ 
                    var objAl =   {
                                    id_ctoper: obj[i].id_ctoper,
                                    ccod_toper: obj[i].ccod_toper,
                                    cdsc_toper: obj[i].cdsc_toper,
                                    ctipo_flag: obj[i].ctipo_flag,
                                    ctipo_transferencia: obj[i].ctipo_transferencia,
                                    cstatus: obj[i].cstatus      
                                } 
                    objAlmacenSalida.push(objAl);
                } 
            }
             
        } 
    }
        $('#ddl_tipOperIngreso').append('<option  class="disabled" disabled value=""></option>'); 
    for (var i = 0; i < objAlmacenIngreso.length; i++) {
        $('#ddl_tipOperIngreso').append('<option  class="disabled" disabled value="' + objAlmacenIngreso[i].ccod_toper + '">(' + objAlmacenIngreso[i].ccod_toper + ') '+ objAlmacenIngreso[i].cdsc_toper +'</option>'); 
    }

         $('#ddl_tipOperSalida').append('<option  class="disabled" disabled value=""></option>'); 
    for (var i = 0; i < objAlmacenSalida.length; i++) {
        $('#ddl_tipOperSalida').append('<option  class="disabled" disabled value="' + objAlmacenSalida[i].ccod_toper + '">(' + objAlmacenSalida[i].ccod_toper + ') '+ objAlmacenSalida[i].cdsc_toper +'</option>'); 
    }

     
}

function PasaDatosCodCoa() {
    var fila = $("#tableBuscadorCoa input[name=radiob]:checked").closest('tr');

    if($('#TipoCoa').val() == "Remitente"){
      
        $('#IdRemitente').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[1].innerText); 
        $('#txtnom_rzn_soc_rem').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[2].innerText); 
    }else if($('#TipoCoa').val() == "Destino"){
//        $('#IdDestino').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[0].lastChild.id);
        $('#IdDestino').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[1].innerText); 
        $('#txtnom_rzn_soc_dest').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[2].innerText); 
    }else if($('#TipoCoa').val() == "Proveedor"){
        $('#ccod_coa').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[0].lastChild.id);
//        $('#IdProveedor').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[0].lastChild.id);
        $('#IdProveedor').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[1].innerText); 
        $('#txtnom_rzn_soc_prov').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[2].innerText); 
    }else if($('#TipoCoa').val() == "Transportista"){
//        $('#IdTransportista').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[0].lastChild.id);
        $('#IdTransportista').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[1].innerText); 
        $('#txtnom_rzn_trans').val($("#tableBuscadorCoa")[0].rows[fila[0].rowIndex].cells[2].innerText); 
    } 
      
}



function AbrirModalCoa(row) {

    var TipoCoa = "";
    if(row.id == "Remitente"){
        TipoCoa = '1'
    }else if(row.id == "Destino"){
        TipoCoa = '1'
    }else if(row.id == "Proveedor"){
        TipoCoa = '0'
    }else if(row.id == "Transportista"){
        TipoCoa = '3'
    }
    $('#TipoCoa').val(row.id);
    $('#modalBuscadorCoa').modal('show');
    $('#tableBuscadorCoa').DataTable().destroy();
    $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/ConsultarCodigoAuxiliar',
        data: '{cproveedor: "'+ TipoCoa +'" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) {

          var obj = response.d;

          $('#tableBuscadorCoa').DataTable({
            "lengthMenu": [5],
            data: obj,
            columns: [ 
                    { data: 'item' }, 
                    { data: 'cruc_coa' },
                    { data: 'cdsc_coa' }
                ]
        });  
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    }); 
}

 


// Convierte una fecha en formato DD/MM/YYYY (o YYYY-MM-DD) al formato
// YYYY-MM-DD que requieren los <input type="date">. Si el formato es
// desconocido se devuelve el string original.
function _normFechaInputDate(s) {
    if (s == null) return '';
    var t = String(s).trim();
    if (!t) return '';
    var m = t.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})/);
    if (m) {
        return m[3] + '-' + ('0'+m[2]).slice(-2) + '-' + ('0'+m[1]).slice(-2);
    }
    m = t.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) return m[1] + '-' + m[2] + '-' + m[3];
    return t;
}

function CompletarCampos(obj){

   CargarAlmacenes("");
   ConsultarOperaciones("");

   // Persistir id_cbinve para el flujo de Guardar: si > 0 al momento del
   // submit, el backend ejecutara webDatpos_ActualizarGuia en lugar de un
   // INSERT (que antes generaba un registro duplicado). #operacion sera
   // 'editar' cuando el usuario clic en el boton Editar (ver Comun.js).
   $('#hdd_id_cbinve').val(obj[0].id_cbinve || 0);

    $('#txtccod_guia').append('<option  class="disabled" disabled value="'+ obj[0].ccod_guia +'">'+ obj[0].ccod_guia +'</option>');
    (document.getElementById("txtccod_guia")).selectedIndex =
    [...(document.getElementById("txtccod_guia")).options].findIndex(option => option.value === (obj[0].ccod_guia).trim());

   $("#txtcserie_guia").val(obj[0].cserie_guia);
   $("#txtcnro_guia").val(obj[0].cnro_guia);

   (document.getElementById("txtCodDocumento")).selectedIndex =
   [...(document.getElementById("txtCodDocumento")).options].findIndex(option => option.value === (obj[0].cod_tip_cpe).trim());

   $("#txtnom_rzn_soc_dest").val(obj[0].cnom_rzn_soc_dest);
   $("#txtnom_rzn_soc_prov").val(obj[0].cdsc_coa);
   // FIX 74 / BUG 2.15: el API ya devuelve los RUC pero CompletarCampos no
   // los asignaba al formulario, por lo que el usuario veia los campos
   // RUC vacios al pasar de Lista -> Datos.
   // IdRemitente es <span> (RUC de la empresa, viene de la sesion); no se
   // sobreescribe. IdDestino e IdProveedor son <input>.
   if (obj[0].cnum_ruc_dest) $("#IdDestino").val(obj[0].cnum_ruc_dest);
   if (obj[0].cnum_ruc_proy) $("#IdProveedor").val(obj[0].cnum_ruc_proy);
   // #txtdfecha es <input type="date"> y solo acepta YYYY-MM-DD. La API
   // devuelve DD/MM/YYYY -> antes la asignacion silenciosamente fallaba y
   // el campo se mostraba vacio ("la fecha se resetea").
   $("#txtdfecha").val(_normFechaInputDate(obj[0].dfecha));
   $("#txtdfec_fin").val(_normFechaInputDate(obj[0].dfec_fin));
   $("#txtcdoc_ref").val(obj[0].cdoc_ref);
    
    (document.getElementById("ddl_almacenOrig")).selectedIndex = 
    [...(document.getElementById("ddl_almacenOrig")).options].findIndex(option => option.value === (obj[0].ccod_alm).trim());  

    $("#txtDircOrig").val(obj[0].cdomicilio_partida);
    $("#txtUbigeoOrig").val(obj[0].ccod_ubi_partida);

    (document.getElementById("ddl_tipOperSalida")).selectedIndex = 
    [...(document.getElementById("ddl_tipOperSalida")).options].findIndex(option => option.value === (obj[0].ctipo).trim());  

    $("#txtSerieOrig").val(obj[0].cserie);
    $("#txtNumeroOrig").val(obj[0].nnumero);

    (document.getElementById("ddl_almacenDest")).selectedIndex = 
    [...(document.getElementById("ddl_almacenDest")).options].findIndex(option => option.value === (obj[0].ccod_alm_ing).trim());  
     
    $("#txtDircDest").val(obj[0].cdomicilio_llegada);
    $("#txtUbigeoDest").val(obj[0].ccod_ubi_llegada);

    (document.getElementById("ddl_tipOperIngreso")).selectedIndex = 
    [...(document.getElementById("ddl_tipOperIngreso")).options].findIndex(option => option.value === (obj[0].ctipo_ing).trim());  

    $("#txtSerieDest").val(obj[0].cserie_ing);
    $("#txtNumeroDest").val(obj[0].nnumero_ing);
    $("#txtnom_rzn_trans").val(obj[0].ctrans_nombre);
    $("#txtLicencia").val(obj[0].ctrans_licencia);
    $("#txtplaca").val(obj[0].ctrans_placa);
    $("#txtmnt_tot_peso_bruto").val(obj[0].nmnt_tot_peso_bruto);
    $("#txtcod_unid_peso_bruto").val(obj[0].ccod_unid_peso_bruto);
    $("#txtdesc_motiv_tras").val(obj[0].cdesc_motiv_tras);
    $("#txtnobs").val(obj[0].nobs);

    CargarDetalles(obj[0].id_cbinve);
      
}



function CargarDetalles(id) {

    $("#tabla > tbody").html("");
 
// $('#tableTransferencia').DataTable().destroy();
    var obj;

    $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/ObtenerDetalleGuiaRemision',
        data: '{id: "' + id + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            obj = response.d;

            for (var i = 0; i < obj.length; i++) {
                $("#tabla").find('tbody')
                .append($('<tr>')
                .append($('<td style="display:none;">' + obj[i].id_lninve + '</td>'))
                .append($('<td style="display:none;">0</td>'))
                .append($('<td>' + obj[i].ccod_articulo + '</td>'))
                .append($('<td>' + obj[i].ccod_artSunat + '</td>'))
                .append($('<td>' + obj[i].cdsc_articulo + '</td>')) 
                .append($('<td>' + obj[i].ncantidad + '</td>'))
                .append($('<td>' + obj[i].ncosto + '</td>'))
                .append($('<td class="text-center"><a class="fa fa-pencil fa_disabled" data-toggle="modal" data-target="#modalEditar" onclick="EditarModal(this)"></a></td>'))
                .append($('<td class="text-center"><a class="fa fa-trash fa_disabled" onclick="EliminarFila(this)"></a></td>'))
                );                    
            }
            
            
            
        },

        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function Imprimir(tbody) {

    var obj = [
        { 
            "id_cbinve": tbody.id,
            "ilogo": LogoEmpresa
        }
    ]

    $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/InformeGuiaRemision', 
        data: JSON.stringify({ ReporteGuiaRemision: obj }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            window.open((window.DATPOS_BASE_PATH||'')+'/pages/Reportes/InformeGuiaRemision.php', '_blank');  
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function tab_datosclick() {
    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'GuiaRemision.aspx/ObtenerGuiaRemision',
                data: '{id_cbinve: "' + $('#hdd_ultimafila').val() + '" }',
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
        Desabilitar();
    }
    var tblRows = $('#table_id')[0] ? $('#table_id')[0].rows : [];
    if (tblRows.length > 1 && tblRows[1].cells[1] && tblRows[1].cells[1].innerText ==  $('#hdd_ultimafila').val()){
        $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');  
        $("#table_id tr:nth-child("+1+")").css('background', 'silver'); 
         
        $('#hdd_fila').val(1);

        $(".limpiar_checked").removeAttr("checked");
        $("#"+$('#hdd_id_cbinve').val()).prop('checked', true);
    }
}

 
function table_two_click(tbody) {

     $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', ''); 
    var index = tbody.ondblclick.arguments[0].target.parentElement.rowIndex; 
    $("#table_id tr:nth-child("+index+")").css('background', 'silver'); 
    $('#hdd_fila').val(index);
     
    var fila = tbody.ondblclick.arguments[0].target.parentElement.cells;
    $(".limpiar_checked").removeAttr("checked");
    $("#"+fila[1].innerText).prop('checked', true);
   
    
   $('#hdd_ultimafila').val(fila[1].innerText);

    if($('#hdd_numerofilas').val()>0){
        $.ajax({
            type: "POST",
            url: 'GuiaRemision.aspx/ObtenerGuiaRemision',
            data: '{id_cbinve: "' + fila[1].innerText + '" }',
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
} 


function Guardar() {
    
    if(navigator.onLine) {

    if ($('#txtccod_guia').val() == "") {
        Mensaje('Advertencia','Ingresar Documento de Guia de Remetente.','warning');
         return;
    }else if ($('#txtccod_guia').val() == null) {
        Mensaje('Advertencia','Ingresar Documento de Guia de Remetente.','warning');
         return;
    }else if ($('#txtnom_rzn_soc_dest').val() == "") {
        Mensaje('Advertencia','Ingresar Destinatario.','warning');
         return;
    }else if ($('#txtnom_rzn_soc_prov').val() == "") {
        Mensaje('Advertencia','Ingresar Proveedor.','warning');
         return; 
//    }else if ($('#txtdfecha').val() == "") {
//        Mensaje('Advertencia','Ingresar Fecha de Partida.','warning');
//         return;
    }else if ($('#txtdfec_fin').val() == "") {
        Mensaje('Advertencia','Ingresar Fecha de Llegada.','warning');
         return;
//    }else if ($('#ddl_almacenOrig').val() == "") {
//        Mensaje('Advertencia','Ingresar Almacén de Origen.','warning');
//         return; 
//    }else if ($('#ddl_almacenOrig').val() == null) {
//        Mensaje('Advertencia','Ingresar Almacén de Origen.','warning');
//         return; 
//    }else if ($('#ddl_tipOperSalida').val() == "") {
//        Mensaje('Advertencia','Ingresar Operación de Salida de Almacén.','warning');
//         return;
//    }else if ($('#ddl_tipOperSalida').val() == null) {
//        Mensaje('Advertencia','Ingresar Operación de Salida de Almacén.','warning');
//         return;
    }else if ($('#txtnom_rzn_trans').val() == null) {
        Mensaje('Advertencia','Ingresar Transportista.','warning');
         return; 
    }else if ($('#txtplaca').val() == null) {
        Mensaje('Advertencia','Ingresar Placa.','warning');
         return;
    }else if ($('#txtcod_unid_peso_bruto').val() == null) {
        Mensaje('Advertencia','Ingresar Codigo de Unidad.','warning');
         return;
    }else if ($('#txtmnt_tot_peso_bruto').val() == null) {
        Mensaje('Advertencia','Ingresar Peso Bruto Total.','warning');
         return;
    }else if ($('#txtmnt_tot_peso_bruto').val() == null) {
        Mensaje('Advertencia','Ingresar Peso Bruto Total.','warning');
         return;
    }                  

    
    var objTransladoDetalle = $('#tabla tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            ccod_articulo: $td.eq(2).text(),
            ccod_artSunat: $td.eq(3).text(),
            cdsc_articulo: $td.eq(4).text(),
            ncantidad: $td.eq(5).text(),
            ncosto: $td.eq(6).text()
        }
    }).get();

    if(objTransladoDetalle.length <= 0){
        Mensaje('Advertencia','Ingresar articulos para continuar.','warning');
        return; 
    }
      
    var TotalCosto = 0; 
    $.ajax({  
        type: "POST",
        url: 'GuiaRemision.aspx/TotalInventario',
        data: JSON.stringify({ obj: objTransladoDetalle }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            var  objDA = response.d;
            TotalCosto = objDA.ntotal; 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    var listCodArticulo = "";

    for (var i = 0; i < objTransladoDetalle.length; i++) {  
            listCodArticulo = listCodArticulo +  objTransladoDetalle[i].ccod_articulo+";"+objTransladoDetalle[i].ncantidad+";";  
    }


    var objInventario = [
        {
            "ccod_alm": $('#ddl_almacenOrig').val(),
            "listArticulo": listCodArticulo
        }
    ]

    var objTranslado = [{ 
            "ccod_coa": $('#ccod_coa').val(),
            "ccod_empresa": $('#txtccod_empresa').text(),
            "ccod_guia": $('#txtccod_guia').val(),
            "cserie_guia": $('#txtcserie_guia').val(),
            "cnom_rzn_soc_rem": $('#txtnom_rzn_soc_rem').text(),
            "cnum_ruc_rem": $('#IdRemitente').text(),
            "cnom_rzn_soc_dest": $('#txtnom_rzn_soc_dest').val(),
            "cnum_ruc_dest": $('#IdDestino').val(),
            "cdsc_coa": $('#txtnom_rzn_soc_prov').val(),
            "cnum_ruc_proy": $('#IdProveedor').val(), 
            "cdomicilio_partida": $('#txtDircOrig').val(),
            "ccod_ubi_partida": $('#txtUbigeoOrig').val(),
            "cdomicilio_llegada": $('#txtDircDest').val(),
            "ccod_ubi_llegada": $('#txtUbigeoDest').val(),
            "ctrans_nombre": $('#txtnom_rzn_trans').val(),
            "ctrans_ruc": $('#IdTransportista').val(), 
            "ccod_unid_peso_bruto": $('#txtcod_unid_peso_bruto').val(), 
            "nmnt_tot_peso_bruto": $('#txtmnt_tot_peso_bruto').val(), 
            "cdesc_motiv_tras": $('#txtdesc_motiv_tras').val(), 
            "nobs": $('#txtnobs').val(), 
            "ctrans_placa": $('#txtplaca').val(), 
            "ctrans_licencia": $('#txtLicencia').val(), 
            "ntotal": parseFloat(TotalCosto).toFixed(2) , 
            "cusu_crea": $('#txtcusu_crea').text(), 
            "ccod_almOrigen": $('#ddl_almacenOrig').val(), 
            "ctipoOrigen": $('#ddl_tipOperSalida').val(), 
            "cserieOrigen": $('#txtSerieOrig').val(), 
            "ccod_almDestino": $('#ddl_almacenDest').val(), 
            "ctipoDestino": $('#ddl_tipOperIngreso').val(), 
            "cserieDestino": $('#txtSerieDest').val(),
            "dfec_fin": $('#txtdfec_fin').val(),
            "cdoc_ref": $('#txtcdoc_ref').val(),
            "cod_tip_cpe": $('#txtCodDocumento').val(),
            "dfecha": $('#txtdfecha').val(),
            "ccod_cliente_emis": $('#ccod_cliente_emis').text(),
            "ctoken": $('#ctoken').text()          
        }
    ]

  

    $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/ValidarListArticulo',
        data: JSON.stringify({ inventario: objInventario}),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d == "-1") MensajeFinSession();
            else {
                obj = response.d;
                if (obj.length > 0){ 
                 
                $('#tbLisArticuloError').DataTable().destroy();
                $('#tbLisArticuloError').DataTable({
                    data: obj,
                    columns: [
                            { data: 'cdsc_articulo' },
                            { data: 'ncantidad' },
                            { data: 'ncantidad_actual' },
                            { data: 'ncantidad_faltante' }
                        ]
                }); 
               
                 $('#exampleModalCenter').modal('show'); 


                }else{

    // Modo: editar si venimos de un doble-click + boton Editar
    // (#hdd_id_cbinve fue seteado por CompletarCampos y #operacion='editar'
    // por Editar() en Comun.js). Cuando es editar, el backend ejecuta
    // webDatpos_ActualizarGuia sobre la cabecera; si es nuevo genera el
    // INSERT como siempre.
    var _idEditar    = parseInt($('#hdd_id_cbinve').val() || '0', 10);
    var _modoGuardar = ($('#operacion').val() === 'editar' && _idEditar > 0) ? 'editar' : 'nuevo';
    if (_modoGuardar !== 'editar') _idEditar = 0;

    $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/Guardar',
        data: JSON.stringify({
            CabTranslado: objTranslado,
            LnTranslado:  objTransladoDetalle,
            operacion:    $('#operacion_select').val(),
            modo:         _modoGuardar,
            id_cbinve:    _idEditar
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d == "-1") MensajeFinSession();
            else {
                obj = response.d;
                 if(response.d[1] == 'OK'){

                    Mensaje('Correcto','','success'); 
                    CargarTabla();
                    if($('#hdd_numerofilas').val()>0){
                                $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                            }
                    Desabilitar();
                    Deshacer();
                   $('.nav-tabs li:eq(2) a').tab('show');
                }  
            }

            if (response.d == false) Mensaje('Error', 'No se realizó la operación', 'error');
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

function EliminarFila(row) {
    $(row).closest("tr").attr("style", "display: none;");
    $(row).closest("tr")[0].children[1].innerHTML = '3';
}

function EditarFila() {
    if ($('#tb_cantidad_editar').val() == "") {
       Mensaje('Advertencia','Ingresar cantidad.','warning');
       return;
    }else if ($('#tb_costo_editar').val() == "") {
       Mensaje('Advertencia','Ingresar costo.','warning');
       return;
    }else if($('#ddl_almacenOrig').val() == ""){
        Mensaje('Advertencia','Ingrese almacen.','warning');
        return;
    }  
     var objArticuloCantaArti = [
        {
            "ccod_articulo": $('#tb_cod_editar').val(),
            "ncantidad": $('#tb_cantidad_editar').val(),
            "ccod_alm": $('#ddl_almacenOrig').val()
        }
    ]
 
 $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/VerificarCantaArti',
        data: JSON.stringify({ ArticuloCantaArti: objArticuloCantaArti }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) { 
            if (response.d == "-1") MensajeFinSession();
            else {
                obj = response.d;
                  if(obj[0].ccod_articulo == 'OK'){

                    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = '2';
                    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML = $("#tb_cod_editar").val();
                    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = $("#tb_articulo_editar").val(); 
                    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[4].innerHTML = $("#tb_cantidad_editar").val();
                    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[5].innerHTML =  parseFloat($("#tb_costo_editar").val()).toFixed(2);
     
                    $("#modalEditar").modal('hide');//ocultamos el modal
                    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
                    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
                }else {
                    Mensaje('Advertencia','El saldo actual del articulo ('+$('#tb_cod_editar').val()+') es de '+obj[0].ccod_articulo,'warning');
                    return;
                }
            }
             
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function EditarModal(row) {
    if ($('#ddl_almacenOrig').val() == "") {
       Mensaje('Advertencia','Ingresar Almacen.','warning');
         return;
    }

    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_cod_editar").val(currentRow.find("td:eq(2)").text());
    $("#tb_articulo_editar").val(currentRow.find("td:eq(3)").text()); 
    $("#tb_cantidad_editar").val(currentRow.find("td:eq(4)").text());
    $("#tb_costo_editar").val(parseFloat(currentRow.find("td:eq(5)").text()).toFixed(2));
}

function InsertarFila() {

if($('#tb_cod').val() == ""){
        Mensaje('Advertencia','Ingrese código de articulo.','warning');
        return;
 }else if($('#tb_codSunat').val() == ""){
        Mensaje('Advertencia','EL artículo debe tener código Sunat.','warning');
        return;
 }else if($('#tb_cantidad').val() == ""){
        Mensaje('Advertencia','Ingrese cantidad de articulo.','warning');
        return;
 }else if($('#tb_costo').val() == ""){
        Mensaje('Advertencia','Ingrese costo de articulo.','warning');
        return;
 }else if($('#ddl_almacenOrig').val() == ""){
        Mensaje('Advertencia','Ingrese almacen.','warning');
        return;
 }
  
 var objArticuloCantaArti = [
        {
            "ccod_articulo": $('#tb_cod').val(),
            "ncantidad": $('#tb_cantidad').val(),
            "ccod_alm": $('#ddl_almacenOrig').val()
        }
    ]

 $.ajax({
        type: "POST",
        url: 'GuiaRemision.aspx/VerificarCantaArti',
        data: JSON.stringify({ ArticuloCantaArti: objArticuloCantaArti }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) { 
            if (response.d == "-1") MensajeFinSession();
            else {
                obj = response.d;
                  if(obj[0].ccod_articulo == 'OK'){

                        $("#tabla").find('tbody')
                        .append($('<tr>')
                        .append($('<td style="display:none;">0</td>'))
                        .append($('<td style="display:none;">1</td>'))
                        .append($('<td>' + $('#tb_cod').val() + '</td>'))
                        .append($('<td>' + $('#tb_codSunat').val() + '</td>'))
                        .append($('<td>' + $('#tb_articulo').val() + '</td>')) 
                        .append($('<td>' + $("#tb_cantidad").val() + '</td>'))
                        .append($('<td>' + parseFloat($("#tb_costo").val()).toFixed(2) + '</td>'))
                        .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditar" onclick="EditarModal(this)"></a></td>'))
                        .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFila(this)"></a></td>'))
                        );
                    $("#modalnuevo").modal('hide');//ocultamos el modal
                    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
                    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
                }else {
                    Mensaje('Advertencia','El saldo actual del articulo ('+$('#tb_cod').val()+') es de '+obj[0].ccod_articulo,'warning');
                    return;
                }
            }
             
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

}

function PasarArticulo() {
    var fila = $("#table_Articulos input[name=radiob]:checked").closest('tr');
    $('#tb_articulo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[3].innerText);
    $('#tb_codSunat').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[2].innerText);
    $('#tb_cod').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
    $('#tb_cantActual').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[4].innerText);
    $('#tb_costo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[5].innerText);
}

function ModalArticulos() {

 $('#table_Articulos').DataTable().destroy();

      $.ajax({
            type: "POST",
            url: 'GuiaRemision.aspx/ConsultarArticulosSalida',
            data: '{almacen: "' + $('#ddl_almacenOrig').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) { 
                 obj = response.d;
 
            $('#table_Articulos').DataTable({
                iDisplayLength: 5,
                data: obj,
                columns: [
                        { data: 'cbx',
                          render: function (data, type, row) {
                          if (type === 'display') { return '<input type="radio" name="radiob">'; }
                          return data;},className: "dt-body-center"},
                        { data: 'ccod_articulo' },
                        { data: 'ccod_artSunat' },
                        { data: 'cdsc_articulo' }, 
                        { data: 'ncantidad' },
                        { data: 'ncosto' }]
            });

    },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function NuevoModal() {

    if(navigator.onLine) {
    
        if ($('#ddl_almacenOrig').val() == "") {
           Mensaje('Advertencia','Ingresar Almacen.','warning');
             return;
        }else if ($('#ddl_almacenOrig').val() == null) {
           Mensaje('Advertencia','Ingresar Almacen.','warning');
             return;
        }


        $('#modalnuevo').modal('show');
        $("#tb_cod").val("");
        $("#tb_codSunat").val("");
        $("#tb_articulo").val("");
        $("#tb_cantActual").val("");
        $("#tb_cantidad").val("");
        $("#tb_costo").val("");

        } else {
         Mensaje('Error','Sin acceso a internet.','error');
    }
}

function Nuevo() {
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
  
   
     $("#tabla > tbody").html("");
    $(".readonl").prop("readonly", false);
    $(".disabled").prop("disabled", false);
    $(".limpiar").val("");
    $("#operacion").val("nuevo");
    // Limpiar id de edicion: Guardar usa este flag (junto con #operacion)
    // para decidir entre INSERT y UPDATE de la cabecera.
    $("#hdd_id_cbinve").val(0);

    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    //    Fecha actual sugerida
    var date = new Date() 
    var day = date.getDate()
    var month = date.getMonth() + 1
    var year = date.getFullYear() 
    if(month < 10){
      var month = '0'+month
    } 
    if(day < 10){
      var day = '0'+day
    }
    // #txtdfecha es <input type="date"> y solo acepta YYYY-MM-DD. Antes
    // se asignaba DD/MM/YYYY y el campo quedaba vacio sin error visible.
    $("#txtdfecha").val(year+'-'+month+'-'+day);
    $("#txtdfec_fin").val(year+'-'+month+'-'+day);
 
 (document.getElementById("txtccod_guia")).selectedIndex = 
    [...(document.getElementById("txtccod_guia")).options].findIndex(option => option.value === ""); 
 (document.getElementById("txtCodDocumento")).selectedIndex = 
    [...(document.getElementById("txtCodDocumento")).options].findIndex(option => option.value === "");  
  (document.getElementById("ddl_almacenOrig")).selectedIndex = 
    [...(document.getElementById("ddl_almacenOrig")).options].findIndex(option => option.value === "");  
 (document.getElementById("ddl_tipOperSalida")).selectedIndex = 
    [...(document.getElementById("ddl_tipOperSalida")).options].findIndex(option => option.value === "");  
 (document.getElementById("ddl_almacenDest")).selectedIndex = 
    [...(document.getElementById("ddl_almacenDest")).options].findIndex(option => option.value === "");  
   (document.getElementById("ddl_tipOperIngreso")).selectedIndex = 
    [...(document.getElementById("ddl_tipOperIngreso")).options].findIndex(option => option.value === "");  

    // Auto-llenar Destinatario y Tercero con datos de la empresa
    AutoLlenarDestinatario();
}

// Auto-llenar campos de Destinatario y Tercero con datos de la empresa
// (en traslado entre almacenes, el remitente y destinatario son la misma empresa)
function AutoLlenarDestinatario() {
    var ruc = $('#IdRemitente').text();
    var rzn = $('#txtnom_rzn_soc_rem').text();
    $('#IdDestino').val(ruc);
    $('#txtnom_rzn_soc_dest').val(rzn);
    $('#IdProveedor').val(ruc);
    $('#txtnom_rzn_soc_prov').val(rzn);
    // Convencion: Coa.ccod_coa = RUC SUNAT. Aseguramos que el Coa del propio
    // remitente exista para que FK_CbGuia_Coa no falle.
    $('#ccod_coa').val(ruc);
    if (ruc) { EnsureCoaByRuc(ruc, rzn, '', '2'); }
}

/**
 * Upsert idempotente de un Coa con ccod_coa = RUC SUNAT. Llama al endpoint
 * PHP `Clientes.aspx/EnsureCoaByRuc` que ejecuta `webDatpos_EnsureCoaByRuc`.
 * Sirve para garantizar que la FK FK_CbGuia_Coa este satisfecha al guardar.
 *
 * @param {string} ruc           RUC (o DNI) que se usara como ccod_coa
 * @param {string} razon_social  Razon social devuelta por SUNAT (opcional)
 * @param {string} direccion     Domicilio fiscal (opcional)
 * @param {string} cproveedor    '1'=Cliente '0'=Proveedor '2'=Otros '3'=Transportista
 */
function EnsureCoaByRuc(ruc, razon_social, direccion, cproveedor) {
    var rucClean = (ruc || '').toString().trim();
    if (rucClean === '') return;
    $.ajax({
        type: "POST",
        url: '../Ventas/Clientes.aspx/EnsureCoaByRuc',
        data: JSON.stringify({
            ruc: rucClean,
            razon_social: razon_social || '',
            direccion: direccion || '',
            cproveedor: cproveedor || '2'
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function () { /* upsert silencioso */ },
        error: function () {
            // No interrumpimos al usuario; el guardado podra fallar mas adelante
            // y mostrar un mensaje claro.
            if (window.console) console.warn('EnsureCoaByRuc fallo para RUC ' + rucClean);
        }
    });
}

/**
 * Consultar SUNAT para un RUC y completar el campo de Razón Social ligado.
 * - Para RUC (11 dígitos) llama al servicio externo apifacturador.msgsac.net.
 * - Para DNI (8 dígitos) sólo se valida formato; el usuario debe escribir la
 *   Razón Social manualmente porque el servicio no expone DNIs.
 * @param {string} idCampoDoc   id del input con el RUC/DNI
 * @param {string} idCampoNom   id del input con la razón social a completar
 */
function ConsultarRucSunat(idCampoDoc, idCampoNom, opciones) {
    opciones = opciones || {};
    var doc = ($('#' + idCampoDoc).val() || '').trim();
    if (doc === '') return;

    if (/^\d{8}$/.test(doc)) {
        // DNI: solo validacion de formato. Igual aseguramos el Coa con codigo=DNI
        // si se pidio (por defecto si hay tipoCoa).
        if (opciones.tipoCoa) {
            EnsureCoaByRuc(doc, ($('#' + idCampoNom).val() || ''), '', opciones.tipoCoa);
            if (opciones.setCcodCoa) $('#ccod_coa').val(doc);
        }
        return;
    }
    if (!/^\d{11}$/.test(doc)) {
        if (typeof Mensaje === 'function') {
            Mensaje('Advertencia','El documento debe tener 11 dígitos (RUC) u 8 dígitos (DNI).','warning');
        }
        return;
    }
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/DatosRucApi',
        data: JSON.stringify({ ruc: doc }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            var razon = '';
            var direc = '';
            if (response && response.d) {
                var obj = response.d;
                if (Array.isArray(obj)) obj = obj[0] || {};
                if (obj && obj.nombre_o_razon_social) {
                    razon = obj.nombre_o_razon_social;
                    $('#' + idCampoNom).val(razon);
                }
                if (obj && obj.domicilio_fiscal) {
                    direc = obj.domicilio_fiscal;
                    // Si el campo de direccion esta vinculado al destino y esta vacio, llenarlo.
                    if (idCampoNom === 'txtnom_rzn_soc_dest'
                        && ($('#txtDircDest').val() || '').trim() === '') {
                        $('#txtDircDest').val(direc);
                    }
                }
                // Ubigeo (6 digitos INEI) -> Ubigeo Llegada para el destinatario
                if (obj && obj.ubigeo && idCampoNom === 'txtnom_rzn_soc_dest'
                    && ($('#txtUbigeoDest').val() || '').trim() === '') {
                    $('#txtUbigeoDest').val(obj.ubigeo);
                }
            }
            // Asegura el Coa para que la FK FK_CbGuia_Coa no falle al guardar.
            if (opciones.tipoCoa) {
                EnsureCoaByRuc(doc, razon || ($('#' + idCampoNom).val() || ''), direc, opciones.tipoCoa);
                if (opciones.setCcodCoa) $('#ccod_coa').val(doc);
            }
        },
        error: function () {
            if (typeof Mensaje === 'function') {
                Mensaje('Advertencia','No se pudo consultar el RUC en SUNAT. Verifique conexión.','warning');
            }
        }
    });
}

// Handlers especificos para cada campo RUC/DNI de la guia de remision.
// tipoCoa: '1'=Cliente (Destinatario), '0'=Proveedor/Tercero, '3'=Transportista.
// setCcodCoa: si true, copia el RUC al hidden #ccod_coa que viaja al SP.
function BuscarDatosRucDestino()       { ConsultarRucSunat('IdDestino',       'txtnom_rzn_soc_dest', { tipoCoa: '1', setCcodCoa: true }); }
function BuscarDatosRucTercero()       { ConsultarRucSunat('IdProveedor',     'txtnom_rzn_soc_prov', { tipoCoa: '0', setCcodCoa: true }); }
function BuscarDatosRucTransportista() { ConsultarRucSunat('IdTransportista', 'txtnom_rzn_trans',    { tipoCoa: '3', setCcodCoa: false }); }

// Handler cuando cambia el Modo de traslado
function SelecModo() {
    var modo = $('#operacion_select').val();
    if (modo === '04') {
        // Translado entre almacenes: destinatario = misma empresa
        AutoLlenarDestinatario();
    } else {
        // Otros modos: limpiar para que el usuario busque manualmente
        $('#IdDestino').val('');
        $('#txtnom_rzn_soc_dest').val('');
        $('#IdProveedor').val('');
        $('#txtnom_rzn_soc_prov').val('');
    }
}

 

function SelecNumerador() { 
    for (var i = 0; i < objNumerador.length; i++) {
        if( $('#txtccod_guia').val() == objNumerador[i].cdoc_tipo){
            $('#txtcserie_guia').val(objNumerador[i].cdoc_serie); 
        } 
    }
}


function SelecAlmacenDestino() { 
    for (var i = 0; i < objAlmacen.length; i++) {
        if( $('#ddl_almacenDest').val() == objAlmacen[i].ccod_alm){
            $('#txtDircDest').val(objAlmacen[i].cdirc_almac);
            $('#txtUbigeoDest').val(objAlmacen[i].cubigeo);
            $('#txtSerieDest').val(objAlmacen[i].cserieDest); 
        }else if( $('#ddl_almacenDest').val() == ""){
            $('#txtDircDest').val(""); 
            $('#txtUbigeoDest').val(""); 
            $('#txtSerieDest').val(""); 
        }
    }
}


function SelecAlmacenOrigen() { 
    for (var i = 0; i < objAlmacen.length; i++) {
        if( $('#ddl_almacenOrig').val() == objAlmacen[i].ccod_alm){
            $('#txtDircOrig').val(objAlmacen[i].cdirc_almac);
            $('#txtUbigeoOrig').val(objAlmacen[i].cubigeo);
            $('#txtSerieOrig').val(objAlmacen[i].cserieOrig); 
        }else if( $('#ddl_almacenOrig').val() == ""){
            $('#txtDircOrig').val(""); 
            $('#txtUbigeoOrig').val(""); 
            $('#txtSerieOrig').val(""); 
        }
    }
}


function CargarAlmacenes(tipo) { 

     var obj = llenarobjeto('GuiaRemision.aspx/ConsultarAlamcenes');

    var listBoxOrigen = document.getElementById("ddl_almacenOrig");
    listBoxOrigen.options.length = 0;

    var listBoxDesctino = document.getElementById("ddl_almacenDest");
    listBoxDesctino.options.length = 0;


    if (obj.length > 0){
        for (var i = 0; i < obj.length; i++) {
            if (tipo == "ACTIVO"){ 
                if (obj[i].cstatus == '1'){ 
                    var objAl =   {
                        id_ctalmac : obj[i].id_ctalmac,
                        ccod_alm : obj[i].ccod_alm,
                        cdsc_alm : obj[i].cdsc_alm,
                        cdirc_almac : obj[i].cdirc_almac,
                        cubigeo : obj[i].cubigeo,
                        cstatus : obj[i].cstatus,
                        cserieDest : obj[i].cserieDest,
                        cserieOrig : obj[i].cserieOrig   
                    } 
                    objAlmacen.push(objAl);
                }
            }else{
                var objAl =   {
                        id_ctalmac : obj[i].id_ctalmac,
                        ccod_alm : obj[i].ccod_alm,
                        cdsc_alm : obj[i].cdsc_alm,
                        cdirc_almac : obj[i].cdirc_almac,
                        cubigeo : obj[i].cubigeo,
                        cstatus : obj[i].cstatus,
                        cserieDest : obj[i].cserieDest,
                        cserieOrig : obj[i].cserieOrig   
                    } 
                    objAlmacen.push(objAl);
            } 
       } 
       
    }

        $('#ddl_almacenOrig').append('<option  class="disabled" disabled value=""></option>'); 
    for (var i = 0; i < objAlmacen.length; i++) {
        $('#ddl_almacenOrig').append('<option  class="disabled" disabled value="' + objAlmacen[i].ccod_alm + '">(' + objAlmacen[i].ccod_alm + ') '+ objAlmacen[i].cdsc_alm +'</option>'); 
    }

        $('#ddl_almacenDest').append('<option  class="disabled" disabled value=""></option>'); 
    for (var i = 0; i < objAlmacen.length; i++) {
        $('#ddl_almacenDest').append('<option  class="disabled" disabled value="' + objAlmacen[i].ccod_alm + '">(' + objAlmacen[i].ccod_alm + ') '+ objAlmacen[i].cdsc_alm +'</option>'); 
    }

}

function CargarNumeradorIngreso() {
var ff = $('#ddl_almacenDest').val();
 if($('#ddl_almacenDest').val() != '') {
  $.ajax({
            type: "POST",
            url: 'GuiaRemision.aspx/ConsultarNumerador',
            data: '{almacen: "' + $('#ddl_almacenDest').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                obj = response.d;
                if(obj.length<1){
                    Mensaje('Advertencia','El numerador del almacen no esta configurado o no es valido.\n\nConfigure el numerador de almacen para continuar.','warning'); 
                }else{
                    $('#tb_serieDest').val(obj[0].cserie); 
                }
       
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    }else{
        $('#tb_serieDest').val("");
        $('#tb_numDest').val("");
    }
}

 
 

function CargarNumeradorSalida() {
 if($('#ddl_almacenOrig').val() != '') {
  $.ajax({
            type: "POST",
            url: 'GuiaRemision.aspx/ConsultarNumeradorSalida',
            data: '{almacen: "' + $('#ddl_almacenOrig').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                obj = response.d;
                if(obj.length<1){
                 Mensaje('Advertencia','El numerador del almacen no esta configurado o no es valido.\n\nConfigure el numerador de almacen para continuar.','warning');


                }else{
                $('#tb_serieOrigen').val(obj[0].cserie); 
                }
                 
       
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    }else{
        $('#tb_serieOrigen').val("");
        $('#tb_numOrigen').val("");
    }
}

 function checked_click(row) {
    $(".limpiar_checked").removeAttr("checked");
    $(row).prop('checked', true);
     var currentRow = $(row).closest("tr"); 
    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
        $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', ''); 
    $("#table_id tr:nth-child("+currentRow[0].rowIndex+")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);
 }
  
function CargarTabla() {

 $('#tableTransferencias').DataTable().destroy(); 
    $('#table_id').DataTable().destroy();

    var obj = llenarobjeto('GuiaRemision.aspx/ConsultarGuiaRemision');
    
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
     "ordering": false,
        data: obj, 
        columns: [
                { data: 'item' },
                { data: 'id_cbinve' },
                { data: 'ctipo' },
                { data: 'cod_tip_cpe' },
                { data: 'ccod_alm' },
                { data: 'cdomicilio_partida' },
                { data: 'ccod_alm_ing' },
                { data: 'cdomicilio_llegada' },
                { data: 'dfecha' },
                { data: 'cdoc_ref' },
                { data: 'guia' } 
                  
            ]
    });
    $('#tableTransferencias').DataTable({
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
                { data: 'id_cbinve' },
                { data: 'ctipo' },
                { data: 'cod_tip_cpe' },
                { data: 'ccod_alm' },
                { data: 'cdomicilio_partida' },
                { data: 'ccod_alm_ing' },
                { data: 'cdomicilio_llegada' },
                { data: 'dfecha' },
                { data: 'cdoc_ref' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

  $('#table_id').attr("style", "width:100%");


}

$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas();

 $("#modalArticulos").draggable();
  
$("#modalnuevo").draggable();

$("#modalEditar").draggable();

$("#ModalDatosPersonales").draggable();

$("#thTablaDetalleTransfer").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            
            $('#tableTransferencia').DataTable().destroy();
            var obj = $('#tabla tr:has(td)').map(function(i, v) {
            var $td =  $('td', this);
                return {
                            ccod_articulo: $td.eq(2).text(),
                            cdsc_articulo: $td.eq(3).text(),
                            csim_unidadmedida: $td.eq(4).text(),
                            ncantidad: $td.eq(5).text(),
                            ncosto: $td.eq(6).text()
                        }
            }).get();

            $('#tableTransferencia').DataTable({
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
                { data: 'ncosto' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
            var blob = new Blob([document.getElementById('tableTransferenciaExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

$("#thTablaTransferencias").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

   $('.nav-tabs li:eq(2) a').tab('show');
   inicar_menu_nivel3('Guía Remisión', '1_li_Almacen', '2_li_Operaciones', '3_li_GuiaRemision', '2');
     
   CargarAlmacenes("ACTIVO");
   ConsultarOperaciones("ACTIVO");
   CargarTabla();
    ObtenerNumerador();

    $("#tb_cod").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) { 
            $.ajax({
                type: "POST",
                url: 'GuiaRemision.aspx/ValidarArticuloAlmacenSalida',
                data: '{ccod_articulo: "' + $('#tb_cod').val() + '",ccod_alm: "' + $('#ddl_almacenOrig').val() + '"  }',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,

                success: function (response) {
                    if (response.d) {
                        
                        if (response.d.length >0){ 
                             $('#tb_articulo').val(response.d[0].cdsc_articulo);
                             $('#tb_cantActual').val(response.d[0].ncantidad);
                             $('#tb_costo').val(response.d[0].ncosto);
                             $('#tb_codSunat').val(response.d[0].ccod_artSunat);
                             $("#tb_cantidad").focus();
                        }else{
                             Mensaje('No se encontro el código del artículo ingresado.','','warning');
                             $('#tb_articulo').val("");
                             $('#tb_costo').val("");
                             $('#tb_cantActual').val("");
                             $('#tb_codSunat').val("");
                        }
                    }else{
                        Mensaje('Error','El artículo no esta registrado o no tiene saldo suficiente.','error');
                    }
                     
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            });

        }
    });

      if($('#hdd_numerofilas').val()>0){
     $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
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
    $("#txtdfec_fin").datepicker();
});
