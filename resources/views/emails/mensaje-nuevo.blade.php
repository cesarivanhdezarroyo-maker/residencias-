<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Aviso del Departamento de Sistemas</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family: Arial, Helvetica, sans-serif; color:#374151;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:6px; overflow:hidden;">
                    <tr>
                        <td style="background:#621132; padding:18px 24px; color:#ffffff; font-size:18px; font-weight:bold;">
                            Cuentas TI - ISSSTE C.H. Xalapa
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px 0; font-size:14px;">
                                Hola {{ $mensaje->usuario->name ?? '' }}, el Departamento de Sistemas te envió un aviso:
                            </p>

                            <p style="margin:0 0 8px 0; font-size:16px; font-weight:bold; color:#621132;">
                                {{ $mensaje->asunto }}
                            </p>

                            <p style="margin:0 0 16px 0; font-size:14px; line-height:1.5;">
                                {!! nl2br(e($mensaje->cuerpo)) !!}
                            </p>

                            @if($mensaje->requiere_confirmacion)
                                <p style="margin:0 0 16px 0; font-size:14px; color:#b91c1c; font-weight:bold;">
                                    Este aviso requiere tu confirmación. Entra a Cuentas TI, abre "Avisos" y presiona "Estoy enterado".
                                </p>
                            @endif

                            <p style="margin:0; font-size:13px;">
                                Puedes consultar este aviso en cualquier momento dentro del sistema:
                                <a href="{{ route('mensajes.index') }}" style="color:#621132;">{{ route('mensajes.index') }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f9fafb; padding:12px 24px; font-size:11px; color:#6b7280;">
                            Mensaje automático. No respondas a este correo: escribe a Sistemas desde el apartado "Avisos" del sistema.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
