<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cancha;
use App\Models\ReservaExterna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalReservationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $cancha = Cancha::query()
            ->where('integration_identifier', (string) $request->header('X-Integration-Identifier'))
            ->where('integration_token', (string) $request->header('X-Integration-Token'))
            ->first();

        if (!$cancha) {
            return response()->json([
                'message' => 'Integracion no autorizada para esta cancha.',
            ], 401);
        }

        $data = $request->validate([
            'external_reference' => ['required', 'string', 'max:120'],
            'nombre_cliente' => ['required', 'string', 'max:150'],
            'telefono_cliente' => ['nullable', 'string', 'max:30'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'numero_subcancha' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $reserva = ReservaExterna::updateOrCreate(
            ['external_reference' => $data['external_reference']],
            [
                'cancha_id' => $cancha->id,
                'nombre_cliente' => $data['nombre_cliente'],
                'telefono_cliente' => $data['telefono_cliente'] ?? null,
                'fecha' => $data['fecha'],
                'hora' => $data['hora'],
                'numero_subcancha' => $data['numero_subcancha'],
                'estado' => 'pendiente',
                'motivo_cancelacion' => null,
                'cancelada_at' => null,
            ]
        );

        return response()->json([
            'message' => 'Solicitud externa registrada correctamente.',
            'id' => $reserva->id,
            'estado' => $reserva->estado,
        ], 201);
    }
}
