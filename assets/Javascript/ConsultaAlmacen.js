


var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();


function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

    if ($("#NombreColumna").val() == "txtAlmacen") {
        DscTabla = "al_ctalmac";
        DscColumna = "ccod_alm";
        Nombre = "Código de almacén";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtFamilia") {
        DscTabla = "al_ctlin";
        DscColumna = "ccod_lin";
        Nombre = "Código de familia";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtCodArticulo") {
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de artículo";
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


function PasaDatosCodEmpresa() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticulo').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarArticulos() {

    if ($('#txtAlmacen').val() == null) {
        Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
        return;
    } else if ($('#txtFamilia').val() == null) {
        Mensaje('Advertencia', 'Seleccionar familia.', 'warning');
        return;
    }

    $('#modalConsultarArticulos').modal('show');

    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();

    var obj = [
        {
            "ccod_alm": $('#txtAlmacen').val(),
            "cdsc_lin": $('#txtFamilia').val()
        }
    ]

    $.ajax({
        type: "POST",
        url: 'ConsultasAlmacen.aspx/CargarArticuloSaldo',
        data: JSON.stringify({ objSaldo: obj }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d == "-1") MensajeFinSession();
            else {
                obj = response.d;
                $('#hdd_numerofilas').val(obj.length);

                $('#table_visible_ConsultarArticulos').DataTable({
                    pageLength: 5,
                    destroy: true, // importante si se recarga
                    data: obj,
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row) {
                                return '<input type="radio" name="radiob">';
                            },
                            className: "dt-body-center"
                        },
                        { data: 'ccod_articulo' },
                        { data: 'cdsc_articulo' }
                    ]
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


function Ejecutar() {

    if (navigator.onLine) {

        if ($('#txtAlmacen').val() == "" || $('#txtAlmacen').val() == null) {
            Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
            return;
        }
        //     else if ($('#txtFamilia').val() == null) {
        //        Mensaje('Advertencia', 'Seleccionar familia.', 'warning');
        //        return;
        //    }  

        $('#table_principal').DataTable().destroy();
        $('#table_visible').DataTable().destroy();

        var txtCodArticulo = document.getElementById("txtCodArticulo");
        var txtNomAticulo = document.getElementById("txtNomAticulo");
        var txtFamilia = document.getElementById("txtFamilia");
        var txtAlmacen = document.getElementById("txtAlmacen");

        $.ajax({
            type: "POST",
            url: 'ConsultasAlmacen.aspx/ConsultasAlmacenPrincipal',
            data: '{codigo: "' + txtCodArticulo.value + '",nombre: "' + txtNomAticulo.value + '" ,familia: "' + txtFamilia.value + '",almacen: "' + txtAlmacen.value + '" }',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d == "-1") {
                    MensajeFinSession();
                } else {
                    var obj = response.d;

                    $('#txtCantArt').val('');
                    $('#txtImpTot').val('');

                    if (obj.length > 0) {
                        $.ajax({
                            type: "POST",
                            url: 'ConsultasAlmacen.aspx/DatosAdicionales',
                            data: JSON.stringify({ Datos: obj }),
                            contentType: "application/json; charset=utf-8",
                            dataType: "json",
                            async: false,
                            success: function (response) {
                                var objDA = response.d;
                                $('#txtCantArt').val(objDA.ncantidad);
                                $('#txtImpTot').val(objDA.ntotal);
                            },
                            error: function (xhr, status, error) {
                                alert(error);
                            }
                        });
                    }


                    $('#hdd_numerofilas').val(obj.length);
                    $('#table_visible').DataTable({
                        data: obj,
                        "ordering": false,
                        columns: [
                            { data: 'ccod_articulo' },
                            { data: 'cdsc_articulo' },
                            { data: 'cdsc_unidadmedida' },
                            { data: 'cdsc_lin' },
                            { data: 'ncantidad', className: "dt-body-right" },
                            { data: 'npre_costo', className: "dt-body-right" },
                            { data: 'costo_tot', className: "dt-body-right" },
                            { data: 'cigv' }]
                    });

                    $('#table_principal').DataTable({
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
                            { data: 'cdsc_unidadmedida' },
                            { data: 'cdsc_lin' },
                            { data: 'ncantidad' },
                            { data: 'npre_costo' },
                            { data: 'costo_tot' },
                            { data: 'cigv' }],
                        scrollX: "2000px",
                        scrollCollapse: true,
                    });
                }

            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }

}




function Limpiar() {
    $('#txtCodArticulo').val('');
    $('#txtNomAticulo').val('');
    document.getElementById("txtFamilia").setAttribute("value", "");
    $('#txtFamilia').val('');
    document.getElementById("txtAlmacen").setAttribute("value", "");
    $('#txtAlmacen').val('');
    $('#table_visible').DataTable().destroy();
    $('#table_principal').DataTable().destroy();
    var table = $('#table_visible').DataTable();
    table.clear().draw();

}








function CargarAlmacenes() {
    var listBox = document.getElementById("txtAlmacen");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'ConsultasAlmacen.aspx/CargarAlmacenes',
        data: '{}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d == "-1") { MensajeFinSession(); return; }
            var $dropdown = $("#txtAlmacen");
            $dropdown.append($("<option />").val("").text(""));
            $.each(response.d, function () {
                $dropdown.append($("<option />").val(this.ccod_alm).text(this.cdsc_alm));
            });
        },
        error: function (xhr, status, error) { alert(error); }
    });
}

function CargarFamilia() {
    var listBox = document.getElementById("txtFamilia");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: 'ConsultasAlmacen.aspx/CargarFamilia',
        data: '{}',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d == "-1") { MensajeFinSession(); return; }
            var $dropdown = $("#txtFamilia");
            $dropdown.append($("<option />").val("%%%").text("(Todos)"));
            $.each(response.d, function () {
                $dropdown.append($("<option />").val(this.ccod_lin).text(this.cdsc_lin));
            });
        },
        error: function (xhr, status, error) { alert(error); }
    });
}

$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas();
    inicar_menu_nivel3('Consulta de Saldo', '1_li_Almacen', '2_li_ConsultaAlmacen', '3_li_Saldo', '0');

    $("#modalConsultarArticulos").draggable();

    $("#ModalDatosPersonales").draggable();

    CargarAlmacenes();
    CargarFamilia();
    document.getElementById("txtFamilia").setAttribute("value", "");
    $('#txtFamilia').val('');

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
    $('#table_visible').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });

    //    Funcion para generar exel
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
