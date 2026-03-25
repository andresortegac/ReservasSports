@extends('layouts.app')

@section('page-class', 'page-ventas-index')

@section('content')
<h3 class="mb-3">Ventas</h3>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Hoy</small>
                <h4 class="ventas-total">${{ number_format($ventasDia,0,',','.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Esta semana</small>
                <h4 class="ventas-total">${{ number_format($ventasSemana,0,',','.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Este mes</small>
                <h4 class="ventas-total">${{ number_format($ventasMes,0,',','.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <h6>Detalle de ventas de hoy</h6>
        <table class="table table-striped ventas-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Cancha</th>
                    <th>Hora</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detalleDia as $r)
                    <tr>
                        <td>{{ $r->cliente->nombre ?? 'N/A' }}</td>
                        <td>{{ $r->cancha_id }}</td>
                        <td>{{ $r->hora_inicio }} - {{ $r->hora_fin }}</td>
                        <td>${{ number_format($r->precio,0,',','.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No hay ventas hoy.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
