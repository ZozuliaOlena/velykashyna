<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'tire',  'name' => 'Шина'],
            ['code' => 'tube',  'name' => 'Камера'],
            ['code' => 'disk',  'name' => 'Диск'],
            ['code' => 'flap',  'name' => 'Флап'],
            ['code' => 'valve', 'name' => 'Вентиль'],
            ['code' => 'ring',  'name' => 'Ущільнювальне кільце'],
        ];

        foreach ($types as $type) {
            ProductType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
