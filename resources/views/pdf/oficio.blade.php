<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Oficio de Solicitud - ISSSTE</title>
    <style>
        /* Hoja carta, como el oficio institucional impreso */
        @page {
            size: letter;
            margin: 40px 48px 132px 48px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.45;
            margin: 0;
            color: #000;
        }
        /* Texto del oficio: más adentro que los logotipos, como en el formato impreso */
        .contenido {
            margin: 0 72px 0 64px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .logo-gob {
            height: 56px;
            display: block;
        }
        .separador {
            border-left: 1px solid #555;
            height: 40px;
            margin: 0 10px;
        }
        .logo-issste {
            height: 35px;
            display: block;
        }
        .emblema {
            height: 118px;
        }
        .dependencia {
            text-align: right;
            margin-top: 22px;
            line-height: 1.35;
        }
        .destinatario {
            margin-top: 26px;
            line-height: 1.35;
        }
        .presente {
            font-weight: bold;
            letter-spacing: 3px;
        }
        .cuerpo {
            text-align: justify;
            margin-top: 48px;
            line-height: 1.55;
        }
        .cuerpo p {
            text-indent: 46px;
            margin: 0;
        }
        .despedida {
            margin-top: 40px;
            text-indent: 46px;
        }
        .firma {
            margin-top: 48px;
            line-height: 1.35;
        }
        .firma .espacio-firma {
            height: 64px;
        }
        /* Pie fijo al final de la hoja */
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -88px;
        }
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer td {
            padding: 0;
        }
        .footer img {
            height: 78px;
            display: block;
            margin-left: 6px;
        }
        .footer .linea-pie {
            border-top: 5px solid #707070;
            margin: 0 13px 38px 38px;
        }
    </style>
</head>
<body>

    <!-- PIE DE PÁGINA (fijo; se declara primero para que DomPDF lo coloque en la hoja) -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 160px; vertical-align: bottom;">
                    <img src="{{ public_path('images/logo-margarita.png') }}">
                </td>
                <td style="vertical-align: bottom;">
                    <div class="linea-pie"></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <table style="border-collapse: collapse; margin-top: 14px;">
                    <tr>
                        <td style="padding: 0; vertical-align: middle;">
                            <img class="logo-gob" src="{{ public_path('images/logo-gob.png') }}">
                        </td>
                        <td style="padding: 0; vertical-align: middle;">
                            <div class="separador"></div>
                        </td>
                        <td style="padding: 0; vertical-align: middle;">
                            <img class="logo-issste" src="{{ public_path('images/logo-issste-horizontal.png') }}">
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; text-align: right;">
                <img class="emblema" src="{{ public_path('images/logo-mujer.png') }}">
            </td>
        </tr>
    </table>

    @php $config = \App\Models\ConfiguracionOficio::actual(); @endphp

    <div class="contenido">

        <div class="dependencia">
            DELEGACIÓN VERACRUZ<br>
            CLÍNICA HOSPITAL ISSSTE XALAPA<br>
            Subdirección Administrativa<br>
            Coordinación de Recursos Materiales<br>
            Xalapa de Enríquez, Ver, {{ \Carbon\Carbon::parse($solicitud->created_at)->locale('es')->isoFormat('D \d\e MMMM \d\e Y') }}<br>
            <strong>Asunto: Solicitud de {{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}</strong>
        </div>

        <!-- DESTINATARIO -->
        <div class="destinatario">
            <strong>AT'N {{ $config->atn_nombre }}</strong><br>
            {{ $config->atn_puesto }}<br>
            {{ $config->atn_lugar }}<br>
            <span class="presente">PRESENTE</span>
        </div>

        <!-- CUERPO DEL OFICIO -->
        <div class="cuerpo">
            <p>Por medio de la presente le solicito de la manera más atenta se gestione el trámite de <strong>{{ strtolower($solicitud->tipo_solicitud) }}</strong> de <strong>{{ strtolower($solicitud->servicio->nombre_servicio ?? 'servicio') }}</strong> (Categoría: {{ $solicitud->categoria->nombre_categoria ?? 'N/A' }}) del/la C. <strong>{{ $solicitud->usuario->nombres ?? Auth::user()->nombres }} {{ $solicitud->usuario->apellidos ?? Auth::user()->apellidos }}</strong>, quien ocupa el cargo de <strong>{{ $solicitud->usuario->puesto->nombre_puesto ?? Auth::user()->puesto->nombre_puesto }}</strong> de la Clínica Hospital ISSSTE Xalapa. Lo anterior con la siguiente justificación: <em>"{{ $solicitud->justificacion }}"</em>.</p>
        </div>

        <!-- DESPEDIDA -->
        <div class="despedida">Sin otro particular por el momento, le envío un cordial saludo.</div>

        <!-- FIRMA -->
        <div class="firma">
            <strong>Atentamente</strong>
            <div class="espacio-firma">&nbsp;</div>
            <strong>{{ $config->firma_nombre }}</strong><br>
            {{ $config->firma_puesto }}<br>
            {{ $config->firma_lugar }}
        </div>

    </div>

</body>
</html>
