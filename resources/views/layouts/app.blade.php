<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Reservas Sports' }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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

                <div class="section-title">Panel Principal</div>
                <a href="{{ route('dashboard') }}" class="nav-link-rs {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-house-door nav-link-icon" aria-hidden="true"></i>
                        <span>Home</span>
                    </span>
                </a>

                <div class="section-title">Reservas</div>
                <a href="{{ route('reservas.index') }}" class="nav-link-rs {{ request()->routeIs('reservas.index') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-calendar-check nav-link-icon" aria-hidden="true"></i>
                        <span>Gestionar reservas</span>
                    </span>
                </a>
                <a href="{{ route('reservas.create') }}" class="nav-link-rs {{ request()->routeIs('reservas.create') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-calendar-plus nav-link-icon" aria-hidden="true"></i>
                        <span>Nueva reserva</span>
                    </span>
                </a>
                <a href="{{ route('reservas.externas.index') }}" class="nav-link-rs {{ request()->routeIs('reservas.externas.*') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-globe-americas nav-link-icon" aria-hidden="true"></i>
                        <span>Reservas externas</span>
                    </span>
                </a>

                <div class="section-title">Canchas</div>
                <a href="{{ route('canchas.index') }}" class="nav-link-rs {{ request()->routeIs('canchas.*') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-bounding-box nav-link-icon" aria-hidden="true"></i>
                        <span>Canchas</span>
                    </span>
                </a>

                <div class="section-title">Clientes</div>
                <a href="{{ route('clientes.index') }}" class="nav-link-rs {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-people nav-link-icon" aria-hidden="true"></i>
                        <span>Clientes</span>
                    </span>
                </a>

                <div class="section-title">Ventas</div>
                <a href="{{ route('ventas.index') }}" class="nav-link-rs {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                    <span class="nav-link-main">
                        <i class="bi bi-cash-stack nav-link-icon" aria-hidden="true"></i>
                        <span>Ventas y caja</span>
                    </span>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-rs btn-rs-danger w-100">
                        <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Cerrar sesión
                    </button>
                </form>
            </aside>

            <main class="content-wrap">
                <div class="topbar">
                    <button type="button" class="btn btn-rs btn-rs-light" id="sidebarToggle">Menú</button>
                    <div>
                        <strong>Reservas Sports</strong>
                        <div class="text-muted small">Sistema de reservas y operación</div>
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
