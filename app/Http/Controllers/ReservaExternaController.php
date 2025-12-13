<?php
namespace App\Http\Controllers;

use App\Models\ReservaExterna;
use Illuminate\Http\Request;

class ReservaExternaController extends Controller
{
    public function index()
    {
        $reservas = ReservaExterna::where('cancha_id', 1)
        ->orderBy('fecha', 'desc')
        ->orderBy('hora', 'desc')
        ->paginate(15);

    return view('reservas_externas.index', compact('reservas'));
    }

    public function create()
    {
        return view('reservas_externas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cancha_id' => 'required|integer|min:1',
            'numero_subcancha' => 'required|integer|min:1',
            'nombre_cliente' => 'required|string|max:150',
            'telefono_cliente' => 'required|string|max:30',
            'fecha' => 'required|date',
            'hora' => 'required', // time
        ]);

        ReservaExterna::create($data);

        return redirect()
            ->route('reservas.externas.index')
            ->with('ok', 'Reserva externa creada correctamente');
    }

    public function edit(ReservaExterna $reservas_externa)
    {
        // Laravel usa el nombre del parámetro según la ruta resource
        return view('reservas_externas.edit', [
            'reserva' => $reservas_externa
        ]);
    }

    public function update(Request $request, ReservaExterna $reservas_externa)
    {
        $data = $request->validate([
            'cancha_id' => 'required|integer|min:1',
            'numero_subcancha' => 'required|integer|min:1',
            'nombre_cliente' => 'required|string|max:150',
            'telefono_cliente' => 'required|string|max:30',
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        $reservas_externa->update($data);

        return redirect()
            ->route('reservas.externas.index')
            ->with('ok', 'Reserva externa actualizada');
    }

    public function destroy(ReservaExterna $reservas_externa)
    {
        $reservas_externa->delete();

        return back()->with('ok', 'Reserva externa eliminada');
    }
}

