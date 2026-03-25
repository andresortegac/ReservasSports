@extends('layouts.app')

@section('page-class', 'page-dashboard')

@section('content')
<div class="dashboard-hero d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <span class="badge text-bg-success rounded-pill px-3 py-2 mb-2">Home administrativo</span>
        <h1 class="h3 mb-2">Operacion central de la cancha</h1>
        <p class="text-muted mb-0">Desde aqui puedes controlar reservas, clientes, cobros y seguimiento diario del negocio.</p>
    </div>
    <div class="dashboard-actions d-flex flex-wrap">
        <a href="{{ route('reservas.create') }}" class="btn btn-rs btn-rs-primary">Crear reserva</a>
        <a href="{{ route('clientes.create') }}" class="btn btn-rs btn-rs-light">Nuevo cliente</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body dashboard-summary-card">
                <p class="text-muted mb-2">Reservas hoy</p>
                <h2 class="mb-1">{{ $metrics['reservasHoy'] }}</h2>
                <small class="text-muted">Activas para la jornada</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body dashboard-summary-card">
                <p class="text-muted mb-2">Cobros pendientes</p>
                <h2 class="mb-1">{{ $metrics['pendientesCobro'] }}</h2>
                <small class="text-muted">Reservas aun sin pago completo</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body dashboard-summary-card">
                <p class="text-muted mb-2">Ingresos hoy</p>
                <h2 class="mb-1">${{ number_format($metrics['ingresosHoy'], 0, ',', '.') }}</h2>
                <small class="text-muted">Pagos confirmados del dia</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body dashboard-summary-card">
                <p class="text-muted mb-2">Clientes</p>
                <h2 class="mb-1">{{ $metrics['clientesTotales'] }}</h2>
                <small class="text-muted">Base actual registrada</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h5 mb-1">Proximas reservas</h3>
                        <p class="text-muted mb-0">Vista rapida para la operacion del dia y siguientes turnos.</p>
                    </div>
                    <a href="{{ route('reservas.index') }}" class="btn btn-sm btn-outline-dark rounded-pill">Ver todas</a>
                </div>

                @if ($proximasReservas->isEmpty())
                    <div class="alert alert-light border mb-0">No hay reservas proximas registradas.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($proximasReservas as $reserva)
                                    <tr>
                                        <td>{{ $reserva->cliente->nombre ?? 'Sin cliente' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ substr($reserva->hora, 0, 5) }}</td>
                                        <td>
                                            <span class="badge rounded-pill text-bg-{{ $reserva->estado === 'pagada' ? 'success' : ($reserva->estado === 'pendiente' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($reserva->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h5 mb-3">Estado general</h3>
                <div class="d-grid gap-3">
                    <div class="p-3 rounded-4 border bg-light">
                        <div class="d-flex justify-content-between">
                            <span>Reservas pendientes</span>
                            <strong>{{ $estadoReservas['pendiente'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="p-3 rounded-4 border bg-light">
                        <div class="d-flex justify-content-between">
                            <span>Reservas pagadas</span>
                            <strong>{{ $estadoReservas['pagada'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="p-3 rounded-4 border bg-light">
                        <div class="d-flex justify-content-between">
                            <span>Reservas canceladas</span>
                            <strong>{{ $estadoReservas['cancelada'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h3 class="h5 mb-3">Horas mas reservadas del mes</h3>
                @if ($horasMasReservadas->isEmpty())
                    <div class="alert alert-light border mb-0">Aun no hay suficiente informacion para mostrar tendencias.</div>
                @else
                    <div class="d-grid gap-2">
                        @foreach ($horasMasReservadas as $franja)
                            <div class="p-3 rounded-4 border bg-light d-flex justify-content-between align-items-center">
                                <span>{{ substr($franja->hora, 0, 5) }}</span>
                                <span class="badge text-bg-dark rounded-pill">{{ $franja->total }} reservas</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h5 mb-3">Mapa funcional del sistema</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <h4 class="h6">Reservas y calendario</h4>
                            <p class="text-muted mb-3">Creacion, validacion de horarios ocupados, cambios y seguimiento de estados.</p>
                            <a href="{{ route('reservas.index') }}" class="btn btn-sm btn-outline-success rounded-pill">Abrir modulo</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <h4 class="h6">Clientes</h4>
                            <p class="text-muted mb-3">Base de clientes y trazabilidad para promociones o historial de reservas.</p>
                            <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-success rounded-pill">Abrir modulo</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <h4 class="h6">Caja y ventas</h4>
                            <p class="text-muted mb-3">Ingreso diario, semanal y mensual de lo que ya esta registrado en reservas.</p>
                            <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-success rounded-pill">Abrir modulo</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-light">
                            <h4 class="h6">Siguiente fase</h4>
                            <p class="text-muted mb-0">Inventario, POS detallado, pagos parciales, cierre de caja y reportes avanzados quedan listos para la siguiente iteracion.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h5 mb-3">Accesos rapidos</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('reservas.create') }}" class="btn btn-success rounded-pill">Registrar nueva reserva</a>
                    <a href="{{ route('reservas.externas.index') }}" class="btn btn-outline-dark rounded-pill">Consultar reservas externas</a>
                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-dark rounded-pill">Revisar ingresos</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
