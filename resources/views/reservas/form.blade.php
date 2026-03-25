@php
    $selectedCanchaId = old('cancha_id', $reserva->cancha_id ?? null);
    $selectedCancha = $canchas->firstWhere('id', $selectedCanchaId);
    $precioBase = old('precio', $reserva->precio ?? $selectedCancha?->precio_hora ?? 0);
    $estado = old('estado', $reserva->estado ?? 'pendiente');
    $oldHora = old('hora', $reserva->hora_inicio ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label" for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" class="form-select" required>
            <option value="">-- Selecciona un cliente --</option>
            @foreach ($clientes as $cliente)
                <option
                    value="{{ $cliente->id }}"
                    {{ (string) old('cliente_id', $reserva->cliente_id ?? '') === (string) $cliente->id ? 'selected' : '' }}
                >
                    {{ $cliente->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="cancha-select">Cancha</label>
        <select name="cancha_id" id="cancha-select" class="form-select" required>
            <option value="">Seleccione una cancha</option>
            @foreach ($canchas as $cancha)
                @php
                    $tipoJerarquia = $cancha->tipo_jerarquia;
                    $estadoLegible = $cancha->estado_legible;
                @endphp
                <option
                    value="{{ $cancha->id }}"
                    data-precio="{{ $cancha->precio_hora }}"
                    data-apertura="{{ substr((string) $cancha->hora_apertura, 0, 5) }}"
                    data-cierre="{{ substr((string) $cancha->hora_cierre, 0, 5) }}"
                    data-intervalo="{{ $cancha->intervalo_minutos }}"
                    data-estado="{{ $estadoLegible }}"
                    {{ (string) $selectedCanchaId === (string) $cancha->id ? 'selected' : '' }}
                >
                    {{ $cancha->nombre_completo }} · {{ $tipoJerarquia }}
                </option>
            @endforeach
        </select>
        <div id="cancha-meta" class="form-text">
            Selecciona una cancha para cargar horarios y precio base.
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label" for="fecha">Fecha</label>
        <input
            type="date"
            name="fecha"
            id="fecha"
            class="form-control"
            value="{{ old('fecha', optional($reserva->fecha)->format('Y-m-d')) }}"
            required
        >
    </div>

    <div class="col-md-3">
        <label class="form-label" for="hora-select">Hora de reserva</label>
        <select name="hora" id="hora-select" class="form-select" required>
            <option value="">{{ $selectedCanchaId ? 'Cargando horarios...' : 'Seleccione primero una cancha' }}</option>
        </select>
        @error('hora')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="precio">Precio</label>
        <input
            type="number"
            name="precio"
            id="precio"
            step="0.01"
            class="form-control"
            value="{{ $precioBase }}"
            min="0"
            required
        >
    </div>

    <div class="col-md-3">
        <label class="form-label" for="estado">Estado</label>
        <select name="estado" id="estado" class="form-select" required>
            <option value="pendiente" {{ $estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="confirmada" {{ $estado === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
            <option value="pagada" {{ $estado === 'pagada' ? 'selected' : '' }}>Pagada</option>
            <option value="cancelada" {{ $estado === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label" for="notas">Notas</label>
        <textarea name="notas" id="notas" class="form-control" rows="2" placeholder="Opcional">{{ old('notas', $reserva->notas ?? '') }}</textarea>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canchaSelect = document.getElementById('cancha-select');
    const fechaInput = document.getElementById('fecha');
    const horaSelect = document.getElementById('hora-select');
    const precioInput = document.getElementById('precio');
    const canchaMeta = document.getElementById('cancha-meta');
    const oldHora = @json($oldHora);
    const isEditMode = @json((bool) $reserva->exists);
    const ignoreReservationId = @json($reserva->id ?? null);

    if (!canchaSelect || !fechaInput || !horaSelect || !precioInput || !canchaMeta) {
        return;
    }

    function selectedOption() {
        return canchaSelect.options[canchaSelect.selectedIndex] || null;
    }

    function updateCanchaMeta(forcePriceUpdate) {
        const option = selectedOption();

        if (!option || !option.value) {
            canchaMeta.textContent = 'Selecciona una cancha para cargar horarios y precio base.';
            return;
        }

        canchaMeta.textContent =
            'Horario ' + option.dataset.apertura +
            ' - ' + option.dataset.cierre +
            ' · Intervalo ' + option.dataset.intervalo +
            ' min · Estado ' + option.dataset.estado;

        if (forcePriceUpdate || (!isEditMode && (!precioInput.value || Number(precioInput.value) === 0))) {
            precioInput.value = option.dataset.precio || 0;
        }
    }

    async function loadHours() {
        const option = selectedOption();
        if (!option || !option.value || !fechaInput.value) {
            horaSelect.innerHTML = '<option value="">Seleccione cancha y fecha</option>';
            return;
        }

        const url = new URL(@json(route('reservas.horas-disponibles')), window.location.origin);
        url.searchParams.set('cancha_id', option.value);
        url.searchParams.set('fecha', fechaInput.value);
        if (ignoreReservationId) {
            url.searchParams.set('ignore_reserva_id', ignoreReservationId);
        }

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar los horarios.');
            }

            const hours = await response.json();
            horaSelect.innerHTML = '';

            if (!hours.length) {
                horaSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                return;
            }

            horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';

            hours.forEach(function (hour) {
                const optionElement = document.createElement('option');
                optionElement.value = hour;
                optionElement.textContent = hour;
                if (oldHora === hour) {
                    optionElement.selected = true;
                }
                horaSelect.appendChild(optionElement);
            });
        } catch (error) {
            console.error(error);
            horaSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
        }
    }

    canchaSelect.addEventListener('change', function () {
        updateCanchaMeta(true);
        loadHours();
    });

    fechaInput.addEventListener('change', loadHours);

    updateCanchaMeta(false);
    if (canchaSelect.value && fechaInput.value) {
        loadHours();
    }
});
</script>
@endpush
