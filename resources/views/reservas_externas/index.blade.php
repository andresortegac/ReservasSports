@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1">Reservas de usuarios externas EdwinSport</h3>
        @if (!empty($tenantCancha))
            <div class="text-muted small">Filtrando por cancha del tenant: {{ $tenantCancha->nombre }}</div>
        @endif
    </div>


</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="estado" class="form-select">
            <option value="">Todos los estados</option>
            <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
            <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>Confirmadas</option>
            <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Canceladas</option>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-rs-action btn-rs-action-primary w-100">Filtrar</button>
    </div>
</form>

@if (session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cancha</th>
                    <th>Subcancha</th>
                    <th>Cliente</th>
                    <th>Telefono</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Creada</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($reservas as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->cancha->nombre ?? $r->cancha_id }}</td>
                    <td>{{ $r->numero_subcancha }}</td>
                    <td>{{ $r->nombre_cliente }}</td>
                    <td>{{ $r->telefono_cliente }}</td>
                    <td>{{ optional($r->fecha)->toDateString() }}</td>
                    <td>{{ substr((string) $r->hora, 0, 5) }}</td>
                    <td>
                        <span class="badge bg-{{ $r->estado === 'confirmada' ? 'success' : ($r->estado === 'cancelada' ? 'danger' : 'warning text-dark') }}">
                            {{ ucfirst($r->estado) }}
                        </span>
                    </td>
                    <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            @if ($r->estado === 'pendiente')
                                <form action="{{ route('reservas.externas.confirm', $r) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-rs-action btn-rs-action-primary">
                                        Confirmar
                                    </button>
                                </form>

                                <form action="{{ route('reservas.externas.cancel', $r) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="motivo_cancelacion" value="Solicitud cancelada por el administrador.">
                                    <button class="btn btn-rs-action btn-rs-action-secondary">
                                        Cancelar
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('reservas.externas.edit', $r) }}" class="btn btn-rs-action btn-rs-action-secondary">
                                Editar
                            </a>

                            <form action="{{ route('reservas.externas.destroy', $r) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-rs-action btn-rs-action-delete" onclick="return confirm('¿Eliminar reserva?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            @if ($reservas->isEmpty())
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        No hay datos para mostrar.
                    </td>
                </tr>
            @endif
            </tbody>
        </table>

        {{ $reservas->links() }}
    </div>
</div>
@endsection


