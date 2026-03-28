<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Services\CanchaAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function __construct(private readonly CanchaAvailabilityService $availability)
    {
    }

    public function index(): View
    {
        $reservas = Reserva::with(['cliente', 'cancha.parent'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(15);

        return view('reservas.index', compact('reservas'));
    }

    public function create(): View
    {
        return view('reservas.create', $this->reservationFormContext(new Reserva()));
    }

    public function show(Reserva $reserva): RedirectResponse
    {
        return redirect()->route('reservas.index');
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $cancha] = $this->validatedReservationData($request);

        $reservasCliente = Reserva::where('cliente_id', $data['cliente_id'])
            ->where('estado', '!=', 'cancelada')
            ->count();

        $mensajePremio = null;
        if (($reservasCliente + 1) % 11 === 0) {
            $data['precio'] = 0;
            $data['saldo_pendiente'] = 0;
            $data['estado_pago'] = 'pagado';
            $mensajePremio = 'Esta reserva es gratis: el cliente llegó a la reserva número 11.';
        }

        Reserva::create($data);

        return redirect()
            ->route('reservas.index')
            ->with('ok', "Reserva creada correctamente para {$cancha->nombre_completo}.")
            ->with('premio', $mensajePremio);
    }

    public function edit(Reserva $reserva): View
    {
        return view('reservas.edit', $this->reservationFormContext($reserva));
    }

    public function update(Request $request, Reserva $reserva): RedirectResponse
    {
        [$data, $cancha] = $this->validatedReservationData($request, $reserva);

        $reserva->update($data);

        return redirect()
            ->route('reservas.index')
            ->with('ok', "Reserva actualizada correctamente para {$cancha->nombre_completo}.");
    }

    public function destroy(Reserva $reserva): RedirectResponse
    {
        $reserva->delete();

        return back()->with('ok', 'Reserva eliminada correctamente.');
    }

    public function horasDisponibles(Request $request)
    {
        $request->validate([
            'cancha_id' => ['required', 'integer', 'exists:canchas,id'],
            'fecha' => ['required', 'date'],
            'ignore_reserva_id' => ['nullable', 'integer'],
        ]);

        $cancha = Cancha::findOrFail((int) $request->query('cancha_id'));
        $fecha = Carbon::parse((string) $request->query('fecha'))->toDateString();
        $ignoreReservationId = $request->integer('ignore_reserva_id') ?: null;

        return response()->json(
            $this->availability->availableTimes($cancha, $fecha, $ignoreReservationId)
        );
    }

    /**
     * @return array{0: array<string, mixed>, 1: Cancha}
     */
    private function validatedReservationData(Request $request, ?Reserva $reserva = null): array
    {
        $allowedStates = $reserva?->exists
            ? ['pendiente', 'confirmada', 'abonado', 'pagada', 'cancelada']
            : ['confirmada', 'abonado', 'pagada'];

        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'cancha_id' => ['required', 'exists:canchas,id'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'anticipo' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago_principal' => ['nullable', 'in:efectivo,transferencia,otro'],
            'estado' => ['required', 'in:' . implode(',', $allowedStates)],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        $cancha = Cancha::with('parent')->findOrFail((int) $data['cancha_id']);

        if ($issue = $this->availability->dateAvailabilityIssue($cancha, (string) $data['fecha'])) {
            return $this->throwReservationValidation('cancha_id', $issue);
        }

        if (substr((string) $data['hora'], 3, 2) !== '00') {
            return $this->throwReservationValidation(
                'hora',
                'Las reservas solo pueden comenzar en punto.'
            );
        }

        $fecha = Carbon::parse((string) $data['fecha'])->toDateString();
        $hora = Carbon::createFromFormat('H:i', (string) $data['hora'])->format('H:i:s');

        if (
            $this->availability->reservationDateTimeHasPassed($fecha, $hora)
            && !$this->sameReservationDateTime($reserva, $fecha, $hora)
        ) {
            return $this->throwReservationValidation(
                'hora',
                'No puedes reservar fechas anteriores a hoy ni horas que ya hayan pasado.'
            );
        }

        if (!in_array(substr($hora, 0, 5), $this->availability->timeSlotsFor($cancha), true)) {
            return $this->throwReservationValidation(
                'hora',
                'La hora elegida no está dentro de los bloques horarios configurados para esta cancha.'
            );
        }

        $conflict = $this->availability->conflictingReservation(
            $cancha,
            $fecha,
            $hora,
            $reserva?->id
        );

        if ($conflict) {
            return $this->throwReservationValidation(
                'hora',
                $this->availability->conflictMessage($cancha, $conflict)
            );
        }

        $precioBase = $this->resolveBasePrice($cancha, $reserva, (int) $data['cancha_id']);
        $descuento = (float) ($data['descuento'] ?? 0);

        if ($descuento > $precioBase) {
            return $this->throwReservationValidation(
                'descuento',
                'El descuento no puede ser mayor que el precio base de la cancha.'
            );
        }

        $precioFinal = max(0, $precioBase - $descuento);

        if (
            $reserva
            && $reserva->exists
            && (int) $data['cancha_id'] === $reserva->cancha_id
            && (float) $reserva->precio_base > 0
            && (float) $reserva->precio === 0.0
            && (float) $reserva->descuento === 0.0
        ) {
            $precioFinal = 0;
        }

        $estadoSeleccionado = $data['estado'];
        $anticipoIngresado = (float) ($data['anticipo'] ?? 0);
        $metodoPago = $data['metodo_pago_principal'] ?? null;

        if ($estadoSeleccionado === 'pagada' && $precioFinal > 0 && !$metodoPago) {
            return $this->throwReservationValidation(
                'metodo_pago_principal',
                'Debes seleccionar la forma de pago para marcar la reserva como pagada.'
            );
        }

        if ($estadoSeleccionado === 'abonado') {
            if ($precioFinal <= 0) {
                return $this->throwReservationValidation(
                    'estado',
                    'Una reserva con total 0 no puede quedar como abonada.'
                );
            }

            if ($anticipoIngresado <= 0 || $anticipoIngresado >= $precioFinal) {
                return $this->throwReservationValidation(
                    'anticipo',
                    'El abono debe ser mayor a 0 y menor que el total final.'
                );
            }

            if (!$metodoPago) {
                return $this->throwReservationValidation(
                    'metodo_pago_principal',
                    'Debes seleccionar la forma de pago del abono.'
                );
            }
        }

        if (!in_array($estadoSeleccionado, ['pagada', 'abonado'], true)) {
            $anticipoIngresado = 0;
            $metodoPago = null;
        }

        $estado = $estadoSeleccionado === 'abonado' ? 'confirmada' : $estadoSeleccionado;

        $data['fecha'] = $fecha;
        $data['hora'] = $hora;
        $data['precio_base'] = $precioBase;
        $data['descuento'] = $descuento;
        $data['precio'] = $precioFinal;
        $data['subcancha'] = 1;
        $data['user_id'] = auth()->id();
        $data['duracion_minutos'] = 60;
        $data['estado'] = $estado;
        $data['anticipo'] = $estadoSeleccionado === 'pagada'
            ? $precioFinal
            : ($estadoSeleccionado === 'abonado' ? $anticipoIngresado : 0);
        $data['saldo_pendiente'] = max(0, $precioFinal - (float) $data['anticipo']);
        $data['estado_pago'] = $precioFinal === 0 || $estadoSeleccionado === 'pagada'
            ? 'pagado'
            : ($estadoSeleccionado === 'abonado' ? 'parcial' : 'pendiente');
        $data['metodo_pago_principal'] = $precioFinal === 0 ? null : $metodoPago;

        return [$data, $cancha];
    }

    private function throwReservationValidation(string $field, string $message): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            $field => $message,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reservationFormContext(Reserva $reserva): array
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $canchas = Cancha::with('parent')
            ->withCount('children')
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('parent_id')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return compact('clientes', 'canchas', 'reserva');
    }

    private function resolveBasePrice(Cancha $cancha, ?Reserva $reserva, int $selectedCanchaId): float
    {
        if (
            $reserva
            && $reserva->exists
            && $selectedCanchaId === $reserva->cancha_id
            && (float) $reserva->precio_base > 0
        ) {
            return (float) $reserva->precio_base;
        }

        return (float) $cancha->precio_hora;
    }

    private function sameReservationDateTime(?Reserva $reserva, string $fecha, string $hora): bool
    {
        if (!$reserva || !$reserva->exists) {
            return false;
        }

        return $reserva->fecha?->toDateString() === $fecha
            && substr((string) $reserva->hora, 0, 8) === $hora;
    }
}
