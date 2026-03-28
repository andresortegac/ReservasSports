@extends('layouts.app')

@section('page-class', 'page-reservas-index')

@section('content')
<div class="reservas-header d-flex justify-content-between align-items-center mb-3">
    <h3>Reservas</h3>
    <a href="{{ route('reservas.create') }}" class="btn btn-rs-action btn-rs-action-primary">
        + Nueva reserva
    </a>
</div>

@if (session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
@endif

@if (session('premio'))
    <div class="alert alert-info">{{ session('premio') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle reservas-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Cancha</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    <tr>
                        <td>{{ $reserva->id }}</td>
                        <td>{{ $reserva->cliente->nombre ?? 'N/A' }}</td>
                        <td>{{ $reserva->cancha?->nombre_completo ?? "Cancha #{$reserva->cancha_id}" }}</td>
                        <td>{{ optional($reserva->fecha)->format('Y-m-d') }}</td>
                        <td>{{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</td>
                        <td>
                            <div>${{ number_format((float) $reserva->precio, 0, ',', '.') }}</div>
                            @if ((float) ($reserva->descuento ?? 0) > 0)
                                <div class="text-muted small">Desc. ${{ number_format((float) $reserva->descuento, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $estadoVisual = $reserva->estado_pago === 'parcial' ? 'abonado' : $reserva->estado;
                                $badgeClass = $estadoVisual === 'pagada'
                                    ? 'success'
                                    : ($estadoVisual === 'abonado'
                                        ? 'info'
                                        : ($estadoVisual === 'cancelada' ? 'secondary' : 'warning'));
                            @endphp
                            <span class="badge text-bg-{{ $badgeClass }}">
                                {{ ucfirst($estadoVisual) }}
                            </span>
                            @if ((float) ($reserva->anticipo ?? 0) > 0 && (float) ($reserva->saldo_pendiente ?? 0) > 0)
                                <div class="text-muted small">
                                    Abono ${{ number_format((float) $reserva->anticipo, 0, ',', '.') }}
                                    · Saldo ${{ number_format((float) $reserva->saldo_pendiente, 0, ',', '.') }}
                                </div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-rs-action btn-rs-action-edit">Editar</a>
                                <form action="{{ route('reservas.destroy', $reserva) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-rs-action btn-rs-action-delete" onclick="return confirm('¿Eliminar reserva?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Todavía no hay reservas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $reservas->links() }}
    </div>
</div>
@endsection
