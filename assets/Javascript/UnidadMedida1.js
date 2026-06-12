 var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "tb_UMcodigo"){
        DscTabla = "al_unidadmedida";
        DscColumna = "ccod_unidadmedida";
        Nombre = "Código de unidad de medida";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_UMetiqueta"){
        DscTabla = "al_unidadmedida";
        DscColumna = "csim_unidadmedida";
        Nombre = "Etiqueta de unidad de medida";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_codtribu"){
        DscTabla = "al_unidadmedida";
        DscColumna = "ccod_tributario";
        Nombre = "Código tributario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_UMnombre"){
        DscTabla = "al_unidadmedida";
        DscColumna = "cdsc_unidadmedida";
        Nombre = "Descripción de unidad de medida";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_UMestado"){
        DscTabla = "al_unidadmedida";
        DscColumna = "cstatus";
        Nombre = "Estado de unidad de medida";
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


function Nuevo() {
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');

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

function tab_datosclick() {

    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'UnidadMedida.aspx/ConsultarCodigoUnidadMedida',
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
        $("#"+$('#tb_UMcodigo').val()).prop('checked', true);
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
            url: 'UnidadMedida.aspx/Eliminar',
            data: '{codigo: "' + $('#tb_UMcodigo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if(response.d=="-1") MensajeFinSession();
                else{
                    obj = response.d;
                    if(obj[0].ccod_unidadmedida == 'OK'){
                         
                        CargarTabla();
                         
                        if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                        Desabilitar();
                        Deshacer();
                        $('#table_id').attr("style", "width:100%");
                        Mensaje('Correcto','','success');
                         
                    }else if(obj[0].ccod_unidadmedida == '547'){
                        MensajeError('', "El código de la unidad medida (" + $('#tb_UMcodigo').val() + ") no se puede eliminar porque se encuentra asignados a articulos.",'warning','Cancelar');
                     
                    }else{
                        MensajeError('',"Error: \n\n" + obj[0].cdsc_unidadmedida,'warning','Cancelar');
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



function CompletarCampos(obj){

    $("#tb_UMcodigo").val(obj[0].ccod_unidadmedida);
    $("#tb_UMetiqueta").val(obj[0].csim_unidadmedida);
     $("#tb_UMnombre").val(obj[0].cdsc_unidadmedida);
    (document.getElementById("tb_UMestado")).selectedIndex = 
    [...(document.getElementById("tb_UMestado")).options].findIndex(option => option.value === (obj[0].cstatus).toString());    
    $("#tb_codtribu").val(obj[0].ccod_tributario);
    
}

//function table_one_click(tbody) {

//    var fila = tbody.onclick.arguments[0].target.parentElement.cells;
//    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(fila[0].innerText);

//    $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');
//    var index = tbody.onclick.arguments[0].target.parentElement.rowIndex;
//    $("#table_id tr:nth-child("+index+")").css('background', 'silver');
//    $('#hdd_fila').val(index);
//}

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
            url: 'UnidadMedida.aspx/ConsultarCodigoUnidadMedida',
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

    var obj = llenarobjeto('UnidadMedida.aspx/ConsultarUnidadMedida');
    $('#tableUnidadMedida').DataTable().destroy();
     $('#table_id').DataTable().destroy();
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
        "ordering": false,
        data: obj,
        columns: [
                { data: 'item', 
                className: "dt-body-center"},
                { data: 'ccod_unidadmedida' },
                { data: 'csim_unidadmedida' },
                { data: 'ccod_tributario' },
                { data: 'cdsc_unidadmedida' },
                { data: 'cstatus' }
            ]
    });

    $('#tableUnidadMedida').DataTable({
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
                { data: 'ccod_unidadmedida' },
                { data: 'csim_unidadmedida' },
                { data: 'ccod_tributario' },
                { data: 'cdsc_unidadmedida' },
                { data: 'cstatus' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
     if($('#hdd_numerofilas').val()>0){
     $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }
    $('#table_id').attr("style", "width:100%");
}

// function DatosGenerales() { 
//    $.ajax({
//        type: "POST",
//        url: 'UnidadMedida.aspx/DatosGenerales',
//        data: '{codigo: "' + "codUsu" + '" }',
//        contentType: "application/json; charset=utf-8",
//        dataType: "json",
//        async: false, 
//        success: function (response) {
//            obj = response.d;
//            $('#sitNomTienda').text(obj[0].cdsc_tienda);
//            $('#sitNomAlmacen').text(obj[0].cdsc_alm);
//            $('#sitNomCaja').text(obj[0].cdsc_caja);
//            $('#sitIdListPreNor').text(obj[0].nlista_pre_normal);
//            $('#sitNomListPreNor').text(obj[0].cdsc_listpreNorm);
//            $('#sitIdListPrePre').text(obj[0].nlista_pre_preferencial);
//            $('#sitNomListPrePre').text(obj[0].cdsc_listprePref);
//        }, 
//        error: function (xhr, status, error) {
//            alert(error);
//        }
//    });
//}

$(document).ready(function () {
    CargarMenu();
    ConsultaColumnas();
 $("#ModalDatosPersonales").draggable();

//    Funcion para generar exel
    $("#thTablaUnidadMedida").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });
     

    inicar_menu_nivel3('Unidad de Medida', '1_li_Almacen', '2_li_Tablas', '3_li_UnidadMediad', '1');
    CargarTabla();
    if($('#hdd_numerofilas').val()>0){
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }
});

function Guardar(){
     if(navigator.onLine) {
      
    if($('#tb_UMcodigo').val() == ""){
        Mensaje('Advertencia','Ingresar código de unidad de medida.','warning');
        return;
    }else if($('#tb_UMcodigo').val().length > 10){
        Mensaje('Advertencia','El código de unidad de medida no puede exceder 10 caracteres.','warning');
        return;
     }else if($('#tb_UMetiqueta').val() == ""){
        Mensaje('Advertencia','Ingresar etiqueta de unidad de medida.','warning');
        return;
     }else if($('#tb_codtribu').val() == ""){
        Mensaje('Advertencia','Ingresar codigo tributario.','warning');
        return;
    }else if($('#tb_UMnombre').val() == ""){
        Mensaje('Advertencia','Ingresar descripción de unidad de medida.','warning');
        return;
    }else if($('#tb_UMestado').val() == null){
        Mensaje('Advertencia','Ingresar estado de unidad de medida.','warning');
        return;
    }

    var obj = [
                    { 
                    "ccod_unidadmedida": $('#tb_UMcodigo').val(),
                    "csim_unidadmedida": $('#tb_UMetiqueta').val(),
                    "cdsc_unidadmedida": $('#tb_UMnombre').val(),
                    "cstatus": $('#tb_UMestado').val(),
                    "ccod_tributario": $('#tb_codtribu').val()
                    }
    ]

    $.ajax({
        type: "POST",
        url: 'UnidadMedida.aspx/Guardar',
        data: JSON.stringify({ UnidadMedida: obj, operacion: $('#operacion').val() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if(response.d=="-1") MensajeFinSession();
            else{
                
                obj = response.d;
                if(obj[0].ccod_unidadmedida == 'OK'){
                     Mensaje('Correcto', '', 'success');

                    CargarTabla();
                    if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        } 
                    Desabilitar();
	                Deshacer();
                     $(".limpiar").val("");
                    $('#table_id').attr("style", "width:100%");
                }else if(obj[0].ccod_unidadmedida == '2627'){
                    MensajeError('', "El codigo de la unidad medida (" + $('#tb_UMcodigo').val() + ") se encuentra registrado.",'warning','Cancelar');
                     
                }else{
                    MensajeError('',"Error: \n\n" + obj[0].cdsc_unidadmedida,'warning','Cancelar');
                }
                               
                
            }

//            if(response.d==false) Mensaje('Error','No se realizó la operación','error');
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}