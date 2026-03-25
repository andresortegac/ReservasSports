<div class="alert alert-light border mb-4">
    Crea primero la cancha principal. Si esa cancha se puede dividir, registra cada división como una cancha nueva
    seleccionando la principal en el campo <strong>Cancha principal</strong>.
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
        <label for="tipo" class="form-label">Tipo</label>
        <input
            type="text"
            id="tipo"
            name="tipo"
            class="form-control"
            value="{{ old('tipo', $cancha->tipo) }}"
            placeholder="futbol, microfutbol, padel..."
            required
        >
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

    <div class="col-md-4">
        <label for="parent_id" class="form-label">Cancha principal</label>
        <select id="parent_id" name="parent_id" class="form-select">
            <option value="">Cancha independiente o principal</option>
            @foreach ($canchasPadre as $canchaPadre)
                <option
                    value="{{ $canchaPadre->id }}"
                    {{ (string) old('parent_id', $cancha->parent_id) === (string) $canchaPadre->id ? 'selected' : '' }}
                >
                    {{ $canchaPadre->nombre }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Si eliges una cancha principal, este registro funcionará como división o subcancha.</div>
    </div>

    <div class="col-md-2">
        <label for="orden" class="form-label">Orden</label>
        <input
            type="number"
            id="orden"
            name="orden"
            class="form-control"
            value="{{ old('orden', $cancha->orden ?? 1) }}"
            min="1"
            max="99"
            required
        >
    </div>

    <div class="col-md-3">
        <label for="hora_apertura" class="form-label">Hora apertura</label>
        <input
            type="time"
            id="hora_apertura"
            name="hora_apertura"
            class="form-control"
            value="{{ old('hora_apertura', substr((string) $cancha->hora_apertura, 0, 5)) }}"
            required
        >
    </div>

    <div class="col-md-3">
        <label for="hora_cierre" class="form-label">Hora cierre</label>
        <input
            type="time"
            id="hora_cierre"
            name="hora_cierre"
            class="form-control"
            value="{{ old('hora_cierre', substr((string) $cancha->hora_cierre, 0, 5)) }}"
            required
        >
    </div>

    <div class="col-md-3">
        <label for="intervalo_minutos" class="form-label">Intervalo</label>
        <select id="intervalo_minutos" name="intervalo_minutos" class="form-select" required>
            @foreach ([30, 60, 90, 120] as $intervalo)
                <option
                    value="{{ $intervalo }}"
                    {{ (int) old('intervalo_minutos', $cancha->intervalo_minutos) === $intervalo ? 'selected' : '' }}
                >
                    {{ $intervalo }} minutos
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="estado_operativo" class="form-label">Estado operativo</label>
        @php $estadoOperativo = old('estado_operativo', $cancha->estado_operativo); @endphp
        <select id="estado_operativo" name="estado_operativo" class="form-select" required>
            <option value="disponible" {{ $estadoOperativo === 'disponible' ? 'selected' : '' }}>Disponible</option>
            <option value="mantenimiento" {{ $estadoOperativo === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check border rounded-3 px-3 py-2 w-100">
            <input
                type="checkbox"
                id="activa"
                name="activa"
                value="1"
                class="form-check-input"
                {{ old('activa', $cancha->activa) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="activa">Cancha activa</label>
        </div>
    </div>

    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea
            id="descripcion"
            name="descripcion"
            class="form-control"
            rows="3"
            placeholder="Notas operativas, tipo de uso, si es para torneos, observaciones..."
        >{{ old('descripcion', $cancha->descripcion) }}</textarea>
    </div>
</div>
