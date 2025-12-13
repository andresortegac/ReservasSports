<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // 'mes' o 'semana'

        $clientes = Cliente::withCount([
            // Conteo por periodo (para tu pantalla actual)
            'reservas as reservas_periodo_count' => function($q) use ($periodo) {
                $now = Carbon::now();

                if ($periodo === 'semana') {
                    $q->whereBetween('fecha', [$now->startOfWeek(), $now->endOfWeek()]);
                } else {
                    $q->whereMonth('fecha', $now->month)
                      ->whereYear('fecha', $now->year);
                }
            },
            // ✅ Conteo total de reservas (para el premio 11 gratis)
            'reservas as reservas_total_count' => function($q) {
                $q->where('estado', '!=', 'cancelada'); // opcional: no contar canceladas
            },
        ])
        ->orderBy('nombre')
        ->paginate(15);

        return view('clientes.index', compact('clientes', 'periodo'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:120',
            'telefono' => 'nullable|string|max:30',
            'email'    => 'nullable|email|max:120',
        ]);

        Cliente::create($data);

        return redirect()
            ->route('clientes.index')
            ->with('ok','Cliente creado');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:120',
            'telefono' => 'nullable|string|max:30',
            'email'    => 'nullable|email|max:120',
        ]);

        $cliente->update($data);

        return redirect()
            ->route('clientes.index')
            ->with('ok','Cliente actualizado');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return back()->with('ok','Cliente eliminado');
    }
}
