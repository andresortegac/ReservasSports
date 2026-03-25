<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancha extends Model
{
    protected $fillable = [
        'parent_id',
        'orden',
        'nombre',
        'tipo',
        'subcanchas_count',
        'precio_hora',
        'hora_apertura',
        'hora_cierre',
        'intervalo_minutos',
        'activa',
        'estado_operativo',
        'descripcion',
    ];

    protected $casts = [
        'activa' => 'boolean',
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
        if ($this->parent_id) {
            return 'División';
        }

        if ($this->children_count > 0 || $this->relationLoaded('children') && $this->children->isNotEmpty()) {
            return 'Principal';
        }

        return 'Individual';
    }

    public function getEstadoLegibleAttribute(): string
    {
        if (!$this->activa) {
            return 'Inactiva';
        }

        return $this->estado_operativo === 'mantenimiento'
            ? 'Mantenimiento'
            : 'Disponible';
    }
}
