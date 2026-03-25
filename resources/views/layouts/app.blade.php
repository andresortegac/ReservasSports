<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Reservas Sports' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="@yield('page-class')">
    @auth
        <div class="app-shell">
            <div class="overlay" id="appOverlay"></div>
            <aside class="sidebar" id="appSidebar">
                <div class="sidebar-card">
                    <div class="brand-lockup">
                        <div class="brand-badge">RS</div>
                        <div class="brand-copy">
                            <strong>Reservas Sports</strong>
                            <span>Panel administrativo</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small class="d-block">{{ auth()->user()->email }}</small>
                    </div>
                </div>

                <div class="section-title">General</div>
                <a href="{{ route('dashboard') }}" class="nav-link-rs {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span>Home</span>
                    <span>01</span>
                </a>

                <div class="section-title">Reservas</div>
                <a href="{{ route('reservas.index') }}" class="nav-link-rs {{ request()->routeIs('reservas.index') ? 'active' : '' }}">
                    <span>Gestionar reservas</span>
                    <span>02</span>
                </a>
                <a href="{{ route('reservas.create') }}" class="nav-link-rs {{ request()->routeIs('reservas.create') ? 'active' : '' }}">
                    <span>Nueva reserva</span>
                    <span>03</span>
                </a>
                <a href="{{ route('reservas.externas.index') }}" class="nav-link-rs {{ request()->routeIs('reservas.externas.*') ? 'active' : '' }}">
                    <span>Reservas externas</span>
                    <span>04</span>
                </a>

                <div class="section-title">Operacion</div>
                <a href="{{ route('clientes.index') }}" class="nav-link-rs {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                    <span>Clientes</span>
                    <span>05</span>
                </a>
                <a href="{{ route('ventas.index') }}" class="nav-link-rs {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                    <span>Ventas y caja</span>
                    <span>06</span>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-rs btn-rs-light w-100">Cerrar sesion</button>
                </form>
            </aside>

            <main class="content-wrap">
                <div class="topbar">
                    <button type="button" class="btn btn-rs btn-rs-light" id="sidebarToggle">Menu</button>
                    <div>
                        <strong>Reservas Sports</strong>
                        <div class="text-muted small">Sistema de reservas y operacion</div>
                    </div>
                </div>

                <div class="page-panel">
                    <div class="flash-stack mb-3">
                        @if (session('ok'))
                            <div class="alert alert-success mb-0">{{ session('ok') }}</div>
                        @endif

                        @if (session('premio'))
                            <div class="alert alert-warning mb-0">{{ session('premio') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger mb-0">
                                <strong>Revisa estos datos:</strong>
                                <ul class="mb-0 mt-2 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    @yield('content')
                </div>
            </main>
        </div>
    @else
        <div class="guest-shell">
            <main>
                @yield('content')
            </main>
        </div>
    @endauth

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
