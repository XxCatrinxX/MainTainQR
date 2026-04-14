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

        .filtro-btn {
    border: none;
    transition: all 0.2s ease;
}

.filtro-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.filtro-btn.active {
    transform: scale(1.05);
    box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}


    </style>
@stop

@section('content_header')
    <div class="px-2">
        <h1 class="m-0 text-dark" style="font-weight: 800;">Vista General</h1>
    </div>
@stop

@section('content')



@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius:12px; border-left: 5px solid #dc3545; background-color: #fff; color: #1f2937;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle mr-3" style="font-size: 1.5rem; color: #dc3545;"></i>
            <div>
                <strong class="d-block">Acceso Denegado</strong>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" style="top: 10px;"><span>&times;</span></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" style="border-radius:12px; border-left: 5px solid #10b981; background-color: #fff; color: #1f2937;">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle mr-3" style="font-size: 1.5rem; color: #10b981;"></i>
            <div>
                <strong class="d-block">Éxito</strong>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" style="top: 10px;"><span>&times;</span></button>
    </div>
@endif

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
        {{-- PANEL DE URGENCIAS MEJORADO --}}

<div class="card shadow-sm mb-4">

<div class="card-header border-0 bg-white py-3 d-flex justify-content-between align-items-center">
    <h3 class="card-title" style="font-weight:700; color:#111827;">
        Panel de Urgencias
    </h3>

</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3 px-3" style="gap:10px; margin-top: 1rem;">

    <button class="btn filtro-btn active" data-filtro="todos"
        style="background:#f3f4f6; color:#374151; border-radius:20px; padding:6px 14px; font-weight:600;">
        Todas
    </button>

    <button class="btn filtro-btn" data-filtro="urgente"
        style="background:#fee2e2; color:#dc2626; border-radius:20px; padding:6px 14px; font-weight:600;">
        🔴 Urgentes
    </button>

    <button class="btn filtro-btn" data-filtro="atencion"
        style="background:#fef3c7; color:#d97706; border-radius:20px; padding:6px 14px; font-weight:600;">
        🟡 En atención
    </button>

    <button class="btn filtro-btn" data-filtro="tiempo"
        style="background:#dcfce7; color:#16a34a; border-radius:20px; padding:6px 14px; font-weight:600;">
        🟢 En tiempo
    </button>

</div>

<div class="card-body table-responsive p-0">
    <table class="table table-hover table-valign-middle">

        <thead class="text-muted" style="font-size:0.85rem; text-transform:uppercase;">
            <tr>
                <th class="px-4">Folio</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Tiempo</th>
                <th>Prioridad</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
           @forelse($ordenesRecientes as $orden)


@php
    $fecha = \Carbon\Carbon::parse($orden->created_at ?? now());
    $horas = $fecha->diffInHours(now());

    if($horas >= 72){
        $tipo = 'urgente';
        $color = '#dc2626';
        $bg = '#fee2e2';
        $label = 'URGENTE';
    } elseif($horas >= 24){
        $tipo = 'atencion';
        $color = '#d97706';
        $bg = '#fef3c7';
        $label = 'EN ATENCIÓN';
    } else {
        $tipo = 'tiempo';
        $color = '#16a34a';
        $bg = '#dcfce7';
        $label = 'EN TIEMPO';
    }
@endphp

<tr data-tipo="{{ $tipo }}" style="display:none; border-left:6px solid {{ $color }}; background: {{ $bg }};">
                
                {{-- Folio --}}
                <td class="px-4">
                    <strong>#{{ $orden->folio }}</strong>
                </td>

                {{-- Cliente --}}
                <td>
                    {{ $orden->cliente?->nombre ?? 'Sin cliente' }}
                </td>

                {{-- Estado --}}
                <td>
                    <span class="badge badge-pill p-2"
                        style="background:#e5e7eb; color:#374151; font-size:12px;">
                        {{ ucfirst($orden->estado) }}
                    </span>
                </td>

                {{-- Tiempo --}}
                <td>
                    <span style="font-weight:600; font-size:14px; color:#374151;">
                        {{ $fecha->diffForHumans() }}
                    </span>
                </td>

                {{-- PRIORIDAD GRANDE --}}
                <td>
                    <span class="badge badge-pill px-3 py-2"
                        style="
                            background: {{ $color }};
                            color:#fff;
                            font-weight:700;
                            font-size:12px;
                            letter-spacing:0.5px;
                            box-shadow:0 4px 10px {{ $color }}40;
                        ">
                         {{ $label }}
                    </span>
                </td>

                {{-- Acción --}}
                <td class="text-right px-4">
                    <a href="{{ route('ordenes.show', $orden->id) }}"
                       class="btn btn-sm btn-light border-0"
                       style="transition:0.2s;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </td>

            </tr>

            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    No hay órdenes registradas.
                </td>
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
                    <a href="{{ route('ordenes.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-list-alt text-primary mr-3" style="width:20px; text-align: center;"></i> Base de Órdenes</a>
                    <a href="{{ route('clientes.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-user-friends text-info mr-3" style="width:20px; text-align: center;"></i> Consulta de Clientes</a>
                    <a href="{{ route('equipos.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-laptop-medical text-secondary mr-3" style="width:20px; text-align: center;"></i> Archivo de Equipos</a>
                    <a href="{{ route('web.inventario.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-cubes text-warning mr-3" style="width:20px; text-align: center;"></i> Stock de Repuestos</a>
                    <a href="{{ route('solicitudes.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-shopping-cart text-danger mr-3" style="width:20px; text-align: center;"></i> Solicitudes de Repuestos</a>
                    <a href="{{ route('pagos.index') }}" class="list-group-item list-group-item-action border-0" style="border-radius: 8px; font-weight: 500; padding: 0.6rem 1rem;"><i class="fas fa-file-invoice-dollar text-success mr-3" style="width:20px; text-align: center;"></i> Flujo de Caja y Pagos</a>
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
<script>
document.addEventListener("DOMContentLoaded", function(){

    const botones = document.querySelectorAll(".filtro-btn");
    const filas = document.querySelectorAll("tbody tr");

    // 👇 OCULTAR TODO AL INICIO
    filas.forEach(fila => fila.style.display = "none");

    botones.forEach(btn => {
        btn.addEventListener("click", function(){

            botones.forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            const filtro = this.getAttribute("data-filtro");

            filas.forEach(fila => {
                const tipo = fila.getAttribute("data-tipo");

                if(filtro === "todos"){
                    fila.style.display = "";
                } else if(tipo === filtro){
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            });

        });
    });

});
</script>
@stop