<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanchaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('canchas')->updateOrInsert(
            ['id' => 1],
            [
                'parent_id' => null,
                'orden' => 1,
                'nombre' => 'Cancha Principal',
                'tipo' => 'futbol',
                'subcanchas_count' => 3,
                'precio_hora' => 210000,
                'hora_apertura' => '06:00:00',
                'hora_cierre' => '23:00:00',
                'intervalo_minutos' => 60,
                'activa' => true,
                'estado_operativo' => 'disponible',
                'descripcion' => 'Cancha principal para torneos y partidos completos.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        foreach ([1, 2, 3] as $division) {
            DB::table('canchas')->updateOrInsert(
                ['id' => $division + 1],
                [
                    'parent_id' => 1,
                    'orden' => $division,
                    'nombre' => "Cancha Principal - División {$division}",
                    'tipo' => 'microfutbol',
                    'subcanchas_count' => 1,
                    'precio_hora' => 75000,
                    'hora_apertura' => '06:00:00',
                    'hora_cierre' => '23:00:00',
                    'intervalo_minutos' => 60,
                    'activa' => true,
                    'estado_operativo' => 'disponible',
                    'descripcion' => "División {$division} de la cancha principal.",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
