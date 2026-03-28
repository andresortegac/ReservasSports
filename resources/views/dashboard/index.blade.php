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
        <a href="{{ route('clientes.create') }}" class="btn btn-rs btn-rs-primary">Nuevo cliente</a>
        <a href="{{ route('canchas.index') }}" class="btn btn-rs btn-rs-light">Gestionar canchas</a>
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
                <small class="text-muted">Cobros registrados del dia</small>
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
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap">
                    <div>
                        <h3 class="h5 mb-1">Ganancias de este mes</h3>
                        <p class="text-muted mb-0">Distribucion circular de los ingresos cobrados en {{ ucfirst($monthLabel) }}.</p>
                    </div>
                    <span class="badge text-bg-light border">${{ number_format($gananciasPorDia->sum('total'), 0, ',', '.') }}</span>
                </div>

                @if ($gananciasPorDia->sum('total') <= 0)
                    <div class="alert alert-light border mb-0">Todavia no hay ingresos registrados este mes para graficar.</div>
                @else
                    @php
                        $pieColors = ['#198754', '#20c997', '#0d6efd', '#6f42c1', '#fd7e14', '#dc3545', '#ffc107', '#6c757d', '#6610f2', '#198754', '#fd9843', '#0dcaf0'];
                        $gananciasConValor = $gananciasPorDia->filter(fn ($item) => $item['total'] > 0)->values();
                        $pieSegments = [];
                        $currentDeg = 0;
                        $totalGananciasMes = (float) $gananciasConValor->sum('total');

                        foreach ($gananciasConValor as $index => $item) {
                            $portion = $totalGananciasMes > 0 ? ($item['total'] / $totalGananciasMes) * 360 : 0;
                            $start = $currentDeg;
                            $end = $currentDeg + $portion;
                            $color = $pieColors[$index % count($pieColors)];
                            $pieSegments[] = "{$color} {$start}deg {$end}deg";
                            $currentDeg = $end;
                        }

                        $pieBackground = 'conic-gradient(' . implode(', ', $pieSegments) . ')';
                    @endphp

                    <div class="dashboard-pie-layout">
                        <div class="dashboard-pie-card">
                            <div class="dashboard-pie-chart" style="background: {{ $pieBackground }};">
                                <div class="dashboard-pie-center">
                                    <span>Total mes</span>
                                    <strong>${{ number_format($totalGananciasMes, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-pie-legend">
                            @foreach ($gananciasConValor as $index => $item)
                                <div class="dashboard-pie-legend-item">
                                    <span class="dashboard-pie-legend-color" style="background-color: {{ $pieColors[$index % count($pieColors)] }};"></span>
                                    <div class="dashboard-pie-legend-text">
                                        <strong>{{ \Carbon\Carbon::parse($item['fecha'])->format('d/m') }}</strong>
                                        <span>${{ number_format($item['total'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
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
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap">
                    <div>
                        <h3 class="h5 mb-1">Reservas realizadas este mes por cancha</h3>
                        <p class="text-muted mb-0">Conteo de reservas activas registradas en {{ ucfirst($monthLabel) }}.</p>
                    </div>
                    <span class="badge text-bg-light border">{{ $reservasPorCancha->sum('total') }} reservas</span>
                </div>

                @if ($reservasPorCancha->isEmpty() || $maxReservasCancha === 0)
                    <div class="alert alert-light border mb-0">Todavia no hay reservas este mes para comparar por cancha.</div>
                @else
                    <div class="d-grid gap-3">
                        @foreach ($reservasPorCancha as $item)
                            @php
                                $width = $maxReservasCancha > 0 ? max(4, ($item['total'] / $maxReservasCancha) * 100) : 0;
                            @endphp
                            <div class="dashboard-bar-row">
                                <div class="dashboard-bar-header">
                                    <span>{{ $item['nombre'] }}</span>
                                    <strong>{{ $item['total'] }}</strong>
                                </div>
                                <div class="dashboard-bar-track">
                                    <div class="dashboard-bar-fill" style="width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
