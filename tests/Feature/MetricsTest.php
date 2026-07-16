<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Observabilidad: endpoint /metrics en formato Prometheus (medir RNF-02/03).
 */
class MetricsTest extends TestCase
{
    use RefreshDatabase;

    /** /metrics exige autenticación. */
    public function test_guest_cannot_access_metrics(): void
    {
        $this->get(route('admin.metrics'))->assertRedirect(route('login'));
    }

    /** Expone métricas en formato Prometheus con los valores actuales. */
    public function test_metrics_are_exposed_in_prometheus_format(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.metrics'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/plain; version=0.0.4; charset=utf-8');
        $response->assertSee('mesaqr_orders_total 3', false);   // 3 pedidos creados
        $response->assertSee('# TYPE mesaqr_orders_total counter', false);
        $response->assertSee('mesaqr_memory_peak_bytes', false); // RNF-03
    }
}
