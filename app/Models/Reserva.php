<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'cliente_id',
        'cancha_id',
        'subcancha',
        'fecha',
        'hora',
        'precio',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
