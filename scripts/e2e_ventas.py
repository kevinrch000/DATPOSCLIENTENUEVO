
import asyncio
from pathlib import Path
from playwright.async_api import async_playwright

BASE='http://127.0.0.1:8080'
ART=Path('/home/ubuntu/repos/DatPos-e2e/DatPos/test-artifacts')
ART.mkdir(exist_ok=True)

async def main():
    results=[]
    async with async_playwright() as p:
        browser = await p.chromium.connect_over_cdp('http://localhost:29229')
        ctx = browser.contexts[0] if browser.contexts else await browser.new_context()
        page = ctx.pages[0] if ctx.pages else await ctx.new_page()
        page.on('console', lambda msg: results.append(('console', msg.type, msg.text)))
        page.on('pageerror', lambda exc: results.append(('pageerror', 'error', str(exc))))
        async def shot(name):
            await page.screenshot(path=str(ART/f'{name}.png'), full_page=True)
        async def goto(path, name):
            await page.goto(BASE+path, wait_until='networkidle')
            await shot(name)
            title = await page.title()
            body = (await page.locator('body').inner_text(timeout=5000))[:500]
            results.append(('page', name, title, body.replace('\n',' | ')))
        await goto('/pages/migcliente/LogOn.php','login')
        await page.fill('#UserName','ADMIN')
        await page.fill('#Password','123456')
        await page.click('#btnlogin')
        await page.wait_for_load_state('networkidle')
        await shot('home_after_login')
        results.append(('url','after_login',page.url))
        pages = [
            ('/pages/Ventas/AperturaCaja.php','apertura'),
            ('/pages/Ventas/CierreCaja.php','cierre'),
            ('/pages/Ventas/Facturacion.php','facturacion'),
            ('/pages/Ventas/Clientes.php','clientes'),
            ('/pages/Ventas/Precios.php','precios'),
            ('/pages/Ventas/NotaCredito.php','nota_credito'),
            ('/pages/Ventas/NotaDebito.php','nota_debito'),
            ('/pages/Ventas/Anulacion.php','anulacion'),
        ]
        for path,name in pages:
            try:
                await goto(path,name)
            except Exception as e:
                results.append(('FAIL_PAGE', name, repr(e)))
        # API smoke through logged session
        for method,payload in [
            ('ClientePorDefecto',{}),('ConsultarCategoriasDisponibles',{}),('ConsultarArticulosTodos',{'texto':''}),('ConsultarClientesTodos',{'texto':'','tipodoc':''}),('ConsultarTienda',{}),('ConsultarImpuestos',{})
        ]:
            resp = await page.request.post(f'{BASE}/api/facturacion_api.php?method={method}', data=payload)
            txt = await resp.text()
            results.append(('api', method, resp.status, txt[:300]))
        await browser.close()
    out='\n'.join(map(str, results))
    (ART/'e2e_results.txt').write_text(out)
    print(out)

asyncio.run(main())
