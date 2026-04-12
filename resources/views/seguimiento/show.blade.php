<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Orden - {{ $orden->folio }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f7f9fc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #111827;
        }
        .tracker-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 1rem;
        }
        .card-modern {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #eaeaea;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025);
            overflow: hidden;
        }
        .card-header-modern {
            background: #ffffff;
            border-bottom: 1px solid #f3f4f6;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-pill-modern {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.025em;
        }

        .status-recibido { background: #E0F2FE; color: #0369A1; }
        .status-diagnostico { background: #FEF3C7; color: #B45309; }
        .status-espera { background: #FEE2E2; color: #B91C1C; }
        .status-reparacion { background: #EDE9FE; color: #5B21B6; }
        .status-para-pzas { background: #ECFCCB; color: #3F6212; }
        .status-listo { background: #DCFCE7; color: #15803D; }
        .status-entregado { background: #F3F4F6; color: #374151; }

        .timeline {
            position: relative;
            padding: 2rem 0;
            margin: 0;
            list-style: none;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 24px;
            width: 2px;
            background: #e5e7eb;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 60px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-icon {
            position: absolute;
            left: 14px;
            top: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #e5e7eb;
            z-index: 1;
        }
        .timeline-icon.active {
            background: #000000;
            border-color: #000000;
            box-shadow: 0 0 0 4px rgba(0,0,0,0.1);
        }
        .timeline-content h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .timeline-content p {
            color: #6b7280;
            font-size: 0.95rem;
            margin: 0;
        }
        .action-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }
        .btn-modern {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-black {
            background: #000000;
            color: #ffffff;
            border: none;
        }
        .btn-black:hover {
            background: #333333;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            color: white;
        }
        .result-box {
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-top: 0.75rem;
        }
        .result-ok {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }
        .result-bad {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

@php
    $esReparable = is_null($orden->es_reparable) ? true : (bool) $orden->es_reparable;

    $estados = [
        'recibido'   => ['label' => 'Recibido', 'class' => 'status-recibido'],
        'diagnostico'=> ['label' => 'Revisión Técnica', 'class' => 'status-diagnostico'],
        'espera'     => ['label' => $esReparable ? 'En Espera (Presupuesto)' : 'En Espera (Propuesta)', 'class' => 'status-espera'],
        'reparacion' => ['label' => 'En Reparación', 'class' => 'status-reparacion'],
        'para_pzas'  => ['label' => 'Aceptado para piezas', 'class' => 'status-para-pzas'],
        'listo'      => ['label' => 'Listo para recoger', 'class' => 'status-listo'],
        'entregado'  => ['label' => 'Entregado', 'class' => 'status-entregado'],
    ];

    $estadoActual = $estados[$orden->estado] ?? ['label' => strtoupper($orden->estado), 'class' => 'status-entregado'];

    if ($esReparable) {
        $levels = ['recibido', 'diagnostico', 'espera', 'reparacion', 'listo', 'entregado'];
    } else {
        $levels = ['recibido', 'diagnostico', 'espera', 'para_pzas', 'entregado'];
    }

    $currentIndex = array_search($orden->estado, $levels);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }

    $subtotalRepuestos = 0;
    if ($orden->repuestos) {
        $subtotalRepuestos = $orden->repuestos->sum(function ($r) {
            return $r->pivot->cantidad * $r->pivot->precio_fijado;
        });
    }

    $totalReparacion = ($orden->mano_obra ?? 0) + $subtotalRepuestos;
    $montoPiezas = $orden->monto_compra_piezas ?? 0;
@endphp

<div class="tracker-container">
    <div class="text-center mb-4">
        <h3 style="font-weight: 700; color: #111827;">Seguimiento de Servicio</h3>
        <p class="text-muted">Consulta el estado en tiempo real de tu equipo</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 8px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="border-radius: 8px;">{{ session('error') }}</div>
    @endif

    <div class="card-modern">
        <div class="card-header-modern">
            <div>
                <h4 class="mb-0" style="font-weight: 700;">{{ $orden->folio }}</h4>
                <div class="text-muted mt-1" style="font-size: 0.9rem;">
                    {{ $orden->equipo->marca }} {{ $orden->equipo->modelo }}
                </div>
            </div>
            <div>
                <span class="badge-pill-modern {{ $estadoActual['class'] }}">
                    {{ $estadoActual['label'] }}
                </span>
            </div>
        </div>

        <div class="card-body p-4">
            <h5 class="mb-4" style="font-weight: 600;">Línea de Tiempo</h5>

            <ul class="timeline">
                {{-- RECEPCION --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 0 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>Equipo Ingresado</h5>
                        <p>Recibido el {{ \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d M Y, h:i A') }}</p>
                        <p class="mt-1" style="font-size: 0.85rem;">
                            <em>Falla reportada:</em> {{ $orden->falla_reportada }}
                        </p>
                    </div>
                </li>

                {{-- DIAGNOSTICO --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 1 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>Diagnóstico Técnico</h5>
                        @if($currentIndex >= 1)
                            <p>Evaluado el {{ $orden->fecha_diagnostico ? \Carbon\Carbon::parse($orden->fecha_diagnostico)->format('d M Y, h:i A') : 'recientemente' }}</p>

                            <div class="result-box {{ $esReparable ? 'result-ok' : 'result-bad' }}">
                                <strong>Resultado:</strong> {{ $esReparable ? 'Equipo reparable' : 'Equipo no reparable' }}
                            </div>

                            @if($orden->solucion_propuesta)
                                <div class="mt-2 p-3" style="background: #f8fafc; border-radius: 8px;">
                                    <strong>{{ $esReparable ? 'Solución propuesta:' : 'Motivo del diagnóstico:' }}</strong><br>
                                    {{ $orden->solucion_propuesta }}
                                </div>
                            @endif
                        @else
                            <p>Aún pendiente de revisión.</p>
                        @endif
                    </div>
                </li>

                {{-- ESPERA / RESPUESTA --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 2 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>{{ $esReparable ? 'Aprobación de Presupuesto' : 'Respuesta a Propuesta por Piezas' }}</h5>

                        @if($orden->estado === 'espera')
                            <p class="text-danger font-weight-bold">
                                {{ $esReparable ? 'Requiere tu aprobación para proceder.' : 'Requiere tu respuesta para la propuesta por piezas.' }}
                            </p>
                        @elseif($orden->fecha_aprobacion)
                            <p>Respondido el {{ \Carbon\Carbon::parse($orden->fecha_aprobacion)->format('d M Y, h:i A') }}</p>
                        @else
                            <p>{{ $esReparable ? 'Cotización en elaboración.' : 'Propuesta en elaboración.' }}</p>
                        @endif
                    </div>
                </li>

                @if($esReparable)
                    {{-- REPARACION --}}
                    <li class="timeline-item">
                        <div class="timeline-icon {{ $currentIndex >= 3 ? 'active' : '' }}"></div>
                        <div class="timeline-content">
                            <h5>En Reparación</h5>
                            @if($currentIndex >= 3)
                                <p>Iniciado el {{ $orden->fecha_reparacion ? \Carbon\Carbon::parse($orden->fecha_reparacion)->format('d M Y, h:i A') : '' }}</p>
                                <p>Nuestros técnicos están trabajando en tu equipo.</p>
                            @else
                                <p>Pendiente.</p>
                            @endif
                        </div>
                    </li>

                    {{-- LISTO --}}
                    <li class="timeline-item">
                        <div class="timeline-icon {{ $currentIndex >= 4 ? 'active' : '' }}"></div>
                        <div class="timeline-content">
                            <h5>Listo para Entrega</h5>
                            @if($currentIndex >= 4)
                                <p>Finalizado el {{ $orden->fecha_listo ? \Carbon\Carbon::parse($orden->fecha_listo)->format('d M Y, h:i A') : '' }}</p>
                                <p>Tu equipo ha sido reparado y está listo en sucursal.</p>
                            @else
                                <p>Pendiente.</p>
                            @endif
                        </div>
                    </li>
                @else
                    {{-- PARA PIEZAS --}}
                    <li class="timeline-item">
                        <div class="timeline-icon {{ $currentIndex >= 3 ? 'active' : '' }}"></div>
                        <div class="timeline-content">
                            <h5>Proceso para Piezas</h5>
                            @if($orden->estado === 'para_pzas')
                                <p>Has aceptado la propuesta por tu equipo para uso en piezas.</p>
                            @elseif($currentIndex > 3)
                                <p>La propuesta fue procesada y el equipo siguió su flujo de salida.</p>
                            @else
                                <p>Pendiente de tu respuesta.</p>
                            @endif
                        </div>
                    </li>
                @endif
            </ul>

            {{-- ACCION REQUERIDA --}}
            @if($orden->estado === 'espera')
                <div class="action-box">
                    @if($esReparable)
                        <h5 class="font-weight-bold text-dark mb-3">Presupuesto Listo</h5>

                        <div class="mb-4 text-left mx-auto" style="max-width: 400px;">
                            <span class="text-muted small font-weight-bold text-uppercase">Desglose de Presupuesto:</span>
                            <table class="table table-sm mt-2 mb-0" style="font-size: 0.9rem;">
                                <tbody>
                                    <tr>
                                        <td>Mano de Obra</td>
                                        <td class="text-right">${{ number_format($orden->mano_obra, 2) }}</td>
                                    </tr>
                                    @foreach($orden->repuestos as $repuesto)
                                        <tr>
                                            <td>{{ $repuesto->nombre_pieza }} (x{{ $repuesto->pivot->cantidad }})</td>
                                            <td class="text-right">${{ number_format($repuesto->pivot->cantidad * $repuesto->pivot->precio_fijado, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between mx-auto mb-4 border-top pt-2" style="max-width: 300px; text-align: left;">
                            <span style="font-size: 1.1rem;">Total Estimado:</span>
                            <strong style="font-size: 1.2rem; color: #111827;">${{ number_format($totalReparacion, 2) }}</strong>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-2">
                                <form action="{{ route('seguimiento.aceptar', $orden->token_rastreo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-modern btn-black w-100">
                                        <i class="fas fa-check-circle mr-1"></i> Autorizar Reparación
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('seguimiento.rechazar', $orden->token_rastreo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-modern btn-outline-danger w-100" onclick="return confirm('¿Estás seguro de que deseas rechazar la reparación?')">
                                        <i class="fas fa-times-circle mr-1"></i> Rechazar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <p class="text-muted small mt-3">
                            Al autorizar, confirmas tu conformidad con el total estimado.
                        </p>
                    @else
                        <h5 class="font-weight-bold text-dark mb-3">Propuesta por Equipo No Reparable</h5>

                        <div class="mb-3 mx-auto p-3 text-left" style="max-width: 500px; background:#fff; border:1px solid #e5e7eb; border-radius:10px;">
                            <p class="mb-2"><strong>Motivo del diagnóstico:</strong></p>
                            <p class="text-muted mb-0">{{ $orden->solucion_propuesta }}</p>
                        </div>

                        <div class="d-flex justify-content-between mx-auto mb-4 border-top pt-3" style="max-width: 340px; text-align: left;">
                            <span style="font-size: 1.1rem;">Monto que te ofrecemos:</span>
                            <strong style="font-size: 1.2rem; color: #111827;">${{ number_format($montoPiezas, 2) }}</strong>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-2">
                                <form action="{{ route('seguimiento.aceptar', $orden->token_rastreo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-modern btn-black w-100">
                                        <i class="fas fa-check-circle mr-1"></i> Aceptar Oferta
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('seguimiento.rechazar', $orden->token_rastreo) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-modern btn-outline-danger w-100" onclick="return confirm('¿Estás seguro de que deseas rechazar esta oferta por tu equipo?')">
                                        <i class="fas fa-times-circle mr-1"></i> No Aceptar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <p class="text-muted small mt-3">
                            Si aceptas, registraremos tu equipo para el flujo de piezas. Si no aceptas, podrás recuperarlo en sucursal.
                        </p>
                    @endif
                </div>
            @endif

        </div>
    </div>

    <div class="text-center mt-4">
        <p class="text-muted small">Powered by MainTainQR Service Center</p>
    </div>
</div>

</body>
</html>