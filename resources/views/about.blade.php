@extends('adminlte::page')

@section('title', 'Acerca de')

@section('content_header')
    <h1>Acerca de MaintainQR</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="bg-dark p-4 text-center text-white">
                    <img src="{{ asset('img/isotipo.png') }}" alt="MaintainQR Logo" style="width: 100px; height: auto;" class="mb-3">
                    <h2 class="mb-0 font-weight-bold">MaintainQR</h2>
                    <p class="mb-0 text-muted opacity-75">Sistema de Gestión de Órdenes de Servicio</p>
                </div>
                
                <div class="p-4">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <i class="fas fa-code-branch fa-2x mb-2 text-primary"></i>
                            <h6 class="font-weight-bold mb-1">Versión</h6>
                            <p class="text-muted small">1.0 (Producción)</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-building fa-2x mb-2 text-primary"></i>
                            <h6 class="font-weight-bold mb-1">Desarrollado por</h6>
                            <p class="text-muted small">CodeCraft</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-calendar-alt fa-2x mb-2 text-primary"></i>
                            <h6 class="font-weight-bold mb-1">Lanzamiento</h6>
                            <p class="text-muted small">Abril 2024</p>
                        </div>
                    </div>

                    <div class="border-top pt-4 text-center">
                        <p class="mb-2" style="color: #4b5563;">
                            <strong>MaintainQR</strong> es una herramienta integral diseñada para optimizar el flujo de trabajo en talleres de reparación y mantenimiento, facilitando el seguimiento de equipos a través de tecnología QR y notificaciones en tiempo real.
                        </p>
                        <hr class="my-4">
                        <p class="text-muted x-small mb-0">
                            © {{ date('Y') }} <strong>CodeCraft</strong>. Todos los derechos reservados.
                        </p>
                        <p class="text-muted x-small">
                            Diseñado con <i class="fas fa-heart text-danger"></i> para la excelencia operativa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .opacity-75 { opacity: 0.75; }
    .x-small { font-size: 0.8rem; }
    .card-body { background: #ffffff; }
</style>
@stop
