<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * RNF-07 + RNF-10: apagar el módulo de comanda (encendido por defecto) quita
 * su ruta (404) y su botón del detalle del pedido.
 */
class ComandaModuleDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('MODULE_COMANDA=false');
        $_ENV['MODULE_COMANDA'] = 'false';
        $_SERVER['MODULE_COMANDA'] = 'false';

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('MODULE_COMANDA');
        unset($_ENV['MODULE_COMANDA'], $_SERVER['MODULE_COMANDA']);
    }

    public function test_comanda_route_is_gone_when_disabled(): void
    {
        $this->assertFalse(Route::has('admin.orders.comanda'));

        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get('/panel/pedidos/'.$order->id.'/comanda')
            ->assertNotFound();
    }

    public function test_comanda_button_is_hidden_when_disabled(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertDontSee('Comanda');
    }
}
