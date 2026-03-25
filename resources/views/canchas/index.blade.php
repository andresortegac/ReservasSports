@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1">Canchas</h3>
        <p class="text-muted mb-0">Administra tipos de cancha, bloques horarios, precios y estados operativos.</p>
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
                    <th>Tipo</th>
                    <th>Horarios</th>
                    <th>Precio/hora</th>
                    <th>Estado</th>
                    <th>Reservas</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($canchas as $cancha)
                    @php
                        $badgeClass = match ($cancha->estado_operativo) {
                            'mantenimiento' => 'warning',
                            'fuera_de_servicio' => 'secondary',
                            default => 'success',
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $cancha->nombre }}</strong>
                            @if ($cancha->parent)
                                <div class="text-muted small">Pertenece a {{ $cancha->parent->nombre }}</div>
                            @elseif ($cancha->tipo === 'con_divisiones')
                                <div class="text-muted small">{{ $cancha->children_count }} subcanchas configuradas</div>
                            @endif
                        </td>
                        <td>{{ $cancha->tipo_jerarquia }}</td>
                        <td>
                            <div>{{ $cancha->bloques_horarios_legibles }}</div>
                            <div class="text-muted small">{{ $cancha->dias_operacion_legibles }}</div>
                        </td>
                        <td>${{ number_format((float) $cancha->precio_hora, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge text-bg-{{ $badgeClass }}">
                                {{ $cancha->estado_legible }}
                            </span>
                        </td>
                        <td>{{ $cancha->reservas_count }}</td>
                        <td class="text-end">
                            <div class="table-actions">
                                <a href="{{ route('canchas.edit', $cancha) }}" class="btn btn-rs-action btn-rs-action-edit">Editar</a>
                                <form action="{{ route('canchas.destroy', $cancha) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-rs-action btn-rs-action-delete" onclick="return confirm('¿Eliminar cancha?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
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
