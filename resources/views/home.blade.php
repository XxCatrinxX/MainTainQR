@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
    {{-- Los estilos personalizados ahora están centralizados en admin-custom.css gestionado por Vite --}}
@stop

@section('content_header')
    <h1>Vista General</h1>
    <p class="text-muted mb-0" style="font-size: 0.9rem;">Analíticas y progreso de tus órdenes de servicio.</p>
@stop

@section('content')

<div class="row">
    <!-- COLUMNA PRINCIPAL (ampliada) -->
    <div class="col-md-8">
        <!-- COMPANY INFO -->
        <div class="card mb-4" style="border: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center" style="padding-bottom: 0;">
                <h3 class="card-title" style="color: #111827; font-weight: 700;"><i class="fas fa-building text-primary mr-2"></i> Identidad Corporativa</h3>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="p-4" style="background-color: #f8fafc; border-radius: 12px; border-left: 4px solid #3b82f6; height: 100%;">
                            <h5 style="font-weight: 800; color: #1e3a8a; margin-bottom: 12px; letter-spacing: -0.02em;"><i class="fas fa-bullseye mr-2"></i> Misión</h5>
                            <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                                Proveer soluciones tecnológicas integrales ofreciendo servicio técnico de vanguardia para la reparación de equipos electrónicos, asegurando el óptimo rendimiento y extendiendo su vida útil; todo respaldado por la confianza, transparencia y trazabilidad en cada una de nuestras operaciones.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4" style="background-color: #f0fdf4; border-radius: 12px; border-left: 4px solid #10b981; height: 100%;">
                            <h5 style="font-weight: 800; color: #065f46; margin-bottom: 12px; letter-spacing: -0.02em;"><i class="fas fa-eye mr-2"></i> Visión</h5>
                            <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                                Convertirnos en el Service Center referente en excelencia, distinguiéndonos nacionalmente por metodologías ágiles, diagnósticos exactos y servicio al cliente estelar, construyendo permanentemente lazos de confiabilidad absolutos en el ecosistema de reparación tecnológica.
                            </p>
                        </div>
                    </div>
                </div>
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

        <!-- ACTION BUTTON -->
        <div class="mb-4">
            <a href="{{ route('ordenes.create_paso1') }}" class="btn btn-dark-modern btn-block shadow-sm py-3" style="font-size: 1.05rem;">
                <i class="fas fa-plus mr-2"></i> Nueva Orden de Servicio
            </a>
        </div>

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

        <!-- NOTAS RÁPIDAS -->
        <div class="card mb-4">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title m-0" style="font-weight: 600; color: #111827;"><i class="fas fa-sticky-note mr-2 text-warning"></i> Notas Personales</h5>
            </div>
            <div class="card-body p-3">
                <textarea class="form-control" rows="4" style="border-radius: 8px; border: 1px dashed #cbd5e1; background-color: #f8fafc; color: #4b5563; font-size: 0.9rem; resize: vertical;" placeholder="Escribe pendientes o recordatorios rápidos aquí..."></textarea>
                <div class="text-right mt-1"><small class="text-muted"><i class="fas fa-info-circle"></i> Volátil (No se guarda)</small></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    // Reloj en vivo
    setInterval(function() {
        const now = new Date();
        const opciones = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        document.getElementById('reloj-tiempo-real').innerText = now.toLocaleString('es-MX', opciones).toUpperCase();
    }, 1000);
</script>
@stop