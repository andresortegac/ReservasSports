<?php

namespace Tests\Feature;

use App\Models\Cancha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalReservationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_reservation_api_stores_pending_request(): void
    {
        $cancha = Cancha::create([
            'nombre' => 'Bombonera',
            'integration_identifier' => 'bombonera-main',
            'integration_token' => 'bombonera-token',
            'tipo' => 'independiente',
            'subcanchas_count' => 1,
            'precio_hora' => 50000,
            'dias_operacion' => ['lunes', 'martes'],
            'bloques_horarios' => [['inicio' => '06:00', 'fin' => '12:00']],
            'estado_operativo' => 'disponible',
        ]);

        $response = $this
            ->withHeaders([
                'X-Integration-Identifier' => 'bombonera-main',
                'X-Integration-Token' => 'bombonera-token',
            ])
            ->postJson('/api/integraciones/reservas-externas', [
                'external_reference' => 'ews-10',
                'nombre_cliente' => 'Carlos Ruiz',
                'telefono_cliente' => '3001231234',
                'fecha' => '2026-04-01',
                'hora' => '10:00',
                'numero_subcancha' => 1,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('reservas_externas', [
            'cancha_id' => $cancha->id,
            'external_reference' => 'ews-10',
            'nombre_cliente' => 'Carlos Ruiz',
            'estado' => 'pendiente',
        ]);
    }
}
