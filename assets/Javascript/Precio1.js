  var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
      

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
      if($("#NombreColumna").val() == "tb_codigo"){
        DscTabla = "fa_cblistpre";
        DscColumna = "ccod_cblistpre";
        Nombre = "Código de lista de precio";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_descripcion"){
        DscTabla = "fa_cblistpre";
        DscColumna = "cdsc_cblistpre";
        Nombre = "Descripción de lista de precio";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "dp_Ini"){
        DscTabla = "fa_cblistpre";
        DscColumna = "dfch_ini";
        Nombre = "Fecha inicio de lista de precio";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "ddl_estado"){
        DscTabla = "fa_cblistpre";
        DscColumna = "cstatus";
        Nombre = "Estado de lista de precio";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "dp_Fin"){
        DscTabla = "fa_cblistpre";
        DscColumna = "dfch_fin";
        Nombre = "Fecha fin de lista de precio";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_cod" || $("#NombreColumna").val() == "tb_cod_editar"){
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de artículo";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_articulo" || $("#NombreColumna").val() == "tb_articulo_editar"){
        DscTabla = "al_articulo";
        DscColumna = "cdsc_articulo";
        Nombre = "Descripción de artículo";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_pu" || $("#NombreColumna").val() == "tb_pu_editar"){
        DscTabla = "fa_lnlistpre";
        DscColumna = "npre_uni";
        Nombre = "Precio unitario";
        Estado = "Opcional";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_dma" || $("#NombreColumna").val() == "tb_dma_editar"){
        DscTabla = "fa_lnlistpre";
        DscColumna = "ndes_max";
        Nombre = "Descuento máximo";
        Estado = "Opcional";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_dmi" || $("#NombreColumna").val() == "tb_dmi_editar"){
        DscTabla = "fa_lnlistpre";
        DscColumna = "ndes_min";
        Nombre = "Descuento mínimo";
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
    $('#tb_codigo').focus();
    $('#lb_codigo').text("");
    $('.nav-tabs li:eq(0) a').tab('show');
    $('#tablaprecios > tbody').html('');

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

function LimpiarModal(){
    
    if(navigator.onLine) {

    $('#modalnuevo').modal('show');
    $('#tb_cod').val('');
    $('#tb_articulo').val('');
    $('#tb_pu').val('0.00');
    $('#tb_dma').val('0.00');
    $('#tb_dmi').val('0.00');

    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}

function tab_datosclick() {
    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Precios.aspx/ConsultarListaPrecio',
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
    var tbl = $('#table_id')[0];
    if (tbl && tbl.rows.length > 1 && tbl.rows[1].cells.length > 1 && tbl.rows[1].cells[1].innerText == $('#hdd_ultimafila').val()){
        $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');  
        $("#table_id tr:nth-child("+1+")").css('background', 'silver'); 
         
        $('#hdd_fila').val(1);

        $(".limpiar_checked").removeAttr("checked");
        $("#"+$('#tb_codigo').val()).prop('checked', true);
    }
}

//function table_one_click(tbody) {
//    
//    $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');

//    var fila = tbody.onclick.arguments[0].target.parentElement.cells;
//    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(fila[0].innerText);
//    $('#lb_codigo').text(fila[0].innerText + " - " + fila[1].innerText);

//    var index = tbody.onclick.arguments[0].target.parentElement.rowIndex;

//    $("#table_id tr:nth-child("+index+")").css('background', 'silver');

//    $('#hdd_fila').val(index);
//}

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
            url: 'Precios.aspx/Eliminar',
            data: '{listaprecio: "' + $('#tb_codigo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if(response.d=="-1") MensajeFinSession();
                else{
                    if(response.d==true){
                        Mensaje('Correcto','','success');

                        (document.getElementById("cboTipFiltro")).selectedIndex = 
                        [...(document.getElementById("cboTipFiltro")).options].findIndex(option => option.value === '1');

                        $('#txtArticulo').val('');


                        $("#tablaprecios > tbody").html("");
                        CargarTabla();
                        if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                        Desabilitar();
//                        Limpiar();
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

function CargarTablaPrecios() {

    $("#tablaprecios > tbody").html("");

    var obj;
         
    $.ajax({
        type: "POST",
        url: 'Precios.aspx/ConsultarPrecios',
        data: '{listaprecio: "' + $('#tb_codigo').val() + '", TipFiltro: "' + $('#cboTipFiltro').val() + '", Articulo: "' + $('#txtArticulo').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            obj = response.d;

            for (var i = 0; i < obj.length; i++) {
                $("#tablaprecios").find('tbody')
                .append($('<tr>')
                .append($('<td style="display:none;">' + obj[i].id_lnlistpre + '</td>'))
                .append($('<td style="display:none;">0</td>'))
                .append($('<td>' + obj[i].ccod_articulo + '</td>'))
                .append($('<td>' + obj[i].cdsc_articulo + '</td>'))
                .append($('<td>' + obj[i].npre_uni + '</td>'))
                .append($('<td>' + obj[i].ndes_max + '</td>'))
                .append($('<td style="display:none;">' + obj[i].ndes_min + '</td>'))
                .append($('<td class="text-center"><a class="fa fa-pencil " data-toggle="modal" data-target="#modalEditar" onclick="EditarModal(this)"></a></td>'))
                .append($('<td class="text-center"><a class="fa fa-trash " onclick="EliminarFila(this)"></a></td>'))
                );
            }
             
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function Guardar() {
    
    if(navigator.onLine) {

    if($('#tb_codigo').val().length < 4 && $('#tb_codigo').val() != "" ){
       Mensaje('Advertencia','Ingresar codigo de lista de precio mayor a 4 digitos.','warning');
        return;
    }else if ($('#tb_descripcion').val() == "") {
        Mensaje('Advertencia','Ingresar descripción de lista de precio.','warning');
         return;
    }else if ($('#dp_Ini').val() == "") {
        Mensaje('Advertencia','Ingresar inicio de vigencia de lista de precio.','warning');
         return;
    }else if ($('#dp_Fin').val() == "") {
        Mensaje('Advertencia','Ingresar fin de vigencia de lista de precio.','warning');
         return;
    }else if ($('#ddl_estado').val() == null) {
        Mensaje('Advertencia','Ingresar estado de lista de precios.','warning');
         return;
    }

    var objListaPrecio = [
        {
            "ccod_cblistpre": $('#tb_codigo').val(),
            "cdsc_cblistpre": $('#tb_descripcion').val(),
            "dfch_ini": $('#dp_Ini').val(),
            "dfch_fin": $('#dp_Fin').val(),
            "cstatus": $('#ddl_estado').val()
        }
    ]

    var obj_precios = $('#tablaprecios tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return {
                    ccod_articulo: $td.eq(2).text(),
                    npre_uni: $td.eq(4).text(),
                    ndes_max: $td.eq(5).text(),
                    ndes_min: $td.eq(6).text(),
                    id_lnlistpre: $td.eq(0).text(),
                    state: $td.eq(1).text()
                }
    }).get();


    $.ajax({
        type: "POST",
        url: 'Precios.aspx/Guardar',
        data: JSON.stringify({ listaprecio: objListaPrecio, precios: obj_precios, operacion: $('#operacion').val() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d == "-1") MensajeFinSession();
            else {
                if (response.d == true) {
                    Mensaje('Correcto', '', 'success');
                    
                    (document.getElementById("cboTipFiltro")).selectedIndex = 
                        [...(document.getElementById("cboTipFiltro")).options].findIndex(option => option.value === '1');

                        $('#txtArticulo').val('');

                    CargarTabla();
                     if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                    $('.nav-tabs li:eq(2) a').tab('show');
                    Desabilitar();
                    Deshacer();
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

function EliminarFila(row){
    $(row).closest("tr").attr("style", "display: none;");
    $(row).closest("tr")[0].children[1].innerHTML = '3';
}
 
function EditarFila() {
    $("#tablaprecios")[0].rows[$("#hdd_rv").val()].cells[1].innerHTML = '2';
    $("#tablaprecios")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = $("#tb_articulo_editar").val();
    $("#tablaprecios")[0].rows[$("#hdd_rv").val()].cells[4].innerHTML = parseFloat($("#tb_pu_editar").val()).toFixed(2);
    $("#tablaprecios")[0].rows[$("#hdd_rv").val()].cells[5].innerHTML = parseFloat($("#tb_dma_editar").val()).toFixed(2);
    $("#tablaprecios")[0].rows[$("#hdd_rv").val()].cells[6].innerHTML = parseFloat($("#tb_dmi_editar").val()).toFixed(2);
    $("#tablaprecios")[0].rows[$("#hdd_rv").val()].cells[2].innerHTML = $("#tb_cod_editar").val();

          $("#modalEditar").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
}

function EditarModal(row) {
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#tb_articulo_editar").val(currentRow.find("td:eq(3)").text());
    $("#tb_pu_editar").val(currentRow.find("td:eq(4)").text());
    $("#tb_dma_editar").val(currentRow.find("td:eq(5)").text());
    $("#tb_dmi_editar").val(currentRow.find("td:eq(6)").text());
    $("#tb_cod_editar").val(currentRow.find("td:eq(2)").text());
}

function InsertarFila(){

if($('#tb_cod').val() == ""){
        Mensaje('Advertencia','Ingrese codigo de articulo.','warning');
        return;
 }else if($('#tb_pu').val() == ""){
        Mensaje('Advertencia','Ingrese precio unitario.','warning');
        return;
 }else if($('#tb_dma').val() == ""){
        Mensaje('Advertencia','Ingrese descuento máximo.','warning');
        return;
 }else if($('#tb_dmi').val() == ""){
        Mensaje('Advertencia','Ingrese descuento mínimo.','warning');
        return;
 } 

    var objDetallArti = $('#tablaprecios tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return {
                    tb_cod: $td.eq(2).text() 
                }
    }).get();

    var trimCodArt = $('#tb_cod').val();
    for (var i = 0; i < objDetallArti.length; i++) {
        if (objDetallArti[i].tb_cod  == trimCodArt.trim()){
         Mensaje('Advertencia','El articulo ('+$('#tb_cod').val()+') ya se encuentra registrado.','warning'); 
         return;
        }
    }

    $("#tablaprecios").find('tbody')
    .append($('<tr>')
    .append($('<td style="display:none;">0</td>'))
    .append($('<td style="display:none;">1</td>'))
    .append($('<td>' + $('#tb_cod').val() + '</td>'))
    .append($('<td>' + $('#tb_articulo').val() + '</td>'))
    .append($('<td>' + parseFloat($("#tb_pu").val()).toFixed(2) + '</td>'))
    .append($('<td>' + parseFloat($("#tb_dma").val()).toFixed(2) + '</td>'))
    .append($('<td style="display:none;">' + parseFloat($("#tb_dmi").val()).toFixed(2) + '</td>'))
    .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditar" onclick="EditarModal(this)"></a></td>'))
    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFila(this)"></a></td>'))
    );

         $("#modalnuevo").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
}

function PasarArticulo(){
    var fila = $("#table_Articulos input[name=radiob]:checked").closest('tr');
    $('#tb_articulo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[2].innerText);
    $('#tb_cod').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
//        $('#tb_costo').val($("#table_Articulos")[0].rows[fila[0].rowIndex].cells[6].innerText);
}

function ModalArticulos(){
    
    $('#table_Articulos').DataTable().destroy();

    $('#table_Articulos').DataTable({

        "pageLength": 5,

        data: llenarobjeto('Precios.aspx/ConsultarCostosArticulos'),

        columns: [

                { data: 'cbx',
                  render: function ( data, type, row ) {
                            if ( type === 'display' ) {return '<input type="radio" name="radiob">';}
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

}

function CompletarCampos(obj){

    $("#tb_codigo").val(obj[0].ccod_cblistpre);
    $("#tb_descripcion").val(obj[0].cdsc_cblistpre);
//    $('#date_range').data('daterangepicker').setStartDate(obj[0].dfch_ini);
//    $('#date_range').data('daterangepicker').setEndDate(obj[0].dfch_fin);
    $("#dp_Ini").val(obj[0].dfch_ini);
    $("#dp_Fin").val(obj[0].dfch_fin);
    (document.getElementById("ddl_estado")).selectedIndex = 
    [...(document.getElementById("ddl_estado")).options].findIndex(option => option.value === (obj[0].cstatus).trim());

    CargarTablaPrecios($('#hdd_ultimafila').val());

        $('#lb_codigo').text(obj[0].ccod_cblistpre + " - " + obj[0].cdsc_cblistpre);
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
            url: 'Precios.aspx/ConsultarListaPrecio',
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

    //CargarTablaPrecios(fila[0].innerText); se hace en CompletarCampos

 
}

function ObtenerIvg() {
    var obj = llenarobjeto('Precios.aspx/ObtenerIvg'); 
    if (obj && obj.length > 0 && obj[0].igv !== undefined) {
        $('#ValorIgv').val(obj[0].igv);
    } else {
        $('#ValorIgv').val('18');
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

    var obj = llenarobjeto('Precios.aspx/ConsultarListaPrecios');
    $('#table_id').DataTable().destroy();
    $('#tablePrecio').DataTable().destroy();
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({
        
        data: obj,
        "ordering": false,
        columns: [
                    { data: 'item', 
                        className: "dt-body-center" },
                    { data: 'ccod_cblistpre' },
                    { data: 'cdsc_cblistpre' },
                    { data: 'dfch_ini' },
                    { data: 'dfch_fin' },
                    { data: 'estado' }
            ]
    });

   $('#tablePrecio').DataTable({
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
                    { data: 'ccod_cblistpre' },
                    { data: 'cdsc_cblistpre' },
                    { data: 'dfch_ini' },
                    { data: 'dfch_fin' },
                    { data: 'estado' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
     
    $('#table_id').attr("style", "width:100%");
}



$(document).ready(function () {
    CargarMenu();
    ObtenerIvg();
    ConsultaColumnas();
$("#ModalDatosPersonales").draggable();
 
$("#modalnuevo").draggable();

$("#modalArticulos").draggable();

$("#modalEditar").draggable();

//    Funcion para generar exel
    $("#thtableDetalleListPrec").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            $('#tableDetalleListPrec').DataTable().destroy();
            var obj = $('#tablaprecios tr:has(td)').map(function(i, v) {
            var $td =  $('td', this);
                return {
                            ccod_articulo: $td.eq(2).text(),
                            cdsc_articulo: $td.eq(3).text(),
                            npre_uni: $td.eq(4).text(),
                            ndes_max: $td.eq(5).text(),
                            ndes_min: $td.eq(6).text()
                        }
            }).get();
              $('#tableDetalleListPrec').DataTable({
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
                    { data: 'npre_uni' },
                    { data: 'ndes_max' },
                    { data: 'ndes_min' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

            var blob = new Blob([document.getElementById('tableDetalleListPrecExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    }); 

    $("#thtablePrecio").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

    inicar_menu_nivel3('Lista de Precios', '1_li_Ventas', '2_li_TablasVentas', '2_li_Precios', '2');
    $('.nav-tabs li:eq(2) a').tab('show');
    CargarTabla();
    if($('#hdd_numerofilas').val()>0 && $('#table_id')[0].rows.length > 1) {
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
        $('#lb_codigo').text($('#table_id')[0].rows[1].cells[1].innerText + " - " + $('#table_id')[0].rows[1].cells[2].innerText);
    }

    $("#tb_cod").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {

            $.ajax({
                type: "POST",
                url: 'Precios.aspx/ConsultarCostoArticulo',
                data: '{codigo: "' + $('#tb_cod').val() + '" }',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,

                success: function (response) {
                    if (response.d) {
                      if (response.d.length >0){
                         $('#tb_articulo').val(response.d[0].cdsc_articulo);
//                         $('#tb_costo').val(response.d[0].ncosto);
                       }else{ 
                            Mensaje('No se encontro el código del artículo ingresado.','','warning');
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

    $('#modalnuevo').on('shown.bs.modal', function () {
        $('#tb_cod').focus();
    })  

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
    $("#dp_Ini").datepicker();
    $("#dp_Fin").datepicker();
});


