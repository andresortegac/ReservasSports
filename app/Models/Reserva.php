<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'cliente_id',
        'cancha_id',
        'user_id',
        'subcancha',
        'fecha',
        'hora',
        'duracion_minutos',
        'precio_base',
        'descuento',
        'precio',
        'anticipo',
        'saldo_pendiente',
        'estado',
        'estado_pago',
        'metodo_pago_principal',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'precio_base' => 'decimal:2',
        'descuento' => 'decimal:2',
        'precio' => 'decimal:2',
        'anticipo' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }

    public function getHoraInicioAttribute(): string
    {
        return substr((string) $this->hora, 0, 5);
    }

    public function getHoraFinAttribute(): string
    {
        return Carbon::createFromFormat('H:i:s', (string) $this->hora)
            ->addMinutes((int) $this->duracion_minutos)
            ->format('H:i');
    }
}
