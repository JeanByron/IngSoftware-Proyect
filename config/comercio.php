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

    // RNF-05: perfiles de redes sociales del comercio (opcionales).
    'redes' => [
        'instagram' => env('SOCIAL_INSTAGRAM'),
        'facebook'  => env('SOCIAL_FACEBOOK'),
        'tiktok'    => env('SOCIAL_TIKTOK'),
    ],

];
