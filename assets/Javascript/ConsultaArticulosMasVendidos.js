
var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();
 
 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "txtTienda"){
        DscTabla = "ad_tienda";
        DscColumna = "ccod_tienda";
        Nombre = "Código de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtfchDesde"){
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha desde";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "txtfchHasta"){
        DscTabla = "al_cbinve";
        DscColumna = "dfecha";
        Nombre = "Fecha hasta";
        Estado = "Obligatorio";
        TipoDato = ""; 
    }else if($("#NombreColumna").val() == "txtFamilia"){
        DscTabla = "al_ctlin";
        DscColumna = "ccod_lin";
        Nombre = "Código de familia";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtCodArticulo"){
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de Artículo";
        Estado = "Opcional";
        TipoDato = "1 hasta";
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
  
 


function PasaDatosCodArticulo() {
    var fila = $("#tableVisibleConsultaArticulo input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#tableVisibleConsultaArticulo")[0].rows[fila[0].rowIndex].cells[1].innerText);
}
 
function ModalConsultarArticulo() {

     
    $('#modalConsultarArticulo').modal('show');

    
     $('#tableVisibleConsultaArticulo').DataTable().destroy();
    $('#table_secundariaConsultarArticulo').DataTable().destroy();

     var obj = llenarobjeto('ConsultaArticulosMasVendidos.aspx/ConsultarArticulos');
                
       $('#tableVisibleConsultaArticulo').DataTable({
        "pageLength": 5,
        data: obj,
        columns: [
                    { data: 'cbx',
                    render: function (data, type, row) {
                    if (type === 'display') { return '<input type="radio" name="radiob">'; }
                    return data;
                    },
                    className: "dt-body-center"
                    },
                    {data: 'ccod_articulo' },
                    { data: 'cdsc_articulo' }
                    ]
    });
   
      $('#table_secundariaConsultarArticulo').DataTable({
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
                {data: 'ccod_articulo' },
                { data: 'cdsc_articulo' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });
          
         
}



function Ejecutar() {

    if(navigator.onLine) {

    if ($('#txtTienda').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }  

  $('#table_principalDoc').DataTable().destroy();
    $('#table_visibleDoc').DataTable().destroy();
     
     
     var obj = [
        {
            "ccod_articulo": $('#txtCodArticulo').val(),
            "ccod_tienda": $('#txtTienda').val(), 
            "n_fchDesde": $('#txtfchDesde').val(),
            "n_fchHasta": $('#txtfchHasta').val(),
            "ccod_lin": $('#txtFamilia').val()
        }
    ]

    $.ajax({
        type: "POST",
        url: 'ConsultaArticulosMasVendidos.aspx/ConsultaArticulosMasVendidos',
        data: JSON.stringify({ ArticulosMasVendidos: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {

        if (response.d=="-1"){
           MensajeFinSession();
           }else{

            var obj = response.d;
            $('#hdd_numerofilas').val(obj.length);

            $('#table_visibleDoc').DataTable({ 
                data: obj,
                "ordering": false,
                columns: [ 
                { data: 'ccod_caja' },
                { data: 'cdsc_caja' },
                { data: 'ccod_lin' },
                { data: 'ccod_articulo' },  
                { data: 'cdsc_articulo' },
                { data: 'ncantidad' }
                 ]
            });

            $('#table_principalDoc').DataTable({
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
                { data: 'ccod_lin' },
                { data: 'ccod_articulo' },  
                { data: 'cdsc_articulo' },
                { data: 'ncantidad' } ],
                 scrollX: "2000px",
                scrollCollapse: true
            });
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
 

function Limpiar() {
    $('#txtCodArticulo').val('');
    $('#txtTienda').val('');  
    $('#txtfchDesde').val('');
    $('#txtfchHasta').val('');
     $('#txtFamilia').val('');
    document.getElementById("txtTienda").setAttribute("value", ""); 
    document.getElementById("txtFamilia").setAttribute("value", ""); 
    $('#table_visibleDoc').DataTable().destroy();

  var table = $('#table_visibleDoc').DataTable();
    table.clear().draw();

    document.getElementById('containerDona').style.display = 'none';
    document.getElementById('containerDonaMenos').style.display = 'none';
 
  
}
 
    function CargarFamilias(){

    var listBox = document.getElementById("txtFamilia");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'ConsultaArticulosMasVendidos.aspx/CargarFamilia',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            if(response.d){
                var $dropdown = $("#txtFamilia");  
                 $dropdown.append($("<option />").val("").text("")); 
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_lin).text(this.cdsc_lin));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

$(document).ready(function () {
     CargarMenu();

     ConsultaColumnas();
  

$("#ModalDatosPersonales").draggable();
     
    $("#modalConsultarArticulo").draggable();
     CargarFamilias(); 
   document.getElementById("txtFamilia").setAttribute("value", ""); 
   CargarMesActual();
    CargarTienda();  
       inicar_menu_nivel3('Consulta de Artículos mas Vendidos', '1_li_Ventas','2_li_ConsultaVenta', '3_li_ArticulosMasVendidos', '0');
    
    $('#btn_p_nuevo').hide();
    $('#btn_p_editar').hide();
    $('#btn_p_grabar').hide();
    $('#btn_p_eliminar').hide();
    $('#btn_p_back').hide();
    $('#btn_p_imprimir').hide(); 
    document.getElementById("divColsulta").style.visibility = "visible";
    $('#btn_p_ejecutar').removeClass("botones_des").addClass("botones_hab"); 
    $('#btn_p_limpiar').removeClass("botones_des").addClass("botones_hab");

    traducir_tabla();
    $('#table_visibleDoc').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });

    $("#thTablaVisible").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
           
            var blob = new Blob([document.getElementById('tableExport').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
            
        }
    });

    $("#thTablaConsultarArticulo").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tableExportarConsultarArticulo').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });
     

 
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
    $("#txtfchDesde").datepicker();
    $("#txtfchHasta").datepicker();
});

 
  