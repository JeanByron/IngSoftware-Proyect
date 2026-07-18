<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-20: máquina de estados (sólo avanza) + bitácora de auditoría de los
 * cambios de estado del pedido.
 */
class OrderAuditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    /** Avanzar el estado registra quién, cuándo y de qué estado a cuál. */
    public function test_valid_transition_is_recorded_in_the_audit_log(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->status(Order::STATUS_RECIBIDO)->create();

        $this->actingAs($admin)->patch(
            route('admin.orders.update-status', $order),
            ['status' => Order::STATUS_EN_PREPARACION]
        )->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_EN_PREPARACION,
        ]);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id'    => $order->id,
            'user_id'     => $admin->id,
            'from_status' => Order::STATUS_RECIBIDO,
            'to_status'   => Order::STATUS_EN_PREPARACION,
        ]);
    }

    /** No se puede retroceder: el estado no cambia y no se registra nada. */
    public function test_backward_transition_is_rejected(): void
    {
        $order = Order::factory()->status(Order::STATUS_LISTO)->create();

        $this->actingAs($this->admin())->patch(
            route('admin.orders.update-status', $order),
            ['status' => Order::STATUS_RECIBIDO]
        )->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_LISTO, // no cambió
        ]);
        $this->assertDatabaseCount('order_status_logs', 0);
    }

    /** Tampoco se acepta "avanzar" al mismo estado. */
    public function test_same_status_transition_is_rejected(): void
    {
        $order = Order::factory()->status(Order::STATUS_RECIBIDO)->create();

        $this->actingAs($this->admin())->patch(
            route('admin.orders.update-status', $order),
            ['status' => Order::STATUS_RECIBIDO]
        )->assertSessionHasErrors('status');

        $this->assertDatabaseCount('order_status_logs', 0);
    }

    /** allowedNextStatuses() sólo devuelve los estados posteriores al actual. */
    public function test_allowed_next_statuses(): void
    {
        $recibido = Order::factory()->status(Order::STATUS_RECIBIDO)->make();
        $this->assertSame(
            [Order::STATUS_EN_PREPARACION, Order::STATUS_LISTO, Order::STATUS_ENTREGADO],
            $recibido->allowedNextStatuses()
        );

        $entregado = Order::factory()->status(Order::STATUS_ENTREGADO)->make();
        $this->assertSame([], $entregado->allowedNextStatuses());
    }

    /** El detalle del pedido muestra la bitácora de cambios. */
    public function test_order_detail_shows_the_audit_trail(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->status(Order::STATUS_RECIBIDO)->create();

        $this->actingAs($admin)->patch(
            route('admin.orders.update-status', $order),
            ['status' => Order::STATUS_EN_PREPARACION]
        );

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Historial de cambios')
            ->assertSee($admin->name);
    }
}
