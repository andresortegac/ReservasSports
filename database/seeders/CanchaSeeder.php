<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanchaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('canchas')->updateOrInsert(
            ['id' => 1],
            [
                'nombre' => 'Cancha Principal',
                'tipo' => 'sintetica',
                'subcanchas_count' => 2,
                'precio_hora' => 120000,
                'activa' => true,
                'descripcion' => 'Cancha principal dividida en dos subcanchas.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
