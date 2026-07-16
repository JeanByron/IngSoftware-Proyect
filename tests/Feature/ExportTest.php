<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-16: descarga de catálogo y ventas en CSV.
 */
class ExportTest extends TestCase
{
    use RefreshDatabase;

    /** El export exige autenticación. */
    public function test_guest_cannot_export(): void
    {
        $this->get(route('admin.export.catalogo'))->assertRedirect(route('login'));
        $this->get(route('admin.export.ventas'))->assertRedirect(route('login'));
    }

    /** El catálogo se descarga como CSV con las filas esperadas. */
    public function test_admin_can_export_catalog_csv(): void
    {
        Dish::factory()->create(['name' => 'Bandeja paisa', 'price' => 28000]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.export.catalogo'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload('catalogo.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('id,nombre,descripcion,precio,disponible', $csv);
        $this->assertStringContainsString('Bandeja paisa', $csv);
    }

    /** Las ventas se descargan como CSV con las filas esperadas. */
    public function test_admin_can_export_sales_csv(): void
    {
        Order::factory()->presencial()->create(['table_number' => 7, 'total' => 50000]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.export.ventas'));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('id,tipo,mesa,direccion,total,estado,fecha', $csv);
        $this->assertStringContainsString('presencial', $csv);
    }
}
