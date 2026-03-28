<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;

class CanchaController extends Controller
{
    public function index(): View
    {
        $canchas = Cancha::with(['parent', 'children'])
            ->withCount(['children', 'reservas'])
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('parent_id')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('canchas.index', compact('canchas'));
    }

    public function create(): View
    {
        return view('canchas.create', [
            'cancha' => new Cancha([
                'tipo' => 'independiente',
                'precio_hora' => 0,
                'estado_operativo' => 'disponible',
                'orden' => 1,
                'dias_operacion' => array_keys(Cancha::diasSemana()),
                'bloques_horarios' => [
                    ['inicio' => '06:00', 'fin' => '12:00'],
                ],
            ]),
            'canchasPadre' => Cancha::root()
                ->where('tipo', 'con_divisiones')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCancha($request);

        DB::transaction(function () use ($data) {
            $cancha = Cancha::create($data);
            $this->syncDivisionCount($cancha);
            $this->syncDivisionCount($cancha->parent);
        });

        return redirect()
            ->route('canchas.index')
            ->with('ok', 'Cancha creada correctamente.');
    }

    public function edit(Cancha $cancha): View
    {
        return view('canchas.edit', [
            'cancha' => $cancha->loadCount('children'),
            'canchasPadre' => Cancha::root()
                ->where('tipo', 'con_divisiones')
                ->whereKeyNot($cancha->id)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function update(Request $request, Cancha $cancha): RedirectResponse
    {
        $data = $this->validateCancha($request, $cancha);

        DB::transaction(function () use ($cancha, $data) {
            $oldParent = $cancha->parent;
            $cancha->update($data);
            $newParent = $cancha->parent_id ? Cancha::find($cancha->parent_id) : null;

            $this->syncDivisionCount($oldParent);
            $this->syncDivisionCount($cancha);
            $this->syncDivisionCount($newParent);
        });

        return redirect()
            ->route('canchas.index')
            ->with('ok', 'Cancha actualizada correctamente.');
    }

    public function destroy(Cancha $cancha): RedirectResponse
    {
        if ($cancha->children()->exists()) {
            return back()->with('error', 'No puedes eliminar una cancha que todavía tiene subcanchas asociadas.');
        }

        if ($cancha->reservas()->exists()) {
            return back()->with('error', 'No puedes eliminar una cancha con reservas registradas.');
        }

        $parent = $cancha->parent;
        $cancha->delete();
        $this->syncDivisionCount($parent);

        return back()->with('ok', 'Cancha eliminada correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCancha(Request $request, ?Cancha $cancha = null): array
    {
        $rootExistsRule = Rule::exists('canchas', 'id')
            ->where(fn ($query) => $query->whereNull('parent_id')->where('tipo', 'con_divisiones'));

        $normalizedBlocks = [];
        $normalizedDays = [];

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:120'],
            'subdominio' => ['nullable', 'string', 'max:120', Rule::unique('canchas', 'subdominio')->ignore($cancha?->id)],
            'integration_identifier' => ['nullable', 'string', 'max:120', Rule::unique('canchas', 'integration_identifier')->ignore($cancha?->id)],
            'integration_token' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['independiente', 'con_divisiones', 'subcancha'])],
            'parent_id' => ['nullable', 'integer', $rootExistsRule],
            'orden' => ['nullable', 'integer', 'min:1', 'max:99'],
            'precio_hora' => ['required', 'numeric', 'min:0'],
            'estado_operativo' => ['required', Rule::in(['disponible', 'mantenimiento', 'fuera_de_servicio'])],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'dias_operacion' => ['required', 'array', 'min:1'],
            'dias_operacion.*' => ['required', Rule::in(array_keys(Cancha::diasSemana()))],
            'bloques_horarios' => ['required', 'array', 'min:1'],
            'bloques_horarios.*.inicio' => ['required', 'date_format:H:i'],
            'bloques_horarios.*.fin' => ['required', 'date_format:H:i'],
        ]);

        $validator->after(function (ValidationValidator $validator) use ($request, $cancha, &$normalizedBlocks, &$normalizedDays) {
            $tipo = (string) $request->input('tipo', '');
            $parentId = $request->input('parent_id');
            $orden = $request->input('orden');

            if ($tipo === 'subcancha' && empty($parentId)) {
                $validator->errors()->add('parent_id', 'Debes seleccionar la cancha con divisiones a la que pertenece esta subcancha.');
            }

            if ($tipo !== 'subcancha' && !empty($parentId)) {
                $validator->errors()->add('parent_id', 'Solo las subcanchas pueden quedar asociadas a una cancha principal.');
            }

            if ($tipo === 'subcancha' && empty($orden)) {
                $validator->errors()->add('orden', 'Debes indicar el orden de la subcancha dentro de la cancha principal.');
            }

            if ($cancha && (int) $parentId === $cancha->id) {
                $validator->errors()->add('parent_id', 'Una cancha no puede depender de sí misma.');
            }

            if ($cancha && $cancha->children()->exists() && $tipo !== 'con_divisiones') {
                $validator->errors()->add('tipo', 'Una cancha que ya tiene subcanchas debe permanecer como cancha con divisiones.');
            }

            if ($tipo === 'subcancha' && $cancha && $cancha->children()->exists()) {
                $validator->errors()->add('tipo', 'Una cancha con subcanchas no puede convertirse en subcancha.');
            }

            $normalizedBlocks = $this->validateScheduleBlocks(
                (array) $request->input('bloques_horarios', []),
                $validator
            );

            $normalizedDays = $this->validateOperationDays(
                (array) $request->input('dias_operacion', []),
                $validator
            );
        });

        $data = $validator->validate();
        $data['dias_operacion'] = $normalizedDays;
        $data['bloques_horarios'] = $normalizedBlocks;
        $data['subdominio'] = $data['subdominio'] ?: null;
        $data['integration_identifier'] = $data['integration_identifier'] ?: null;
        $data['integration_token'] = $data['integration_token'] ?: null;
        $data['parent_id'] = $data['tipo'] === 'subcancha' ? (int) $data['parent_id'] : null;
        $data['orden'] = $data['tipo'] === 'subcancha' ? (int) $data['orden'] : 1;
        $data['subcanchas_count'] = 1;

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @return array<int, array{inicio: string, fin: string}>
     */
    private function validateScheduleBlocks(array $blocks, ValidationValidator $validator): array
    {
        $normalized = [];

        foreach ($blocks as $index => $block) {
            $inicio = (string) ($block['inicio'] ?? '');
            $fin = (string) ($block['fin'] ?? '');

            if ($inicio === '' || $fin === '') {
                continue;
            }

            if (substr($inicio, 3, 2) !== '00' || substr($fin, 3, 2) !== '00') {
                $validator->errors()->add(
                    "bloques_horarios.{$index}.inicio",
                    'Los horarios deben iniciar y terminar en punto, por ejemplo 06:00 o 18:00.'
                );
                continue;
            }

            $inicioHora = Carbon::createFromFormat('H:i', $inicio);
            $finHora = Carbon::createFromFormat('H:i', $fin);

            if ($inicioHora->gte($finHora)) {
                $validator->errors()->add(
                    "bloques_horarios.{$index}.fin",
                    'La hora final debe ser mayor que la hora inicial.'
                );
                continue;
            }

            $normalized[] = [
                'inicio' => $inicioHora->format('H:i'),
                'fin' => $finHora->format('H:i'),
            ];
        }

        usort($normalized, fn ($left, $right) => strcmp($left['inicio'], $right['inicio']));

        for ($i = 1; $i < count($normalized); $i++) {
            if ($normalized[$i]['inicio'] < $normalized[$i - 1]['fin']) {
                $validator->errors()->add(
                    'bloques_horarios',
                    'Los bloques horarios no pueden cruzarse entre sí.'
                );
                break;
            }
        }

        if (empty($normalized)) {
            $validator->errors()->add(
                'bloques_horarios',
                'Debes registrar al menos un bloque horario válido para la cancha.'
            );
        }

        return $normalized;
    }

    /**
     * @param array<int, string> $days
     * @return array<int, string>
     */
    private function validateOperationDays(array $days, ValidationValidator $validator): array
    {
        $orderedDays = array_keys(Cancha::diasSemana());
        $normalized = array_values(array_unique(array_filter($days, fn ($day) => in_array($day, $orderedDays, true))));

        usort($normalized, fn ($left, $right) => array_search($left, $orderedDays, true) <=> array_search($right, $orderedDays, true));

        if (empty($normalized)) {
            $validator->errors()->add(
                'dias_operacion',
                'Debes seleccionar al menos un día de funcionamiento para la cancha.'
            );
        }

        return $normalized;
    }

    private function syncDivisionCount(?Cancha $cancha): void
    {
        if (!$cancha) {
            return;
        }

        $cancha->forceFill([
            'subcanchas_count' => $cancha->tipo === 'con_divisiones'
                ? max(1, $cancha->children()->count())
                : 1,
        ])->saveQuietly();
    }
}
