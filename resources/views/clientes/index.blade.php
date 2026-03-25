@extends('layouts.app')

@section('page-class', 'page-clientes-index')

@section('content')
<div class="clientes-header d-flex justify-content-between align-items-center mb-3">
    <h3>Clientes</h3>
    <a href="{{ route('clientes.create') }}" class="btn btn-success btn-sm">+ Nuevo cliente</a>
</div>

<form class="clientes-filters mb-2">
    <label class="me-2">Periodo:</label>
    <select name="periodo" onchange="this.form.submit()" class="form-select form-select-sm w-auto d-inline">
        <option value="mes" {{ $periodo == 'mes' ? 'selected' : '' }}>Este mes</option>
        <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta semana</option>
    </select>
</form>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-striped clientes-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Canchas separadas (periodo)</th>
                    <th>Total reservas</th>
                    <th>Premio</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($clientes as $c)
                @php
                    $countPeriodo = $c->reservas_periodo_count ?? 0;
                    $total = $c->reservas_total_count ?? 0;
                    $ganoGratis = ($total > 0 && $total % 11 === 0);
                    $faltan = 11 - ($total % 11);

                    if ($faltan === 11) {
                        $faltan = 10;
                    }
                @endphp

                <tr>
                    <td>{{ $c->nombre }}</td>
                    <td>{{ $c->telefono }}</td>
                    <td>{{ $c->email }}</td>
                    <td><span class="badge bg-dark">{{ $countPeriodo }}</span></td>
                    <td><span class="badge bg-primary">{{ $total }}</span></td>
                    <td>
                        @if ($ganoGratis)
                            <span class="badge bg-success">
                                ¡Tiene una reserva gratis disponible!
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                Faltan {{ $faltan }} reservas para la gratis
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="table-actions">
                            <a href="{{ route('clientes.edit', $c) }}" class="btn btn-rs-action btn-rs-action-edit">Editar</a>
                            <form action="{{ route('clientes.destroy', $c) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-rs-action btn-rs-action-delete" onclick="return confirm('¿Eliminar cliente?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $clientes->links() }}
    </div>
</div>
@endsection
