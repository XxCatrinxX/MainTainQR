@extends('adminlte::page')

@section('title', 'Editar Pieza')

@section('css')
<style>
    body { background-color: #fafafa !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .content-wrapper { background-color: transparent !important; }
    .content-header h1 { font-weight: 700; font-size: 1.5rem; color: #111827; }
    
    .card { border: 1px solid #eaeaea !important; border-radius: 12px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; }
    .form-group label { font-weight: 500; color: #374151; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .form-control, .custom-select { border-radius: 8px !important; border: 1px solid #d1d5db !important; padding: 0.6rem 1rem !important; height: auto !important; font-size: 0.95rem; background-color: #ffffff !important; transition: all 0.2s; }
    .form-control:focus, .custom-select:focus { border-color: #000000 !important; box-shadow: 0 0 0 3px rgba(0,0,0,0.1) !important; }
    
    .btn-modern { border-radius: 8px !important; font-weight: 500 !important; padding: 0.6rem 1.5rem; transition: all 0.2s ease; border: none; }
    .btn-dark-modern { background-color: #000000 !important; color: #ffffff !important; }
    .btn-dark-modern:hover { background-color: #333333 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; color: white !important; }
    .btn-light-modern { background-color: #ffffff !important; color: #374151 !important; border: 1px solid #d1d5db !important; }
    .btn-light-modern:hover { background-color: #f9fafb !important; color: #111827 !important; }
</style>
@stop

@section('content_header')
    <h1>Modificar Pieza de Inventario</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card p-4">
            <form action="{{ route('inventario.update', $inventario->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-8 form-group mb-4">
                        <label>Nombre del Repuesto/Pieza *</label>
                        <input type="text" name="nombre_pieza" class="form-control @error('nombre_pieza') is-invalid @enderror" value="{{ old('nombre_pieza', $inventario->nombre_pieza) }}" required>
                        @error('nombre_pieza') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="col-md-4 form-group mb-4">
                        <label>SKU (Único) *</label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $inventario->sku) }}" required>
                        @error('sku') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-4">
                        <label>Calidad *</label>
                        <select name="calidad" class="custom-select @error('calidad') is-invalid @enderror" required>
                            <option value="original" {{ old('calidad', $inventario->calidad) == 'original' ? 'selected' : '' }}>Original / OEM</option>
                            <option value="generica" {{ old('calidad', $inventario->calidad) == 'generica' ? 'selected' : '' }}>Genérica</option>
                            <option value="usada" {{ old('calidad', $inventario->calidad) == 'usada' ? 'selected' : '' }}>Usada (Desarme)</option>
                        </select>
                        @error('calidad') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 form-group mb-4">
                        <label>Unidades de Stock *</label>
                        <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $inventario->stock) }}" min="0" required>
                        @error('stock') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 form-group mb-4">
                        <label>Precio de Venta (MXN/USD) *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white" style="border-radius: 8px 0 0 8px; border-color: #d1d5db;">$</span>
                            </div>
                            <input type="number" step="0.01" name="precio_venta" class="form-control @error('precio_venta') is-invalid @enderror" style="border-radius: 0 8px 8px 0 !important;" value="{{ old('precio_venta', $inventario->precio_venta) }}" min="0" required>
                        </div>
                        @error('precio_venta') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-4 border-top">
                    <a href="{{ route('inventario.index') }}" class="btn btn-modern btn-light-modern mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-modern btn-dark-modern">
                        <i class="fas fa-check mr-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
