var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

 function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "slListPrec"){
        DscTabla = "fa_cblistpre";
        DscColumna = "cdsc_cblistpre";
        Nombre = "Descripción de lista de precio";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtFamilia"){
        DscTabla = "al_ctlin";
        DscColumna = "cdsc_lin";
        Nombre = "Nombre de familia";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "slUniMedida"){
        DscTabla = "al_unidadmedida";
        DscColumna = "cdsc_unidadmedida";
        Nombre = "Descripción de la unidad de medida";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
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
 
function Ejecutar() { 
    
    if(navigator.onLine) {

    if ($('#slListPrec').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar lista de precios.', 'warning');
        return;
    } else if ($('#txtFamilia').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar familia.', 'warning');
        return;
    } else if ($('#slUniMedida').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar unidad de medida.', 'warning');
        return;
    }

    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
     
    var objArticulo = [  {
                    "ccod_cblistpre": $('#slListPrec').val(),
                    "ccod_articulo": $('#txtCodArticulo').val(),
                    "cdsc_articulo": $('#txtNomAticulo').val(),
                    "ccod_lin": $('#txtFamilia').val(),
                    "ccod_unidadmedida": $('#slUniMedida').val()
                     } ]  

    $.ajax({
        type: "POST",
        url: 'ConsultaListPrecio.aspx/ConsultaListPrecioPricipal',
        data: JSON.stringify({ 
            articulo: objArticulo
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
               data: obj,
                columns: [
                        { data: 'ccod_cblistpre' },
                        { data: 'cdsc_cblistpre' },
                        { data: 'ccod_articulo' },
                        { data: 'cdsc_articulo' },
                        { data: 'cdsc_lin' },
                        { data: 'csim_unidadmedida' },
                        { data: 'npre_uni' } 
                    ]
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
               { data: 'ccod_cblistpre' },
                { data: 'cdsc_cblistpre' },
                { data: 'ccod_articulo' },
                { data: 'cdsc_articulo' },
                { data: 'cdsc_lin' },
                { data: 'csim_unidadmedida' },
                 { data: 'npre_uni' } ],
                    scrollX: "2000px",
                scrollCollapse: true,
        });
            }
          
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
//  CargarEstadisticasListPrecio();
    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
}

 

function CargarEstadisticasListPrecio() {

 var objArticulo = [  {
                    "ccod_cblistpre": $('#slListPrec').val(),
                    "ccod_articulo": $('#txtCodArticulo').val(),
                    "cdsc_articulo": $('#txtNomAticulo').val(),
                    "ccod_lin": $('#txtFamilia').val(),
                    "ccod_unidadmedida": $('#slUniMedida').val()
                     } ]  


    $.ajax({
        type: "POST",
        url: 'ConsultaListPrecio.aspx/CargarEstadisticasListPrecio',
        data: JSON.stringify({ 
            articulo: objArticulo
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
        
        objDocMas = response.d;
Highcharts.chart('containerCemiCirculo', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: 0,
        plotShadow: false
    },
    title: {
        text: 'Cantidad de<br>articulos por<br>familia',
        align: 'center',
        verticalAlign: 'middle',
        y: 60
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            dataLabels: {
                enabled: true,
                distance: -50,
                style: {
                    fontWeight: 'bold',
                    color: 'white'
                }
            },
            startAngle: -90,
            endAngle: 90,
            center: ['50%', '75%'],
            size: '110%'
        }
    },
    series: [{
        type: 'pie',
        name: 'Browser share',
        innerSize: '50%',
        data: objDocMas
    }]  
});

 
    },
        error: function (xhr, status, error) {
            alert(error);
        }
   

});
}


function PasaDatosCodEmpresa() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarArticulos() {

if ($('#slListPrec').val() ==null) {
    Mensaje('Advertencia', 'Seleccionar lista de precios.', 'warning');
    return;
} else if ($('#txtFamilia').val() ==null) {
    Mensaje('Advertencia', 'Seleccionar familia.', 'warning');
    return;
} else if ($('#slUniMedida').val() ==null) {
    Mensaje('Advertencia', 'Seleccionar unidad de medida.', 'warning');
    return;
}
     
var obj = [  {  
    "ccod_cblistpre": $('#slListPrec').val(),
    "ccod_lin": $('#txtFamilia').val(),
    "ccod_unidadmedida": $('#slUniMedida').val()
        } ]   

$('#modalConsultarArticulos').modal('show');

    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();

   $.ajax({
    type: "POST",
    url: 'ConsultaListPrecio.aspx/CargarArticuloListPrecio',
    data: JSON.stringify({ objArticuloListPrecio: obj}),
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

// FIX BUG 3.3.8: función eliminada — la versión correcta está más abajo
// (línea ~377). La primera llamaba al endpoint legacy .aspx que podía
// no devolver datos. La segunda (abajo) usa el endpoint correcto PHP.


 

function Limpiar(){
   $('#txtCodArticulo').val('');
    $('#txtNomAticulo').val('');
    $('#slListPrec').val('');   
    $('#txtFamilia').val('');
     $('#slUniMedida').val(''); 

    document.getElementById("slListPrec").setAttribute("value", ""); 
    document.getElementById("txtFamilia").setAttribute("value", "");
    document.getElementById("slUniMedida").setAttribute("value", ""); 
    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
    var table = $('#table_id').DataTable();
    table.clear().draw();

    document.getElementById('containerCemiCirculo').style.display = 'none';
}


// FIX 74 / BUG 3.27: la funcion CargarListPrecio no estaba definida en
// ningun archivo cargado por la pagina (Filtros.js solo expone
// CargarFamilia/CargarUnidadMedida), por lo que el dropdown "Lista de
// precios*" quedaba vacio. La definimos aqui hitting el endpoint
// existente ConsultarListaPrecios (precio_api.php) que ya devuelve
// id_cblistpre + ccod_cblistpre + cdsc_cblistpre.
function CargarListPrecio() {
    var listBox = document.getElementById("slListPrec");
    if (!listBox) return;
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Ventas/Precios.aspx/ConsultarListaPrecios',
        data: '{codigo: ""}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var $dd = $("#slListPrec");
                $dd.append($("<option />").val("").text(""));
                $.each(response.d, function () {
                    $dd.append($("<option />")
                        .val(this.ccod_cblistpre)
                        .text(this.cdsc_cblistpre + " (" + this.ccod_cblistpre + ")"));
                });
            }
        },
        error: function (xhr, status, error) { console.error(error); }
    });
}

$(document).ready(function () {
     CargarMenu();

     ConsultaColumnas();
  $("#modalConsultarArticulos").draggable();

  $("#ModalDatosPersonales").draggable();

CargarUnidadMedida();
CargarFamilia();
CargarListPrecio();
 document.getElementById("slListPrec").setAttribute("value", ""); 
    document.getElementById("txtFamilia").setAttribute("value", "");
    document.getElementById("slUniMedida").setAttribute("value", ""); 
     $('#slListPrec').val('');   
    $('#txtFamilia').val('');
     $('#slUniMedida').val(''); 
    inicar_menu_nivel3('Consulta de Lista de precios','1_li_Ventas','2_li_ConsultaVenta','3_li_ListaPrecios', '0');
 
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
      $("#thTablaConsultarArticulos").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exeltableExportarBuscarEmpresa
            var blob = new Blob([document.getElementById('tableExportarConsultarArticulos').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

});

 