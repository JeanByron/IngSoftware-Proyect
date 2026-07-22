<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
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
    /** Método de pago que se cobra en persona (no pasa por la pasarela). */
    private const PAYMENT_CASH = 'efectivo';

    /**
     * RF-06 / RF-10: punto de entrada del cliente.
     * Si la URL contiene ?mesa=N -> vista presencial; si no -> vista domicilio.
     */
    public function create(Request $request): View
    {
        // RF-05: sólo platos disponibles llegan a la vista de cliente.
        // RNF-04: se sirven desde caché (consulta muy frecuente del catálogo).
        $dishes = Dish::availableCached();

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
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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

        // RNF-08: el pago es obligatorio. Tras registrar el pedido se envía al
        // cobro (URL firmada, no enumerable). La confirmación sólo se alcanza
        // después de pagar (ver processPayment).
        return redirect()
            ->to(URL::signedRoute('orders.payment', ['order' => $order]))
            ->with('status', 'Pedido registrado (#' . $order->id . '). Falta completar el pago.');
    }

    /**
     * RNF-08: pantalla de cobro del pedido (paso obligatorio). Llega por URL
     * firmada desde store(). Si ya está pagado, salta a la confirmación.
     */
    public function showPayment(Order $order): View|RedirectResponse
    {
        if ($order->isPaid()) {
            return redirect()->to(URL::signedRoute('orders.confirmation', ['order' => $order]));
        }

        $order->load('items');

        return view('orders.pago', [
            'order'   => $order,
            'methods' => $this->paymentMethods(),
            // La acción del form también va firmada (POST sobre URL firmada).
            'action'  => URL::signedRoute('orders.payment.process', ['order' => $order]),
        ]);
    }

    /**
     * RNF-08: procesa el paso de pago.
     *  - Efectivo: el pedido se registra pero el cobro se hace EN PERSONA; no
     *    pasa por la pasarela ni queda marcado como pagado.
     *  - Tarjeta/transferencia: se cobra por la pasarela (simulada) y, si
     *    aprueba, el pedido queda pagado.
     * En ambos casos se avanza a la confirmación firmada.
     */
    public function processPayment(Request $request, PaymentGateway $gateway, Order $order): RedirectResponse
    {
        if ($order->isPaid()) {
            return redirect()->to(URL::signedRoute('orders.confirmation', ['order' => $order]));
        }

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods()))],
        ]);
        $method = $validated['payment_method'];

        if ($method === self::PAYMENT_CASH) {
            // Pago en efectivo: se registra el método; el cobro es al entregar.
            $order->update(['payment_method' => $method]);   // sigue 'pendiente'

            return redirect()
                ->to(URL::signedRoute('orders.confirmation', ['order' => $order]))
                ->with('status', '¡Pedido registrado! Pagarás en efectivo al recibirlo.');
        }

        $result = $gateway->charge($order, $method);

        if (! $result->successful) {
            return back()->withErrors(['payment_method' => $result->message ?? 'El pago fue rechazado. Intenta de nuevo.']);
        }

        $order->update([
            'payment_status'    => Order::PAYMENT_PAGADO,
            'payment_method'    => $method,
            'payment_reference' => $result->reference,
            'paid_at'           => now(),
        ]);

        return redirect()
            ->to(URL::signedRoute('orders.confirmation', ['order' => $order]))
            ->with('status', '¡Pago aprobado! Tu pedido #' . $order->id . ' está confirmado.');
    }

    /** Pantalla de confirmación tras registrar y pagar el pedido (RF-15 / RNF-08). */
    public function confirmation(Order $order): View
    {
        $order->load('items');

        return view('orders.confirmation', compact('order'));
    }

    /**
     * Métodos de pago ofrecidos (clave => etiqueta). La pasarela simulada los
     * acepta todos; una real podría restringirlos.
     *
     * @return array<string, string>
     */
    private function paymentMethods(): array
    {
        return [
            'tarjeta'       => 'Tarjeta débito/crédito',
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia / QR bancario',
        ];
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

        // Válida sólo dentro del rango de mesas del comercio (1..máx);
        // fuera de rango degrada a domicilio.
        $maxMesas = (int) config('comercio.mesas', 50);

        return ($mesa >= 1 && $mesa <= $maxMesas) ? $mesa : null;
    }
}
