<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo de Gestión de Menú (panel de administración).
 *
 * Cubre los requerimientos:
 *  - RF-01 crear plato (nombre, descripción, precio)
 *  - RF-02 editar plato
 *  - RF-03 eliminar plato
 *  - RF-04 marcar disponible / no disponible
 *
 * Protegido por el middleware 'auth' (ver routes/web.php).
 */
class DishController extends Controller
{
    /** Lista de platos para el administrador. */
    public function index(): View
    {
        $dishes = Dish::orderBy('name')->paginate(10);

        return view('dishes.index', compact('dishes'));
    }

    /** Formulario de creación. */
    public function create(): View
    {
        return view('dishes.create');
    }

    /** RF-01: persistir un plato nuevo. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDish($request);

        Dish::create($data);

        return redirect()
            ->route('dishes.index')
            ->with('status', 'Plato creado correctamente.');
    }

    /** Formulario de edición (RF-02). */
    public function edit(Dish $dish): View
    {
        return view('dishes.edit', compact('dish'));
    }

    /** RF-02 / RF-04: actualizar datos y/o disponibilidad. */
    public function update(Request $request, Dish $dish): RedirectResponse
    {
        $data = $this->validateDish($request);

        $dish->update($data);

        return redirect()
            ->route('dishes.index')
            ->with('status', 'Plato actualizado correctamente.');
    }

    /** RF-03: eliminar un plato. */
    public function destroy(Dish $dish): RedirectResponse
    {
        $dish->delete();

        return redirect()
            ->route('dishes.index')
            ->with('status', 'Plato eliminado correctamente.');
    }

    /**
     * RF-04: alternar rápidamente la disponibilidad de un plato
     * sin pasar por el formulario completo de edición.
     */
    public function toggle(Dish $dish): RedirectResponse
    {
        $dish->update(['is_available' => ! $dish->is_available]);

        return redirect()
            ->route('dishes.index')
            ->with('status', 'Disponibilidad actualizada.');
    }

    /** Reglas de validación compartidas entre store y update (RF-01). */
    private function validateDish(Request $request): array
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'price'        => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        // El checkbox no envía valor cuando está desmarcado.
        $validated['is_available'] = $request->boolean('is_available');

        return $validated;
    }
}
