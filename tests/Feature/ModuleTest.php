<?php

namespace Tests\Feature;

use App\Support\Module;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * RNF-10 / RNF-13: utilidades de módulos activables (Module::enabled / safe).
 */
class ModuleTest extends TestCase
{
    /** RNF-10: enabled() refleja el flag de config/modules.php. */
    public function test_enabled_reads_the_module_flag(): void
    {
        config(['modules.demo' => true]);
        $this->assertTrue(Module::enabled('demo'));

        config(['modules.demo' => false]);
        $this->assertFalse(Module::enabled('demo'));

        // Módulo inexistente = apagado.
        $this->assertFalse(Module::enabled('no_existe'));
    }

    /** RNF-13: safe() devuelve el valor cuando no hay error. */
    public function test_safe_returns_value_on_success(): void
    {
        $this->assertSame(42, Module::safe(fn () => 42, 0));
    }

    /** RNF-13: safe() aísla la excepción, registra y devuelve el fallback. */
    public function test_safe_isolates_exceptions_and_logs(): void
    {
        Log::spy();

        $result = Module::safe(fn () => throw new \RuntimeException('boom'), 'degradado', 'prueba');

        $this->assertSame('degradado', $result);
        Log::shouldHaveReceived('warning')->once();
    }
}
