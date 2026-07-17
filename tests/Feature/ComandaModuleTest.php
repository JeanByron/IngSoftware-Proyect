<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-07: comanda/ticket de cocina en texto plano. El módulo está encendido
 * por defecto (MODULE_COMANDA), así que no hace falta tocar el flag. El caso
 * APAGADO lo cubre ComandaModuleDisabledTest.
 */
class ComandaModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_comanda(): void
    {
        $order = Order::factory()->create();

        $this->get(route('admin.orders.comanda', $order))
            ->assertRedirect(route('login'));
    }

    public function test_comanda_is_plain_text_with_order_data(): void
    {
        $order = Order::factory()->presencial()->create(['table_number' => 5]);
        OrderItem::factory()->for($order)->create([
            'dish_name' => 'Bandeja Paisa',
            'quantity'  => 2,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.comanda', $order));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/plain; charset=utf-8');
        $response->assertSee('COMANDA DE COCINA', false);
        $response->assertSee('Pedido #'.$order->id, false);
        $response->assertSee('MESA: 5', false);
        $response->assertSee('2x Bandeja Paisa', false);
    }

    public function test_comanda_shows_address_for_delivery(): void
    {
        $order = Order::factory()->domicilio()->create(['address' => 'Carrera 5 # 12-34']);
        OrderItem::factory()->for($order)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.comanda', $order));

        $response->assertOk();
        $response->assertSee('DOMICILIO', false);
        $response->assertSee('Carrera 5 # 12-34', false);
    }
}
