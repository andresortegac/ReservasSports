<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaExterna extends Model
{
    protected $table = 'reservas_externas';

    protected $fillable = [
        'cancha_id',
        'external_reference',
        'nombre_cliente',
        'telefono_cliente',
        'fecha',
        'hora',
        'numero_subcancha',
        'estado',
        'motivo_cancelacion',
        'reserva_id',
        'confirmada_at',
        'cancelada_at',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'confirmada_at' => 'datetime',
        'cancelada_at' => 'datetime',
    ];

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }
}
