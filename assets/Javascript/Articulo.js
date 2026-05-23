var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();


function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
    if ($("#NombreColumna").val() == "tb_codigo") {
        DscTabla = "al_articulo";
        DscColumna = "ccod_articulo";
        Nombre = "Código de artículo";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_codigoSunat") {
        DscTabla = "al_articulo";
        DscColumna = "ccod_artSunat";
        Nombre = "Código Tributario";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_descripcion") {
        DscTabla = "al_articulo";
        DscColumna = "cdsc_articulo";
        Nombre = "Nombre de artículo";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_familia") {
        DscTabla = "al_articulo";
        DscColumna = "ccod_lin";
        Nombre = "Familia de artículo";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_um") {
        DscTabla = "al_articulo";
        DscColumna = "ccod_unidadmedida";
        Nombre = "Unidad de medida de artículo";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_estado") {
        DscTabla = "al_articulo";
        DscColumna = "cstatus";
        Nombre = "Estado de artículo";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "ddl_tipArticulo") {
        DscTabla = "al_articulo";
        DscColumna = "ctip_articulo";
        Nombre = "Tipo de artículo";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "tb_stock_min") {
        DscTabla = "al_articulo";
        DscColumna = "nstock_min";
        Nombre = "Stock Minimo";
        Estado = "Opcional";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "tb_stock_max") {
        DscTabla = "al_articulo";
        DscColumna = "nstock_max";
        Nombre = "Stock Maximo";
        Estado = "Opcional";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "txtCodVari" || $("#NombreColumna").val() == "txtEdtCodVari" || $("#NombreColumna").val() == "txtRefNomVari" || $("#NombreColumna").val() == "txtEdtCodVari") {
        DscTabla = "al_cbvariante";
        DscColumna = "cdsc_variante";
        Nombre = "Nombre de la variante";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtConDetVari" || $("#NombreColumna").val() == "txtEdtConDetVari") {
        DscTabla = "al_lnvariante";
        DscColumna = "cdsc_lnvariante";
        Nombre = "Nombre del detalle de la variante";
        Estado = "Obligatorio";
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

//    Variable para almacenar el src de la imagen
var result = "";
//    Variable para recortar base64 de la imagen
var BASE64_MARKER = ';base64,';

function BorarImagen() {
    document.getElementById("imgSalida").src = "";
    $('#file-input').val('');
    document.getElementById("imgSalida").style.display = "none";
    result = "";
}


function Nuevo() {
    $("#tblDetalleVariantes > tbody").html("");
    $("#tblVariantes > tbody").html("");
    result = "";
    $('#lb_codigoVari').text("");


    $('#hdd_ultimafilaVariante').val("");
    //Para desbloquear el boton de detalle de variantes
    if (0 < document.getElementById('tblDetalleVariantes').getElementsByTagName('tbody')[0].rows.length) {
        document.getElementById("btnNuevoDetalleV").disabled = false;
    } else {
        //    Bloqueado
        document.getElementById("btnNuevoDetalleV").disabled = true;
    }

    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');

    document.getElementById("imgSalida").src = "";
    document.getElementById("imgSalida").style.display = "none";
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

    document.getElementById("ckbigv").checked = false;
    document.getElementById("ckbisc").checked = false;
}

function tab_datosclick() {


    if ($('#operacion').val() == '') {
        if ($('#hdd_ultimafila').val() != '') {

            $("#tblVariantes > tbody").html("");
            $("#tblDetalleVariantes > tbody").html("");

            $.ajax({
                type: "POST",
                url: 'Articulos.aspx/ConsultarArticulo',
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

            $.ajax({
                type: "POST",
                url: 'Articulos.aspx/ConsultarVarianteArticulo',
                data: '{codigo: "' + $('#hdd_ultimafila').val() + '" }',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                async: false,

                success: function (response) {
                    if (response.d) CompletarCamposVariantes(response.d);
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

    if ($('#table_id')[0].rows[1].cells[1].innerText == $('#hdd_ultimafila').val()) {
        $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
        $("#table_id tr:nth-child(" + 1 + ")").css('background', 'silver');

        $('#hdd_fila').val(1);

        $(".limpiar_checked").removeAttr("checked");
        $("#" + $('#tb_codigo').val()).prop('checked', true);
    }
}

function CompletarCamposVariantes(obj) {
    //$("#tableDetalleVariante > tbody").html("");

    for (var i = 0; i < obj.length; i++) {
        $("#tblVariantes").find('tbody')
            .append($('<tr>')
                .append($('<td>' + obj[i].cdsc_variante + '</td>'))
                .append($('<td style="display:none;">' + obj[i].ccod_articulo + '</td>'))
                .append($('<td style="display:none;">' + obj[i].id_cbvariante + '</td>'))
                .append($('<td style="display:none;">' + obj[i].cstate + '</td>'))
                .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarVariante" onclick="EditarVariante(this)"></a></td>'))
                .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaVariante(this)"></a></td>'))
            );
    }

    //     Buscar el detalle de la primera variante

    $.ajax({
        type: "POST",
        url: 'Articulos.aspx/ConsultarDetalleVarianteArticulo',
        data: '{ccod_articulo: "' + $('#tb_codigo').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                objE = response.d;

                for (var i = 0; i < objE.length; i++) {

                    if (objE[i].id_cbvariante == obj[0].id_cbvariante && objE[i].cstate != 'E') {
                        $("#tblDetalleVariantes").find('tbody')
                            .append($('<tr>')
                                .append($('<td>' + objE[i].cdsc_lnvariante + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].id_cbvariante + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].id_lnvariante + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].cstate + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].cdsc_variante + '</td>'))
                                .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                                .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
                            );

                    } else {
                        $("#tblDetalleVariantes").find('tbody')
                            .append($('<tr>')
                                .append($('<td style="display:none;">' + objE[i].cdsc_lnvariante + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].id_cbvariante + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].id_lnvariante + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].cstate + '</td>'))
                                .append($('<td style="display:none;">' + objE[i].cdsc_variante + '</td>'))
                                .append($('<td style="display:none; class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                                .append($('<td style="display:none; class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
                            );
                    }
                }
            } else {
                MensajeFinSession();
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CompletarCampos(obj) {

    if (obj[0].iimage == null) {
        document.getElementById("imgSalida").src = "";
        document.getElementById("imgSalida").style.display = "none";
        $('#file-input').val('');

    } else if (obj[0].iimage == "") {
        document.getElementById("imgSalida").src = "";
        document.getElementById("imgSalida").style.display = "none";
        $('#file-input').val('');
    } else {
        document.getElementById("imgSalida").src = "data:image/png;base64," + obj[0].iimage;
        document.getElementById("imgSalida").style.display = "block";
    }

    result = "data:image/png;base64," + obj[0].iimage;

    // if(obj[0].cigv=='1'){
    //   document.getElementById("ckbigv").checked = true;
    //    }else{
    //     document.getElementById("ckbigv").checked = false;
    //    }

    //     if(obj[0].cisc=='1'){
    //   document.getElementById("ckbisc").checked = true;
    //    }else{
    //     document.getElementById("ckbisc").checked = false;
    //    }
    $("#tb_stock_max").val(obj[0].nstock_max);
    $("#tb_stock_min").val(obj[0].nstock_min);

    (document.getElementById("ckbigv")).selectedIndex =
        [...(document.getElementById("ckbigv")).options].findIndex(option => option.value === (obj[0].cigv).toString());


    $("#tb_codigo").val(obj[0].ccod_articulo);
    $("#tb_codigoSunat").val(obj[0].ccod_artSunat);
    $("#tb_descripcion").val(obj[0].cdsc_articulo);

    (document.getElementById("ddl_familia")).selectedIndex =
        [...(document.getElementById("ddl_familia")).options].findIndex(option => (option.value).trim() === (obj[0].ccod_lin).trim());
    (document.getElementById("ddl_um")).selectedIndex =
        [...(document.getElementById("ddl_um")).options].findIndex(option => option.value === (obj[0].uni_medi).toString());
    (document.getElementById("ddl_estado")).selectedIndex =
        [...(document.getElementById("ddl_estado")).options].findIndex(option => option.value === (obj[0].cstatus).toString());
    (document.getElementById("ddl_tipArticulo")).selectedIndex =
        [...(document.getElementById("ddl_tipArticulo")).options].findIndex(option => option.value === (obj[0].ctip_articulo).toString());

    //     $("#txtMontoISC").val((obj[0].nmonto_isc).replace(',', '.'));
    //    $("#txtPorcentajeISC").val(obj[0].nporcentaje_isc); 
    //     (document.getElementById("txtTipoISC")).selectedIndex = 
    //    [...(document.getElementById("txtTipoISC")).options].findIndex(option => option.value === (obj[0].ctipo_isc).toString());
    //  (document.getElementById("ckbisc")).selectedIndex = 
    //    [...(document.getElementById("ckbisc")).options].findIndex(option => option.value === (obj[0].cisc).toString());


    $('#lb_codigoVari').text(obj[0].ccod_articulo + " - " + obj[0].cdsc_articulo);

}


//function table_one_click(tbody) {

////    Bloqueado
//document.getElementById("btnNuevoDetalleV").disabled = true;

//    var fila = tbody.onclick.arguments[0].target.parentElement.cells;
//    $('#hdd_ultimafila').val(fila[0].innerText);

//    $("#table_id tr:nth-child("+$('#hdd_fila').val()+")").css('background', '');
//    var index = tbody.onclick.arguments[0].target.parentElement.rowIndex;
//    $("#table_id tr:nth-child("+index+")").css('background', 'silver');
//    $('#hdd_fila').val(index);
//}

function table_two_click(tbody) {
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    var index = tbody.ondblclick.arguments[0].target.parentElement.rowIndex;
    $("#table_id tr:nth-child(" + index + ")").css('background', 'silver');
    $('#hdd_fila').val(index);

    var fila = tbody.ondblclick.arguments[0].target.parentElement.cells;
    $(".limpiar_checked").removeAttr("checked");
    $("#" + fila[1].innerText).prop('checked', true);


    $('#hdd_ultimafila').val(fila[1].innerText);


    $("#tblVariantes > tbody").html("");
    $("#tblDetalleVariantes > tbody").html("");
    $.ajax({
        type: "POST",
        url: 'Articulos.aspx/ConsultarArticulo',
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

    $.ajax({
        type: "POST",
        url: 'Articulos.aspx/ConsultarVarianteArticulo',
        data: '{codigo: "' + fila[1].innerText + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            if (response.d) CompletarCamposVariantes(response.d);
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    $('.nav-tabs li:eq(0) a').tab('show');
    Desabilitar();
    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_des").addClass("botones_hab");
}




function CargarTabla() {
    $('#table_id').DataTable().destroy();
    $('#tableArticulo').DataTable().destroy();
    var obj = llenarobjeto('Articulos.aspx/ConsultarArticulos');
    $('#hdd_numerofilas').val(obj.length);



    $('#table_id').DataTable({
        data: obj,
        "ordering": false,
        columns: [
            {
                data: 'item',
                className: "dt-body-center"
            },
            { data: 'ccod_articulo' },
            { data: 'cdsc_articulo' },
            { data: 'linea' },
            { data: 'uni_medi' },
            {
                data: 'ctip_articulo',
                render: function (data, type, row) {
                    if (data === 'B') return 'Bien';
                    if (data === 'P') return 'Producto';
                    return data;
                }
            },
            { data: 'estado' },
            { data: 'cigv' }]
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
            {
                data: 'ctip_articulo',
                render: function (data, type, row) {
                    if (data === 'B') return 'Bien';
                    if (data === 'P') return 'Producto';
                    return data;
                }
            },
            { data: 'estado' },
            { data: 'cigv' }
        ],
        scrollX: "2000px",
        scrollCollapse: true,
    });


    $('#table_id').attr("style", "width:100%");
}




$(window).load(function () {

    $(function () {
        $('#file-input').change(function (e) {
            addImage(e);
        });
        function addImage(e) {
            var file = e.target.files[0],
                imageType = /image.*/;
            if (!file.type.match(imageType))
                return;
            var reader = new FileReader();
            reader.onload = fileOnload;
            reader.readAsDataURL(file);
        }
        function fileOnload(e) {
            result = e.target.result;
            document.getElementById("imgSalida").style.display = "block";
            $('#imgSalida').attr("src", result);
        }
    });
});


function Guardar() {

    if (navigator.onLine) {

        if ($('#tb_codigo').val().length < 4 && $('#tb_codigo').val() != "") {
            Mensaje('Advertencia', 'Ingresar código de articulo mayor a 4 digitos.', 'warning');
            return;
        } else if ($('#tb_codigo').val() == "") {
            Mensaje('Advertencia', 'Ingresar código de articulo mayor a 4 digitos.', 'warning');
            return;
        } else if ($('#tb_descripcion').val() == "") {
            Mensaje('Advertencia', 'Ingresar descripción de articulo.', 'warning');
            return;
        } else if ($('#ddl_familia').val() == null) {
            Mensaje('Advertencia', 'Ingresar familia de articulo.', 'warning');
            return;
        } else if ($('#ddl_um').val() == null) {
            Mensaje('Advertencia', 'Ingresar unidad de medida de articulo.', 'warning');
            return;
        } else if ($('#ddl_estado').val() == null) {
            Mensaje('Advertencia', 'Ingresar estado de articulo.', 'warning');
            return;
        } else if ($('#ddl_tipArticulo').val() == null) {
            Mensaje('Advertencia', 'Ingresar tipo de articulo.', 'warning');
            return;
        } else if ($('#ckbigv').val() == null) {
            Mensaje('Advertencia', 'Ingresar tipo de IGV.', 'warning');
            return;
        }

        var base64Index = result.indexOf(BASE64_MARKER) + BASE64_MARKER.length;
        var base64 = result.substring(base64Index);

        var objArticulo = [{
            "ccod_articulo": $('#tb_codigo').val(),
            "cdsc_articulo": $('#tb_descripcion').val(),
            "ccod_lin": $('#ddl_familia').val(),
            "uni_medi": $('#ddl_um').val(),
            "cstatus": $('#ddl_estado').val(),
            "ctip_articulo": $('#ddl_tipArticulo').val(),
            "cigv": $('#ckbigv').val(),
            "cisc": "0",
            "iimage": base64,
            "ccod_artSunat": $('#tb_codigoSunat').val(),
            "nstock_max": $('#tb_stock_max').val(),
            "nstock_min": $('#tb_stock_min').val(),
            "ctipo_isc": '03',
            "nporcentaje_isc": '10',
            "nmonto_isc": '0'
        }]

        //"ctipo_isc": $('#txtTipoISC').val(),
        //                    "nporcentaje_isc": $('#txtPorcentajeISC').val(),
        //                    "nmonto_isc": $('#txtMontoISC').val()
        var objCabVariantes = $('#tblVariantes tr:has(td)').map(function (i, v) {
            var $td = $('td', this);
            return {
                cdsc_variante: $td.eq(0).text(),
                ccod_articulo: $td.eq(1).text(),
                id_cbvariante: $td.eq(2).text(),
                cstate: $td.eq(3).text()
            }
        }).get();

        var objDetVariantes = $('#tblDetalleVariantes tr:has(td)').map(function (i, v) {
            var $td = $('td', this);
            return {
                cdsc_lnvariante: $td.eq(0).text(),
                id_cbvariante: $td.eq(1).text(),
                id_lnvariante: $td.eq(2).text(),
                cstate: $td.eq(3).text(),
                cdsc_variante: $td.eq(4).text()
            }
        }).get();

        $.ajax({
            type: "POST",
            url: 'Articulos.aspx/Guardar',
            data: JSON.stringify({
                articulo: objArticulo,
                operacion: $('#operacion').val(),
                CabVariantes: objCabVariantes,
                DetVariantes: objDetVariantes
            }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                if (response.d == false) {
                    MensajeFinSession();
                } else {
                    obj = response.d;
                    if (response.d[1] == '2627') {
                        MensajeError('', "El codigo de artículo " + $('#tb_codigo').val() + " se encuentra registrado.", 'warning', 'Cancelar');
                    } else if (response.d[1] == 'ExisteSaldo') {
                        MensajeError('', "No se puede cambiar el artículo de bien a servicio por que cuenta con saldo en el almacén.", 'warning', 'Cancelar');
                    } else if (response.d[1] == 'OK') {
                        Mensaje('Correcto', '', 'success');
                        CargarTabla();
                        $('.nav-tabs li:eq(3) a').tab('show');
                        Desabilitar();
                        Deshacer();
                        if ($('#hdd_numerofilas').val() > 0) {
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                    } else {
                        MensajeError('', "Error: \n\n" + response.d[2], 'warning', 'Cancelar');

                    }
                }
            },
            error: function (xhr, status, error) {
                alert(xhr.responseText);
            }
        });
        result = "";

    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }

}

function Eliminar() {

    if (navigator.onLine) {

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

                var objCabVariantes = $('#tblVariantes tr:has(td)').map(function (i, v) {
                    var $td = $('td', this);
                    return {
                        cdsc_variante: $td.eq(0).text(),
                        ccod_articulo: $td.eq(1).text(),
                        id_cbvariante: $td.eq(2).text(),
                        cstate: $td.eq(3).text()
                    }
                }).get();


                $.ajax({
                    type: "POST",
                    url: 'Articulos.aspx/Eliminar',
                    data: JSON.stringify({ CabVariantes: objCabVariantes, articulo: $('#tb_codigo').val() }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,

                    success: function (response) {

                        if (response.d == "-1") MensajeFinSession();
                        else {
                            obj = response.d;
                            if (response.d[1] == 'OK') {
                                Mensaje('Correcto', '', 'success');

                                CargarTabla();
                                Desabilitar();
                                Deshacer();
                                if ($('#hdd_numerofilas').val() > 0) {
                                    $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                                }
                            } else if (response.d[1] == '547') {
                                MensajeError('', "El código del articulo (" + $('#tb_codigo').val() + ") no se puede eliminar porque se encuentra asignado.", 'warning', 'Cancelar');

                            } else {
                                MensajeError('', "Error: \n\n" + response.d[2], 'warning', 'Cancelar');
                            }

                        }
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                });

            }
        });

    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}

function Limpiar() {
    $("#tb_codigo").val("");
    $("#tb_descripcion").val("");
    $("#ddl_familia").val("");
    $("#ddl_um").val("");
    $("#ddl_estado").val("");
}

function NuevoVariante() {
    $(".readonl").prop("readonly", false);
    $("#txtCodVari").val("");
}

function NuevoDetalleVariante() {

    ///Verificar si existen elementos activos
    var CountVarActiva = '';
    var FirstVarActiva = '';
    var objCabVariantes = $('#tblVariantes tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_variante: $td.eq(0).text(),
            ccod_articulo: $td.eq(1).text(),
            id_cbvariante: $td.eq(2).text(),
            cstate: $td.eq(3).text()
        }
    }).get();
    for (var i = 0; i < objCabVariantes.length; i++) {
        if (objCabVariantes[i].cstate != 'E' && CountVarActiva == '') {
            CountVarActiva = "A";
            FirstVarActiva = objCabVariantes[i].cdsc_variante;
        }
    }

    if ($('#hdd_ultimafilaVariante').val() == "") {
        //        $('#hdd_ultimafilaVariante').val($('#tblVariantes')[0].rows[1].cells[0].innerText);
        $('#hdd_ultimafilaVariante').val(FirstVarActiva);
    }


    $("#txtRefNomVari").val($('#hdd_ultimafilaVariante').val());
    $("#txtConDetVari").val("");
}

function EditarVariante(row) {
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#txtEdtCodVari").val(currentRow.find("td:eq(0)").text());

}

function EditarDetalleVariante(row) {
    var currentRow = $(row).closest("tr");
    $("#hdd_rv").val(currentRow[0].rowIndex);
    $("#txtEdtRefNomVari").val(currentRow.find("td:eq(4)").text());
    $("#txtEdtConDetVari").val(currentRow.find("td:eq(0)").text());

}

function BtnEditarVariante() {
    var EdtCodVari = document.getElementById("txtEdtCodVari");
    if (EdtCodVari.value == "") {
        Mensaje('Advertencia', 'Ingresar Variante.', 'warning');
        return;
    }

    $("#tblVariantes")[0].rows[$("#hdd_rv").val()].cells[0].innerHTML = $("#txtEdtCodVari").val();
    $("#tblVariantes")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = "M";



    var objDetVariantes = $('#tblDetalleVariantes tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_lnvariante: $td.eq(0).text(),
            id_cbvariante: $td.eq(1).text(),
            id_lnvariante: $td.eq(2).text(),
            cstate: $td.eq(3).text(),
            cdsc_variante: $td.eq(4).text()
        }
    }).get();
    $("#tblDetalleVariantes > tbody").html("");
    for (var i = 0; i < objDetVariantes.length; i++) {

        if (objDetVariantes[i].cdsc_variante == $('#hdd_ultimafilaVariante').val() && objDetVariantes[i].cstate != 'E') {
            $("#tblDetalleVariantes").find('tbody')
                .append($('<tr>')
                    .append($('<td>' + objDetVariantes[i].cdsc_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_cbvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cstate + '</td>'))
                    .append($('<td style="display:none;">' + $("#txtEdtCodVari").val() + '</td>'))
                    .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
                );
        } else {
            $("#tblDetalleVariantes").find('tbody')
                .append($('<tr>')
                    .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_cbvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cstate + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_variante + '</td>'))
                    .append($('<td style="display:none;"> <a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                    .append($('<td style="display:none;"> <a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
                );
        }
    }
    if ($('#hdd_ultimafilaVariante').val() != "") {
        $('#hdd_ultimafilaVariante').val($('#txtEdtCodVari').val());
    }
    $("#modalEditarVariante").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal

}

function BtnEditarDetalleVariante() {

    var EdtConDetVari = document.getElementById("txtEdtConDetVari");
    if (EdtConDetVari.value == "") {
        Mensaje('Advertencia', 'Ingresar Detalle de Variante.', 'warning');
        return;
    }
    $("#tblDetalleVariantes")[0].rows[$("#hdd_rv").val()].cells[0].innerHTML = $("#txtEdtConDetVari").val();
    $("#tblDetalleVariantes")[0].rows[$("#hdd_rv").val()].cells[3].innerHTML = "M";

    $("#modalEditarDetalleVariante").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
}


function InsertarDetalleVariante() {
    var ConDetVari = document.getElementById("txtConDetVari");
    if (ConDetVari.value == "") {
        Mensaje('Advertencia', 'Ingresar Detalle de Variante.', 'warning');
        return;
    }

    $("#tblDetalleVariantes").find('tbody')
        .append($('<tr>')
            .append($('<td>' + $("#txtConDetVari").val() + '</td>'))
            .append($('<td style="display:none;">' + 'id_cbvariante' + '</td>'))
            .append($('<td style="display:none;">' + 'id_lnvariante' + '</td>'))
            .append($('<td style="display:none;">' + 'N' + '</td>'))
            .append($('<td style="display:none;">' + $('#txtRefNomVari').val() + '</td>'))
            .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
            .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
        );

    $("#modalDetalleVariante").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal

}


function InsertarVariante() {

    var CodVari = document.getElementById("txtCodVari");
    if (CodVari.value == "") {
        Mensaje('Advertencia', 'Ingresar Variante.', 'warning');
        return;
    }

    $("#tblVariantes").find('tbody')
        .append($('<tr>')
            .append($('<td>' + $("#txtCodVari").val() + '</td>'))
            .append($('<td style="display:none;">' + $('#tb_codigo').val() + '</td>'))
            .append($('<td style="display:none;">' + 'id_cbvariante' + '</td>'))
            .append($('<td style="display:none;">' + "N" + '</td>'))
            .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarVariante" onclick="EditarVariante(this)"></a></td>'))
            .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaVariante(this)"></a></td>'))
        );

    //Para desbloquear el boton de detalle de variantes
    if (0 < document.getElementById('tblVariantes').getElementsByTagName('tbody')[0].rows.length) {
        document.getElementById("btnNuevoDetalleV").disabled = false;
    } else {
        //    Bloqueado
        document.getElementById("btnNuevoDetalleV").disabled = true;
    }

    $("#modalVariante").modal('hide');//ocultamos el modal
    $('body').removeClass('modal-open');//eliminamos la clase del body para poder hacer scroll
    $('.modal-backdrop').remove();//eliminamos el backdrop del modal
}

function EliminarFilaVariante(row) {
    $(row).closest("tr").attr("style", "display: none;");
    $(row).closest("tr")[0].children[3].innerHTML = 'E';
    var objDetVariantes = $('#tblDetalleVariantes tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_lnvariante: $td.eq(0).text(),
            id_cbvariante: $td.eq(1).text(),
            id_lnvariante: $td.eq(2).text(),
            cstate: $td.eq(3).text(),
            cdsc_variante: $td.eq(4).text()
        }
    }).get();

    $("#tblDetalleVariantes > tbody").html("");

    for (var i = 0; i < objDetVariantes.length; i++) {
        $("#tblDetalleVariantes").find('tbody')
            .append($('<tr>')
                .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_lnvariante + '</td>'))
                .append($('<td style="display:none;">' + objDetVariantes[i].id_cbvariante + '</td>'))
                .append($('<td style="display:none;">' + objDetVariantes[i].id_lnvariante + '</td>'))
                .append($('<td style="display:none;">' + objDetVariantes[i].cstate + '</td>'))
                .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_variante + '</td>'))
                .append($('<td style="display:none;"> <a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                .append($('<td style="display:none;"> <a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
            );
    }


    ///Verificar si existen elementos activos para bloquear boten de nuevo detalle de variante
    var CountVarActiva = "";
    var objCabVariantes = $('#tblVariantes tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_variante: $td.eq(0).text(),
            ccod_articulo: $td.eq(1).text(),
            id_cbvariante: $td.eq(2).text(),
            cstate: $td.eq(3).text()
        }
    }).get();
    for (var i = 0; i < objCabVariantes.length; i++) {
        if (objCabVariantes[i].cstate != 'E') {
            CountVarActiva = "A"
        }
    }
    if (CountVarActiva == "A") {
        document.getElementById("btnNuevoDetalleV").disabled = false;
    } else {
        //    Bloqueado
        document.getElementById("btnNuevoDetalleV").disabled = true;
    }
    ///Limpiarla variante predeterminada
    $('#hdd_ultimafilaVariante').val('')

}

function EliminarFilaDetalleV(row) {
    $(row).closest("tr").attr("style", "display: none;");
    $(row).closest("tr")[0].children[3].innerHTML = 'E';

}


function table_one_clickVariante(tbody) {

    var numerofilas = document.getElementById('tblVariantes').getElementsByTagName('tbody')[0].rows.length;

    var fila = tbody.onclick.arguments[0].target.parentElement.cells;

    if (parseInt(numerofilas) > 0) $('#hdd_ultimafilaVariante').val(fila[0].innerText);

    $("#tblVariantes tr:nth-child(" + $('#hdd_filaVariante').val() + ")").css('background', '');
    var index = tbody.onclick.arguments[0].target.parentElement.rowIndex;
    $("#tblVariantes tr:nth-child(" + index + ")").css('background', 'silver');
    $('#hdd_filaVariante').val(index);


    //   $("#tblDetalleVariantes > tbody").html("");
    var objDetVariantes = $('#tblDetalleVariantes tr:has(td)').map(function (i, v) {
        var $td = $('td', this);
        return {
            cdsc_lnvariante: $td.eq(0).text(),
            id_cbvariante: $td.eq(1).text(),
            id_lnvariante: $td.eq(2).text(),
            cstate: $td.eq(3).text(),
            cdsc_variante: $td.eq(4).text()
        }
    }).get();
    $("#tblDetalleVariantes > tbody").html("");
    for (var i = 0; i < objDetVariantes.length; i++) {

        if (objDetVariantes[i].cdsc_variante == fila[0].innerText && objDetVariantes[i].cstate != 'E') {
            $("#tblDetalleVariantes").find('tbody')
                .append($('<tr>')
                    .append($('<td>' + objDetVariantes[i].cdsc_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_cbvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cstate + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_variante + '</td>'))
                    .append($('<td class="text-center "><a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                    .append($('<td class="text-center"><a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
                );
        } else {
            $("#tblDetalleVariantes").find('tbody')
                .append($('<tr>')
                    .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_cbvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].id_lnvariante + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cstate + '</td>'))
                    .append($('<td style="display:none;">' + objDetVariantes[i].cdsc_variante + '</td>'))
                    .append($('<td style="display:none;"> <a class="fa fa-pencil fa_enabled" data-toggle="modal" data-target="#modalEditarDetalleVariante" onclick="EditarDetalleVariante(this)"></a></td>'))
                    .append($('<td style="display:none;"> <a class="fa fa-trash fa_enabled" onclick="EliminarFilaDetalleV(this)"></a></td>'))
                );
        }
    }

}


function Editar() {


    if (0 < document.getElementById('tblVariantes').getElementsByTagName('tbody')[0].rows.length) {
        document.getElementById("btnNuevoDetalleV").disabled = false;
    } else {
        //    Bloqueado
        document.getElementById("btnNuevoDetalleV").disabled = true;
    }

    $(".disabled").prop("disabled", false);
    $("#operacion").val("editar");
    $('.fa_disabled').removeClass("fa_disabled").addClass("fa_enabled");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
}

function checked_click(row) {

    $(".limpiar_checked").removeAttr("checked");

    $(row).prop('checked', true);

    var currentRow = $(row).closest("tr");

    var eeees = $('#hdd_ultimafila').val();
    var effff = $('#hdd_numerofilas').val();

    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    $("#table_id tr:nth-child(" + currentRow[0].rowIndex + ")").css('background', 'silver');

    $('#hdd_fila').val(currentRow[0].rowIndex);

}


$(document).ready(function () {
    CargarMenu();
    ConsultaColumnas();

    $('#txtCodVari').keypress(function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            InsertarVariante();
            $('#modalVariante').modal('hide');
        }
    });

    $("#ModalDatosPersonales").draggable();

    $("#modalVariante").draggable();

    $("#modalDetalleVariante").draggable();

    inicar_menu_nivel3('Articulos', '1_li_Almacen', '2_li_Tablas', '3_li_Articulos', '2');
    CargarTabla();

    if ($('#hdd_numerofilas').val() > 0) {
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }



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

    //    Funcion para generar exel
    $("#thVariantes").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            $('#tableVariante').DataTable().destroy();

            var objDetVariantes = $('#tblVariantes tr:has(td)').map(function (i, v) {
                var $td = $('td', this);
                return {
                    cdsc_variante: $td.eq(0).text()
                }
            }).get();

            $('#tableVariante').DataTable({
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
                data: objDetVariantes,
                columns: [
                    { data: 'cdsc_variante' }],
                scrollX: "2000px",
                scrollCollapse: true,
            });


            var blob = new Blob([document.getElementById('tableVarianteExportarExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

    //    Funcion para generar exel
    $("#thDetalleVariantes").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            $('#tableDetalleVariante').DataTable().destroy();

            var objDetVariantes = $('#tblDetalleVariantes tr:has(td)').map(function (i, v) {
                var $td = $('td', this);
                return {
                    cdsc_lnvariante: $td.eq(0).text()
                }
            }).get();

            $('#tableDetalleVariante').DataTable({
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
                data: objDetVariantes,
                columns: [
                    { data: 'cdsc_lnvariante' }],
                scrollX: "2000px",
                scrollCollapse: true,
            });

            var blob = new Blob([document.getElementById('tableDetalleVarianteExportarExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

});
