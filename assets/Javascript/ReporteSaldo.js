var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "txtAlmacen"){
        DscTabla = "al_ctalmac";
        DscColumna = "ccod_alm";
        Nombre = "Código de almacén";
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
    }else if($("#NombreColumna").val() == "txtCodArticulo"){
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de artículo";
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

var tipoOper = "";
var serie = "";
var numero = "";
var AlmCod = "";
var AlmNom = "";


function Ejecutar() { 

    if(navigator.onLine) {

    if ($('#txtAlmacen').val() == null) {
        Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
        return;
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }  
      
     var objSaldo = [  {
                    "ccod_articulo": $('#txtCodArticulo').val(),  
                    "ccod_alm": $('#txtAlmacen').val(),
                    "n_fchDesde": $('#txtfchDesde').val(),
                    "n_fchHasta": $('#txtfchHasta').val(), 
                    "cdsc_alm": $("#txtAlmacen option:selected").text(),
                    "ilogo": LogoEmpresa
                     } ]  

    $.ajax({
        type: "POST",
        url: 'ReporteSaldo.aspx/ReporteSaldoPrincipal',
        data: JSON.stringify({ 
            ReporteSaldo: objSaldo
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) { 
           window.open((window.DATPOS_BASE_PATH||'')+'/pages/Reportes/InformeSaldo.php', '_blank');
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    }); 

    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}

  
  
 
$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas(); 
    CargarAlmacenes(); 
    CargarMesActual();
    $("#ModalDatosPersonales").draggable();
     

   inicar_menu_nivel3('Reporte de Saldo', '1_li_Almacen', '2_li_ReporteAlmacen', '3_li_ReporteSaldo', '0');
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
   

});

function PasaDatosCodEmpresa() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarArticulos() {
 
    $('#modalConsultarArticulos').modal('show'); 

    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();
    var obj = llenarobjeto('../Consultas/ConsultaArticulos.aspx/CargarArticulo');

    $('#table_visible_ConsultarArticulos').DataTable({
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
                    {data: 'cdsc_articulo' } ]
    });
    $('#table_secundariaConsultarArticulos').DataTable({
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
                { data: 'cdsc_articulo' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });


    
   
     
     
}
 
  

function Limpiar(){
   $('#txtfchHasta').val('');
    $('#txtfchDesde').val('');
    $('#txtAlmacen').val('');    
    $('#txtCodArticulo').val(''); 
     
    document.getElementById("txtAlmacen").setAttribute("value", ""); 

}

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
