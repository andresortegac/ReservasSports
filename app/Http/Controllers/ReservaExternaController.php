<?php

namespace App\Http\Controllers;

use App\Models\ReservaExterna;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class ReservaExternaController extends Controller
{
    public function index()
    {
        try {
            $reservas = ReservaExterna::where('cancha_id', 1)
                ->orderBy('fecha', 'desc')
                ->orderBy('hora', 'desc')
                ->paginate(15);

            return view('reservas_externas.index', compact('reservas'));
        } catch (Throwable $e) {
            report($e);

            return view('reservas_externas.index', [
                'reservas' => $this->emptyReservasPaginator(),
                'externalError' => $this->externalDbMessage(),
            ]);
        }
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
            'hora' => 'required',
        ]);

        try {
            ReservaExterna::create($data);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('reservas.externas.index')
                ->with('error', $this->externalDbMessage());
        }

        return redirect()
            ->route('reservas.externas.index')
            ->with('ok', 'Reserva externa creada correctamente');
    }

    public function edit(string $reservas_externa)
    {
        try {
            return view('reservas_externas.edit', [
                'reserva' => ReservaExterna::findOrFail($reservas_externa),
            ]);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('reservas.externas.index')
                ->with('error', $this->externalDbMessage());
        }
    }

    public function update(Request $request, string $reservas_externa)
    {
        $data = $request->validate([
            'cancha_id' => 'required|integer|min:1',
            'numero_subcancha' => 'required|integer|min:1',
            'nombre_cliente' => 'required|string|max:150',
            'telefono_cliente' => 'required|string|max:30',
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        try {
            ReservaExterna::findOrFail($reservas_externa)->update($data);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('reservas.externas.index')
                ->with('error', $this->externalDbMessage());
        }

        return redirect()
            ->route('reservas.externas.index')
            ->with('ok', 'Reserva externa actualizada');
    }

    public function destroy(string $reservas_externa)
    {
        try {
            ReservaExterna::findOrFail($reservas_externa)->delete();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', $this->externalDbMessage());
        }

        return back()->with('ok', 'Reserva externa eliminada');
    }

    private function emptyReservasPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            15,
            1,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    private function externalDbMessage(): string
    {
        return 'No se pudo conectar a la base de datos externa. Revisa las variables DB_EXTERNAL_* en el archivo .env.';
    }
}
