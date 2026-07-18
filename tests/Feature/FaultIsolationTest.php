<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * RNF-13: el fallo de un módulo OPCIONAL no debe tumbar el flujo básico.
 *
 * Se fuerza una config de redes mal formada (una cadena en vez de un arreglo):
 * sin aislamiento, el array_filter del footer lanzaría un TypeError y la vista
 * del cliente daría 500. Con Module::safe, el footer se degrada y la página
 * (flujo básico de pedido) sigue respondiendo 200.
 */
class FaultIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_broken_optional_module_does_not_crash_client_page(): void
    {
        Log::spy();

        // Módulo de redes encendido pero con config inválida.
        config(['modules.redes' => true]);
        config(['comercio.redes' => 'esto-no-es-un-arreglo']);

        // El flujo básico del cliente sobrevive (200, no 500).
        $this->get(route('orders.create'))->assertOk();

        // Y el fallo quedó registrado (degradación controlada, no silenciosa).
        Log::shouldHaveReceived('warning')->once();
    }
}
