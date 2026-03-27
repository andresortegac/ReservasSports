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
                @php
                    $timeSlots = collect($cancha->bloques_horarios ?? [])
                        ->flatMap(function ($block) {
                            $inicio = substr((string) ($block['inicio'] ?? ''), 0, 5);
                            $fin = substr((string) ($block['fin'] ?? ''), 0, 5);

                            if ($inicio === '' || $fin === '') {
                                return [];
                            }

                            $inicioMinutos = ((int) substr($inicio, 0, 2) * 60) + (int) substr($inicio, 3, 2);
                            $finMinutos = ((int) substr($fin, 0, 2) * 60) + (int) substr($fin, 3, 2);

                            if ($inicioMinutos >= $finMinutos) {
                                return [];
                            }

                            $slots = [];
                            for ($minutos = $inicioMinutos; $minutos < $finMinutos; $minutos += 60) {
                                $slots[] = sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
                            }

                            return $slots;
                        })
                        ->unique()
                        ->values();
                @endphp
                <option
                    value="{{ $cancha->id }}"
                    data-precio="{{ $cancha->precio_hora }}"
                    data-bloques="{{ $cancha->bloques_horarios_legibles }}"
                    data-dias="{{ $cancha->dias_operacion_legibles }}"
                    data-estado="{{ $cancha->estado_legible }}"
                    data-tipo="{{ $cancha->tipo_jerarquia }}"
                    data-slots='@json($timeSlots)'
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

    <div class="col-12">
        <div class="border rounded-3 p-3 bg-light-subtle">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                <div>
                    <div class="fw-semibold">Horarios disponibles</div>
                    <div id="availability-status" class="text-muted small">
                        Selecciona una cancha y una fecha para consultar disponibilidad.
                    </div>
                </div>
                <span id="availability-count" class="badge text-bg-secondary align-self-start align-self-lg-center">
                    Sin consulta
                </span>
            </div>

            <div id="availability-slots" class="d-flex flex-wrap gap-2">
                <span class="text-muted small">Todavia no hay horarios para mostrar.</span>
            </div>
        </div>
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
    const availabilityStatus = document.getElementById('availability-status');
    const availabilityCount = document.getElementById('availability-count');
    const availabilitySlots = document.getElementById('availability-slots');
    const oldHora = @json($oldHora);
    const ignoreReservationId = @json($reserva->id ?? null);
    let preferredHour = oldHora || '';
    let currentRequestId = 0;

    if (
        !canchaSelect ||
        !fechaInput ||
        !horaSelect ||
        !precioBaseInput ||
        !descuentoInput ||
        !precioTotalInput ||
        !canchaMeta ||
        !availabilityStatus ||
        !availabilityCount ||
        !availabilitySlots
    ) {
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

    function setAvailabilitySummary(message, countLabel) {
        availabilityStatus.textContent = message;
        availabilityCount.textContent = countLabel;
    }

    function configuredSlotsFor(option) {
        if (!option || !option.value) {
            return [];
        }

        try {
            const slots = JSON.parse(option.dataset.slots || '[]');
            return Array.isArray(slots) ? slots : [];
        } catch (error) {
            console.error(error);
            return [];
        }
    }

    function renderAvailabilityButtons(allHours, availableHours, selectedHour) {
        availabilitySlots.innerHTML = '';

        if (!allHours.length) {
            availabilitySlots.innerHTML = '<span class="text-muted small">No hay horarios disponibles para mostrar.</span>';
            return;
        }

        allHours.forEach(function (hour) {
            const isAvailable = availableHours.includes(hour);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm ' + (
                selectedHour === hour
                    ? 'btn-primary'
                    : (isAvailable ? 'btn-outline-primary' : 'btn-outline-secondary')
            );
            button.textContent = hour;
            button.dataset.hour = hour;

            if (isAvailable) {
                button.addEventListener('click', function () {
                    horaSelect.value = hour;
                    preferredHour = hour;
                    renderAvailabilityButtons(allHours, availableHours, hour);
                });
            } else {
                button.disabled = true;
                button.title = 'Horario reservado';
                button.setAttribute('aria-label', hour + ' reservado');
            }

            availabilitySlots.appendChild(button);
        });
    }

    function updateCanchaMeta(forceBaseUpdate) {
        const option = selectedOption();

        if (!option || !option.value) {
            canchaMeta.textContent = 'Selecciona una cancha para cargar horarios y precio base.';
            precioBaseInput.value = formatAmount(0);
            setAvailabilitySummary('Selecciona una cancha y una fecha para consultar disponibilidad.', 'Sin consulta');
            renderAvailabilityButtons([], [], '');
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
            setAvailabilitySummary('Selecciona una cancha y una fecha para consultar disponibilidad.', 'Sin consulta');
            renderAvailabilityButtons([], [], '');
            return;
        }

        currentRequestId += 1;
        const requestId = currentRequestId;
        const allHours = configuredSlotsFor(option);
        const url = new URL(@json(route('reservas.horas-disponibles')), window.location.origin);
        url.searchParams.set('cancha_id', option.value);
        url.searchParams.set('fecha', fechaInput.value);
        if (ignoreReservationId) {
            url.searchParams.set('ignore_reserva_id', ignoreReservationId);
        }

        horaSelect.innerHTML = '<option value="">Cargando horarios...</option>';
        setAvailabilitySummary('Consultando disponibilidad de la cancha seleccionada...', 'Cargando');
        availabilitySlots.innerHTML = '<span class="text-muted small">Consultando horarios disponibles...</span>';

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
            if (requestId !== currentRequestId) {
                return;
            }

            horaSelect.innerHTML = '';

            if (!hours.length) {
                horaSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                preferredHour = '';
                setAvailabilitySummary(
                    'No encontramos horarios libres para esta cancha en la fecha elegida. Los bloqueados indican que ya están reservados.',
                    '0 disponibles'
                );
                renderAvailabilityButtons(allHours, [], '');
                return;
            }

            horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';
            const selectedHour = hours.includes(horaSelect.value)
                ? horaSelect.value
                : (hours.includes(preferredHour) ? preferredHour : '');

            hours.forEach(function (hour) {
                const optionElement = document.createElement('option');
                optionElement.value = hour;
                optionElement.textContent = hour;
                if (selectedHour === hour) {
                    optionElement.selected = true;
                }
                horaSelect.appendChild(optionElement);
            });

            horaSelect.value = selectedHour;
            setAvailabilitySummary(
                'Puedes elegir los horarios libres. Los bloqueados indican que ya están reservados.',
                hours.length === 1 ? '1 disponible' : hours.length + ' disponibles'
            );
            renderAvailabilityButtons(allHours, hours, selectedHour);
        } catch (error) {
            console.error(error);
            horaSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
            preferredHour = '';
            setAvailabilitySummary('No fue posible cargar la disponibilidad en este momento.', 'Error');
            availabilitySlots.innerHTML = '<span class="text-danger small">Ocurrio un error consultando los horarios.</span>';
        }
    }

    canchaSelect.addEventListener('change', function () {
        preferredHour = '';
        updateCanchaMeta(true);
        loadHours();
    });

    fechaInput.addEventListener('change', loadHours);
    descuentoInput.addEventListener('input', updateTotal);
    horaSelect.addEventListener('change', function () {
        preferredHour = horaSelect.value;

        const hours = Array.from(horaSelect.options)
            .map(function (option) {
                return option.value;
            })
            .filter(Boolean);

        renderAvailabilityButtons(configuredSlotsFor(selectedOption()), hours, preferredHour);
    });

    updateCanchaMeta(false);
    if (canchaSelect.value && fechaInput.value) {
        loadHours();
    }
});
</script>
@endpush
