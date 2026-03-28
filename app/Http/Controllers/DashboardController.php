<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\Cliente;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $metrics = [
            'reservasHoy' => Reserva::whereDate('fecha', $today)
                ->where('estado', '!=', 'cancelada')
                ->count(),
            'pendientesCobro' => Reserva::where('estado_pago', '!=', 'pagado')
                ->where('estado', '!=', 'cancelada')
                ->count(),
            'ingresosHoy' => Reserva::whereDate('fecha', $today)
                ->where('estado', '!=', 'cancelada')
                ->sum(DB::raw("CASE
                    WHEN estado_pago = 'pagado' THEN precio
                    WHEN estado_pago = 'parcial' THEN anticipo
                    ELSE 0
                END")),
            'clientesTotales' => Cliente::count(),
        ];

        $estadoReservas = Reserva::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $horasMasReservadas = Reserva::select('hora', DB::raw('count(*) as total'))
            ->whereBetween('fecha', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('estado', '!=', 'cancelada')
            ->groupBy('hora')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $gananciasDelMes = Reserva::selectRaw("
                fecha,
                SUM(
                    CASE
                        WHEN estado = 'cancelada' THEN 0
                        WHEN estado_pago = 'pagado' THEN precio
                        WHEN estado_pago = 'parcial' THEN anticipo
                        ELSE 0
                    END
                ) as total
            ")
            ->whereBetween('fecha', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->fecha)->toDateString());

        $gananciasPorDia = collect();
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            $dateKey = $cursor->toDateString();
            $gananciasPorDia->push([
                'fecha' => $dateKey,
                'dia' => $cursor->day,
                'total' => (float) ($gananciasDelMes[$dateKey]->total ?? 0),
            ]);
            $cursor->addDay();
        }

        $maxGananciaDia = (float) $gananciasPorDia->max('total');

        $canchasNombres = Cancha::pluck('nombre', 'id');

        $reservasPorCancha = Cancha::query()
            ->leftJoin('reservas', function ($join) use ($monthStart, $monthEnd) {
                $join->on('reservas.cancha_id', '=', 'canchas.id')
                    ->whereBetween('reservas.fecha', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->where('reservas.estado', '!=', 'cancelada');
            })
            ->select(
                'canchas.id',
                'canchas.parent_id',
                'canchas.nombre',
                DB::raw('COUNT(reservas.id) as total')
            )
            ->groupBy('canchas.id', 'canchas.parent_id', 'canchas.nombre')
            ->orderByDesc('total')
            ->orderBy('canchas.nombre')
            ->get()
            ->map(function ($cancha) use ($canchasNombres) {
                $label = $cancha->nombre;

                if ($cancha->parent_id) {
                    $parentName = $canchasNombres[$cancha->parent_id] ?? null;
                    $label = $parentName ? "{$parentName} / {$cancha->nombre}" : $cancha->nombre;
                }

                return [
                    'nombre' => $label,
                    'total' => (int) $cancha->total,
                ];
            });

        $maxReservasCancha = (int) $reservasPorCancha->max('total');
        $monthLabel = $monthStart->translatedFormat('F Y');

        return view('dashboard.index', compact(
            'metrics',
            'estadoReservas',
            'horasMasReservadas',
            'gananciasPorDia',
            'maxGananciaDia',
            'reservasPorCancha',
            'maxReservasCancha',
            'monthLabel'
        ));
    }
}
