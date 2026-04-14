@extends('adminlte::page')

@section('title', 'Registrar Pago')

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
</style>
@stop

@section('content_header')
    <h1>Ingresar Nuevo Cobro</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card p-4">
            <form action="{{ route('pagos.store') }}" method="POST">
                @csrf
                
                <div class="form-group mb-4">
                    <label>Asignar Pago a la Orden de Servicio *</label>
                    <select name="orden_servicio_id" class="custom-select @error('orden_servicio_id') is-invalid @enderror" required>
                        <option value="">-- Seleccionar ID Folio --</option>
                        @foreach($orden_lista as $orden)
                            <option value="{{ $orden->id }}" 
                                {{ (old('orden_servicio_id') == $orden->id || (isset($orden_preseleccionada) && $orden_preseleccionada->id == $orden->id)) ? 'selected' : '' }}>
                                {{ $orden->folio }} - {{ $orden->cliente->nombre ?? 'N/A' }} | Pendiente: ${{ number_format($orden->restante, 2) }} | Estado: {{ strtoupper($orden->estado) }}
                            </option>
                        @endforeach
                    </select>
                    @error('orden_servicio_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-4">
                        <label>Monto Recibido</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white" style="border-radius: 8px 0 0 8px; border-color: #d1d5db;">$</span>
                            </div>
                            <input type="number" step="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" style="border-radius: 0 8px 8px 0 !important;" value="{{ old('monto') }}" min="1" required>
                        </div>
                        @error('monto') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 form-group mb-4">
                        <label>Canal de Pago</label>
                        <select name="metodo_pago" class="custom-select @error('metodo_pago') is-invalid @enderror" required>
                            <option value="efectivo" {{ old('metodo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo (Caja Fuerte)</option>
                            <option value="tarjeta" {{ old('metodo_pago') == 'tarjeta' ? 'selected' : '' }}>Tarjeta (Terminal POS)</option>
                            <option value="transferencia" {{ old('metodo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia (SPEI)</option>
                        </select>
                        @error('metodo_pago') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label>Clasificación del Movimiento (Concepto)</label>
                    <div class="d-flex mt-2">
                        <div class="custom-control custom-radio mr-4">
                            <input class="custom-control-input" type="radio" id="pago_anticipo" name="tipo_pago" value="anticipo" {{ old('tipo_pago', 'anticipo') == 'anticipo' ? 'checked' : '' }}>
                            <label for="pago_anticipo" class="custom-control-label font-weight-normal text-muted">Anticipo / Reserva</label>
                        </div>
                        <div class="custom-control custom-radio mr-4">
                            <input class="custom-control-input" type="radio" id="pago_liquidacion" name="tipo_pago" value="liquidacion" {{ old('tipo_pago') == 'liquidacion' ? 'checked' : '' }}>
                            <label for="pago_liquidacion" class="custom-control-label font-weight-normal text-muted">Liquidación Final de Deuda</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="pago_cliente" name="tipo_pago" value="pago_cliente" {{ old('tipo_pago') == 'pago_cliente' ? 'checked' : '' }}>
                            <label for="pago_cliente" class="custom-control-label font-weight-normal text-muted">Pago a Cliente (Compra de Piezas)</label>
                        </div>
                    </div>
                    @error('tipo_pago') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4 pt-4 border-top">
                    <a href="{{ route('pagos.index') }}" class="btn btn-modern btn-light-modern mr-3">Cancelar</a>
                    <button type="submit" class="btn btn-modern btn-dark-modern">
                        <i class="fas fa-check-circle mr-1"></i> Asentar Cobro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
