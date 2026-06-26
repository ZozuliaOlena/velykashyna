<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Support\Translit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Демо-наповнення каталогу: бренди (з логотипами), дерево категорій
 * та 28 товарів із повністю заповненими полями та згенерованими фото.
 *
 * Ідемпотентно: повторний запуск оновлює дані за артикулом/назвою,
 * фото додається лише якщо його ще немає.
 *
 *   php artisan db:seed --class=Database\\Seeders\\DemoCatalogSeeder
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $brands = $this->seedBrands();
        $cats = $this->seedCategories();
        $types = ProductType::pluck('id', 'code');   // ['tire'=>1, ...]

        // Базові типорозміри з повним набором характеристик.
        $sizes = [
            ['raw' => '710/70R38',  'w' => 710,  'p' => 70, 'rim' => 38, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR16', 'li' => '171D', 'spec' => 'STEEL BELTED'],
            ['raw' => '600/65R28',  'w' => 600,  'p' => 65, 'rim' => 28, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR12', 'li' => '154D', 'spec' => 'RADIAL'],
            ['raw' => '520/85R42',  'w' => 520,  'p' => 85, 'rim' => 42, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR16', 'li' => '162A8', 'spec' => 'STEEL BELTED'],
            ['raw' => '480/70R34',  'w' => 480,  'p' => 70, 'rim' => 34, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR14', 'li' => '143D', 'spec' => 'RADIAL'],
            ['raw' => '420/85R30',  'w' => 420,  'p' => 85, 'rim' => 30, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR14', 'li' => '140A8', 'spec' => 'RADIAL'],
            ['raw' => '340/85R24',  'w' => 340,  'p' => 85, 'rim' => 24, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR12', 'li' => '125A8', 'spec' => 'RADIAL'],
            ['raw' => '23.1-26',    'w' => 587,  'p' => null, 'rim' => 26, 'rd' => 'D', 'tt' => 'TT', 'pr' => 'PR12', 'li' => '149A6', 'spec' => 'BIAS'],
            ['raw' => '18.4-34',    'w' => 467,  'p' => null, 'rim' => 34, 'rd' => 'D', 'tt' => 'TT', 'pr' => 'PR10', 'li' => '142A6', 'spec' => 'BIAS'],
            ['raw' => '16.9-30',    'w' => 429,  'p' => null, 'rim' => 30, 'rd' => 'D', 'tt' => 'TT', 'pr' => 'PR8',  'li' => '137A6', 'spec' => 'BIAS'],
            ['raw' => '15.5-38',    'w' => 394,  'p' => null, 'rim' => 38, 'rd' => 'D', 'tt' => 'TT', 'pr' => 'PR8',  'li' => '128A6', 'spec' => 'BIAS'],
            ['raw' => '12.4-28',    'w' => 315,  'p' => null, 'rim' => 28, 'rd' => 'D', 'tt' => 'TT', 'pr' => 'PR6',  'li' => '118A6', 'spec' => 'BIAS'],
            ['raw' => '900/60R32',  'w' => 900,  'p' => 60, 'rim' => 32, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR16', 'li' => '176A8', 'spec' => 'STEEL BELTED'],
            ['raw' => '650/75R32',  'w' => 650,  'p' => 75, 'rim' => 32, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR16', 'li' => '172A8', 'spec' => 'RADIAL'],
            ['raw' => '800/65R32',  'w' => 800,  'p' => 65, 'rim' => 32, 'rd' => 'R', 'tt' => 'TL', 'pr' => 'PR16', 'li' => '178A8', 'spec' => 'STEEL BELTED'],
        ];

        $models = ['AGRIMAX RT', 'POWER CL', 'MACHXBIB', 'TM800', 'AGRO INDUSTRIAL', 'Ф-2А', 'CEREXBIB', 'AGRIMAX FORCE'];
        $brandList = $brands->values();

        $created = 0;
        foreach ($sizes as $i => $s) {
            // Кожен типорозмір дає 2 товари від різних брендів → 28 позицій.
            foreach ([0, 1] as $k) {
                $idx = $i * 2 + $k;
                $brand = $brandList[$idx % $brandList->count()];
                $model = $models[$idx % count($models)];
                $sku = sprintf('VK-%05d', 1001 + $idx);

                // Тип товару: переважно шини, кілька камер та дисків.
                $typeCode = match (true) {
                    $idx % 7 === 6 => 'tube',
                    $idx % 9 === 8 => 'disk',
                    default => 'tire',
                };
                $typeName = ['tire' => 'Шина', 'tube' => 'Камера', 'disk' => 'Диск'][$typeCode];

                // Варіюємо комерційні поля, щоб у таблиці було видно всі стани.
                $priceModes = ['fixed', 'fixed', 'fixed', 'from', 'inquiry'];
                $priceMode = $priceModes[$idx % count($priceModes)];
                $basePrice = 4200 + ($idx * 730) % 21000;

                $discountValue = null;
                $discountType = null;
                if ($idx % 4 === 0) {                 // частина — зі знижкою
                    $discountType = 'percent';
                    $discountValue = [5, 10, 15, 20][intdiv($idx, 4) % 4];
                } elseif ($idx % 5 === 0) {
                    $discountType = 'amount';
                    $discountValue = 500;
                }

                $stockStatuses = ['in_stock', 'in_stock', 'on_order', 'inquiry'];
                $stock = $stockStatuses[$idx % count($stockStatuses)];

                $name = trim("{$typeName} {$brand->name} {$s['raw']} {$model}");

                $product = Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'product_type_id' => $types[$typeCode],
                        'name' => $name,
                        'brand_id' => $brand->id,
                        'model' => $model,
                        'size_raw' => $s['raw'],
                        'size_width' => $s['w'],
                        'size_profile' => $s['p'],
                        'rim_diameter' => $s['rim'],
                        'rd_type' => $s['rd'],
                        'tube_type' => $s['tt'],
                        'ply_rating' => $s['pr'],
                        'load_speed_index' => $s['li'],
                        'specification' => $s['spec'],
                        'stock_status' => $stock,
                        'price_mode' => $priceMode,
                        'price' => $priceMode === 'inquiry' ? null : $basePrice,
                        'currency' => 'UAH',
                        'exchange_rate' => null,
                        'discount_value' => $priceMode === 'inquiry' ? null : $discountValue,
                        'discount_type' => $priceMode === 'inquiry' ? null : $discountType,
                        'is_promo' => $idx % 3 === 0,
                        'free_shipping' => $idx % 4 === 1,
                        'merchant_enabled' => $idx % 2 === 0,
                        'seo_title' => "Купити {$name} — Велика Шина",
                        'seo_description' => "{$name}. Доставка по Україні, гарантія, найкраща ціна на сільгоспшини.",
                        'seo_h1' => $name,
                        'is_active' => $idx % 11 !== 10,   // один-два неактивних для прикладу
                    ]
                );

                // Категорії: за типом товару.
                $catKey = match ($typeCode) {
                    'tube' => 'cam',
                    'disk' => 'disk',
                    default => $s['rim'] >= 34 ? 'rear' : 'front',
                };
                $product->categories()->sync(array_filter([
                    $cats['root_tire'] ?? null,
                    $cats[$catKey] ?? null,
                ]));

                // Фото — лише якщо ще немає (щоб повторний seed не плодив дублі).
                if (! $product->getFirstMedia('main')) {
                    $photo = $this->makePhoto($brand->name, $s['raw'], $this->brandColor($idx));
                    $product->addMedia($photo)->toMediaCollection('main');
                }

                $created++;
            }
        }

        $this->command?->info("Демо-каталог: брендів {$brands->count()}, товарів {$created}.");
    }

    /** @return \Illuminate\Support\Collection<string, Brand> ключ = код бренду */
    private function seedBrands(): \Illuminate\Support\Collection
    {
        $data = [
            ['name' => 'BKT',         'country' => 'Індія'],
            ['name' => 'Mitas',       'country' => 'Чехія'],
            ['name' => 'Michelin',    'country' => 'Франція'],
            ['name' => 'Continental', 'country' => 'Німеччина'],
            ['name' => 'Trelleborg',  'country' => 'Швеція'],
            ['name' => 'Rosava',      'country' => 'Україна'],
            ['name' => 'Belshina',    'country' => 'Білорусь'],
        ];

        $brands = collect();
        foreach ($data as $i => $row) {
            $brand = Brand::updateOrCreate(
                ['name' => $row['name']],
                [
                    'slug' => Str::slug(Translit::uk($row['name'])),
                    'country' => $row['country'],
                    'is_active' => true,
                ]
            );

            // Логотип — генеруємо, якщо ще немає файлу.
            if (! $brand->logo || ! Storage::disk('public')->exists($brand->logo)) {
                $path = "brands/demo-{$brand->id}.jpg";
                Storage::disk('public')->put($path, $this->logoBytes($row['name'], $this->brandColor($i)));
                $brand->update(['logo' => $path]);
            }

            $brands->put($row['name'], $brand);
        }

        return $brands;
    }

    /** @return array<string,int> мапа ключ → category_id */
    private function seedCategories(): array
    {
        $rootTire = Category::updateOrCreate(
            ['name' => 'Шини для тракторів'],
            ['parent_id' => null, 'level' => 1, 'sort_order' => 1, 'is_active' => true]
        );
        $front = Category::updateOrCreate(
            ['name' => 'Передні (керовані)'],
            ['parent_id' => $rootTire->id, 'level' => 2, 'sort_order' => 1, 'is_active' => true]
        );
        $rear = Category::updateOrCreate(
            ['name' => 'Ведучі (задні)'],
            ['parent_id' => $rootTire->id, 'level' => 2, 'sort_order' => 2, 'is_active' => true]
        );
        $disk = Category::updateOrCreate(
            ['name' => 'Диски колісні'],
            ['parent_id' => null, 'level' => 1, 'sort_order' => 2, 'is_active' => true]
        );
        $cam = Category::updateOrCreate(
            ['name' => 'Камери'],
            ['parent_id' => null, 'level' => 1, 'sort_order' => 3, 'is_active' => true]
        );

        return [
            'root_tire' => $rootTire->id,
            'front' => $front->id,
            'rear' => $rear->id,
            'disk' => $disk->id,
            'cam' => $cam->id,
        ];
    }

    /** Колір-плейсхолдер за індексом (стабільний, без рандому). */
    private function brandColor(int $i): array
    {
        $palette = [
            [33, 78, 122], [183, 28, 28], [27, 94, 32], [74, 20, 140],
            [191, 54, 12], [38, 50, 56], [0, 96, 100],
        ];

        return $palette[$i % count($palette)];
    }

    /** Згенерувати фото товару (jpg) у тимчасовому файлі, повернути шлях. */
    private function makePhoto(string $brand, string $size, array $rgb): string
    {
        $w = $h = 600;
        $img = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($img, 240, 242, 245);
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);

        // Силует шини: темне кільце на світлому тлі.
        $tyre = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledellipse($img, $w / 2, $h / 2, 460, 460, $tyre);
        $hub = imagecolorallocate($img, 224, 226, 230);
        imagefilledellipse($img, $w / 2, $h / 2, 230, 230, $hub);

        $white = imagecolorallocate($img, 255, 255, 255);
        $dark = imagecolorallocate($img, 40, 44, 52);
        $this->centerText($img, 5, $brand, $h / 2 - 28, $white, $w);
        $this->centerText($img, 5, $size, $h / 2 - 6, $white, $w);
        $this->centerText($img, 3, 'VELYKA SHYNA', $h - 40, $dark, $w);

        $path = storage_path('app/seed-photo-' . uniqid() . '.jpg');
        imagejpeg($img, $path, 88);
        imagedestroy($img);

        return $path;
    }

    /** Байти лого бренду (jpg) для збереження на диск public. */
    private function logoBytes(string $name, array $rgb): string
    {
        $w = 240;
        $h = 120;
        $img = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);
        $white = imagecolorallocate($img, 255, 255, 255);
        $this->centerText($img, 5, $name, $h / 2 - 8, $white, $w);

        ob_start();
        imagejpeg($img, null, 90);
        $bytes = ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }

    /** Горизонтально центрований текст вбудованим шрифтом GD. */
    private function centerText($img, int $font, string $text, int $y, int $color, int $width): void
    {
        $tw = imagefontwidth($font) * strlen($text);
        imagestring($img, $font, (int) (($width - $tw) / 2), (int) $y, $text, $color);
    }
}
