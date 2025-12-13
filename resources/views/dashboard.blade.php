@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Reservas externas</h5>
                <p>Consulta reservas guardadas en otra BD.</p>
                <a href="{{ route('reservas.externas.index') }}" class="btn btn-dark btn-sm">
                    Ver reservas
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Nueva reserva</h5>
                <p>Crear una reserva en tu BD principal.</p>
                <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-sm">
                    Hacer reserva
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Clientes</h5>
                <p>Gestiona clientes, y revisa cuántas canchas separaron.</p>
                <a href="{{ route('clientes.index') }}" class="btn btn-success btn-sm">
                    Ver clientes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
