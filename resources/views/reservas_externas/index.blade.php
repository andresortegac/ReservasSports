@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Reservas de usuarios externas EdwinSport</h3>

    <a href="{{ route('reservas.externas.create') }}" class="btn btn-primary btn-sm">
        + Nueva reserva externa
    </a>
</div>

@if (session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

@if (!empty($externalError))
    <div class="alert alert-warning">{{ $externalError }}</div>
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
                    <th>Teléfono</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Creada</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($reservas as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->cancha_id }}</td>
                    <td>{{ $r->numero_subcancha }}</td>
                    <td>{{ $r->nombre_cliente }}</td>
                    <td>{{ $r->telefono_cliente }}</td>
                    <td>{{ $r->fecha }}</td>
                    <td>{{ $r->hora }}</td>
                    <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <form action="{{ route('reservas.externas.destroy', $r) }}" method="POST">
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
                    <td colspan="9" class="text-center text-muted py-4">
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
