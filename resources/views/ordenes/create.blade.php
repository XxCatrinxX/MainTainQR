@extends('adminlte::page')

@section('title', 'Nueva Orden de Servicio')

@section('content')
<div class="container-fluid">
    <form action="{{ route('ordenes.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title font-weight-bold">Detalles de la Recepción</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Equipo</label>
                                <select name="id_equipo" class="form-control @error('id_equipo') is-invalid @enderror">
                                    <option value="">Seleccionar Equipo...</option>
                                    @foreach($equipos as $equipo)
                                        <option value="{{ $equipo->id_equipo }}" {{ old('id_equipo') == $equipo->id_equipo ? 'selected' : '' }}>{{ $equipo->marca }} - {{ $equipo->modelo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Técnico / Usuario Responsable</label>
                                <select name="id_usuario" class="form-control @error('id_usuario') is-invalid @enderror">
                                    <option value="">Asignar a...</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ old('id_usuario') == $usuario->id ? 'selected' : '' }}>{{ $usuario->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Problema Reportado (Cliente)</label>
                            <textarea name="problema_reportado" class="form-control" rows="3">{{ old('problema_reportado') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Diagnóstico Inicial (Opcional)</label>
                                <textarea name="diagnostico" class="form-control" rows="3">{{ old('diagnostico') }}</textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Actividad a Realizar</label>
                                <textarea name="actividad_a_realizar" class="form-control" rows="3">{{ old('actividad_a_realizar') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header"><h3 class="card-title">Estado y Tiempos</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Estado de la Orden</label>
                            <select name="estado" class="form-control">
                                <option value="abierta">Abierta</option>
                                <option value="en_diagnostico">En Diagnóstico</option>
                                <option value="en_proceso">En Proceso</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Recepción</label>
                            <input type="datetime-local" name="fecha_recepcion" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                </div>

                <div class="card card-success">
    <div class="card-header"><h3 class="card-title">Costos Iniciales (MXN)</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Costo Materiales</label>
            <input type="number" id="costo_materiales" name="costo_materiales" step="0.01" class="form-control" value="0.00">
        </div>
        <div class="form-group">
            <label>Costo Servicio</label>
            <input type="number" id="costo_servicio" name="costo_servicio" step="0.01" class="form-control" value="0.00">
        </div>
        <div class="form-group">
            <label class="text-success">Costo Total</label>
            <input type="number" id="costo_total" name="costo_total" step="0.01" class="form-control font-weight-bold" value="0.00" readonly>
        </div>
    </div>
</div>
            </div>
        </div>

        <div class="row pb-5">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary btn-lg shadow"><i class="fas fa-save"></i> Guardar Orden de Servicio</button>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Función para calcular el total
        function calcularTotal() {
            // Obtenemos los valores. parseFloat ayuda a tratar los números correctamente.
            let materiales = parseFloat($('#costo_materiales').val()) || 0;
            let servicio = parseFloat($('#costo_servicio').val()) || 0;
            
            // Calculamos la suma
            let total = materiales + servicio;
            
            // Asignamos el resultado al campo total con 2 decimales
            $('#costo_total').val(total.toFixed(2));
        }

        // Detectar cuando el usuario escribe en cualquiera de los dos campos
        $('#costo_materiales, #costo_servicio').on('input', function() {
            calcularTotal();
        });
    }); // <--- Aquí estaba el error, antes decía </div>
</script>
@stop