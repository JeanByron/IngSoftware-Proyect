<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Utilidades de los módulos activables.
 *
 * - enabled() (RNF-10): consulta el flag del módulo en config/modules.php.
 * - safe()    (RNF-13): ejecuta el código de un módulo OPCIONAL de forma
 *   aislada, para que su fallo no tumbe el flujo básico que lo aloja.
 */
class Module
{
    /** RNF-10: ¿está encendido el módulo? */
    public static function enabled(string $name): bool
    {
        return (bool) config("modules.{$name}", false);
    }

    /**
     * RNF-13: aislamiento de fallos por módulo (degradación controlada).
     *
     * Corre $fn; si lanza cualquier excepción/error, lo registra y devuelve
     * $fallback en lugar de propagarlo. Así, un módulo opcional que falla
     * (encendido pero roto) NO derriba la vista o el flujo básico donde se
     * muestra: esa parte simplemente se degrada (desaparece) y el resto sigue.
     *
     * Nota honesta para la sustentación: en un monolito no hay aislamiento de
     * PROCESO; esto es aislamiento a nivel de APLICACIÓN (try/catch + fallback).
     * Los flags dan la modularidad de configuración; esto añade la de fiabilidad.
     *
     * @template T
     * @param  callable():T  $fn
     * @param  T  $fallback
     * @return T
     */
    public static function safe(callable $fn, mixed $fallback = null, ?string $context = null): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::warning('Módulo falló y se degradó'.($context ? ": {$context}" : ''), [
                'exception' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }
}
