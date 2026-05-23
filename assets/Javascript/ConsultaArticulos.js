var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

    if ($("#NombreColumna").val() == "txtFamilia") {
        DscTabla = "al_ctlin";
        DscColumna = "ccod_lin";
        Nombre = "Código de familia";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "slUniMedida") {
        DscTabla = "al_unidadmedida";
        DscColumna = "ccod_unidadmedida";
        Nombre = "Código de unidad de medida";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "slTipArticulo") {
        DscTabla = "al_articulo";
        DscColumna = "ctip_articulo";
        Nombre = "Tipo de artículo";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "slTributos") {
        DscTabla = "al_articulo";
        DscColumna = "cigv";
        Nombre = "Tipo de tributos";
        Estado = "Opcional";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "slEstado") {
        DscTabla = "al_articulo";
        DscColumna = "cstatus";
        Nombre = "Estado";
        Estado = "Opcional";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "txtCodArticulo") {
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de Artículo";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    }

    for (var i = 0; i < objColumnas.length; i++) {
        if (DscColumna == objColumnas[i].DscColumna && DscTabla == objColumnas[i].DscTabla) {
            $("#txt_nombreCampo").text(Nombre);
            $("#txt_TipoDato").text(objColumnas[i].TipoDato);
            $("#txt_estado").text(Estado);
            $("#txt_longitud").text(TipoDato + " " + objColumnas[i].longitud);
            $("#txt_cantidadEntero").text(objColumnas[i].CantEnteros);
            $("#txt_cantidadDecimales").text(objColumnas[i].CantDecimales);
        }
    }

}

function Ejecutar() {

    if (navigator.onLine) {

        if ($('#txtFamilia').val() == null) {
            Mensaje('Advertencia', 'Seleccionar familia.', 'warning');
            return;
        } else if ($('#slUniMedida').val() == null) {
            Mensaje('Advertencia', 'Seleccionar unidad de medida.', 'warning');
            return;
        } else if ($('#slTipArticulo').val() == null) {
            Mensaje('Advertencia', 'Seleccionar tipo de articulo.', 'warning');
            return;
        }

        $('#table_id').DataTable().destroy();
        $('#tableArticulo').DataTable().destroy();

        var CodArticulo = document.getElementById("txtCodArticulo");
        var NomAticulo = document.getElementById("txtNomAticulo");
        var TipArticulo = document.getElementById("slTipArticulo");

        var Tributos = document.getElementById("slTributos");
        var Familia = document.getElementById("txtFamilia");
        var UniMedida = document.getElementById("slUniMedida");

        var Estado = document.getElementById("slEstado");

        $.ajax({
            type: "POST",
            url: 'ConsultaArticulos.aspx/ConsultarArticulosPricipal',
            data: '{CodArticulo: "' + CodArticulo.value + '",NomAticulo: "' + NomAticulo.value + '" ,TipArticulo: "' + TipArticulo.value +
                '",Tributos: "' + Tributos.value + '",Familia: "' + Familia.value + '",UniMedida: "' + UniMedida.value +
                '",Estado: "' + Estado.value + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d == "-1") {
                    MensajeFinSession();
                } else {
                    document.getElementById('containerCemiCirculo').style.display = 'inline';
                    var obj = response.d;
                    $('#table_id').DataTable({

                        data: obj,
                        columns: [
                            { data: 'ccod_articulo' },
                            { data: 'cdsc_articulo' },
                            { data: 'linea' },
                            { data: 'uni_medi' },
                            { data: 'ctip_articulo' },
                            { data: 'estado' },
                            { data: 'cigv' }
                            //                          ,{ data: 'cisc' }
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
                            { data: 'ccod_articulo' },
                            { data: 'cdsc_articulo' },
                            { data: 'linea' },
                            { data: 'uni_medi' },
                            { data: 'ctip_articulo' },
                            { data: 'estado' },
                            { data: 'cigv' }],
                        //                  { data: 'cisc' }],
                        scrollX: "2000px",
                        scrollCollapse: true,
                    });
                }

            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
        // CargarEstadisticasConsArti();
    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}



function CargarEstadisticasConsArti() {

    var CodArticulo = document.getElementById("txtCodArticulo");
    var NomAticulo = document.getElementById("txtNomAticulo");
    var TipArticulo = document.getElementById("slTipArticulo");

    var Tributos = document.getElementById("slTributos");
    var Familia = document.getElementById("txtFamilia");
    var UniMedida = document.getElementById("slUniMedida");

    var Estado = document.getElementById("slEstado");

    $.ajax({
        type: "POST",
        url: 'ConsultaArticulos.aspx/CargarEstadisticasConsArti',
        data: '{CodArticulo: "' + CodArticulo.value + '",NomAticulo: "' + NomAticulo.value + '" ,TipArticulo: "' + TipArticulo.value +
            '",Tributos: "' + Tributos.value + '",Familia: "' + Familia.value + '",UniMedida: "' + UniMedida.value +
            '",Estado: "' + Estado.value + '" }',
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

$(document).ready(function () {
    CargarMenu();
    ConsultaColumnas();
    $("#modalConsultarArticulos").draggable();

    $("#ModalDatosPersonales").draggable();

    CargarUnidadMedida();
    CargarFamilia();
    document.getElementById("slTipArticulo").setAttribute("value", "");
    document.getElementById("slTributos").setAttribute("value", "");
    document.getElementById("txtFamilia").setAttribute("value", "");
    document.getElementById("slUniMedida").setAttribute("value", "");
    document.getElementById("slEstado").setAttribute("value", "");
    $('#slTipArticulo').val('');
    $('#txtFamilia').val('');
    $('#slUniMedida').val('');

    inicar_menu_nivel3('Consulta de Articulos', '1_li_Almacen', '2_li_ConsultaAlmacen', '3_li_Articulo', '0');

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

function PasaDatosCodEmpresa() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarArticulos() {
    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();

    var obj = llenarobjeto('ConsultaArticulos.aspx/CargarArticulo');

    $('#table_visible_ConsultarArticulos').DataTable({
        "pageLength": 5,
        data: obj,
        columns: [
            {
                data: 'cbx',
                render: function (data, type, row) {
                    if (type === 'display') { return '<input type="radio" name="radiob">'; }
                    return data;
                },
                className: "dt-body-center"
            },
            { data: 'ccod_articulo' },
            { data: 'cdsc_articulo' }]
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



function Limpiar() {
    $('#txtCodArticulo').val('');
    $('#txtNomAticulo').val('');

    $('#slTributos').val('');

    $('#slEstado').val('');

    $('#slTipArticulo').val('');
    $('#slUniMedida').val('');
    $('#txtFamilia').val('');
    document.getElementById("slTipArticulo").setAttribute("value", "");
    document.getElementById("slTributos").setAttribute("value", "");
    document.getElementById("txtFamilia").setAttribute("value", "");
    document.getElementById("slUniMedida").setAttribute("value", "");
    document.getElementById("slEstado").setAttribute("value", "");
    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
    var table = $('#table_id').DataTable();
    table.clear().draw();

    document.getElementById('containerCemiCirculo').style.display = 'none';
}