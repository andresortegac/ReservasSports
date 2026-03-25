@php
    $tipoCancha = old('tipo', $cancha->tipo ?? 'independiente');
    $estadoOperativo = old('estado_operativo', $cancha->estado_operativo ?? 'disponible');
    $diasDisponibles = \App\Models\Cancha::diasSemana();
    $diasOperacion = old('dias_operacion', $cancha->dias_operacion ?? array_keys($diasDisponibles));
    $bloquesHorarios = old('bloques_horarios', $cancha->bloques_horarios ?? [['inicio' => '06:00', 'fin' => '12:00']]);

    if (empty($bloquesHorarios)) {
        $bloquesHorarios = [['inicio' => '06:00', 'fin' => '12:00']];
    }
@endphp

<div class="alert alert-light border mb-4">
    <h6 class="mb-2">Configura la cancha segun su funcionamiento</h6>
    <div class="small text-muted">
        Las reservas siempre duran 1 hora y solo pueden empezar en punto. Usa uno o varios bloques horarios para
        definir exactamente en que horas se puede reservar cada cancha.
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            class="form-control"
            value="{{ old('nombre', $cancha->nombre) }}"
            required
        >
    </div>

    <div class="col-md-3">
        <label for="tipo" class="form-label">Tipo de cancha</label>
        <select id="tipo" name="tipo" class="form-select" required>
            <option value="independiente" {{ $tipoCancha === 'independiente' ? 'selected' : '' }}>
                Cancha independiente
            </option>
            <option value="con_divisiones" {{ $tipoCancha === 'con_divisiones' ? 'selected' : '' }}>
                Cancha con divisiones
            </option>
            <option value="subcancha" {{ $tipoCancha === 'subcancha' ? 'selected' : '' }}>
                Subcancha
            </option>
        </select>
    </div>

    <div class="col-md-3">
        <label for="precio_hora" class="form-label">Precio por hora</label>
        <input
            type="number"
            id="precio_hora"
            name="precio_hora"
            class="form-control"
            value="{{ old('precio_hora', $cancha->precio_hora) }}"
            min="0"
            step="0.01"
            required
        >
    </div>

    <div class="col-12">
        <div id="tipo-help" class="alert alert-info small mb-0">
            <strong>Cancha independiente:</strong> se reserva como una sola cancha y no tiene subcanchas.
        </div>
    </div>

    <div class="col-md-6" id="parent-group">
        <label for="parent_id" class="form-label">Cancha con divisiones</label>
        <select id="parent_id" name="parent_id" class="form-select">
            <option value="">Selecciona una cancha principal</option>
            @foreach ($canchasPadre as $canchaPadre)
                <option
                    value="{{ $canchaPadre->id }}"
                    {{ (string) old('parent_id', $cancha->parent_id) === (string) $canchaPadre->id ? 'selected' : '' }}
                >
                    {{ $canchaPadre->nombre }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Elige aqui la cancha grande a la que pertenece esta subcancha.</div>
    </div>

    <div class="col-md-2" id="orden-group">
        <label for="orden" class="form-label">Orden</label>
        <input
            type="number"
            id="orden"
            name="orden"
            class="form-control"
            value="{{ old('orden', $cancha->orden ?? 1) }}"
            min="1"
            max="99"
        >
        <div class="form-text">Sirve para identificar subcancha 1, 2, 3, etc.</div>
    </div>

    <div class="col-md-4">
        <label for="estado_operativo" class="form-label">Estado operativo</label>
        <select id="estado_operativo" name="estado_operativo" class="form-select" required>
            <option value="disponible" {{ $estadoOperativo === 'disponible' ? 'selected' : '' }}>Disponible</option>
            <option value="mantenimiento" {{ $estadoOperativo === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
            <option value="fuera_de_servicio" {{ $estadoOperativo === 'fuera_de_servicio' ? 'selected' : '' }}>Fuera de servicio</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label d-block mb-2">Dias de funcionamiento</label>
        <div class="form-text mb-2">
            Marca los dias en los que esta cancha acepta reservas. Si un dia no esta marcado, no aparecera disponible.
        </div>
        <div class="row g-2">
            @foreach ($diasDisponibles as $valor => $etiqueta)
                <div class="col-md-3 col-sm-4 col-6">
                    <label class="form-check border rounded-3 px-3 py-2 h-100">
                        <input
                            type="checkbox"
                            name="dias_operacion[]"
                            value="{{ $valor }}"
                            class="form-check-input me-2"
                            {{ in_array($valor, $diasOperacion, true) ? 'checked' : '' }}
                        >
                        <span class="form-check-label">{{ $etiqueta }}</span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <label class="form-label mb-0">Bloques horarios</label>
                <div class="form-text">
                    Agrega todos los rangos en los que la cancha estara disponible. Ejemplo: manana, tarde y noche.
                </div>
            </div>
            <button type="button" id="agregar-bloque" class="btn btn-outline-primary btn-sm">
                + Agregar otro bloque horario
            </button>
        </div>

        <div id="bloques-container" class="d-grid gap-2">
            @foreach ($bloquesHorarios as $index => $bloque)
                <div class="row g-2 align-items-end border rounded-3 p-3 bg-light-subtle" data-bloque-row>
                    <div class="col-md-5">
                        <label class="form-label">Hora inicio</label>
                        <input
                            type="time"
                            name="bloques_horarios[{{ $index }}][inicio]"
                            class="form-control"
                            step="3600"
                            value="{{ substr((string) ($bloque['inicio'] ?? ''), 0, 5) }}"
                            required
                        >
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Hora fin</label>
                        <input
                            type="time"
                            name="bloques_horarios[{{ $index }}][fin]"
                            class="form-control"
                            step="3600"
                            value="{{ substr((string) ($bloque['fin'] ?? ''), 0, 5) }}"
                            required
                        >
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100" data-remove-bloque>
                            Quitar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12">
        <label for="descripcion" class="form-label">Descripcion</label>
        <textarea
            id="descripcion"
            name="descripcion"
            class="form-control"
            rows="3"
            placeholder="Notas operativas, uso para torneos, observaciones o reglas especiales..."
        >{{ old('descripcion', $cancha->descripcion) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo');
    const tipoHelp = document.getElementById('tipo-help');
    const parentGroup = document.getElementById('parent-group');
    const ordenGroup = document.getElementById('orden-group');
    const parentSelect = document.getElementById('parent_id');
    const ordenInput = document.getElementById('orden');
    const bloquesContainer = document.getElementById('bloques-container');
    const agregarBloque = document.getElementById('agregar-bloque');

    if (!tipoSelect || !tipoHelp || !parentGroup || !ordenGroup || !parentSelect || !ordenInput || !bloquesContainer || !agregarBloque) {
        return;
    }

    const helpTexts = {
        independiente: '<strong>Cancha independiente:</strong> se reserva completa y no depende de otras canchas.',
        con_divisiones: '<strong>Cancha con divisiones:</strong> es una cancha grande que puede bloquear a sus subcanchas cuando se reserve completa.',
        subcancha: '<strong>Subcancha:</strong> es una division reservable dentro de una cancha grande y bloquea la cancha principal en el mismo horario.'
    };

    function syncBlockNames() {
        bloquesContainer.querySelectorAll('[data-bloque-row]').forEach(function (row, index) {
            const timeInputs = row.querySelectorAll('input[type="time"]');
            const inicio = timeInputs[0] || null;
            const fin = timeInputs[1] || null;

            if (inicio) {
                inicio.name = 'bloques_horarios[' + index + '][inicio]';
            }

            if (fin) {
                fin.name = 'bloques_horarios[' + index + '][fin]';
            }
        });
    }

    function addBlockRow(inicio, fin) {
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end border rounded-3 p-3 bg-light-subtle';
        row.setAttribute('data-bloque-row', '');
        row.innerHTML = `
            <div class="col-md-5">
                <label class="form-label">Hora inicio</label>
                <input type="time" class="form-control" step="3600" value="${inicio || ''}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Hora fin</label>
                <input type="time" class="form-control" step="3600" value="${fin || ''}" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100" data-remove-bloque>Quitar</button>
            </div>
        `;

        bloquesContainer.appendChild(row);
        syncBlockNames();
    }

    function ensureAtLeastOneRow() {
        if (!bloquesContainer.querySelector('[data-bloque-row]')) {
            addBlockRow('06:00', '12:00');
        }
    }

    function updateTipoFields() {
        const tipo = tipoSelect.value;
        const isSubcancha = tipo === 'subcancha';

        tipoHelp.innerHTML = helpTexts[tipo] || helpTexts.independiente;
        parentGroup.style.display = isSubcancha ? '' : 'none';
        ordenGroup.style.display = isSubcancha ? '' : 'none';
        parentSelect.required = isSubcancha;
        ordenInput.required = isSubcancha;

        if (!isSubcancha) {
            parentSelect.value = '';
            if (!ordenInput.value) {
                ordenInput.value = '1';
            }
        }
    }

    agregarBloque.addEventListener('click', function () {
        addBlockRow('', '');
    });

    bloquesContainer.addEventListener('click', function (event) {
        const button = event.target.closest('[data-remove-bloque]');
        if (!button) {
            return;
        }

        const row = button.closest('[data-bloque-row]');
        if (row) {
            row.remove();
            syncBlockNames();
            ensureAtLeastOneRow();
        }
    });

    syncBlockNames();
    ensureAtLeastOneRow();
    updateTipoFields();
    tipoSelect.addEventListener('change', updateTipoFields);
});
</script>
@endpush
