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
        if (empty($cancha->bloques_horarios)) {
            return "La cancha {$cancha->nombre} no tiene bloques horarios configurados.";
        }

        if ($cancha->estado_operativo !== 'disponible') {
            return "La cancha {$cancha->nombre} está {$this->estadoDescripcion($cancha)}.";
        }

        if (empty($this->timeSlotsFor($cancha))) {
            return "La cancha {$cancha->nombre} no tiene un horario válido configurado.";
        }

        $ancestorId = $cancha->parent_id;
        while ($ancestorId) {
            $ancestor = Cancha::find($ancestorId);
            if (!$ancestor) {
                break;
            }

            if ($ancestor->estado_operativo !== 'disponible') {
                return "La cancha principal {$ancestor->nombre} está {$this->estadoDescripcion($ancestor)}.";
            }

            $ancestorId = $ancestor->parent_id;
        }

        foreach ($this->descendants($cancha->id) as $descendant) {
            if ($descendant->estado_operativo !== 'disponible') {
                return "No puedes reservar {$cancha->nombre} mientras {$descendant->nombre} esté {$this->estadoDescripcion($descendant)}.";
            }
        }

        return null;
    }

    public function availableTimes(Cancha $cancha, string $fecha, ?int $ignoreReservationId = null): array
    {
        if ($this->dateAvailabilityIssue($cancha, $fecha)) {
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
        $slots = [];

        foreach ($cancha->bloques_horarios ?? [] as $block) {
            $inicio = (string) ($block['inicio'] ?? '');
            $fin = (string) ($block['fin'] ?? '');

            if ($inicio === '' || $fin === '') {
                continue;
            }

            $start = Carbon::createFromFormat('H:i', $inicio);
            $end = Carbon::createFromFormat('H:i', $fin);

            if ($start->gte($end)) {
                continue;
            }

            while ($start < $end) {
                $slots[] = $start->format('H:i');
                $start->addHour();
            }
        }

        $slots = array_values(array_unique($slots));
        sort($slots);

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

    public function dateAvailabilityIssue(Cancha $cancha, string $fecha): ?string
    {
        if ($issue = $this->availabilityIssue($cancha)) {
            return $issue;
        }

        if (!$this->operatesOnDate($cancha, $fecha)) {
            return "La cancha {$cancha->nombre} no funciona el {$this->dayNameFromDate($fecha)}.";
        }

        $ancestorId = $cancha->parent_id;
        while ($ancestorId) {
            $ancestor = Cancha::find($ancestorId);
            if (!$ancestor) {
                break;
            }

            if (!$this->operatesOnDate($ancestor, $fecha)) {
                return "La cancha principal {$ancestor->nombre} no funciona el {$this->dayNameFromDate($fecha)}.";
            }

            $ancestorId = $ancestor->parent_id;
        }

        return null;
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

    private function estadoDescripcion(Cancha $cancha): string
    {
        return match ($cancha->estado_operativo) {
            'mantenimiento' => 'en mantenimiento',
            'fuera_de_servicio' => 'fuera de servicio',
            default => 'disponible',
        };
    }

    public function operatesOnDate(Cancha $cancha, string $fecha): bool
    {
        $day = $this->dayKeyFromDate($fecha);

        return in_array($day, $cancha->dias_operacion ?? [], true);
    }

    private function dayKeyFromDate(string $fecha): string
    {
        return match (Carbon::parse($fecha)->dayOfWeekIso) {
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            default => 'domingo',
        };
    }

    private function dayNameFromDate(string $fecha): string
    {
        return Cancha::diasSemana()[$this->dayKeyFromDate($fecha)] ?? 'día seleccionado';
    }
}
