#!/usr/bin/env python3
"""
Genera ManualDatpos.pdf — Manual de usuario del sistema DATPOS.

Uso:
    python3 scripts/generate_manual.py

Genera el archivo en assets/manuales/ManualDatpos.pdf
"""
from fpdf import FPDF
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT_DIR = REPO / "assets" / "manuales"
OUT_DIR.mkdir(parents=True, exist_ok=True)
OUT = OUT_DIR / "ManualDatpos.pdf"


class ManualPDF(FPDF):
    def header(self):
        if self.page_no() == 1:
            return
        self.set_font("Helvetica", "B", 10)
        self.set_text_color(33, 182, 215)
        self.cell(0, 8, "Manual de Usuario - DATPOS", border=0, ln=1, align="L")
        self.set_draw_color(33, 182, 215)
        self.set_line_width(0.4)
        self.line(10, 20, 200, 20)
        self.ln(4)
        self.set_text_color(0, 0, 0)

    def footer(self):
        self.set_y(-12)
        self.set_font("Helvetica", "I", 8)
        self.set_text_color(120, 120, 120)
        self.cell(0, 8, f"DATPOS - Pagina {self.page_no()}", align="C")

    def h1(self, text):
        self.set_font("Helvetica", "B", 18)
        self.set_text_color(33, 182, 215)
        self.cell(0, 12, text, ln=1)
        self.set_text_color(0, 0, 0)
        self.ln(2)

    def h2(self, text):
        self.set_font("Helvetica", "B", 14)
        self.set_text_color(40, 40, 40)
        self.cell(0, 10, text, ln=1)
        self.set_draw_color(33, 182, 215)
        self.set_line_width(0.3)
        x = self.get_x()
        y = self.get_y()
        self.line(x, y, x + 60, y)
        self.ln(3)

    def h3(self, text):
        self.set_font("Helvetica", "B", 12)
        self.set_text_color(60, 60, 60)
        self.cell(0, 8, text, ln=1)
        self.set_text_color(0, 0, 0)

    def p(self, text):
        self.set_font("Helvetica", "", 11)
        self.multi_cell(0, 6, text)
        self.ln(1)

    def bullet(self, text):
        self.set_font("Helvetica", "", 11)
        x = self.get_x()
        y = self.get_y()
        self.cell(6, 6, "-", border=0)
        avail = self.w - self.r_margin - self.get_x()
        self.multi_cell(avail, 6, text)
        self.set_x(x)

    def note(self, text):
        self.set_fill_color(255, 248, 220)
        self.set_draw_color(220, 180, 50)
        self.set_font("Helvetica", "I", 10)
        self.multi_cell(0, 6, "Nota: " + text, border=1, fill=True)
        self.ln(2)


def cover(pdf: ManualPDF):
    pdf.add_page()
    pdf.set_fill_color(33, 182, 215)
    pdf.rect(0, 0, 210, 60, "F")
    pdf.set_text_color(255, 255, 255)
    pdf.set_font("Helvetica", "B", 32)
    pdf.set_xy(10, 18)
    pdf.cell(0, 14, "DATPOS", ln=1)
    pdf.set_font("Helvetica", "", 16)
    pdf.set_x(10)
    pdf.cell(0, 10, "Manual de Usuario", ln=1)

    pdf.set_text_color(0, 0, 0)
    pdf.set_y(80)
    pdf.set_font("Helvetica", "B", 22)
    pdf.cell(0, 12, "Sistema de Punto de Venta", ln=1, align="C")
    pdf.set_font("Helvetica", "", 12)
    pdf.ln(8)
    pdf.multi_cell(
        0,
        7,
        "Esta guia describe los modulos principales del sistema DATPOS, "
        "asi como los pasos basicos para operar Tablas, Administracion, "
        "Ventas, Operaciones logisticas, Consultas y Reportes.",
        align="C",
    )
    pdf.ln(20)
    pdf.set_font("Helvetica", "I", 10)
    pdf.set_text_color(120, 120, 120)
    pdf.cell(0, 6, "Version 1.0", ln=1, align="C")
    pdf.cell(0, 6, "MSGSAC", ln=1, align="C")


def toc(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("Contenido")
    items = [
        "1. Introduccion",
        "2. Acceso al sistema",
        "3. Pantalla principal",
        "4. Tablas (Familias, Unidades, Almacenes, Articulos)",
        "5. Administracion (Usuarios, Roles, Tiendas, Cajas, Tipos de operacion)",
        "6. Configuracion General",
        "7. Ventas (Facturacion, Notas, Apertura y Cierre de caja)",
        "8. Operaciones logisticas (Ingresos, Salidas, Transferencias, Guias)",
        "9. Consultas",
        "10. Reportes",
        "11. Preguntas frecuentes",
    ]
    pdf.set_font("Helvetica", "", 12)
    for it in items:
        pdf.cell(0, 8, it, ln=1)


def section_intro(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("1. Introduccion")
    pdf.p(
        "DATPOS es un sistema integral de Punto de Venta orientado a "
        "comercios minoristas y mayoristas. Permite administrar maestros "
        "de articulos, clientes, proveedores, almacenes y cajas; realizar "
        "ventas (boletas, facturas, notas de credito y debito), gestionar "
        "movimientos logisticos (ingresos, salidas, transferencias y guias "
        "de remision) y consultar reportes operativos y tributarios."
    )
    pdf.h2("Glosario rapido")
    pdf.bullet("Empresa (ccod_cia): codigo de la empresa con la que opera el usuario.")
    pdf.bullet("Tienda / Almacen / Caja: ubicaciones logicas asignadas al usuario.")
    pdf.bullet("Tipo de operacion: define si un movimiento es de ingreso (I) o salida (S).")
    pdf.bullet("Tarifa: lista de precios activa para la venta.")
    pdf.bullet("Cliente Boleta: cliente generico utilizado en boletas de venta.")


def section_acceso(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("2. Acceso al sistema")
    pdf.p(
        "Ingrese a la URL del sistema (por ejemplo http://localhost:8080) "
        "e introduzca su usuario y contrasena. Si la sesion expira, sera "
        "redirigido automaticamente a la pagina de inicio de sesion."
    )
    pdf.h3("Pasos")
    pdf.bullet("Abra el navegador (Chrome o Edge actualizado).")
    pdf.bullet("Escriba sus credenciales de usuario.")
    pdf.bullet("Seleccione la empresa y tienda asignada.")
    pdf.bullet("Haga clic en Iniciar sesion.")
    pdf.note(
        "Si recibe el mensaje 'Usuario o contrasena incorrectos', verifique "
        "el bloqueo de mayusculas y reintente. Tras varios intentos fallidos "
        "el usuario puede ser bloqueado por el administrador."
    )


def section_home(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("3. Pantalla principal")
    pdf.p(
        "La pantalla principal muestra accesos directos a los modulos "
        "habilitados para su rol. El menu lateral permite navegar entre "
        "Tablas, Administracion, Ventas, Operaciones, Consultas y Reportes."
    )
    pdf.h2("Barra de acciones")
    pdf.bullet("Nuevo: crea un registro nuevo en el modulo activo.")
    pdf.bullet("Editar: habilita los campos del registro seleccionado.")
    pdf.bullet("Grabar: persiste los cambios.")
    pdf.bullet("Eliminar: marca el registro como inactivo o lo borra.")
    pdf.bullet("Deshacer: descarta los cambios no guardados.")


def section_tablas(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("4. Tablas")
    pdf.p(
        "Los maestros (Familias, Unidades de medida, Almacenes y "
        "Articulos) son la base sobre la que opera el resto del sistema."
    )
    pdf.h2("Familias")
    pdf.p("Agrupa articulos por categoria. Cada familia tiene codigo y descripcion.")
    pdf.h2("Unidades de medida")
    pdf.p("Define las unidades comerciales y de almacenaje (UND, KG, CJ, etc).")
    pdf.h2("Almacenes")
    pdf.p("Permite registrar los almacenes fisicos asociados a la empresa.")
    pdf.h2("Articulos")
    pdf.p(
        "Maestro de productos con stock, precio, familia, unidad de medida "
        "y configuracion tributaria. La consulta soporta filtro por familia "
        "(incluyendo la opcion 'Todos')."
    )


def section_admin(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("5. Administracion")
    pdf.h2("Usuarios")
    pdf.p(
        "Permite crear y mantener usuarios, asignar rol, tienda, almacen "
        "y caja. Tambien define la contrasena inicial."
    )
    pdf.h2("Roles")
    pdf.p("Configura los permisos por modulo y por accion (consultar, crear, editar, eliminar).")
    pdf.h2("Tiendas / Cajas")
    pdf.p("Mantiene el catalogo de tiendas fisicas y cajas asociadas a cada tienda.")
    pdf.h2("Tipos de operacion")
    pdf.p(
        "Define los tipos de operacion (ingresos, salidas, devoluciones) que se "
        "utilizan en los modulos de Operaciones logisticas y en la "
        "Configuracion General."
    )


def section_config(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("6. Configuracion General")
    pdf.p(
        "Centraliza los parametros de la empresa: codigo de cliente boleta, "
        "moneda, tarifa, numero tributario, cantidad de tiendas y usuarios "
        "habilitados, monto maximo por boleta, facturacion electronica, "
        "fecha de vencimiento de la facturacion, operaciones automaticas "
        "para devoluciones y salidas, tributos (IGV / ISC) y logo de la empresa."
    )
    pdf.h2("Como editar la configuracion")
    pdf.bullet("Pulse Editar para habilitar los campos.")
    pdf.bullet("Modifique los valores requeridos. Los campos con * son obligatorios.")
    pdf.bullet("Pulse Grabar para guardar los cambios.")
    pdf.h2("Botones especiales")
    pdf.bullet("Manual DATPOS: abre este manual de usuario en una nueva pestana.")
    pdf.bullet("Borrar imagen: elimina el logo cargado.")
    pdf.note(
        "Los datos de empresa (Moneda, Tarifa, Numero tributario, "
        "Cantidad de tiendas y usuarios, Facturacion electronica y Fecha "
        "de vencimiento) se obtienen de la sesion del usuario. Si los ve "
        "vacios o con codigo entre signos %, cierre sesion y vuelva a entrar; "
        "si persiste, contacte al administrador."
    )


def section_ventas(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("7. Ventas")
    pdf.p(
        "El modulo de Ventas concentra Facturacion, Notas de credito y "
        "debito, Anulaciones, Apertura y Cierre de caja, Clientes y Precios."
    )
    pdf.h2("Facturacion")
    pdf.p("Permite emitir boletas y facturas. Soporta venta con o sin lista de precios.")
    pdf.h2("Apertura y Cierre de caja")
    pdf.p(
        "Antes de operar Ventas debe aperturar la caja del dia. Al "
        "finalizar el turno se realiza el cierre de caja con cuadre de "
        "efectivo y otros medios de pago."
    )
    pdf.h2("Notas de credito y debito")
    pdf.p("Permiten ajustar documentos previamente emitidos por devoluciones o cargos adicionales.")


def section_logistica(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("8. Operaciones logisticas")
    pdf.h2("Ingresos")
    pdf.p("Registra entradas de mercaderia al almacen (compras, devoluciones de cliente, etc).")
    pdf.h2("Salidas")
    pdf.p("Registra salidas que no corresponden a ventas (consumos, mermas, traslados internos).")
    pdf.h2("Transferencias")
    pdf.p("Mueve stock entre almacenes de la misma empresa.")
    pdf.h2("Guias de remision")
    pdf.p("Genera el documento de traslado fisico de mercaderia.")


def section_consultas(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("9. Consultas")
    pdf.p(
        "Las consultas son pantallas de solo lectura que permiten revisar "
        "informacion operativa sin modificarla."
    )
    pdf.bullet("Configuracion General: parametros maestros de la empresa.")
    pdf.bullet("Consultas Almacen: stock por almacen, articulo y familia.")
    pdf.bullet("Consultas Venta: documentos emitidos por rango de fechas.")
    pdf.bullet("Kardex: historico de movimientos de un articulo.")
    pdf.bullet("Margen de utilidad: rentabilidad por articulo o periodo.")
    pdf.bullet("Stock minimo: alerta de articulos por debajo del nivel minimo.")


def section_reportes(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("10. Reportes")
    pdf.p(
        "Los reportes generan archivos imprimibles o exportables (PDF / Excel) "
        "para uso operativo, gerencial y tributario."
    )
    pdf.bullet("Reporte de Almacen.")
    pdf.bullet("Reporte de Kardex.")
    pdf.bullet("Reporte de Saldo.")
    pdf.bullet("Reporte Tributario.")
    pdf.bullet("Reporte de Turno.")
    pdf.bullet("Reporte de Venta.")


def section_faq(pdf: ManualPDF):
    pdf.add_page()
    pdf.h1("11. Preguntas frecuentes")
    pdf.h3("No puedo grabar un documento.")
    pdf.p(
        "Verifique que la caja este aperturada, que tenga rol con permiso "
        "de creacion y que todos los campos obligatorios esten completos."
    )
    pdf.h3("Mi sesion se cerro sola.")
    pdf.p("Por seguridad la sesion expira tras un tiempo de inactividad. Vuelva a iniciar sesion.")
    pdf.h3("No veo los datos de la empresa en Configuracion General.")
    pdf.p(
        "Cierre sesion y vuelva a iniciarla. Si los campos siguen vacios, "
        "contacte al administrador para revisar la configuracion del usuario."
    )
    pdf.h3("Como descargar este manual?")
    pdf.p(
        "En Configuracion General pulse el boton 'Manual DATPOS'. Tambien "
        "puede acceder directamente a la URL /assets/manuales/ManualDatpos.pdf."
    )


def main():
    pdf = ManualPDF(orientation="P", unit="mm", format="A4")
    pdf.set_auto_page_break(auto=True, margin=18)
    pdf.set_margins(15, 18, 15)

    cover(pdf)
    toc(pdf)
    section_intro(pdf)
    section_acceso(pdf)
    section_home(pdf)
    section_tablas(pdf)
    section_admin(pdf)
    section_config(pdf)
    section_ventas(pdf)
    section_logistica(pdf)
    section_consultas(pdf)
    section_reportes(pdf)
    section_faq(pdf)

    pdf.output(str(OUT))
    print(f"OK -> {OUT} ({OUT.stat().st_size} bytes)")


if __name__ == "__main__":
    main()
