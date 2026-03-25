@php
    $selectedCanchaId = old('cancha_id', $reserva->cancha_id ?? null);
    $selectedCancha = $canchas->firstWhere('id', $selectedCanchaId);
    $descuento = old('descuento', $reserva->descuento ?? 0);
    $precioBaseVisual = old('precio_base_visual');

    if ($precioBaseVisual === null) {
        if ($reserva->exists && (string) $selectedCanchaId === (string) $reserva->cancha_id && (float) ($reserva->precio_base ?? 0) > 0) {
            $precioBaseVisual = $reserva->precio_base;
        } else {
            $precioBaseVisual = $selectedCancha?->precio_hora ?? 0;
        }
    }

    $precioTotalVisual = max(0, (float) $precioBaseVisual - (float) $descuento);

    if (
        $reserva->exists
        && (float) ($reserva->precio_base ?? 0) > 0
        && (float) ($reserva->precio ?? 0) === 0.0
        && (float) ($reserva->descuento ?? 0) === 0.0
        && (string) $selectedCanchaId === (string) $reserva->cancha_id
    ) {
        $precioTotalVisual = 0;
    }

    $estado = old('estado', $reserva->estado ?? 'confirmada');
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
                <option
                    value="{{ $cancha->id }}"
                    data-precio="{{ $cancha->precio_hora }}"
                    data-bloques="{{ $cancha->bloques_horarios_legibles }}"
                    data-dias="{{ $cancha->dias_operacion_legibles }}"
                    data-estado="{{ $cancha->estado_legible }}"
                    data-tipo="{{ $cancha->tipo_jerarquia }}"
                    {{ (string) $selectedCanchaId === (string) $cancha->id ? 'selected' : '' }}
                >
                    {{ $cancha->nombre_completo }} · {{ $cancha->tipo_jerarquia }}
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
        <div class="form-text">Solo se muestran horarios exactos de una hora y en punto.</div>
        @error('hora')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="precio-base">Precio base</label>
        <input
            type="number"
            id="precio-base"
            class="form-control"
            value="{{ number_format((float) $precioBaseVisual, 2, '.', '') }}"
            min="0"
            step="0.01"
            readonly
        >
        <div class="form-text">Este valor se toma automáticamente desde la cancha.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label" for="descuento">Descuento</label>
        <input
            type="number"
            name="descuento"
            id="descuento"
            step="0.01"
            class="form-control"
            value="{{ number_format((float) $descuento, 2, '.', '') }}"
            min="0"
        >
        <div class="form-text">Úsalo, por ejemplo, para eventos o tarifas especiales.</div>
        @error('descuento')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="precio-total">Total final</label>
        <input
            type="number"
            id="precio-total"
            class="form-control"
            value="{{ number_format((float) $precioTotalVisual, 2, '.', '') }}"
            min="0"
            step="0.01"
            readonly
        >
    </div>

    <div class="col-md-3">
        <label class="form-label" for="estado">Estado</label>
        <select name="estado" id="estado" class="form-select" required>
            @if ($reserva->exists)
                <option value="pendiente" {{ $estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            @endif
            <option value="confirmada" {{ $estado === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
            <option value="pagada" {{ $estado === 'pagada' ? 'selected' : '' }}>Pagada</option>
            @if ($reserva->exists)
                <option value="cancelada" {{ $estado === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            @endif
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
    const precioBaseInput = document.getElementById('precio-base');
    const descuentoInput = document.getElementById('descuento');
    const precioTotalInput = document.getElementById('precio-total');
    const canchaMeta = document.getElementById('cancha-meta');
    const oldHora = @json($oldHora);
    const ignoreReservationId = @json($reserva->id ?? null);

    if (!canchaSelect || !fechaInput || !horaSelect || !precioBaseInput || !descuentoInput || !precioTotalInput || !canchaMeta) {
        return;
    }

    function selectedOption() {
        return canchaSelect.options[canchaSelect.selectedIndex] || null;
    }

    function formatAmount(value) {
        return Number(value || 0).toFixed(2);
    }

    function updateTotal() {
        const precioBase = Number(precioBaseInput.value || 0);
        const descuento = Number(descuentoInput.value || 0);
        const total = Math.max(0, precioBase - descuento);

        precioTotalInput.value = formatAmount(total);
    }

    function updateCanchaMeta(forceBaseUpdate) {
        const option = selectedOption();

        if (!option || !option.value) {
            canchaMeta.textContent = 'Selecciona una cancha para cargar horarios y precio base.';
            precioBaseInput.value = formatAmount(0);
            updateTotal();
            return;
        }

        canchaMeta.textContent =
            option.dataset.tipo +
            ' · Días ' + option.dataset.dias +
            ' · Horarios ' + option.dataset.bloques +
            ' · Estado ' + option.dataset.estado;

        if (forceBaseUpdate) {
            precioBaseInput.value = formatAmount(option.dataset.precio || 0);
        }

        updateTotal();
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
    descuentoInput.addEventListener('input', updateTotal);

    updateCanchaMeta(false);
    if (canchaSelect.value && fechaInput.value) {
        loadHours();
    }
});
</script>
@endpush
