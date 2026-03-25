@extends('layouts.app')

@section('content')
<h3 class="mb-3">Editar reserva #{{ $reserva->id }}</h3>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('reservas.update', $reserva) }}" method="POST">
            @csrf
            @method('PUT')
            @include('reservas.form')

            <div class="form-actions mt-4">
                <button class="btn btn-rs-action btn-rs-action-primary">Actualizar</button>
                <a href="{{ route('reservas.index') }}" class="btn btn-rs-action btn-rs-action-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>
@endsection
