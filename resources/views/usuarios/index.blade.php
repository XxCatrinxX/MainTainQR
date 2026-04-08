@extends('adminlte::page')

@section('title', 'Usuarios del Sistema')

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Usuarios del Sistema</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Personal con acceso a la plataforma.</p>
        </div>
        @if(Auth::user()->rol === 'admin')
        <a href="{{ route('usuarios.create') }}" class="btn btn-dark-modern shadow-sm">
            <i class="fas fa-user-plus mr-1"></i> Nuevo Usuario
        </a>
        @endif
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table-modern table mb-0">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    @if(Auth::user()->rol === 'admin')
                    <th class="text-right">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="mr-3" style="width: 38px; height: 38px; background: #111827; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}{{ strtoupper(substr($usuario->apellido, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #111827;">{{ $usuario->nombre }} {{ $usuario->apellido }}</div>
                                <div style="font-size: 0.75rem; color: #6b7280;">#{{ $usuario->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $usuario->email }}</td>
                    <td>
                        @php
                            $roleColors = ['admin' => 'badge-dark', 'tecnico' => 'badge-primary', 'almacenista' => 'badge-warning', 'recepcionista' => 'badge-info'];
                            $roleLabels = ['admin' => 'Admin', 'tecnico' => 'Técnico', 'almacenista' => 'Almacenista', 'recepcionista' => 'Recepcionista'];
                        @endphp
                        <span class="badge {{ $roleColors[$usuario->rol] ?? 'badge-secondary' }}">
                            {{ $roleLabels[$usuario->rol] ?? $usuario->rol }}
                        </span>
                    </td>
                    <td>
                        @if($usuario->estado === 'activo')
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </td>
                    @if(Auth::user()->rol === 'admin')
                    <td class="text-right">
                        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-action" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($usuario->id !== Auth::id())
                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-action text-danger" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3 text-light"></i><br>
                        No hay usuarios registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop
