@extends('layouts.app')

@section('content')
<h3 class="mb-3">Nueva reserva</h3>

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
        <form action="{{ route('reservas.store') }}" method="POST">
            @csrf
            @include('reservas.form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ route('reservas.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
