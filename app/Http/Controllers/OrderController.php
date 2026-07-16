<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Módulo de Flujo de Cliente (presencial vía QR y a domicilio).
 *
 * Detección de contexto (detalle técnico clave del documento):
 *  El QR de cada mesa codifica una URL con el parámetro 'mesa', p. ej.
 *  /pedido?mesa=12. La presencia o ausencia de ese parámetro decide la vista.
 *
 * Cubre los requerimientos:
 *  - RF-05 mostrar sólo platos disponibles
 *  - RF-06 vista presencial cuando la URL trae ?mesa=
 *  - RF-07 mostrar el número de mesa en pantalla
 *  - RF-08 asociar la mesa al pedido
 *  - RF-09/RF-11 agregar platos disponibles al carrito
 *  - RF-10 vista domicilio cuando NO viene ?mesa=
 *  - RF-12 solicitar dirección al confirmar un domicilio
 *  - RF-13 modificar cantidades (carrito en el cliente, validado aquí)
 *  - RF-14 calcular el total
 *  - RF-15 registrar el pedido
 *  - RF-16 impedir confirmar con el carrito vacío
 *  - RF-17 estado inicial "recibido"
 */
class OrderController extends Controller
{
    /**
     * RF-06 / RF-10: punto de entrada del cliente.
     * Si la URL contiene ?mesa=N -> vista presencial; si no -> vista domicilio.
     */
    public function create(Request $request): View
    {
        // RF-05: sólo platos disponibles llegan a la vista de cliente.
        $dishes = Dish::available()->orderBy('name')->get();

        $tableNumber = $this->resolveTableNumber($request);

        if ($tableNumber !== null) {
            // RF-06 / RF-07 / RF-08
            return view('orders.presencial', [
                'dishes'      => $dishes,
                'tableNumber' => $tableNumber,
            ]);
        }

        // RF-10
        return view('orders.domicilio', [
            'dishes' => $dishes,
        ]);
    }

    /**
     * RF-15: registrar el pedido cuando el cliente confirma.
     *
     * Recibe el carrito como JSON (cada ítem: dish_id + quantity), el tipo
     * de pedido y, según el tipo, la mesa (RF-08) o la dirección (RF-12).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'              => ['required', 'in:presencial,domicilio'],
            'table_number'      => ['nullable', 'integer', 'min:1'],
            'address'           => ['nullable', 'string', 'max:255'],
            'items'             => ['required', 'array', 'min:1'], // RF-16: al menos un ítem
            'items.*.dish_id'   => ['required', 'integer', 'exists:dishes,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
        ]);

        // Reglas condicionales según el tipo de pedido:
        //  - RF-12: en domicilio la dirección es obligatoria.
        //  - RF-08: en presencial la mesa es obligatoria.
        $contextError = match (true) {
            $validated['type'] === Order::TYPE_DOMICILIO && empty($validated['address'])
                => ['address' => 'La dirección de entrega es obligatoria para pedidos a domicilio.'],
            $validated['type'] === Order::TYPE_PRESENCIAL && empty($validated['table_number'])
                => ['table_number' => 'Falta el número de mesa del pedido presencial.'],
            default => null,
        };

        if ($contextError !== null) {
            return back()->withErrors($contextError)->withInput();
        }

        // Cargamos los platos del carrito y revalidamos disponibilidad/precio
        // en el servidor (no confiamos en el precio que venga del cliente).
        $dishIds = collect($validated['items'])->pluck('dish_id');
        $dishes  = Dish::available()->whereIn('id', $dishIds)->get()->keyBy('id');

        // RF-05 (refuerzo): si algún plato dejó de estar disponible, se rechaza.
        $unavailable = collect($validated['items'])
            ->first(fn ($item) => ! $dishes->has($item['dish_id']));

        if ($unavailable) {
            return back()
                ->withErrors(['items' => 'Uno de los platos ya no está disponible. Revisa tu carrito.'])
                ->withInput();
        }

        $order = DB::transaction(function () use ($validated, $dishes) {
            // RF-17: estado inicial "recibido".
            $order = Order::create([
                'type'         => $validated['type'],
                'table_number' => $validated['type'] === Order::TYPE_PRESENCIAL
                                    ? $validated['table_number']   // RF-08
                                    : null,
                'address'      => $validated['type'] === Order::TYPE_DOMICILIO
                                    ? $validated['address']        // RF-12
                                    : null,
                'total'        => 0,
                'status'       => Order::STATUS_RECIBIDO,
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $dish     = $dishes->get($item['dish_id']);
                $quantity = (int) $item['quantity'];                 // RF-13
                $subtotal = $dish->price * $quantity;
                $total   += $subtotal;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'dish_id'    => $dish->id,
                    'dish_name'  => $dish->name,
                    'unit_price' => $dish->price,
                    'quantity'   => $quantity,
                    'subtotal'   => $subtotal,
                ]);
            }

            // RF-14: total calculado en el servidor.
            $order->update(['total' => $total]);

            return $order;
        });

        // La confirmación viaja como URL firmada: sin la firma no se puede
        // acceder, lo que impide enumerar pedidos ajenos por su ID incremental
        // (evita exponer direcciones de otros clientes).
        return redirect()
            ->to(URL::signedRoute('orders.confirmation', ['order' => $order]))
            ->with('status', '¡Pedido registrado! Tu número de pedido es #' . $order->id);
    }

    /** Pantalla de confirmación tras registrar el pedido (RF-15). */
    public function confirmation(Order $order): View
    {
        $order->load('items');

        return view('orders.confirmation', compact('order'));
    }

    /**
     * RF-06 / RF-08: lee el parámetro de mesa de la URL del QR.
     * Devuelve el número de mesa válido (>=1) o null si no aplica.
     */
    private function resolveTableNumber(Request $request): ?int
    {
        if (! $request->filled('mesa')) {
            return null;
        }

        $mesa = (int) $request->query('mesa');

        return $mesa >= 1 ? $mesa : null;
    }
}
