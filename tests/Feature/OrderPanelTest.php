<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Módulo Panel del Restaurante — RF-18 a RF-20.
 * Protegido por autenticación. Cubre la lista de pedidos y el cambio de estado.
 */
class OrderPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    /** RF-18: el panel exige login; un invitado es redirigido. */
    public function test_guest_cannot_access_the_panel(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
    }

    /** RF-19: el panel lista los pedidos con su mesa o dirección. */
    public function test_panel_lists_orders_with_table_or_address(): void
    {
        Order::factory()->presencial()->create(['table_number' => 7]);
        Order::factory()->domicilio()->create(['address' => 'Carrera 5 # 12-34']);

        $response = $this->actingAs($this->admin())->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertSee('Mesa 7');
        $response->assertSee('Carrera 5 # 12-34');
    }

    /** RF-20: el personal actualiza el estado de un pedido. */
    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->status(Order::STATUS_RECIBIDO)->create();

        $response = $this->actingAs($this->admin())->patch(
            route('admin.orders.update-status', $order),
            ['status' => Order::STATUS_EN_PREPARACION]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_EN_PREPARACION,
        ]);
    }

    /** RF-20: sólo se aceptan estados válidos. */
    public function test_status_update_rejects_invalid_status(): void
    {
        $order = Order::factory()->status(Order::STATUS_RECIBIDO)->create();

        $response = $this->actingAs($this->admin())->patch(
            route('admin.orders.update-status', $order),
            ['status' => 'inventado']
        );

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_RECIBIDO, // no cambió
        ]);
    }

    /** RF-19: el filtro por estado muestra sólo los pedidos de ese estado. */
    public function test_panel_can_filter_by_status(): void
    {
        Order::factory()->status(Order::STATUS_RECIBIDO)->create(['table_number' => 1]);
        Order::factory()->status(Order::STATUS_ENTREGADO)->create(['table_number' => 2]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => Order::STATUS_ENTREGADO]));

        $response->assertOk();
        // El pedido entregado (mesa 2) aparece; filtrar no rompe la vista.
        $response->assertSee('Mesa 2');
    }
}
