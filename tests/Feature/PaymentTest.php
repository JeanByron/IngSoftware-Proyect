<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Services\Payments\FakePaymentGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * RNF-08: el cobro es un paso OBLIGATORIO del flujo de pedido (no un módulo
 * activable). store() -> pago (firmado) -> pasarela -> confirmación (firmada).
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** La app resuelve la pasarela simulada por defecto (binding en el provider). */
    public function test_default_gateway_is_the_fake_one(): void
    {
        $this->assertInstanceOf(FakePaymentGateway::class, app(PaymentGateway::class));
    }

    /** La página de pago no es enumerable: sin firma válida devuelve 403. */
    public function test_payment_page_requires_a_valid_signature(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.payment', $order))->assertForbidden();

        $this->get(URL::signedRoute('orders.payment', ['order' => $order]))->assertOk();
    }

    /** Flujo completo: registrar -> pagar -> confirmación, con el pedido marcado como pagado. */
    public function test_full_flow_registers_pays_and_confirms(): void
    {
        $dish = Dish::factory()->create(['price' => 10000]);

        // 1) Registrar el pedido: redirige al cobro firmado.
        $store = $this->post(route('orders.store'), [
            'type'         => 'presencial',
            'table_number' => 3,
            'items'        => [['dish_id' => $dish->id, 'quantity' => 2]],
        ]);

        $order = Order::first();
        $store->assertRedirectToSignedRoute('orders.payment', ['order' => $order]);
        $this->assertFalse($order->isPaid());

        // 2) Ver la página de pago (firmada).
        $this->get(URL::signedRoute('orders.payment', ['order' => $order]))
            ->assertOk()
            ->assertSee('Total a pagar');

        // 3) Pagar (POST firmado) -> redirige a la confirmación firmada.
        $pay = $this->post(URL::signedRoute('orders.payment.process', ['order' => $order]), [
            'payment_method' => 'tarjeta',
        ]);
        $pay->assertRedirectToSignedRoute('orders.confirmation', ['order' => $order]);

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertSame('tarjeta', $order->payment_method);
        $this->assertNotNull($order->payment_reference);
        $this->assertNotNull($order->paid_at);
    }

    /** El método de pago es obligatorio y debe ser uno de los ofrecidos. */
    public function test_payment_requires_a_valid_method(): void
    {
        $order = Order::factory()->create(['payment_status' => Order::PAYMENT_PENDIENTE]);

        $this->post(URL::signedRoute('orders.payment.process', ['order' => $order]), [
            'payment_method' => 'bitcoin',
        ])->assertSessionHasErrors('payment_method');

        $this->assertFalse($order->refresh()->isPaid());
    }

    /** Un pedido ya pagado no se vuelve a cobrar: salta a la confirmación. */
    public function test_paid_order_skips_payment(): void
    {
        $order = Order::factory()->create([
            'payment_status'    => Order::PAYMENT_PAGADO,
            'payment_reference' => 'SIM-ABC123',
        ]);

        $this->get(URL::signedRoute('orders.payment', ['order' => $order]))
            ->assertRedirectToSignedRoute('orders.confirmation', ['order' => $order]);
    }

    /**
     * RNF-08 (sustentación de "estructura lista para pasarela real"): si se
     * enchufa OTRA implementación de PaymentGateway, el flujo la usa sin tocar
     * el controlador. Aquí un driver alterno aprueba con una referencia propia.
     */
    public function test_flow_uses_whatever_gateway_is_bound(): void
    {
        $this->app->bind(PaymentGateway::class, fn () => new class implements PaymentGateway {
            public function charge(Order $order, string $method): PaymentResult
            {
                return PaymentResult::approved('ALT-REF-999');
            }
        });

        $order = Order::factory()->create(['payment_status' => Order::PAYMENT_PENDIENTE]);

        $this->post(URL::signedRoute('orders.payment.process', ['order' => $order]), [
            'payment_method' => 'tarjeta',
        ])->assertRedirectToSignedRoute('orders.confirmation', ['order' => $order]);

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertSame('ALT-REF-999', $order->payment_reference); // referencia del driver alterno
    }

    /**
     * RNF-08: una pasarela real puede RECHAZAR el cobro. El flujo maneja el
     * rechazo: vuelve con error y el pedido queda SIN pagar.
     */
    public function test_declined_payment_keeps_the_order_unpaid(): void
    {
        $this->app->bind(PaymentGateway::class, fn () => new class implements PaymentGateway {
            public function charge(Order $order, string $method): PaymentResult
            {
                return PaymentResult::declined('Fondos insuficientes.');
            }
        });

        $order = Order::factory()->create(['payment_status' => Order::PAYMENT_PENDIENTE]);

        $this->post(URL::signedRoute('orders.payment.process', ['order' => $order]), [
            'payment_method' => 'tarjeta',
        ])->assertSessionHasErrors('payment_method');

        $order->refresh();
        $this->assertFalse($order->isPaid());
        $this->assertNull($order->paid_at);
    }
}
