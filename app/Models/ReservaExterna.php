<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaExterna extends Model
{
    protected $connection = 'external';
    protected $table = 'user_reservas';

    // COMO sí tiene created_at y updated_at, NO pongas false
    public $timestamps = true;

    protected $fillable = [
        'cancha_id',
        'nombre_cliente',
        'telefono_cliente',
        'fecha',
        'hora',
        'numero_subcancha',
    ];
}

