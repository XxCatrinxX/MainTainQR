@extends('adminlte::auth.passwords.email')

@section('auth_header')
    <div class="text-center mb-4">
        <img src="{{ asset('img/isotipo.png') }}" alt="Isotipo" width="80" class="mb-3">
        <h3 class="login-box-msg font-weight-bold mb-0">MaintainQR</h3>
        <p class="text-muted small">Restablecer acceso al sistema</p>
        <span class="badge badge-dark px-3 py-1" style="border-radius: 20px; font-size: 0.75rem; letter-spacing: 0.5px;">
            RECUPERAR ACCESO
        </span>
    </div>
@endsection