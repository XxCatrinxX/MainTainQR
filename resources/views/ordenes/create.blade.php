@extends('adminlte::page')

@section('title', 'Nueva Orden de Servicio')

@section('content_header')
    <h1>Registrar Orden de Servicio</h1>
@stop

@section('content')

<form action="{{ route('ordenes.store') }}" method="POST">
    @csrf

    <div class="row">

        {{-- ===================== CLIENTE ===================== --}}
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Datos del Cliente</h3>
                </div>

                <div class="card-body">

                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="cliente_nombre" class="form-control @error('cliente_nombre') is-invalid @enderror" value="{{ old('cliente_nombre') }}">
                        @error('cliente_nombre') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Apellido paterno *</label>
                        <input type="text" name="cliente_apellido_paterno" class="form-control @error('cliente_apellido_paterno') is-invalid @enderror" value="{{ old('cliente_apellido_paterno') }}">
                        @error('cliente_apellido_paterno') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Apellido materno</label>
                        <input type="text" name="cliente_apellido_materno" class="form-control" value="{{ old('cliente_apellido_materno') }}">
                    </div>

                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="text" name="cliente_telefono" class="form-control @error('cliente_telefono') is-invalid @enderror" value="{{ old('cliente_telefono') }}">
                        @error('cliente_telefono') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="cliente_correo" class="form-control" value="{{ old('cliente_correo') }}">
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="cliente_direccion" class="form-control">{{ old('cliente_direccion') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- ===================== EQUIPO ===================== --}}
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Datos del Equipo</h3>
                </div>

                <div class="card-body">

                    <div class="form-group">
                        <label>Tipo de equipo *</label>
                        <input type="text" name="equipo_tipo" class="form-control @error('equipo_tipo') is-invalid @enderror" value="{{ old('equipo_tipo') }}">
                        @error('equipo_tipo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Marca *</label>
                        <input type="text" name="equipo_marca" class="form-control @error('equipo_marca') is-invalid @enderror" value="{{ old('equipo_marca') }}">
                        @error('equipo_marca') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="equipo_modelo" class="form-control" value="{{ old('equipo_modelo') }}">
                    </div>

                    <div class="form-group">
                        <label>Número de serie</label>
                        <input type="text" name="equipo_numero_serie" class="form-control" value="{{ old('equipo_numero_serie') }}">
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ===================== ORDEN ===================== --}}
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">Orden de Servicio</h3>
        </div>

        <div class="card-body">

            <div class="form-group">
                <label>Técnico asignado</label>
                <select name="id_usuario" class="form-control">
                    <option value="">-- Seleccionar técnico --</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Problema reportado *</label>
                <textarea name="problema_reportado" class="form-control @error('problema_reportado') is-invalid @enderror">{{ old('problema_reportado') }}</textarea>
                @error('problema_reportado') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Estado físico (detalles, golpes, accesorios) *</label>
                <textarea name="estado_fisico" class="form-control @error('estado_fisico') is-invalid @enderror">{{ old('estado_fisico') }}</textarea>
                @error('estado_fisico') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>

    {{-- BOTONES --}}
    <div class="text-right">
        <a href="{{ route('home') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar Orden</button>
    </div>

</form>

@stop