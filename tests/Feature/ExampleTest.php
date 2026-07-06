<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz redirige al flujo de cliente (RF-10: domicilio por defecto).
     * Ver routes/web.php: Route::get('/', fn () => redirect()->route('orders.create')).
     */
    public function test_the_root_redirects_to_the_order_flow(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('orders.create'));
    }
}
