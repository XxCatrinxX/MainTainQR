@extends('adminlte::page')

@section('title', 'Nueva Orden - Resumen Final')

@section('css')
<style>
    body { background-color: #fafafa !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .content-wrapper { background-color: transparent !important; }
    .content-header h1 { font-weight: 700; font-size: 1.5rem; color: #111827; margin-bottom: 0.5rem; }
    
    .card { border: 1px solid #eaeaea !important; border-radius: 12px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; margin-bottom: 1.5rem; }
    .card-header { background-color: transparent !important; border-bottom: 1px solid #eaeaea !important; padding: 1.25rem 1.5rem !important; }
    .card-title { font-weight: 600 !important; color: #111827 !important; }
    
    .list-group-item { border: none !important; border-bottom: 1px solid #f3f4f6 !important; padding: 1rem 0 !important; font-size: 0.95rem; }
    .list-group-item:last-child { border-bottom: none !important; }
    
    .form-group label { font-weight: 500; color: #374151; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .form-control { border-radius: 8px !important; border: 1px solid #d1d5db !important; padding: 0.6rem 1rem !important; font-size: 0.95rem; background-color: #ffffff !important; transition: all 0.2s; }
    .form-control:focus { border-color: #000000 !important; box-shadow: 0 0 0 3px rgba(0,0,0,0.1) !important; }
    
    .btn { border-radius: 8px !important; font-weight: 500 !important; padding: 0.5rem 1.25rem; transition: all 0.2s ease; }
    .btn-dark-modern { background-color: #000000 !important; color: #ffffff !important; border: none !important; }
    .btn-dark-modern:hover { background-color: #333333 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; color: white !important; }
    .btn-light-modern { background-color: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; }
    .btn-light-modern:hover { background-color: #f9fafb !important; color: #111827 !important; }
    .btn-tool { color: #6b7280 !important; border-radius: 6px !important; padding: 0.25rem 0.5rem !important; font-size: 0.85rem !important; }
    .btn-tool:hover { background-color: #f3f4f6 !important; color: #111827 !important; }
</style>
@stop

@section('content_header')
    <h1>Registro de Orden <span class="text-muted font-weight-normal ml-2" style="font-size: 1.1rem;">Paso 3 de 3</span></h1>
@stop

@section('content')

@if(session('error'))
    <div class="alert alert-danger" style="border-radius: 8px;">{{ session('error') }}</div>
@endif

<div class="row">
    {{-- RESUMEN CLIENTE --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-user text-muted mr-2"></i> Resumen del Cliente
                </h3>
                <div class="card-tools m-0">
                    <a href="{{ route('ordenes.create_paso1') }}" class="btn btn-tool">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-unbordered m-0">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Nombre Completo</b> 
                        <span style="font-weight: 500; color: #111827;">{{ $cliente->nombre }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Teléfono</b> 
                        <span style="font-weight: 500; color: #111827;">{{ $cliente->telefono }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Correo</b> 
                        <span style="font-weight: 500; color: #111827;">{{ $cliente->correo ?? 'No registrado' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Dirección</b> 
                        <span style="font-weight: 500; color: #111827;">{{ Str::limit($cliente->direccion ?? 'No registrada', 30) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- RESUMEN EQUIPO --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-mobile-alt text-muted mr-2"></i> Resumen del Equipo
                </h3>
                <div class="card-tools m-0">
                    <a href="{{ route('ordenes.create_paso2') }}" class="btn btn-tool">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-unbordered m-0">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Tipo</b> 
                        <span style="font-weight: 500; color: #111827;">{{ $equipo->tipo }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Marca y Modelo</b> 
                        <span style="font-weight: 500; color: #111827;">{{ $equipo->marca }} / {{ $equipo->modelo ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <b class="text-muted">Número de Serie</b> 
                        <span style="font-weight: 500; color: #111827;">{{ $equipo->numero_serie }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- DETALLES ORDEN --}}
<div class="card">
    <div class="card-header border-0 pb-0">
        <h3 class="card-title">Detalles Finales de la Orden</h3>
    </div>

    <form action="{{ route('ordenes.store_paso3') }}" method="POST">
        @csrf

        <div class="card-body p-4">
            <div class="form-group mb-4">
                <label>Técnico asignado *</label>
                <select name="id_usuario" class="form-control @error('id_usuario') is-invalid @enderror">
                    <option value="">-- Seleccionar técnico --</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" {{ old('id_usuario') == $usuario->id ? 'selected' : '' }}>{{ $usuario->nombre }}</option>
                    @endforeach
                </select>
                @error('id_usuario') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group mb-4">
                <label>Problema reportado (Motivo de servicio) *</label>
                <textarea name="problema_reportado" class="form-control @error('problema_reportado') is-invalid @enderror" rows="2" placeholder="Describe brevemente la falla reportada por el cliente...">{{ old('problema_reportado') }}</textarea>
                @error('problema_reportado') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group mb-4">
                <label>Estado físico actual (Golpes, detalles estéticos, accesorios devueltos) *</label>
                <textarea name="estado_fisico" class="form-control @error('estado_fisico') is-invalid @enderror" rows="2" placeholder="Menciona cable, cargador, funda, rayones, etc...">{{ old('estado_fisico') }}</textarea>
                @error('estado_fisico') <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="card-footer bg-white border-top p-4 text-right" style="border-radius: 0 0 12px 12px;">
            <a href="{{ route('ordenes.create_paso2') }}" class="btn btn-light-modern mr-2"><i class="fas fa-arrow-left mr-1"></i> Atrás (Paso 2)</a>
            <button type="submit" class="btn btn-dark-modern"><i class="fas fa-check-circle mr-1"></i> Confirmar y Generar Folio</button>
        </div>
    </form>
</div>

@stop
