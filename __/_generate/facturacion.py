import os
import zipfile
import re
from datetime import datetime
import requests
from reportlab.lib.pagesizes import letter
from reportlab.pdfgen import canvas
from reportlab.lib import colors

# ==============================================================================
# 1. ENTRADA DE DATOS Y CONFIGURACIÓN DEL PUENTE PHP
# ==============================================================================
year_input = input("Introduce el año (ej: 2026): ").strip()
month_input = input("Introduce el mes (ej: 03 para marzo): ").strip().zfill(2)

URL_BRIDGE = "https://www.arstudiocutz.es/home/admin/get_facturas.php"
API_KEY = ""

# ==============================================================================
# 2. DEFINICIÓN DE CAMPOS QUE DEVUELVE EL PHP
# ==============================================================================
COL_ID          = "id_factura"
COL_NUMERO      = "numero_factura"
COL_FECHA       = "fecha_cita"
COL_DNI         = "dni_cliente"  # Dejado preparado para cuando añadas la columna
COL_NOMBRE      = "nombre_completo"
COL_CONCEPTO    = "concepto_servicio"
COL_IMPORTE     = "total_importe"
COL_EMAIL       = "email_cliente"
COL_TELEFONO    = "telefono_cliente"
COL_METODO_PAGO = "metodo_pago"
COL_BARBERO     = "nombre_barbero"

# Rutas y archivos de salida
dir_facturas = f"./facturas_{year_input}_{month_input}"
os.makedirs(dir_facturas, exist_ok=True)

nombre_zip_mes = f"facturas_{year_input}{month_input}.zip"
ruta_zip_mes = os.path.join(".", nombre_zip_mes)
ruta_logo = 'C:/xampp/htdocs/arbarber_project/img/fact.png'

# ==============================================================================
# PDF Factura
# ==============================================================================
def limpiar_texto(texto):
    """Elimina tildes, comas o espacios para evitar errores al nombrar archivos."""
    if not texto:
        return "sin_datos"
    texto_limpio = re.sub(r'[^\w\s-]', '', str(texto)).strip()
    return re.sub(r'[-\s]+', '_', texto_limpio)

def crear_pdf_factura(datos, ruta_destino, ruta_logo):
    """Genera el PDF con el diseño exacto de la plantilla de ejemplo."""
    c = canvas.Canvas(ruta_destino, pagesize=letter)
    
    # METADATA DEL DOCUMENTO
    num_factura_str = str(datos.get('numero', '0000'))
    c.setTitle(f"Factura #{num_factura_str}")
    
    # Dimensiones y márgenes
    ancho_pagina, alto_pagina = letter # 612 x 792 pt
    margin_left = 40
    margin_right = ancho_pagina - 40
    
    y = alto_pagina - 40

    # --------------------------------------------------------------------------
    # 1. CABECERA: EMISOR / EMPRESA (Izquierda)
    # --------------------------------------------------------------------------
    # Verificación estricta de la ruta del archivo de imagen
    if ruta_logo and os.path.exists(ruta_logo) and os.path.isfile(ruta_logo):
        try:
            # Dibujamos el logo ajustando correctamente las coordenadas Y
            # 'width' y 'height' definen el recuadro máximo
            c.drawImage(
                ruta_logo, 
                margin_left, 
                y - 25, 
                width=110, 
                height=45, 
                preserveAspectRatio=True, 
                mask='auto'
            )
            y -= 35 # Bajamos la posición Y para que los textos no tapen el logo
        except Exception as err:
            # Si la imagen está corrupta o no es un formato válido, muestra texto como fallback
            print(f"⚠️ Aviso: No se pudo renderizar la imagen del logo ({err}). Usando texto.")
            c.setFont("Helvetica-Bold", 16)
            c.drawString(margin_left, y, "ARSTUDIO")
            y -= 18
    else:
        c.setFont("Helvetica-Bold", 16)
        c.drawString(margin_left, y, "ARSTUDIO")
        y -= 18

    # Datos fiscales
    c.setFont("Helvetica-Oblique", 9)
    c.setFillColor(colors.HexColor("#333333"))
    c.drawString(margin_left, y, "Calle Vílchez N° 34")
    y -= 12
    c.drawString(margin_left, y, "23150 Valdepeñas de Jaén, España")
    y -= 12
    c.drawString(margin_left, y, "NIF/CIF: B12345678")
    y -= 12
    c.drawString(margin_left, y, "contacto@arstudiocutz.es")
    y -= 12
    c.drawString(margin_left, y, "+34 666 000 000")
    
    # --------------------------------------------------------------------------
    # 2. SEGUNDA CABECERA: DATOS DEL CLIENTE (Bloque gris a la derecha)
    # --------------------------------------------------------------------------
    # Dimensiones de la caja de datos del cliente
    ancho_caja = 230
    alto_caja = 75
    padding_v = 10
    x_caja = margin_right - ancho_caja
    y_caja = y - alto_caja + 15

    # Dibujar rectángulo de fondo gris
    c.setFillColor(colors.HexColor("#F5F5F5"))
    c.rect(x_caja, y_caja, ancho_caja, alto_caja, fill=True, stroke=False)
    
    # Textos alineados a la derecha dentro de la caja con márgenes equilibrados
    c.setFillColor(colors.black)
    
    # 1. Título del bloque (Arriba con 10pt de padding)
    c.setFont("Helvetica-BoldOblique", 9)
    c.drawRightString(margin_right - 10, y_caja + alto_caja - padding_v - 8, "Datos del cliente")
    
    # 2. Resto de campos distribuidos uniformemente (salto de 12pt)
    c.setFont("Helvetica-Oblique", 8.5)
    
    linea_y = y_caja + alto_caja - padding_v - 22
    c.drawRightString(margin_right - 10, linea_y, str(datos.get('nombre', 'CLIENTE EN MOSTRADOR')))
    
    linea_y -= 12
    dni_val = datos.get('dni') if datos.get('dni') else 'Sin DNI'
    c.drawRightString(margin_right - 10, linea_y, f"NIF/DNI: {dni_val}")
    
    linea_y -= 12
    c.drawRightString(margin_right - 10, linea_y, f"Tel: {datos.get('telefono', 'Sin teléfono')}")
    
    linea_y -= 12
    c.drawRightString(margin_right - 10, linea_y, f"Correo electrónico: {datos.get('email', 'Sin correo electrónico')}")

    # Reajustamos la coordenada 'y' principal para continuar hacia la siguiente sección
    y = y_caja - 20

    # --------------------------------------------------------------------------
    # 3. DATOS DE LA FACTURA
    # --------------------------------------------------------------------------
    c.setFont("Helvetica-Bold", 11)
    c.drawString(margin_left, y, f"FACTURA #{num_factura_str}")
    y -= 14
    c.setFont("Helvetica-Oblique", 9)
    c.drawString(margin_left, y, f"Fecha: {datos.get('fecha_str', '')}")
    y -= 20

    # --------------------------------------------------------------------------
    # 4. TABLA DE PRODUCTOS / SERVICIOS
    # --------------------------------------------------------------------------
    c.setFillColor(colors.HexColor("#F5F5F5"))
    c.rect(margin_left, y - 5, margin_right - margin_left, 18, fill=True, stroke=False)
    
    c.setFillColor(colors.black)
    c.setFont("Helvetica-BoldOblique", 8)
    
    x_concepto = margin_left + 5
    x_precio   = 350
    x_unidades = 410
    x_subtotal = 460
    x_iva      = 510
    x_total    = margin_right - 5
    
    c.drawString(x_concepto, y, "CONCEPTO")
    c.drawRightString(x_precio, y, "PRECIO")
    c.drawRightString(x_unidades, y, "UNIDADES")
    c.drawRightString(x_subtotal, y, "SUBTOTAL")
    c.drawRightString(x_iva, y, "IVA")
    c.drawRightString(x_total, y, "TOTAL")
    
    c.setLineWidth(0.8)
    c.line(margin_left, y + 13, margin_right, y + 13)
    c.line(margin_left, y - 5, margin_right, y - 5)
    
    y -= 20

    # CÁLCULOS DE IVA Y BASE IMPONIBLE (21%)
    try:
        total_val = float(datos.get('importe', 0.00))
    except (ValueError, TypeError):
        total_val = 0.00

    porcentaje_iva = 0.21
    base_imponible = total_val / (1.0 + porcentaje_iva)
    cuota_iva = total_val - base_imponible

    # Fila de concepto
    c.setFont("Helvetica-Bold", 9)
    c.drawString(x_concepto, y, str(datos.get('concepto', 'Servicio de Barbería')))
    
    c.setFont("Helvetica", 9)
    c.drawRightString(x_precio, y, f"{base_imponible:.2f}€")
    c.drawRightString(x_unidades, y, "1 Unidades")
    c.drawRightString(x_subtotal, y, f"{base_imponible:.2f}€")
    c.drawRightString(x_iva, y, "21%")
    c.drawRightString(x_total, y, f"{total_val:.2f}€")
    
    y -= 12
    c.setFont("Helvetica-Oblique", 8)
    c.setFillColor(colors.HexColor("#555555"))
    c.drawString(x_concepto, y, f"Atendido por: {datos.get('barbero', 'Personal Barbería')}")
    c.setFillColor(colors.black)
    
    y -= 15
    c.setLineWidth(0.8)
    c.line(margin_left, y, margin_right, y)
    y -= 35

    # --------------------------------------------------------------------------
    # 5. BLOQUE RESUMEN TOTALES
    # --------------------------------------------------------------------------
    c.setFillColor(colors.HexColor("#F5F5F5"))
    c.rect(300, y - 3, margin_right - 300, 16, fill=True, stroke=False)
    c.setFillColor(colors.black)
    c.setFont("Helvetica-Bold", 8)
    c.drawRightString(480, y, "BASE IMPONIBLE")
    c.drawRightString(margin_right - 10, y, f"{base_imponible:.2f}€")
    y -= 22

    c.setFont("Helvetica-Bold", 8)
    c.drawRightString(480, y, "IVA 21%")
    c.drawRightString(margin_right - 10, y, f"{cuota_iva:.2f}€")
    y -= 22

    c.setFillColor(colors.HexColor("#EDEDED"))
    c.rect(300, y - 5, margin_right - 300, 20, fill=True, stroke=False)
    c.setFillColor(colors.black)
    c.setFont("Helvetica-Bold", 10)
    c.drawRightString(480, y, "TOTAL")
    c.drawRightString(margin_right - 10, y, f"{total_val:.2f}€")
    y -= 45

    # --------------------------------------------------------------------------
    # 6. PIE DE PÁGINA (Forma de Pago)
    # --------------------------------------------------------------------------
    c.setFont("Helvetica", 9)
    metodo_raw = str(datos.get('metodo_pago', 'efectivo')).lower().strip()
    
    if "tarjeta" in metodo_raw:
        texto_pago = "Forma de pago: con tarjeta"
    else:
        texto_pago = "Forma de pago: en efectivo"
        
    c.drawString(margin_left, y, texto_pago)

    c.save()

# ==============================================================================
# 4. EJECUCIÓN CONEXIÓN VÍA HTTP (PUENTE PHP)
# ==============================================================================
try:
    print("Consultando datos mediante el servidor web...")
    
    response = requests.get(
        URL_BRIDGE, 
        params={'year': year_input, 'month': month_input, 'key': API_KEY},
        timeout=15
    )
    
    if response.status_code == 200:
        registros = response.json()
        
        if isinstance(registros, dict) and "error" in registros:
            print(f"❌ Error desde el servidor PHP: {registros['error']}")
            exit()
            
        print(f"\nSe encontraron {len(registros)} citas completadas para {month_input}/{year_input}.\n")

        if not registros:
            print("No hay registros en el periodo seleccionado.")
            exit()

        archivos_pdf_generados = []

        for fila in registros:
            id_factura   = fila[COL_ID]
            num_factura  = fila[COL_NUMERO]
            fecha_str_in = str(fila[COL_FECHA])
            dni          = fila.get(COL_DNI)
            nombre       = fila[COL_NOMBRE]
            concepto     = fila[COL_CONCEPTO]
            importe      = fila[COL_IMPORTE]
            email        = fila[COL_EMAIL]
            telefono     = fila[COL_TELEFONO]
            metodo_pago  = fila[COL_METODO_PAGO]
            barbero      = fila[COL_BARBERO]

            try:
                fecha_obj = datetime.strptime(fecha_str_in, "%Y-%m-%d %H:%M:%S")
            except ValueError:
                fecha_obj = datetime.now()

            str_fecha_formateada = fecha_obj.strftime("%Y%m%d_%H%M%S")
            fecha_pantalla = fecha_obj.strftime("%Y-%m-%d %H:%M:%S")

            datos_pdf = {
                'id': id_factura,
                'numero': num_factura,
                'fecha_str': fecha_pantalla,
                'dni': dni,
                'nombre': nombre,
                'concepto': concepto,
                'importe': importe,
                'email': email,
                'telefono': telefono,
                'metodo_pago': metodo_pago,
                'barbero': barbero
            }

            # CORRECCIÓN 2: Generar ruta PDF única por cada factura dentro de la carpeta
            nombre_clean = limpiar_texto(nombre)
            nombre_pdf = f"f{str_fecha_formateada}_{nombre_clean}.pdf"
            ruta_pdf = os.path.join(dir_facturas, nombre_pdf)

            # Generar el PDF individual pasando la ruta_logo correcta
            crear_pdf_factura(datos_pdf, ruta_pdf, ruta_logo)
            archivos_pdf_generados.append((ruta_pdf, nombre_pdf))
            print(f"✔ PDF Creado: {nombre_pdf}")

        # Comprimir todos los PDFs en el archivo ZIP del mes
        print(f"\nComprimiendo en {nombre_zip_mes}...")
        with zipfile.ZipFile(ruta_zip_mes, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for ruta_abs, nombre_arch in archivos_pdf_generados:
                zipf.write(ruta_abs, arcname=nombre_arch)

        print(f"\n¡Proceso finalizado con éxito! Creado archivo '{nombre_zip_mes}'.")

    else:
        print(f"❌ Error HTTP {response.status_code}: Revisa la URL_BRIDGE o la respuesta del servidor.")

except Exception as e:
    print(f"❌ Error durante la ejecución: {e}")