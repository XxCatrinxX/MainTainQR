@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    <style>
        .small-box { border-radius: 12px; overflow: hidden; transition: transform 0.2s; }
        .small-box:hover { transform: translateY(-5px); }
        .card { border-radius: 12px; border: none; }
        .btn-dark-modern { background-color: #111827; color: white; border-radius: 8px; }
        .btn-dark-modern:hover { background-color: #374151; color: white; }
        .list-group-item { transition: 0.2s; border: none !important; margin-bottom: 2px; }
        .list-group-item:hover { background-color: #f3f4f6 !important; border-radius: 8px !important; }
    </style>
@stop

@section('content_header')
    <div class="px-2">
        <h1 class="m-0 text-dark" style="font-weight: 800;">Vista General</h1>
    </div>
@stop

@section('content')

{{-- TARJETAS KPI --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ $totalOrdenesHoy }}</h3>
                <p>Órdenes Hoy</p>
            </div>
            <div class="icon"><i class="fas fa-tools"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning shadow-sm">
            <div class="inner">
                <h3>{{ $pendientesEntrega }}</h3>
                <p>Por Entregar</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>${{ number_format($ingresosMes, 2) }}</h3>
                <p>Ingresos Mes</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger shadow-sm">
            <div class="inner">
                <h3>{{ $stockBajo }}</h3>
                <p>Stock Bajo</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="row">
    {{-- COLUMNA PRINCIPAL --}}
    <div class="col-md-8">
        {{-- TABLA DE ÓRDENES RECIENTES --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header border-0 bg-white py-3">
                <h3 class="card-title" style="font-weight: 700; color: #111827;">Últimos Movimientos</h3>
                <div class="card-tools">
                    <a href="{{ route('ordenes.index') }}" class="btn btn-sm btn-light">Ver todo</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-valign-middle">
                    <thead class="text-muted" style="font-size: 0.85rem; text-transform: uppercase;">
                        <tr>
                            <th class="px-4">Folio</th>
                            <th>Cliente</th>
                            <th>Equipo</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordenesRecientes as $orden)
                        <tr>
                            <td class="px-4"><strong>#{{ $orden->id }}</strong></td>
                            {{-- Operador ?-> para evitar error si el cliente es null --}}
                            <td>{{ $orden->clientes?->nombre ?? 'Sin cliente' }}</td>
                            <td>{{ $orden->equipo->tipo ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-pill p-2" style="background: #e5e7eb; color: #374151;">
                                    {{ ucfirst($orden->estado) }}
                                </span>
                            </td>
                            <td class="text-right px-4">
                                <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-default border-0">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No hay actividad reciente.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GRÁFICA SEMANAL --}}
        <div class="card shadow-sm">
            <div class="card-header border-0 bg-white">
                <h3 class="card-title" style="font-weight: 700;">Flujo de Órdenes (7 días)</h3>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" style="height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <!-- COLUMNA SECUNDARIA (SIDEBAR RIGHT) -->
    <div class="col-md-4">
        <!-- BIENVENIDA Y FECHA -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #111827, #374151); color: white; border: none !important;">
            <div class="card-body p-4 text-center">
                <h4 style="font-weight: 700; margin-bottom: 0.2rem;">Hola, {{ Auth::user()->nombre ?? 'Usuario' }}</h4>
                <p style="color: #9ca3af; font-size: 0.95rem; margin-bottom: 1rem;">
                    Rol de sesión: <span class="badge badge-light text-capitalize" style="color: #111827 !important; padding: 0.4em 0.8em; font-size: 0.85rem;">{{ Auth::user()->rol ?? 'Administrador' }}</span>
                </p>
                <div class="py-2" style="background: rgba(255,255,255,0.1); border-radius: 8px;">
                    <i class="far fa-clock mr-2"></i>
                    <span id="reloj-tiempo-real" style="font-weight: 600; font-family: monospace; font-size: 1.1rem;">{{ now()->format('d/m/Y h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- BOTÓN ACCIÓN --}}
        <a href="{{ route('ordenes.create_paso1') }}" class="btn btn-dark-modern btn-block shadow-sm py-3 mb-4">
            <i class="fas fa-plus-circle mr-2"></i> Nueva Orden de Servicio
        </a>

         <!-- MÓDULOS DEL SISTEMA -->
        <div class="card mb-4">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title m-0" style="font-weight: 600; color: #111827;"><i class="fas fa-th-large mr-2 text-muted"></i> Accesos Rápidos</h5>
            </div>
            <div class="card-body p-2 mt-2">
                <div class="list-group list-group-flush">
                    <a href="{{ route('ordenes.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-clipboard-list text-primary mr-3" style="width:20px; text-align: center;"></i> Base de Órdenes</a>
                    <a href="{{ route('clientes.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-users text-info mr-3" style="width:20px; text-align: center;"></i> Consulta de Clientes</a>
                    <a href="{{ route('equipos.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-desktop text-secondary mr-3" style="width:20px; text-align: center;"></i> Archivo de Equipos</a>
                    <a href="{{ route('inventario.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-boxes text-warning mr-3" style="width:20px; text-align: center;"></i> Stock de Repuestos</a>
                    <a href="{{ route('solicitudes.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-boxes text-warning mr-3" style="width:20px; text-align: center;"></i> Solicitudes de Repuestos</a>
                    <a href="{{ route('pagos.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-wallet text-success mr-3" style="width:20px; text-align: center;"></i> Flujo de Caja y Pagos</a>
                </div>
            </div>
        </div>


        {{-- NOTAS PERSISTENTES --}}
        <div class="card shadow-sm">
            <div class="card-header border-0 pb-0 bg-white">
                <h5 class="card-title" style="font-weight: 600;">Notas Personales</h5>
            </div>
            <div class="card-body p-3">
                <textarea id="dashNotes" class="form-control" rows="4" 
                    style="border-radius: 8px; border: 1px dashed #cbd5e1; background-color: #f8fafc;" 
                    placeholder="Pendientes rápidos..."></textarea>
                <p class="text-right text-muted mt-2" style="font-size: 0.75rem;"><i class="fas fa-info-circle"></i> Solo visible en este navegador.</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Reloj
    setInterval(() => {
        document.getElementById('reloj-tiempo-real').innerText = new Date().toLocaleString('es-MX', { 
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true 
        });
    }, 1000);

    // Notas (LocalStorage)
    const noteBox = document.getElementById('dashNotes');
    noteBox.value = localStorage.getItem('user_dash_notes') || '';
    noteBox.addEventListener('input', () => {
        localStorage.setItem('user_dash_notes', noteBox.value);
    });

    // Gráfica con datos reales de PHP
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($labelsGrafica) !!},
            datasets: [{
                label: 'Órdenes Creadas',
                data: {!! json_encode($datosGrafica) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
@stop