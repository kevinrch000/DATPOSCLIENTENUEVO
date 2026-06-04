 var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

  objNumeradores=[];

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
    if($("#NombreColumna").val() == "tb_codigo"){
        DscTabla = "al_ctalmac";
        DscColumna = "ccod_alm";
        Nombre = "Código de almacén";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_descripcion"){
        DscTabla = "al_ctalmac";
        DscColumna = "cdsc_alm";
        Nombre = "Nombre de almacén";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_estado"){
        DscTabla = "al_ctalmac";
        DscColumna = "cstatus";
        Nombre = "Estado de almacén";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_tipDoc" || $("#NombreColumna").val() == "tb_tipDoc_editar"){
        DscTabla = "fa_ctnumeralmacen";
        DscColumna = "ctip_doc";
        Nombre = "Tipo Documento";
        Estado = "Opcional";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_serie" || $("#NombreColumna").val() == "tb_serie_editar"){
        DscTabla = "fa_ctnumeralmacen";
        DscColumna = "cserie";
        Nombre = "Número de serie";
        Estado = "Opcional";
        TipoDato = "Exacto";
    }else if($("#NombreColumna").val() == "tb_correlativo" || $("#NombreColumna").val() == "tb_correlativo_editar"){
        DscTabla = "fa_ctnumeralmacen";
        DscColumna = "nnumero";
        Nombre = "Correlativo";
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

    document.getElementById("btn_nuevonum").disabled = false;
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
            url: 'Almacenes.aspx/Eliminar',
            data: '{almacen: "' + $('#tb_codigo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if(response.d=="-1") MensajeFinSession();
                else{
                    obj = response.d;
                    if(obj[0].ccod_alm == 'OK'){
                        Mensaje('Correcto','','success');
                       
                        CargarTabla();
                        if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                        Desabilitar();
                        Deshacer();
                        $('#table_id').attr("style", "width:100%");
                    }else if(obj[0].ccod_alm == '547'){
                        MensajeError('', "El código del almacen (" + $('#tb_codigo').val() + ") no se puede eliminar porque se encuentra asignado.",'warning','Cancelar');
                     
                    }else{
                        MensajeError('',"Error: \n\n" + obj[0].cdsc_alm,'warning','Cancelar');
                    }   

                                               
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

function Guardar() { 

    if(navigator.onLine) {

    if($('#tb_codigo').val() == ""){
        Mensaje('Advertencia','Ingresar código de almacen.','warning');
        return; 
    }else if($('#tb_codigo').val().length > 20){
        Mensaje('Advertencia','El código de almacen no puede exceder 20 caracteres.','warning');
        return;
    }else if($('#tb_descripcion').val() == ""){
        Mensaje('Advertencia','Ingresar nombre de almacen.','warning');
        return;
    }else if($('#ddl_estado').val() == null){
        Mensaje('Advertencia','Ingresar estado de almacen.','warning');
        return;
    }


    var obj = [
        {
            "ccod_alm": $('#tb_codigo').val(),
            "cdsc_alm": $('#tb_descripcion').val(),
            "cstatus": $('#ddl_estado').val(),
            "cdepartamento": $("#txtDepartamento option:selected").text(),
            "cprovincia": $("#txtProvincia option:selected").text(), 
            "cdistrito": $("#txtDistrito option:selected").text(),    
            "cubigeo": $('#txtUbigeo').val(),
            "cdirc_almac": $('#tb_direccion').val(),
            "curba_almac": $('#td_urbanizacion').val()
        }
    ]

     var obj_numerador = $('#tablanumerador tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return {
                    
                    cdoc_tipo: $td.eq(0).text(),
                    cdsc_numer: $td.eq(1).text(),
                    cdoc_serie: $td.eq(2).text(),
                    cdoc_nro: $td.eq(3).text()                
                }
    }).get();


    $.ajax({
        type: "POST",
        url: 'Almacenes.aspx/Guardar',
        data: JSON.stringify({ almacen: obj, numerador: obj_numerador, operacion: $('#operacion').val() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d == "-1") MensajeFinSession();
            else {
            obj = response.d;
           
            if(response.d[1] == 'OK'){
                   Mensaje('Correcto', '', 'success');
                    CargarTabla(); 
                    if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                          
                    Desabilitar();
                    Deshacer();
                    $(".limpiar").val("");
                    $('#table_id').attr("style", "width:100%");
                    
               
            }else  if(response.d[1] == '2627'){
                    MensajeError('',"El código del almacén ingresado se encuentra registrado.",'warning','Cancelar');
                    return; 
            }else if(response.d[1] == '2601'){
                    MensajeError('',"El numerador (" +response.d[3]+ ") se encuentra registrado.",'warning','Cancelar');
                    return;
            }else {
                    MensajeError('',"Error:\n\n"+response.d[2],'warning','Cancelar');
                    return;
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
            url: 'Almacenes.aspx/ConsultarAlmacen',
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
  

function tab_datosclick() {
    


    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {
        
            $.ajax({
                type: "POST",
                url: 'Almacenes.aspx/ConsultarAlmacen',
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


function CompletarCampos(obj){
 
    CargarTablaNumerador($('#hdd_ultimafila').val());

    if (2 == objNumeradores.length) {
        document.getElementById("btn_nuevonum").disabled = true;
    }else{
        document.getElementById("btn_nuevonum").disabled = false;
    }

    $("#tb_codigo").val(obj[0].ccod_alm);
    $("#tb_descripcion").val(obj[0].cdsc_alm); 
    (document.getElementById("ddl_estado")).selectedIndex = 
    [...(document.getElementById("ddl_estado")).options].findIndex(option => option.value === (obj[0].cstatus).toString());

    $('#lb_codigo').text(obj[0].ccod_alm + " - " + obj[0].cdsc_alm);

    (document.getElementById("txtDepartamento")).selectedIndex = 
    [...(document.getElementById("txtDepartamento")).options].findIndex(option => option.text === (obj[0].cdepartamento).toString());
    CargarProvincia();
    (document.getElementById("txtProvincia")).selectedIndex = 
    [...(document.getElementById("txtProvincia")).options].findIndex(option => option.text === (obj[0].cprovincia).toString());
    CargarDistrito();
    (document.getElementById("txtDistrito")).selectedIndex = 
    [...(document.getElementById("txtDistrito")).options].findIndex(option => option.text === (obj[0].cdistrito).toString());
    $("#txtUbigeo").val(obj[0].cubigeo);
    $("#tb_direccion").val(obj[0].cdirc_almac);
    $("#td_urbanizacion").val(obj[0].curba_almac);

}
 

function InsertarFila() {

 var tipDoc = document.getElementById("tb_tipDoc");
 var serie = document.getElementById("tb_serie");
 var correlativo = document.getElementById("tb_correlativo");
if (tipDoc.value == "") {
       Mensaje('Advertencia','Ingresar tipo de documento.','warning');
        return;
    }else if (serie.value == "") {
       Mensaje('Advertencia','Ingresar serie del documento.','warning');
        return;
    }else  if (serie.value != "" && serie.value.length != 5) {
       Mensaje('Advertencia','La serie del documento debe contar con 5 caracteres exactos.','warning');
         return;
    }else  if (correlativo.value == "") {
       Mensaje('Advertencia','Ingresar correlativo del documento.','warning');
         return;
    }else  if (correlativo.value == "0") {
       Mensaje('Advertencia','Ingresar correlativo del documento mayor a 0','warning');
         return;
    }

    var combo = document.getElementById("tb_tipDoc");
    var selected = combo.options[combo.selectedIndex].text;

    $("#tablanumerador").find('tbody')
    .append($('<tr>') 
    .append($('<td style="display:none;">' + $("#tb_tipDoc").val() + '</td>'))
    .append($('<td class="text-center">' + selected + '</td>'))
    .append($('<td style="text-align: right;">' + $("#tb_serie").val() + '</td>'))
    .append($('<td style="text-align: right;">' + $("#tb_correlativo").val() + '</td>'))
    .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modaleditar" onclick="EditarModal(this)"></a></td>'))
    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFila(this)"></a></td>'))
    );

    $("#modalnuevo").modal('hide');//ocultamos el modal
        $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
        $('.modal-backdrop').remove();//eliminamos el backdrop del modal

//    Bloquer el boton para insertar numerador
    if (2 == document.getElementById('tablanumerador').getElementsByTagName('tbody')[0].rows.length) {
    document.getElementById("btn_nuevonum").disabled = true;
    }else{
    document.getElementById("btn_nuevonum").disabled = false;
    }
}

function NuevoModal() {
    _poblarTipDocSelect();
}

// Poblar el select de tipo documento en el modal de nuevo numerador.
// Se llama tanto desde NuevoModal() como desde el evento show.bs.modal
// para evitar condición de carrera con Bootstrap.
function _poblarTipDocSelect() {
    var select = document.getElementById("tb_tipDoc");
    if (!select) return;
    // Eliminar opciones existentes
    while (select.options.length > 0) { select.options[0] = null; }

    var $dropdown = $("#tb_tipDoc");
    $dropdown.append($("<option />").val("").text("— Seleccione —"));

    var tbody = document.getElementById('tablanumerador').getElementsByTagName('tbody')[0];
    var cant = tbody ? tbody.rows.length : 0;

    if (cant === 0) {
        // Sin filas: ofrecer ambas opciones
        $dropdown.append($("<option />").val("I").text("Ingreso"));
        $dropdown.append($("<option />").val("S").text("Salida"));
    } else if (cant === 1) {
        // Una fila: ofrecer la que falta
        // rows[1] = primera fila de tbody (rows[0] es el <thead>)
        var existente = document.getElementById("tablanumerador").rows[1].cells[0].innerHTML.trim();
        if (existente === "I") {
            $dropdown.append($("<option />").val("S").text("Salida"));
        } else if (existente === "S") {
            $dropdown.append($("<option />").val("I").text("Ingreso"));
        } else {
            // Por seguridad, ofrecer ambas
            $dropdown.append($("<option />").val("I").text("Ingreso"));
            $dropdown.append($("<option />").val("S").text("Salida"));
        }
    }
    // Si cant >= 2 el botón ya está desactivado; este caso no debería ocurrir.

    $("#tb_tipDoc").val("");
    $("#tb_serie").val("");
    $("#tb_correlativo").val("");
}

// Garantizar que el select siempre esté poblado al abrir el modal,
// independientemente del orden onclick vs data-toggle de Bootstrap.
// .off antes de .on: este script se re-ejecuta en cada navegación SPA;
// el namespace evita que el handler se duplique en cada visita.
$(document).off('show.bs.modal.almacennuevo').on('show.bs.modal.almacennuevo', '#modalnuevo', function () {
    _poblarTipDocSelect();
});



 
function EditarModal(row) {

//Eliminar los options
 var select = document.getElementById("tb_tipDoc_editar");
 var length = select.options.length;
for (i = length-1; i >= 0; i--) {
    select.options[i] = null;
}

var CantFilaTablaNumeradores = document.getElementById('tablanumerador').getElementsByTagName('tbody')[0].rows.length;

var currentRow = $(row).closest("tr");
var $dropdown = $("#tb_tipDoc_editar");
$dropdown.append($("<option />").val("").text(""));
if ( 1 == CantFilaTablaNumeradores) {
    $dropdown.append($("<option />").val("I").text("Ingreso"));
    $dropdown.append($("<option />").val("S").text("Salida"));
}else if ( 1 < CantFilaTablaNumeradores) {
    if(currentRow.find("td:eq(0)").text()=="I"){     
        $dropdown.append($("<option />").val("I").text("Ingreso"));   
    }else if(currentRow.find("td:eq(0)").text()=="S"){
          $dropdown.append($("<option />").val("S").text("Salida"));
    }
} 

    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_tipDoc_editar").val(currentRow.find("td:eq(0)").text());
    $("#tb_serie_editar").val(currentRow.find("td:eq(2)").text());
    $("#tb_correlativo_editar").val(currentRow.find("td:eq(3)").text());
     
}

function ModificarFila() { 

var tipDoc_editar = document.getElementById("tb_tipDoc_editar");
 var serie_editar = document.getElementById("tb_serie_editar");
 var correlativo_editar = document.getElementById("tb_correlativo_editar");
    
    if (tipDoc_editar.value == "") {
       Mensaje('Advertencia','Ingresar tipo de documento.','warning');
        return;
    }else if (serie_editar.value == "") {
       Mensaje('Advertencia','Ingresar serie del documento.','warning');
        return;
    }else  if (serie_editar.value != "" && serie_editar.value.length != 5) {
       Mensaje('Advertencia','La serie del documento debe contar con 5 caracteres exactos.','warning');
         return;
    }else  if (correlativo_editar.value == "") {
       Mensaje('Advertencia','Ingresar correlativo del documento.','warning');
         return;
    }else  if (correlativo_editar.value == "0") {
       Mensaje('Advertencia','Ingresar correlativo del documento mayor a 0','warning');
         return;
    }

    var combo = document.getElementById("tb_tipDoc_editar");
    var selected = combo.options[combo.selectedIndex].text;

    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[0].innerHTML = $("#tb_tipDoc_editar").val();
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = selected;
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML = $("#tb_serie_editar").val();
    $("#tablanumerador")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = $("#tb_correlativo_editar").val();

        $("#modaleditar").modal('hide');//ocultamos el modal
        $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
        $('.modal-backdrop').remove();//eliminamos el backdrop del modal

}

 
function EliminarFila(row){
    $(row).closest("tr").remove();
    document.getElementById("btn_nuevonum").disabled = false;
}

function CargarTabla() { 
 $('#tableAlmacen').DataTable().destroy(); 
 $('#table_id').DataTable().destroy();

    var obj = llenarobjeto('Almacenes.aspx/ConsultarAlmacenes'); 
    $('#hdd_numerofilas').val(obj.length); 

    $('#table_id').DataTable({
        data: obj,
        "ordering": false,
        columns: [
                { data: 'item', 
                className: "dt-body-center"},
                { data: 'ccod_alm' },
                { data: 'cdsc_alm' },
                { data: 'estado' }
            ] 
    }); 
    $('#tableAlmacen').DataTable({
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
                { data: 'ccod_alm' },
                { data: 'cdsc_alm' },
                { data: 'estado' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

    $('#table_id').attr("style", "width:100%");
}

function CargarTablaNumerador(id_almacen) {
 
    $("#tablanumerador > tbody").html("");
      
    $.ajax({
        type: "POST",
        url: 'Almacenes.aspx/ConsultarNumeradoresAlmacen',
        data: '{almacen: "' + id_almacen + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            objNumeradores = response.d;

            for (var i = 0; i < objNumeradores.length; i++) {
                $("#tablanumerador").find('tbody')
                .append($('<tr>')
                .append($('<td style="display:none;">' + objNumeradores[i].cdoc_tipo + '</td>'))
                .append($('<td style="text-align: center;">' + objNumeradores[i].cdsc_numer + '</td>'))
                .append($('<td style="text-align: right;">' + objNumeradores[i].cdoc_serie + '</td>'))
                .append($('<td style="text-align: right;">' + objNumeradores[i].cdoc_nro + '</td>'))
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

function CargarDepartamento(){
    var listBox = document.getElementById("txtDepartamento");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarDepartamento',
        data: '{ccod_cia: "' + "" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d){
                var obj = response.d;
                $('#txtDepartamento').append('<option   value=""> </option>');
                for (var i = 0; i < obj.length; i++) {
                    $('#txtDepartamento').append('<option value="' + obj[i].id + '">' + obj[i].name + '</option>');
                }
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

 
 function CargarProvincia(){
    if ($('#txtDepartamento').val()==""){
        return;
    }
    $('#txtUbigeo').val("");
    $('#txtDistrito').val(""); 

    var listBox = document.getElementById("txtProvincia");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarProvincia',
        data: '{id_departamento: "' + $('#txtDepartamento').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d){
                var obj = response.d;
                $('#txtProvincia').append('<option   value=""> </option>');
                for (var i = 0; i < obj.length; i++) {
                    $('#txtProvincia').append('<option value="' + obj[i].id + '">' + obj[i].name + '</option>');
                } 
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}


function CargarDistrito(){
    if ($('#txtProvincia').val()==""){
        return;
    }
    $('#txtUbigeo').val("");
     

    var listBox = document.getElementById("txtDistrito");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarDistrito',
        data: '{id_provincia: "' + $('#txtProvincia').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d){
                var obj = response.d;
                $('#txtDistrito').append('<option   value=""> </option>');
                for (var i = 0; i < obj.length; i++) {
                    $('#txtDistrito').append('<option value="' + obj[i].id + '">' + obj[i].name + '</option>');
                }
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarUbigeo(){
    if ($('#txtDistrito').val()==null){
        return;
    }
     $('#txtUbigeo').val($('#txtDistrito').val())
}
 


$(document).ready(function () {
    CargarMenu(); 
    ConsultaColumnas();
        CargarDepartamento();
 $("#ModalDatosPersonales").draggable();
  $("#modalnuevo").draggable();
  $("#modaleditar").draggable();
   $("#idInformeDepurador").draggable();

    inicar_menu_nivel3('Almacenes', '1_li_Almacen', '2_li_Tablas', '3_li_Almacenes','2');
    CargarTabla();
  
    if($('#hdd_numerofilas').val()>0){
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }

    //    Funcion para generar exel
    $("#thTablaAlmacenes").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });
     $("#thTablaNumeradores").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel

            $('#tableNumerador').DataTable().destroy();
            var objNumeradores = $('#tablanumerador tr:has(td)').map(function(i, v) {
            var $td =  $('td', this);
                return {
                            cdsc_numer: $td.eq(1).text(),
                            cdoc_serie: $td.eq(2).text(),
                            cdoc_nro: $td.eq(3).text() 
                        }
            }).get();

            $('#tableNumerador').DataTable({
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
                data: objNumeradores,
                columns: [
                { data: 'cdsc_numer' },
                { data: 'cdoc_serie' },
                { data: 'cdoc_nro' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
           
            var blob = new Blob([document.getElementById('tableNumeradorExportarExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });
});