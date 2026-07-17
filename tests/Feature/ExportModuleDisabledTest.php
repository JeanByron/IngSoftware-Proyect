<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * RNF-10: demuestra el apagado de un módulo que POR DEFECTO está encendido
 * (export CSV). Se apaga su flag antes de bootear la app; la ruta deja de
 * registrarse y la tarjeta del dashboard desaparece. Es la prueba del
 * "corte real" (rutas + vistas), no cosmético.
 */
class ExportModuleDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('MODULE_EXPORT=false');
        $_ENV['MODULE_EXPORT'] = 'false';
        $_SERVER['MODULE_EXPORT'] = 'false';

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('MODULE_EXPORT');
        unset($_ENV['MODULE_EXPORT'], $_SERVER['MODULE_EXPORT']);
    }

    /** Con el flag apagado, la ruta de export no existe: 404 a mano. */
    public function test_export_routes_are_gone_when_module_disabled(): void
    {
        $this->assertFalse(Route::has('admin.export.ventas'));
        $this->assertFalse(Route::has('admin.export.catalogo'));

        $this->actingAs(User::factory()->create())
            ->get('/panel/export/ventas.csv')
            ->assertNotFound();
    }

    /** Y su tarjeta desaparece del dashboard (RNF-10, capa de vistas). */
    public function test_export_card_is_hidden_when_module_disabled(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Exportar ventas CSV');
    }
}
