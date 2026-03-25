@extends('layouts.app')

@section('page-class', 'page-login')

@section('content')
<section class="login-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
                <div class="card login-card border-0 shadow-lg rounded-5">
                    <div class="card-body">
                        <div class="login-header">
                            <div class="brand-badge mx-auto mb-3">RS</div>
                            <p class="login-kicker mb-2">Acceso administrativo</p>
                            <h1 class="login-title">Iniciar sesión</h1>
                            <p class="login-sub mb-0">Ingresa con tu cuenta de administrador para gestionar reservas, clientes y ventas.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert-danger-custom mb-4">
                                Verifica tu correo y contraseña antes de continuar.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" class="login-form">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo</label>
                                <div class="input-wrap">
                                    <span class="input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <rect x="3.5" y="6.5" width="17" height="11" rx="2"></rect>
                                            <path d="M4 8l8 5.5L20 8"></path>
                                        </svg>
                                    </span>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="admin@reservassports.com" required autofocus autocomplete="email">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-wrap">
                                    <span class="input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M8 10V8.75a4 4 0 1 1 8 0V10"></path>
                                            <rect x="5.5" y="10" width="13" height="9.5" rx="2"></rect>
                                            <path d="M12 13.25v3"></path>
                                        </svg>
                                    </span>
                                    <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="password" name="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Mantener sesión iniciada</label>
                            </div>

                            <button type="submit" class="btn btn-rs login-submit w-100">Entrar al panel</button>
                        </form>

                        <div class="login-help">
                            <strong>Acceso inicial</strong>
                            correo: <code>admin@reservassports.com</code><br>
                            clave: <code>admin12345</code>
                        </div>

                        <p class="small-note mb-0">Reservas Sports mantiene tu operación lista para el siguiente turno.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection