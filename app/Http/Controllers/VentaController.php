<?php
namespace App\Http\Controllers;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $ventasDia = Reserva::whereDate('fecha', $now->toDateString())
            ->where('estado', 'pagada')
            ->sum('precio');

        $ventasSemana = Reserva::whereBetween('fecha', [$now->startOfWeek(), $now->endOfWeek()])
            ->where('estado','pagada')
            ->sum('precio');

        $ventasMes = Reserva::whereMonth('fecha', $now->month)
            ->whereYear('fecha', $now->year)
            ->where('estado','pagada')
            ->sum('precio');

        // Para tabla detalle del día:
        $detalleDia = Reserva::with('cliente')
            ->whereDate('fecha', $now->toDateString())
            ->where('estado','pagada')
            ->orderBy('hora_inicio')
            ->get();

        return view('ventas.index', compact('ventasDia','ventasSemana','ventasMes','detalleDia'));
    }
}
