<?php

/*
|--------------------------------------------------------------------------
| Módulos activables (feature flags) — RNF-10
|--------------------------------------------------------------------------
| El diferenciador del proyecto: una plantilla que se adapta a cada comercio
| encendiendo/apagando módulos SIN tocar el código fuente (RNF-24). Cada flag
| es un booleano leído del .env; el módulo apagado desaparece en 3 capas
| (rutas -> 404, navegación -> oculta, lógica -> inactiva). Ver GuiaEstilo.md
| y ModularidadFeatureFlags.md.
|
| Los módulos BÁSICOS (menú, pedidos de cliente, panel de pedidos y QR por
| mesa) son el núcleo de MesaQR y NO llevan flag: siempre están activos.
*/

return [

    // RNF-16: exportación de catálogo y ventas en CSV.
    'export'   => env('MODULE_EXPORT', true),

    // Observabilidad: endpoint /metrics en formato Prometheus.
    'metrics'  => env('MODULE_METRICS', false),

    // RNF-05: enlaces a redes sociales en el pie del cliente.
    'redes'    => env('MODULE_REDES', true),

    // Reserva de mesas (módulo completo gobernado por su flag).
    'reservas' => env('MODULE_RESERVAS', false),

    // RNF-07: comanda/ticket de cocina imprimible por pedido.
    'comanda'  => env('MODULE_COMANDA', true),

    // Nota: el cobro del pedido (RNF-08) NO es un módulo activable: es un paso
    // obligatorio del flujo de pedido (pasarela simulada, driver real
    // enchufable). Por eso no lleva flag aquí.

];
