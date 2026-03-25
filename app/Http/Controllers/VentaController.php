<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class VentaController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $ventasDia = Reserva::whereDate('fecha', $now->toDateString())
            ->where('estado', 'pagada')
            ->sum('precio');

        $ventasSemana = Reserva::whereBetween('fecha', [$now->startOfWeek(), $now->endOfWeek()])
            ->where('estado', 'pagada')
            ->sum('precio');

        $ventasMes = Reserva::whereMonth('fecha', $now->month)
            ->whereYear('fecha', $now->year)
            ->where('estado', 'pagada')
            ->sum('precio');

        $detalleDia = Reserva::with('cliente')
            ->whereDate('fecha', $now->toDateString())
            ->where('estado', 'pagada')
            ->orderBy('hora')
            ->get();

        return view('ventas.index', compact('ventasDia', 'ventasSemana', 'ventasMes', 'detalleDia'));
    }

    public function export(): RedirectResponse
    {
        return back()->with('ok', 'La exportacion de ventas se habilitara en la siguiente fase.');
    }
}
