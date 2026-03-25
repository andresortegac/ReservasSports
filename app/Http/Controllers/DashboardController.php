<?php

namespace App\Http\Controllers;

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
            'pendientesCobro' => Reserva::where('estado', 'pendiente')->count(),
            'ingresosHoy' => Reserva::whereDate('fecha', $today)
                ->where('estado', 'pagada')
                ->sum('precio'),
            'clientesTotales' => Cliente::count(),
        ];

        $proximasReservas = Reserva::with('cliente')
            ->where(function ($query) use ($today) {
                $query->whereDate('fecha', '>', $today)
                    ->orWhere(function ($subQuery) use ($today) {
                        $subQuery->whereDate('fecha', $today)
                            ->whereTime('hora', '>=', now()->format('H:i:s'));
                    });
            })
            ->where('estado', '!=', 'cancelada')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(5)
            ->get();

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

        return view('dashboard.index', compact(
            'metrics',
            'proximasReservas',
            'estadoReservas',
            'horasMasReservadas'
        ));
    }
}
