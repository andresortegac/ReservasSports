<?php

namespace App\Services;

use App\Models\Cancha;
use App\Models\ReservaExterna;
use Illuminate\Support\Facades\Http;
use Throwable;

class EdwinSportStatusNotifier
{
    public function notify(ReservaExterna $reserva): void
    {
        $cancha = $reserva->cancha;

        if (!$cancha instanceof Cancha) {
            return;
        }

        $baseUrl = env('EDWINSPORT_API_URL', 'http://127.0.0.1:8001');

        try {
            Http::asJson()
                ->acceptJson()
                ->timeout(10)
                ->withHeaders([
                    'X-Integration-Identifier' => (string) $cancha->integration_identifier,
                    'X-Integration-Token' => (string) $cancha->integration_token,
                    'X-EdwinSport-Callback-Token' => (string) env('EDWINSPORT_CALLBACK_TOKEN', 'edwinsport-callback-token'),
                ])
                ->post(rtrim($baseUrl, '/').'/api/integraciones/reservas-externas/estado', [
                    'external_reference' => $reserva->external_reference,
                    'estado_solicitud' => $reserva->estado,
                    'motivo' => $reserva->motivo_cancelacion,
                    'reserva_id' => $reserva->reserva_id,
                ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
