@extends('layouts.app')

@section('content')

<h3 class="mb-3">Nueva reserva Champios Clud</h3>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('reservas.store') }}" method="POST">
            @include('reservas.form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ route('reservas.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fechaInput      = document.querySelector('input[name="fecha"]');
    const subcanchaSelect = document.querySelector('select[name="subcancha"]');
    const canchaInput     = document.querySelector('input[name="cancha_id"]');
    const horaSelect      = document.querySelector('select[name="hora"]');

    if (!fechaInput || !subcanchaSelect || !horaSelect) return;

    const oldHora = "{{ old('hora') }}";

    async function cargarHoras() {
        const fecha     = fechaInput.value;
        const subcancha = subcanchaSelect.value;
        const canchaId  = canchaInput ? canchaInput.value : 1;

        if (!fecha || !subcancha) {
            horaSelect.innerHTML = '<option value="">Seleccione primero fecha y subcancha</option>';
            return;
        }

        const url = "{{ route('reservas.horas-disponibles') }}"
            + '?fecha=' + encodeURIComponent(fecha)
            + '&subcancha=' + encodeURIComponent(subcancha)
            + '&cancha_id=' + encodeURIComponent(canchaId);

        try {
            const resp = await fetch(url);
            if (!resp.ok) throw new Error(resp.status);

            const horas = await resp.json();
            horaSelect.innerHTML = '';

            if (!horas.length) {
                horaSelect.innerHTML = '<option>No hay horarios disponibles</option>';
                return;
            }

            horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';
            horas.forEach(h => {
                const opt = document.createElement('option');
                opt.value = h;
                opt.textContent = h;
                if (oldHora === h) opt.selected = true;
                horaSelect.appendChild(opt);
            });

        } catch (e) {
            console.error(e);
            horaSelect.innerHTML = '<option>Error al cargar horas</option>';
        }
    }

    fechaInput.addEventListener('change', cargarHoras);
    subcanchaSelect.addEventListener('change', cargarHoras);

    if (fechaInput.value && subcanchaSelect.value) {
        cargarHoras();
    }
});
</script>
@endpush
@endsection

