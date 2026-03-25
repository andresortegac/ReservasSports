<?php

namespace Database\Seeders;

use App\Models\Cancha;
use Illuminate\Database\Seeder;

class CanchaSeeder extends Seeder
{
    public function run(): void
    {
        $dias = array_keys(Cancha::diasSemana());
        $bloques = [
            ['inicio' => '06:00', 'fin' => '12:00'],
            ['inicio' => '14:00', 'fin' => '18:00'],
            ['inicio' => '19:00', 'fin' => '23:00'],
        ];

        $principal = Cancha::query()->updateOrCreate(
            ['id' => 1],
            [
                'parent_id' => null,
                'orden' => 1,
                'nombre' => 'Cancha Principal',
                'tipo' => 'con_divisiones',
                'subcanchas_count' => 3,
                'precio_hora' => 210000,
                'dias_operacion' => $dias,
                'bloques_horarios' => $bloques,
                'estado_operativo' => 'disponible',
                'descripcion' => 'Cancha principal para torneos y partidos completos.',
            ]
        );

        foreach ([1, 2, 3] as $division) {
            Cancha::query()->updateOrCreate(
                ['id' => $division + 1],
                [
                    'parent_id' => $principal->id,
                    'orden' => $division,
                    'nombre' => "Cancha Principal - Division {$division}",
                    'tipo' => 'subcancha',
                    'subcanchas_count' => 1,
                    'precio_hora' => 75000,
                    'dias_operacion' => $dias,
                    'bloques_horarios' => $bloques,
                    'estado_operativo' => 'disponible',
                    'descripcion' => "Division {$division} de la cancha principal.",
                ]
            );
        }
    }
}
