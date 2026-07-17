<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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

    /**
     * RF-03 (caso borde corregido): borrar un plato con pedidos históricos
     * no debe fallar; el order_item conserva su snapshot y dish_id queda en NULL.
     */
    public function test_can_delete_a_dish_that_has_historical_orders(): void
    {
        $dish  = Dish::factory()->create(['name' => 'Ajiaco', 'price' => 24000]);
        $order = Order::factory()->create();
        $item  = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'dish_id'    => $dish->id,
            'dish_name'  => 'Ajiaco',
            'unit_price' => 24000,
        ]);

        $response = $this->actingAs($this->admin())->delete(route('dishes.destroy', $dish));

        $response->assertRedirect(route('dishes.index'));         // no 500
        $this->assertDatabaseMissing('dishes', ['id' => $dish->id]);
        // El histórico sobrevive: dish_id NULL, pero nombre y precio congelados intactos.
        $this->assertDatabaseHas('order_items', [
            'id'         => $item->id,
            'dish_id'    => null,
            'dish_name'  => 'Ajiaco',
            'unit_price' => 24000.00,
        ]);
    }

    /** RNF-01: al crear un plato con imagen, se guarda el archivo y su ruta. */
    public function test_admin_can_create_a_dish_with_image(): void
    {
        Storage::fake('public');

        // Se usa create() con mime explícito (no image()) porque el entorno no
        // tiene la extensión GD; image() la requiere para generar el binario.
        $this->actingAs($this->admin())->post(route('dishes.store'), [
            'name'  => 'Con foto',
            'price' => 12000,
            'image' => UploadedFile::fake()->create('plato.jpg', 200, 'image/jpeg'),
        ])->assertRedirect(route('dishes.index'));

        $dish = Dish::firstWhere('name', 'Con foto');
        $this->assertNotNull($dish->image_path);
        Storage::disk('public')->assertExists($dish->image_path);
    }

    /** RNF-01: sólo se aceptan imágenes válidas (un .pdf es rechazado). */
    public function test_dish_image_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('dishes.store'), [
            'name'  => 'Archivo malo',
            'price' => 9000,
            'image' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('dishes', ['name' => 'Archivo malo']);
    }

    /** RNF-01: reemplazar la imagen borra la anterior (sin archivos huérfanos). */
    public function test_updating_image_deletes_the_previous_file(): void
    {
        Storage::fake('public');

        $dish = Dish::factory()->create([
            'image_path' => UploadedFile::fake()->create('vieja.jpg', 100, 'image/jpeg')->store('dishes', 'public'),
        ]);
        $anterior = $dish->image_path;

        $this->actingAs($this->admin())->put(route('dishes.update', $dish), [
            'name'  => $dish->name,
            'price' => $dish->price,
            'image' => UploadedFile::fake()->create('nueva.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('dishes.index'));

        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($dish->fresh()->image_path);
    }

    /** RNF-01: al eliminar el plato se borra también su imagen. */
    public function test_deleting_a_dish_removes_its_image(): void
    {
        Storage::fake('public');

        $dish = Dish::factory()->create([
            'image_path' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg')->store('dishes', 'public'),
        ]);
        $ruta = $dish->image_path;

        $this->actingAs($this->admin())->delete(route('dishes.destroy', $dish));

        Storage::disk('public')->assertMissing($ruta);
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

    /** RNF-04: el catálogo se cachea; una nueva consulta no vuelve a la BD. */
    public function test_catalog_is_cached(): void
    {
        Cache::forget(Dish::CATALOG_CACHE_KEY);
        Dish::factory()->create(['name' => 'Bandeja']);

        $this->assertFalse(Cache::has(Dish::CATALOG_CACHE_KEY)); // aún no consultado

        Dish::availableCached();                                 // primera lectura → cachea
        $this->assertTrue(Cache::has(Dish::CATALOG_CACHE_KEY));  // ya está en caché
    }

    /** RNF-04: crear/editar/borrar un plato invalida la caché del catálogo. */
    public function test_mutating_a_dish_invalidates_the_catalog_cache(): void
    {
        Dish::availableCached();                                 // llena la caché
        $this->assertTrue(Cache::has(Dish::CATALOG_CACHE_KEY));

        Dish::factory()->create();                               // saved → invalida
        $this->assertFalse(Cache::has(Dish::CATALOG_CACHE_KEY));
    }

    /** RF-05: la vista de cliente muestra sólo los platos disponibles. */
    public function test_client_view_shows_only_available_dishes(): void
    {
        Dish::factory()->create(['name' => 'Plato disponible']);
        Dish::factory()->unavailable()->create(['name' => 'Plato agotado']);

        $response = $this->get(route('orders.create'));

        $response->assertSee('Plato disponible');
        $response->assertDontSee('Plato agotado');
    }
}
