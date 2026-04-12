@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">

<style>

/* 🌌 Fondo con degradado suave */
.login-page{
    background: linear-gradient(135deg, #eef2f7, #e3e8ef) !important;
}

/* 🧊 Tarjeta */
.login-box .card{
    border-radius:16px;
    border:none;
    box-shadow:0 12px 30px rgba(0,0,0,0.08);
    backdrop-filter: blur(6px);
    animation: fadeInUp 0.6s ease;
}

/* ✨ Animación entrada */
@keyframes fadeInUp{
    from{
        opacity:0;
        transform: translateY(20px);
    }
    to{
        opacity:1;
        transform: translateY(0);
    }
}

/* 🏷️ Header */
.login-title{
    font-weight:700;
    color:#2c2f36;
    letter-spacing:0.5px;
}

.login-subtitle{
    color:#8a94a6;
    font-size:14px;
}

/* 🧾 Inputs */
.form-control{
    border-radius:10px;
    border:1px solid #e0e6ed;
    padding:10px;
    transition: all 0.2s ease;
}

/* Focus bonito */
.form-control:focus{
    border-color:#6c63ff;
    box-shadow:0 0 0 3px rgba(108,99,255,0.1);
}

/* Iconos */
.input-group-text{
    background:#f1f3f6;
    border:none;
    border-radius:0 10px 10px 0;
}

/* ✨ Botón */
.btn-login{
    background: linear-gradient(135deg, #6c63ff, #5a54e6) !important;
    border:none !important;
    color:#fff !important;
    font-weight:600;
    border-radius:10px;
    transition: all 0.25s ease;
    box-shadow:0 4px 12px rgba(108,99,255,0.3);
}

/* Hover */
.btn-login:hover{
    transform: translateY(-1px);
    box-shadow:0 6px 16px rgba(108,99,255,0.35);
}

/* Click */
.btn-login:active{
    transform: scale(0.98);
}

/* Links */
a{
    color:#6c63ff;
    transition: all 0.2s ease;
}

a:hover{
    color:#4b47c2;
    text-decoration:none;
}

/* Checkbox */
.icheck-primary input:checked ~ label::before{
    background-color:#6c63ff !important;
    border-color:#6c63ff !important;
}

/* Inputs error */
.is-invalid{
    border-color:#e74c3c !important;
}

/* ✨ Animación suave en inputs */
.input-group{
    transition: all 0.2s ease;
}

.input-group:focus-within{
    transform: scale(1.01);
}

/* 📱 Responsive mejorado */
@media (max-width: 576px){
    .login-box{
        width: 95%;
    }
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
id="password"
type="password"
name="password"
class="form-control @error('password') is-invalid @enderror"
placeholder="Contraseña"
>

<div class="input-group-append">
<div class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
<span class="fas fa-eye" id="togglePasswordIcon"></span>
</div>
</div>

@error('password')
<span class="invalid-feedback">
<strong>{{ $message }}</strong>
</span>
@enderror

</div>

<script>
function togglePassword() {
    var x = document.getElementById("password");
    var icon = document.getElementById("togglePasswordIcon");
    if (x.type === "password") {
        x.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        x.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

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