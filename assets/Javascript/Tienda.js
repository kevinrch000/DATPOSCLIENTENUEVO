var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
   

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
      if($("#NombreColumna").val() == "tb_codigo"){
        DscTabla = "ad_tienda";
        DscColumna = "ccod_tienda";
        Nombre = "Código de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_descripcion"){
        DscTabla = "ad_tienda";
        DscColumna = "cdsc_tienda";
        Nombre = "Nombre de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_telefono"){
        DscTabla = "ad_tienda";
        DscColumna = "ctelf";
        Nombre = "Telefono de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    
    
    }else if($("#NombreColumna").val() == "tb_mail"){
        DscTabla = "ad_tienda";
        DscColumna = "cmail";
        Nombre = "Mail de tienda";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "tb_clave"){
        DscTabla = "ad_tienda";
        DscColumna = "cpassmail";
        Nombre = "Contraseña mail de tienda";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_estado"){
        DscTabla = "ad_tienda";
        DscColumna = "cstatus";
        Nombre = "Estado de tienda";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "tb_direccion"){
        DscTabla = "ad_tienda";
        DscColumna = "cdirc_tienda";
        Nombre = "Domicilio de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "td_urbanizacion"){
        DscTabla = "ad_tienda";
        DscColumna = "curba_tienda";
        Nombre = "Urbanización de tienda";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtDepartamento"){
        DscTabla = "ad_tienda";
        DscColumna = "cdepartamento";
        Nombre = "Departamento de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtProvincia"){
        DscTabla = "ad_tienda";
        DscColumna = "cprovincia";
        Nombre = "Provincia de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtDistrito"){
        DscTabla = "ad_tienda";
        DscColumna = "cdistrito";
        Nombre = "Distrito de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    
    }else if($("#NombreColumna").val() == "txtUbigeo"){
        DscTabla = "ad_tienda";
        DscColumna = "cubigeo";
        Nombre = "Ubigeo de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtCodLocEmi"){
        DscTabla = "ad_tienda";
        DscColumna = "ccod_loc_emis";
        Nombre = "Codigo Tributario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "ddl_lpn" || $("#NombreColumna").val() == "ddl_lpp"){
        DscTabla = "ad_tienda";
        DscColumna = "nlista_pre_normal";
        Nombre = "Código de lista de precio";
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

function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCodCliBol').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText);
     $('#txtNomCliBol').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[2].innerText);
}
 
function ModalConsultarClientes() {
     $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();

    var obj = llenarobjeto('Tiendas.aspx/CargarCliente');
 
  $('#hdd_numerofilas').val(obj.length);
     $('#tableVisibleConsulClientes').DataTable({
        "pageLength": 5,
        data: obj,
        columns: [
                    { data: 'cbx',
                    render: function (data, type, row) {
                    if (1 == 1) { return '<input type="radio" name="radiob">'; }
                    return data;
                    },
                    className: "dt-body-center"
                    },
                    {data: 'ccod_coa' },
                    { data: 'cdsc_coa' }
                    ]
    });
   
      $('#table_secundariaConsultarCliente').DataTable({
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
}


function Nuevo() {
 

    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    $('#TablaCaja > tbody').html(''); 
    $('#TablaAlmacenes > tbody').html('');

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

    (document.getElementById("ddl_lpn")).selectedIndex = 0;
    (document.getElementById("ddl_lpp")).selectedIndex = 0;

    $('#lb_codigo_a').text("");
    $('#lb_codigo_c').text("");

}

function tab_datosclick() {
    if($('#operacion').val() == '') {
        if($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Tiendas.aspx/ConsultarTienda',
                data: '{codigo: "' + $('#hdd_ultimafila').val() + '" }',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,

                success: function (response) {
                    if(response.d) CompletarCampos(response.d);
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

function CargarTablaAlmacen(id_tienda) {
//  $('#tableExportAlmacenes').DataTable().destroy();
    $("#TablaAlmacenes > tbody").html(""); 
    var obj; 
    $.ajax({
        type: "POST",
        url: 'Tiendas.aspx/ConsultarTiendaAlmacenes',
        data: '{tienda: "' + id_tienda + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) {
            obj = response.d; 
            for (var i = 0; i < obj.length; i++) {
                $("#TablaAlmacenes").find('tbody')
                .append($('<tr>')
                .append($('<td>' + obj[i].ccod_alm + '</td>'))
                .append($('<td>' + obj[i].cdsc_alm + '</td>'))
                .append($('<td class="text-center"><a class="fa fa-trash fa_disabled" onclick="NuevoEliminar(this)"></a></td>'))
                );                    
            } 
              
               
        },

        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarTablaCaja(id_tienda) {

    $("#TablaCaja > tbody").html("");
//   $('#TablaExportCaja').DataTable().destroy();

    var obj;

    $.ajax({
        type: "POST",
        url: 'Tiendas.aspx/ConsultarTiendaCajas',
        data: '{tienda: "' + id_tienda + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            obj = response.d;

            for (var i = 0; i < obj.length; i++) {
                $("#TablaCaja").find('tbody')
                .append($('<tr>')
                .append($('<td>' + obj[i].ccod_caja + '</td>'))
                .append($('<td>' + obj[i].cdsc_caja + '</td>'))
                .append($('<td class="text-center"><a class="fa fa-trash fa_disabled" onclick="NuevoEliminar(this)"></a></td>'))
                );                    
            }   
               
        }, 
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function InsertarFilaCaja(){

       $('#tablaAsignarCaja').find('input[type="checkbox"]:checked').each(function () {

      var fila = this.parentNode._DT_CellIndex.row;
          
            $("#TablaCaja").find('tbody')
            .append($('<tr>')
            .append($('<td>' + $("#tablaAsignarCaja")[0].rows[fila+1].cells[1].innerText + '</td>'))
            .append($('<td>' + $("#tablaAsignarCaja")[0].rows[fila+1].cells[2].innerText + '</td>'))
            .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="NuevoEliminar(this)"></a></td>'))
            );
       });
}

function CargarCajasDisponibles() {

    if(navigator.onLine) {

    $('#modalCajaNuevo').modal('show');
    $('#tablaAsignarCaja').DataTable().destroy();
    $('#TablaExportAsignarCaja').DataTable().destroy();
     var obj = llenarobjeto('Tiendas.aspx/ConsultarCargarCajasDisponibles');
    $('#tablaAsignarCaja').DataTable({ 
        data: obj,
        columns: [
                { data: 'cbx',
                  render: function ( data, type, row ) {
                            if ( type === 'display' ) {return '<input type="checkbox">';}
                            return data;
                          },
                  className: "dt-body-center"
                },
                { data: 'ccod_caja' },
                { data: 'cdsc_caja' },
            ]
    });
     $('#TablaExportAsignarCaja').DataTable({
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
               { data: 'cdsc_caja' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
     
}

function InsertarFilaAlmacen(){

       $('#tablaAsignarAlmacenes').find('input[type="checkbox"]:checked').each(function () {
          var fila = this.parentNode._DT_CellIndex.row;
          
            $("#TablaAlmacenes").find('tbody')
            .append($('<tr>')
            .append($('<td>' + $("#tablaAsignarAlmacenes")[0].rows[fila+1].cells[1].innerText + '</td>'))
            .append($('<td>' + $("#tablaAsignarAlmacenes")[0].rows[fila+1].cells[2].innerText + '</td>'))
            .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="NuevoEliminar(this)"></a></td>'))
            );
       });
}

function CargarAlmacenesDisponibles() {

    if(navigator.onLine) {

    $('#modalAlmacenNuevo').modal('show');
    $('#tablaAsignarAlmacenes').DataTable().destroy();
    $('#TablaExportAsignarAlmacenes').DataTable().destroy();
      var obj = llenarobjeto('Tiendas.aspx/ConsultarCargarAlmacenesDisponibles');
    $('#tablaAsignarAlmacenes').DataTable({
            
        data: obj,
        columns: [
                { data: 'cbx',
                  render: function ( data, type, row ) {
                            if ( type === 'display' ) {return '<input type="checkbox">';}
                            return data;
                          },
                  className: "dt-body-center"
                },
                { data: 'ccod_alm' },
                { data: 'cdsc_alm' },
            ]
    });
    $('#TablaExportAsignarAlmacenes').DataTable({
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
               { data: 'cdsc_alm' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

        } else {
        Mensaje('Error','Sin acceso a internet.','error');
}
//    $('#table_id').attr("style", "width: -webkit-fill-available;");
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
            url: 'Tiendas.aspx/Eliminar',
            data: '{tienda: "' + $('#tb_codigo').val() + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if(response.d=="-1") MensajeFinSession();
                else{                
                    if(response.d==true){
                        Mensaje('Correcto','','success'); 
                        $("#TablaCaja > tbody").html("");
                        $("#TablaAlmacenes > tbody").html("");
                        CargarTabla();
                        $('.nav-tabs li:eq(3) a').tab('show');
                        if($('#hdd_numerofilas').val()>0){
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
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

//function table_one_click(tbody){
//    
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

function table_two_click(tbody){
    
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
            url: 'Tiendas.aspx/ConsultarTienda',
            data: '{codigo: "' + fila[1].innerText + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {
                if(response.d) CompletarCampos(response.d);
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

function CompletarCampos(obj){

    $("#tb_codigo").val(obj[0].ccod_tiend);
    $("#tb_descripcion").val(obj[0].cnombr);
    $("#tb_direccion").val(obj[0].cdirec);
    $("#td_urbanizacion").val(obj[0].curba_tienda);
     
    $("#tb_mail").val(obj[0].cmail);
    $("#tb_telefono").val(obj[0].ctelef);
    $("#txtCodCliBol").val(obj[0].ccod_clibol);
    $("#txtNomCliBol").val(obj[0].cnom_clibol);
    (document.getElementById("ddl_estado")).selectedIndex = 
    [...(document.getElementById("ddl_estado")).options].findIndex(option => option.value === (obj[0].cstatus).toString());
    (document.getElementById("ddl_lpn")).selectedIndex = 
    [...(document.getElementById("ddl_lpn")).options].findIndex(option => option.value === (obj[0].nlista_pre_normal).toString());
    (document.getElementById("ddl_lpp")).selectedIndex = 
    [...(document.getElementById("ddl_lpp")).options].findIndex(option => option.value === (obj[0].nlista_pre_preferencial).toString());

    $('#lb_codigo_a').text(obj[0].ccod_tiend + " - " + obj[0].cnombr);
    $('#lb_codigo_c').text(obj[0].ccod_tiend + " - " + obj[0].cnombr);
 
    CargarTablaAlmacen(obj[0].ccod_tiend);
    CargarTablaCaja(obj[0].ccod_tiend); 
    $("#tb_clave").val(obj[0].cpassw);

    (document.getElementById("txtDepartamento")).selectedIndex = 
    [...(document.getElementById("txtDepartamento")).options].findIndex(option => option.text === (obj[0].cdepartamento).toString());
    CargarProvincia();
    (document.getElementById("txtProvincia")).selectedIndex = 
    [...(document.getElementById("txtProvincia")).options].findIndex(option => option.text === (obj[0].cprovincia).toString());
    CargarDistrito();
    (document.getElementById("txtDistrito")).selectedIndex = 
    [...(document.getElementById("txtDistrito")).options].findIndex(option => option.text === (obj[0].cdistrito).toString());
    $("#txtUbigeo").val(obj[0].cubigeo);
    $("#td_urbanizacion").val(obj[0].curba_tienda);
    $("#txtCodLocEmi").val(obj[0].ccod_loc_emis);
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
//    Funcion para generar exel
    $("#thtableTienda").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

     $("#thtableExportAlmacenes").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            $('#tableExportAlmacenes').DataTable().destroy();
             
            var obj = $('#TablaAlmacenes tr:has(td)').map(function(i, v) {
            var $td =  $('td', this);
                return {
                            ccod_alm: $td.eq(0).text(),
                            cdsc_alm: $td.eq(1).text() 
                        }
            }).get();

            $('#tableExportAlmacenes').DataTable({
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
               { data: 'cdsc_alm' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

            var blob = new Blob([document.getElementById('DivtableExportAlmacenes').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

     $("#thtableExportCaja").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            TablaCaja
             $('#TablaExportCaja').DataTable().destroy();
             var obj = $('#TablaCaja tr:has(td)').map(function(i, v) {
                var $td =  $('td', this);
                    return {
                                ccod_caja: $td.eq(0).text(),
                                cdsc_caja: $td.eq(1).text() 
                            }
                }).get();

             $('#TablaExportCaja').DataTable({
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
               { data: 'cdsc_caja' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });

            var blob = new Blob([document.getElementById('DivtableExportCajas').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

     $("#thtableExportAsignarCaja").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            var blob = new Blob([document.getElementById('DivtableExportAsignarCajas').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });

    $("#thtableExportAsignarAlmacenes").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            var blob = new Blob([document.getElementById('DivtableExportAsignarAlmacenes').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });


 $("#ModalDatosPersonales").draggable(); 

    inicar_menu_nivel2('Tiendas', '1_li_Administracion', '2_li_Tiendas','3');
    CargarTabla();
    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
});

function CargarTabla(){

$('#table_id').DataTable().destroy();
$('#tableTienda').DataTable().destroy();
    var obj = llenarobjeto('Tiendas.aspx/ConsultarTiendas');
    
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({ 
        data: obj, 
         "ordering": false,
        columns: [
                { data: 'item', 
                    className: "dt-body-center" },
                { data: 'ccod_tiend' },
                { data: 'cnombr' },
                { data: 'cdirec' },
                { data: 'ctelef' },
                { data: 'cmail' },
//                { data: 'cpassw' },
                { data: 'estado' }
            ]
    });

        $('#tableTienda').DataTable({
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
                    { data: 'ccod_tiend' },
                    { data: 'cnombr' },
                    { data: 'cdirec' },
                    { data: 'ctelef' },
                    { data: 'cmail' },
    //                { data: 'cpassw' },
                    { data: 'estado' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
     
    $('#table_id').attr("style", "width:100%");
}

function NuevoEliminar(row){
    $(row).closest("tr").remove();
}

function Guardar(){

    if(navigator.onLine) {

    // Validacion flexible: codigo de 1 a 10 caracteres alfanumericos
    var _cod = ($('#tb_codigo').val() || '').trim();
    if(_cod.length > 0 && (_cod.length > 10 || !/^[A-Za-z0-9_-]+$/.test(_cod))){
        Mensaje('Advertencia','El codigo de tienda debe tener de 1 a 10 caracteres alfanumericos.','warning');
        return;
    }
    if($('#tb_descripcion').val() == ""){
        Mensaje('Advertencia','Ingresar nombre de la tienda.','warning');
        return;
    }else if($('#tb_telefono').val() == ""){
        Mensaje('Advertencia','Ingresar teléfono de la tienda.','warning');
        return;

    }else if($('#ddl_estado').val() == ""){
        Mensaje('Advertencia','Seleccione estado de la tienda.','warning');
        return;
    }else if($('#tb_direccion').val() == ""){
        Mensaje('Advertencia','Ingresar dirección de la tienda.','warning');
        return; 
    }else if($('#txtDepartamento').val() == ""){
        Mensaje('Advertencia','Seleccione departamento de la tienda.','warning');
        return;
    }else if($('#txtProvincia').val() == ""){
        Mensaje('Advertencia','Seleccione provincia de la tienda.','warning');
        return;
    }else if($('#txtDistrito').val() == ""){
        Mensaje('Advertencia','Seleccione distrito de la tienda.','warning');
        return;
    }else if($('#txtUbigeo').val() == ""){
        Mensaje('Advertencia','Ingresar Ubigeo de tienda.','warning');
        return; 
    }else if($('#txtCodLocEmi').val() == ""){
        Mensaje('Advertencia','Ingresar codigo tributario de tienda.','warning');
        return; 
    }
             
    var obj_almacen = $('#TablaAlmacenes tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return {
                    ccod_alm: $td.eq(0).text()
                }
    }).get();

    var obj_caja = $('#TablaCaja tr:has(td)').map(function(i, v) {
    var $td =  $('td', this);
        return {
                    ccod_caja: $td.eq(0).text()
                }
    }).get();
     
    var obj_tienda = [
                    {
                    "ccod_tiend": $('#tb_codigo').val(),
                    "cnombr": $('#tb_descripcion').val(),
                    "cdirec": $('#tb_direccion').val(),
                    "ctelef": $('#tb_telefono').val(),
                    "cmail": $('#tb_mail').val(),
                    "cpassw": $('#tb_clave').val(),
                    "cstatus": $('#ddl_estado').val(),
                    "nlista_pre_normal": $('#ddl_lpn').val(),
                    "nlista_pre_preferencial": $('#ddl_lpp').val(),
                    "ccod_clibol": $('#txtCodCliBol').val(),
                    "cnom_clibol": $('#txtNomCliBol').val(), 
                    "cdepartamento": $("#txtDepartamento option:selected").text(),
                    "cprovincia": $("#txtProvincia option:selected").text(), 
                    "cdistrito": $("#txtDistrito option:selected").text(),    
                    "cubigeo": $('#txtUbigeo').val(),
                    "ccod_loc_emis": $('#txtCodLocEmi').val(),
                    "curba_tienda": $('#td_urbanizacion').val()
                    }
    ]

    $.ajax({
        type: "POST",
        url: 'Tiendas.aspx/GuardarAjax',
        data: JSON.stringify({ 
            tienda: obj_tienda, 
            operacion: $('#operacion').val(), 
            almacen: obj_almacen,
            caja: obj_caja 
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d == "-1") MensajeFinSession();
            else {                
                obj = response.d;
                if(response.d[1] == '2627'){
                    MensajeError('',"El codigo de tienda " + $('#tb_codigo').val() + " se encuentra registrado.",'warning','Cancelar'); 
                }else if(response.d[1] == 'OK'){
                    Mensaje('Correcto','','success');
                    CargarTabla();
                    if($('#hdd_numerofilas').val()>0){
                        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                    }
                    $('.nav-tabs li:eq(3) a').tab('show');
                      Desabilitar();
	                  Deshacer();
                }else if(response.d[1] == 'LIMITETIENDA'){
                    MensajeError('',"Ha superado la cantidad de tiendas de " + response.d[2]+ ".",'warning','Cancelar'); 
                }else{
                    MensajeError('',"Error: \n\n" + response.d[2],'warning','Cancelar');
                    
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