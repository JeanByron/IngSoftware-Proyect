<?php

/*
|--------------------------------------------------------------------------
| Configuración del comercio (branding y enlaces)
|--------------------------------------------------------------------------
| Valores editables por cada restaurante SIN tocar el código fuente (RNF-24):
| se definen en el archivo .env. Aquí viven los enlaces a redes sociales
| (RNF-05); un enlace vacío no se muestra en la interfaz.
*/

return [

    // RNF-24: identidad visual del comercio, editable por .env.
    'nombre' => env('COMERCIO_NOMBRE', "Balcoa's Café"),
    'logo'   => env('COMERCIO_LOGO', '/img/logo.png'),

    // Número de mesas del comercio (para el QR presencial). Default 50.
    'mesas'  => (int) env('COMERCIO_MESAS', 50),

    // RNF-05: perfiles de redes sociales del comercio (opcionales).
    'redes' => [
        'instagram' => env('SOCIAL_INSTAGRAM'),
        'facebook'  => env('SOCIAL_FACEBOOK'),
        'tiktok'    => env('SOCIAL_TIKTOK'),
    ],

];
