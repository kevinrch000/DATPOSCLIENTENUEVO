 var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();


 objSalida=[];
 objIngreso=[];

 //    Variable para almacenar el src de la imagen
 var result="";
//    Variable para recortar base64 de la imagen
 var BASE64_MARKER = ';base64,';

  function CargarDatosColumna() {
    var DscTabla = "";
    var DscColumna = "";
    var Nombre = "";
    var Estado = "";
    var TipoDato = "";
      
    if($("#NombreColumna").val() == "txtCodOpeSal" || $("#NombreColumna").val() == "txtCodOpeIng"){
        DscTabla = "ad_configcia";
        DscColumna = "coper_ingreso";
        Nombre = "Código de operación";
        Estado = "Obligatorio";
        TipoDato = "1 hasta";
    }else if($("#NombreColumna").val() == "txtIGV" || $("#NombreColumna").val() == "txtISC"){
        DscTabla = "ad_configcia";
        DscColumna = "nigv";
        Nombre = "Porcentaje de tributo";
        Estado = "Obligatorio";
        TipoDato = "";
    }else if($("#NombreColumna").val() == "txtCodCliBol"){
        DscTabla = "ad_configcia";
        DscColumna = "ccod_clibol";
        Nombre = "Código de cliente";
        Estado = "Obligatorio";
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

 $(window).load(function(){ 
 $(function() {
  $('#file-input').change(function(e) {
      addImage(e); 
     });
     function addImage(e){
      var file = e.target.files[0],
      imageType = /image.*/;
      if (!file.type.match(imageType))
       return;
      var reader = new FileReader();
      reader.onload = fileOnload;
      reader.readAsDataURL(file);
     }
     function fileOnload(e) {
       result=e.target.result;
       document.getElementById("imgSalida").style.display="block";
      $('#imgSalida').attr("src",result);
     }
    });
  });


function BorarImagen() {
    document.getElementById("imgSalida").src = "";
        $('#file-input').val('');
     document.getElementById("imgSalida").style.display="none";
     result="";
}


//var uploadFile = new FormData();

function Guardar() { 
   

    if(navigator.onLine) { 
    if($('#txtCodCliBol').val() == ""){
        Mensaje('Advertencia','Ingresar código cliente boleta.','warning');
        return;
    }else if($("#txtCodOpeIng option:selected").text() == ""){
        Mensaje('Advertencia','Ingresar Oper. Auto. para Devoluciones.','warning');
        return;
    }else if($("#txtCodOpeSal option:selected").text() == ""){
        Mensaje('Advertencia','Ingresar Oper. Auto. para Salida.','warning');
        return;
    }else if($('#txtIGV').val() == ""){
        Mensaje('Advertencia','Ingresar tasa de impuesto a las ventas.','warning');
        return;
    }else if($('#txtISC').val() == ""){
        Mensaje('Advertencia','Ingresar tasa de impuesto selectivo al consumo.','warning');
        return;
    } 

     var base64Index = result.indexOf(BASE64_MARKER) + BASE64_MARKER.length;
        var base64 = result.substring(base64Index); 

    var MontoMaxBoleta ="";

    if($('#txtMoneda').val() == "Soles"){
        MontoMaxBoleta = "700"
    }else{
        MontoMaxBoleta = $('#txtMaxBol').val()
    }

    var obj = [
        {
            "ccod_clibol": $('#txtCodCliBol').val(),
            "ccod_OperIngreso": $("#txtCodOpeIng option:selected").text(),
            "ccod_OperSalida": $("#txtCodOpeSal option:selected").text(),
            "ctipo_OperIngreso": $('#ctipo_doc_ingreso').val(),
            "ctipo_OperSalida": $('#ctipo_doc_salida').val(),
            "nigv": $('#txtIGV').val(),
            "nisc": $('#txtISC').val(),
            "nmonto_maxboleta": MontoMaxBoleta,
            "ilogo" : base64
        }
    ] 

    $.ajax({
        type: "POST",
        url: 'ConfigGeneral.aspx/Guardar',
        data: JSON.stringify({ ConfigGeneral: obj, operacion: $('#operacion').val() }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
      
            if (response.d == "-1") MensajeFinSession();
            else {
                if (response.d == true) {
                    Mensaje('Correcto', '', 'success');
                       $(".readonl").prop("readonly", true);
                         $(".disabled").prop("disabled", true);
                           $("#operacion").val("");
                     $('#btn_p_grabar').removeClass("botones_hab").addClass("botones_des");
                    $('#btn_p_back').removeClass("botones_hab").addClass("botones_des");
                    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
                    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
                    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");

                   
                   var CodOpeSal = document.getElementById('txtCodOpeSal');
                    var CodOpeIng = document.getElementById('txtCodOpeIng');
                    if(CodOpeIng.value==''){
                    $('#blAutoDevol').removeClass("floating-disable").addClass("floating-select2"); 
                    }else{
                    $('#blAutoDevol').removeClass("floating-select2").addClass("floating-disable"); 
                    }
                    if(CodOpeSal.value==''){
                    $('#blAutoSalida').removeClass("floating-disable").addClass("floating-select2");
                    }else{
                    $('#blAutoSalida').removeClass("floating-select2").addClass("floating-disable");
                    }
                    document.getElementById("txtCodOpeSal").setAttribute("value", "");
                    document.getElementById("txtCodOpeIng").setAttribute("value", "");

                   

                }
            }

            if (response.d == false) Mensaje('Error', 'No se realizó la operación', 'error');
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
    result="";
    } else {
     Mensaje('Error','Sin acceso a internet.','error');
}
 
}

function Deshacer() {
var CodOpeSal = document.getElementById('txtCodOpeSal');
var CodOpeIng = document.getElementById('txtCodOpeIng');
if(CodOpeIng.value==''){
    $('#blAutoDevol').removeClass("floating-disable").addClass("floating-select2"); 
}else{
    $('#blAutoDevol').removeClass("floating-select2").addClass("floating-disable"); 
}
if(CodOpeSal.value==''){
    $('#blAutoSalida').removeClass("floating-disable").addClass("floating-select2");
}else{
    $('#blAutoSalida').removeClass("floating-select2").addClass("floating-disable");
}
document.getElementById("txtCodOpeSal").setAttribute("value", "");
document.getElementById("txtCodOpeIng").setAttribute("value", "");


 $(".readonl").prop("readonly", true);
    $(".disabled").prop("disabled", true);

    $('#btn_p_grabar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    if($('#operacion').val() =="editar"){
        $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
        $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
        DatosConfigGenreal();
    }else if($('#operacion').val() =="nuevo"){
        document.getElementById("txtCodOpeSal").setAttribute("value", "");
    document.getElementById("txtCodOpeIng").setAttribute("value", "");

        $('#btn_p_nuevo').removeClass("botones_des").addClass("botones_hab");
        $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
        $("#txtCodCliBol").val("");
        document.getElementById("txtNomCliBol").innerHTML = ""; 
        document.getElementById("blCodOpeIngreso").innerHTML = ""; 
        document.getElementById("blCodOpeSalida").innerHTML = ""; 
        (document.getElementById("txtCodOpeIng")).selectedIndex = 
        [...(document.getElementById("txtCodOpeIng")).options].findIndex(option => option.text === "");
        (document.getElementById("txtCodOpeSal")).selectedIndex = 
        [...(document.getElementById("txtCodOpeSal")).options].findIndex(option => option.text === "");
    }


    $('#operacion').val(''); 
   

}




function CodigoOperacionIngreso() {
 

    var listBox = document.getElementById("txtCodOpeIng");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'ConfigGeneral.aspx/CodigoOperacionIngreso',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
            objIngreso = response.d; 
            $('#txtCodOpeIng').append('<option   value="" ></option>');

           for (var i = 0; i < objIngreso.length; i++) {
               $('#txtCodOpeIng').append('<option  class="disabled" disabled value="' + objIngreso[i].cdsc_toper + '">' + objIngreso[i].ccod_toper + '</option>');
           }  
            },
 
        error: function (xhr, status, error) {
            alert(error);
        }
    });
    
     
}
 
function CodigoOperacionSalida() {

    var listBox = document.getElementById("txtCodOpeSal");
    listBox.options.length = 0;

    $.ajax({
        type: "POST",
        url: 'ConfigGeneral.aspx/CodigoOperacionSalida',
        data: '{codigo: "' + "cod" + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,

        success: function (response) {
         
            if (response.d) {
             objSalida = response.d;

           

            $('#txtCodOpeSal').append('<option   value=""> </option>');
           for (var i = 0; i < objSalida.length; i++) {
               $('#txtCodOpeSal').append('<option  class="disabled" disabled value="' + objSalida[i].cdsc_toper + '">' + objSalida[i].ccod_toper + '</option>');

           }
            
            }
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
}


function Nuevo() { 
result="";
     $(".readonl").prop("readonly", false);
    $(".disabled").prop("disabled", false);
    $("#operacion").val("nuevo");
    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    
    var CodOpeSal = document.getElementById('txtCodOpeSal');
    var CodOpeIng = document.getElementById('txtCodOpeIng');
    if(CodOpeIng.value==''){
    $('#blAutoDevol').removeClass("floating-disable").addClass("floating-select2"); 
    }else{
    $('#blAutoDevol').removeClass("floating-select2").addClass("floating-disable"); 
    }
    if(CodOpeSal.value==''){
    $('#blAutoSalida').removeClass("floating-disable").addClass("floating-select2");
    }else{
    $('#blAutoSalida').removeClass("floating-select2").addClass("floating-disable");
    }
    document.getElementById("txtCodOpeSal").setAttribute("value", "");
    document.getElementById("txtCodOpeIng").setAttribute("value", "");


    
}


function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtCodCliBol').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText); 
    document.getElementById("txtNomCliBol").innerHTML = $("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[2].innerText;
}

function ModalConsultarClientes() {
 
    $('#tableVisibleConsulClientes').DataTable().destroy();
    $('#table_secundariaConsultarCliente').DataTable().destroy();  

    var obj = llenarobjeto('ConfigGeneral.aspx/CargarCliente');

    $('#hdd_numerofilas').val(obj.length);
    $('#tableVisibleConsulClientes').DataTable({
        "pageLength": 5,
        data: obj,
        columns: [
            { data: 'cbx',
                render: function (data, type, row) {
                    if (1 == 1) { return '<input type="radio" name="radiob">'; }
                    return data;
                },
                className: "dt-body-center"
            },
            { data: 'ccod_coa' },
            { data: 'cdsc_coa' }
            ]
    });
     $('#table_secundariaConsultarCliente').DataTable({
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
                { data: 'cdsc_coa' }],
                scrollX: "2000px",
                scrollCollapse: true,
   });


    
} 

function FunctionOperIngreso() {

    
 
    var x = document.getElementById('txtCodOpeIng').value;  
    document.getElementById("blCodOpeIngreso").innerHTML = x;

     $('#blAutoDevol').removeClass("floating-disable").addClass("floating-select2");

     for (var i = 0; i < objIngreso.length; i++) {
              if( txtCodOpeIng.options[txtCodOpeIng.selectedIndex].text == objIngreso[i].ccod_toper){
               $('#ctipo_doc_ingreso').val(objIngreso[i].ctipo_flag_Oper); 
              }else if( txtCodOpeIng.options[txtCodOpeIng.selectedIndex].text==""){
              $('#ctipo_doc_ingreso').val(""); 
              }
           }
            
}

function FunctionOperSalida() {
    var x = document.getElementById('txtCodOpeSal').value;
    document.getElementById("blCodOpeSalida").innerHTML = x;

      $('#blAutoSalida').removeClass("floating-disable").addClass("floating-select2");

           for (var i = 0; i < objSalida.length; i++) {
              if( txtCodOpeSal.options[txtCodOpeSal.selectedIndex].text==objSalida[i].ccod_toper){
               $('#ctipo_doc_salida').val(objSalida[i].ctipo_flag_Oper); 
              }else if( txtCodOpeSal.options[txtCodOpeSal.selectedIndex].text==""){
              $('#ctipo_doc_salida').val(""); 
              }
           }
}
 
function DatosConfigGenreal() {

    var obj = llenarobjeto('ConfigGeneral.aspx/DatosConfigGenreal');
    if(obj.length>0){
        $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
        $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
        $("#txtCodCliBol").val(obj[0].ccod_clibol);

        $("#txtIGV").val(obj[0].nigv);
        $("#txtISC").val(obj[0].nisc);
        $("#txtMaxBol").val(obj[0].nmonto_maxboleta);
        document.getElementById("txtNomCliBol").innerHTML = obj[0].cnom_clibol; 
        document.getElementById("blCodOpeIngreso").innerHTML = obj[0].cnom_OperIngreso; 
        document.getElementById("blCodOpeSalida").innerHTML = obj[0].cnom_OperSalida; 

        for (var i = 0; i < objSalida.length; i++) {
            if( txtCodOpeSal.options[txtCodOpeSal.selectedIndex].text==objSalida[i].ccod_toper){
                $('#ctipo_doc_salida').val(objSalida[i].ctipo_flag_Oper); 
                document.getElementById("txtCodOpeSal").setAttribute("value", "Oper. Auto. para Salida*");
            }else if( txtCodOpeSal.options[txtCodOpeSal.selectedIndex].text==""){
                $('#ctipo_doc_salida').val(""); 
                document.getElementById("txtCodOpeSal").setAttribute("value", "");
            }
        }

        for (var i = 0; i < objIngreso.length; i++) {
            if( txtCodOpeIng.options[txtCodOpeIng.selectedIndex].text==objIngreso[i].ccod_toper){
                $('#ctipo_doc_ingreso').val(objIngreso[i].ctipo_flag_Oper); 
                document.getElementById("txtCodOpeIng").setAttribute("value", "Oper. Auto. para Devoluciones*"); 
            }else if( txtCodOpeIng.options[txtCodOpeIng.selectedIndex].text==""){
                $('#ctipo_doc_ingreso').val(""); 
                document.getElementById("txtCodOpeIng").setAttribute("value", "");
            }
        }

        (document.getElementById("txtCodOpeIng")).selectedIndex = 
        [...(document.getElementById("txtCodOpeIng")).options].findIndex(option => option.text === (obj[0].ccod_OperIngreso).toString());
        
        (document.getElementById("txtCodOpeSal")).selectedIndex = 
        [...(document.getElementById("txtCodOpeSal")).options].findIndex(option => option.text === (obj[0].ccod_OperSalida).toString());

        if(obj[0].ilogo==""){
            document.getElementById("imgSalida").src = "";
            document.getElementById("imgSalida").style.display="none";
            $('#file-input').val('');
        }else{
            document.getElementById("imgSalida").src = "data:image/png;base64,"+obj[0].ilogo;
            document.getElementById("imgSalida").style.display="block";
            result="data:image/png;base64,"+obj[0].ilogo;
        }
    }
     
}


function Editar() {
 

    $(".disabled").prop("disabled", false);
    $("#operacion").val("editar");

    var CodOpeSal = document.getElementById('txtCodOpeSal');
    var CodOpeIng = document.getElementById('txtCodOpeIng');
    if(CodOpeIng.value==''){
    $('#blAutoDevol').removeClass("floating-disable").addClass("floating-select2"); 
    }else{
    $('#blAutoDevol').removeClass("floating-select2").addClass("floating-disable"); 
    }
    if(CodOpeSal.value==''){
    $('#blAutoSalida').removeClass("floating-disable").addClass("floating-select2");
    }else{
    $('#blAutoSalida').removeClass("floating-select2").addClass("floating-disable");
    }
    document.getElementById("txtCodOpeSal").setAttribute("value", "");
    document.getElementById("txtCodOpeIng").setAttribute("value", "");

    $('#btn_p_editar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_grabar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_nuevo').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
    if ($("#txtMoneda").val() == "Soles") {
    document.getElementById("txtMaxBol").disabled = true;
    }else{
    document.getElementById("txtMaxBol").disabled = false;
    }

     
}

$(document).ready(function () {
    CargarMenu();

    ConsultaColumnas();

 var NomMoneda = document.getElementById("lNomMoneda").textContent;
 var SimMoneda = document.getElementById("lSimMoneda").textContent;

  var lTarifa = document.getElementById("lTarifa").textContent;
 var Cantienda = document.getElementById("Cantienda").textContent;
  var CanUsuario = document.getElementById("CanUsuario").textContent;
    var NumTributario = document.getElementById("NumTributario").textContent;
    var FactElectronica = document.getElementById("FactElectronica").textContent;
      
      $("#txtdfchvencimiento").val(document.getElementById("dfchvencimiento").textContent);
    $("#txtMoneda").val(NomMoneda);
     
     $("#txtFactElect").val(FactElectronica);
    $("#txtTarifa").val(lTarifa);
   $("#txtCanUsuMax").val(CanUsuario);
   $("#txtCanTieMax").val(Cantienda);
   $("#txtNumTri").val(NumTributario);

    $("#thTablaConsultarCliente").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) {
            //click para exporta a exel
            var blob = new Blob([document.getElementById('tableExportarConsultarCliente').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

// $('#txtCodOpeIng').removeClass("floating-select:not([value=""]):valid"); 
    $("#ModalDatosPersonales").draggable();
                    
      $("#modalConsultarClientes").draggable();

      inicar_menu_nivel2('Configuraciones Generales', '1_li_Administracion', '2_li_ConfGeneral','0');

    $('#btn_p_grabar').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_back').removeClass("botones_hab").addClass("botones_des");
    $('#btn_p_editar').removeClass("botones_des").addClass("botones_hab");
    $('#btn_p_eliminar').removeClass("botones_hab").addClass("botones_des");
     
       
    CodigoOperacionIngreso();
    CodigoOperacionSalida();
     DatosConfigGenreal();
//    if ($("#txtMoneda").val() == "Soles") {
//    document.getElementById("txtMaxBol").disabled = true;
//    }else{
//    document.getElementById("txtMaxBol").disabled = false;
//    }
    traducir_tabla();
});