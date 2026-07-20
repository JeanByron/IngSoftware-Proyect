<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * RNF-18: en producción se fuerza HTTPS para que toda URL generada viaje
 * cifrada. En local se omite para no romper `php artisan serve` (http).
 */
class HttpsProductionTest extends TestCase
{
    /** En producción, las URLs generadas salen con esquema https. */
    public function test_production_forces_https_scheme(): void
    {
        // Se simula el entorno de producción y se re-ejecuta el boot del
        // provider (donde vive la regla), sin depender del despliegue real.
        $this->app->detectEnvironment(fn () => 'production');
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/pedido'));
    }

    /** En local/testing NO se fuerza https (para no romper el servidor local). */
    public function test_local_environment_keeps_http(): void
    {
        // El entorno de prueba es 'testing'; el provider no fuerza el esquema.
        $this->assertStringStartsWith('http://', url('/pedido'));
    }
}
