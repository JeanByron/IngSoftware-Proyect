<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-06: generación del QR por mesa (codifica /pedido?mesa=N).
 */
class TableQrTest extends TestCase
{
    use RefreshDatabase;

    /** La generación del QR exige autenticación (panel). */
    public function test_guest_cannot_access_the_qr(): void
    {
        $this->get(route('admin.tables.qr', ['mesa' => 5]))
            ->assertRedirect(route('login'));
    }

    /** El admin obtiene una página con el QR (SVG) de la mesa. */
    public function test_admin_gets_the_qr_for_a_table(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.tables.qr', ['mesa' => 12]));

        $response->assertOk();
        $response->assertSee('Mesa 12');
        $response->assertSee('<svg', false);              // se renderizó un QR SVG
        $response->assertSee('mesa=12');                  // codifica la URL presencial
    }

    /** Una mesa inválida (0 o negativa) no genera QR. */
    public function test_invalid_table_number_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.tables.qr', ['mesa' => 0]))
            ->assertNotFound();
    }

    /** Navegación entre mesas: enlaces a la anterior y la siguiente. */
    public function test_navigation_links_between_tables(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.tables.qr', ['mesa' => 5]))
            ->assertSee(route('admin.tables.qr', ['mesa' => 4]), false)  // ◀ anterior
            ->assertSee(route('admin.tables.qr', ['mesa' => 6]), false); // siguiente ▶
    }

    /** En la mesa 1 no hay enlace a la mesa 0 (anterior deshabilitado). */
    public function test_first_table_has_no_previous_link(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.tables.qr', ['mesa' => 1]))
            ->assertOk()
            ->assertDontSee(route('admin.tables.qr', ['mesa' => 0]), false)
            ->assertSee(route('admin.tables.qr', ['mesa' => 2]), false); // sí a la siguiente
    }
}
