@extends('adminlte::auth.auth-page', ['authType' => 'register'])
@php
$loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
$registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');

if (config('adminlte.use_route_url', false)) {
    $loginUrl = $loginUrl ? route($loginUrl) : '';
    $registerUrl = $registerUrl ? route($registerUrl) : '';
} else {
    $loginUrl = $loginUrl ? url($loginUrl) : '';
    $registerUrl = $registerUrl ? url($registerUrl) : '';
}
@endphp
@section('adminlte_css_pre')
@section('auth_header')

<div class="text-center">

<h3 class="register-title">
Crear cuenta
</h3>

<p style="color:#6c757d;font-size:14px;">
Registro de usuario
</p>

</div>

@stop

@section('auth_body')

<form action="{{ $registerUrl }}" method="post">
@csrf

{{-- Nombre --}}
<div class="input-group mb-3">

<input
type="text"
name="nombre"
class="form-control @error('nombre') is-invalid @enderror"
value="{{ old('nombre') }}"
placeholder="Nombre"
required
>

<div class="input-group-append">
<div class="input-group-text">
<span class="fas fa-user"></span>
</div>
</div>

@error('nombre')
<span class="invalid-feedback">
<strong>{{ $message }}</strong>
</span>
@enderror

</div>


{{-- Apellidos --}}
<div class="input-group mb-3">

<input
type="text"
name="apellido"
class="form-control @error('apellido') is-invalid @enderror"
value="{{ old('apellido') }}"
placeholder="Apellidos"
required
>

<div class="input-group-append">
<div class="input-group-text">
<span class="fas fa-user"></span>
</div>
</div>

@error('apellido')
<span class="invalid-feedback">
<strong>{{ $message }}</strong>
</span>
@enderror

</div>


{{-- Email --}}
<div class="input-group mb-3">

<input
type="email"
name="email"
class="form-control @error('email') is-invalid @enderror"
value="{{ old('email') }}"
placeholder="Correo electrónico"
required
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
required
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


{{-- Confirmar password --}}
<div class="input-group mb-3">

<input
type="password"
name="password_confirmation"
class="form-control"
placeholder="Confirmar contraseña"
required
>

<div class="input-group-append">
<div class="input-group-text">
<span class="fas fa-lock"></span>
</div>
</div>

</div>


<button type="submit" class="btn btn-register btn-block">

<span class="fas fa-user-plus"></span>

Registrarse

</button>

</form>

@stop
@section('auth_footer')

<p class="my-0 text-center">

<a href="{{ $loginUrl }}">
Ya tengo una cuenta
</a>

</p>

@stop
<style>

/* fondo */
.register-page{
    background:#f4f6f9 !important;
}

/* tarjeta */
.register-box .card{
    border-radius:10px;
    border:none;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}

/* titulo */
.register-title{
    font-weight:600;
    color:#000;
}

/* inputs */
.form-control{
    border-radius:6px;
}

.form-control:focus{
    border-color:#444;
    box-shadow:none;
}

/* iconos */
.input-group-text{
    background:#f8f9fa;
}

/* boton */
.btn-register{
    background:#000 !important;
    color:#fff !important;
    font-weight:600;
    border:none;
    border-radius:6px;
}

.btn-register:hover{
    background:#333 !important;
}

</style>

@stop