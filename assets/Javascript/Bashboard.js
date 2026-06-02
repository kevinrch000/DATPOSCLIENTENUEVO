var fecha = new Date();
var hoy = fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear() + "-" + fecha.getHours() + ":" + fecha.getMinutes() + ":" + fecha.getSeconds() + ":" + fecha.getMilliseconds();

 


 
 function tab_PorProducto(){
   traducir_tabla(); 
   $('#txtClientePorProducto').val(""); 
   $('#txtArticuloPorProducto').val(""); 
   $('#txtVariantePorProducto').val("");  
  
 
    $('#table_visibleDocPorProducto').DataTable().destroy();
     traducir_tabla(); 
     $('#table_visibleDocPorProducto').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });
//   EjecutarPorProducto();
    
 }
   
    
    function tab_Clientes(){ 
     $('#tableCliente').DataTable().destroy();
     traducir_tabla(); 
     $('#tableCliente').DataTable({
        "zeroRecords": "No se encontraron resultados."
    });

      
    $('#txtClienteClientes').val(""); 
  }


  function tab_Reporte(){
  
    traducir_tabla();    
  }

 function tab_kardex(){
    $('#txtCodArticuloKardex').val(""); 
    traducir_tabla();   

      $('#tableKardex').DataTable().destroy();
     traducir_tabla(); 
     $('#tableKardex').DataTable({
        "zeroRecords": "No se encontraron resultados."
    }); 
//    EjecutarKardex();
  }

  function tab_DelDia(){
    var objProductosDelDia = llenarobjeto('Home.aspx/CargarProductosDelDia');
    if (objProductosDelDia.length > 0){
        $('#tbProductosDelDia').DataTable().destroy();
        $('#tbProductosDelDia').DataTable({ 
                "autoWidth": false,
                "ordering": false,
                "lengthMenu": [5], 
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
                data: objProductosDelDia, 
                columns: [
               { data: 'cdsc_articulo' },
               { data: 'nimporte_neto' ,className: "dt-body-right" } ]
                     
        });
        $('#tbProductosDelDiaExel').DataTable().destroy();
        $('#tbProductosDelDiaExel').DataTable({
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
                            data: objProductosDelDia,
                            columns: [
                            { data: 'cdsc_articulo' },
                            { data: 'nimporte_neto'} ],
                            scrollX: "2000px",
                            scrollCollapse: true,
               });  
    }
     
    var objVendedoresDelDia = llenarobjeto('Home.aspx/CargarVendedoresDelDia');
    if (objVendedoresDelDia.length > 0){
    $('#tbVendedoresDelDia').DataTable().destroy();
    $('#tbVendedoresDelDia').DataTable({
                "autoWidth": false,
                "ordering": false,
                "lengthMenu": [5], 
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
                data: objVendedoresDelDia,
                columns: [
               { data: 'cusu_crea' },
                { data: 'nimporte_neto' ,className: "dt-body-right" }],
                     
        }); 
        $('#tbVendedoresDelDiaExel').DataTable().destroy();
        $('#tbVendedoresDelDiaExel').DataTable({
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
                            data: objVendedoresDelDia,
                            columns: [
                            { data: 'cusu_crea' },
                            { data: 'nimporte_neto'} ],
                            scrollX: "2000px",
                            scrollCollapse: true,
               });  
    }
     
    
  }


   function PasaDatosCodUsuario() {
    var fila = $("#tableVisibleConsulUsuario input[name=radiob]:checked").closest('tr');
    $('#txtUsuarioClientes').val($("#tableVisibleConsulUsuario")[0].rows[fila[0].rowIndex].cells[1].innerText);
}

function ModalConsultarUsuarios() {

 
 $('#modalConsultarUsuarios').modal('show');
     $('#tableVisibleConsulUsuario').DataTable().destroy(); 

    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/CargarListaUsuario');

     $('#tableVisibleConsulUsuario').DataTable({
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
                {data: 'ccod_usuario' },
                {data: 'cdsc_usuario' }
                ]
    });
    
}
 

  
function PasaDatosCodCliente() {
    var fila = $("#tableVisibleConsulClientes input[name=radiob]:checked").closest('tr');
    $('#txtClientePorProducto').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText); 
    $('#txtClienteClientes').val($("#tableVisibleConsulClientes")[0].rows[fila[0].rowIndex].cells[1].innerText); 
}

function ModalConsultarClientes() { 
    $('#modalConsultarClientes').modal('show');
    $('#tableVisibleConsulClientes').DataTable().destroy();  
    var obj = llenarobjeto('../Consultas/ConfigGeneral.aspx/CargarCliente');
  
    $('#tableVisibleConsulClientes').DataTable({
    "pageLength": 5,
    data: obj,
    columns: [
        { data: 'cbx',
        render: function (data, type, row) {
        if (1 == 1) { return '<input type="radio" name="radiob">'; }
        return data; }, className: "dt-body-center" },
        {data: 'ccod_coa' },
        {data: 'cdsc_coa' }
        ]
});
   
}



 function PasaDatosCodArticulo() {
    var fila = $("#table_visible_ConsultarArticulos input[name=radiob]:checked").closest('tr');
    $('#txtCodArticuloKardex').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
    $('#txtArticuloPorProducto').val($("#table_visible_ConsultarArticulos")[0].rows[fila[0].rowIndex].cells[1].innerText);
}


 function ModalConsultarArticulos() {
 

    $('#modalConsultarArticulos').modal('show');
   
    $('#table_visible_ConsultarArticulos').DataTable().destroy();
    $('#table_secundariaConsultarArticulos').DataTable().destroy();
    var obj = llenarobjeto('../Consultas/ConsultaArticulos.aspx/CargarArticulo');

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
     
}


 function CargarAlamcen(){
    var objAlmacenF = [];
    var listBox = document.getElementById("txtAlmacenKardex");
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
                var $dropdown = $("#txtAlmacenKardex"); 
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

     // CORRECCIÓN: Primero verificamos que obj no sea nulo, luego le hacemos trim()
     if (obj != null && obj.trim() != "") {
         (document.getElementById("txtAlmacenKardex")).selectedIndex =
             [...(document.getElementById("txtAlmacenKardex")).options].findIndex(option => option.value === (obj).toString());
     }
 }

  function EjecutarPorProducto(){
 
    if ($('#txtTiendaPorProducto').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtfchDesdePorProducto').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHastaPorProducto').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }  
     
    var obj = [
        {
            "ccod_articulo": $('#txtArticuloPorProducto').val(),
            "ccod_tienda": $('#txtTiendaPorProducto').val(),
            "ccod_coa": $('#txtClientePorProducto').val(),
            "n_fchDesde": $('#txtfchDesdePorProducto').val(),
            "n_fchHasta": $('#txtfchHastaPorProducto').val(),
            "cobser_variante": $('#txtVariantePorProducto').val()
        }
    ] 
 
    $.ajax({
        type: "POST",
        url: '../Consultas/ConsultasVenta.aspx/ConsultasVentaPricipal',
        data: JSON.stringify({ ConsultaArticulo: obj }), 
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {

        if (response.d=="-1"){
           MensajeFinSession();
           }else{
             var objfactura = response.d;  

            $('#table_visibleDocPorProducto').DataTable().destroy();
            $('#table_visibleDocPorProducto').DataTable({ 
                "ordering": false,
                "searching": false,
                data: objfactura, 
                columns: [ 
                { data: 'ccod_coa' },
                { data: 'cdoc_seri' },  
                { data: 'cdsc_articulo' }, 
                { data: 'ncantidad',className: "dt-body-right" },
                { data: 'nprecio',className: "dt-body-right" },
                { data: 'nimpuesto',className: "dt-body-right" },
                { data: 'nisc',className: "dt-body-right" },
                { data: 'ndescuento',className: "dt-body-right" }, 
                { data: 'nimporte_neto',className: "dt-body-right" }, 
                { data: 'dfch_doc',className: "dt-body-right" },
                { data: 'cobser_variante' }]
            });

            $('#table_visibleDocPorProductoExel').DataTable().destroy();
            $('#table_visibleDocPorProductoExel').DataTable({ 
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
                data: objfactura, 
                columns: [ 
                { data: 'ccod_coa' },
                { data: 'cdoc_seri' },  
                { data: 'cdsc_articulo' }, 
                { data: 'ncantidad'},
                { data: 'nprecio' },
                { data: 'nimpuesto'},
                { data: 'nisc'},
                { data: 'ndescuento'}, 
                { data: 'nimporte_neto'}, 
                { data: 'dfch_doc'},
                { data: 'cobser_variante'}],
                    scrollX: "2000px",
                scrollCollapse: true,
            });

            $('#txtCantTotPorProducto').val('');
            $('#txtImpBrutoTotPorProducto').val('');
            $('#txtIgvTotPorProducto').val('');
            $('#txtIscTotPorProducto').val('');
            $('#txtDescTotPorProducto').val('');
            $('#txtImpNetoTotPorProducto').val('');
             if(objfactura.length>0){ 
                $.ajax({  
                    type: "POST",
                    url: '../Consultas/ConsultasVenta.aspx/DatosAdicionales',
                    data: JSON.stringify({ VentasPorArticulo: objfactura }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    async: false,
                    success: function (response) {
                        var  objDA = response.d;
                        $('#txtCantTotPorProducto').val(objDA.ncantidad);
                        $('#txtImpBrutoTotPorProducto').val(objDA.nimporte_bruto);
                        $('#txtIgvTotPorProducto').val(objDA.nimpuesto);
                        $('#txtIscTotPorProducto').val(objDA.nisc);
                        $('#txtDescTotPorProducto').val(objDA.ndescuento);       
                        $('#txtImpNetoTotPorProducto').val(objDA.nimporte_neto);  
                    },
                    
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }); 
            }
           

           } 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });
     
     
}

function EjecutarClientes(){
 
    if ($('#txtTiendaClientes').val() == null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return;
    } else if ($('#txtfchDesdeClientes').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHastaClientes').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }  

     var obj = [ {
            "ccod_coa": $('#txtClienteClientes').val(), 
            "ccod_tienda": $('#txtTiendaClientes').val(),
            "fchDesde": $('#txtfchDesdeClientes').val(),
            "fchHasta": $('#txtfchHastaClientes').val(),
            "cusu_crea": $('#txtUsuarioClientes').val()} ]  
     
    $.ajax({
        type: "POST",
        url: '../Interfaces/Home.aspx/CargarOperacionesClientes',
        data: JSON.stringify({ OperCliente: obj }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) { 
           var obj = response.d;
           $('#tableCliente').DataTable().destroy();
           $('#tableCliente').DataTable({ 
               data: obj,
               "searching": false,
               "ordering": false,
                columns: [
                { data: 'cdsc_coa' },
                { data: 'cdsc_usuario' },
                { data: 'DocRef' },
                { data: 'cnom_tarje' },
                { data: 'dfch_crea',className: "dt-body-right" },
                { data: 'nmonto',className: "dt-body-right" } ]
            });

            $('#tableClienteExel').DataTable().destroy();
            $('#tableClienteExel').DataTable({ 
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
                { data: 'cdsc_coa' },
                { data: 'cdsc_usuario' },
                { data: 'DocRef' },
                { data: 'cnom_tarje' },
                { data: 'dfch_crea',className: "dt-body-right" },
                { data: 'nmonto',className: "dt-body-right" }],
                    scrollX: "2000px",
                scrollCollapse: true,
            });  
        },
        error: function (xhr, status, error) {
           
        }
    }); 
     
}


 function EjecutarKardex(){
 
    if ($('#txtAlmacenKardex').val() == null) {
        Mensaje('Advertencia', 'Seleccionar almacén.', 'warning');
        return;
    } else if ($('#txtfchDesdeKardex').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHastaKardex').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    } 
    

     var objKardex = [ {
            "ccod_articulo": $('#txtCodArticuloKardex').val(), 
            "ccod_alm": $('#txtAlmacenKardex').val(),
            "n_fchDesde": $('#txtfchDesdeKardex').val(),
            "n_fchHasta": $('#txtfchHastaKardex').val()} ]  
     
    $.ajax({
        type: "POST",
        url: '../Interfaces/Home.aspx/ReporteKardexPrincipal',
        data: JSON.stringify({ ReporteKardex: objKardex }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) { 
           var obj = response.d;
           $('#tableKardex').DataTable().destroy();
           $('#tableKardex').DataTable({ 
               data: obj,
               "searching": false,
               "ordering": false,
                columns: [
                        { data: 'DocRef' },
                        { data: 'FchDoc' },
                        { data: 'cdsc_articulo' },
                        { data: 'EntradaCantidad',className: "dt-body-right" },
                        { data: 'EntradaCosto',className: "dt-body-right" },
                        { data: 'EntradaTotal',className: "dt-body-right" },
                        { data: 'SalidaCantidad',className: "dt-body-right" },
                        { data: 'SalidaCosto',className: "dt-body-right" },
                        { data: 'SalidaTotal',className: "dt-body-right" },
                        { data: 'SaldoCantidad',className: "dt-body-right" },
                        { data: 'SaldoCosto',className: "dt-body-right" },
                        { data: 'SaldoTotal',className: "dt-body-right" }
                    ]
            });  
            $('#tableKardexExel').DataTable().destroy();
            $('#tableKardexExel').DataTable({ 
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
                { data: 'DocRef' },
                { data: 'FchDoc' },
                { data: 'cdsc_articulo' },
                { data: 'EntradaCantidad',className: "dt-body-right" },
                { data: 'EntradaCosto',className: "dt-body-right" },
                { data: 'EntradaTotal',className: "dt-body-right" },
                { data: 'SalidaCantidad',className: "dt-body-right" },
                { data: 'SalidaCosto',className: "dt-body-right" },
                { data: 'SalidaTotal',className: "dt-body-right" },
                { data: 'SaldoCantidad',className: "dt-body-right" },
                { data: 'SaldoCosto',className: "dt-body-right" },
                { data: 'SaldoTotal',className: "dt-body-right" }],
                    scrollX: "2000px",
                scrollCollapse: true,
            });

            
        },
        error: function (xhr, status, error) {
           
        }
    }); 
     
}


 

function LimpiarClientes(){
$('#txtTiendaClientes').val('');
    $('#txtfchDesdeClientes').val('');
    $('#txtfchHastaClientes').val('');    
    $('#txtClienteClientes').val(''); 
     document.getElementById("txtTiendaClientes").setAttribute("value", ""); 

}
 
function ExelClientes(){
    var blob = new Blob([document.getElementById('DivtableClienteExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte Clientes del " + hoy + ".xls");
}

function ExelKardex(){
    var blob = new Blob([document.getElementById('DivtableKardexExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte Kardex del " + hoy + ".xls");
}

function ExelPorProducto(){
    var blob = new Blob([document.getElementById('Divtable_visibleDocPorProductoExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte Ventas Por Articulo del " + hoy + ".xls");
}

 function LimpiarPorProducto(){
      
   $('#txtTiendaPorProducto').val('');
    $('#txtfchDesdePorProducto').val('');
    $('#txtfchHastaPorProducto').val('');    
    $('#txtClientePorProducto').val(''); 
     $('#txtArticuloPorProducto').val(''); 
      $('#txtVariantePorProducto').val('');  
    document.getElementById("txtTiendaPorProducto").setAttribute("value", ""); 

}


 function LimpiarKardex(){
   $('#txtAlmacenKardex').val('');
    $('#txtfchDesdeKardex').val('');
    $('#txtfchHastaKardex').val('');    
    $('#txtCodArticuloKardex').val('');  
    document.getElementById("txtAlmacenKardex").setAttribute("value", ""); 

}

function ArticuloSinStock() {
    var obj = llenarobjeto('Home.aspx/ArticuloSinStock');
    if (parseFloat(obj[0].cdescripcion) > 0) {
        
        Swal.fire({
        title: 'Cuenta con articulos con minimo stock. \n\n¿Desea consultarlo?',
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Consultar',
        showCancelButton: true,
        cancelButtonColor: '#f7505a',
        cancelButtonText: "Cancelar"
        }).then(
            (result) => {
            if (result.isConfirmed) {
                window.open((window.DATPOS_BASE_PATH||'')+'/pages/Consultas/ConsultaStockMinimo.php', '_blank');  
                 
            }
            });
    }
}

function DiasRestantes() {
    var obj = llenarobjeto('Home.aspx/DiasRestantes');
    if (parseFloat(obj[0].cdescripcion) < 6) {
        Mensaje('Advertencia', 'Tu licencia finaliza en '+ obj[0].cdescripcion +' dias.', 'warning'); 
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
    $('#txtfchDesdeReporte').val(primerDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchHastaReporte').val(ultimoDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchDesdeKardex').val(primerDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchHastaKardex').val(ultimoDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchDesdePorProducto').val(primerDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchHastaPorProducto').val(ultimoDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchDesdeClientes').val(primerDia + "/" + mes + "/" + date.getFullYear());
    $('#txtfchHastaClientes').val(ultimoDia + "/" + mes + "/" + date.getFullYear());

}

function EjecutarReporte() {
     

    if ($('#txtTiendaReporte').val() ==null) {
        Mensaje('Advertencia', 'Seleccionar tienda.', 'warning');
        return; 
    } else if ($('#txtfchDesdeReporte').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha desde.', 'warning');
        return;
    } else if ($('#txtfchHastaReporte').val() == "") {
        Mensaje('Advertencia', 'Ingresar fecha hasta.', 'warning');
        return;
    }
    UsuariosRegistrados(); 
    CargarDiagramaBarras();

}

function LimpiarReporte() { 
    $('#txtTiendaReporte').val(''); 
    $('#txtfchDesdeReporte').val('');
    $('#txtfchHastaReporte').val(''); 
    document.getElementById("txtTiendaReporte").setAttribute("value", "");  
}

function DatosCajero() {
    var obj = llenarobjeto('Home.aspx/CargarDatosCajero');
     
    $("#txtImporteCaja").text(obj[0].ImporteCaja);
    $("#txtTotVentTurn").text(obj[0].TotVentTurn);
    $("#txtTotDescTurn").text(obj[0].TotDescTurn);
    $("#txtDocAnulado").text(obj[0].DocAnulado);
}

 


function CargarProductoConDescuento() {
    var obj = llenarobjeto('Home.aspx/CargarProductoConDescuento');
    $('#tbProducDescuent').DataTable().destroy();
    $('#tbProducDescuent').DataTable({
                "autoWidth": false,
                "lengthMenu": [5], 
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
               { data: 'cdsc_articulo' },
                { data: 'ndes_max' } ],
                     
        }); 
         
}
 
function CargarTienda() {
 
        var obj = llenarobjeto('Home.aspx/CargarTiendaDashboard');
        if (obj.length > 0) {
           
             var $dropdown = $("#txtTiendaPorProducto"); 
            $.each(obj, function (item) {
                $dropdown.append($("<option />").val(this.ccod_tiend).text(this.cnombr));
            });
              var $dropdown = $("#txtTiendaReporte"); 
            $.each(obj, function (item) {
                $dropdown.append($("<option />").val(this.ccod_tiend).text(this.cnombr));
            });

            var $dropdown = $("#txtTiendaClientes"); 
            $.each(obj, function (item) {
                $dropdown.append($("<option />").val(this.ccod_tiend).text(this.cnombr));
            });
        } 
}

 
function UsuariosRegistrados() {
    $.ajax({
        type: "POST",
        url: 'Home.aspx/ConsultarDashboard',
        data: '{ccod_tienda: "' + $('#txtTiendaReporte').val() + '", fchDesde: "' + $('#txtfchDesdeReporte').val() + '", fchHasta: "' + $('#txtfchHastaReporte').val() + '" }',
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        async: false,
        success: function (response) {
            var obj = response.d;
            $("#txtCantUsuario").text(obj[0].ImporteCaja);
            $("#txtVentaDelDia").text(obj[0].VentaDelDia);
            $("#txtCantUsuarios").text(obj[0].CantUsuarios);
            $("#txtUsuariosRegistrados").text(obj[0].UsuRegistrados); 
        },
        error: function (xhr, status, error) {
            alert(error);
        }
    });   
}


function CargarDiagramaBarras() {

    var params = '{ccod_tienda: "' + $('#txtTiendaReporte').val() + '", fchDesde: "' + $('#txtfchDesdeReporte').val() + '", fchHasta: "' + $('#txtfchHastaReporte').val() + '" }';

    // Diagrama: Ventas por Usuario (barras horizontales)
    $.ajax({
            type: "POST",
            url: 'Home.aspx/DiagramaUsuario',
            data: params,
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                renderHorizontalBars('containerUsuario', response.d, 'Sin datos de usuarios para el período seleccionado');
            },
            error: function (xhr, status, error) {
                console.log('DiagramaUsuario error:', error);
            }
        });

    // Diagrama: Ventas por Caja (barras horizontales)
    $.ajax({
            type: "POST",
            url: 'Home.aspx/DiagramaCaja',
            data: params,
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            async: false,
            success: function (response) {
                renderHorizontalBars('container', response.d, 'Sin datos de cajas para el período seleccionado');
            },
            error: function (xhr, status, error) {
                console.log('DiagramaCaja error:', error);
            }
        });
}

/**
 * Renderiza una lista de barras horizontales modernas en `targetId`
 * a partir de la misma estructura que devuelven los SP DiagramaCaja /
 * DiagramaUsuario (array de objetos con `name` y `y`, compatible con
 * el formato que ya consume Highcharts).
 */
function renderHorizontalBars(targetId, data, emptyMsg) {
    var $target = $('#' + targetId);
    if (!$target.length) { return; }
    if (!data || !data.length) {
        $target.html('<div class="dp-bars-empty">' + (emptyMsg || 'Sin datos disponibles') + '</div>');
        return;
    }

    var maxVal = 0;
    for (var i = 0; i < data.length; i++) {
        var v = parseFloat(data[i].y || data[i].nimporte || data[i].importe || 0) || 0;
        if (v > maxVal) { maxVal = v; }
    }
    if (maxVal <= 0) { maxVal = 1; }

    var fmt = function (n) {
        var num = Number(n) || 0;
        return 'S/ ' + num.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    var esc = function (s) { return $('<div/>').text(s == null ? '' : s).html(); };

    var html = '<div class="dp-bars">';
    for (var j = 0; j < data.length; j++) {
        var item = data[j];
        var label = esc(item.name || item.cdsc_usuario || item.cdsc_caja || '—');
        var val = parseFloat(item.y || item.nimporte || item.importe || 0) || 0;
        var pct = Math.max(2, Math.round((val / maxVal) * 100));
        html += '<div class="dp-bar">' +
            '<span class="dp-bar-label">' + label + '</span>' +
            '<span class="dp-bar-amount">' + fmt(val) + '</span>' +
            '<div class="dp-bar-track"><div class="dp-bar-fill" style="width:' + pct + '%"></div></div>' +
        '</div>';
    }
    html += '</div>';
    $target.html(html);
}



 

function CargarDiagramaPastel() {


    var objDatos = llenarobjeto('Home.aspx/CargarDiagramaPastelDatos'); 
  
    Highcharts.chart('containerPastel', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Los 5 productos más vendidos'
        },
        tooltip: {
            pointFormat: '<b>{point.name}</b>: {point.y}'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.y}'
                }
            }
        },
           
        series: [{
            name: '',
            colorByPoint: true,
            data: objDatos
        }]

    });

     
}



  $(document).ready(function () {
    $('#id_titulo').text("Dashboard | DATPOS");
    $('#btn_p_nuevo').hide();
    $('#btn_p_editar').hide();
    $('#btn_p_grabar').hide();
    $('#btn_p_eliminar').hide();
    $('#btn_p_back').hide();
    $('#btn_p_imprimir').hide(); 
    CargarMenu(); 

    var TipoRol = document.getElementById("lblid_rol").textContent;

    // Mostrar dashboard completo para cualquier rol
    document.getElementById("ulOpciones").style.display = "block";    
    document.getElementById("divPestanas").style.display = "block";  
    $('.nav-tabs li:eq(0) a').tab('show');

    CargarTienda();
    CargarAlamcen(); 
    CargarMesActual();  
    try { ArticuloSinStock(); } catch(e) {}
    try { DiasRestantes(); } catch(e) {}
    EjecutarReporte();
     
    $("#thtable_visibleDocPorProducto").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) { 
            var blob = new Blob([document.getElementById('Divtable_visibleDocPorProductoExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

    $("#thProductosDelDia").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) { 
            var blob = new Blob([document.getElementById('DivtbProductosDelDiaExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });

    $("#thVendedoresDelDia").contextMenu({
        menuSelector: "#contextMenu",
        menuSelected: function (invokedOn, selectedMenu) { 
            var blob = new Blob([document.getElementById('DivtbVendedoresDelDiaExel').innerHTML], {
                type: 'application/xml;charset=utf-8', encoding: 'utf-8'
            });
            saveAs(blob, "Reporte del " + hoy + ".xls");
        }
    });
     

    $("#ModalDatosPersonales").draggable();

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
    $("#txtfchDesdeReporte").datepicker();
    $("#txtfchHastaReporte").datepicker();
  
     $("#txtfchDesdeKardex").datepicker();
    $("#txtfchHastaKardex").datepicker();
    $("#txtfchDesdeClientes").datepicker();
    $("#txtfchHastaClientes").datepicker();

    $("#txtfchDesdePorProducto").datepicker();
    $("#txtfchHastaPorProducto").datepicker();
     
});