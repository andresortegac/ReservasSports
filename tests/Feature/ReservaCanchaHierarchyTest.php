<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaCanchaHierarchyTest extends TestCase
{
    use RefreshDatabase;

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
                'estado' => 'pendiente',
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
                'estado' => 'pendiente',
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
                'estado' => 'pendiente',
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
                'estado' => 'pendiente',
            ])
            ->assertRedirect(route('reservas.create'))
            ->assertSessionHasErrors('hora');
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
            'tipo' => 'futbol',
            'parent_id' => null,
            'orden' => 1,
            'subcanchas_count' => 3,
            'precio_hora' => 210000,
            'hora_apertura' => '06:00:00',
            'hora_cierre' => '23:00:00',
            'intervalo_minutos' => 60,
            'activa' => true,
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        $divisionUno = Cancha::create([
            'nombre' => 'Cancha Principal - División 1',
            'tipo' => 'microfutbol',
            'parent_id' => $principal->id,
            'orden' => 1,
            'subcanchas_count' => 1,
            'precio_hora' => 80000,
            'hora_apertura' => '06:00:00',
            'hora_cierre' => '23:00:00',
            'intervalo_minutos' => 60,
            'activa' => true,
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        Cancha::create([
            'nombre' => 'Cancha Principal - División 2',
            'tipo' => 'microfutbol',
            'parent_id' => $principal->id,
            'orden' => 2,
            'subcanchas_count' => 1,
            'precio_hora' => 80000,
            'hora_apertura' => '06:00:00',
            'hora_cierre' => '23:00:00',
            'intervalo_minutos' => 60,
            'activa' => true,
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        Cancha::create([
            'nombre' => 'Cancha Principal - División 3',
            'tipo' => 'microfutbol',
            'parent_id' => $principal->id,
            'orden' => 3,
            'subcanchas_count' => 1,
            'precio_hora' => 80000,
            'hora_apertura' => '06:00:00',
            'hora_cierre' => '23:00:00',
            'intervalo_minutos' => 60,
            'activa' => true,
            'estado_operativo' => 'disponible',
            'descripcion' => null,
        ]);

        return [$user, $cliente, $principal, $divisionUno];
    }
}
