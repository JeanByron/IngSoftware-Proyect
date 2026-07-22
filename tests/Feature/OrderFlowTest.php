<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Módulo de Flujo de Cliente — RF-06 a RF-17.
 * Público (sin autenticación). Cubre la detección por QR, el carrito,
 * el cálculo del total en servidor y el registro del pedido.
 */
class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    /** RF-06 / RF-07: con ?mesa=N se muestra la vista presencial con el número de mesa. */
    public function test_presencial_view_when_url_has_table_param(): void
    {
        $response = $this->get(route('orders.create', ['mesa' => 12]));

        $response->assertOk();
        $response->assertSee('Mesa 12');
    }

    /** RF-10: sin ?mesa se muestra la vista de domicilio (pide dirección). */
    public function test_domicilio_view_when_url_has_no_table_param(): void
    {
        $response = $this->get(route('orders.create'));

        $response->assertOk();
        $response->assertSee('Dirección de entrega');
    }

    /**
     * RNF-15: el carrito se persiste en localStorage con clave por contexto,
     * para sobrevivir a recargas. (Comprobación de que el cableado se emite; el
     * comportamiento en el navegador se valida manualmente.)
     */
    public function test_cart_persistence_is_wired_per_context(): void
    {
        // Presencial: la clave incluye el número de mesa.
        $this->get(route('orders.create', ['mesa' => 8]))
            ->assertSee('localStorage', false)
            ->assertSee("'mesaqr.cart.'", false)
            ->assertSee('tableNumber: 8', false);

        // Domicilio: sin mesa (clave termina en 'domicilio').
        $this->get(route('orders.create'))
            ->assertSee('tableNumber: null', false);
    }

    /**
     * RNF-14: la vista de cliente trae el aviso de "sin conexión" (se muestra
     * al detectar el evento offline; el carrito ya persiste, no se congela).
     */
    public function test_offline_notice_is_present(): void
    {
        $this->get(route('orders.create'))
            ->assertSee('Sin conexión a internet', false)
            ->assertSee("addEventListener('offline'", false);
    }

    /** RF-06: una mesa inválida (0, negativa, no numérica) degrada a domicilio. */
    public function test_invalid_table_param_falls_back_to_domicilio(): void
    {
        $this->get(route('orders.create', ['mesa' => 0]))->assertDontSee('Mesa 0');
        $this->get(route('orders.create', ['mesa' => 'abc']))->assertSee('Dirección de entrega');
    }

    /** Una mesa por encima del máximo del comercio también degrada a domicilio. */
    public function test_table_above_maximum_falls_back_to_domicilio(): void
    {
        config(['comercio.mesas' => 50]);

        $this->get(route('orders.create', ['mesa' => 51]))
            ->assertSee('Dirección de entrega');
    }

    /** RF-08 / RF-15 / RF-17: registrar un pedido presencial asociado a la mesa. */
    public function test_can_register_a_presencial_order(): void
    {
        $dish = Dish::factory()->create(['price' => 10000]);

        $response = $this->post(route('orders.store'), [
            'type'         => 'presencial',
            'table_number' => 5,
            'items'        => [
                ['dish_id' => $dish->id, 'quantity' => 2],
            ],
        ]);

        $order = Order::first();
        // RNF-08: tras registrar, se envía al cobro (URL firmada, no enumerable).
        $response->assertRedirectToSignedRoute('orders.payment', ['order' => $order]);

        $this->assertDatabaseHas('orders', [
            'type'           => 'presencial',
            'table_number'   => 5,
            'address'        => null,
            'status'         => Order::STATUS_RECIBIDO,       // RF-17
            'payment_status' => Order::PAYMENT_PENDIENTE,     // RNF-08: aún sin pagar
        ]);
    }

    /** RF-12: en domicilio la dirección es obligatoria. */
    public function test_domicilio_order_requires_address(): void
    {
        $dish = Dish::factory()->create();

        $response = $this->post(route('orders.store'), [
            'type'  => 'domicilio',
            'items' => [
                ['dish_id' => $dish->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('address');
        $this->assertDatabaseCount('orders', 0);
    }

    /** RF-12 / RF-15: registrar un domicilio con dirección. */
    public function test_can_register_a_domicilio_order_with_address(): void
    {
        $dish = Dish::factory()->create(['price' => 8000]);

        $this->post(route('orders.store'), [
            'type'    => 'domicilio',
            'address' => 'Calle 10 # 20-30',
            'items'   => [
                ['dish_id' => $dish->id, 'quantity' => 1],
            ],
        ]);

        $this->assertDatabaseHas('orders', [
            'type'    => 'domicilio',
            'address' => 'Calle 10 # 20-30',
        ]);
    }

    /** RF-14: el total se calcula en el servidor con el precio de la BD (ignora el del cliente). */
    public function test_total_is_calculated_on_server_from_db_price(): void
    {
        $dish = Dish::factory()->create(['price' => 10000]);

        $this->post(route('orders.store'), [
            'type'         => 'presencial',
            'table_number' => 1,
            'items'        => [
                ['dish_id' => $dish->id, 'quantity' => 3],
            ],
        ]);

        // 3 × 10000 = 30000, calculado en servidor.
        $this->assertDatabaseHas('orders', ['total' => 30000.00]);
        $this->assertDatabaseHas('order_items', [
            'dish_id'    => $dish->id,
            'unit_price' => 10000.00,
            'quantity'   => 3,
            'subtotal'   => 30000.00,
        ]);
    }

    /** RF-16: no se puede confirmar un pedido con el carrito vacío. */
    public function test_cannot_place_order_with_empty_cart(): void
    {
        $response = $this->post(route('orders.store'), [
            'type'         => 'presencial',
            'table_number' => 1,
            'items'        => [],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('orders', 0);
    }

    /** RF-05 (refuerzo en servidor): un plato no disponible en el carrito se rechaza. */
    public function test_cannot_order_an_unavailable_dish(): void
    {
        $dish = Dish::factory()->unavailable()->create();

        $response = $this->post(route('orders.store'), [
            'type'         => 'presencial',
            'table_number' => 1,
            'items'        => [
                ['dish_id' => $dish->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('orders', 0);
    }

    /** RF-15: el pedido congela nombre y precio del plato (snapshot histórico). */
    public function test_order_item_freezes_dish_name_and_price(): void
    {
        $dish = Dish::factory()->create(['name' => 'Ajiaco', 'price' => 24000]);

        $this->post(route('orders.store'), [
            'type'         => 'presencial',
            'table_number' => 2,
            'items'        => [
                ['dish_id' => $dish->id, 'quantity' => 1],
            ],
        ]);

        $this->assertDatabaseHas('order_items', [
            'dish_id'    => $dish->id,
            'dish_name'  => 'Ajiaco',
            'unit_price' => 24000.00,
        ]);
    }

    /** La confirmación NO es enumerable: sin firma válida devuelve 403; con firma, 200. */
    public function test_confirmation_requires_a_valid_signature(): void
    {
        $order = Order::factory()->create();

        // Acceso directo por ID (enumeración) sin firma → 403.
        $this->get(route('orders.confirmation', $order))->assertForbidden();

        // Con la URL firmada (la que genera el sistema al confirmar) → 200.
        $signed = URL::signedRoute('orders.confirmation', ['order' => $order]);
        $this->get($signed)->assertOk();
    }

    /** Anti-spam: POST /pedido está limitado a 10 por minuto por IP (throttle). */
    public function test_order_endpoint_is_rate_limited(): void
    {
        $dish = Dish::factory()->create();
        $payload = [
            'type'         => 'presencial',
            'table_number' => 1,
            'items'        => [['dish_id' => $dish->id, 'quantity' => 1]],
        ];

        // Las primeras 10 peticiones se aceptan (no 429).
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('orders.store'), $payload)->assertStatus(302);
        }

        // La 11ª supera el límite → 429 Too Many Requests.
        $this->post(route('orders.store'), $payload)->assertStatus(429);
    }
}
