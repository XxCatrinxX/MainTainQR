@extends('adminlte::auth.passwords.reset')

@section('auth_header')
    <div class="text-center mb-4">
        <img src="{{ asset('img/isotipo.png') }}" alt="Isotipo" width="60" class="mb-3">
        <h4 class="login-box-msg font-weight-bold mb-0">MaintainQR</h4>
        <span class="badge badge-dark px-3 mt-1" style="border-radius: 20px; font-size: 0.7rem;">CAMBIAR CONTRASEÑA</span>
    </div>
@endsection