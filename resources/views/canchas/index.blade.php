@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1">Canchas</h3>
        <p class="text-muted mb-0">Administra canchas principales, divisiones, horarios, precios y estados operativos.</p>
    </div>
    <a href="{{ route('canchas.create') }}" class="btn btn-primary btn-sm">+ Nueva cancha</a>
</div>

@if (session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Jerarquía</th>
                    <th>Horario</th>
                    <th>Precio/hora</th>
                    <th>Estado</th>
                    <th>Reservas</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($canchas as $cancha)
                    <tr>
                        <td>
                            <strong>{{ $cancha->nombre }}</strong>
                            <div class="text-muted small">{{ ucfirst($cancha->tipo) }}</div>
                            @if ($cancha->parent)
                                <div class="text-muted small">Pertenece a {{ $cancha->parent->nombre }}</div>
                            @elseif ($cancha->children_count > 0)
                                <div class="text-muted small">{{ $cancha->children_count }} divisiones configuradas</div>
                            @endif
                        </td>
                        <td>{{ $cancha->tipo_jerarquia }}</td>
                        <td>{{ substr((string) $cancha->hora_apertura, 0, 5) }} - {{ substr((string) $cancha->hora_cierre, 0, 5) }}</td>
                        <td>${{ number_format((float) $cancha->precio_hora, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge text-bg-{{ $cancha->estado_legible === 'Disponible' ? 'success' : ($cancha->estado_legible === 'Mantenimiento' ? 'warning' : 'secondary') }}">
                                {{ $cancha->estado_legible }}
                            </span>
                        </td>
                        <td>{{ $cancha->reservas_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('canchas.edit', $cancha) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="{{ route('canchas.destroy', $cancha) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar cancha?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Todavía no hay canchas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
