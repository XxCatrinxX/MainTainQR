<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ $esReparable ? 'Diagnóstico listo' : 'Propuesta por equipo no reparable' }} — {{ $orden->folio }}
    </title>
</head>
<body style="margin:0; padding:0; background-color:#f9fafb; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f9fafb; padding:2rem 1rem;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; width:100%;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background:linear-gradient(135deg, #111827, #1f2937); border-radius:12px 12px 0 0; padding:2rem; text-align:center;">
                            <h1 style="color:#ffffff; margin:0; font-size:1.5rem; font-weight:800; letter-spacing:-0.02em;">MaintainQR</h1>
                            <p style="color:#9ca3af; margin:0.4rem 0 0; font-size:0.875rem;">Servicio Técnico Profesional</p>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="background:#ffffff; padding:2rem 2.5rem;">

                            <p style="color:#374151; font-size:1rem; margin:0 0 1rem 0;">
                                Hola, <strong>{{ $cliente->nombre }} {{ $cliente->apellido_paterno ?? '' }}</strong> 👋
                            </p>

                            @if($esReparable)
                                <p style="color:#6b7280; font-size:0.95rem; line-height:1.6; margin:0 0 1.5rem 0;">
                                    Tu equipo <strong style="color:#111827;">{{ $equipo->marca }} {{ $equipo->modelo ?? '' }}</strong>
                                    (Orden <code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:0.85rem;">{{ $orden->folio }}</code>)
                                    ha sido diagnosticado. A continuación encontrarás el resumen de la reparación propuesta:
                                </p>
                            @else
                                <p style="color:#6b7280; font-size:0.95rem; line-height:1.6; margin:0 0 1.5rem 0;">
                                    Tu equipo <strong style="color:#111827;">{{ $equipo->marca }} {{ $equipo->modelo ?? '' }}</strong>
                                    (Orden <code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:0.85rem;">{{ $orden->folio }}</code>)
                                    fue revisado por nuestro equipo técnico y el diagnóstico indica que <strong style="color:#b91c1c;">no es reparable</strong>.
                                    A continuación te compartimos el motivo{{ $orden->ofrecer_compra ? ' y una propuesta por el equipo para uso en piezas' : '' }}.
                                </p>
                            @endif

                            {{-- RESULTADO --}}
                            <div style="background:{{ $esReparable ? '#ecfdf5' : '#fef2f2' }}; border-left:4px solid {{ $esReparable ? '#10b981' : '#ef4444' }}; border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.25rem;">
                                <p style="margin:0; font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:{{ $esReparable ? '#047857' : '#b91c1c' }};">
                                    Resultado del diagnóstico
                                </p>
                                <p style="margin:0.45rem 0 0; font-size:1rem; font-weight:700; color:#111827;">
                                    {{ $esReparable ? 'Equipo reparable' : 'Equipo no reparable' }}
                                </p>
                            </div>

                            {{-- DIAGNOSTICO --}}
                            <div style="background:#f8fafc; border-left:4px solid #3b82f6; border-radius:8px; padding:1.25rem 1.5rem; margin-bottom:1.5rem;">
                                <p style="color:#1e3a8a; font-weight:700; font-size:0.875rem; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 0.5rem 0;">
                                    Comentario del técnico
                                </p>
                                <p style="color:#374151; font-size:0.95rem; line-height:1.6; margin:0;">
                                    {{ $orden->solucion_propuesta ?? 'Sin descripción.' }}
                                </p>
                            </div>

                            {{-- COSTO / OFERTA --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-bottom:1.5rem;">
                                <tr style="background:#f9fafb;">
                                    <td style="padding:0.75rem 1rem; color:#6b7280; font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">
                                        {{ $esReparable ? 'Concepto' : 'Resumen' }}
                                    </td>
                                    <td style="padding:0.75rem 1rem; color:#6b7280; font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; text-align:right;">
                                        {{ $esReparable ? 'Costo' : 'Monto' }}
                                    </td>
                                </tr>

                                @if($esReparable)
                                    <tr>
                                        <td style="padding:0.75rem 1rem; color:#374151; border-top:1px solid #f3f4f6;">Mano de Obra</td>
                                        <td style="padding:0.75rem 1rem; color:#374151; border-top:1px solid #f3f4f6; text-align:right; font-weight:600;">
                                            ${{ $manoObra }}
                                        </td>
                                    </tr>

                                    @if($totalPiezas != '0.00')
                                    <tr>
                                        <td style="padding:0.75rem 1rem; color:#374151; border-top:1px solid #f3f4f6;">Repuestos / Materiales</td>
                                        <td style="padding:0.75rem 1rem; color:#374151; border-top:1px solid #f3f4f6; text-align:right; font-weight:600;">
                                            ${{ $totalPiezas }}
                                        </td>
                                    </tr>
                                    @endif

                                    <tr style="background:#111827;">
                                        <td style="padding:0.85rem 1rem; color:#ffffff; font-weight:700; font-size:1rem;">Total Estimado</td>
                                        <td style="padding:0.85rem 1rem; color:#ffffff; font-weight:800; font-size:1.1rem; text-align:right;">
                                            ${{ $total }}
                                        </td>
                                    </tr>
                                @else
                                    @if($orden->ofrecer_compra)
                                        <tr>
                                            <td style="padding:0.75rem 1rem; color:#374151; border-top:1px solid #f3f4f6;">Oferta por equipo para piezas</td>
                                            <td style="padding:0.75rem 1rem; color:#374151; border-top:1px solid #f3f4f6; text-align:right; font-weight:700;">
                                                ${{ $total }}
                                            </td>
                                        </tr>
                                        <tr style="background:#111827;">
                                            <td style="padding:0.85rem 1rem; color:#ffffff; font-weight:700; font-size:1rem;">Monto Ofrecido</td>
                                            <td style="padding:0.85rem 1rem; color:#ffffff; font-weight:800; font-size:1.1rem; text-align:right;">
                                                ${{ $total }}
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            </table>

                            {{-- EVIDENCIAS --}}
                            @if($orden->evidencias && $orden->evidencias->count() > 0)
                                <p style="color:#374151; font-weight:600; font-size:0.875rem; margin:0 0 0.75rem 0;">
                                    Evidencia fotográfica adjunta ({{ $orden->evidencias->count() }} foto{{ $orden->evidencias->count() > 1 ? 's' : '' }}):
                                </p>

                                <table cellpadding="4" cellspacing="0" role="presentation" style="margin-bottom:1.5rem;">
                                    <tr>
                                        @foreach($orden->evidencias->take(4) as $ev)
                                            <td>
                                                <a href="{{ asset('storage/' . $ev->url_foto) }}" style="display:block;">
                                                    <img src="{{ asset('storage/' . $ev->url_foto) }}"
                                                         width="110"
                                                         height="110"
                                                         style="border-radius:8px; object-fit:cover; border:1px solid #e5e7eb;"
                                                         alt="Evidencia">
                                                </a>
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            @endif

                            {{-- CTA --}}
                            @if($esReparable)
                                <p style="color:#374151; font-size:0.95rem; line-height:1.6; margin:0 0 1.5rem 0;">
                                    ¿Deseas autorizar la reparación de tu equipo? Por favor elige una opción:
                                </p>

                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:2rem;">
                                    <tr>
                                        <td style="padding-right:0.5rem;" width="50%">
                                            <a href="{{ $urlAceptar }}"
                                               style="display:block; text-align:center; background-color:#059669; color:#ffffff; padding:0.9rem; border-radius:10px; font-weight:700; font-size:1rem; text-decoration:none;">
                                                ✅ Sí, proceder con la reparación
                                            </a>
                                        </td>
                                        <td style="padding-left:0.5rem;" width="50%">
                                            <a href="{{ $urlRechazar }}"
                                               style="display:block; text-align:center; background-color:#ffffff; color:#dc2626; padding:0.9rem; border-radius:10px; font-weight:700; font-size:1rem; text-decoration:none; border:2px solid #dc2626;">
                                                ❌ No, declinar la reparación
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <p style="color:#374151; font-size:0.95rem; line-height:1.6; margin:0 0 1.5rem 0;">
                                    @if($orden->ofrecer_compra)
                                        Si estás de acuerdo con esta propuesta por tu equipo para uso en piezas, confirma tu decisión a continuación:
                                    @else
                                        Por favor pasa a nuestra sucursal con tu comprobante para la devolución de tu equipo. Lamentamos profundamente que no haya sido posible repararlo.
                                    @endif
                                </p>

                                @if($orden->ofrecer_compra)
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:2rem;">
                                    <tr>
                                        <td style="padding-right:0.5rem;" width="50%">
                                            <a href="{{ $urlAceptar }}"
                                               style="display:block; text-align:center; background-color:#059669; color:#ffffff; padding:0.9rem; border-radius:10px; font-weight:700; font-size:1rem; text-decoration:none;">
                                                ✅ Sí, aceptar oferta
                                            </a>
                                        </td>
                                        <td style="padding-left:0.5rem;" width="50%">
                                            <a href="{{ $urlRechazar }}"
                                               style="display:block; text-align:center; background-color:#ffffff; color:#dc2626; padding:0.9rem; border-radius:10px; font-weight:700; font-size:1rem; text-decoration:none; border:2px solid #dc2626;">
                                                ❌ No, rechazar oferta
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                @endif
                            @endif

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="background:#f3f4f6; border-radius:0 0 12px 12px; padding:1.25rem; text-align:center;">
                            <p style="color:#9ca3af; font-size:0.8rem; margin:0; line-height:1.5;">
                                Este correo fue enviado automáticamente por MaintainQR.<br>
                                No respondas directamente a este mensaje.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>