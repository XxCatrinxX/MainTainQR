<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Tu equipo está listo! — {{ $orden->folio }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f9fafb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f9fafb; padding: 2rem 1rem;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; width:100%;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #059669, #10b981); border-radius: 12px 12px 0 0; padding: 2rem; text-align: center;">
                            <h1 style="color:#ffffff; margin:0; font-size: 1.5rem; font-weight: 800;">MaintainQR</h1>
                            <p style="color:#ecfdf5; margin: 0.4rem 0 0; font-size: 0.875rem;">¡Tu reparación ha concluido!</p>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="background:#ffffff; padding: 2rem 2.5rem; border-radius: 0 0 12px 12px;">

                            <p style="color:#374151; font-size:1rem; margin: 0 0 1rem 0;">
                                Hola, <strong>{{ $cliente->nombre }}</strong> 👋
                            </p>

                            <p style="color:#6b7280; font-size:0.95rem; line-height:1.6; margin: 0 0 1.5rem 0;">
                                Nos complace informarte que la reparación de tu <strong>{{ $equipo->marca }} {{ $equipo->modelo }}</strong>
                                ha finalizado con éxito. El equipo ya se encuentra en estado <strong>Listo para Entrega</strong>.
                            </p>

                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:1.25rem; margin-bottom:1.5rem; text-align: center;">
                                <p style="color:#166534; font-weight:700; margin:0;">Ya puedes pasar a recogerlo a nuestra sucursal.</p>
                            </div>

                            <p style="color:#374151; font-weight:600; font-size:0.875rem; margin:0 0 0.5rem 0;">Resumen del Trabajo:</p>
                            <div style="background:#f8fafc; border-radius:8px; padding:1rem; margin-bottom:1.5rem; color:#4b5563; font-size:0.9rem; line-height:1.5;">
                                {{ $orden->detallesTecnicos->trabajo_finalizado ?? 'Reparación completada según diagnóstico.' }}
                            </div>

                            <div style="text-align: center; margin-bottom: 2rem;">
                                <a href="{{ $urlSeguimiento }}"
                                   style="display:inline-block; text-align:center; background-color:#059669; color:#ffffff; padding:0.9rem 2rem; border-radius:10px; font-weight:700; font-size:1rem; text-decoration:none;">
                                    🔍 Ver detalles de la orden
                                </a>
                            </div>

                            <p style="color:#6b7280; font-size:0.85rem; line-height:1.4; margin: 2rem 0 0 0; text-align: center;">
                                Gracias por confiar en <strong>MaintainQR</strong>.<br>
                                Si tienes dudas, contáctanos mencionando el folio <strong>{{ $orden->folio }}</strong>.
                            </p>

                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
