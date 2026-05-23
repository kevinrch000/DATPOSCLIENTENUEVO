<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Factura Lista Precio | DATPOS';
$pageScript = 'FacturaListaPrecio3.js';
$showCrudButtons = false;
$showConsultButtons = false;
$startupScript = '';
$listaPrecios = array();

// Validar acceso al modulo 1035 (Facturacion) - paridad con FacturaListaPrecio.aspx.vb:23-26
try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1035'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[FacturaListaPrecio] VerificarAccesos rol 1035 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    // Degradacion graceful: si la consulta de roles falla, no bloquear el POS
    error_log('[FacturaListaPrecio] VerificarAccesos rol 1035 fallo: ' . $e->getMessage());
}

try {
    $validParams = array(
        '@CodCia' => array('value' => $o->ccod_empresa ?? ''),
        '@ccod_usuario' => array('value' => $o->ccod_usuario ?? ''),
        '@resp' => array('value' => '', 'direction' => 'output')
    );
    $validResult = Database::executeStoredTenantWithOutput('sp_validarfacturacion', $validParams, $o);
    $resp = $validResult['@resp']['value'] ?? ($validResult['@resp'] ?? '');
    if (strtoupper(trim((string)$resp)) === 'OK') {
        $resp = '';
    }

    if ($resp === '') {
        $turnoRows = Database::selectStoredTenant('sp_consultarusuarioturno', array(
            '@ccod_cia' => $o->ccod_empresa ?? '',
            '@ccod_usuario' => $o->ccod_usuario ?? ''
        ), $o);
        if (!empty($turnoRows)) {
            $_SESSION['id_turno'] = $turnoRows[0][0] ?? '';
        } else {
            $startupScript = 'MensajeTurno();';
        }
    } else {
        $startupScript = "MensajeValidacionFacturacion('" . addslashes((string)$resp) . "');";
    }
} catch (Exception $e) {
    $startupScript = '';
}

try {
    $conn = Database::getTenantConnection($o);
    if ($conn) {
        // FIX 55: usar ccod_cblistpre (LP001/LP002) como value del <option>
        // porque los SP sp_lp*/sp_ls* filtran LnListaPrecio.ccod_cblistpre,
        // no id_cblistpre. Antes enviaba "1"/"2" y los JOIN no encontraban precio.
        $stmt = sqlsrv_query($conn, "SELECT ccod_cblistpre, cdsc_cblistpre FROM CbListaPrecio WHERE ccod_cia=? AND cstatus='A' ORDER BY id_cblistpre", array($o->ccod_empresa ?? ''));
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $listaPrecios[] = array('id' => strval($r[0] ?? ''), 'name' => strval($r[1] ?? ''));
            }
            sqlsrv_free_stmt($stmt);
        }
        sqlsrv_close($conn);
    }
} catch (Exception $e) {
    $listaPrecios = array();
}

ob_start();
?>

<script src="<?= basePath() ?>/assets/Javascript/Numerosaletras.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
    <link href="<?= basePath() ?>/assets/Styles/bootstrap-float-label.css" rel="stylesheet" type="text/css" />

    <link href="<?= basePath() ?>/assets/Styles/css/switcher.css" rel="stylesheet" type="text/css" />
    <script src="<?= basePath() ?>/assets/Scripts/jquery.switcher.js" type="text/javascript"></script>
    
    <script src="<?= basePath() ?>/assets/Scripts/qrcode.js" type="text/javascript"></script>

    <link href="<?= basePath() ?>/assets/Styles/css/jquery.toggleinput.css" rel="stylesheet" type="text/css" />
    <script src="<?= basePath() ?>/assets/Scripts/jquery.toggleinput.js" type="text/javascript"></script>

    <script src="<?= basePath() ?>/assets/Scripts/html2canvas.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/html2canvas.min.js" type="text/javascript"></script>


<style>
    
    .lb_modal{
        font-size: 13px;        
    }        

    .cuadrado{
        height: 75px;
        width: 100%;
        padding: 0px;        
        margin: 3px;
        max-width: 75px;
        /*border-radius: 37px;        */
    }
    
    .cuadrado_desc{
        font-size: 10px;        
    }    
    
    .sombreado{
        box-shadow: 0 0 8px 4px rgba(51, 51, 51, 75%) !important;        
    }  
    
    .sombreado_mp{
        box-shadow: 0 0 8px 4px rgba(51, 51, 51, 75%);        
    }     
    
    .precio{
        position: absolute;
        font-size: 15px;
        color: white;
        left: 30px;
        bottom: 3px;
        background-color: rgba(0, 0, 0, 28%);
        color: white !important;        
    }      



</style>


<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>
<input id="hdd_metodopago" type="hidden" value="Visa"/>
<input id="hdd_total" type="hidden" value="0.00"/>
<input id="hdd_coa" type="hidden"/>
<input id="hdd_direc" type="hidden"/>
<input id="hdd_rucC" type="hidden"/>
<input id="hdd_cdsc_coa" type="hidden"/>

<input id="hdd_rv" type="hidden"/>
<input id="hdd_igv" type="hidden"/>
<input id="hdd_isc" type="hidden"/>
<input id="hdd_ruc" type="hidden" value="<?= e(($o->cnum_tribu ?? '')) ?>"/>
<input id="hhd_empresa" type="hidden" value="<?= e(($o->cdescripcion ?? '')) ?>"/>

<input id="hhd_direccionE" type="hidden" value="<?= e(($o->cdomicilio ?? '')) ?>"/>
<input id="hhd_ubigeoE" type="hidden" value="<?= e(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? '')) ?>"/>
<label id="lSimMoneda" style="display:none;" ><?= e(($o->csimbolo_moneda ?? '')) ?></label>
<label id="lNomMoneda" style="display:none;" ><?= e(($o->cnombre_moneda ?? '')) ?></label>

<input id="hdd_telefono_tienda" type="hidden" value="<?= e(($o->ctelf_tienda ?? '')) ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e(($o->cdsc_tienda ?? '')) ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? '')) ?>"/>
<label id="FactElectronica" style="display:none;" ><?= e(($o->ctip_facturador ?? '')) ?></label>
<input id="hdd_ctip_doc" type="hidden"/>
<input id="hdd_id_cbfact" type="hidden"/>

<div class="c-content-center modern-page">





    <ul class="nav nav-tabs" style="">
        <li onclick="" class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color: #228ac9;
            font-size: 17px;">Factura</a></li>
        <li onclick="Cambiar_Cobranza();"><a data-toggle="tab" class="tabcito" href="#tab_cliente" style="color: #228ac9;
            font-size: 17px;">Cobranza</a></li>
        <li onclick="tab_listaclick();">
        <a data-toggle="tab" href="#Lista" class="tabcito" style="color: #228ac9; font-size: 17px;display:none;">Lista</a></li>
    </ul>
    <div class="tab-content">
        <div id="Datos" class="tab-pane in active " style="padding: 13px;">
            
<?php /*
            <div class="row">
                <div class="col-sm-6">
                    <input id="tb_articulo" onkeyup="BuscarArticulos();" class="limpiar form-control moderno_tb" placeholder="Buscar Artículos" />
                </div>
                <div class="col-sm-6">
                    <input id="tb_anadir" class="limpiar form-control moderno_tb" placeholder="Añadir Artículo" />
                </div>
            </div>
*/ ?>

            <div class="row" style="height: 400px; margin-bottom: 0px;">
            

<?php /*
            <input type="text" id="pruebita"/>
            <input type="button" value="ver" onclick="alert(NumeroALetras($('#pruebita').val()));" />
*/ ?>
                
                <div class="col-md-6">
                    <div class="col-md-12" style="margin-bottom: 20px;">
                      
                        <select class="form-control moderno_tb" id="ddl_lpn" onchange="Favoritos(1);" >
                            <?php foreach ($listaPrecios as $lp): ?>
                                <option value="<?= e($lp['id']) ?>"><?= e($lp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                    </div>
                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <input id="tb_articulo" onkeyup="BuscarArticulos();" class="limpiar form-control moderno_tb" placeholder="Buscar Artículos" />
                    </div>

                    <div class="col-md-12">
                    <div class="col-md-3" style="min-width: 123px;">
                        <div class="" id="div_favoritos"></div>
                        <div class="" id="div_categorias" style="height:394px; overflow-y: auto;margin-right: -6px;min-width: 111px;"></div>
                    </div>

                    <div class="col-md-8" id="div_articulos" style="height:475px; overflow-y: auto;background-color: #80808012;"></div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <input id="tb_anadir" class="limpiar form-control moderno_tb" autocomplete="off" placeholder="Añadir Artículo" />
                    </div>
                    <div class="col-md-12" id="div_venta" style="height:274px; overflow-y: auto;">                    

                    

                        <table id="table_Articulos" class="display" style="width: -webkit-fill-available;">
                            <colgroup>  
                                <col style="width:30%"></col> 
                                <col style="width:10%"></col> 
                                <col style="width:10%"></col>
                                <col style="width:10%"></col>
                                <col style="width:5%"></col>
                                <col style="width:5%"></col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>
                                        Artículo
                                    </th>
                                    <th>
                                        Cantidad
                                    </th>
                                    <th>
                                        Precio
                                    </th>
                                    <th>
                                        Importe
                                    </th>
                                    <th>
                                    </th>
                                    <th>
                                    </th>
                                </tr>
                            </thead> 
                            <tbody>

                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6" style="padding: 1px;">
                            <input onclick="ValidarCuenta()" type="button" class="btn btn-primary" value="Guardar Cuenta" style="width: -webkit-fill-available;"/>
                        </div>
                        <div class="col-md-6" style="padding: 1px;">
                            <input onclick="CargarCuentas();" data-toggle="modal" data-target="#modalObtenerCuenta" type="button" class="btn btn-primary" value="Obtener Cuenta" style="width: -webkit-fill-available;"/>
                        </div>
                    </div>

                    <div id="modalGuardarCuenta" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header" style="border-bottom: 0px;">
                            </div>
                            <div class="modal-body">
                              <div class="input-group">
                                  <span class="has-float-label">
                                      <input id="tb_etiqueta" onkeyup="if($('#tb_etiqueta').val().length==0) $('#btn_guardarcuenta').removeClass('fa_enabled').addClass('fa_disabled'); else $('#btn_guardarcuenta').removeClass('fa_disabled').addClass('fa_enabled');" type="text" class="limpiar form-control moderno_tb" placeholder=" "/>
                                      <label for="tb_etiqueta">Cuenta</label>
                                  </span>
                              </div>
                            </div>
                            <div class="modal-footer" style="border-top: 0px;">
                                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="GuardarCuenta();" id="btn_guardarcuenta">Confirmar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                      </div>
                    </div>

                    <div onclick="CambiarFavoritos();" id="modalfavoritos" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                      <div id="modalfavoritos_position" class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div id="div_favtext">añadir a favoritos</div>
                        </div>
                      </div>
                    </div>

                    <div id="modalObtenerCuenta" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-body">
                                <table id="tablacuentas" class="table table-bordered table-striped">
                                  <thead>
                                    <tr>
                                      <th class="text-center">
                                        Cuenta<br />
                                      </th>
                                      <th class="text-center">
                                        Fecha Creación<br />
                                      </th>
                                    </tr>
                                  </thead>
                                  <tbody>

                                  </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-12">
                        <div style="border-bottom: groove; margin-bottom: 10px; margin-top: 10px;border-color: #228ac9;"></div>
                    </div>

                    <div class="col-md-12">
                        <div class="row" style="margin-bottom: 2px;">
                            <div class="col-md-6">Descuento:</div>      
                            <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div>            
                            <div id="div_desc" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                        </div>
                        <div class="row" style="margin-bottom: 2px;">
                            <div class="col-md-6">SubTotal:</div>                
                            <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div>  
                            <div id="div_subtotal" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                        </div>
                        <div class="row" style="margin-bottom: 2px;">
                            <div class="col-md-6">IGV:</div>        
                            <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div>                                       
                            <div id="div_igv" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                        </div>
                        <div class="row" style="margin-bottom: 2px;DISPLAY: NONE;">
                            <div class="col-md-6">ISC:</div>        
                            <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div>                                       
                            <div id="div_isc" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                        </div>
                        <div class="row" style="margin-bottom: 2px;">
                            <div class="col-md-6">Cobranza Total:</div>      
                            <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div>                                       
                            <div id="div_total" class="col-md-2" style="text-align-last: end;">0.00</div>    
                        </div>
                    </div>
                    <div class="col-md-12">
                        <input type="button" value="Ir a Cobranza" onclick="Cambiar_Cobranza();" class="btn btn-primary" style="width: -webkit-fill-available;"/>
                    </div>

                </div>



            </div>



        </div>

        <div id="tab_cliente" class="tab-pane" style="padding: 13px;">

<?php /*
            <div id="printzone" class="col-md-4" style="margin-left: -27px;margin-right: 87px;">
                <iframe id="ifrm"  name="ifrm" src="" style="height:524px; width:430px"></iframe>
            </div>
*/ ?>

            <div class="col-md-4">

                <div id="zona-imprimir" style="border: outset; width: 280px;">

                    <div style="width: 280px;font-size: 10px;">
                        <div style="text-align: center;">
                            <image src="/Styles/img/icon/icon_LogoCircle.png" style="width: 50px;margin-top: 10px;"></image>
                            <div class="col-xs-12" id="nombre_empresa1"></div>
                            <div class="col-xs-12" id="direccion_empresa"></div>
                            <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                            <div>
                                <div class="col-xs-6" id="ruc_empresa"></div>
                                <div class="col-xs-6" id="telefono_tienda"></div>
                            </div>
                            <div class="col-xs-12" id="nombre_tienda"></div>
                            <div class="col-xs-12" id="direccion_tienda"></div>
                            <div class="col-xs-12" id="ubigeo_tienda"></div>
                            <div class="col-xs-12">===========================================</div>
                            <div class="col-xs-12" id="nombre_documento"></div>
                            <div class="col-xs-12" id="codigo_documento"></div>
                            <div class="col-xs-12">===========================================</div>
                        </div>
                        <div>
                            <div class="col-xs-6" id="fecha_documento"></div>
                            <div class="col-xs-6" style="text-align: right;" id="hora_documento"></div>
                        </div>
                        <div class="col-xs-12" id="nombre_cliente"></div>
                        <div class="col-xs-12" id="direccion_cliente"></div>
                        <div class="col-xs-12" id="ruc_cliente"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div style="text-align: center;">
                            <div class="col-xs-3">Descrip.</div>
                            <div class="col-xs-3">Cant.</div>
                            <div class="col-xs-3">P.Unit</div>
                            <div class="col-xs-3">Monto</div>
                        </div>
                        <div class="col-xs-12">===========================================</div>
                        <div id="div_articlosdocumento"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-4">Sub. Total</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e(($o->csimbolo_moneda ?? '')) ?></div>
                        <div class="col-xs-4" id="opgrabada_documento" style="text-align: right;"></div>
                        <div class="col-xs-4">IGV</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e(($o->csimbolo_moneda ?? '')) ?></div>
                        <div class="col-xs-4" id="igv_documento" style="text-align: right;"></div>
                        <div class="col-xs-4" style="text-align: right;DISPLAY: NONE;">ISC</div>
                        <div class="col-xs-4" style="text-align: right;DISPLAY: NONE;"><?= e(($o->csimbolo_moneda ?? '')) ?></div>
                        <div class="col-xs-4" id="isc_documento" style="text-align: right;DISPLAY: NONE;"></div>
                        <div class="col-xs-4">Total a Pagar</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e(($o->csimbolo_moneda ?? '')) ?></div>
                        <div class="col-xs-4" id="total_documento" style="text-align: right;"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-12" id="son_documento"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div id="div_cobranzadocumento"></div>
                        <div class="col-xs-4">Vuelto</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e(($o->csimbolo_moneda ?? '')) ?></div>                
                        <div class="col-xs-4" id="vuelto" style="text-align: right;"></div>

                         <div class="col-xs-8">Condición de Pago</div>                
                        <div class="col-xs-4" style="text-align: right;">CONTADO</div>

                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-12" id="vendedor"></div>
                        <div class="col-xs-12" id="codigo_caja"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-12" style="text-align: center;">Cuéntanos tu experiencia en:</div>
                        <div class="col-xs-12" style="text-align: center;">www.datpos.com</div>
                        <div class="col-xs-12" style="text-align: center;">Para Consultar El Documento Ingrese</div>
                        <div class="col-xs-12" style="text-align: center;">https://comprobantes.msgsac.net:453/documentos</div>
                        <div class="col-xs-12" style="text-align: center;" id="qrcode"></div>
                
                    </div>

                    <div style="color: white;">.</div>

                </div>
                <textarea id="tb_observacion" class="limpiar form-control moderno_tb" maxlength="50" cols="20" rows="2" style="margin-top: 10px;"></textarea>
                <div id="ponercanvas" style="margin-top: 0px !important;"></div>

            </div>

            <div class="col-md-8">

                
                <div class="col-md-12 row">
                    <div class="col-md-9 ui-widget">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="tb_clientes" onkeyup="BuscarClientes();"  type="text"  class="limpiar form-control moderno_tb"
                                    placeholder=" " autocomplete="off"/>
                                <label for="txtCliente">
                                    Buscar Clientes</label>
                            </span><a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes"
                                onclick="ModalConsultarClientes();" style="background-color: #ffffff; border: 0px">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                        </div>
                        <div id="sugerencias_clientes" style="display: none; width: 752px; height:519px; overflow-y: auto;z-index: 9999;position: absolute;background-color: white;"></div>
                         <?php /*
<input id="tb_clientes" onkeyup="BuscarClientes();" class="limpiar form-control moderno_tb" placeholder="Buscar Clientes"/>
                         
                        
*/ ?>
                    </div>
                    <div class="col-md-3 row" style="PADDING: 0PX; margin-left: 15px;" >
                        <input type="radio" id="rb_boleta" name="tipo" value="BV" checked>
                        <label id="ll_boleta" for="male">Boleta</label><br>

                        <input type="radio" id="rb_factura" name="tipo" value="FV" onchange="$('#hdd_coa').val('');$('#tb_clientes').val('')">
                        <label id="ll_factura" for="female">Factura</label><br>

                        <input type="radio" id="rb_notaventa" name="tipo" value="NV">
                        <label for="female">Nota de Venta</label><br>
                   </div>
                </div>

                 
                


                <div class="row">
                    <div class="col-md-4" style="text-align: center;">
                        <?php /*
<input data-toggle="modal" data-target="#modalEfectivoNuevo" type="button" value="Efectivo" class="btn btn-primary" style="width: -webkit-fill-available;" onclick="$('#tb_montonuevoefectivo').val('');"/>
*/ ?>
                        <?php /*
<img style="inline-size: 30%;" src="<?= basePath() ?>/assets/Styles/img/efectivo.jpg" data-toggle="modal" data-target="#modalEfectivoNuevo" title="Efectivo" onclick="$('#tb_montonuevoefectivo').val('');"/>
*/ ?>
                        <button   type="button" class="btn btn-primary" style="width: -webkit-fill-available;"  onclick="EfectivoNuevo();">
                            <i class="fa fa-money" style="font-size: 18px;"></i> Efectivo
                        </button>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        <?php /*
<input data-toggle="modal" data-target="#modalTarjetaNuevo" type="button" value="Tarjeta" class="btn btn-primary" style="width: -webkit-fill-available;" onclick="TarjetaNuevo();"/>
*/ ?>
                        <?php /*
<img style="inline-size: 30%;" src="<?= basePath() ?>/assets/Styles/img/tarjeta.png" data-toggle="modal" data-target="#modalTarjetaNuevo" title="Tarjeta" onclick="TarjetaNuevo();"/>
*/ ?>
                        <button  type="button" class="btn btn-primary" style="width: -webkit-fill-available;" onclick="TarjetaNuevo();">
                            <i class="fa fa-credit-card" style="font-size: 18px;"></i> Tarjeta
                        </button>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        <?php /*
<button type="button" value="Nota de Crédito" class="btn btn-primary" style="width: -webkit-fill-available;"/>
*/ ?>
                        <?php /*
<img style="inline-size: 20%;" src="<?= basePath() ?>/assets/Styles/img/notacredito.png" title="Nota Crédito"/>
*/ ?>
                        <button  type="button" class="btn btn-primary" style="width: -webkit-fill-available;" onclick="NuevoNC();">
                            <i class="fa fa-sticky-note-o" style="font-size: 18px;"></i> Nota de Crédito
                        </button>
                    </div>
                </div>



                <div style="height:238px; overflow-y: auto;">
                    <table id="tabla_pago" style="width: -webkit-fill-available;">
                        <colgroup>  
                            <col style="width:20%"></col> 
                            <col style="width:15%"></col> 
                            <col style="width:15%"></col>
                            <col style="width:15%"></col>
                            <col style="width:5%"></col>
                            <col style="width:5%"></col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">Pago</th>
                                <th class="text-center">Número Tarjeta</th>
                                <th class="text-center">Número Ref.</th>
                                <th class="text-center">Monto</th>
                                <th class="text-center"></th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <?php /*
<div style="text-align: end; margin-top: 10px;">
                    <a class="fa fa-plus" style="font-size: 27px;" title="Agregar medio de pago" data-toggle="modal" data-target="#modalNuevo" onclick="NuevoModal()"></a>
                </div>
*/ ?>

                <div class="col-md-12" style="border: groove;/*margin-top: 350px;*/">
                    <div class="row" style="margin-bottom: 2px;">
                        <div class="col-md-6">Monto Ingresado:</div>         
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div>        
                        <div id="div_totalcobranza" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                    </div>
                    <div class="row" style="margin-bottom: 2px;">
                        <div class="col-md-6">Faltante:</div>                
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div> 
                        <div id="div_faltante" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                    </div>
                    <div class="row" style="margin-bottom: 2px;">
                        <div class="col-md-6">Vuelto:</div>                
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div> 
                        <div id="div_vuelto" class="col-md-2" style="text-align-last: end;">0.00</div>    
                    </div>
                </div>

                <input type="button" class="btn btn-primary" value="Finalizar Cobranza" onclick="Cobrar();" style="margin-top: 10px;width: -webkit-fill-available;"/>

            </div>

        </div>

            <!-- LISTADO -->
            <div id="Lista" class="tab-pane tabcito" style="padding: 13px;">

                <div class="row" style="padding-top: 10px;">
                    <div class="col-sm-6">
                        <span class="has-float-label">
                            <input id="tb_cliente" type="text" class="limpiar form-control moderno_tb" placeholder=" "/>
                            <label for="tb_cliente">Cliente</label>
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <span class="has-float-label">
                            <input id="tb_ruc" type="number" maxlength="11" class="limpiar form-control moderno_tb" placeholder=" "/>
                            <label for="tb_ruc">Ruc</label>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="floating-label">
                            <select class="floating-select" onclick="this.setAttribute('value', this.value);<?php /*
ObtenerNombreColumna(this);
*/ ?>" value="" id="ddl_tipodoc">
                                <option value=""></option>
                                <option value="BV">Boleta</option>
                                <option value="FV">Factura</option>
                                <option value="NC">Nota de credito</option>
                            </select>
                            <label class="floating-select2">Tipo Documento</label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <span class="has-float-label">
                            <input id="tb_serie" type="number" maxlength="8" class="limpiar form-control moderno_tb" placeholder=" "/>
                            <label for="tb_serie">Serie</label>
                        </span>
                    </div>
                    <div class="col-sm-4">
                        <span class="has-float-label">
                            <input id="dp_fechadoc" type="text" class="limpiar form-control moderno_tb" placeholder="" <?php /*
onclick="ObtenerNombreColumna(this)"
*/ ?> />
                            <label for="dp_fechadoc">Fecha Documento</label>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="floating-label">
                            <select class="floating-select" value="" id="ddl_estado">
                                <option value=""></option>
                                <option value="1">Por enviar</option>
                                <option value="2">Enviando</option>
                                <option value="3">Enviado a Sunat</option>
                                <option value="4">Declarado Sunat - Aceptado</option>
                                <option value="5">Declarado Sunat - Aceptado con Obs</option>
                                <option value="6">Declarado Sunat - Rechazado</option>
                            </select>
                            <label class="floating-select2">Estado Documento</label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <span class="has-float-label">
                            <input id="dp_fechasunat" type="text" class="limpiar form-control moderno_tb" placeholder=""/>
                            <label for="dp_fechasunat">Fecha Sunat</label>
                        </span>
                    </div>
                </div>

                <table id="table_id" class="display" style="width: -webkit-fill-available;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo Documento</th>
                            <th>Serie y Número</th>
                            <th>Importe Total</th>
                            <th>Fecha Documento</th>
                            <th>Estado</th>
                            <th>Fecha Sunat</th>
                            <th>Error Sunat</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>



    </div>





    <div id="modalEfectivoNuevo" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div> 
                        <div class="col-md-1" id="modal_divTotalEfectivoNuevo"></div>
                </div>
            </div>
            <div class="modal-body">
                <input id="tb_montonuevoefectivo" type="number" placeHolder="Ingrese Monto" class="moderno_tb" onkeyup="if($('#tb_montonuevoefectivo').val().length==0) $('#btn_confirmarmontonuevoefectivo').removeClass('fa_enabled').addClass('fa_disabled'); else $('#btn_confirmarmontonuevoefectivo').removeClass('fa_disabled').addClass('fa_enabled');"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="PasarPagoEfectivo();" id="btn_confirmarmontonuevoefectivo">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>
    
    <div id="modalEditarCantidad" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
      <?php /*
<div class="modal-dialog modal-sm">
*/ ?>
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
<?php /*
                <div class="row">
                    <div class="col-md-12" style="margin-bottom: 5px;">Nombre de Producto</div>
                    <div class="col-md-12" style="margin-bottom: 20px;"><input id="tb_nombre" class="moderno_tb" value='Polo Adidas'/></div>
                    <div class="col-md-12" style="margin-bottom: 5px;">Precio de Producto</div>
                    <div class="col-md-12" style="margin-bottom: 20px;"><input id="tb_precio" class="moderno_tb" value='S/ 15.00'/></div>
                    <div class="col-md-12" style="margin-bottom: 5px;">Cantidad</div>
                    <div class="col-md-12"><input id="tb_cantidad" onfocus="this.select();" type="number" class="moderno_tb" value='1'/></div>
                </div>
*/ ?>
                <div class="row">

                    <input type="hidden" id="hdd_descmax"/>
                    <input type="hidden" id="hdd_precio"/>

                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <div class="row col-md-8">
                            <div class="col-md-3">Producto:</div>
                            <div class="col-md-9"><input disabled id="tb_nombre" class="moderno_tb"/></div>    
                        </div>
                        <div class="row col-md-4">
                            <div class="col-md-4">Precio:</div>
                            <div class="col-md-8"><input disabled id="tb_precio" class="moderno_tb" style="width: -webkit-fill-available;"/></div>
                        </div>
                    </div>

                    <div class="col-md-12">

                        <div class="row col-md-5">
                            <div class="col-md-4" style="margin-bottom: 5px;">Cantidad:</div>
                            <div class="col-md-8"><input id="tb_cantidad" onfocus="this.select();" type="number" class="moderno_tb" style="width: inherit;"/></div>  
                        </div>
                        <div class="row col-md-7">
                            <div class="col-md-3" style="margin-bottom: 5px;">Descuento:</div>
                            <div class="col-md-5">
                                <input id="tb_descuento" type="number" class="moderno_tb" style="width: -webkit-fill-available;"/>
                            </div>
                            <div class="col-md-4">
		                        <div class="radio-toggle">
			                        <div class="form-check">
			  	                        <label class="form-check-label" id="lb_moneda">
				                            <input class="form-check-input" type="radio" name="exampleRadios" id="cb_moneda" checked>
				                            <?= e(($o->csimbolo_moneda ?? '')) ?>
			  	                        </label>
			  	                        <label class="form-check-label" id="lb_porcentaje">
				                            <input class="form-check-input" type="radio" name="exampleRadios" id="cb_porcentaje">
				                            %
			  	                        </label>
			                        </div>
		                        </div>
                            </div>
                        </div>
                    </div>

                    <div id="div_variantes">
                        <div class="col-md-12">
                            <a class="" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample" style="margin-left: 14px;">
                                Variantes
                            </a>
                        </div>
                        <div class="collapse" id="collapseExample">

                            <div class="col-md-12">
                                <div class="col-md-5"><select class="form-control moderno_tb" id="ddl_variante" onchange="CargarSubvariantes();"></select></div>
                                <div class="col-md-5"><select class="form-control moderno_tb" id="ddl_subvariante"></select></div>
                                <div class="col-md-2"><a class="fa fa-plus" style="font-size: 20pt;margin-top: 11px;" onclick="PasarVariante();"></a></div>
                            </div>    
                            <div id="div_eraser" class="col-md-12" style="display:none">
                                <div class="col-md-1" style="margin-top: 15px;"><a class="fa fa-eraser" onclick="$('#cadena_variantes').text('');$('#div_eraser').hide();"></a></div>
                                <div class="col-md-11" style="margin-top: 15px;"><label id="cadena_variantes"></label></div>
                            </div>    

                            <input id="hdd_subvariantes" type="hidden" />
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="CambiarCantidad();">Aceptar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalEfectivoEditar" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div> 
                        <div class="col-md-1" id="modal_divTotalEfectivoEditar"></div>
                </div>
            </div>
            <div class="modal-body">
                <input id="tb_montoeditarefectivo" type="number" placeHolder="Ingrese Monto" class="moderno_tb" onkeyup="if($('#tb_montoeditarefectivo').val().length==0) $('#btn_editarpagoefectivo').removeClass('fa_enabled').addClass('fa_disabled'); else $('#btn_editarpagoefectivo').removeClass('fa_disabled').addClass('fa_enabled');"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="EditarPagoEfectivo();" id="btn_editarpagoefectivo">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalNCNuevo" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-8">Importe total:</div> 
                        <div class="col-md-2" id="txtITNC"></div>
                </div>
            </div>
            <div class="modal-body">
                 
                <table id="tableNC" class="display" style="width: -webkit-fill-available;">
                   <colgroup>  
                        <col style="width:5%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col> 
                    </colgroup>
                    <thead id="thtableClientes">
                        <tr> 
                            <th> 
                            </th>
                            <th>
                                Doc. Ref.
                            </th>
                             <th>
                                Credito
                            </th>
                            <th>
                                Fecha Doc.
                            </th>  
                        </tr>
                    </thead>
                    <tbody  >
                    </tbody>
                </table>
               
            </div>
            <div class="modal-footer"> 
                <button type="button" class="btn btn-primary"   onclick="PasarPagoNC();">
                        Seleccionar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>


    <div id="modalTarjetaNuevo" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div> 
                        <div class="col-md-1" id="modal_divTotalNuevoTarjeta"></div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 40px;">
                    <div class="col-md-4" style="text-align: center;">
                        <img style="inline-size: 35%;" class="sombreado_mp" src="<?= basePath() ?>/assets/Styles/img/logo_visa.png"  onclick="PagoVisa(this)"/>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        <img style="inline-size: 35%;" src="<?= basePath() ?>/assets/Styles/img/logo_mastercard.png"  onclick="PagoMasterCard(this)"/>
                    </div>  
                    <div class="col-md-4" style="text-align: center;">
                        <img style="inline-size: 35%;" src="<?= basePath() ?>/assets/Styles/img/otrastarjetas.png" onclick="PagoOtraTarjeta(this)"/>
                    </div>                          
                </div>
                <div  class="row" id="div_tarjetanuevo" style="display: none;text-align: center;">
                    <select id="ddl_tarjetanuevo" class="moderno_tb" onchange="$('#hdd_metodopago').val(this.value);">
                        <option>Seleccione tipo de Tarjeta</option>
                        <option>Diners Club</option>
                        <option>American Express</option>
                        <option>Transferencia</option>
                        <option>Yape</option>
                        <option>Plin</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        Monto de Operación
                    </div>
                    <div class="col-md-6">
                        <input type="number" onfocusout="PerdidaFoco(this);" id="tb_montonuevotarjeta" class="moderno_tb" onkeyup="if( $('#tb_montonuevotarjeta').val()>0 &&  $('#tb_tarjeta').val().length>0 && $('#tb_referencia').val().length>0) $('#btn_confirmartarjeta').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjeta').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Últimos 4 dígitos
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_tarjeta" maxlength="4"class="moderno_tb" onkeyup="if( $('#tb_montonuevotarjeta').val()>0 &&  $('#tb_tarjeta').val().length>0 && $('#tb_referencia').val().length>0) $('#btn_confirmartarjeta').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjeta').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Número de referencia
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_referencia" maxlength="8" class="moderno_tb" onkeyup="if( $('#tb_montonuevotarjeta').val()>0 &&  $('#tb_tarjeta').val().length>0 && $('#tb_referencia').val().length>0) $('#btn_confirmartarjeta').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjeta').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="PasarPagoTarjeta();" id="btn_confirmartarjeta">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalTarjetaEditar" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e(($o->csimbolo_moneda ?? '')) ?></div> 
                        <div class="col-md-1" id="modal_divTotalEditarTarjeta"></div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 40px;">
                    <div class="col-md-4">
                        <img id="img_visa" class="sombreado_mp" src="<?= basePath() ?>/assets/Styles/img/logo_visa.png"  onclick="PagoVisa(this)"/>
                    </div>
                    <div class="col-md-4">
                        <img id="img_mastercard" src="<?= basePath() ?>/assets/Styles/img/logo_mastercard.png"  onclick="PagoMasterCard(this)"/>
                    </div>  
                    <div class="col-md-4">
                        <img id="img_otra" src="<?= basePath() ?>/assets/Styles/img/otrastarjetas.png" onclick="PagoOtraTarjeta(this)"/>
                    </div>                          
                </div>
                <div  class="row" id="div_tarjetaeditar" style="display: none">
                    <select id="ddl_tarjetas" class="moderno_tb"  onchange="$('#hdd_metodopago').val(this.value);">
                        <option>Seleccione tipo de Tarjeta</option>
                        <option>Diners Club</option>
                        <option>American Express</option>
                        <option>Transferencia</option>
                        <option>Yape</option>
                        <option>Plin</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        Monto de Operación
                    </div>
                    <div class="col-md-6">
                        <input type="number" onfocusout="PerdidaFoco(this);" id="tb_montoeditartarjeta" class="moderno_tb" onkeyup="if( $('#tb_montoeditartarjeta').val()>0 && $('#tb_tarjetaeditar').val().length>0 && $('#tb_referenciaeditar').val().length>0) $('#btn_confirmartarjetaeditar').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjetaeditar').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Últimos 4 dígitos
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_tarjetaeditar" maxlength="4" class="moderno_tb" onkeyup="if( $('#tb_montoeditartarjeta').val()>0 && $('#tb_tarjetaeditar').val().length>0 && $('#tb_referenciaeditar').val().length>0) $('#btn_confirmartarjetaeditar').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjetaeditar').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Número de referencia
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_referenciaeditar" maxlength="8" class="moderno_tb" onkeyup="if( $('#tb_montoeditartarjeta').val()>0 && $('#tb_tarjetaeditar').val().length>0 && $('#tb_referenciaeditar').val().length>0) $('#btn_confirmartarjetaeditar').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjetaeditar').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="EditarPagoTarjeta();" id="btn_confirmartarjetaeditar">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalResumenVenta" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 0px;">
                <div class="col-md-12" style="text-align: center;">
                    <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" style="width: 33px;">
                    <h4>Operación Completada</h1>
                </div>
            </div>
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-md-6">Num. Documento:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_doc"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Monto Total:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_total"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Monto Entregado:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_entregado"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Vuelto:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_vuelto"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Imprimir</div>
                    <div class="col-md-6" style="text-align: right;">
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="ckb_Imprimir" value="option1"></div>
                    </div>
                </div>
                <div class="row" style="DISPLAY: NONE;">
                    <div class="col-md-6">Enviar Correo:</div>
                    <div class="col-md-6" style="text-align: right;">
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="ckb_Correo" value="option2"></div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer" style="border-top: 0px;"> 
                <div class="col-md-12" style="text-align: center;">
                    <button type="button" class="btn btn-primary" onclick="FinalizarResumenDoc();">Confirmar</button>
                </div>
            </div>
        </div>
        </div>
    </div>

    <ul id="MenuFavoritos" class="dropdown-menu" role="menu" style="display:none" >
        <div class="input-group">
            <a><img src="<?= basePath() ?>/assets/Styles/images/icon_exel_c.png" style="width:14px;margin-right:8px;margin-left:5px" />Añadir a Favoritos</a> 
        </div>
    </ul>

 
    <div class="modal" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="modalLabel"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: #d4e1e4;">
                <div class="modal-header">
                    <div class="col-sm-6">
                        <h5 class="modal-title">
                            Seleccione Cliente</h5>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body" style="margin: 10px;">
                    <table id="tableVisibleConsulClientes" class="display" style="width: 100%;">
                        <colgroup>
                            <col style="width: 10%"></col>
                            <?php /*
<col style="width: 30%"></col>
*/ ?>
                            <col style="width: 60%"></col>
                        </colgroup>
                        <thead id="thTablaConsultarCliente">
                            <tr>
                                <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4;
                                    background-color: rgb(33, 182, 215); color: White;">
                                </th>
<?php /*
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                    color: White;">
                                    Cliente
                                </th>
*/ ?>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                    color: White;">
                                    Nombre del Cliente
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer" style="margin: 10px;">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodCliente();">
                        Seleccionar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php /*
    <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalArticuloLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="H1">
                            <div class="col-md-6">Importe total:</div>
                            <div class="col-md-6" id="Div2"></div>
                    </h5>

                </div>
                <div class="modal-body">
                    <div class="row" style="margin-bottom: 40px;">
                        <div class="col-md-4">
                            <img class="sombreado_mp" onclick="ActivarMedioPagoEfectivo(this)" src="<?= basePath() ?>/assets/Styles/img/efectivo.jpg" />
                        </div>
                        <div class="col-md-4">
                            <img src="<?= basePath() ?>/assets/Styles/img/logo_visa.png"  onclick="ActivarMedioPagoTarjeta(this)"/>
                        </div>
                        <div class="col-md-4">
                            <img src="<?= basePath() ?>/assets/Styles/img/logo_mastercard.png"  onclick="ActivarMedioPagoTarjeta(this)"/>
                        </div>    
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            Monto de Operación
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="moderno_tb" />
                        </div>
                    </div> 
                    <div id="div3" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                Últimos 4 dígitos
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="moderno_tb" />
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                Número de referencia
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="moderno_tb" />
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasarArticulo();">Seleccionar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
*/ ?>


<?php /*
<script>
    var str = '';
    var menu = new Contextmenu({
        name: "menu",
        wrapper: ".wrapper",
        trigger: ".item",
        item: [{
            "name": "Click Event",
            "func": "setText()",
            "link": null,
            "disable": false
        }
        ],
        target: "_blank",
        beforeFunc: function (ele) {
            str = $(ele).text();
        }
    });

    function setText() {
        alert(str);
    }

	
</script>
*/ ?>




</div>

<?php /*
<div class="col-md-12">

    <div id="zona-imprimir" style="width: 280px; display:none">

        <div style="width: 280px;font-size: 10px;">
            <div style="text-align: center;">
                <image src="/Styles/img/icon/icon_LogoCircle.png" style="width: 50px;margin-top: 10px;"></image>
                <div class="col-xs-12" id="nombre_empresa1"></div>
                <div class="col-xs-12" id="direccion_empresa"></div>
                <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                <div>
                    <div class="col-xs-6" id="ruc_empresa"></div>
                    <div class="col-xs-6" id="telefono_tienda"></div>
                </div>
                <div class="col-xs-12" id="nombre_tienda"></div>
                <div class="col-xs-12" id="direccion_tienda"></div>
                <div class="col-xs-12" id="ubigeo_tienda"></div>
                <div class="col-xs-12">===========================================</div>
                <div class="col-xs-12" id="nombre_documento"></div>
                <div class="col-xs-12" id="codigo_documento"></div>
                <div class="col-xs-12">===========================================</div>
            </div>
            <div>
                <div class="col-xs-6" id="fecha_documento"></div>
                <div class="col-xs-6" style="text-align: right;" id="hora_documento"></div>
            </div>
            <div class="col-xs-12" id="nombre_cliente"></div>
            <div class="col-xs-12" id="direccion_cliente"></div>
            <div class="col-xs-12" id="ruc_cliente"></div>
            <div class="col-xs-12">===========================================</div>
            <div style="text-align: center;">
                <div class="col-xs-3">Descrip.</div>
                <div class="col-xs-3">Cant.</div>
                <div class="col-xs-3">P.Unit</div>
                <div class="col-xs-3">Monto</div>
            </div>
            <div class="col-xs-12">===========================================</div>
            <div id="div_articlosdocumento"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-4">Op.Grabada</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="opgrabada_documento" style="text-align: right;"></div>
            <div class="col-xs-4">IGV</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="igv_documento" style="text-align: right;"></div>
            <div class="col-xs-4">ISC</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="isc_documento" style="text-align: right;"></div>
            <div class="col-xs-4">Total a Pagar</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="total_documento" style="text-align: right;"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-12" id="son_documento"></div>
            <div class="col-xs-12">===========================================</div>
            <div id="div_cobranzadocumento"></div>
            <div class="col-xs-4">Vuelto</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>                
            <div class="col-xs-4" id="vuelto" style="text-align: right;"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-12" id="vendedor"></div>
            <div class="col-xs-12" id="codigo_caja"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-12" style="text-align: center;">Cuéntanos tu experiencia en:</div>
            <div class="col-xs-12" style="text-align: center;">www.datpos.com</div>
            <div class="col-xs-12" style="text-align: center;" id="qrcode"></div>
                
        </div>

        <div style="color: white;">.</div>

    </div>

    <div id="ponercanvas" style="margin-top: 0px !important;"></div>

</div>
*/ ?>

<?php if (!empty($startupScript)): ?>
<script type="text/javascript">
    $(document).ready(function() {
        <?= $startupScript ?>
    });
</script>
<?php endif; ?>
<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
