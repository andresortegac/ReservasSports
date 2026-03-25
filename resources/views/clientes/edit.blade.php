@extends('layouts.app')

@section('content')
<h3 class="mb-3">Editar cliente</h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('clientes.update', $cliente) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cliente->nombre) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}">
                </div>
            </div>

            <div class="form-actions mt-4">
                <button class="btn btn-rs-action btn-rs-action-primary">Actualizar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-rs-action btn-rs-action-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
