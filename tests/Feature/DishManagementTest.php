<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Módulo de Gestión de Menú — RF-01 a RF-05.
 * Protege el CRUD de platos y la regla de disponibilidad.
 */
class DishManagementTest extends TestCase
{
    use RefreshDatabase;

    /** Usuario autenticado del panel para las acciones protegidas. */
    private function admin(): User
    {
        return User::factory()->create();
    }

    /** El CRUD de menú exige autenticación (RF-18): sin sesión redirige a login. */
    public function test_guest_cannot_access_dish_management(): void
    {
        $this->get(route('dishes.index'))->assertRedirect(route('login'));
        $this->get(route('dishes.create'))->assertRedirect(route('login'));
    }

    /** RF-01: el administrador crea un plato con nombre, descripción y precio. */
    public function test_admin_can_create_a_dish(): void
    {
        $response = $this->actingAs($this->admin())->post(route('dishes.store'), [
            'name'         => 'Bandeja paisa',
            'description'  => 'Plato típico antioqueño.',
            'price'        => 28000,
            'is_available' => 1,
        ]);

        $response->assertRedirect(route('dishes.index'));
        $this->assertDatabaseHas('dishes', [
            'name'  => 'Bandeja paisa',
            'price' => 28000.00,
        ]);
    }

    /** RF-01: el nombre y el precio son obligatorios. */
    public function test_creating_a_dish_requires_name_and_price(): void
    {
        $response = $this->actingAs($this->admin())->post(route('dishes.store'), [
            'description' => 'Sin nombre ni precio.',
        ]);

        $response->assertSessionHasErrors(['name', 'price']);
        $this->assertDatabaseCount('dishes', 0);
    }

    /** RF-02: el administrador edita los datos de un plato existente. */
    public function test_admin_can_update_a_dish(): void
    {
        $dish = Dish::factory()->create(['name' => 'Nombre viejo', 'price' => 10000]);

        $response = $this->actingAs($this->admin())->put(route('dishes.update', $dish), [
            'name'         => 'Nombre nuevo',
            'description'  => 'Actualizado.',
            'price'        => 15000,
            'is_available' => 1,
        ]);

        $response->assertRedirect(route('dishes.index'));
        $this->assertDatabaseHas('dishes', [
            'id'    => $dish->id,
            'name'  => 'Nombre nuevo',
            'price' => 15000.00,
        ]);
    }

    /** RF-03: el administrador elimina un plato. */
    public function test_admin_can_delete_a_dish(): void
    {
        $dish = Dish::factory()->create();

        $response = $this->actingAs($this->admin())->delete(route('dishes.destroy', $dish));

        $response->assertRedirect(route('dishes.index'));
        $this->assertDatabaseMissing('dishes', ['id' => $dish->id]);
    }

    /** RF-04: alternar la disponibilidad de un plato (toggle). */
    public function test_admin_can_toggle_availability(): void
    {
        $dish = Dish::factory()->create(['is_available' => true]);

        $this->actingAs($this->admin())->patch(route('dishes.toggle', $dish));
        $this->assertFalse($dish->fresh()->is_available);

        $this->actingAs($this->admin())->patch(route('dishes.toggle', $dish));
        $this->assertTrue($dish->fresh()->is_available);
    }

    /** RF-05: la vista de cliente muestra sólo los platos disponibles. */
    public function test_client_view_shows_only_available_dishes(): void
    {
        $available   = Dish::factory()->create(['name' => 'Plato disponible']);
        $unavailable = Dish::factory()->unavailable()->create(['name' => 'Plato agotado']);

        $response = $this->get(route('orders.create'));

        $response->assertSee('Plato disponible');
        $response->assertDontSee('Plato agotado');
    }
}
