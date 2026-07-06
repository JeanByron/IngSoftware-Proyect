<?php

namespace Database\Seeders;

use App\Models\Dish;
use Illuminate\Database\Seeder;

/**
 * Platos de ejemplo para probar el menú y los flujos de pedido.
 */
class DishSeeder extends Seeder
{
    public function run(): void
    {
        $dishes = [
            ['name' => 'Bandeja paisa',        'description' => 'Frijoles, arroz, carne, chicharrón, huevo y arepa.', 'price' => 28000, 'is_available' => true],
            ['name' => 'Ajiaco santafereño',   'description' => 'Sopa de pollo con tres papas, guascas y mazorca.',    'price' => 24000, 'is_available' => true],
            ['name' => 'Hamburguesa clásica',  'description' => 'Carne de res, queso, lechuga, tomate y papas.',       'price' => 19000, 'is_available' => true],
            ['name' => 'Pizza margarita',      'description' => 'Salsa de tomate, mozzarella y albahaca.',             'price' => 32000, 'is_available' => true],
            ['name' => 'Ensalada César',       'description' => 'Lechuga, crutones, parmesano y aderezo César.',        'price' => 16000, 'is_available' => true],
            ['name' => 'Limonada de coco',     'description' => 'Bebida fría de limón y coco.',                        'price' => 8000,  'is_available' => true],
            ['name' => 'Postre del día',       'description' => 'Consultar disponibilidad con el mesero.',             'price' => 10000, 'is_available' => false],
        ];

        foreach ($dishes as $dish) {
            Dish::create($dish);
        }
    }
}
