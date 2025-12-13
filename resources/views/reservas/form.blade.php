<div class="row g-3">

    <div class="col-md-4">
        <label class="form-label">Cliente</label>
        <select name="cliente_id" class="form-select" required>
            <option value="">-- Selecciona un cliente --</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}"
                    {{ old('cliente_id', $reserva->cliente_id ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->nombre }} -
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Cancha</label>
        <input type="text" class="form-control" value="Cancha 1" disabled>
        <input type="hidden" name="cancha_id"
               value="{{ old('cancha_id', $reserva->cancha_id ?? 1) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Subcancha</label>
       @php
    $subc = old('subcancha', $reserva->subcancha ?? 1);
@endphp
<select name="subcancha" class="form-select" required>
    <option value="1" {{ (int)$subc === 1 ? 'selected' : '' }}>1</option>
    <option value="2" {{ (int)$subc === 2 ? 'selected' : '' }}>2</option>
</select>

    </div>

    <div class="col-md-2">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control"
               value="{{ old('fecha', $reserva->fecha ?? '') }}" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">Hora de reserva</label>
        <select name="hora" id="hora-select" class="form-select" required>
            <option value="">Seleccione primero fecha y subcancha</option>
        </select>
        @error('hora')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Precio</label>
        <input type="number" name="precio" step="0.01" class="form-control"
               value="{{ old('precio', $reserva->precio ?? 0) }}" min="0" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">Estado</label>
        @php $estado = old('estado', $reserva->estado ?? 'pendiente'); @endphp
        <select name="estado" class="form-select" required>
            <option value="pendiente" {{ $estado=='pendiente'?'selected':'' }}>Pendiente</option>
            <option value="pagada" {{ $estado=='pagada'?'selected':'' }}>Pagada</option>
            <option value="cancelada" {{ $estado=='cancelada'?'selected':'' }}>Cancelada</option>
        </select>
    </div>

</div>
