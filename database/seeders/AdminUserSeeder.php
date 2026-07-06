<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuario del personal del restaurante para acceder al panel (RF-18).
 *
 *   Email:    admin@mesaqr.test
 *   Password: password
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mesaqr.test'],
            [
                'name'     => 'Administrador MesaQR',
                'password' => Hash::make('password'),
            ],
        );
    }
}
