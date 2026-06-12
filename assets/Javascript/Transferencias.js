 

var almacenesList = [];
var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

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
//    }else 
    if($("#NombreColumna").val() == "dp_fecha"){
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha del movimiento";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "ddl_almacenOrig"){
        DscTabla = "al_cbinve";
        DscColumna = "ccod_alm";
        Nombre = "Código del almacen de origen";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_tipOperSalida"){
        DscTabla = "al_cbinve";
        DscColumna = "ctipo";
        Nombre = "Código de operación de origen";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_serieOrigen"){
        DscTabla = "al_cbinve";
        DscColumna = "cserie";
        Nombre = "Serie de origen";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_numOrigen"){
        DscTabla = "al_cbinve";
        DscColumna = "nnumero";
        Nombre = "Numerador de origen";
        Estado = "Opcional";
        TipoDato = ""; 
    }else if($("#NombreColumna").val() == "ddl_almacenDest"){
        DscTabla = "al_cbinve";
        DscColumna = "ccod_alm";
        Nombre = "Código del almacen de destino";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_tipOperIngreso"){
        DscTabla = "al_cbinve";
        DscColumna = "ctipo";
        Nombre = "Código de operación de destino";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_serieDest"){
        DscTabla = "al_cbinve";
        DscColumna = "cserie";
        Nombre = "Serie de destino";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_numDest"){
        DscTabla = "al_cbinve";
        DscColumna = "nnumero";
        Nombre = "Numerador de destino";
        Estado = "Opcional";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_observacion"){
        DscTabla = "al_cbinve";
        DscColumna = "cobservacion";
        Nombre = "Observación";
        Estado = "Opcional";
        TipoDato = "1 hasta"; 
    }else if($("#NombreColumna").val() == "tb_cod" || $("#NombreColumna").val() == "tb_cod_editar" ){
        DscTabla = "al_lninve";
        DscColumna = "ccod_articulo";
        Nombre = "Código de artículo";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_articulo" || $("#NombreColumna").val() == "tb_articulo_editar" ){
        DscTabla = "al_articulo";
        DscColumna = "cdsc_articulo";
        Nombre = "Nombre de artículo";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_cantActual"   ){
        DscTabla = "al_lninve";
        DscColumna = "ncantidad";
        Nombre = "Cantidad actual de artículo";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_cantidad" || $("#NombreColumna").val() == "tb_cantidad_editar" ){
        DscTabla = "al_lninve";
        DscColumna = "ncantidad";
        Nombre = "Cantidad de artículo";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_costo" || $("#NombreColumna").val() == "tb_costo_editar" ){
        DscTabla = "al_lninve";
        DscColumna = "ncosto";
        Nombre = "Costo de artículo";
        Estado = "Obligatorio";
        TipoDato = "";
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

function CompletarCampos(obj){ 

   $("#dp_fecha").val(obj[0].dfecha);
//   (document.getElementById("ddl_tienda")).selectedIndex = 
//    [...(document.getElementById("ddl_tienda")).options].findIndex(option => option.value === (obj[0].cdsc_tienda).trim());
   
   CargarAlmacenes();

//    document.getElementById("tb_serieOrigen").innerHTML = obj[0].vserieDestino; 
//    document.getElementById("tb_numOrigen").innerHTML = obj[0].nnumeroDestino;  
//    document.getElementById("tb_serieDest").innerHTML = obj[0].vserieOrigen; 
//    document.getElementById("tb_numDest").innerHTML = obj[0].nnumeroOrigen; 
    $("#tb_serieOrigen").val(obj[0].vserieDestino);
    $("#tb_numOrigen").val(obj[0].nnumeroDestino);
    $("#tb_serieDest").val(obj[0].vserieOrigen);
    $("#tb_numDest").val(obj[0].nnumeroOrigen);
     
    (document.getElementById("ddl_almacenOrig")).selectedIndex = 
    [...(document.getElementById("ddl_almacenOrig")).options].findIndex(option => option.value === (obj[0].cdsc_almOrigen).toString());
   
    (document.getElementById("ddl_tipOperSalida")).selectedIndex = 
    [...(document.getElementById("ddl_tipOperSalida")).options].findIndex(option => option.value === (obj[0].ctipoOrigen).toString());
    
    (document.getElementById("ddl_almacenDest")).selectedIndex = 
    [...(document.getElementById("ddl_almacenDest")).options].findIndex(option => option.value === (obj[0].cdsc_almDestino).toString());
    
     (document.getElementById("ddl_tipOperIngreso")).selectedIndex = 
    [...(document.getElementById("ddl_tipOperIngreso")).options].findIndex(option => option.value === (obj[0].ctipoDestino).toString());

     $("#hdd_id_cbinve").val(obj[0].id_cbinve);
    CargarDetalles(obj[0].id_cbinve);
    ActualizarDropdownsAlmacen();
      
}



function CargarDetalles(id) {

    $("#tabla > tbody").html("");
 
// $('#tableTransferencia').DataTable().destroy();
    var obj;

    $.ajax({
        type: "POST",
        url: 'Transferencias.aspx/ConsultarInventarioDetalle',
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


function tab_datosclick() {
    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Transferencias.aspx/ConsultarTransferencia',
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
             
        }

        Desabilitar();
    }
    if ($('#table_id')[0].rows[1].cells[1].innerText ==  $('#hdd_ultimafila').val()){
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
            url: 'Transferencias.aspx/ConsultarTransferencia',
            data: '{codigo: "' + fila[1].innerText + '" }',
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

//     if ($('#ddl_tienda').val() == "") {
//       Mensaje('Advertencia','Ingresar tienda.','warning');
//        return; 
//    }else 
    if ($('#dp_fecha').val() == "") {
        Mensaje('Advertencia','Ingresar fecha.','warning');
         return;
    }else if ($('#ddl_almacenOrig').val() == "") {
        Mensaje('Advertencia','Ingresar almacen de origen.','warning');
         return;
    }else if ($('#ddl_tipOperSalida').val() == "") {
        Mensaje('Advertencia','Ingresar tipo de operación de salida.','warning');
         return; 
    }else if ($('#tb_serieOrigen').val() == "") {
        Mensaje('Advertencia','La serie origen del almacén no esta configurado o no es valido.\n\nConfigure el numerador de almacen para continuar.','warning');
         return;
    }else if ($('#ddl_almacenDest').val() == "") {
        Mensaje('Advertencia','Ingresar almacen de destino.','warning');
         return;
    }else if ($('#ddl_tipOperIngreso').val() == "") {
        Mensaje('Advertencia','Ingresar tipo de operación de ingreso.','warning');
         return;
    }else if ($('#tb_serieDest').val() == "") {
        Mensaje('Advertencia','La serie destino del almacén no esta configurado o no es valido.\n\nConfigure el numerador de almacen para continuar.','warning');
         return; 
    }   

    
    var objTransladoDetalle = $('#tabla tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            id_lninve: $td.eq(0).text(),
            state: $td.eq(1).text(),
            ccod_articulo: $td.eq(2).text(),
            ncantidad: $td.eq(4).text(),
            ncosto: $td.eq(5).text()
        }
    }).get();

    if(objTransladoDetalle.length <= 0){
        Mensaje('Advertencia','Ingresar articulos a la lista de articulos.','warning');
        return; 
    }
      
     var TotalCosto = 0; 
    $.ajax({  
        type: "POST",
        url: 'Salida.aspx/TotalInventario',
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
        if (objTransladoDetalle[i].state != '3' ){
            listCodArticulo = listCodArticulo+  objTransladoDetalle[i].ccod_articulo+";"+objTransladoDetalle[i].ncantidad+";"; 
        }
    }

      if(listCodArticulo == ""){
        Mensaje('Advertencia','Ingresar articulos a la lista de articulos.','warning');
        return; 
    }

    var objInventario = [
        {
            "ccod_alm": $('#ddl_almacenOrig').val(),
            "listArticulo": listCodArticulo
        }
    ]

    var objTranslado = [
        {
            "ccod_tienda": "",
            "dfecha": $('#dp_fecha').val(),
            "ccod_almOrigen": $('#ddl_almacenOrig').val(),
            "ctipoOrigen": $('#ddl_tipOperSalida').val(),
            "vserieOrigen": $('#tb_serieOrigen').val(),
            "nnumeroOrigen": $('#tb_numOrigen').val(),  
            "ccod_almDestino": $('#ddl_almacenDest').val(),
            "ctipoDestino": $('#ddl_tipOperIngreso').val(),
            "vserieDestino": $('#tb_serieDest').val(),
            "nnumeroDestino": $('#tb_numDest').val(), 
            "vobservacion": $('#tb_observacion').val(),
            "ntotal": parseFloat(TotalCosto).toFixed(2) 
        }
    ]

//    if ($('#ddl_tienda').val() == "") {
//       Mensaje('Advertencia','Ingresar Tienda.','warning');
//        return;
//    }else 
    if ($('#dp_fecha').val() == "") {
       Mensaje('Advertencia','Ingresar Fecha.','warning');
         return;
    }else if ($('#ccod_almOrigen').val() == "") {
       Mensaje('Advertencia','Ingresar Almacen de Origen.','warning');
        return;
    }else if ($('#ctipoOrigen').val() == "") {
       Mensaje('Advertencia','Ingresar Tipo de operación de Origen.','warning');
       return;
   }else if ($('#ccod_almDestino').val() == "") {
       Mensaje('Advertencia','Ingresar Almacen de Destino.','warning');
        return;
    }else if ($('#ctipoDestino').val() == "") {
       Mensaje('Advertencia','Ingresar Tipo de operación de Destino.','warning');
       return;
    }else if(objTransladoDetalle.length<1){
        Mensaje('Advertencia','Ingresar articulos para transferir.','warning');
        return; 
    }

    $.ajax({
        type: "POST",
        url: 'Salida.aspx/ValidarListArticulo',
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

    $.ajax({
        type: "POST",
        url: 'Transferencias.aspx/Guardar',
        data: JSON.stringify({ CabTranslado: objTranslado, LnTranslado: objTransladoDetalle, operacion: $('#operacion').val() }),
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
        url: 'Transferencias.aspx/VerificarCantaArti',
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
        Mensaje('Advertencia','Ingrese codigo de articulo.','warning');
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
        url: 'Transferencias.aspx/VerificarCantaArti',
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
    $('#tb_articulo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[2].innerText);
    $('#tb_cod').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
    $('#tb_cantActual').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[4].innerText);
    $('#tb_costo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[5].innerText);
}
function ModalArticulos() {

 $('#table_Articulos').DataTable().destroy();

      $.ajax({
            type: "POST",
            url: 'Transferencias.aspx/ConsultarArticulosSalida',
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
                        { data: 'cdsc_articulo' },
                        { data: 'linea' }, 
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
    }
    $('#modalnuevo').modal('show');
    $("#tb_cod").val("");
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
    $("#dp_fecha").val(day+'/'+month+'/'+year);  
    ActualizarDropdownsAlmacen();
}

function CargarAlmacenes() {

//  if($('#ddl_tienda').val() == '') {
//        $('#ddl_almacenOrig').val("")
//        $('#tb_serieOrigen').val("")
//        $('#ddl_almacenDest').val("")
//        $('#tb_serieDest').val("")
//        return;
//    }

//if ($("#ddl_tienda").val() !=''){ 

    var listBoxOrigen = document.getElementById("ddl_almacenOrig");
    listBoxOrigen.options.length = 0;

    var listBoxDesctino = document.getElementById("ddl_almacenDest");
    listBoxDesctino.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'Transferencias.aspx/ConsultarAlmEmpActivos',
        data: '{tienda: "' + "" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) {
                almacenesList = response.d;
                ActualizarDropdownsAlmacen();
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function ActualizarDropdownsAlmacen() {
    var origVal = $("#ddl_almacenOrig").val();
    var destVal = $("#ddl_almacenDest").val();

    var $origDropdown = $("#ddl_almacenOrig");
    $origDropdown.empty();
    $origDropdown.append($("<option />").val("").text(""));
    $.each(almacenesList, function () {
        if (destVal === "" || this.ccod_alm !== destVal) {
            $origDropdown.append($("<option />").val(this.ccod_alm).text(this.cdsc_alm));
        }
    });
    $origDropdown.val(origVal);

    var $destDropdown = $("#ddl_almacenDest");
    $destDropdown.empty();
    $destDropdown.append($("<option />").val("").text(""));
    $.each(almacenesList, function () {
        if (origVal === "" || this.ccod_alm !== origVal) {
            $destDropdown.append($("<option />").val(this.ccod_alm).text(this.cdsc_alm));
        }
    });
    $destDropdown.val(destVal);
}

function CargarNumeradorIngreso() {
var ff = $('#ddl_almacenDest').val();
 if($('#ddl_almacenDest').val() != '') {
  $.ajax({
            type: "POST",
            url: 'Transferencias.aspx/ConsultarNumerador',
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
                    $('#tb_numDest').val(obj[0].nnumero);
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
function CargarOperTransladoIngreso(){
  

    var listBox = document.getElementById("ddl_tipOperIngreso");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'Transferencias.aspx/CargarTiposOperacionTransferencia',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){ 
                var $dropdown = $("#ddl_tipOperIngreso");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_toper).text(this.cdsc_toper));
                });
            } 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}
function CargarOperTransladoSalida(){
    var listBox = document.getElementById("ddl_tipOperSalida");
    listBox.options.length = 0;
     
    $.ajax({
        type: "POST",
        url: 'Transferencias.aspx/CargarTiposOperacionTransferenciaSalida',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d){
                var $dropdown = $("#ddl_tipOperSalida");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_toper).text(this.cdsc_toper));
                }); 
            } 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}
function CargarNumeradorSalida() {
 if($('#ddl_almacenOrig').val() != '') {
  $.ajax({
            type: "POST",
            url: 'Transferencias.aspx/ConsultarNumeradorSalida',
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
                    $('#tb_numOrigen').val(obj[0].nnumero);
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

    var obj = llenarobjeto('Transferencias.aspx/ConsultarTransferencias');
    
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
     "ordering": false,
        data: obj, 
        columns: [
                { data: 'item', 
                className: "dt-body-center" },
                { data: 'id_cbinve' },
                { data: 'cdsc_almOrigen' },
                { data: 'ctipoOrigen' },
                { data: 'vserieOrigen' },
                { data: 'nnumeroOrigen' },
                { data: 'cdsc_almDestino' },
                { data: 'ctipoDestino' },
                { data: 'vserieDestino' },
                { data: 'nnumeroDestino' },
//                { data: 'cdsc_tienda' },
                { data: 'dfecha' }
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
                { data: 'cdsc_almOrigen' },
                { data: 'ctipoOrigen' },
                { data: 'vserieOrigen' },
                { data: 'nnumeroOrigen' },
                { data: 'cdsc_almDestino' },
                { data: 'ctipoDestino' },
                { data: 'vserieDestino' },
                { data: 'nnumeroDestino' },
//                { data: 'cdsc_tienda' },
                { data: 'dfecha' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

  $('#table_id').attr("style", "width:100%");


}

$(document).ready(function () {
    CargarMenu();
    CargarAlmacenes();
    ConsultaColumnas();

    $("#ddl_almacenOrig").change(function () {
        ActualizarDropdownsAlmacen();
    });
    $("#ddl_almacenDest").change(function () {
        ActualizarDropdownsAlmacen();
    });

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
    inicar_menu_nivel3('Transferencias', '1_li_Almacen', '2_li_Operaciones', '3_li_Transferencias', '2');
   CargarOperTransladoIngreso();
   CargarOperTransladoSalida();
   CargarTabla();

    $("#tb_cod").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) { 
            $.ajax({
                type: "POST",
                url: 'Transferencias.aspx/ValidarArticuloAlmacenSalida',
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
                             $("#tb_cantidad").focus();
                        }else{
                             Mensaje('No se encontro el código del artículo ingresado.','','warning');
                             $('#tb_articulo').val("");
                             $('#tb_costo').val("");
                             $('#tb_cantActual').val("");
                        }
                    }

                    else MensajeFinSession();
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
    $("#dp_fecha").datepicker();
});