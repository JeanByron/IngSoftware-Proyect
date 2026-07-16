<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /** Detalle de un pedido con sus líneas. */
    public function show(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.show', [
            'order'    => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    /** RF-20: actualizar el estado de un pedido desde el panel. */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order->update(['status' => $request->validated()['status']]);

        return back()->with('status', "Pedido #{$order->id} actualizado a “{$order->statusLabel()}”.");
    }
}
