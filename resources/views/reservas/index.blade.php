@extends('layouts.app')

@section('page-class', 'page-reservas-index')

@section('content')
<div class="reservas-header d-flex justify-content-between align-items-center mb-3">
    <h3>Reservas</h3>
    <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-sm">
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
                        <td>${{ number_format((float) $reserva->precio, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge text-bg-{{ $reserva->estado === 'pagada' ? 'success' : ($reserva->estado === 'cancelada' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="{{ route('reservas.destroy', $reserva) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar reserva?')">
                                    Eliminar
                                </button>
                            </form>
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
