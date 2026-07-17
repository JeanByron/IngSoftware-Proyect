<?php

use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\MetricsController;
use App\Http\Controllers\Admin\OrderPanelController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\TableQrController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MesaQR — Rutas web organizadas por módulo
|--------------------------------------------------------------------------
| Plantilla web modular de pedidos para restaurantes con detección de
| contexto por código QR. Los requerimientos (RF-xx) se referencian en cada
| bloque para trazabilidad con el documento de requerimientos.
*/

/*
| MÓDULO 0 — Inicio
| La raíz redirige al flujo de cliente (domicilio por defecto, RF-10).
*/
Route::get('/', fn () => redirect()->route('orders.create'))->name('home');

/*
|--------------------------------------------------------------------------
| MÓDULO 1 — Flujo de Cliente (público, sin autenticación)
|--------------------------------------------------------------------------
| Detección de contexto por QR: /pedido?mesa=N -> presencial; sin mesa -> domicilio.
| RF-05, RF-06, RF-07, RF-08, RF-09, RF-10, RF-11, RF-12, RF-13, RF-14, RF-15, RF-16, RF-17.
*/
Route::controller(OrderController::class)->group(function () {
    Route::get('/pedido', 'create')->name('orders.create');         // RF-06 / RF-10
    // El registro de pedidos es público: se limita a 10 por minuto por IP
    // (throttle) para frenar el envío masivo de pedidos falsos.
    Route::post('/pedido', 'store')->middleware('throttle:10,1')->name('orders.store'); // RF-15
    // 'signed' exige que la URL lleve una firma válida (se genera al confirmar):
    // impide acceder a la confirmación de otro pedido enumerando su ID.
    Route::get('/pedido/{order}/confirmacion', 'confirmation')
        ->middleware('signed')->name('orders.confirmation');
});

/*
|--------------------------------------------------------------------------
| MÓDULO 2 + 3 — Administración (protegido por autenticación, RF-18)
|--------------------------------------------------------------------------
| Requiere sesión iniciada (Laravel Breeze). Agrupa la gestión de menú y el
| panel de pedidos del restaurante.
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard del personal (vista de Breeze adaptada).
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    /*
    | MÓDULO 2 — Gestión de Menú (CRUD de platos)
    | RF-01, RF-02, RF-03, RF-04, RF-05.
    */
    Route::resource('dishes', DishController::class)->except(['show']);
    Route::patch('dishes/{dish}/toggle', [DishController::class, 'toggle'])
        ->name('dishes.toggle'); // RF-04: alternar disponibilidad

    /*
    | MÓDULO 3 — Panel del Restaurante (pedidos entrantes)
    | RF-19, RF-20.
    */
    Route::prefix('panel')->name('admin.orders.')->controller(OrderPanelController::class)->group(function () {
        Route::get('/pedidos', 'index')->name('index');                       // RF-19
        Route::get('/pedidos/{order}', 'show')->name('show');
        Route::patch('/pedidos/{order}/estado', 'updateStatus')->name('update-status'); // RF-20
    });

    // RNF-06: QR imprimible por mesa (codifica /pedido?mesa=N).
    Route::get('/panel/mesas/{mesa}/qr', [TableQrController::class, 'show'])
        ->whereNumber('mesa')->name('admin.tables.qr');

    /*
    | MÓDULO ACTIVABLE — Reserva de mesas (RNF-10, flag MODULE_RESERVAS)
    | Módulo completo (modelo + CRUD + vistas) gobernado por su flag: si está
    | apagado, NINGUNA de estas rutas se registra -> /reservas responde 404.
    | Encender el módulo en un comercio = MODULE_RESERVAS=true en su .env.
    */
    if (config('modules.reservas')) {
        Route::resource('reservas', ReservationController::class)
            ->except(['show'])
            ->parameters(['reservas' => 'reservation'])
            ->names('admin.reservations');
    }

    // RNF-16: respaldo del catálogo y de las ventas en CSV.
    // Módulo opcional (RNF-10): si el flag está apagado, las rutas no se
    // registran -> /panel/export/*.csv responde 404 aunque se escriba a mano.
    if (config('modules.export')) {
        Route::get('/panel/export/catalogo.csv', [ExportController::class, 'catalogo'])->name('admin.export.catalogo');
        Route::get('/panel/export/ventas.csv', [ExportController::class, 'ventas'])->name('admin.export.ventas');
    }

    // Observabilidad: métricas en formato Prometheus (medir RNF-02/03).
    // Módulo opcional (RNF-10): normalmente sólo se enciende en entornos con
    // monitorización (Prometheus).
    if (config('modules.metrics')) {
        Route::get('/metrics', MetricsController::class)->name('admin.metrics');
    }

    // Perfil del usuario (Breeze).
    $profilePath = '/profile';
    Route::get($profilePath, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch($profilePath, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete($profilePath, [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
| MÓDULO FUTURO — Reserva de mesas
| Documentado en el alcance como módulo futuro; la arquitectura modular
| permite añadirlo aquí sin afectar lo existente. Fuera del MVP.
*/

require __DIR__ . '/auth.php';
