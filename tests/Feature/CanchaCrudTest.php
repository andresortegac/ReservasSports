<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanchaCrudTest extends TestCase
{
    use RefreshDatabase;

    private const DIAS_OPERACION = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

    public function test_authenticated_user_can_create_update_and_delete_canchas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('canchas.store'), [
                'nombre' => 'Cancha Torneo',
                'tipo' => 'con_divisiones',
                'parent_id' => null,
                'orden' => 1,
                'precio_hora' => 220000,
                'estado_operativo' => 'disponible',
                'descripcion' => 'Cancha grande para torneos.',
                'dias_operacion' => self::DIAS_OPERACION,
                'bloques_horarios' => [
                    ['inicio' => '06:00', 'fin' => '12:00'],
                    ['inicio' => '14:00', 'fin' => '18:00'],
                ],
            ])
            ->assertRedirect(route('canchas.index'));

        $principal = Cancha::where('nombre', 'Cancha Torneo')->firstOrFail();
        $this->assertSame('con_divisiones', $principal->tipo);

        foreach ([1, 2, 3] as $division) {
            $this->actingAs($user)
                ->post(route('canchas.store'), [
                    'nombre' => "Cancha Torneo - Division {$division}",
                    'tipo' => 'subcancha',
                    'parent_id' => $principal->id,
                    'orden' => $division,
                    'precio_hora' => 80000,
                    'estado_operativo' => 'disponible',
                    'descripcion' => "Division {$division} de la cancha principal.",
                    'dias_operacion' => self::DIAS_OPERACION,
                    'bloques_horarios' => [
                        ['inicio' => '06:00', 'fin' => '12:00'],
                        ['inicio' => '14:00', 'fin' => '18:00'],
                    ],
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
                'tipo' => 'subcancha',
                'parent_id' => $principal->id,
                'orden' => 1,
                'precio_hora' => 85000,
                'estado_operativo' => 'fuera_de_servicio',
                'descripcion' => 'Division 1 fuera de servicio.',
                'dias_operacion' => ['viernes', 'sabado', 'domingo'],
                'bloques_horarios' => [
                    ['inicio' => '07:00', 'fin' => '12:00'],
                    ['inicio' => '15:00', 'fin' => '21:00'],
                ],
            ])
            ->assertRedirect(route('canchas.index'));

        $divisionUno->refresh();
        $this->assertSame('fuera_de_servicio', $divisionUno->estado_operativo);
        $this->assertSame('07:00', $divisionUno->bloques_horarios[0]['inicio']);
        $this->assertSame(['viernes', 'sabado', 'domingo'], $divisionUno->dias_operacion);
        $this->assertSame(85000.0, (float) $divisionUno->precio_hora);

        $standalone = Cancha::create([
            'nombre' => 'Cancha Auxiliar',
            'tipo' => 'independiente',
            'parent_id' => null,
            'orden' => 1,
            'subcanchas_count' => 1,
            'precio_hora' => 50000,
            'dias_operacion' => self::DIAS_OPERACION,
            'bloques_horarios' => [
                ['inicio' => '08:00', 'fin' => '21:00'],
            ],
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
