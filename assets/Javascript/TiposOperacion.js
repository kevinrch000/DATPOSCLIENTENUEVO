 var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
 

  function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
    if($("#NombreColumna").val() == "tb_codigo"){
        DscTabla = "al_ctoper";
        DscColumna = "ccod_toper";
        Nombre = "Código de operación de almacén"
        Estado = "Obligatorio"
        TipoDato = "1 hasta"
    }else if($("#NombreColumna").val() == "tb_descripcion"){
        DscTabla = "al_ctoper";
        DscColumna = "cdsc_toper";
        Nombre = "Nombre de operación de almacén"
        Estado = "Obligatorio"
        TipoDato = "1 hasta"
    }else if($("#NombreColumna").val() == "ddl_flagtipo"){
        DscTabla = "al_ctoper";
        DscColumna = "ctipo_flag";
        Nombre = "Tipo de operación de almacén"
        Estado = "Obligatorio"
        TipoDato = ""
    }else if($("#NombreColumna").val() == "ddl_estado"){
        DscTabla = "al_ctoper";
        DscColumna = "cstatus";
        Nombre = "Estado de operación de almacén"
        Estado = "Obligatorio"
        TipoDato = ""
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
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    document.getElementById("chkTipTransf").checked = false;
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
                url: 'TiposOperacion.aspx/ConsultarTipoOperacion',
                data: '{codigo: "' +  $('#hdd_ultimafila').val() + '" }',
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
            url: 'TiposOperacion.aspx/Eliminar',
            data: '{id: "' + $('#tb_codigo').val() + '", ddl_flagtipo: "' + $('#ddl_flagtipo').val() + '"}',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if(response.d=="-1") MensajeFinSession();
                else{   
                obj = response.d;
                    if(obj[0].ccod_toper == 'OK'){
                        Mensaje('Correcto','','success');
                         
                        Desabilitar();
                        Deshacer();
                        CargarTabla();
                        if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                    }else if(obj[0].ccod_toper == '547'){
                        MensajeError('', "El código de tipo de operacion (" + $('#tb_codigo').val() + ") no se puede eliminar porque se encuentra asignados a articulos.",'warning','Cancelar');
                     
                    }else{
                        MensajeError('',"Error: \n\n" + obj[0].cdsc_toper,'warning','Cancelar');
                    }   
                      
//                    if(response.d==false) Mensaje('Error','No se realizó la operación','error');
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

function Guardar(){

if(navigator.onLine) {

    if ($('#tb_codigo').val() == "") {
        Mensaje('Advertencia', 'Ingresar código de operación de almacén.', 'warning');
        return;
    }else if($('#tb_codigo').val() == ""){
        Mensaje('Advertencia','Ingresar código de operación de almacén.','warning');
        return;
    }else if($('#tb_descripcion').val() == ""){
        Mensaje('Advertencia','Ingresar nombre de operación de almacén.','warning');
        return;
    }else if($('#ddl_flagtipo').val() == null){
        Mensaje('Advertencia','Ingresar tipo de operación de almacén.','warning');
        return;
    }else if($('#ddl_estado').val() == null){
        Mensaje('Advertencia','Ingresar estado de operación de almacén.','warning');
        return;
    }

    var obj = [
                    {
                    "id_ctoper": $('#hdd_id').val(),
                    "ccod_toper": $('#tb_codigo').val(),
                    "cdsc_toper": $('#tb_descripcion').val(),
                    "ctipo_flag": $('#ddl_flagtipo').val(),
                    "cstatus": $('#ddl_estado').val(),
                    "ctipo_transferencia": document.getElementById("chkTipTransf").checked
                    }
    ]

    $.ajax({
        type: "POST",
        url: 'TiposOperacion.aspx/Guardar',
        data: JSON.stringify({ tipooperacion: obj, operacion: $('#operacion').val() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d=="-1") MensajeFinSession();
            else{   
                obj = response.d;
                if(obj[0].ccod_toper == 'OK'){
                     
                    Desabilitar();
	                Deshacer(); 
                    CargarTabla();
                    Mensaje('Correcto', '', 'success'); 
                    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                }else if(obj[0].ccod_toper == '2627'){
                    MensajeError('', "El codigo de tipo de operacion (" + $('#tb_codigo').val() + ") se encuentra registrado.",'warning','Cancelar');
                     
                }else{
                    MensajeError('',"Error: \n\n" + obj[0].cdsc_toper,'warning','Cancelar');
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

function CompletarCampos(obj) {
    var tipTransf = obj[0].ctipo_transferencia;
    document.getElementById("chkTipTransf").checked =
        (tipTransf === true || tipTransf === '1' || tipTransf === 'true');

    $("#hdd_id").val(obj[0].id_ctoper);
    $("#tb_codigo").val(obj[0].ccod_toper);
    $("#tb_descripcion").val(obj[0].cdsc_toper);

    var flagVal = (obj[0].ctipo_flag).toString().trim();
    (document.getElementById("ddl_flagtipo")).selectedIndex =
        [...(document.getElementById("ddl_flagtipo")).options]
            .findIndex(option => option.value.trim() === flagVal);

    var estadoVal = (obj[0].cstatus).toString().trim();
    (document.getElementById("ddl_estado")).selectedIndex =
        [...(document.getElementById("ddl_estado")).options]
            .findIndex(option => option.value.trim() === estadoVal);
}

//function table_one_click(tbody) {

//    var fila = tbody.onclick.arguments[0].target.parentElement.cells;
//    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(fila[0].innerText);

//    $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');
//    var index = tbody.onclick.arguments[0].target.parentElement.rowIndex;
//    $("#table_id tr:nth-child("+index+")").css('background', 'silver');
//    $('#hdd_fila').val(index);
//}

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
            url: 'TiposOperacion.aspx/ConsultarTipoOperacion',
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

function CargarTabla() {

    var obj = llenarobjeto('TiposOperacion.aspx/ConsultarTiposOperacion');
    $('#tableOperAlmacen').DataTable().destroy();
     $('#table_id').DataTable().destroy();
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
        "ordering": false,
        data: obj,
        columns: [
                { data: 'item', 
                className: "dt-body-center" },
                { data: 'ccod_toper' },
                { data: 'cdsc_toper' },
                { data: 'flag' },
                { data: 'estado' }
            ]
    });
    $('#tableOperAlmacen').DataTable({
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
                { data: 'ccod_toper' },
                { data: 'cdsc_toper' },
                { data: 'flag' },
                { data: 'estado' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
     
    $('#table_id').attr("style", "width:100%");
}

$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas();

 $("#ModalDatosPersonales").draggable();

//    Funcion para generar exel
    $("#thTablaOperAlmacen").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });

    inicar_menu_nivel3('Operaciones de Almacén', '1_li_Almacen', '2_li_Tablas', '3_li_TiposOperacion', '1');
    CargarTabla();
    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
});