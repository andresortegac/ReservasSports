<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('canchas')->nullOnDelete();
            $table->unsignedTinyInteger('orden')->default(1)->after('parent_id');
            $table->time('hora_apertura')->default('06:00:00')->after('precio_hora');
            $table->time('hora_cierre')->default('23:00:00')->after('hora_apertura');
            $table->unsignedSmallInteger('intervalo_minutos')->default(60)->after('hora_cierre');
            $table->enum('estado_operativo', ['disponible', 'mantenimiento'])->default('disponible')->after('activa');
        });

        $this->migrateExistingDivisions();
    }

    public function down(): void
    {
        Schema::table('canchas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'orden',
                'hora_apertura',
                'hora_cierre',
                'intervalo_minutos',
                'estado_operativo',
            ]);
        });
    }

    private function migrateExistingDivisions(): void
    {
        $now = now();
        $canchas = DB::table('canchas')->orderBy('id')->get();

        foreach ($canchas as $cancha) {
            $divisionCount = max(1, (int) ($cancha->subcanchas_count ?? 1));
            if ($divisionCount <= 1) {
                continue;
            }

            $alreadyHasChildren = DB::table('canchas')
                ->where('parent_id', $cancha->id)
                ->exists();

            if ($alreadyHasChildren) {
                continue;
            }

            for ($division = 1; $division <= $divisionCount; $division++) {
                $childId = DB::table('canchas')->insertGetId([
                    'parent_id' => $cancha->id,
                    'orden' => $division,
                    'nombre' => "{$cancha->nombre} - División {$division}",
                    'tipo' => $cancha->tipo,
                    'subcanchas_count' => 1,
                    'precio_hora' => $cancha->precio_hora,
                    'hora_apertura' => '06:00:00',
                    'hora_cierre' => '23:00:00',
                    'intervalo_minutos' => 60,
                    'activa' => $cancha->activa,
                    'estado_operativo' => 'disponible',
                    'descripcion' => "División {$division} de {$cancha->nombre}.",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('reservas')
                    ->where('cancha_id', $cancha->id)
                    ->where('subcancha', $division)
                    ->update([
                        'cancha_id' => $childId,
                        'subcancha' => 1,
                    ]);
            }
        }
    }
};
