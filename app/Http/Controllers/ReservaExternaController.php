<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\ReservaExterna;
use App\Services\EdwinSportStatusNotifier;
use App\Services\TenantCanchaResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaExternaController extends Controller
{
    public function __construct(
        private readonly TenantCanchaResolver $tenantResolver,
        private readonly EdwinSportStatusNotifier $statusNotifier,
    ) {
    }

    public function index()
    {
        $tenantCancha = $this->tenantResolver->resolve();

        $reservas = ReservaExterna::with(['cancha', 'reserva'])
            ->when($tenantCancha, fn ($query) => $query->where('cancha_id', $tenantCancha->id))
            ->when(request('estado'), fn ($query, $estado) => $query->where('estado', $estado))
            ->orderByRaw("CASE estado WHEN 'pendiente' THEN 0 WHEN 'confirmada' THEN 1 ELSE 2 END")
            ->orderBy('fecha')
            ->orderBy('hora')
            ->paginate(15)
            ->withQueryString();

        return view('reservas_externas.index', compact('reservas', 'tenantCancha'));
    }

    public function create()
    {
        $tenantCancha = $this->tenantResolver->resolve();

        return view('reservas_externas.create', [
            'reserva' => new ReservaExterna(['estado' => 'pendiente']),
            'canchas' => $tenantCancha
                ? collect([$tenantCancha])
                : Cancha::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateReserva($request);
        $data['external_reference'] = 'manual-'.now()->format('YmdHis').'-'.str()->lower(str()->random(6));

        ReservaExterna::create($data);

        return redirect()
            ->route('reservas.externas.index')
            ->with('ok', 'Solicitud externa creada correctamente.');
    }

    public function show(ReservaExterna $reservas_externa)
    {
        return redirect()->route('reservas.externas.edit', $reservas_externa);
    }

    public function edit(ReservaExterna $reservas_externa)
    {
        $this->ensureTenantAccess($reservas_externa);
        $tenantCancha = $this->tenantResolver->resolve();

        return view('reservas_externas.edit', [
            'reserva' => $reservas_externa,
            'canchas' => $tenantCancha
                ? collect([$tenantCancha])
                : Cancha::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, ReservaExterna $reservas_externa)
    {
        $this->ensureTenantAccess($reservas_externa);
        $data = $this->validateReserva($request);
        $reservas_externa->update($data);

        return redirect()
            ->route('reservas.externas.index')
            ->with('ok', 'Solicitud externa actualizada.');
    }

    public function confirm(ReservaExterna $reservas_externa)
    {
        $this->ensureTenantAccess($reservas_externa);

        if ($reservas_externa->estado !== 'pendiente') {
            return back()->with('error', 'Solo puedes confirmar solicitudes pendientes.');
        }

        $cancha = Cancha::findOrFail($reservas_externa->cancha_id);
        $hora = str_pad(substr((string) $reservas_externa->hora, 0, 5), 8, ':00');

        $conflict = Reserva::query()
            ->where('cancha_id', $reservas_externa->cancha_id)
            ->where('subcancha', $reservas_externa->numero_subcancha)
            ->where('fecha', $reservas_externa->fecha->toDateString())
            ->where('hora', $hora)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'reserva' => 'Ya existe una reserva interna para esa cancha, subcancha, fecha y hora.',
            ]);
        }

        DB::transaction(function () use ($reservas_externa, $cancha, $hora) {
            $cliente = $reservas_externa->telefono_cliente
                ? Cliente::query()->where('telefono', $reservas_externa->telefono_cliente)->first()
                : null;

            if (!$cliente) {
                $cliente = Cliente::create([
                    'nombre' => $reservas_externa->nombre_cliente,
                    'telefono' => $reservas_externa->telefono_cliente,
                ]);
            } elseif ($cliente->nombre !== $reservas_externa->nombre_cliente) {
                $cliente->update(['nombre' => $reservas_externa->nombre_cliente]);
            }

            $reserva = Reserva::create([
                'cliente_id' => $cliente->id,
                'cancha_id' => $reservas_externa->cancha_id,
                'user_id' => auth()->id(),
                'subcancha' => $reservas_externa->numero_subcancha,
                'fecha' => $reservas_externa->fecha->toDateString(),
                'hora' => $hora,
                'duracion_minutos' => 60,
                'precio_base' => (float) $cancha->precio_hora,
                'descuento' => 0,
                'precio' => (float) $cancha->precio_hora,
                'anticipo' => 0,
                'saldo_pendiente' => (float) $cancha->precio_hora,
                'estado' => 'confirmada',
                'estado_pago' => 'pendiente',
                'metodo_pago_principal' => null,
                'notas' => 'Reserva confirmada desde solicitud externa '.$reservas_externa->external_reference,
            ]);

            $reservas_externa->update([
                'estado' => 'confirmada',
                'reserva_id' => $reserva->id,
                'confirmada_at' => now(),
                'motivo_cancelacion' => null,
            ]);
        });

        $this->statusNotifier->notify($reservas_externa->fresh('cancha'));

        return back()->with('ok', 'Solicitud externa confirmada y convertida en reserva.');
    }

    public function cancel(Request $request, ReservaExterna $reservas_externa)
    {
        $this->ensureTenantAccess($reservas_externa);

        if ($reservas_externa->estado !== 'pendiente') {
            return back()->with('error', 'Solo puedes cancelar solicitudes pendientes.');
        }

        $data = $request->validate([
            'motivo_cancelacion' => ['nullable', 'string', 'max:500'],
        ]);

        $reservas_externa->update([
            'estado' => 'cancelada',
            'motivo_cancelacion' => $data['motivo_cancelacion'] ?? null,
            'cancelada_at' => now(),
        ]);

        $this->statusNotifier->notify($reservas_externa->fresh('cancha'));

        return back()->with('ok', 'Solicitud externa cancelada.');
    }

    public function destroy(ReservaExterna $reservas_externa)
    {
        $this->ensureTenantAccess($reservas_externa);

        if ($reservas_externa->reserva_id) {
            return back()->with('error', 'No puedes eliminar una solicitud ya convertida en reserva.');
        }

        $reservas_externa->delete();

        return back()->with('ok', 'Solicitud externa eliminada.');
    }

    private function validateReserva(Request $request): array
    {
        $data = $request->validate([
            'cancha_id' => ['required', 'exists:canchas,id'],
            'numero_subcancha' => ['required', 'integer', 'min:1', 'max:99'],
            'nombre_cliente' => ['required', 'string', 'max:150'],
            'telefono_cliente' => ['nullable', 'string', 'max:30'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        if ($tenantCancha = $this->tenantResolver->resolve()) {
            $data['cancha_id'] = $tenantCancha->id;
        }

        return $data;
    }

    private function ensureTenantAccess(ReservaExterna $reserva): void
    {
        $tenantCancha = $this->tenantResolver->resolve();

        if ($tenantCancha && $tenantCancha->id !== $reserva->cancha_id) {
            abort(404);
        }
    }
}
