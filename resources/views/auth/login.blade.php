@extends('layouts.app')

@section('page-class', 'page-login')

@section('content')
<section class="login-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="card login-card border-0 shadow-lg rounded-5">
                    <div class="card-body">
                        <div class="login-header">
                            <div class="brand-badge mx-auto mb-3">RS</div>
                            <h1 class="h3 mb-1">Iniciar sesion</h1>
                            <p class="text-muted mb-0">Ingresa con tu cuenta de administrador.</p>
                        </div>

                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo</label>
                                <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contrasena</label>
                                <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Mantener sesion iniciada</label>
                            </div>

                            <button type="submit" class="btn btn-rs btn-rs-primary w-100">Entrar</button>
                        </form>

                        <div class="alert alert-light border mt-4 mb-0 login-help">
                            <strong>Acceso inicial:</strong><br>
                            correo: <code>admin@reservassports.com</code><br>
                            clave: <code>admin12345</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
