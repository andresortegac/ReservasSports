<?php

namespace App\Services;

use App\Models\Cancha;
use App\Models\Reserva;
use Carbon\Carbon;

class CanchaAvailabilityService
{
    public function relatedCanchaIds(Cancha $cancha): array
    {
        $ids = [$cancha->id];

        $currentParentId = $cancha->parent_id;
        while ($currentParentId) {
            $ids[] = $currentParentId;
            $currentParentId = Cancha::whereKey($currentParentId)->value('parent_id');
        }

        $ids = array_merge($ids, $this->descendantIds($cancha->id));

        return array_values(array_unique($ids));
    }

    public function availabilityIssue(Cancha $cancha): ?string
    {
        if (!$cancha->activa) {
            return "La cancha {$cancha->nombre} está inactiva.";
        }

        if ($cancha->estado_operativo === 'mantenimiento') {
            return "La cancha {$cancha->nombre} está en mantenimiento.";
        }

        if ($cancha->hora_apertura >= $cancha->hora_cierre) {
            return "La cancha {$cancha->nombre} no tiene un horario válido configurado.";
        }

        $ancestorId = $cancha->parent_id;
        while ($ancestorId) {
            $ancestor = Cancha::find($ancestorId);
            if (!$ancestor) {
                break;
            }

            if (!$ancestor->activa) {
                return "La cancha principal {$ancestor->nombre} está inactiva.";
            }

            if ($ancestor->estado_operativo === 'mantenimiento') {
                return "La cancha principal {$ancestor->nombre} está en mantenimiento.";
            }

            $ancestorId = $ancestor->parent_id;
        }

        foreach ($this->descendants($cancha->id) as $descendant) {
            if (!$descendant->activa) {
                return "No puedes reservar {$cancha->nombre} mientras {$descendant->nombre} esté inactiva.";
            }

            if ($descendant->estado_operativo === 'mantenimiento') {
                return "No puedes reservar {$cancha->nombre} mientras {$descendant->nombre} esté en mantenimiento.";
            }
        }

        return null;
    }

    public function availableTimes(Cancha $cancha, string $fecha, ?int $ignoreReservationId = null): array
    {
        if ($this->availabilityIssue($cancha)) {
            return [];
        }

        $slots = $this->timeSlotsFor($cancha);
        $reserved = $this->reservedTimes($cancha, $fecha, $ignoreReservationId);

        return array_values(array_diff($slots, $reserved));
    }

    public function conflictingReservation(
        Cancha $cancha,
        string $fecha,
        string $hora,
        ?int $ignoreReservationId = null
    ): ?Reserva {
        return Reserva::with('cancha')
            ->whereIn('cancha_id', $this->relatedCanchaIds($cancha))
            ->whereDate('fecha', $fecha)
            ->whereTime('hora', $hora)
            ->where('estado', '!=', 'cancelada')
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->first();
    }

    public function timeSlotsFor(Cancha $cancha): array
    {
        $start = Carbon::createFromFormat('H:i:s', $cancha->hora_apertura);
        $end = Carbon::createFromFormat('H:i:s', $cancha->hora_cierre);
        $step = max(15, (int) $cancha->intervalo_minutos);

        $slots = [];
        while ($start < $end) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($step);
        }

        return $slots;
    }

    public function conflictMessage(Cancha $selectedCancha, Reserva $conflict): string
    {
        $conflictCanchaName = $conflict->cancha?->nombre_completo ?? "cancha #{$conflict->cancha_id}";
        $selectedName = $selectedCancha->nombre_completo;

        if ($conflict->cancha_id === $selectedCancha->id) {
            return "La cancha {$selectedName} ya tiene una reserva en ese horario.";
        }

        return "Ese horario no está disponible para {$selectedName} porque se cruza con {$conflictCanchaName}.";
    }

    /**
     * @return array<int, string>
     */
    public function reservedTimes(Cancha $cancha, string $fecha, ?int $ignoreReservationId = null): array
    {
        return Reserva::whereIn('cancha_id', $this->relatedCanchaIds($cancha))
            ->whereDate('fecha', $fecha)
            ->where('estado', '!=', 'cancelada')
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->pluck('hora')
            ->map(fn ($hora) => substr((string) $hora, 0, 5))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function descendantIds(int $canchaId): array
    {
        $ids = [];

        foreach (Cancha::where('parent_id', $canchaId)->pluck('id') as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->descendantIds((int) $childId));
        }

        return $ids;
    }

    /**
     * @return array<int, Cancha>
     */
    private function descendants(int $canchaId): array
    {
        $descendants = [];

        foreach (Cancha::where('parent_id', $canchaId)->get() as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $this->descendants($child->id));
        }

        return $descendants;
    }
}
