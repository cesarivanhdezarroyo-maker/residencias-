"""
Microservicio de conversión Excel -> PDF para Ubuntu/Linux.
Alternativa a app.py (que usaba win32com y solo corre en Windows).

Requiere tener instalado LibreOffice en el servidor:
    sudo apt install libreoffice qpdf

Uso: exactamente igual que el original -> POST /convertir con el
archivo Excel adjunto en el campo "archivo". Responde con el PDF.
"""
import os
import subprocess
import uuid
from flask import Flask, request, send_file

app = Flask(__name__)


@app.route('/convertir', methods=['POST'])
def convertir_pdf():
    if 'archivo' not in request.files:
        return "No se envió ningún archivo", 400

    excel_file = request.files['archivo']

    base_dir = '/tmp'
    trabajo_id = uuid.uuid4().hex
    temp_excel = os.path.join(base_dir, f'temp_{trabajo_id}.xlsx')
    temp_pdf_multi = os.path.join(base_dir, f'temp_{trabajo_id}.pdf')
    temp_pdf_final = os.path.join(base_dir, f'salida_{trabajo_id}.pdf')

    excel_file.save(temp_excel)

    try:
        # NOTA: ya NO tocamos el archivo con openpyxl antes de convertir.
        # openpyxl no preserva de forma confiable las imágenes embebidas
        # al volver a guardar (perdía el logo del ISSSTE). En vez de
        # "ocultar" las demás hojas para que no generen páginas extra,
        # simplemente convertimos el archivo tal cual y nos quedamos con
        # la página 1 del resultado (que siempre es "Formato", por ser
        # la primera hoja del libro) usando qpdf, igual que antes.

        resultado = subprocess.run(
            [
                'soffice', '--headless', '--norestore',
                '--convert-to', 'pdf',
                '--outdir', base_dir,
                temp_excel,
            ],
            capture_output=True, text=True, timeout=60,
        )
        if resultado.returncode != 0:
            return f"Error al convertir con LibreOffice: {resultado.stderr}", 500

        pdf_generado = temp_excel.replace('.xlsx', '.pdf')
        if not os.path.exists(pdf_generado):
            return "LibreOffice no generó el PDF esperado", 500
        os.rename(pdf_generado, temp_pdf_multi)

        # "Formato" siempre es la primera hoja del libro -> siempre es
        # la página 1 del PDF resultante. Nos quedamos solo con esa.
        subprocess.run(
            ['qpdf', '--pages', temp_pdf_multi, '1', '--', temp_pdf_multi, temp_pdf_final],
            check=True, timeout=30,
        )

        return send_file(temp_pdf_final, as_attachment=True, download_name='Formato_Oficial.pdf')

    except subprocess.TimeoutExpired:
        return "La conversión tardó demasiado", 504
    except Exception as e:
        return f"Error en la conversión: {str(e)}", 500
    finally:
        for f in [temp_excel, temp_pdf_multi, temp_pdf_final]:
            if os.path.exists(f):
                os.remove(f)


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
