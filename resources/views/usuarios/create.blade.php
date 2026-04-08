@extends('adminlte::page')

@section('title', 'Nuevo Usuario')

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Nuevo Usuario</h1>
            <a href="{{ route('usuarios.index') }}" class="text-muted" style="font-size: 0.85rem; text-decoration: none;">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
            </a>
        </div>
    </div>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Datos del nuevo usuario</h3>
            </div>

            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger" style="border-radius: 8px;">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Nombre *</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej. Juan">
                            @error('nombre') <span class="text-danger" style="font-size:0.85rem;">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label>Apellido *</label>
                            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror" value="{{ old('apellido') }}" placeholder="Ej. Pérez">
                            @error('apellido') <span class="text-danger" style="font-size:0.85rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Correo Electrónico *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                            @error('email') <span class="text-danger" style="font-size:0.85rem;">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label>Rol *</label>
                            <select name="rol" class="form-control @error('rol') is-invalid @enderror">
                                <option value="">-- Seleccionar rol --</option>
                                <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="tecnico" {{ old('rol') == 'tecnico' ? 'selected' : '' }}>Técnico</option>
                                <option value="almacenista" {{ old('rol') == 'almacenista' ? 'selected' : '' }}>Almacenista</option>
                                <option value="recepcionista" {{ old('rol') == 'recepcionista' ? 'selected' : '' }}>Recepcionista</option>
                            </select>
                            @error('rol') <span class="text-danger" style="font-size:0.85rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label>Contraseña *</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 8 caracteres">
                            @error('password') <span class="text-danger" style="font-size:0.85rem;">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label>Confirmar Contraseña *</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repetir contraseña">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label>Estado *</label>
                        <select name="estado" class="form-control @error('estado') is-invalid @enderror">
                            <option value="activo" {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado') <span class="text-danger" style="font-size:0.85rem;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 text-right">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-light-modern mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-dark-modern"><i class="fas fa-save mr-1"></i> Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop
