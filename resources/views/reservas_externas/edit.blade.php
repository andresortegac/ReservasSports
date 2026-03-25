@extends('layouts.app')

@section('content')
<h3 class="mb-3">Editar reserva externa #{{ $reserva->id }}</h3>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('reservas.externas.update', $reserva) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cancha ID</label>
                    <input type="number" name="cancha_id" class="form-control" value="{{ old('cancha_id', $reserva->cancha_id) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Subcancha #</label>
                    <input type="number" name="numero_subcancha" class="form-control" value="{{ old('numero_subcancha', $reserva->numero_subcancha) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nombre cliente</label>
                    <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente', $reserva->nombre_cliente) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Teléfono cliente</label>
                    <input type="text" name="telefono_cliente" class="form-control" value="{{ old('telefono_cliente', $reserva->telefono_cliente) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="{{ old('fecha', $reserva->fecha) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hora</label>
                    <input type="time" name="hora" class="form-control" value="{{ old('hora', $reserva->hora) }}" required>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button class="btn btn-rs-action btn-rs-action-primary">Actualizar</button>
                <a href="{{ route('reservas.externas.index') }}" class="btn btn-rs-action btn-rs-action-secondary">
                    Volver
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
