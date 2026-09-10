<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 24px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#621132; padding:20px 24px;">
                            <img src="{{ asset('images/logo-issste.png') }}" alt="ISSSTE" height="30" style="display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px 24px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 24px; background:#f9fafb; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:11px; color:#9ca3af;">
                                Portal de Servicios TI &mdash; ISSSTE, Clínica Hospital Xalapa.<br>
                                Este es un correo automático, por favor no respondas a esta dirección.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
