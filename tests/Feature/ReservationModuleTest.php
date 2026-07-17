<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Módulo Reservas (RNF-10). Estas pruebas encienden el flag MODULE_RESERVAS
 * ANTES de bootear la app para que sus rutas queden registradas y se pueda
 * ejercitar el CRUD. El caso APAGADO (404) lo cubre ModuleFlagsTest.
 */
class ReservationModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        putenv('MODULE_RESERVAS=true');
        $_ENV['MODULE_RESERVAS'] = 'true';
        $_SERVER['MODULE_RESERVAS'] = 'true';

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('MODULE_RESERVAS');
        unset($_ENV['MODULE_RESERVAS'], $_SERVER['MODULE_RESERVAS']);
    }

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_guest_cannot_access_reservations(): void
    {
        $this->get(route('admin.reservations.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_list_reservations(): void
    {
        Reservation::factory()->create(['customer_name' => 'Ana Pérez', 'reserved_at' => now()->addDay()]);

        $this->actingAs($this->actingUser())
            ->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertSee('Ana Pérez');
    }

    public function test_staff_can_create_a_reservation(): void
    {
        $this->actingAs($this->actingUser())
            ->post(route('admin.reservations.store'), [
                'customer_name' => 'Carlos Ruiz',
                'phone'         => '3001234567',
                'reserved_at'   => now()->addDays(2)->format('Y-m-d\TH:i'),
                'party_size'    => 4,
                'table_number'  => 7,
            ])
            ->assertRedirectToRoute('admin.reservations.index');

        $this->assertDatabaseHas('reservations', [
            'customer_name' => 'Carlos Ruiz',
            'party_size'    => 4,
            'status'        => Reservation::STATUS_PENDIENTE,
        ]);
    }

    public function test_reservation_requires_a_future_date(): void
    {
        $this->actingAs($this->actingUser())
            ->post(route('admin.reservations.store'), [
                'customer_name' => 'Sin fecha válida',
                'reserved_at'   => now()->subDay()->format('Y-m-d\TH:i'),
                'party_size'    => 2,
            ])
            ->assertSessionHasErrors('reserved_at');

        $this->assertDatabaseMissing('reservations', ['customer_name' => 'Sin fecha válida']);
    }

    public function test_staff_can_update_a_reservation(): void
    {
        $reservation = Reservation::factory()->create(['reserved_at' => now()->addDay()]);

        $this->actingAs($this->actingUser())
            ->put(route('admin.reservations.update', $reservation), [
                'customer_name' => $reservation->customer_name,
                'reserved_at'   => now()->addDays(3)->format('Y-m-d\TH:i'),
                'party_size'    => 6,
                'status'        => Reservation::STATUS_CONFIRMADA,
            ])
            ->assertRedirectToRoute('admin.reservations.index');

        $this->assertDatabaseHas('reservations', [
            'id'         => $reservation->id,
            'party_size' => 6,
            'status'     => Reservation::STATUS_CONFIRMADA,
        ]);
    }

    public function test_staff_can_delete_a_reservation(): void
    {
        $reservation = Reservation::factory()->create();

        $this->actingAs($this->actingUser())
            ->delete(route('admin.reservations.destroy', $reservation))
            ->assertRedirectToRoute('admin.reservations.index');

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }
}
