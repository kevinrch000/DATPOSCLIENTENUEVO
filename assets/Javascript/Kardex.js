var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

var tipoOper ="";
var serie ="";
var numero ="";
var AlmCod ="";
var AlmNom ="";
 
function Ejecutar() { 

    if ($('#txtTienda').val() == null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtAlmacen').val() == null) {
        Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
        return;
    } else if ($('#txtfchDesde').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHasta').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    } 

    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
      
     var objKardex = [  {
                    "ccod_articulo": $('#txtCodArticulo').val(),
                    "cdsc_articulo": $('#txtNomAticulo').val(),
                    "ccod_tienda": $('#txtTienda').val(),
                    "ccod_alm": $('#txtAlmacen').val(),
                    "n_fchDesde": $('#txtfchDesde').val(),
                    "n_fchHasta": $('#txtfchHasta').val()
                     } ]  

    $.ajax({
        type: "POST",
        url: 'Kardex.aspx/ConsultaKardexPricipal',
        data: JSON.stringify({ 
            Kardex: objKardex
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) { 
          if (response.d== "-1"){
           MensajeFinSession();
           }else{ 
           
            var obj = response.d; 
            $('#table_id').DataTable({
            "ordering": false,
               data: obj,
                columns: [
                        { data: 'ccod_tienda' },
                        { data: 'ccod_alm' }, 
                        { data: 'ccod_articulo' },
                        { data: 'cdsc_articulo' }, 
                        { data: 'n_anio' }, 
                        { data: 'n_mes' }, 
                        { data: 'n_cantInicial' }, 
                        { data: 'n_cantIngreso' }, 
                        { data: 'n_cantSalisa' },
                        { data: 'n_saldo' }]
            });
            $('#tableArticulo').DataTable({
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
                        { data: 'ccod_alm' }, 
                        { data: 'ccod_articulo' },
                        { data: 'cdsc_articulo' }, 
                        { data: 'n_anio' }, 
                        { data: 'n_mes' }, 
                        { data: 'n_cantInicial' }, 
                        { data: 'n_cantIngreso' }, 
                        { data: 'n_cantSalisa' },
                        { data: 'n_saldo' }],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
            }
          
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
// CargarEstadisticasMargenUtilidad();
}

 

function CargarEstadisticasMargenUtilidad() {
 document.getElementById('containerBarras').style.display = 'inline'; 
 var objMargenUtilidad = [  {
                    "cdoc": $('#txtCodDocumento').val(),
                    "cdoc_serie": $('#txtSerieDoc').val(),
                    "cdoc_nro": $('#txtNroDoc').val(),
                    "n_fchDesde": $('#txtfchDesde').val(),
                    "n_fchHasta": $('#txtfchHasta').val(),
                    "ccoa_dsc": $('#txtCliente').val()
                     } ]  
       
     $.ajax({
        type: "POST",
        url: 'Kardex.aspx/CargarEstadisticasMargenUtilidad',
        data: JSON.stringify({ 
            MargenUtilidad: objMargenUtilidad
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            objMargenUtilidad = response.d;
             $("#datatable tbody").html("");
    var cuerpo = ""
     
    for (var i = 0; i < objMargenUtilidad.length; i++) { 
        cuerpo += '<tr>' +
                    '<td>' + objMargenUtilidad[i].Tipo + '</td>' +
                    '<td>' + objMargenUtilidad[i].Cantidad + '</td>' ; 
    }
    $('#datatable tbody').append(cuerpo);
     
        },

        error: function (xhr, status, error) {
            alert(error);
        }
    });
    
 
     Highcharts.chart('containerBarras', {
        data: {
            table: 'datatable'
        },
        chart: {
            type: 'column'
        },
        title: {
            text: 'Margen de Utilidad por documentos'
        },
        yAxis: {
            allowDecimals: true,
            title: {
                text: 'Cantidad'
            }
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                this.point.y + ' ' + this.point.name.toLowerCase();
            }
        }
    });
 
 }
  
 
$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas();

    CargarTienda();
    CargarAlmacenes(); 
    CargarMesActual();
    $("#ModalDatosPersonales").draggable();
     

      //    Funcion para generar exel
    $("#thTablaArticulo").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel 
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls"); 
        }
    });


    inicar_menu_nivel3('Kardex', '1_li_Almacen', '2_li_ConsultaAlmacen', '3_li_Kardex', '0');
   
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
    $('#table_id').DataTable({
        "zeroRecords": "No se encontraron resultados."
    }); 

});

function PasaDatosCodEmpresa() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarArticulos() {

     if ($('#txtTienda').val() == null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtAlmacen').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
        return;
    }    


    $('#modalConsultarArticulos').modal('show');
    var obj = [
        { 
            "ccod_tienda": $('#txtTienda').val(),
            "ccod_alm": $('#txtAlmacen').val() 
        }
    ]
 
    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();
    $.ajax({
        type: "POST",
        url: 'Kardex.aspx/CargarArticuloKardex',
        data: JSON.stringify({ objKardex: obj}),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            if (response.d == "-1") MensajeFinSession();
            else { 
                 obj = response.d;
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
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    }); 
     
}

function Limpiar(){
   $('#txtfchHasta').val('');
    $('#txtfchDesde').val('');
    $('#txtAlmacen').val('');  
    $('#txtTienda').val('');
    $('#txtNomAticulo').val(''); 
    $('#txtCodArticulo').val(''); 

    document.getElementById("txtTienda").setAttribute("value", ""); 
    document.getElementById("txtAlmacen").setAttribute("value", ""); 

    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
    var table = $('#table_id').DataTable();
    table.clear().draw();

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
