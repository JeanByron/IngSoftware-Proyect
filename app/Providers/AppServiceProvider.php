<?php

namespace App\Providers;

use App\Services\Payments\FakePaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // RNF-08: la app depende de la interfaz PaymentGateway; aquí se decide
        // qué implementación se usa. Para integrar una pasarela real, se cambia
        // esta línea por el driver real (Wompi/MercadoPago/Stripe) — sin tocar
        // el flujo del pedido.
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // RNF-19: política de contraseñas segura, aplicada de forma centralizada
        // a todo formulario que use Password::defaults() (registro, reset, cambio).
        // Mínimo 8 caracteres, con mayúscula, minúscula y número.
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        // RNF-18: en producción se fuerza HTTPS para que toda URL generada (y las
        // redirecciones) viajen cifradas. En local se omite para no romper
        // `php artisan serve`, que sirve por http://127.0.0.1.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
