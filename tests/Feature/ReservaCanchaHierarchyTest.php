<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaCanchaHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private const DIAS_OPERACION = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

    public function test_reserving_parent_cancha_blocks_child_hours_and_creation(): void
    {
        [$user, $cliente, $principal, $divisionUno] = $this->baseReservationSetup();

        $this->actingAs($user)
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-15',
                'hora' => '10:00',
                'precio' => 210000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.index'));

        $hours = $this->actingAs($user)
            ->getJson(route('reservas.horas-disponibles', [
                'cancha_id' => $divisionUno->id,
                'fecha' => '2030-01-15',
            ]))
            ->json();

        $this->assertNotContains('10:00', $hours);

        $this->actingAs($user)
            ->from(route('reservas.create'))
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $divisionUno->id,
                'fecha' => '2030-01-15',
                'hora' => '10:00',
                'precio' => 80000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.create'))
            ->assertSessionHasErrors('hora');
    }

    public function test_reserving_child_cancha_blocks_parent_hours_and_creation(): void
    {
        [$user, $cliente, $principal, $divisionUno] = $this->baseReservationSetup();

        $this->actingAs($user)
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $divisionUno->id,
                'fecha' => '2030-01-16',
                'hora' => '18:00',
                'precio' => 80000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.index'));

        $hours = $this->actingAs($user)
            ->getJson(route('reservas.horas-disponibles', [
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-16',
            ]))
            ->json();

        $this->assertNotContains('18:00', $hours);

        $this->actingAs($user)
            ->from(route('reservas.create'))
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-16',
                'hora' => '18:00',
                'precio' => 210000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.create'))
            ->assertSessionHasErrors('hora');
    }

    public function test_only_configured_time_blocks_are_available_for_reservations(): void
    {
        [$user, $cliente, $principal] = $this->baseReservationSetup();

        $principal->update([
            'bloques_horarios' => [
                ['inicio' => '06:00', 'fin' => '10:00'],
                ['inicio' => '15:00', 'fin' => '18:00'],
            ],
        ]);

        $hours = $this->actingAs($user)
            ->getJson(route('reservas.horas-disponibles', [
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-17',
            ]))
            ->json();

        $this->assertSame(['06:00', '07:00', '08:00', '09:00', '15:00', '16:00', '17:00'], $hours);

        $this->actingAs($user)
            ->from(route('reservas.create'))
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-17',
                'hora' => '11:00',
                'precio' => 210000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.create'))
            ->assertSessionHasErrors('hora');
    }

    public function test_reservations_are_blocked_when_cancha_does_not_operate_on_selected_day(): void
    {
        [$user, $cliente, $principal] = $this->baseReservationSetup();

        $principal->update([
            'dias_operacion' => ['sabado', 'domingo'],
        ]);

        $hours = $this->actingAs($user)
            ->getJson(route('reservas.horas-disponibles', [
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-15',
            ]))
            ->json();

        $this->assertSame([], $hours);

        $this->actingAs($user)
            ->from(route('reservas.create'))
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-15',
                'hora' => '10:00',
                'precio' => 210000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.create'))
            ->assertSessionHasErrors('cancha_id');
    }

    public function test_base_price_is_taken_from_cancha_and_discount_is_applied(): void
    {
        [$user, $cliente, $principal] = $this->baseReservationSetup();

        $this->actingAs($user)
            ->post(route('reservas.store'), [
                'cliente_id' => $cliente->id,
                'cancha_id' => $principal->id,
                'fecha' => '2030-01-18',
                'hora' => '10:00',
                'precio' => 5000,
                'descuento' => 20000,
                'estado' => 'confirmada',
            ])
            ->assertRedirect(route('reservas.index'));

        $this->assertDatabaseHas('reservas', [
            'cliente_id' => $cliente->id,
            'cancha_id' => $principal->id,
            'precio_base' => 210000,
            'descuento' => 20000,
            'precio' => 190000,
        ]);
    }

    /**
     * @return array{0: User, 1: Cliente, 2: Cancha, 3: Cancha}
     */
    private function baseReservationSetup(): array
    {
        $user = User::factory()->create();
        $cliente = Cliente::create([
            'nombre' => 'Cliente Prueba',
            'telefono' => '3000000000',
            'email' => 'cliente@example.com',
        ]);

        $principal = Cancha::create([
            'nombre' => 'Cancha Principal',
            'tipo' => 'con_divisiones',
            'parent_id' => null,
            'orden' => 1,
            'subcanchas_count' => 3,
            'precio_hora' => 210000,
            'dias_operacion' => self::DIAS_OPERACION,
            'bloques_horarios' => [
                ['inicio' => '06:00', 'fin' => '12:00'],
                ['inicio' => '14:00', 'fin' => '23:00'],
            ],
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        $divisionUno = Cancha::create([
            'nombre' => 'Cancha Principal - Division 1',
            'tipo' => 'subcancha',
            'parent_id' => $principal->id,
            'orden' => 1,
            'subcanchas_count' => 1,
            'precio_hora' => 80000,
            'dias_operacion' => self::DIAS_OPERACION,
            'bloques_horarios' => [
                ['inicio' => '06:00', 'fin' => '12:00'],
                ['inicio' => '14:00', 'fin' => '23:00'],
            ],
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        Cancha::create([
            'nombre' => 'Cancha Principal - Division 2',
            'tipo' => 'subcancha',
            'parent_id' => $principal->id,
            'orden' => 2,
            'subcanchas_count' => 1,
            'precio_hora' => 80000,
            'dias_operacion' => self::DIAS_OPERACION,
            'bloques_horarios' => [
                ['inicio' => '06:00', 'fin' => '12:00'],
                ['inicio' => '14:00', 'fin' => '23:00'],
            ],
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        Cancha::create([
            'nombre' => 'Cancha Principal - Division 3',
            'tipo' => 'subcancha',
            'parent_id' => $principal->id,
            'orden' => 3,
            'subcanchas_count' => 1,
            'precio_hora' => 80000,
            'dias_operacion' => self::DIAS_OPERACION,
            'bloques_horarios' => [
                ['inicio' => '06:00', 'fin' => '12:00'],
                ['inicio' => '14:00', 'fin' => '23:00'],
            ],
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        return [$user, $cliente, $principal, $divisionUno];
    }
}
