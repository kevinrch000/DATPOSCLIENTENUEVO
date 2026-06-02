
var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
  
  function CargarDatosColumna() {

    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";


//    if($("#NombreColumna").val() == "ddl_tienda"){
//        DscTabla = "ad_tienda";
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
    }else if($("#NombreColumna").val() == "ddl_almacen"){
        DscTabla = "al_cbinve";
        DscColumna = "ccod_alm";
        Nombre = "Código del almacen";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_tipop"){
        DscTabla = "al_cbinve";
        DscColumna = "ctipo";
        Nombre = "Código de operación";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_serie"){
        DscTabla = "al_cbinve";
        DscColumna = "cserie";
        Nombre = "Serie";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_num"){
        DscTabla = "al_cbinve";
        DscColumna = "nnumero";
        Nombre = "Numerador";
        Estado = "Opcional";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_observacion"){
        DscTabla = "al_cbinve";
        DscColumna = "cobservacion";
        Nombre = "Observación";
        Estado = "Opcional";
        TipoDato = "1 hasta"; 
    }else if($("#NombreColumna").val() == "tbProveedor"){
        DscTabla = "al_cbinve";
        DscColumna = "ccod_coa";
        Nombre = "Código del proveedor";
        Estado = "Obligatorio";
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
    }else if($("#NombreColumna").val() == "tb_um" || $("#NombreColumna").val() == "tb_um_editar" ){
        DscTabla = "al_unidadmedida";
        DscColumna = "ccod_unidadmedida";
        Nombre = "Unidad de medida";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
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
 
 function checked_click(row) {
 
$(".limpiar_checked").removeAttr("checked");
 
$(row).prop('checked', true);
 
 var currentRow = $(row).closest("tr"); 
if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', ''); 
$("#table_id tr:nth-child("+currentRow[0].rowIndex+")").css('background', 'silver');

$('#hdd_fila').val(currentRow[0].rowIndex);
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
   
}

function tab_datosclick() {

    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Ingresos.aspx/ConsultarIngreso',
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

 
function CargarDetalles(id) {

    $("#tabla > tbody").html("");

    var obj;

    $.ajax({
        type: "POST",
        url: 'Ingresos.aspx/ConsultarInventarioDetalle',
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
                .append($('<td>' + obj[i].csim_unidadmedida + '</td>'))
                .append($('<td style="text-align: right;">' + obj[i].ncantidad + '</td>'))
                .append($('<td style="text-align: right;">' + obj[i].ncosto + '</td>'))
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

function CompletarCampos(obj){
 
  $("#tb_serie").val(obj[0].vserie);
    $("#tb_num").val(obj[0].nnumero);

//    (document.getElementById("ddl_tienda")).selectedIndex = 
//    [...(document.getElementById("ddl_tienda")).options].findIndex(option => option.value === (obj[0].ccod_tienda).trim());
    $("#dp_fecha").val(obj[0].dfecha);
    CargarAlmacenes();
    (document.getElementById("ddl_almacen")).selectedIndex = 
    [...(document.getElementById("ddl_almacen")).options].findIndex(option => option.value === (obj[0].ccod_alm).trim());
    (document.getElementById("ddl_tipop")).selectedIndex = 
    [...(document.getElementById("ddl_tipop")).options].findIndex(option => option.value === (obj[0].ctipo).trim());
 
    $("#tb_observacion").val(obj[0].vobservacion);
    $("#hdd_id_cbinve").val(obj[0].id_cbinve);
     $("#tbProveedor").val(obj[0].ccod_coa);
    CargarDetalles(obj[0].id_cbinve);
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
            url: 'Ingresos.aspx/ConsultarIngreso',
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
//    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
//    $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");
} 

function Guardar() {
      
    if(navigator.onLine) {

//    if ($('#ddl_tienda').val() == "") {
//       Mensaje('Advertencia','Ingresar tienda.','warning');
//        return;
//    }else 
    if ($('#ddl_almacen').val() == "") {
        Mensaje('Advertencia','Ingresar almacen.','warning');
         return;
    }else if ($('#dp_fecha').val() == "") {
        Mensaje('Advertencia','Ingresar fecha.','warning');
         return;
    }else if ($('#ddl_tipop').val() == "") {
        Mensaje('Advertencia','Ingresar tipo de operación.','warning');
         return;
    }else if ($('#tb_serie').val() == "") {
        Mensaje('Advertencia','La serie del almacen no esta configurado o no es valido.\n\nConfigure el numerador de almacen para continuar.','warning');
         return; 
    } 

     var objInventarioDetalle = $('#tabla tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            id_lninve: $td.eq(0).text(),
            state: $td.eq(1).text(),
            ccod_articulo: $td.eq(2).text(),
            ncantidad: $td.eq(5).text(),
            ncosto: $td.eq(6).text()
        }
    }).get();
    
    var TotalCosto = 0;
    $.ajax({  
        type: "POST",
        url: 'Ingresos.aspx/TotalInventario',
        data: JSON.stringify({ obj: objInventarioDetalle }),
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
    for (var i = 0; i < objInventarioDetalle.length; i++) { 
        if (objInventarioDetalle[i].state != '3' ){
            listCodArticulo = "ConData"; 
        }
    }
    
     if(listCodArticulo == ""){
        Mensaje('Advertencia','Ingresar articulos a la lista de articulos.','warning');
        return; 
    }

     var objInventario = [
        {
            "ccod_tienda": "",
            "ccod_alm": $('#ddl_almacen').val(),
            "dfecha": $('#dp_fecha').val(),
            "ctipo": $('#ddl_tipop').val(),
            "vserie": $('#tb_serie').val(), 
            "vobservacion": $('#tb_observacion').val(),
            "id_cbinve": $('#hdd_id_cbinve').val(),
            "ccod_coa": $('#tbCodProveedor').val(),
            "ntotal": parseFloat(TotalCosto).toFixed(2) 
        }
    ]

     
    $.ajax({
        type: "POST",
        url: 'Ingresos.aspx/Guardar',
        data: JSON.stringify({ inventario: objInventario, detalleinventario: objInventarioDetalle, operacion: $('#operacion').val() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d == "-1") MensajeFinSession();
            else {
                if (response.d == true) {

                $("#tb_serie").val("");
                    Mensaje('Correcto', '', 'success');
                 
                    Desabilitar();
                   	Deshacer();
                    CargarTabla();
                    if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                        $(".limpiar").val("");
                    $('#table_id').attr("style", "width:100%");
                }
            }

            if (response.d == false) Mensaje('Error', 'No se realizó la operación', 'error');
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
    }

    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = '2';
    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML = $("#tb_cod_editar").val();
    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = $("#tb_articulo_editar").val();
    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[4].innerHTML = $("#tb_um_editar").val();
    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[5].innerHTML = $("#tb_cantidad_editar").val();
    $("#tabla")[0].rows[$("#hdd_rv").val()].cells[6].innerHTML =  parseFloat($("#tb_costo_editar").val()).toFixed(2);

    $("#modalEditar").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal

}

function EditarModal(row) {
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_cod_editar").val(currentRow.find("td:eq(2)").text());
    $("#tb_articulo_editar").val(currentRow.find("td:eq(3)").text());
    $("#tb_um_editar").val(currentRow.find("td:eq(4)").text());
    $("#tb_cantidad_editar").val(currentRow.find("td:eq(5)").text());
    $("#tb_costo_editar").val(parseFloat(currentRow.find("td:eq(6)").text()).toFixed(2));
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
 } 

    
 
    $("#tabla").find('tbody')
            .append($('<tr>')
            .append($('<td style="display:none;">0</td>'))
            .append($('<td style="display:none;">1</td>'))
            .append($('<td>' + $('#tb_cod').val() + '</td>'))
            .append($('<td>' + $('#tb_articulo').val() + '</td>'))
            .append($('<td>' + $("#tb_um").val() + '</td>'))
            .append($('<td>' + $("#tb_cantidad").val() + '</td>'))
            .append($('<td>' + parseFloat($("#tb_costo").val()).toFixed(2) + '</td>'))
            .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditar" onclick="EditarModal(this)"></a></td>'))
            .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFila(this)"></a></td>'))
            );
         $("#modalnuevo").modal('hide');//ocultamos el modal
         $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
         $('.modal-backdrop').remove();//eliminamos el backdrop del modal
    
}

function ObtenerIvg() {
    var obj = llenarobjeto('Ingresos.aspx/ObtenerIvg'); 
     $('#ValorIgv').val(obj[0].igv); 
}

 

 


function PasarArticulo() {
    var fila = $("#table_Articulos input[name=radiob]:checked").closest('tr');
    $('#tb_articulo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[2].innerText);
    $('#tb_cod').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
    $('#tb_um').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[4].innerText);
}

function ConsultarProveedor() {
    
    if(navigator.onLine) {

    $('#modalConsultarProveedor').modal('show'); 
    $('#tableProveedor').DataTable().destroy();
    var obj = llenarobjeto('Ingresos.aspx/ConsultarProveedor');

    $('#tableProveedor').DataTable({ 
        "lengthMenu": [5],
        data: obj,
        columns: [ 
                { data: 'cbx',
                    render: function (data, type, row) {
                        if (1===1) { return '<input type="radio" name="radiob">'; }
                        return data;
                    },
                    className: "dt-body-center"
                }, 
                { data: 'ccod_coa' },
                { data: 'cdsc_coa' }
            ]
    });
    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}

}

function PasaDatosCodProveedor() {
    var fila = $("#tableProveedor input[name=radiob]:checked").closest('tr');
    $('#tbCodProveedor').val($("#tableProveedor")[0].rows[fila[0].rowIndex].cells[1].innerText); 
    $('#tbProveedor').val($("#tableProveedor")[0].rows[fila[0].rowIndex].cells[2].innerText); 
     
 
}

function ModalArticulos() {
$('#table_Articulos').DataTable().destroy();
$('#tbtable_Articulos').DataTable().destroy();
  var obj = llenarobjeto('Ingresos.aspx/ConsultarArticulos');
    $('#table_Articulos').DataTable({
        iDisplayLength: 5,
        data: obj,
        columns: [ 
                { data: 'cbx',
                    render: function (data, type, row) {
                        if (type === 'display') { return '<input type="radio" name="radiob">'; }
                        return data;
                    },
                    className: "dt-body-center"
                }, 
                { data: 'ccod_articulo' },
                { data: 'cdsc_articulo' },
                { data: 'linea' },
                { data: 'uni_medi' },
                { data: 'estado' }
            ]

    });
    $('#tbtable_Articulos').DataTable({
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
                { data: 'linea' },
                { data: 'uni_medi' },
                { data: 'estado' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

}

function NuevoModal() {

 if(navigator.onLine) { 

    $('#modalnuevo').modal('show');
    $("#tb_cod").val("");
    $("#tb_articulo").val("");
    $("#tb_um").val("");
    $("#tb_cantidad").val("");
    $("#tb_costo").val("");

    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}

function CargarAlmacenes() {

//    if($('#ddl_tienda').val() == '') {
//        $('#ddl_almacen').val("")
//        $('#tb_serie').val("")
//        return;
//    }

    var listBox = document.getElementById("ddl_almacen");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'Ingresos.aspx/ConsultarAlmEmpActivos',
        data: '{tienda: "' + "%%%" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d) {
                var $dropdown = $("#ddl_almacen");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.ccod_alm).text(this.cdsc_alm));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarNumerador() {
 if($('#ddl_almacen').val() != '') {
  $.ajax({
            type: "POST",
            url: 'Ingresos.aspx/ConsultarNumerador',
            data: '{almacen: "' + $('#ddl_almacen').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                obj = response.d;
                if(obj.length<1){
                    Mensaje('Advertencia','El numerador del almacen no esta configurado o no es valido.\n\nConfigure el numerador de almacen para continuar.','warning');
                }else{
                $('#tb_serie').val(obj[0].cserie);
                $('#tb_num').val(obj[0].nnumero);
                }
                 
       
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    }else{

        $('#tb_serie').val('');
    }
}

function CargarTabla() {
     
     

    var obj = llenarobjeto('Ingresos.aspx/ConsultarIngresos');
     $('#table_id').DataTable().destroy();
    $('#tableIngresos').DataTable().destroy();
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({ 
        data: obj,
        "ordering": false,
        columns: [
                { data: 'item', 
                className: "dt-body-center" },
                { data: 'id_cbinve' },
                { data: 'ctipo' },
                { data: 'vserie' },
                { data: 'nnumero' },
                { data: 'dfecha' }, 
                { data: 'cdsc_alm' }
            ]
    });
    $('#tableIngresos').DataTable({
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
                { data: 'vserie' },
                { data: 'nnumero' },
                { data: 'dfecha' }, 
                { data: 'cdsc_alm' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

   $('#table_id').attr("style", "width:100%");
    
}

$(document).ready(function () {
    CargarMenu();
    ObtenerIvg();
    ConsultaColumnas();
 $("#tb_cod").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) { 
        $.ajax({
            type: "POST",
            url: 'Ingresos.aspx/ValidarArticulo',
            data: '{ccod_articulo: "' + $('#tb_cod').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if (response.d) { 
                    if (response.d.length > 0){  
                            $('#tb_articulo').val(response.d[0].cdsc_articulo);
                            $('#tb_um').val(response.d[0].uni_medi);
                            $("#tb_cantidad").focus();
                    }else{
                            Mensaje('No se encontro el código del artículo ingresado.','','warning');
                            $('#tb_articulo').val("");
                            $('#tb_um').val("");
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

$("#modalArticulos").draggable();
  
$("#modalnuevo").draggable();

$("#modalEditar").draggable();

$("#ModalDatosPersonales").draggable();

$("#modalConsultarProveedor").draggable();
 
//    Funcion para generar exel
    $("#thTablaIngresos").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });

    //    Funcion para generar exel proovedor
    $("#thtableProveedor").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            $('#tbtableProveedor').DataTable().destroy(); 
            var obj = $('#tableProveedor tr:has(td)').map(function(i, v) {
            var $td =  $('td', this);
                return {
                            ccod_coa: $td.eq(1).text(),
                            cdsc_coa: $td.eq(2).text()
                        }
            }).get();

            $('#tbtableProveedor').DataTable({
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
           
            var blob = new Blob([document.getElementById('divtableProveedor').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });

//    Funcion para generar exel detalle de ingreso
    $("#thDetalleIngreso").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            $('#tableDetalleExport').DataTable().destroy(); 
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

            $('#tbDetalleIngreso').DataTable({
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
                { data: 'ncosto' } ],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
           
            var blob = new Blob([document.getElementById('ExelDetalleIngreso').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });
    inicar_menu_nivel3('Ingresos directas', '1_li_Almacen', '2_li_Operaciones','3_li_Ingresos', '1');

    CargarTabla();

    CargarAlmacenes();

    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
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