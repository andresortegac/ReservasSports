<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Carbon\Carbon;   // 👈 IMPORTANTE

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with('cliente')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(15);

        return view('reservas.index', compact('reservas'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        return view('reservas.create', compact('clientes'));
    }

    // 👇👇 FALTABA ESTE MÉTODO
    public function show(Reserva $reserva)
{
    return redirect()->route('reservas.index');
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'       => 'required|exists:clientes,id',
            'cancha_id'        => 'required|integer|min:1',
            'subcancha' => 'required|integer|in:1,2',
            'fecha'            => 'required|date',
            'hora'             => 'required|date_format:H:i',
            'precio'           => 'required|numeric|min:0',
            'estado'           => 'required|in:pendiente,pagada,cancelada',
        ]);

        // Verificar si ya existe reserva en esa cancha+subcancha+fecha+hora
        $yaExiste = Reserva::where('cancha_id', $data['cancha_id'])
            ->where('subcancha', $data['subcancha'])
            ->whereDate('fecha', $data['fecha'])
            ->whereTime('hora', $data['hora'])
            ->exists();

        if ($yaExiste) {
            $reservadas = Reserva::where('cancha_id', $data['cancha_id'])
                ->where('subcancha', $data['subcancha'])
                ->whereDate('fecha', $data['fecha'])
                ->orderBy('hora')
                ->pluck('hora')
                ->map(fn($h) => substr($h, 0, 5))
                ->implode(', ');

            return back()
                ->withErrors([
                    'hora' => 'Esa subcancha ya está reservada a esa hora. Horarios ocupados: ' .
                              ($reservadas ?: 'ninguno') . '. Elige otra hora.',
                ])
                ->withInput();
        }

        // Promo reserva 11 gratis
        $reservasCliente = Reserva::where('cliente_id', $data['cliente_id'])
            ->where('estado', '!=', 'cancelada')
            ->count();

        $mensajePremio = null;
        if ( ($reservasCliente + 1) % 11 == 0 ) {
            $data['precio'] = 0;
            $mensajePremio = '🎁 Esta reserva es GRATIS: el cliente llegó a 10 reservas y la 11 es de cortesía.';
        }

        Reserva::create($data);

        return redirect()
            ->route('reservas.index')
            ->with('ok', 'Reserva creada correctamente')
            ->with('premio', $mensajePremio);
    }

    public function edit(Reserva $reserva)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        return view('reservas.edit', compact('reserva', 'clientes'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        $data = $request->validate([
            'cliente_id'       => 'required|exists:clientes,id',
            'cancha_id'        => 'required|integer|min:1',
            'subcancha' => 'required|integer|in:1,2',
            'fecha'            => 'required|date',
            'hora'             => 'required|date_format:H:i',
            'precio'           => 'required|numeric|min:0',
            'estado'           => 'required|in:pendiente,pagada,cancelada',
        ]);

        $yaExiste = Reserva::where('cancha_id', $data['cancha_id'])
            ->where('subcancha', $data['subcancha'])
            ->whereDate('fecha', $data['fecha'])
            ->whereTime('hora', $data['hora'])
            ->where('id', '!=', $reserva->id)
            ->exists();

        if ($yaExiste) {
            $reservadas = Reserva::where('cancha_id', $data['cancha_id'])
                ->where('subcancha', $data['subcancha'])
                ->whereDate('fecha', $data['fecha'])
                ->orderBy('hora')
                ->pluck('hora')
                ->map(fn($h) => substr($h, 0, 5))
                ->implode(', ');

            return back()
                ->withErrors([
                    'hora' => 'Esa subcancha ya está reservada a esa hora. Horarios ocupados: ' .
                              ($reservadas ?: 'ninguno') . '. Elige otra hora.',
                ])
                ->withInput();
        }

        $reserva->update($data);

        return redirect()
            ->route('reservas.index')
            ->with('ok', 'Reserva actualizada correctamente');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return back()->with('ok', 'Reserva eliminada correctamente');
    }

    // 👉 MÉTODO PARA AJAX
        public function horasDisponibles(Request $request)
    {
        $fechaRaw  = $request->query('fecha');
        $subcancha = $request->query('subcancha');
        $canchaId  = $request->query('cancha_id', 1);

        if (!$fechaRaw || !$subcancha) {
            return response()->json([]);
        }

        // 👇 ESTO ES LO QUE PREGUNTAS
        try {
            $fecha = str_contains($fechaRaw, '/')
                ? Carbon::createFromFormat('d/m/Y', $fechaRaw)->format('Y-m-d')
                : Carbon::parse($fechaRaw)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([]);
        }

        // Horario fijo
        $horas = [];
        $time = Carbon::createFromTime(6, 0);
        $cierre = Carbon::createFromTime(23, 0);

        while ($time < $cierre) {
            $horas[] = $time->format('H:i');
            $time->addHour();
        }

        $reservadas = Reserva::where('cancha_id', $canchaId)
            ->where('subcancha', $subcancha)
            ->whereDate('fecha', $fecha) // 👈 YA NORMALIZADA
            ->pluck('hora')
            ->map(fn($h) => substr($h, 0, 5))
            ->toArray();

        $disponibles = array_values(array_diff($horas, $reservadas));

        return response()->json($disponibles);
    }



}
