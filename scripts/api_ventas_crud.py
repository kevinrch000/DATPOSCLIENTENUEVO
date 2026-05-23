
import asyncio, json, datetime
from pathlib import Path
from playwright.async_api import async_playwright
BASE='http://127.0.0.1:8080'
ART=Path('/home/ubuntu/repos/DatPos-e2e/DatPos/test-artifacts'); ART.mkdir(exist_ok=True)
async def main():
    out=[]
    async with async_playwright() as p:
        browser=await p.chromium.connect_over_cdp('http://localhost:29229')
        ctx=browser.contexts[0] if browser.contexts else await browser.new_context()
        page=ctx.pages[0] if ctx.pages else await ctx.new_page()
        if 'LogOn' not in page.url and 'Home' not in page.url:
            await page.goto(BASE+'/pages/migcliente/LogOn.php')
        if 'LogOn' in page.url:
            await page.fill('#UserName','ADMIN'); await page.fill('#Password','123456'); await page.click('#btnlogin'); await page.wait_for_load_state('networkidle')
        async def post(api, method, data):
            r=await page.request.post(f'{BASE}/api/{api}?method={method}', data=json.dumps(data), headers={'Content-Type':'application/json'})
            t=await r.text()
            try: j=json.loads(t)
            except Exception: j=t
            out.append((api, method, r.status, str(j)[:600]))
            if r.status>=400: raise Exception((api,method,r.status,t))
            return j
        # Apertura caja CRUD
        tienda=(await post('aperturacaja_api.php','CargarTienda',{}))['d'][0]
        caja=(await post('aperturacaja_api.php','CargarCajaDeUsuario',{'ccod_usuario':'ADMIN'}))['d'][0]
        apertura=await post('aperturacaja_api.php','Guardar',{'DatTurno':[{'ccod_tienda':tienda['ccod_tiend'],'ccod_usuario':'ADMIN','ccod_caja':caja['ccod_caja'],'nmonto_ini':100,'dfchdoc_ini':datetime.datetime.now().strftime('%d/%m/%Y %H:%M:%S')}]})
        id_turno=apertura['d'][0]['id_turno']
        turnos=(await post('aperturacaja_api.php','ConsultarCierreCaja',{}))['d']
        if not str(id_turno).isdigit():
            id_turno=turnos[0]['id_turno']
        await post('aperturacaja_api.php','ConsultarIdCierreCaja',{'id_turno':id_turno})
        cierre=await post('cierrecaja_api.php','Guardar',{'DatTurno':[{'id_turno':id_turno,'ntot_entreg':100,'nmonto_fin':100,'ndiferencia':0}]})
        # Clientes CRUD
        code='TST'+datetime.datetime.now().strftime('%H%M%S')
        cliente={'ccod_coa':code,'cdoc_coa':'00000000','cdsc_coa':'CLIENTE TEST '+code,'ctelf':'999999999','cmail':'test@datpos.local','ctipo_coa':'CL','cpais':'PE','cdepartamento':'15','cprovincia':'1501','cdistrito':'150101','cdirc_coa':'DIR TEST','cstatus':'A','cproveedor':'2','ctip_doc':'CL','cruc_coa':'00000000'}
        await post('cliente_api.php','Guardar',{'operacion':'nuevo','cliente':[cliente]})
        await post('cliente_api.php','ConsultarCliente',{'codigo':code})
        cliente['cdsc_coa']='CLIENTE TEST EDITADO '+code
        await post('cliente_api.php','Guardar',{'operacion':'editar','cliente':[cliente]})
        await post('cliente_api.php','ConsultarCliente',{'codigo':code})
        await post('cliente_api.php','Eliminar',{'cliente':code})
        # Precios list and details
        lps=(await post('precio_api.php','ConsultarListaPrecios',{}))['d']; lp=lps[0]['ccod_cblistpre']
        await post('precio_api.php','ConsultarListaPrecio',{'codigo':lp})
        arts=(await post('precio_api.php','ConsultarArticulos',{}))['d']; art=arts[0]['ccod_articulo']
        await post('precio_api.php','ConsultarPrecios',{'listaprecio':lp,'TipFiltro':'1','Articulo':art})
        await post('precio_api.php','ObtenerIvg',{})
        # Facturacion backend CRUD
        await post('facturacion_api.php','ConsultarCategoriasDisponibles',{})
        await post('facturacion_api.php','ConsultarArticulosTodos',{'texto':''})
        await post('facturacion_api.php','ConsultarArticuloPrecioCodigo',{'codigo':art})
        det=[{'id_articulo':art,'ncantidad':1,'nprecio':8,'nimporte_neto':8,'nimporte_bruto':8,'nimpuesto':1.44,'ndescuento':0,'nigv':18}]
        cuenta=await post('facturacion_api.php','GuardarCuenta',{'cliente':'CLI000','etiqueta':'E2E','detalle':det})
        # Search docs and note/anulacion APIs should return safely
        today=datetime.datetime.now().strftime('%d/%m/%Y')
        filtro={'notacredito':[{'cdoc_seri':'','serie':'','correlativo':'','ccod_tienda':tienda['ccod_tiend'],'ccod_coa':'','fchDesde':today,'fchHasta':today,'cdoc':'','cdoc_serie':'','cdoc_nro':'','n_fchDesde':today,'n_fchHasta':today}]}
        await post('notacredito_api.php','ConsultarDocumentosNotaCredito',filtro)
        await post('notadebito_api.php','ConsultarDocumentos',filtro)
        await post('anulacion_api.php','AnulacionPricipal',{'anulacion':[{'cdoc':'','cdoc_serie':'','cdoc_nro':'','ccod_tienda':tienda['ccod_tiend'],'ccod_coa':'','n_fchDesde':today,'n_fchHasta':today}]})
        await browser.close()
    text='\n'.join(map(str,out))
    (ART/'api_crud_results.txt').write_text(text)
    print(text)
asyncio.run(main())
