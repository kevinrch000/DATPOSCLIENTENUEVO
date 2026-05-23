var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";

    if ($("#NombreColumna").val() == "tb_codigo") {
        DscTabla = "co_ctcoa";
        DscColumna = "ccod_coa";
        Nombre = "Código de asociado";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_numdoc") {
        DscTabla = "co_ctcoa";
        DscColumna = "cdoc_coa";
        Nombre = "Número de docuemnto";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_descripcion") {
        DscTabla = "co_ctcoa";
        DscColumna = "cdsc_coa";
        Nombre = "Descripción de asociado";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_telefono") {
        DscTabla = "co_ctcoa";
        DscColumna = "ctelf";
        Nombre = "Teléfono de asociado";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_mail") {
        DscTabla = "co_ctcoa";
        DscColumna = "cmail";
        Nombre = "Mail de asociado";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "ddl_estado") {
        DscTabla = "co_ctcoa";
        DscColumna = "cstatus";
        Nombre = "Estado de asociado";
        Estado = "Obligatorio";
        TipoDato = "";
    } else if ($("#NombreColumna").val() == "cbProveedor") {
        DscTabla = "co_ctcoa";
        DscColumna = "cproveedor";
        Nombre = "Tipo de asociado";
        Estado = "Obligatorio";
        TipoDato = "";

    } else if ($("#NombreColumna").val() == "tb_direccion") {
        DscTabla = "co_ctcoa";
        DscColumna = "cdirc_coa";
        Nombre = "Dirección de asociado";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtDistrito") {
        DscTabla = "co_ctcoa";
        DscColumna = "cdistrito";
        Nombre = "Distrito de asociado";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtProvincia") {
        DscTabla = "co_ctcoa";
        DscColumna = "cprovincia";
        Nombre = "Provincia de asociado";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "txtDepartamento") {
        DscTabla = "co_ctcoa";
        DscColumna = "cdepartamento";
        Nombre = "Departamento de asociado";
        Estado = "Opcional";
        TipoDato = "1 hasta";
    } else if ($("#NombreColumna").val() == "tb_pais") {
        DscTabla = "co_ctcoa";
        DscColumna = "cpais";
        Nombre = "País de asociado";
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

/**
 * Llena los campos de Cliente a partir de los datos devueltos por la API
 * de SUNAT (apifacturador.msgsac.net) cuando el usuario escribe el RUC
 * en el campo #tb_codigo (o presiona el botón SUNAT). Acepta también
 * DNI (8 dígitos): no consulta la API (SUNAT no expone DNIs), pero pone
 * Tipo Persona = Natural y replica el código en #tb_numdoc.
 *
 * Campos que se autocompletan desde el endpoint ConsultaRuc3:
 *   - tb_descripcion  <- nombre_o_razon_social
 *   - tb_direccion    <- domicilio_fiscal
 *   - tb_pais         <- 'PERU' (la API es de SUNAT, siempre Perú)
 *   - tb_tipo_coa     <- contribuyente_tipo (PERSONA JURIDICA / NATURAL)
 *   - ddl_estado      <- contribuyente_estado (ACTIVO -> 1, otro -> 0)
 *   - txtDepartamento / txtProvincia / txtDistrito <- ubigeo (INEI)
 *
 * Campos manuales (la API no los provee): tb_numdoc (DNI), tb_telefono,
 * tb_mail, cbProveedor (Asociado).
 */
function BuscarDatosSunatPorCodigo() {
    var doc = ($('#tb_codigo').val() || '').trim();
    if (doc === '') return;

    if (/^\d{8}$/.test(doc)) {
        if (($('#tb_numdoc').val() || '').trim() === '') {
            $('#tb_numdoc').val(doc);
        }
        BuscarDatosDni();
        return;
    }
    if (!/^\d{11}$/.test(doc)) {
        Mensaje('Advertencia', 'El código debe ser RUC (11 dígitos) o DNI (8 dígitos).', 'warning');
        return;
    }

    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/DatosRucApi',
        data: JSON.stringify({ ruc: doc }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (!response || !response.d) {
                Mensaje('Advertencia', 'No se obtuvo respuesta del servicio SUNAT.', 'warning');
                return;
            }
            var obj = response.d;
            if (Array.isArray(obj)) obj = obj[0] || {};
            if (!obj) return;

            // Si la API no encontró el RUC, mostrar mensaje y dejar el form intacto.
            // success=false con MensajeRuc poblado es el caso típico "No existe el RUC consultado".
            var encontrado = (obj.success === true || obj.success === 'true' || obj.success === 1);
            if (!encontrado && !obj.nombre_o_razon_social) {
                var msg = obj.mensaje && obj.mensaje !== 'Exito!!' ? obj.mensaje : 'El RUC consultado no existe en SUNAT.';
                Mensaje('Advertencia', msg, 'warning');
                return;
            }

            if (obj.nombre_o_razon_social) {
                $('#tb_descripcion').val(obj.nombre_o_razon_social);
            }
            if (obj.domicilio_fiscal) {
                $('#tb_direccion').val(obj.domicilio_fiscal);
            }
            if (($('#tb_pais').val() || '').trim() === '') {
                $('#tb_pais').val('PERU');
            }

            // Persona Natural / Jurídica. Si la API responde 'tipo', usamos eso;
            // si no, el primer dígito del RUC (2x = Jurídica, 1x = Natural).
            if ($('#tb_tipo_coa').length) {
                var tipoApi = (obj.tipo || '').toString().toUpperCase();
                var esJuridica = (tipoApi.indexOf('JURIDIC') >= 0) ||
                    (tipoApi === '' && doc.charAt(0) === '2');
                $('#tb_tipo_coa').val(esJuridica ? '0' : '1');
            }

            // Estado: la API devuelve 'estado' (ACTIVO / SUSPENDIDA / BAJA / ...).
            if ($('#ddl_estado').length && obj.estado) {
                var est = obj.estado.toString().toUpperCase();
                $('#ddl_estado').val(est.indexOf('ACTIV') >= 0 ? '1' : '0');
            }

            // Ubigeo (6 dígitos INEI) -> Departamento (2) / Provincia (4) / Distrito (6).
            if (obj.ubigeo) {
                AplicarUbigeoDesdeCodigo(obj.ubigeo);
            }
        },
        error: function () {
            Mensaje('Advertencia', 'No se pudo consultar el RUC en SUNAT. Verifique conexión.', 'warning');
        }
    });
}

/**
 * Dado un código INEI de 6 dígitos (ej. '070103'), selecciona el
 * departamento, provincia y distrito en los <select> correspondientes,
 * recargando las opciones dependientes en cascada. Los IDs de las
 * tablas Departamento/Provincia/Distrito coinciden con los INEI.
 */
function AplicarUbigeoDesdeCodigo(codigoUbigeo) {
    var ubi = (codigoUbigeo || '').toString().trim();
    if (ubi.length < 2) return;

    var depId = ubi.substring(0, 2);
    var provId = (ubi.length >= 4) ? ubi.substring(0, 4) : '';
    var distId = (ubi.length >= 6) ? ubi.substring(0, 6) : '';

    if (!$('#txtDepartamento option[value="' + depId + '"]').length) return;
    $('#txtDepartamento').val(depId);

    if (provId) {
        CargarProvincia();
        if ($('#txtProvincia option[value="' + provId + '"]').length) {
            $('#txtProvincia').val(provId);
        }
    }
    if (distId) {
        CargarDistrito();
        if ($('#txtDistrito option[value="' + distId + '"]').length) {
            $('#txtDistrito').val(distId);
        }
    }
}

// Handlers legacy preservados (otras pantallas siguen invocando estos nombres).
function BuscarDatosRuc() {
    BuscarDatosSunatPorCodigo();
}

function BuscarDatosDni() {
    var dni = ($('#tb_numdoc').val() || '').trim();
    if (dni === '') return;
    if (!/^\d{8}$/.test(dni)) {
        Mensaje('Advertencia', 'El DNI debe tener 8 dígitos.', 'warning');
        return;
    }
    if (($('#tb_codigo').val() || '').trim() === '') {
        $('#tb_codigo').val(dni);
    }
    if ($('#tb_tipo_coa').length) {
        $('#tb_tipo_coa').val('1'); // Natural
    }
    if (($('#tb_pais').val() || '').trim() === '') {
        $('#tb_pais').val('PERU');
    }

    $('#btn_buscar_dni').addClass('btn-lookup--loading');
    $.ajax({
        type: "POST",
        url: 'Clientes.aspx/ConsultarDni',
        data: JSON.stringify({ dni: dni }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: true,
        success: function (response) {
            if (!response || response.d === "-1") {
                MensajeFinSession();
                return;
            }
            AplicarDatosDni(response.d, dni);
        },
        error: function () {
            Mensaje('Advertencia', 'No se pudo consultar el DNI. Verifique conexión.', 'warning');
        },
        complete: function () {
            $('#btn_buscar_dni').removeClass('btn-lookup--loading');
        }
    });
}

function AplicarDatosDni(data, dni) {
    if (!data || data.success === false) {
        var msg = data && data.mensaje ? data.mensaje : 'No se encontraron datos para el DNI.';
        Mensaje('Advertencia', msg, 'warning');
        return;
    }

    var nombre = (data.nombre_completo || '').toString().trim();
    if (nombre !== '') {
        $('#tb_descripcion').val(nombre);
    }
    if (data.direccion) {
        $('#tb_direccion').val(data.direccion);
    }
    if (($('#tb_codigo').val() || '').trim() === '') {
        $('#tb_codigo').val(dni);
    }
    if ($('#tb_tipo_coa').length) $('#tb_tipo_coa').val('1');
    if (($('#tb_pais').val() || '').trim() === '') $('#tb_pais').val('PERU');
    if ($('#ddl_estado').length) $('#ddl_estado').val('1');

    $('#tb_numdoc').attr('title', buildDniTitle(data));

    if (data.ubigeo) {
        AplicarUbigeoDesdeCodigo(data.ubigeo);
    } else {
        AplicarUbigeoDesdeNombres(data.departamento, data.provincia, data.distrito);
    }
}

function buildDniTitle(data) {
    var parts = ['DNI verificado'];
    if (data.codigo_verificacion) parts.push('Código verificación: ' + data.codigo_verificacion);
    if (data.genero) parts.push('Género: ' + data.genero);
    if (data.fecha_nacimiento) parts.push('Nacimiento: ' + data.fecha_nacimiento);
    return parts.join(' | ');
}

function AplicarUbigeoDesdeNombres(departamento, provincia, distrito) {
    if (!departamento) return;
    if (_seleccionarOpcion(document.getElementById("txtDepartamento"), departamento)) {
        CargarProvincia();
        if (provincia && _seleccionarOpcion(document.getElementById("txtProvincia"), provincia)) {
            CargarDistrito();
            if (distrito) {
                _seleccionarOpcion(document.getElementById("txtDistrito"), distrito);
            }
        }
    }
}

function CargarDepartamento() {
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
            if (response.d) {
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

function CargarProvincia() {
    if ($('#txtDepartamento').val() == "") {
        return;
    }
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
            if (response.d) {
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

function CargarDistrito() {
    if ($('#txtProvincia').val() == "") {
        return;
    }

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
            if (response.d) {
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

function Nuevo() {
    $('#tb_codigo').focus();
    $('.nav-tabs li:eq(0) a').tab('show');
    //    document.getElementById("cbProveedor").checked = false;
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
}

function tab_datosclick() {
    if ($('#operacion').val() == '') {
        if ($('#hdd_ultimafila').val() != '') {

            $.ajax({
                type: "POST",
                url: 'Clientes.aspx/ConsultarCliente',
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
                    url: 'Clientes.aspx/Eliminar',
                    data: '{cliente: "' + $('#tb_codigo').val() + '" }',
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

function Guardar() {

    if (navigator.onLine) {

        var codVal = ($('#tb_codigo').val() || '').trim();
        var dniVal = ($('#tb_numdoc').val() || '').trim();

        // El código del asociado es el RUC SUNAT (o un DNI). Validamos formato.
        if (codVal === '') {
            Mensaje('Advertencia', 'Ingresar código (RUC 11 díg. o DNI 8 díg.).', 'warning');
            return;
        }
        if (!/^\d{11}$/.test(codVal) && !/^\d{8}$/.test(codVal)) {
            Mensaje('Advertencia', 'El código debe ser RUC (11 díg.) o DNI (8 díg.).', 'warning');
            return;
        }
        if (dniVal !== '' && !/^\d{8}$/.test(dniVal)) {
            Mensaje('Advertencia', 'El DNI debe tener 8 dígitos.', 'warning');
            return;
        }
        if (codVal.length < 4) {
            Mensaje('Advertencia', 'Ingresar código de asociado mayor a 4 digitos.', 'warning');
            return;
        } else if ($('#tb_codigo').val() == "") {
            Mensaje('Advertencia', 'Ingresar código de asociado.', 'warning');
            return;
        } else if ($('#tb_descripcion').val() == "") {
            Mensaje('Advertencia', 'Ingresar descripción de asociado.', 'warning');
            return;
        } else if ($('#ddl_estado').val() == null) {
            Mensaje('Advertencia', 'Ingresar estado de asociado.', 'warning');
            return;
        } else if ($('#cbProveedor').val() == null) {
            Mensaje('Advertencia', 'Ingresar tipo de asociado.', 'warning');
            return;
        } else if ($('#tb_direccion').val() == "") {
            Mensaje('Advertencia', 'Ingresar dirección de asociado.', 'warning');
            return;
        } else if ($('#tb_tipo_coa').val() == "") {
            Mensaje('Advertencia', 'Ingresar el tipo de persona.', 'warning');
            return;
        }

        var objCliente = [
            {
                "ccod_coa": $('#tb_codigo').val(),
                "cdoc_coa": $('#tb_numdoc').val(),
                "cdsc_coa": $('#tb_descripcion').val(),
                "ctelf": $('#tb_telefono').val(),
                "cmail": $('#tb_mail').val(),
                "ctipo_coa": $('#tb_tipo_coa').val(),
                "cpais": $('#tb_pais').val(),
                // Los campos cdepartamento/cprovincia/cdistrito de Coa son VARCHAR(2/4/6)
                // y guardan el codigo INEI (ej. "15", "1501", "150101"). Antes se enviaba
                // option:selected.text() ("LIMA"/"CALLAO") que terminaba truncado por la
                // BD a 2/4/6 chars y luego CompletarCampos no podia recuperar la seleccion.
                "cdepartamento": $("#txtDepartamento").val(),
                "cprovincia": $("#txtProvincia").val(),
                "cdistrito": $("#txtDistrito").val(),
                "cdirc_coa": $('#tb_direccion').val(),
                "cstatus": $('#ddl_estado').val(),
                "cproveedor": $('#cbProveedor').val(),
                // Convenio: ccod_coa = cruc_coa = RUC SUNAT (el SP cae al ccod_coa si está vacío).
                "cruc_coa": (/^\d{11}$/.test(codVal) ? codVal : '')
            }
        ]

        $.ajax({
            type: "POST",
            url: 'Clientes.aspx/Guardar',
            data: JSON.stringify({ cliente: objCliente, operacion: $('#operacion').val() }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,

            success: function (response) {

                // El backend (api/cliente_api.php case 'Guardar') ahora responde
                // un array [{ ccod_coa: '...' }] tras un guardado correcto. El check
                // legacy 'response.d==true' nunca matcheaba ese formato, asi que el
                // usuario veia el registro guardado en BD pero sin Mensaje('Correcto')
                // ni cambio a la pestania Lista. Aceptamos cualquier respuesta
                // truthy distinta de '-1'/'false'/array vacio como exito.
                if (response.d === "-1") { MensajeFinSession(); return; }
                if (response.d === false || response.d === null || response.d === '' ||
                    (Array.isArray(response.d) && response.d.length === 0)) {
                    Mensaje('Error', 'No se realizó la operación', 'error');
                    return;
                }
                Mensaje('Correcto', '', 'success');
                CargarTabla();
                if ($('#hdd_numerofilas').val() > 0) {
                    $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
                }
                $('.nav-tabs li:eq(1) a').tab('show');
                Desabilitar();
                Deshacer();
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    } else {
        Mensaje('Error', 'Sin acceso a internet.', 'error');
    }
}

// Selecciona un <option> en `selectEl` cuyo `value` (preferido) o `text`
// coincida con `target`. Devuelve true si encontro coincidencia.
function _seleccionarOpcion(selectEl, target) {
    if (!selectEl || target == null) return false;
    var t = target.toString().trim();
    if (t === '') return false;
    var opts = selectEl.options;
    for (var i = 0; i < opts.length; i++) {
        if (opts[i].value === t) { selectEl.selectedIndex = i; return true; }
    }
    var tUpper = t.toUpperCase();
    for (var j = 0; j < opts.length; j++) {
        if ((opts[j].text || '').toString().trim().toUpperCase() === tUpper) {
            selectEl.selectedIndex = j;
            return true;
        }
    }
    return false;
}

function CompletarCampos(obj) {

    $("#tb_codigo").val(obj[0].ccod_coa);
    $("#tb_numdoc").val(obj[0].cdoc_coa);
    $("#tb_descripcion").val(obj[0].cdsc_coa);
    $("#tb_telefono").val(obj[0].ctelf);
    $("#tb_mail").val(obj[0].cmail);

    $("#tb_pais").val(obj[0].cpais);
    //    $("#tb_departamento").val(obj[0].cdepartamento);
    //    $("#tb_provincia").val(obj[0].cprovincia);
    //    $("#tb_distrito").val(obj[0].cdistrito);
    $("#tb_direccion").val(obj[0].cdirc_coa);

    // Departamento/Provincia/Distrito: matchear por value (INEI id 2/4/6 dig.)
    // primero y caer a text (nombre) si la BD tiene registros legacy con texto
    // truncado. Recargar las listas dependientes en cascada antes de seleccionar.
    _seleccionarOpcion(document.getElementById("txtDepartamento"), obj[0].cdepartamento);
    CargarProvincia();
    _seleccionarOpcion(document.getElementById("txtProvincia"), obj[0].cprovincia);
    CargarDistrito();
    _seleccionarOpcion(document.getElementById("txtDistrito"), obj[0].cdistrito);

    // Estado: la BD guarda 'A'/'I' pero el dropdown usa value='1'/'0'.
    // Convertimos antes de seleccionar. Aceptamos tambien '1'/'0' directos
    // o ACTIVO/INACTIVO por si llega normalizado.
    var estadoRaw = (obj[0].cstatus || '').toString().trim().toUpperCase();
    var estadoVal = (estadoRaw === 'A' || estadoRaw === '1' || estadoRaw === 'ACTIVO') ? '1'
        : (estadoRaw === 'I' || estadoRaw === '0' || estadoRaw === 'INACTIVO') ? '0'
            : estadoRaw;
    _seleccionarOpcion(document.getElementById("ddl_estado"), estadoVal);

    _seleccionarOpcion(document.getElementById("cbProveedor"), (obj[0].cproveedor || '').toString().trim());
    _seleccionarOpcion(document.getElementById("tb_tipo_coa"), (obj[0].ctipo_coa || '').toString().trim());
}



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
            url: 'Clientes.aspx/ConsultarCliente',
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

$(document).ready(function () {
    CargarMenu();
    CargarDepartamento();
    ConsultaColumnas();
    $("#ModalDatosPersonales").draggable();

    //    Funcion para generar exel
    $("#thtableClientes").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tablePrincipalExportExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

    inicar_menu_nivel3('Asociados', '1_li_Ventas', '2_li_TablasVentas', '2_li_Clientes', '1');
    CargarTabla();
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val($('#table_id')[0].rows[1].cells[1].innerText);
});

function checked_click(row) {
    $(".limpiar_checked").removeAttr("checked");
    $(row).prop('checked', true);
    var currentRow = $(row).closest("tr");
    if ($('#hdd_numerofilas').val() > 0) $('#hdd_ultimafila').val(currentRow.find("td:eq(1)").text());
    $("#table_id tr:nth-child(" + $('#hdd_fila').val() + ")").css('background', '');
    $("#table_id tr:nth-child(" + currentRow[0].rowIndex + ")").css('background', 'silver');
    $('#hdd_fila').val(currentRow[0].rowIndex);
}

// Mapea el código crudo cproveedor / cstatus a etiquetas legibles para Lista.
function _renderAsociado(data) {
    var v = (data == null) ? '' : data.toString().trim();
    if (v === '1') return 'Proveedor';
    if (v === '0') return 'Cliente';
    if (v === '2') return 'Otros';
    return v;
}
function _renderEstado(data) {
    var v = (data == null) ? '' : data.toString().trim().toUpperCase();
    if (v === '1' || v === 'A' || v === 'ACTIVO') return 'Activo';
    if (v === '0' || v === 'I' || v === 'INACTIVO') return 'Inactivo';
    return data || '';
}

function CargarTabla() {
    $('#table_id').DataTable().destroy();
    $('#tableClientes').DataTable().destroy();
    var obj = llenarobjeto('Clientes.aspx/ConsultarClientes');

    $('#hdd_numerofilas').val(obj.length);

    $('#table_id').DataTable({

        data: obj,
        "ordering": false,
        columns: [
            {
                data: 'item',
                className: "dt-body-center"
            },
            { data: 'ccod_coa' },
            { data: 'cdsc_coa' },
            { data: 'cdoc_coa' },
            { data: 'cdirc_coa' },
            { data: 'cproveedor', render: _renderAsociado },
            { data: 'estado', render: _renderEstado }
        ]
    });

    $('#tableClientes').DataTable({
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
            { data: 'cdsc_coa' },
            { data: 'cdoc_coa' },
            { data: 'cdirc_coa' },
            { data: 'cproveedor', render: _renderAsociado },
            { data: 'estado', render: _renderEstado }],
        scrollX: "2000px",
        scrollCollapse: true,
    });


    $('#table_id').attr("style", "width:100%");
}
