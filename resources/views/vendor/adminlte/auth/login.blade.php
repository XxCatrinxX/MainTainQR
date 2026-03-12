@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">

<style>

/* Fondo */
.login-page{
    background:#f4f6f9 !important;
}

/* Tarjeta */
.login-box .card{
    border-radius:10px;
    border:none;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}

/* Header */
.login-title{
    font-weight:600;
    color:#000;
}

.login-subtitle{
    color:#6c757d;
    font-size:14px;
}

/* Inputs */
.form-control{
    border-radius:6px;
}

.form-control:focus{
    border-color:#444;
    box-shadow:none;
}

/* Botón login */
.btn-login{
    background:#000 !important;
    border:none !important;
    color:#fff !important;
    font-weight:600;
    border-radius:6px;
}

.btn-login:hover{
    background:#333 !important;
}

/* Iconos */
.input-group-text{
    background:#f8f9fa;
}

</style>
@stop


@php
$loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
$registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');
$passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

if (config('adminlte.use_route_url', false)) {
    $loginUrl = $loginUrl ? route($loginUrl) : '';
    $registerUrl = $registerUrl ? route($registerUrl) : '';
    $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
} else {
    $loginUrl = $loginUrl ? url($loginUrl) : '';
    $registerUrl = $registerUrl ? url($registerUrl) : '';
    $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
}
@endphp


@section('auth_header')

<div class="text-center">



<h3 class="login-title mt-2">
MaintainQR
</h3>

<p class="login-subtitle">
Sistema de gestión de mantenimiento
</p>

</div>

@stop


@section('auth_body')

<form action="{{ $loginUrl }}" method="post">
@csrf

{{-- Email --}}
<div class="input-group mb-3">

<input
type="email"
name="email"
class="form-control @error('email') is-invalid @enderror"
value="{{ old('email') }}"
placeholder="Correo electrónico"
autofocus
>

<div class="input-group-append">
<div class="input-group-text">
<span class="fas fa-envelope"></span>
</div>
</div>

@error('email')
<span class="invalid-feedback">
<strong>{{ $message }}</strong>
</span>
@enderror

</div>


{{-- Password --}}
<div class="input-group mb-3">

<input
type="password"
name="password"
class="form-control @error('password') is-invalid @enderror"
placeholder="Contraseña"
>

<div class="input-group-append">
<div class="input-group-text">
<span class="fas fa-lock"></span>
</div>
</div>

@error('password')
<span class="invalid-feedback">
<strong>{{ $message }}</strong>
</span>
@enderror

</div>


<div class="row">

<div class="col-6">

<div class="icheck-primary">

<input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

<label for="remember">
Recordarme
</label>

</div>

</div>

<div class="col-6">

<button type="submit" class="btn btn-login btn-block">

<span class="fas fa-sign-in-alt"></span>

Iniciar sesión

</button>

</div>

</div>

</form>

@stop


@section('auth_footer')

@if($passResetUrl)
<p class="my-2 text-center">
<a href="{{ $passResetUrl }}">
¿Olvidaste tu contraseña?
</a>
</p>
@endif

@if($registerUrl)
<p class="text-center">
<a href="{{ $registerUrl }}">
Crear cuenta
</a>
</p>
@endif

@stop