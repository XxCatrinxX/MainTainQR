@extends('adminlte::page')

@section('title', 'Nueva Orden - Paso 2')

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <h1>Registro de Orden <span class="text-muted font-weight-normal ml-2" style="font-size: 1.1rem;">Paso 2 de 3</span></h1>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información del Equipo</h3>
            </div>

            <form action="{{ route('ordenes.store_paso2') }}" method="POST">
                @csrf

                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger" style="border-radius: 8px;">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Tipo de Equipo *</label>
                            <input type="text" name="tipo" class="form-control @error('tipo') is-invalid @enderror" value="{{ old('tipo', $equipo->tipo ?? '') }}" placeholder="Ej: Celular, Laptop, Consola">
                            @error('tipo') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label>Marca *</label>
                            <input type="text" name="marca" class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca', $equipo->marca ?? '') }}" placeholder="Ej: Samsung, HP">
                            @error('marca') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Modelo</label>
                            <input type="text" name="modelo" class="form-control" value="{{ old('modelo', $equipo->modelo ?? '') }}" placeholder="Ej: Galaxy S21">
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label>Número de Serie / IMEI</label>
                            <input type="text" name="numero_serie" class="form-control" value="{{ old('numero_serie', $equipo->numero_serie ?? '') }}" placeholder="Dejar vacío para autogenerar">
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 text-right" style="border-radius: 0 0 12px 12px;">
                    <a href="{{ route('ordenes.create_paso1') }}" class="btn btn-light-modern mr-2"><i class="fas fa-arrow-left mr-1"></i> Atrás</a>
                    <button type="submit" class="btn btn-dark-modern">Guardar y Continuar <i class="fas fa-arrow-right ml-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop
