
var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
    if ($("#NombreColumna").val() == "tb_codigo") {
        DscTabla = "ad_usuario";
        DscColumna = "ccod_usuario";
        Nombre = "Usuario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_cpassw") {
        DscTabla = "ad_usuario";
        DscColumna = "cpassw";
        Nombre = "Contraseña";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_descripcion") {
        DscTabla = "ad_usuario";
        DscColumna = "cdsc_usuario";
        Nombre = "Nombre de usuario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_cdirec") {
        DscTabla = "ad_usuario";
        DscColumna = "cdirc_usuario";
        Nombre = "Dirección de usuario";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "dl_rol") {
        DscTabla = "ad_usuario";
        DscColumna = "cdsc_rol";
        Nombre = "Rol de usuario";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "ddl_estado") {
        DscTabla = "ad_usuario";
        DscColumna = "cstatus";
        Nombre = "Estado de usuario";
        Estado = "Obligatorio";
        TipoDato = "";

    } else if ($("#NombreColumna").val() == "ddl_tienda") {
        DscTabla = "ad_usuario";
        DscColumna = "ccod_tiend";
        Nombre = "Código de tienda";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_almacen") {
        DscTabla = "ad_usuario";
        DscColumna = "ccod_almacen";
        Nombre = "Código de almacen";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_caja") {
        DscTabla = "ad_usuario";
        DscColumna = "ccod_caja";
        Nombre = "Código de caja";
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


//    Variable para almacenar el src de la imagen
var result = "";
//    Variable para recortar base64 de la imagen
var BASE64_MARKER = ';base64,';
function CargarRolesSelect() {
    var obj = llenarobjeto('../Administracion/Roles.aspx/ConsultarRoles');
    var $dd = $("#dl_rol");
    $dd.empty().append($("<option/>").val("").text(""));
    if (obj && obj.length > 0) {
        $.each(obj, function () {
            $dd.append($("<option/>").val(this.id_rol).text(this.cdescripcion));
        });
    }
}

function CargarTiendasSelect() {
    var obj = llenarobjeto('../Interfaces/Home.aspx/CargarTiendaDashboard');
    var $dd = $("#ddl_tienda");
    $dd.empty().append($("<option/>").val("").text(""));
    if (obj && obj.length > 0) {
        $.each(obj, function () {
            $dd.append($("<option/>").val(this.ccod_tiend).text(this.cnombr));
        });
    }
}
function mostrarContrasenaUsu() {
    //         Mensaje('Advertencia', 'Ingrese Estado de Usuario', 'warning');

    var tipo = document.getElementById("tb_cpassw");

    if (tipo.type == "password") {

        $("#UsuActual").hide();
        $("#UsuActual2").show();
        tipo.type = "text";
    } else {
        $("#UsuActual2").hide();
        $("#UsuActual").show();
        tipo.type = "password";
    }
}


function Nuevo() {
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    document.getElementById("imgSalida").src = "";
    document.getElementById("imgSalida").style.display = "none";
    result = "";
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

    document.getElementById("idCkPermDescu").checked = false;
}

function tab_datosclick() {
    if ($('#operacion').val() == '') {
        if ($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Usuarios.aspx/ConsultarUsuario',
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
    if ($('#table_id')[0].rows[1].cells[1].innerText == $('#hdd_ultimafila').val()) {
        $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
        $("#table_id tr:nth-child(" + 1 + ")").css('background', 'silver');

        $('#hdd_fila').val(1);

        $(".limpiar_checked").removeAttr("checked");
        $("#" + $('#tb_codigo').val()).prop('checked', true);
    }
}



function CargarCajas() {

    var listBox = document.getElementById("ddl_caja");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'Usuarios.aspx/ConsultarCajasEmpActivos',
        data: '{tienda: "' + $("#ddl_tienda").val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d) {
                var $dropdown = $("#ddl_caja");

                $dropdown.append($("<option />").val("").text(""));

                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.ccod_caja).text(this.cdsc_caja));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function CargarAlmacenes() {

    var listBox = document.getElementById("ddl_almacen");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'Usuarios.aspx/ConsultarAlmEmpActivos',
        data: '{tienda: "' + $("#ddl_tienda").val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {

            if (response.d) {
                var $dropdown = $("#ddl_almacen");
                $dropdown.append($("<option />").val("").text(""));
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.ccod_alm).text(this.cdsc_alm));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}

function checked_click(row) {
    $(".limpiar_checked").removeAttr("checked");
    $(row).prop('checked', true);
    var currentRow = $(row).closest("tr");
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    $("#table_id tr:nth-child(" + currentRow[0].rowIndex + ")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);
}

//function table_one_click(tbody){
//    var fila = tbody.onclick.arguments[0].target.parentElement.cells;
//    if($('#hdd_numerofilas').val()>0) $('#hdd_ultimafila').val(fila[0].innerText);
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

    if ($('#hdd_numerofilas').val() > 0) {
        $.ajax({
            type: "POST",
            url: 'Usuarios.aspx/ConsultarUsuario',
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
}

function CompletarCampos(obj) {
    $("#tb_codigo").val(obj[0].ccod_usuario);
    $("#tb_descripcion").val(obj[0].cdsc_usuario);
    $("#tb_cdirec").val(obj[0].cdirec);
    $("#tb_cpassw").val(obj[0].cpassw);

    var dd = document.getElementById("dl_rol");
    var idxRol = [...dd.options].findIndex(o => o.value == obj[0].id_rol);
    if (idxRol === -1) idxRol = [...dd.options].findIndex(o => o.text === obj[0].cdsc_rol);
    dd.selectedIndex = idxRol;

    var dd1 = document.getElementById("ddl_tienda");
    dd1.selectedIndex = [...dd1.options].findIndex(o => o.value === obj[0].ccod_tiend);

    var dd2 = document.getElementById("ddl_estado");
    dd2.selectedIndex = [...dd2.options].findIndex(o => o.value == obj[0].estado);

    CargarAlmacenes();
    CargarCajas();

    document.getElementById("ddl_almacen").value = (obj[0].ccod_almacen ?? '').trim();
    document.getElementById("ddl_caja").value = (obj[0].ccod_caja ?? '').trim();

    if (obj[0].ifoto) {
        document.getElementById("imgSalida").src = "data:image/png;base64," + obj[0].ifoto;
        document.getElementById("imgSalida").style.display = "block";
        result = "data:image/png;base64," + obj[0].ifoto;
    } else {
        document.getElementById("imgSalida").src = "";
        document.getElementById("imgSalida").style.display = "none";
        $('#file-input').val('');
    }

    document.getElementById("idCkPermDescu").checked = obj[0].cperm_descn == '1';
}

function Limpiar() {
    $("#tb_codigo").val("");
    $("#tb_descripcion").val("");
    $("#tb_cpassw").val("");
    $("#tb_cdirec").val("");
    $("#dl_rol").val("");
    $("#ddl_tienda").val("");
    $("#ddl_estado").val("");
    $("#ddl_almacen").val("");
    $("#ddl_caja").val("");
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

                $.ajax({
                    type: "POST",
                    url: 'Usuarios.aspx/Eliminar',
                    data: '{usuario: "' + $('#tb_codigo').val() + '" }',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,

                    success: function (response) {

                        if (response.d == "-1") MensajeFinSession();
                        else {
                            if (response.d == true) {
                                Mensaje('Correcto', '', 'success');
                                CargarTabla();
                                if ($('#hdd_numerofilas').val() > 0) {
                                    $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                                }
                                $('.nav-tabs li:eq(1) a').tab('show');
                                Desabilitar();
                                Deshacer();
                                CargarFotoUsuario();
                            }
                            if (response.d == false) Mensaje('Error', 'No se realizó la operación', 'error');
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

$(document).ready(function () {

    CargarMenu();

    ConsultaColumnas();
    CargarRolesSelect();
    CargarTiendasSelect();

    //    Funcion para generar exel
    $("#thtableUsuario").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel

            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");

        }
    });


    $("#ModalDatosPersonales").draggable();

    inicar_menu_nivel2('Usuarios', '1_li_Administracion', '2_li_Usuarios', '1');
    CargarTabla();
    if ($('#hdd_numerofilas').val() > 0) {
        $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
    }
});

function CargarTabla() {

    var obj = llenarobjeto('Usuarios.aspx/ConsultarUsuarios');
    $('#table_id').DataTable().destroy();
    $('#tableUsuario').DataTable().destroy();
    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({

        data: obj,
        "ordering": false,
        columns: [
            {
                data: 'item',
                className: "dt-body-center"
            },
            { data: 'ccod_usuario' },
            { data: 'cdsc_usuario' },
            { data: 'cdirec' },
            { data: 'cdsc_rol' },
            { data: 'ccod_tiend' },
            {
                data: 'estado',
                render: function (data) {
                    return data == 1 ? 'Activo' : 'Inactivo';
                }
            }
        ]
    });
    $('#tableUsuario').DataTable({
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
            { data: 'ccod_usuario' },
            { data: 'cdsc_usuario' },
            { data: 'cdirec' },
            { data: 'cdsc_rol' },
            { data: 'ccod_tiend' },
            {
                data: 'estado',
                render: function (data) {
                    return data == 1 ? 'Activo' : 'Inactivo';
                }
            }
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


function BorarImagen() {
    document.getElementById("imgSalida").src = "";
    $('#file-input').val('');
    document.getElementById("imgSalida").style.display = "none";
    result = "";
}

function Guardar() {

    if (navigator.onLine) {

        if ($('#operacion').val() == "nuevo") {
            if ($('#tb_codigo').val().length != 8 && $('#tb_codigo').val() != "") {
                Mensaje('Advertencia', 'Ingresar codigo de usuario de 8 digitos.', 'warning');
                return;
            }
        }

        if ($('#tb_cpassw').val() == "") {
            Mensaje('Advertencia', 'Ingresar contraseña.', 'warning');
            return;
        } else if ($('#tb_cdirec').val() == "") {
            Mensaje('Advertencia', 'Ingresar dirección.', 'warning');
            return;
        } else if ($('#tb_descripcion').val() == "") {
            Mensaje('Advertencia', 'Ingresar nombre.', 'warning');
            return;
        } else if ($('#dl_rol').val() == null) {
            Mensaje('Advertencia', 'Ingresar rol.', 'warning');
            return;
        } else if ($('#ddl_estado').val() == null) {
            Mensaje('Advertencia', 'Ingresar estado.', 'warning');
            return;
        } else if ($('#ddl_tienda').val() == null) {
            Mensaje('Advertencia', 'Ingresar tienda.', 'warning');
            return;
        }

        var base64Index = result.indexOf(BASE64_MARKER) + BASE64_MARKER.length;
        var base64 = result.substring(base64Index);

        var objUsuario = [
            {
                "ccod_usuario": $('#tb_codigo').val(),
                "cdsc_usuario": $('#tb_descripcion').val(),
                "cpassw": $('#tb_cpassw').val(),
                "cdirec": $('#tb_cdirec').val(),
                "cdsc_rol": $('#dl_rol').val(),
                "ccod_tiend": $('#ddl_tienda').val(),
                "id_estado": $('#ddl_estado').val(),
                "ccod_almacen": $('#ddl_almacen').val(),
                "ccod_caja": $('#ddl_caja').val(),
                "ifoto": base64,
                "cperm_descn": document.getElementById("idCkPermDescu").checked
            }
        ]

        $.ajax({
            type: "POST",
            url: 'Usuarios.aspx/Guardar',
            data: JSON.stringify({ usuario: objUsuario, operacion: $('#operacion').val() }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                if (response.d == "-1") MensajeFinSession();
                else {

                    if (response.d == 'OK') {
                        Mensaje('Correcto', '', 'success');
                        CargarTabla();
                        if ($('#hdd_numerofilas').val() > 0) {
                            $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                        }
                        $('.nav-tabs li:eq(1) a').tab('show');
                        Desabilitar();
                        Deshacer();
                        CargarFotoUsuario();
                    } else if (response.d == 'UsuRep') {
                        Mensaje('Error', 'Ya existe un usuario registrado con este codigo (' + $('#tb_codigo').val() + ')', 'error');
                    } else if (response.d == 'UsuMax') {
                        Mensaje('Error', 'Se ha llegado al limite de usuarios permitidos', 'error');
                    } else if (response.d == 'FALLIDO') {
                        Mensaje('Error', 'No se realizó la operación', 'error');
                    } else {
                        Mensaje('Error', 'No se realizó la operación', 'error');
                    }
                }
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });
        result = "";
    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}