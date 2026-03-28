<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancha extends Model
{
    public const DIAS_SEMANA = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    protected $fillable = [
        'parent_id',
        'orden',
        'nombre',
        'subdominio',
        'integration_identifier',
        'integration_token',
        'tipo',
        'subcanchas_count',
        'precio_hora',
        'dias_operacion',
        'bloques_horarios',
        'estado_operativo',
        'descripcion',
    ];

    protected $casts = [
        'dias_operacion' => 'array',
        'bloques_horarios' => 'array',
        'precio_hora' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('orden')->orderBy('nombre');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function reservasExternas(): HasMany
    {
        return $this->hasMany(ReservaExterna::class);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        if (!$this->parent) {
            return $this->nombre;
        }

        return "{$this->parent->nombre} / {$this->nombre}";
    }

    public function getTipoJerarquiaAttribute(): string
    {
        return match ($this->tipo) {
            'con_divisiones' => 'Cancha con divisiones',
            'subcancha' => 'Subcancha',
            default => 'Cancha independiente',
        };
    }

    public function getEstadoLegibleAttribute(): string
    {
        return match ($this->estado_operativo) {
            'mantenimiento' => 'Mantenimiento',
            'fuera_de_servicio' => 'Fuera de servicio',
            default => 'Disponible',
        };
    }

    public function getBloquesHorariosLegiblesAttribute(): string
    {
        $blocks = collect($this->bloques_horarios ?? [])
            ->map(function ($block) {
                $inicio = substr((string) ($block['inicio'] ?? ''), 0, 5);
                $fin = substr((string) ($block['fin'] ?? ''), 0, 5);

                if ($inicio === '' || $fin === '') {
                    return null;
                }

                return "{$inicio} - {$fin}";
            })
            ->filter()
            ->values();

        return $blocks->isNotEmpty()
            ? $blocks->implode(' / ')
            : 'Sin horarios configurados';
    }

    public function getDiasOperacionLegiblesAttribute(): string
    {
        $days = collect($this->dias_operacion ?? [])
            ->map(fn ($day) => self::DIAS_SEMANA[$day] ?? null)
            ->filter()
            ->values();

        return $days->isNotEmpty()
            ? $days->implode(', ')
            : 'Sin días configurados';
    }

    /**
     * @return array<string, string>
     */
    public static function diasSemana(): array
    {
        return self::DIAS_SEMANA;
    }
}
