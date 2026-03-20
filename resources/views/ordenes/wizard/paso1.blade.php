@extends('adminlte::page')

@section('title', 'Nueva Orden - Paso 1')

@section('css')
<style>
    body { background-color: #fafafa !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .content-wrapper { background-color: transparent !important; }
    .content-header h1 { font-weight: 700; font-size: 1.5rem; color: #111827; margin-bottom: 0.5rem; }
    
    .card {
        border: 1px solid #eaeaea !important; border-radius: 12px !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important;
    }
    .card-header { background-color: transparent !important; border-bottom: 1px solid #eaeaea !important; padding: 1.5rem !important; }
    .card-title { font-weight: 600 !important; color: #111827 !important; }
    
    .form-group label { font-weight: 500; color: #374151; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .form-control {
        border-radius: 8px !important; border: 1px solid #d1d5db !important;
        padding: 0.6rem 1rem !important; font-size: 0.95rem; background-color: #ffffff !important;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #000000 !important; box-shadow: 0 0 0 3px rgba(0,0,0,0.1) !important;
    }
    
    .btn { border-radius: 8px !important; font-weight: 500 !important; padding: 0.5rem 1.25rem; transition: all 0.2s ease; }
    .btn-dark-modern { background-color: #000000 !important; color: #ffffff !important; border: none !important; }
    .btn-dark-modern:hover { background-color: #333333 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; color: white !important; }
    .btn-light-modern { background-color: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; }
    .btn-light-modern:hover { background-color: #f9fafb !important; color: #111827 !important; }
</style>
@stop

@section('content_header')
    <h1>Registro de Orden <span class="text-muted font-weight-normal ml-2" style="font-size: 1.1rem;">Paso 1 de 3</span></h1>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información del Cliente</h3>
            </div>

            <form action="{{ route('ordenes.store_paso1') }}" method="POST">
                @csrf

                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger" style="border-radius: 8px;">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Nombre *</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $cliente->nombre ?? '') }}" placeholder="Ej. Juan">
                            @error('nombre') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label>Apellido Paterno *</label>
                            <input type="text" name="apellido_paterno" class="form-control @error('apellido_paterno') is-invalid @enderror" value="{{ old('apellido_paterno', $cliente->apellido_paterno ?? '') }}" placeholder="Ej. Pérez">
                            @error('apellido_paterno') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" value="{{ old('apellido_materno', $cliente->apellido_materno ?? '') }}" placeholder="Opcional">
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label>Teléfono *</label>
                            <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $cliente->telefono ?? '') }}" placeholder="Ej. 555-123-4567">
                            @error('telefono') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" value="{{ old('correo', $cliente->correo ?? '') }}" placeholder="correo@ejemplo.com">
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label>Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2" placeholder="Calle, Número, Colonia">{{ old('direccion', $cliente->direccion ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 text-right" style="border-radius: 0 0 12px 12px;">
                    <a href="{{ route('home') }}" class="btn btn-light-modern mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-dark-modern">Guardar y Continuar <i class="fas fa-arrow-right ml-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop
