<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\Response;

/**
 * Observabilidad: expone métricas de la app en formato de texto Prometheus.
 *
 * Sirve para MEDIR (no acelerar): un Prometheus externo puede scrapear /metrics
 * y graficar latencia/memoria, evidencia útil para RNF-02 (tiempo de carga) y
 * RNF-03 (consumo de memoria). Endpoint protegido por 'auth'.
 */
class MetricsController extends Controller
{
    public function __invoke(): Response
    {
        $metrics = [
            '# HELP mesaqr_orders_total Número total de pedidos registrados.',
            '# TYPE mesaqr_orders_total counter',
            'mesaqr_orders_total ' . Order::count(),

            '# HELP mesaqr_dishes_available Número de platos disponibles en el catálogo.',
            '# TYPE mesaqr_dishes_available gauge',
            'mesaqr_dishes_available ' . Dish::available()->count(),

            '# HELP mesaqr_memory_peak_bytes Pico de memoria del proceso actual (RNF-03).',
            '# TYPE mesaqr_memory_peak_bytes gauge',
            'mesaqr_memory_peak_bytes ' . memory_get_peak_usage(true),
        ];

        // Formato de exposición Prometheus: text/plain, una métrica por línea.
        return response(implode("\n", $metrics) . "\n", 200)
            ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    }
}
