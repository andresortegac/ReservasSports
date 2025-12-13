<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Sports</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Si usas Bootstrap vía CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f6f7fb; }
        .sidebar {
            width: 260px; min-height: 100vh;
            background: #111827; color: #fff;
            position: fixed; top: 0; left: 0;
            padding: 18px 12px;
        }
        .sidebar a {
            color:#e5e7eb; text-decoration:none; display:block;
            padding:10px 12px; border-radius:10px; margin-bottom:6px;
        }
        .sidebar a:hover, .sidebar a.active {
            background:#1f2937; color:#fff;
        }
        .content {
            margin-left: 260px; padding: 22px;
        }
        .brand { font-weight:700; font-size:18px; padding: 10px 12px; }
        .section-title { font-size:12px; letter-spacing:.08em; color:#9ca3af; margin: 12px 0 6px 12px; }
    </style>
</head>
<body>

    <!-- Topbar móvil -->
<div class="topbar">
    <button id="sidebarToggle" class="btn btn-dark btn-sm">
        ☰ Menú
    </button>
    <strong>Reservas Sports</strong>
</div>

<!-- Overlay -->
<div class="overlay"></div>

    <aside class="sidebar">
        <div class="brand">🏟️ Reservas Sports</div>

        <div class="section-title">RESERVAS</div>
        <a href="{{ route('reservas.externas.index') }}"
            class="{{ request()->routeIs('reservas.externas.*') ? 'active' : '' }}">
            Ver Reservas externas
        </a>


        <a href="{{ route('reservas.index') }}"
           class="{{ request()->routeIs('reservas.*') ? 'active' : '' }}">
           CRUD Reservas (BD principal)
        </a>

        <a href="{{ route('reservas.create') }}"
           class="{{ request()->routeIs('reservas.create') ? 'active' : '' }}">
           Hacer reserva
        </a>

        <div class="section-title">CLIENTES</div>
        <a href="{{ route('clientes.index') }}"
           class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">
           Clientes + Canchas separadas
        </a>

        <div class="section-title">VENTAS</div>
        <a href="{{ route('ventas.index') }}"
           class="{{ request()->routeIs('ventas.*') ? 'active' : '' }}">
           Reporte ventas (día/semana/mes)
        </a>
    </aside>

    <main class="content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
