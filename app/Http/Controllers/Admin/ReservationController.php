<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Módulo Reservas (activable, RNF-10 / flag MODULE_RESERVAS).
 * CRUD de reservas de mesa gestionado por el personal del restaurante.
 * Sus rutas sólo se registran si el módulo está encendido (ver routes/web.php).
 */
class ReservationController extends Controller
{
    /** Listado de reservas próximas (hoy en adelante). */
    public function index(): View
    {
        $reservations = Reservation::upcoming()->paginate(10);

        return view('reservas.index', compact('reservations'));
    }

    public function create(): View
    {
        return view('reservas.create');
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        Reservation::create($request->validatedData());

        return redirect()
            ->route('admin.reservations.index')
            ->with('status', 'Reserva registrada correctamente.');
    }

    public function edit(Reservation $reservation): View
    {
        return view('reservas.edit', compact('reservation'));
    }

    public function update(StoreReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validatedData());

        return redirect()
            ->route('admin.reservations.index')
            ->with('status', 'Reserva actualizada correctamente.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()
            ->route('admin.reservations.index')
            ->with('status', 'Reserva eliminada correctamente.');
    }
}
