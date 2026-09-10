# Microservicio de conversión Excel → PDF (versión Linux/Ubuntu)

Alternativa a `app.py` original (que usaba win32com y solo corre en Windows).
Este usa LibreOffice en modo headless — probado y verificado con la plantilla
real del proyecto.

## Instalar dependencias del sistema (una sola vez)

```bash
sudo apt update
sudo apt install -y libreoffice qpdf python3-pip
```

## Instalar dependencias de Python

```bash
cd microservicio-pdf-linux
pip install -r requirements.txt --break-system-packages
```

## Correrlo

```bash
python3 app.py
```

Queda escuchando en el puerto 5000, exactamente igual que la versión de
Windows — no hay que cambiar nada del lado de Laravel
(`SolicitudController.php` sigue apuntando a `http://localhost:5000/convertir`).

## Cómo funciona (por si te preguntan)

1. Oculta (no borra) todas las hojas del Excel excepto "Formato", para que
   las fórmulas internas que jalan domicilio/CLUE/clave presupuestal desde
   otras hojas del libro sigan funcionando.
2. Convierte el archivo completo con `soffice --headless --convert-to pdf`.
3. Como esa conversión exporta todas las hojas como páginas, se queda solo
   con la página 1 (que siempre es "Formato", por ser la primera hoja del
   libro) usando `qpdf`.
4. Limpia los archivos temporales al terminar.

## Para dejarlo corriendo solo (opcional, más adelante)

Cuando ya no quieras tener una terminal abierta a mano, se puede configurar
como servicio de systemd para que arranque solo con la VM. Avísame cuando
llegues a esa parte y lo armamos.
