<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                'tipo' => 'futbol',
                'hora_apertura' => '06:00:00',
                'hora_cierre' => '23:00:00',
                'intervalo_minutos' => 60,
                'precio_hora' => 0,
                'activa' => true,
                'estado_operativo' => 'disponible',
                'orden' => 1,
            ]),
            'canchasPadre' => Cancha::root()->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCancha($request);

        DB::transaction(function () use ($data) {
            $cancha = Cancha::create($data);
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
                ->whereKeyNot($cancha->id)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function update(Request $request, Cancha $cancha): RedirectResponse
    {
        $data = $this->validateCancha($request, $cancha);

        if ($cancha->children()->exists() && !empty($data['parent_id'])) {
            return back()
                ->withErrors([
                    'parent_id' => 'Una cancha que ya tiene divisiones no puede convertirse en subcancha.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($cancha, $data) {
            $oldParent = $cancha->parent;
            $cancha->update($data);

            $this->syncDivisionCount($oldParent);
            $this->syncDivisionCount($cancha->parent);
        });

        return redirect()
            ->route('canchas.index')
            ->with('ok', 'Cancha actualizada correctamente.');
    }

    public function destroy(Cancha $cancha): RedirectResponse
    {
        if ($cancha->children()->exists()) {
            return back()->with('error', 'No puedes eliminar una cancha que todavía tiene divisiones asociadas.');
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
            ->where(fn ($query) => $query->whereNull('parent_id'));

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', $rootExistsRule],
            'orden' => ['required', 'integer', 'min:1', 'max:99'],
            'hora_apertura' => ['required', 'date_format:H:i'],
            'hora_cierre' => ['required', 'date_format:H:i', 'after:hora_apertura'],
            'intervalo_minutos' => ['required', 'integer', 'min:15', 'max:180'],
            'precio_hora' => ['required', 'numeric', 'min:0'],
            'estado_operativo' => ['required', Rule::in(['disponible', 'mantenimiento'])],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $data['activa'] = $request->boolean('activa');
        $data['subcanchas_count'] = 1;

        if ($cancha && (int) ($data['parent_id'] ?? 0) === $cancha->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Una cancha no puede ser hija de sí misma.',
            ]);
        }

        return $data;
    }

    private function syncDivisionCount(?Cancha $cancha): void
    {
        if (!$cancha) {
            return;
        }

        $cancha->forceFill([
            'subcanchas_count' => max(1, $cancha->children()->count()),
        ])->saveQuietly();
    }
}
