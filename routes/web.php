<?php

use App\Http\Controllers\Admin\OrderPanelController;
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
    Route::post('/pedido', 'store')->name('orders.store');          // RF-15
    Route::get('/pedido/{order}/confirmacion', 'confirmation')->name('orders.confirmation');
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

    // Perfil del usuario (Breeze).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
| MÓDULO FUTURO — Reserva de mesas
| Documentado en el alcance como módulo futuro; la arquitectura modular
| permite añadirlo aquí sin afectar lo existente. Fuera del MVP.
*/

require __DIR__ . '/auth.php';
