<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-20: bitácora inalterable de auditoría de los cambios en el catálogo y los
 * precios (quién, cuándo, qué). Complementa la auditoría de estados de pedido.
 */
class CatalogAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_dish_is_audited(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('dishes.store'), [
            'name'  => 'Plato nuevo',
            'price' => 10000,
        ]);

        $dish = Dish::firstWhere('name', 'Plato nuevo');

        $this->assertDatabaseHas('dish_audit_logs', [
            'dish_id'   => $dish->id,
            'user_id'   => $admin->id,
            'action'    => 'created',
            'new_price' => 10000.00,
        ]);
    }

    public function test_price_change_is_audited_with_old_and_new(): void
    {
        $admin = User::factory()->create();
        $dish  = Dish::factory()->create(['price' => 10000]);

        $this->actingAs($admin)->put(route('dishes.update', $dish), [
            'name'  => $dish->name,
            'price' => 15000,
        ]);

        $this->assertDatabaseHas('dish_audit_logs', [
            'dish_id'   => $dish->id,
            'user_id'   => $admin->id,
            'action'    => 'updated',
            'old_price' => 10000.00,   // precio anterior
            'new_price' => 15000.00,   // precio nuevo
        ]);
    }

    public function test_deletion_is_audited(): void
    {
        $admin = User::factory()->create();
        $dish  = Dish::factory()->create(['name' => 'A borrar']);

        $this->actingAs($admin)->delete(route('dishes.destroy', $dish));

        $this->assertDatabaseHas('dish_audit_logs', [
            'user_id'   => $admin->id,
            'action'    => 'deleted',
            'dish_name' => 'A borrar',
            'dish_id'   => null,       // el plato ya no existe; se conserva el nombre
        ]);
    }

    /** RNF-20: la bitácora del catálogo se ve desde el panel (sólo lectura). */
    public function test_audit_page_lists_catalog_changes(): void
    {
        $admin = User::factory()->create();
        $dish  = Dish::factory()->create(['name' => 'Ajiaco', 'price' => 10000]);

        $this->actingAs($admin)->put(route('dishes.update', $dish), [
            'name'  => 'Ajiaco',
            'price' => 15000,
        ]);

        $this->actingAs($admin)->get(route('admin.dishes.audit'))
            ->assertOk()
            ->assertSee('Historial de cambios del menú')
            ->assertSee('Ajiaco')
            ->assertSee('15.000');   // precio nuevo mostrado
    }

    public function test_guest_cannot_access_the_audit_page(): void
    {
        $this->get(route('admin.dishes.audit'))->assertRedirect(route('login'));
    }
}
