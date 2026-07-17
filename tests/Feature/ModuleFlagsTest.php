<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * RNF-10: módulos activables (feature flags).
 *
 * Verifica el corte en 3 capas de un módulo APAGADO: la ruta no se registra
 * (404 real, no sólo un botón oculto) y su navegación no aparece. El caso
 * ENCENDIDO de un módulo flagueado lo cubren ExportTest (export, on por
 * defecto) y MetricsTest (metrics, encendido en su setUp).
 */
class ModuleFlagsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * El módulo de métricas está apagado por defecto: su ruta NO existe,
     * aunque se escriba la URL a mano responde 404.
     */
    public function test_disabled_module_route_is_not_registered(): void
    {
        $this->assertFalse(Route::has('admin.metrics'));

        $this->actingAs(User::factory()->create())
            ->get('/metrics')
            ->assertNotFound();
    }

    /**
     * Un módulo encendido por defecto (export) sí registra su ruta y su
     * tarjeta aparece en el dashboard.
     */
    public function test_enabled_module_is_registered_and_visible(): void
    {
        $this->assertTrue(Route::has('admin.export.ventas'));

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Exportar ventas CSV');
    }
}
