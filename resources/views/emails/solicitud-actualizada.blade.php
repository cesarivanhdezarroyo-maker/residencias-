<x-emails.layout>
    @php
        $esAprobado = $solicitud->estado_solicitud === 'Aprobado';
        $etiquetaCredencial = ($solicitud->servicio && $solicitud->servicio->nombre_servicio === 'Hardware / Equipo de Cómputo')
            ? 'Usuario'
            : 'Correo';
    @endphp
    
    <h2 style="color:{{ $esAprobado ? '#15803d' : '#b91c1c' }}; margin-top:0; font-size:18px;">
        Tu solicitud fue {{ strtolower($solicitud->estado_solicitud) }}
    </h2>

    <p style="color:#374151; font-size:14px; line-height:1.5;">Hola {{ $solicitud->usuario->name }},</p>

    <p style="color:#374151; font-size:14px; line-height:1.5;">
        Tu trámite de <strong>{{ $solicitud->tipo_solicitud }}</strong> —
        {{ $solicitud->servicio->nombre_servicio ?? 'Servicio' }}
        ({{ $solicitud->categoria->nombre_categoria ?? '' }}) fue marcado como
        <strong style="color:{{ $esAprobado ? '#15803d' : '#b91c1c' }};">{{ $solicitud->estado_solicitud }}</strong>
        por el área de Sistemas.
    </p>

    @if($esAprobado)
        @if($solicitud->credencial_usuario || $solicitud->credencial_password)
            <table cellpadding="10" style="background:#f9fafb; border-radius:6px; width:100%; margin: 16px 0; border:1px solid #e5e7eb;">
                @if($solicitud->credencial_usuario)
                    <tr>
                        <td style="font-weight:bold; width:120px; font-size:14px; color:#111827;">{{ $etiquetaCredencial }}:</td>
                        <td style="font-family: monospace; font-size:14px; color:#111827;">{{ $solicitud->credencial_usuario }}</td>
                    </tr>
                @endif
                @if($solicitud->credencial_password)
                    <tr>
                        <td style="font-weight:bold; font-size:14px; color:#111827;">Contraseña:</td>
                        <td style="font-family: monospace; font-size:14px; color:#111827;">{{ $solicitud->credencial_password }}</td>
                    </tr>
                @endif
            </table>
        @endif
        @if($solicitud->credencial_notas)
            <table cellpadding="10" style="background:#f9fafb; border-radius:6px; width:100%; margin: 16px 0; border:1px solid #e5e7eb;">
                <tr>
                    <td style="font-weight:bold; font-size:13px; color:#374151;">Nota:</td>
                </tr>
                <tr>
                    <td style="font-size:14px; color:#111827;">{{ $solicitud->credencial_notas }}</td>
                </tr>
            </table>
        @endif
    @else
        @if($solicitud->credencial_notas)
            <table cellpadding="10" style="background:#fef2f2; border-radius:6px; width:100%; margin: 16px 0; border:1px solid #fecaca;">
                <tr>
                    <td style="font-weight:bold; font-size:13px; color:#991b1b;">Motivo del rechazo:</td>
                </tr>
                <tr>
                    <td style="font-size:14px; color:#111827;">{{ $solicitud->credencial_notas }}</td>
                </tr>
            </table>
        @endif
        <p style="color:#374151; font-size:14px; line-height:1.5;">
            Si tienes dudas sobre el motivo, puedes acercarte al área de Sistemas de la Clínica Hospital ISSSTE Xalapa.
        </p>
    @endif
</x-emails.layout>
