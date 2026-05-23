
 

function CargarUnidadMedida(){
    var listBox = document.getElementById("slUniMedida");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarUnidadMedida',
        data: '{codigo: "' + "" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if(response.d){
                var $dropdown = $("#slUniMedida"); 
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_unidadmedida).text(this.cdsc_unidadmedida));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}


  function CargarFamilia(){

    var listBox = document.getElementById("txtFamilia");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarFamilia',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            if(response.d){
                var $dropdown = $("#txtFamilia");  
                $dropdown.append($("<option />").val("%%%").text("Todos"));
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
 
function CargarNumeradorTipoOper() { 
    var listBox = document.getElementById("txtTipoOperacion");
    listBox.options.length = 0; 
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarNumeradorTipoOper',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            if (response.d) {
                var $dropdown = $("#txtTipoOperacion"); 
                $dropdown.append($("<option />").val("").text("")); 
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.ccod_toper).text(this.cdsc_toper+" ("+this.ccod_toper+")"));
                });
            } 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}
 
function CargarNumeradorFactura() {
    var listBox = document.getElementById("txtCodDocumento");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarNumeradorFactura',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            if (response.d) {
                var $dropdown = $("#txtCodDocumento");
                $dropdown.append($("<option />").val("").text("")); 
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.cdoc_tipo).text(this.cdsc_numer));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}


 function CargarCaja(){
    var objCajaF = [];
    var listBox = document.getElementById("txtCaja");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarCaja',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            objCajaF = response.d;
            if(response.d){
                var $dropdown = $("#txtCaja"); 
                $.each(response.d, function(item) {
                    $dropdown.append($("<option />").val(this.ccod_caja).text(this.cdsc_caja+" ("+this.ccod_caja+")"));
                });
            }
            else MensajeFinSession();
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/CajaAsignada');
  
    if ( obj.trim() !="" ) {
        document.getElementById("txtCaja").setAttribute("value", "Caja*");
        (document.getElementById("txtCaja")).selectedIndex = 
        [...(document.getElementById("txtCaja")).options].findIndex(option => option.value === (obj).toString()); 
    }else if(objCajaF.length > 0){
        document.getElementById("txtCaja").setAttribute("value", "Caja*");
    }else{
        document.getElementById("txtCaja").setAttribute("value", "");
    }
}

function CargarTienda() { 
    var objTiendaF = [];
    var listBox = document.getElementById("txtTienda");
    listBox.options.length = 0; 
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarTienda',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false, 
        success: function (response) { 
            objTiendaF = response.d;
            if (response.d) {
                var $dropdown = $("#txtTienda"); 
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.ccod_tiend).text(this.cnombr+" ("+this.ccod_tiend+")"));
                });
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    }); 
    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/TiendaAsignada');
  
    if ( obj.trim() !="" ) {
        document.getElementById("txtTienda").setAttribute("value", "Tienda*");
        (document.getElementById("txtTienda")).selectedIndex = 
        [...(document.getElementById("txtTienda")).options].findIndex(option => option.value === (obj).toString()); 
    }else if(objTiendaF.length > 0){
        document.getElementById("txtTienda").setAttribute("value", "Tienda*");
    }else{
        document.getElementById("txtTienda").setAttribute("value", "");
    } 
}

function CargarClientePredeterminado() {
    
    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/CargarClientePredeterminado'); 
    if ( obj.length>0 ) {  
         $('#txtCliente').val(obj[0].ccod_coa);
    }else{
         $('#txtCliente').val("");
    } 
} 

function CargarAlmacenes() {
    var objAlmacenF = [];
    var listBox = document.getElementById("txtAlmacen");
    listBox.options.length = 0;
    $.ajax({
        type: "POST",
        url: '../Consultas/ConfigGeneral.aspx/CargarAlmacenes',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            objAlmacenF = response.d;
            if (response.d) {
                var $dropdown = $("#txtAlmacen"); 
                $.each(response.d, function (item) {
                    $dropdown.append($("<option />").val(this.ccod_alm).text(this.cdsc_alm+" ("+this.ccod_alm+")"));
                });
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });

    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/AlmacenAsignado'); 
    if ( obj.trim() !="" ) {
        document.getElementById("txtAlmacen").setAttribute("value", "Almacén*");
        (document.getElementById("txtAlmacen")).selectedIndex = 
        [...(document.getElementById("txtAlmacen")).options].findIndex(option => option.value === (obj).toString()); 
    }else if(objAlmacenF.length > 0){
        document.getElementById("txtAlmacen").setAttribute("value", "Almacén*");
    }else{
        document.getElementById("txtAlmacen").setAttribute("value", "");
    } 
}


function CargarMesActual() {

    // obtenemos el primer y último día de la semana del año indicado
    var date = new Date();
    var primerDia = new Date(date.getFullYear(), date.getMonth(), 1);
    var ultimoDia = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    var mes = (fecha.getMonth() + 1); 
    var primerDia = primerDia.getDate();
    var ultimoDia = ultimoDia.getDate(); 
    if (primerDia < 10) {
        var primerDia = '0' + primerDia
    } 
    if (ultimoDia < 10) {
        var ultimoDia = '0' + ultimoDia
    } 
    if (mes < 10) {
        var mes = '0' + mes
    } 
    $('#txtfchDesde').val(primerDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchHasta').val(ultimoDia + "/" + mes + "/" + date.getFullYear());
}