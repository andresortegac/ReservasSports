<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanchaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_and_delete_canchas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('canchas.store'), [
                'nombre' => 'Cancha Torneo',
                'tipo' => 'futbol',
                'parent_id' => null,
                'orden' => 1,
                'hora_apertura' => '06:00',
                'hora_cierre' => '23:00',
                'intervalo_minutos' => 60,
                'precio_hora' => 220000,
                'estado_operativo' => 'disponible',
                'activa' => 1,
                'descripcion' => 'Cancha grande para torneos.',
            ])
            ->assertRedirect(route('canchas.index'));

        $principal = Cancha::where('nombre', 'Cancha Torneo')->firstOrFail();

        foreach ([1, 2, 3] as $division) {
            $this->actingAs($user)
                ->post(route('canchas.store'), [
                    'nombre' => "Cancha Torneo - División {$division}",
                    'tipo' => 'microfutbol',
                    'parent_id' => $principal->id,
                    'orden' => $division,
                    'hora_apertura' => '06:00',
                    'hora_cierre' => '23:00',
                    'intervalo_minutos' => 60,
                    'precio_hora' => 80000,
                    'estado_operativo' => 'disponible',
                    'activa' => 1,
                    'descripcion' => "División {$division} de la cancha principal.",
                ])
                ->assertRedirect(route('canchas.index'));
        }

        $principal->refresh();
        $this->assertSame(3, $principal->subcanchas_count);

        $divisionUno = Cancha::where('parent_id', $principal->id)
            ->where('orden', 1)
            ->firstOrFail();

        $this->actingAs($user)
            ->put(route('canchas.update', $divisionUno), [
                'nombre' => $divisionUno->nombre,
                'tipo' => $divisionUno->tipo,
                'parent_id' => $principal->id,
                'orden' => 1,
                'hora_apertura' => '07:00',
                'hora_cierre' => '22:00',
                'intervalo_minutos' => 60,
                'precio_hora' => 85000,
                'estado_operativo' => 'mantenimiento',
                'activa' => 1,
                'descripcion' => 'División 1 en mantenimiento.',
            ])
            ->assertRedirect(route('canchas.index'));

        $this->assertDatabaseHas('canchas', [
            'id' => $divisionUno->id,
            'precio_hora' => 85000,
            'estado_operativo' => 'mantenimiento',
            'hora_apertura' => '07:00',
        ]);

        $standalone = Cancha::create([
            'nombre' => 'Cancha Auxiliar',
            'tipo' => 'padel',
            'parent_id' => null,
            'orden' => 1,
            'subcanchas_count' => 1,
            'precio_hora' => 50000,
            'hora_apertura' => '08:00:00',
            'hora_cierre' => '21:00:00',
            'intervalo_minutos' => 60,
            'activa' => true,
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        $this->actingAs($user)
            ->delete(route('canchas.destroy', $standalone))
            ->assertRedirect();

        $this->assertDatabaseMissing('canchas', [
            'id' => $standalone->id,
        ]);
    }
}
