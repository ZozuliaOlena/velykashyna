<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Адмін за замовчуванням. firstOrCreate — ідемпотентно:
        // створюється при першому запуску й не дублюється/не скидається далі.
        User::firstOrCreate(
            ['email' => 'admin@velykashyna.com.ua'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $this->call([
            ProductTypeSeeder::class,
        ]);

        // Демо-каталог (бренди, категорії, 28 товарів із фото) — лише локально,
        // щоб фейкові дані не потрапили в продакшн. Фронтендеру достатньо
        // звичайного `composer setup` / `php artisan migrate --seed`.
        // Порядок важливий: спирається на типи з ProductTypeSeeder вище.
        if (app()->environment('local')) {
            $this->call([
                DemoCatalogSeeder::class,
                BlogSeeder::class,
            ]);
        }
    }
}
