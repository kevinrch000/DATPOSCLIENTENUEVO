var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
  
function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

    if($("#NombreColumna").val() == "tb_codigo"){
        DscTabla = "al_ctcaja";
        DscColumna = "ccod_caja";
        Nombre = "Código de caja";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_descripcion"){
        DscTabla = "al_ctcaja";
        DscColumna = "cdsc_caja";
        Nombre = "Nombre de caja";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_estado"){
        DscTabla = "al_ctcaja";
        DscColumna = "cstatus";
        Nombre = "Estado de caja";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "ddl_td" || $("#NombreColumna").val() == "ddl_td_editar"){
        DscTabla = "fa_ctnumer";
        DscColumna = "cdsc_numer";
        Nombre = "Nombre de documento";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_cod" || $("#NombreColumna").val() == "tb_codigo_editar"){
        DscTabla = "fa_ctnumer";
        DscColumna = "cdoc_tipo";
        Nombre = "Código de documento";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_serie" || $("#NombreColumna").val() == "tb_serie_editar"){
        DscTabla = "fa_ctnumer";
        DscColumna = "cdoc_serie";
        Nombre = "Serie de documento";
        Estado = "Opcional";
        TipoDato = "Exactos";
    }else if($("#NombreColumna").val() == "tb_correlativo" || $("#NombreColumna").val() == "tb_correlativo_editar"){
        DscTabla = "al_cbinve";
        DscColumna = "nnumero";
        Nombre = "Correlativo de documento";
        Estado = "Opcional";
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

function Nuevo() {
    $('#lb_codigo').text("");

    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    $('#tablanumerador > tbody').html('');

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

    //    Bloquer el boton para insertar numerador 
        document.getElementById("btn_nuevonum").disabled = false; 
     
}

function tab_datosclick() {
    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Cajas.aspx/ConsultarCaja',
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
    if ($('#table_id')[0].rows[1].cells[1].innerText ==  $('#hdd_ultimafila').val()){
        $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');  
        $("#table_id tr:nth-child("+1+")").css('background', 'silver'); 
         
        $('#hdd_fila').val(1);

        $(".limpiar_checked").removeAttr("checked");
        $("#"+$('#tb_codigo').val()).prop('checked', true);
    }
}

function Eliminar(){

    if(navigator.onLine) {

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

        var obj;

        $.ajax({
            type: "POST",
            url: 'Cajas.aspx/Eliminar',
            data: '{caja: "' + $('#tb_codigo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                
                if(response.d=="-1") MensajeFinSession();
                else{
                    if(response.d==true){
                        Mensaje('Correcto','','success');

                        $("#tablanumerador > tbody").html("");
                        CargarTabla();
                        if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
//                        $('.nav-tabs li:eq(2) a').tab('show');
                        Desabilitar();
                        Deshacer();
                    }
                    if(response.d==false) Mensaje('Error','No se realizó la operación','error');
                }
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        }); 

      }
    });
    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}

function InsertarFila() {
var tipDoc = document.getElementById("ddl_td");
var codigo = document.getElementById("tb_cod");
 var serie = document.getElementById("tb_serie");
 var correlativo = document.getElementById("tb_correlativo");
 if (tipDoc.value == "") {
       Mensaje('Advertencia','Ingresar tipo de documento.','warning');
        return;
    }else if (codigo.value == "") {
       Mensaje('Advertencia','Ingresar codigo del documento.','warning');
        return;
    }else if (codigo.value != "" && codigo.value.length != 2) {
       Mensaje('Advertencia','El codigo del documento debe contar con 2 caracteres exactos.','warning');
        return;
    }else if (serie.value == "") {
        Mensaje('Advertencia','Ingresar serie del documento','warning');
         return;
    }else  if (serie.value != "" && serie.value.length != 4) {
       Mensaje('Advertencia','El número de serie debe contar con 4 caracteres exactos.','warning');
         return;
    }else  if (correlativo.value == "") {
       Mensaje('Advertencia','Ingresar correlativo del documento','warning');
         return;
    }else  if (correlativo.value == "0") {
       Mensaje('Advertencia','Ingresar correlativo del documento mayor a 0','warning');
         return;
     }

    var combo = document.getElementById("ddl_td");
    var selected = combo.options[combo.selectedIndex].text;
    $("#tablanumerador").find('tbody')
    .append($('<tr>')
    .append($('<td style="display:none;">' + $("#ddl_td").val() + '</td>'))
    .append($('<td>' + selected + '</td>'))
    .append($('<td>' + $("#tb_cod").val() + '</td>'))
    .append($('<td>' + $("#tb_serie").val() + '</td>'))
    .append($('<td>' + $("#tb_correlativo").val() + '</td>'))
    .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modaleditar" onclick="EditarModal(this)"></a></td>'))
    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFila(this)"></a></td>'))
    );
    //    Bloquer el boton para insertar numerador
    if (9 > document.getElementById('tablanumerador').getElementsByTagName('tbody')[0].rows.length) {
        document.getElementById("btn_nuevonum").disabled = false;
    }else{
        document.getElementById("btn_nuevonum").disabled = true;
    }
        $("#modalnuevo").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
}

function Editar() {
    $(".disabled").prop("disabled", false);
    $("#operacion").val("editar");
    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");

//    Bloquer el boton para insertar numerador
    if (9 > document.getElementById('tablanumerador').getElementsByTagName('tbody')[0].rows.length) {
        document.getElementById("btn_nuevonum").disabled = false;
    }else{
        document.getElementById("btn_nuevonum").disabled = true;
    }

}

function NuevoModal() {

var NotaCredito = "0";
var select = document.getElementById("ddl_td");
var length = select.options.length;
for (i = length-1; i >= 0; i--) {
  select.options[i] = null;
}
 
var $dropdown = $("#ddl_td");
$dropdown.append($("<option />").val("").text(""));
$dropdown.append($("<option />").val("BV").text("Boleta"));
$dropdown.append($("<option />").val("FV").text("Factura"));  
$dropdown.append($("<option />").val("IN").text("Ingreso Caja"));
$dropdown.append($("<option />").val("EG").text("Egreso Caja"));
$dropdown.append($("<option />").val("NV").text("Nota de venta"));
$dropdown.append($("<option />").val("NF").text("Nota Crédito Factura"));
$dropdown.append($("<option />").val("NB").text("Nota Crédito Boleta"));
$dropdown.append($("<option />").val("DF").text("Nota Débito Factura"));
$dropdown.append($("<option />").val("DB").text("Nota Débito Boleta"));

   var op=select.getElementsByTagName("option");
   var CantFilaTablaNumeradores = document.getElementById('tablanumerador').getElementsByTagName('tbody')[0].rows.length;
   for (var i = 0; i < CantFilaTablaNumeradores; i++) {
    var orden = i+1;
    var we =document.getElementById("tablanumerador").rows[orden].cells[0].innerText;
   
        var Tipos =op[orden].value; 
 	    if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "BV"){
			    op[1].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "FV"){
			    op[2].style.display="none";
        }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "IN"){
                op[3].style.display="none";
        }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "EG"){
			    op[4].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "NV"){
			    op[5].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "NF"){
			    op[6].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "NB"){
			    op[7].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "DF"){
			    op[8].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "DB"){
			    op[9].style.display="none";
	    }
 
    }

    $("#ddl_td").val("");
    $("#tb_cod").val("");
    $("#tb_serie").val("");
    $("#tb_correlativo").val("");
}

function EditarModal(row) {

 var select = document.getElementById("ddl_td_editar");
var length = select.options.length;
for (i = length-1; i >= 0; i--) {
  select.options[i] = null;
}
 
var $dropdown = $("#ddl_td_editar");

$dropdown.append($("<option />").val("").text(""));
$dropdown.append($("<option />").val("BV").text("Boleta"));
$dropdown.append($("<option />").val("FV").text("Factura"));  
$dropdown.append($("<option />").val("IN").text("Ingreso Caja"));
$dropdown.append($("<option />").val("EG").text("Egreso Caja"));
$dropdown.append($("<option />").val("NV").text("Nota de venta"));
$dropdown.append($("<option />").val("NF").text("Nota Crédito Factura"));
$dropdown.append($("<option />").val("NB").text("Nota Crédito Boleta"));
$dropdown.append($("<option />").val("DF").text("Nota Débito Factura"));
$dropdown.append($("<option />").val("DB").text("Nota Débito Boleta"));

    var op=select.getElementsByTagName("option");
   var CantFilaTablaNumeradores = document.getElementById('tablanumerador').getElementsByTagName('tbody')[0].rows.length;
   for (var i = 0; i < CantFilaTablaNumeradores; i++) {
    var orden = i+1;
    var we =document.getElementById("tablanumerador").rows[orden].cells[0].innerText;
   
        var Tipos =op[orden].value;
  
        if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "BV"){
			    op[1].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "FV"){
			    op[2].style.display="none";
        }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "IN"){
                op[3].style.display="none";
        }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "EG"){
			    op[4].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "NV"){
			    op[5].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "NF"){
			    op[6].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "NB"){
			    op[7].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "DF"){
			    op[8].style.display="none";
	    }else if(document.getElementById("tablanumerador").rows[orden].cells[0].innerText == "DB"){
			    op[9].style.display="none";
	    }
        

 }
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#ddl_td_editar").val(currentRow.find("td:eq(0)").text());
    $("#tb_codigo_editar").val(currentRow.find("td:eq(2)").text());
    $("#tb_serie_editar").val(currentRow.find("td:eq(3)").text());
    $("#tb_correlativo_editar").val(currentRow.find("td:eq(4)").text());
     $dropdown.append($("<option />").val(currentRow.find("td:eq(0)").text()).text(currentRow.find("td:eq(1)").text()));
}

function ModificarFila() {

    var tipDoc = document.getElementById("ddl_td_editar");
var codigo = document.getElementById("tb_codigo_editar");
 var serie = document.getElementById("tb_serie_editar");
 var correlativo = document.getElementById("tb_correlativo_editar");
  if (tipDoc.value == "") {
       Mensaje('Advertencia','Ingresar tipo de documento.','warning');
        return;
    }else if (codigo.value == "") {
       Mensaje('Advertencia','Ingresar codigo del documento.','warning');
        return;
    }else if (codigo.value != "" && codigo.value.length != 2) {
       Mensaje('Advertencia','El codigo del documento debe contar con 2 caracteres exactos.','warning');
        return;
    }else if (serie.value == "") {
        Mensaje('Advertencia','Ingresar serie del documento','warning');
         return;
    }else  if (serie.value != "" && serie.value.length != 4) {
       Mensaje('Advertencia','El número de serie debe contar con 4 caracteres exactos.','warning');
         return;
    }else  if (correlativo.value == "") {
       Mensaje('Advertencia','Ingresar correlativo del documento','warning');
         return;
    }else  if (correlativo.value == "0") {
       Mensaje('Advertencia','Ingresar correlativo del documento mayor a 0','warning');
         return;
     }

var combo = document.getElementById("ddl_td_editar");
    var selected = combo.options[combo.selectedIndex].text;
 
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[0].innerHTML = $("#ddl_td_editar").val();
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = selected;
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML = $("#tb_codigo_editar").val();
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = $("#tb_serie_editar").val();
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[4].innerHTML = $("#tb_correlativo_editar").val();

        $("#modaleditar").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
}

function EliminarFila(row){
    $(row).closest("tr").remove();
    document.getElementById("btn_nuevonum").disabled = false;
}

function Guardar(){

    if(navigator.onLine) {

    if($('#tb_codigo').val().length != 3 && $('#tb_codigo').val() != "" ){
        Mensaje('Advertencia','Ingresar codigo de caja de 3 digitos.','warning');
        return;
    }else if($('#tb_descripcion').val() == ""){
        Mensaje('Advertencia','Ingresar nombre de caja.','warning');
        return;
    }else if($('#ddl_estado').val() == ""){
        Mensaje('Advertencia','Ingresar estado de caja.','warning');
        return;
    }
    var obj_caja = [
                    {
                    "ccod_caja": $('#tb_codigo').val(),
                    "cdsc_caja": $('#tb_descripcion').val(),
                    "cstatus": $('#ddl_estado').val()
                    }
    ]

    var obj_numerador = $('#tablanumerador tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return {
                    cdoc_tipo: $td.eq(2).text(),
                    cdoc_serie: $td.eq(3).text(),
                    cdoc_nro: $td.eq(4).text(),
                    ccod_numer: $td.eq(0).text(),
                    cdsc_numer: $td.eq(1).text()             
                }
    }).get();

    $.ajax({
        type: "POST",
        url: 'Cajas.aspx/Guardar',
        data: JSON.stringify({ 
            caja: obj_caja, 
            numerador: obj_numerador, 
            operacion: $('#operacion').val()
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
                    $(".limpiar").val(""); 
               
            }else  if(response.d[1] == '2627'){
                    MensajeError('',"El código de la caja ingresado se encuentra registrado.",'warning','Cancelar');
                    return; 
            }else if(response.d[1] == '2601'){
                    MensajeError('',"El numerador (" +response.d[3]+ ") se encuentra registrado.",'warning','Cancelar');
                    return;
            }else {
                    MensajeError('',"Error:\n\n"+response.d[2],'warning','Cancelar');
                    return;
             } 
              
            }

            if(response.d==false) Mensaje('Error','No se realizó la operación','error');
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}

function CargarTablaNumerador(id_caja) {

// $('#tableExelNumerador').DataTable().destroy();

    $("#tablanumerador > tbody").html("");
    var obj;
    $.ajax({
        type: "POST",
        url: 'Cajas.aspx/ConsultarNumeradores',
        data: '{caja: "' + id_caja + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            obj = response.d; 
            for (var i = 0; i < obj.length; i++) {
                $("#tablanumerador").find('tbody')
                .append($('<tr>')
                .append($('<td style="display:none;">' + obj[i].ccod_numer + '</td>'))
                .append($('<td>' + obj[i].cdsc_numer + '</td>'))
                .append($('<td>' + obj[i].cdoc_tipo + '</td>'))
                .append($('<td>' + obj[i].cdoc_serie + '</td>'))
                .append($('<td>' + obj[i].cdoc_nro + '</td>'))
                .append($('<td class="text-center"><a class="fa fa-pencil fa_disabled" data-toggle="modal" data-target="#modaleditar" onclick="EditarModal(this)"></a></td>'))
                .append($('<td class="text-center"><a class="fa fa-trash fa_disabled" onclick="EliminarFila(this)"></a></td>'))
                );
            }

            
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CompletarCampos(obj){

    $("#tb_codigo").val(obj[0].ccod_caja);
    $("#tb_descripcion").val(obj[0].cdsc_caja);
    var dd2 = document.getElementById("ddl_estado");
    (document.getElementById("ddl_estado")).selectedIndex = 
    [...(document.getElementById("ddl_estado")).options].findIndex(option => option.value === (obj[0].cstatus).toString());    
    CargarTablaNumerador(obj[0].ccod_caja);

    $('#lb_codigo').text(obj[0].ccod_caja + " - " + obj[0].cdsc_caja);
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
            url: 'Cajas.aspx/ConsultarCaja',
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

    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");

    //CargarTablaNumerador(fila[0].innerText);se hace en CompletarCampos

//    $('#lb_codigo').text(fila[0].innerText + " - " + fila[1].innerText);
}

function CargarTabla() {

 $('#table_id').DataTable().destroy();
  $('#tablaCaja').DataTable().destroy();
    var obj = llenarobjeto('Cajas.aspx/ConsultarCajas');
    
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({

        data: obj,
        "ordering": false,
        columns: [
                { data: 'item', 
                    className: "dt-body-center" },
                { data: 'ccod_caja' },
                { data: 'cdsc_caja' },
                { data: 'estado' }
            ]
    });
    $('#tablaCaja').DataTable({
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
                { data: 'ccod_caja' },
                { data: 'cdsc_caja' },
                { data: 'estado' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
   $('#table_id').attr("style", "width:100%");
}


 

$(document).ready(function () {
    CargarMenu();
    ConsultaColumnas();

     $("#modalnuevo").draggable();

     $("#modaleditar").draggable();
      
     $("#ModalDatosPersonales").draggable();

      //    Funcion para generar exel
    $("#thTablaCaja").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        } 
    });
     
    $("#thtableExelNumerador").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            $('#tableExelNumerador').DataTable().destroy();
            var obj = $('#tablanumerador tr:has(td)').map(function(i, v) {
            var $td =  $('td', this);
                return {
                            cdsc_numer: $td.eq(1).text(),
                            cdoc_tipo: $td.eq(2).text(),
                            cdoc_serie: $td.eq(3).text(),
                            cdoc_nro: $td.eq(4).text()
                        }
            }).get();

             $('#tableExelNumerador').DataTable({
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
                { data: 'cdsc_numer' },
                { data: 'cdoc_tipo' },
                { data: 'cdoc_serie' },
                { data: 'cdoc_nro' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
            var blob = new Blob([document.getElementById('tableExportNumeradores').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        } 
    });

    inicar_menu_nivel2('Cajas', '1_li_Administracion', '2_li_Cajas', '2');
    CargarTabla();
    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText); 

    $('#lb_codigo').text($('#table_id')[0].rows[1].cells[1].innerText + " - " + $('#table_id')[0].rows[1].cells[2].innerText);
     
});