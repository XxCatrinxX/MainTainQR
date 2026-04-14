@extends('adminlte::auth.login')

@section('auth_header')
    <div class="text-center mb-4">
        <img src="{{ asset('img/isotipo.png') }}" alt="Isotipo" width="80" class="mb-3">
        <h3 class="login-box-msg font-weight-bold mb-0">MaintainQR</h3>
        <p class="text-muted small">Sistema de Gestión de Mantenimiento</p>
        <span class="badge badge-dark px-3 py-1" style="border-radius: 20px; font-size: 0.75rem; letter-spacing: 0.5px;">
            VERSÍON 1.0
        </span>
    </div>
@endsection