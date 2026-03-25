@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Editar cancha</h3>
    <a href="{{ route('canchas.index') }}" class="btn btn-secondary">Volver</a>
</div>

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
        <form action="{{ route('canchas.update', $cancha) }}" method="POST">
            @csrf
            @method('PUT')
            @include('canchas.form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Actualizar cancha</button>
                <a href="{{ route('canchas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
