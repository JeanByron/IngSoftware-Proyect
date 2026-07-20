<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-14 (sustentación de la parte defendible): la app NO depende de servicios
 * externos. Ninguna vista carga recursos de un host de terceros (sin CDN):
 * fuentes self-hosted, assets locales por Vite, QR generado en el servidor.
 *
 * OJO: esto NO es "modo offline" (navegador desconectado, que NO está
 * implementado). Es autonomía de dependencias: la app corre en una máquina o
 * LAN sin internet ni servicios externos.
 */
class AutonomyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Extrae los hosts de todos los src/href absolutos del HTML y afirma que
     * todos son locales (localhost / 127.0.0.1). Sin externos = sin CDN.
     */
    private function assertNoExternalHosts(string $html, string $page): void
    {
        preg_match_all('/(?:src|href)="(https?:\/\/[^"\/]+)/i', $html, $matches);

        foreach (array_unique($matches[1]) as $url) {
            $this->assertMatchesRegularExpression(
                '/\/\/(localhost|127\.0\.0\.1)/i',
                $url,
                "La página {$page} carga un recurso de un host externo: {$url}"
            );
        }
    }

    public function test_client_page_has_no_external_resources(): void
    {
        $html = $this->get(route('orders.create', ['mesa' => 3]))->assertOk()->getContent();
        $this->assertNoExternalHosts($html, '/pedido');
    }

    public function test_login_page_has_no_external_resources(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();
        $this->assertNoExternalHosts($html, '/login');
    }

    public function test_panel_has_no_external_resources(): void
    {
        $html = $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))->assertOk()->getContent();
        $this->assertNoExternalHosts($html, '/dashboard');
    }
}
