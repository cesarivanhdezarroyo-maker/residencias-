<x-emails.layout>
    <h2 style="color:#621132; margin-top:0; font-size:18px;">Tus credenciales de acceso fueron actualizadas</h2>

    <p style="color:#374151; font-size:14px; line-height:1.5;">Hola {{ $usuario->name }},</p>

    <p style="color:#374151; font-size:14px; line-height:1.5;">
        El área de Sistemas actualizó las credenciales con las que accedes al Portal de Servicios TI.
        A partir de ahora, usa exclusivamente los siguientes datos para iniciar sesión:
    </p>

    <table cellpadding="10" style="background:#f9fafb; border-radius:6px; width:100%; margin: 16px 0; border:1px solid #e5e7eb;">
        <tr>
            <td style="font-weight:bold; width:120px; font-size:14px; color:#111827;">Usuario:</td>
            <td style="font-family: monospace; font-size:14px; color:#111827;">{{ $usuarioNuevo }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; font-size:14px; color:#111827;">Contraseña:</td>
            <td style="font-family: monospace; font-size:14px; color:#111827;">{{ $passwordNueva }}</td>
        </tr>
    </table>

    <p style="color:#b91c1c; font-size:13px; line-height:1.5;">
        Tu matrícula (número de empleado) ya <strong>no</strong> funciona para iniciar sesión —
        a partir de este cambio, solo funcionan las credenciales de arriba.
    </p>

    <p style="color:#6b7280; font-size:12px; line-height:1.5;">
        Si tú no solicitaste este cambio, contacta de inmediato al área de Sistemas.
    </p>
</x-emails.layout>
