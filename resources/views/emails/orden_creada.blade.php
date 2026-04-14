<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de servicio registrada — {{ $orden->folio }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f9fafb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f9fafb; padding: 2rem 1rem;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; width:100%;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #111827, #374151); border-radius: 12px 12px 0 0; padding: 2rem; text-align: center;">
                            <h1 style="color:#ffffff; margin:0; font-size: 1.5rem; font-weight: 800;">MaintainQR</h1>
                            <p style="color:#9ca3af; margin: 0.4rem 0 0; font-size: 0.875rem;">Servicio Técnico Profesional</p>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="background:#ffffff; padding: 2rem 2.5rem; border-radius: 0 0 12px 12px;">

                            <p style="color:#374151; font-size:1rem; margin: 0 0 1rem 0;">
                                Hola, <strong>{{ $orden->equipo->cliente->nombre ?? 'Cliente' }}</strong> 👋
                            </p>

                            <p style="color:#6b7280; font-size:0.95rem; line-height:1.6; margin: 0 0 1.5rem 0;">
                                Tu equipo ha sido registrado correctamente en nuestro sistema. Estamos listos para comenzar con la revisión.
                            </p>

                            {{-- RESUMEN --}}
                            <div style="background:#f8fafc; border-left:4px solid #3b82f6; border-radius:8px; padding:1.25rem; margin-bottom:1.5rem;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 0.5rem; color:#6b7280; font-size:0.85rem;">Folio de Servicio:</td>
                                        <td style="padding-bottom: 0.5rem; color:#111827; font-size:0.85rem; font-weight:700; text-align:right;">{{ $orden->folio }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom: 0.5rem; color:#6b7280; font-size:0.85rem;">Equipo:</td>
                                        <td style="padding-bottom: 0.5rem; color:#111827; font-size:0.85rem; font-weight:600; text-align:right;">{{ $orden->equipo->marca }} {{ $orden->equipo->modelo }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom: 0.5rem; color:#6b7280; font-size:0.85rem;">Técnico Asignado:</td>
                                        <td style="padding-bottom: 0.5rem; color:#111827; font-size:0.85rem; font-weight:600; text-align:right;">{{ $orden->user->nombre ?? 'Por asignar' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#6b7280; font-size:0.85rem;">Estado Inicial:</td>
                                        <td style="color:#16a34a; font-size:0.85rem; font-weight:700; text-align:right; text-transform:uppercase;">{{ $orden->estado }}</td>
                                    </tr>
                                </table>
                            </div>

                            <p style="color:#374151; font-size:0.95rem; line-height:1.6; margin: 0 0 1.5rem 0;">
                                En breve iniciaremos con el diagnóstico. Puedes seguir el progreso de tu reparación en tiempo real haciendo clic en el siguiente botón:
                            </p>

                            {{-- CTA --}}
                            <div style="text-align: center; margin: 2rem 0;">
                                <a href="{{ url('/seguimiento/' . $orden->token_rastreo) }}"
                                   style="display:inline-block; text-align:center; background-color:#3b82f6; color:#ffffff; padding:0.9rem 2rem; border-radius:10px; font-weight:700; font-size:1rem; text-decoration:none; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
                                    🔍 Rastrear mi equipo
                                </a>
                            </div>

                            <p style="color:#6b7280; font-size:0.85rem; line-height:1.4; margin: 2rem 0 0 0; text-align: center;">
                                Gracias por confiar en <strong>MaintainQR</strong> 🙌<br>
                                Recibirás una notificación en cuanto el diagnóstico esté listo.
                            </p>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="padding: 1.5rem; text-align: center;">
                            <p style="color:#9ca3af; font-size:0.75rem; margin:0;">
                                Este es un mensaje automático, por favor no respondas a este correo.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
