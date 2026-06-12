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
    }else if($("#NombreColumna").val() == "txtCaja"){
        DscTabla = "al_ctcaja";
        DscColumna = "ccod_caja";
        Nombre = "Código de caja";
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
      
function Ejecutar() {

    if(navigator.onLine) {

    if ($('#txtTienda').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtCaja').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar caja.', 'warning');
        return; 
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    } 
    var obj = [
        { 
            "ccod_tienda": $('#txtTienda').val(),
            "ccod_caja": $('#txtCaja').val(),
            "n_fchDesde": $('#txtfchDesde').val(),
            "n_fchHasta": $('#txtfchHasta').val() 
        }
    ]
        $('#table_visibleDoc').DataTable().destroy();
        $('#table_principalDoc').DataTable().destroy();
    $.ajax({
        type: "POST",
        url: 'ConsultaMargenUtilidadDia.aspx/MargenUtilidadDiaPricipal',
        data: JSON.stringify({ MargenUtilidadDia: obj }),  
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
       
        if (response.d=="-1"){
           MensajeFinSession();
           }else{
           var obj = response.d;

           $('#txtImpTotA').val('');
           $('#txtCosTotA').val('');
           $('#txtMarUtiA').val('');

           if (obj.length > 0) {
               $.ajax({
                   type: "POST",
                   url: 'ConsultaMargenUtilidadDia.aspx/DatosAdicionales',
                   data: JSON.stringify({ Datos: obj }),
                   contentType: "application/json; charset=utf-8",
                   dataType: "json",
                   async: false,
                   success: function (response) {
                       var objDA = response.d;
                       $('#txtImpTotA').val(objDA.nprecio);
                       $('#txtCosTotA').val(objDA.ncosto);
                       $('#txtMarUtiA').val(objDA.n_margenUtilidad);
                   },
                   error: function (xhr, status, error) {
                       alert(error);
                   }
               });
           }
          


            $('#hdd_numerofilas').val(obj.length);
            $('#table_visibleDoc').DataTable({
               "ordering": false,
                data: obj,
                columns: [ 
                { data: 'ccod_tienda' },
                { data: 'cdsc_tienda' },
                { data: 'ccod_caja' },
                { data: 'cdsc_caja' },
                { data: 'nprecio' },
                { data: 'ncosto' },
                { data: 'n_margenUtilidad' },
                { data: 'n_marUtiPorcenta' },
                { data: 'dfch_crea' } 
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
                { data: 'ccod_tienda' },
                { data: 'cdsc_tienda' },
                { data: 'ccod_caja' },
                { data: 'cdsc_caja' },
                { data: 'nprecio' },
                { data: 'ncosto' },
                { data: 'n_margenUtilidad' },
                { data: 'n_marUtiPorcenta' },
                { data: 'dfch_crea'}],
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
  $('#txtfchHasta').val('');
    $('#txtfchDesde').val(''); 
    $('#txtTienda').val(''); 
    $('#txtCaja').val('');
     document.getElementById("txtTienda").setAttribute("value", "");
     document.getElementById("txtCaja").setAttribute("value", ""); 
 
  $('#table_visibleDoc').DataTable().destroy();
    var table = $('#table_visibleDoc').DataTable();
    table.clear().draw();
}
   
$(document).ready(function () {
    CargarMenu();
    ConsultaColumnas();

    $("#ModalDatosPersonales").draggable();
  
    CargarMesActual();
   
    CargarCaja();
    CargarTienda(); 
     
    inicar_menu_nivel3('Consulta de Margen de Utilidad por Día', '1_li_Ventas','2_li_ConsultaVenta', '3_li_MargenUtilidadDia', '0');
    
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

  
 
     