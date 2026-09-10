<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Oficio de Solicitud - ISSSTE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            margin: 35px 45px;
            color: #000;
        }
        .header-table {
            width: 100%;
            margin-bottom: 18px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logos {
            width: 42%;
        }
        .emblema-col {
            width: 58%;
            text-align: right;
        }
        .emblema-col img {
            height: 90px;
            margin-bottom: 6px;
        }
        .dependencia {
            text-align: right;
            font-size: 10.5px;
            font-weight: bold;
            line-height: 1.3;
        }
        .fecha-asunto {
            text-align: right;
            font-size: 12.5px;
            margin-top: 8px;
        }
        .destinatario {
            margin-bottom: 22px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 13px;
            line-height: 1.35;
        }
        .cuerpo {
            text-align: justify;
            margin-bottom: 26px;
            line-height: 1.55;
        }
        .cuerpo p {
            text-indent: 40px;
        }
        .despedida {
            margin-bottom: 45px;
        }
        .firma {
            text-align: left;
            margin-top: 40px;
            font-size: 13px;
        }
        .firma .espacio-firma {
            margin-top: 140px;
            margin-bottom: 4px;
        }
        .firma .nombre {
            font-weight: bold;
        }
        .footer {
            width: 100%;
            margin-top: 70px;
        }
        .footer table {
            width: 100%;
        }
        .footer img {
            height: 42px;
        }
        .footer .linea-pie {
            border-top: 1px solid #000;
            width: 100%;
            display: block;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td class="logos">
                <table style="width: auto; border-collapse: collapse; margin-top: 26px;">
                    <tr>
                        <td style="padding: 0; vertical-align: middle;">
                            <img src="{{ public_path('images/logo-gob.png') }}" style="height: 38px; display: block;">
                        </td>
                        <td style="padding: 0 0 0 12px; vertical-align: middle;">
                            <img src="{{ public_path('images/logo-issste.png') }}" style="height: 38px; display: block;">
                        </td>
                    </tr>
                </table>
            </td>
            <td class="emblema-col">
                <img src="{{ public_path('images/logo-mujer.png') }}">
                <div class="dependencia">
                    DELEGACIÓN VERACRUZ<br>
                    CLÍNICA HOSPITAL ISSSTE XALAPA<br>
                    Subdirección Administrativa<br>
                    Coordinación de Recursos Materiales
                </div>
                <div class="fecha-asunto">
                    Xalapa de Enríquez, Ver, a {{ \Carbon\Carbon::parse($solicitud->created_at)->locale('es')->isoFormat('D \d\e MMMM \d\e Y') }}<br>
                    <strong>Asunto:</strong> Solicitud de {{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- DESTINATARIO -->
    @php $config = \App\Models\ConfiguracionOficio::actual(); @endphp
    <div class="destinatario">
        AT'N {{ $config->atn_nombre }}<br>
        {{ $config->atn_puesto }}<br>
        {{ $config->atn_lugar }}<br>
        <span style="letter-spacing: 2px;">PRESENTE</span>
    </div>

    <!-- CUERPO DEL OFICIO -->
    <div class="cuerpo">
        <p>
            Por medio de la presente le solicito de la manera más atenta se gestione el trámite de <strong>{{ strtolower($solicitud->tipo_solicitud) }}</strong>
            de <strong>{{ strtolower($solicitud->servicio->nombre_servicio ?? 'servicio') }}</strong>
            (Categoría: {{ $solicitud->categoria->nombre_categoria ?? 'N/A' }})
            del/la C. <strong>{{ $solicitud->usuario->nombres ?? Auth::user()->nombres }} {{ $solicitud->usuario->apellidos ?? Auth::user()->apellidos }}</strong>, quien ocupa el cargo de
            <strong>{{ $solicitud->usuario->puesto->nombre_puesto ?? Auth::user()->puesto->nombre_puesto }}</strong> de la Clínica Hospital ISSSTE Xalapa. Lo anterior con la siguiente justificación:
            <em>"{{ $solicitud->justificacion }}"</em>.
        </p>
    </div>

    <!-- DESPEDIDA -->
    <div class="despedida">
        <p>Sin otro particular por el momento, le envío un cordial saludo.</p>
    </div>

    <!-- FIRMA -->
    <div class="firma">
        Atentamente
        <div class="espacio-firma">&nbsp;</div>
        <div class="nombre">{{ $config->firma_nombre }}</div>
        {{ $config->firma_puesto }}<br>
        {{ $config->firma_lugar }}
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 30%; vertical-align: bottom;">
                    <img src="{{ public_path('images/logo-margarita.png') }}">
                </td>
                <td style="width: 70%; vertical-align: bottom;">
                    <div class="linea-pie"></div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
