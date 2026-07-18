<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Módulo Panel del Restaurante.
 *
 * Cubre los requerimientos:
 *  - RF-19 listar pedidos con su mesa o dirección
 *  - RF-20 actualizar el estado de un pedido
 *
 * Protegido por el middleware 'auth' (ver routes/web.php). RF-18 (login) lo
 * resuelve Laravel Breeze.
 */
class OrderPanelController extends Controller
{
    /** RF-19: lista de pedidos entrantes con su mesa o dirección. */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->withCount('items')
            ->latest();

        // Filtro opcional por estado para el personal.
        if ($request->filled('status') && in_array($request->status, Order::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders'   => $orders,
            'statuses' => Order::STATUSES,
            'filter'   => $request->status,
        ]);
    }

    /** Detalle de un pedido con sus líneas y su bitácora de estados (RNF-20). */
    public function show(Order $order): View
    {
        $order->load(['items', 'statusLogs.user']);

        return view('admin.orders.show', [
            'order'       => $order,
            // Sólo se ofrecen los estados a los que se puede AVANZAR.
            'allowedNext' => $order->allowedNextStatuses(),
        ]);
    }

    /**
     * RF-20: actualizar el estado de un pedido desde el panel.
     * Máquina de estados: sólo avanza (sin retrocesos). RNF-20: cada cambio
     * queda registrado en la bitácora (quién, cuándo, de qué estado a cuál).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $to = $request->validated()['status'];

        if (! $order->canTransitionTo($to)) {
            return back()->withErrors([
                'status' => "No se puede pasar de “{$order->statusLabel()}” a ese estado (sólo se avanza en el flujo).",
            ]);
        }

        $from = $order->status;

        DB::transaction(function () use ($order, $request, $from, $to) {
            $order->update(['status' => $to]);
            $order->statusLogs()->create([
                'user_id'     => $request->user()?->id,   // RNF-20: quién
                'from_status' => $from,
                'to_status'   => $to,
            ]);
        });

        return back()->with('status', "Pedido #{$order->id} actualizado a “{$order->statusLabel()}”.");
    }
}
