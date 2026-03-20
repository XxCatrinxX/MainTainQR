<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Orden - {{ $orden->folio }}</title>
    <!-- Usamos Bootstrap 4 para consistencia, pero con mucho custom CSS SaaS -->
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
        
        /* Colores de estado */
        .status-recibido { background: #E0F2FE; color: #0369A1; }
        .status-diagnostico { background: #FEF3C7; color: #B45309; }
        .status-espera { background: #FEE2E2; color: #B91C1C; } /* Espera = Presupuestado (Requiere acción) */
        .status-reparacion { background: #EDE9FE; color: #5B21B6; }
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
    </style>
</head>
<body>

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
                @php
                    $estados = [
                        'recibido' => ['label' => 'Recibido', 'class' => 'status-recibido'],
                        'diagnostico' => ['label' => 'Revisión Técnica', 'class' => 'status-diagnostico'],
                        'espera' => ['label' => 'En Espera (Presupuesto)', 'class' => 'status-espera'],
                        'reparacion' => ['label' => 'En Reparación', 'class' => 'status-reparacion'],
                        'listo' => ['label' => 'Listo para recoger', 'class' => 'status-listo'],
                        'entregado' => ['label' => 'Entregado', 'class' => 'status-entregado'],
                    ];
                    $estadoActual = $estados[$orden->estado] ?? ['label' => strtoupper($orden->estado), 'class' => 'status-entregado'];
                @endphp
                <span class="badge-pill-modern {{ $estadoActual['class'] }}">
                    {{ $estadoActual['label'] }}
                </span>
            </div>
        </div>
        
        <div class="card-body p-4">
            
            <h5 class="mb-4" style="font-weight: 600;">Línea de Tiempo</h5>
            
            <ul class="timeline">
                @php
                    $levels = ['recibido', 'diagnostico', 'espera', 'reparacion', 'listo', 'entregado'];
                    $currentIndex = array_search($orden->estado, $levels);
                    if ($currentIndex === false) $currentIndex = 0;
                @endphp

                {{-- RECEPCION --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 0 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>Equipo Ingresado</h5>
                        <p>Recibido el {{ \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d M Y, h:i A') }}</p>
                        <p class="mt-1" style="font-size: 0.85rem;"><em>Falla reportada:</em> {{ $orden->falla_reportada }}</p>
                    </div>
                </li>

                {{-- DIAGNOSTICO --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 1 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>Diagnóstico Técnico</h5>
                        @if($currentIndex >= 1)
                            <p>El técnico está evaluando el equipo y determinando fallas.</p>
                            @if($orden->solucion_propuesta)
                                <div class="mt-2 p-3" style="background: #f8fafc; border-radius: 8px;">
                                    <strong>Solución propuesta:</strong><br>
                                    {{ $orden->solucion_propuesta }}
                                </div>
                            @endif
                        @else
                            <p>Aún pendiente de revisión.</p>
                        @endif
                    </div>
                </li>

                {{-- ESPERA / PRESUPUESTO --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 2 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>Aprobación de Presupuesto</h5>
                        @if($orden->estado === 'espera')
                            <p class="text-danger font-weight-bold">Requiere tu aprobación para proceder.</p>
                        @elseif($currentIndex > 2)
                            <p>Presupuesto aceptado por el cliente.</p>
                        @else
                            <p>Cotización en elaboración.</p>
                        @endif
                    </div>
                </li>

                {{-- REPARACION --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 3 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>En Reparación</h5>
                        <p>{{ $currentIndex >= 3 ? 'Nuestros técnicos están trabajando en tu equipo.' : 'Pendiente.' }}</p>
                    </div>
                </li>

                {{-- LISTO --}}
                <li class="timeline-item">
                    <div class="timeline-icon {{ $currentIndex >= 4 ? 'active' : '' }}"></div>
                    <div class="timeline-content">
                        <h5>Listo para Entrega</h5>
                        <p>{{ $currentIndex >= 4 ? 'Tu equipo ha sido reparado y está listo en sucursal.' : 'Pendiente.' }}</p>
                    </div>
                </li>
            </ul>

            {{-- ACCION REQUERIDA: ACEPTAR PRESUPUESTO --}}
            @if($orden->estado === 'espera')
                <div class="action-box">
                    <h5 class="font-weight-bold text-dark mb-3">Presupuesto Listo</h5>
                    
                    @php
                        $subtotalRepuestos = 0;
                        if($orden->repuestos) {
                            $subtotalRepuestos = $orden->repuestos->sum(function($r) {
                                return $r->pivot->cantidad * $r->pivot->precio_fijado;
                            });
                        }
                        $total = $orden->mano_obra + $subtotalRepuestos;
                    @endphp
                    
                    <div class="d-flex justify-content-between mx-auto mb-3" style="max-width: 300px; text-align: left;">
                        <span>Mano de Obra:</span>
                        <strong>${{ number_format($orden->mano_obra, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mx-auto mb-3" style="max-width: 300px; text-align: left;">
                        <span>Repuestos/Piezas:</span>
                        <strong>${{ number_format($subtotalRepuestos, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mx-auto mb-4 border-top pt-2" style="max-width: 300px; text-align: left;">
                        <span style="font-size: 1.1rem;">Total Estimado:</span>
                        <strong style="font-size: 1.2rem; color: #111827;">${{ number_format($total, 2) }}</strong>
                    </div>

                    <form action="{{ route('seguimiento.aceptar', $orden->token_rastreo) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-modern btn-black w-100" style="max-width: 300px;">
                            <i class="fas fa-check-circle mr-1"></i> Autorizar Reparación
                        </button>
                    </form>
                    <p class="text-muted small mt-3">Al autorizar, confirmas tu conformidad con el total estimado.</p>
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
