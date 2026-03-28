<div class="row g-3">
    <div class="col-md-6">
        @if ($canchas->count() === 1)
            @php($canchaTenant = $canchas->first())
            <label class="form-label">Cancha</label>
            <input type="hidden" name="cancha_id" value="{{ $canchaTenant->id }}">
            <div class="form-control bg-light">{{ $canchaTenant->nombre }}</div>
            <div class="form-text">La cancha se resolvio automaticamente por el tenant actual.</div>
        @else
            <label class="form-label">Cancha</label>
            <select name="cancha_id" class="form-select" required>
                <option value="">Selecciona una cancha</option>
                @foreach ($canchas as $cancha)
                    <option value="{{ $cancha->id }}" {{ (string) old('cancha_id', $reserva->cancha_id) === (string) $cancha->id ? 'selected' : '' }}>
                        {{ $cancha->nombre }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">Subcancha #</label>
        <input type="number" name="numero_subcancha" class="form-control" value="{{ old('numero_subcancha', $reserva->numero_subcancha ?? 1) }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Hora</label>
        <input type="time" name="hora" class="form-control" value="{{ old('hora', isset($reserva->hora) ? substr((string) $reserva->hora, 0, 5) : '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nombre cliente</label>
        <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente', $reserva->nombre_cliente) }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Telefono cliente</label>
        <input type="text" name="telefono_cliente" class="form-control" value="{{ old('telefono_cliente', $reserva->telefono_cliente) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', optional($reserva->fecha)->toDateString() ?? $reserva->fecha) }}" required>
    </div>
</div>
