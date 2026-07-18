<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Lead;
use App\Models\MachineryBrand;
use App\Models\MachineryModel;
use App\Models\MachineryPosition;
use App\Models\MachineryType;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductFieldPhoto;
use App\Models\ProductMachineryCompatibility;
use App\Models\ProductType;
use App\Support\Translit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Повне демо-наповнення БД для тестування: техніка, характеристики (зі
 * значеннями по всіх товарах), сумісність, фото «в роботі», думка експерта,
 * товари всіх типів, статті блогу та приклади заявок.
 *
 *   php artisan db:seed --class=Database\\Seeders\\FullDemoSeeder
 *
 * Ідемпотентно: повторний запуск доповнює/оновлює, не плодить дублі.
 */
class FullDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Базовий каталог (бренди, категорії, ~28 товарів із фото).
        $this->call(DemoCatalogSeeder::class);

        $this->seedExtraProducts();   // товари типів flap/valve/ring/tube/disk
        $this->seedMachinery();
        $this->seedAttributes();
        $this->fillAttributeValues();
        $this->seedCompatibility();
        $this->seedFieldPhotos();
        $this->seedExpertNotes();
        $this->seedPosts();
        $this->seedLeads();

        $this->command?->info('Повне демо: товарів '.Product::count()
            .', характеристик '.Attribute::count()
            .', значень '.ProductAttributeValue::count()
            .', моделей техніки '.MachineryModel::count()
            .', фото в роботі '.ProductFieldPhoto::count()
            .', статей '.Post::count()
            .', заявок '.Lead::count().'.');
    }

    // ── Товари недопредставлених типів ───────────────────────────────────
    private function seedExtraProducts(): void
    {
        $brands = Brand::pluck('id', 'name');
        $types = ProductType::pluck('id', 'code');
        $brandIds = $brands->values();

        $extra = [
            ['tube',  'Камера 800/65R32 TR-218A',     '800/65R32', 'TR-218A'],
            ['tube',  'Камера 600/65R28 TR-15',       '600/65R28', 'TR-15'],
            ['disk',  'Диск W14x34 8 отворів',        'W14x34',    'DW14Lx34'],
            ['disk',  'Диск DW20Bx38',                'DW20Bx38',  'DW20Bx38'],
            ['flap',  'Флап 18.4-34',                 '18.4-34',   'FLAP'],
            ['flap',  'Флап 23.1-26',                 '23.1-26',   'FLAP'],
            ['valve', 'Вентиль TR-218A',              '-',         'TR-218A'],
            ['valve', 'Вентиль JS-2',                 '-',         'JS-2'],
            ['ring',  'Ущільнювальне кільце 32"',     '32"',       'O-RING'],
            ['ring',  'Ущільнювальне кільце 38"',     '38"',       'O-RING'],
        ];

        foreach ($extra as $i => [$typeCode, $name, $size, $model]) {
            $sku = sprintf('VK-%05d', 2001 + $i);
            $brandId = $brandIds[$i % $brandIds->count()];

            $product = Product::updateOrCreate(['sku' => $sku], [
                'product_type_id' => $types[$typeCode] ?? $types['tire'],
                'name' => $name,
                'brand_id' => $brandId,
                'model' => $model,
                'size_raw' => $size === '-' ? null : $size,
                'stock_status' => ['in_stock', 'on_order'][$i % 2],
                'price_mode' => 'fixed',
                'price' => 350 + $i * 120,
                'currency' => 'UAH',
                'merchant_enabled' => true,
                'is_active' => true,
                'seo_title' => "Купити {$name} - Велика Шина",
            ]);

            if (! $product->getFirstMedia('main')) {
                $photo = $this->makePhoto($product->brand?->name ?? 'VK', $size, $this->color($i));
                $product->addMedia($photo)->toMediaCollection('main');
            }
        }
    }

    // ── Техніка ──────────────────────────────────────────────────────────
    private function seedMachinery(): void
    {
        foreach (['Передня', 'Задня', 'Ведуча', 'Напрямна', 'Універсальна'] as $p) {
            MachineryPosition::firstOrCreate(['name' => $p]);
        }

        $types = [];
        foreach (['Трактор', 'Комбайн', 'Обприскувач', 'Навантажувач', 'Причіп'] as $t) {
            $types[$t] = MachineryType::firstOrCreate(['name' => $t])->id;
        }

        // бренд => [тип => [моделі]]
        $data = [
            'John Deere'      => ['Трактор' => ['8400', '7830', '6155R'], 'Комбайн' => ['S780', 'W650']],
            'CASE IH'         => ['Трактор' => ['Magnum 340', 'Puma 185'], 'Комбайн' => ['Axial-Flow 9240']],
            'New Holland'     => ['Трактор' => ['T7.270', 'T8.435'], 'Комбайн' => ['CR9.90']],
            'CLAAS'           => ['Трактор' => ['Axion 850'], 'Комбайн' => ['Lexion 760', 'Tucano 450']],
            'Fendt'           => ['Трактор' => ['936 Vario', '724 Vario']],
            'МТЗ (Беларус)'   => ['Трактор' => ['МТЗ-82', 'МТЗ-1221', 'МТЗ-3522']],
            'Massey Ferguson' => ['Трактор' => ['MF 7720'], 'Обприскувач' => ['MF 9300']],
            'JCB'             => ['Навантажувач' => ['3CX', '531-70']],
        ];

        foreach ($data as $brandName => $byType) {
            $brand = MachineryBrand::firstOrCreate(['name' => $brandName]);
            foreach ($byType as $typeName => $models) {
                foreach ($models as $modelName) {
                    MachineryModel::firstOrCreate([
                        'name' => $modelName,
                        'machinery_brand_id' => $brand->id,
                    ], ['machinery_type_id' => $types[$typeName] ?? null]);
                }
            }
        }

        // Серії та прив'язка моделей до них.
        $seriesMap = [
            'John Deere'      => ['8R' => ['8400'], '7R' => ['7830'], '6R' => ['6155R'], 'S-Series' => ['S780'], 'W-Series' => ['W650']],
            'CASE IH'         => ['Magnum' => ['Magnum 340'], 'Puma' => ['Puma 185'], 'Axial-Flow' => ['Axial-Flow 9240']],
            'New Holland'     => ['T7' => ['T7.270'], 'T8' => ['T8.435'], 'CR' => ['CR9.90']],
            'CLAAS'           => ['Axion' => ['Axion 850'], 'Lexion' => ['Lexion 760'], 'Tucano' => ['Tucano 450']],
            'Fendt'           => ['Vario' => ['936 Vario', '724 Vario']],
            'МТЗ (Беларус)'   => ['Беларус' => ['МТЗ-82', 'МТЗ-1221', 'МТЗ-3522']],
            'Massey Ferguson' => ['MF 7700' => ['MF 7720'], 'MF 9300' => ['MF 9300']],
            'JCB'             => ['Backhoe' => ['3CX'], 'Loadall' => ['531-70']],
        ];

        foreach ($seriesMap as $brandName => $seriesList) {
            $brand = MachineryBrand::where('name', $brandName)->first();
            if (! $brand) {
                continue;
            }
            foreach ($seriesList as $seriesName => $modelNames) {
                $series = \App\Models\MachinerySeries::firstOrCreate([
                    'machinery_brand_id' => $brand->id,
                    'name' => $seriesName,
                ]);
                MachineryModel::where('machinery_brand_id', $brand->id)
                    ->whereIn('name', $modelNames)
                    ->update(['machinery_series_id' => $series->id]);
            }
        }
    }

    // ── Характеристики (EAV) ─────────────────────────────────────────────
    private function seedAttributes(): void
    {
        $tire = ProductType::where('code', 'tire')->value('id');
        $tube = ProductType::where('code', 'tube')->value('id');
        $disk = ProductType::where('code', 'disk')->value('id');

        // [type_id|null, code, name, data_type, unit, filterable, options[]]
        $defs = [
            [null, 'country', 'Країна виробництва', 'text', null, false, []],
            [null, 'warranty', 'Гарантія', 'number', 'міс', false, []],
            // «Призначення» прибрано - дублювало «Сумісність з технікою» (яка потужніша).
            [$tire, 'tread', 'Тип малюнка', 'select', null, true, ['R-1', 'R-1W', 'R-2', 'R-3', 'R-4', 'I-3']],
            [$tire, 'tubeless', 'Безкамерна', 'boolean', null, true, []],
            [$tire, 'tread_depth', 'Глибина протектора', 'number', 'мм', false, []],
            [$tire, 'max_load', 'Макс. навантаження', 'number', 'кг', false, []],
            [$tire, 'max_speed', 'Макс. швидкість', 'number', 'км/год', false, []],
            [$tube, 'valve', 'Тип вентиля', 'select', null, true, ['TR-218A', 'TR-15', 'JS-2']],
            [$disk, 'bolts', 'Кількість отворів', 'number', 'шт', true, []],
            [$disk, 'et', 'Виліт (ET)', 'number', 'мм', false, []],
        ];

        foreach ($defs as $i => [$typeId, $code, $name, $dataType, $unit, $filterable, $options]) {
            $attr = Attribute::updateOrCreate(
                ['code' => $code, 'product_type_id' => $typeId],
                ['name' => $name, 'data_type' => $dataType, 'unit' => $unit,
                 'is_filterable' => $filterable, 'sort_order' => $i],
            );

            foreach ($options as $j => $val) {
                AttributeOption::firstOrCreate(
                    ['attribute_id' => $attr->id, 'value' => $val],
                    ['sort_order' => $j],
                );
            }
        }
    }

    // ── Значення характеристик по всіх товарах ───────────────────────────
    private function fillAttributeValues(): void
    {
        $attrsByType = Attribute::with('options')->get()->groupBy('product_type_id');

        foreach (Product::with('attributeValues')->get() as $idx => $product) {
            // застосовні: спільні (null) + власні для типу товару
            $applicable = collect($attrsByType->get(null, collect()))
                ->merge($attrsByType->get($product->product_type_id, collect()));

            foreach ($applicable as $k => $attr) {
                $payload = ['value_text' => null, 'value_number' => null, 'option_id' => null];
                $seed = $idx + $k;

                switch ($attr->data_type) {
                    case 'select':
                        $opt = $attr->options[$seed % max(1, $attr->options->count())] ?? null;
                        if (! $opt) {
                            continue 2;
                        }
                        $payload['option_id'] = $opt->id;
                        break;
                    case 'number':
                        $payload['value_number'] = match ($attr->code) {
                            'warranty' => [12, 24, 36][$seed % 3],
                            'tread_depth' => 30 + ($seed % 30),
                            'max_load' => 2000 + ($seed % 20) * 250,
                            'max_speed' => [30, 40, 50, 65][$seed % 4],
                            'bolts' => [8, 10, 12][$seed % 3],
                            'et' => [0, 25, 50][$seed % 3],
                            default => 10 + ($seed % 40),
                        };
                        break;
                    case 'boolean':
                        $payload['value_text'] = ($seed % 2 === 0) ? '1' : null;
                        if (! $payload['value_text']) {
                            continue 2;
                        }
                        break;
                    default: // text
                        $payload['value_text'] = $attr->code === 'country'
                            ? ['Індія', 'Чехія', 'Україна', 'Туреччина', 'Польща'][$seed % 5]
                            : 'Стандарт';
                }

                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $attr->id],
                    $payload,
                );
            }
        }
    }

    // ── Сумісність з технікою ────────────────────────────────────────────
    private function seedCompatibility(): void
    {
        $models = MachineryModel::with('brand')->get();
        $positions = MachineryPosition::pluck('id')->all();
        if ($models->isEmpty()) {
            return;
        }

        foreach (Product::where('product_type_id', ProductType::where('code', 'tire')->value('id'))->get() as $idx => $product) {
            // 1–3 моделі на товар
            for ($n = 0; $n < 1 + ($idx % 3); $n++) {
                $model = $models[($idx + $n) % $models->count()];
                ProductMachineryCompatibility::firstOrCreate([
                    'product_id' => $product->id,
                    'machinery_model_id' => $model->id,
                ], [
                    'machinery_type_id' => $model->machinery_type_id,
                    'machinery_brand_id' => $model->machinery_brand_id,
                    'position_id' => $positions[($idx + $n) % count($positions)] ?? null,
                ]);
            }
        }
    }

    // ── Фото «в роботі» ──────────────────────────────────────────────────
    private function seedFieldPhotos(): void
    {
        $models = MachineryModel::with('brand')->get();
        if ($models->isEmpty()) {
            return;
        }

        $captions = [
            '2 роки в роботі, 3000 мотогодин',
            'Сезон оранки, твердий ґрунт',
            'Інспекція - протектор у нормі',
            '1 рік, робота на полі',
            'Робота на болотистому ґрунті',
        ];

        $tireProducts = Product::where('product_type_id', ProductType::where('code', 'tire')->value('id'))
            ->take(8)->get();

        foreach ($tireProducts as $idx => $product) {
            if ($product->fieldPhotos()->exists()) {
                continue; // вже є - не дублюємо
            }
            // 1–2 фото на товар
            for ($n = 0; $n <= ($idx % 2); $n++) {
                $model = $models[($idx + $n) % $models->count()];
                $fp = ProductFieldPhoto::create([
                    'product_id' => $product->id,
                    'machinery_type_id' => $model->machinery_type_id,
                    'machinery_brand_id' => $model->machinery_brand_id,
                    'machinery_model_id' => $model->id,
                    'caption' => $captions[($idx + $n) % count($captions)],
                ]);
                $img = $this->makePhoto(
                    trim(($model->brand?->name ?? '').' '.$model->name),
                    $product->size_raw ?? '',
                    $this->color($idx + $n + 3),
                );
                $fp->addMedia($img)->toMediaCollection('photo');
            }
        }
    }

    // ── Думка експерта ───────────────────────────────────────────────────
    private function seedExpertNotes(): void
    {
        $notes = [
            'Чудово працює на твердому покритті, тримає тиск. На болоті радимо знизити тиск.',
            'Глибокий протектор, добре самоочищається. Рекомендована для важких ґрунтів.',
            'Економічна модель для причепів та легких тракторів. Довгий ресурс.',
            'Підсилена боковина - стійка до проколів. Підходить для лісосмуг.',
            'Радіальна конструкція - менший опір коченню, економія пального.',
        ];

        $descriptions = [
            'Сільгоспшина для важких польових робіт. Збалансований протектор забезпечує тягу та довгий ресурс.',
            'Універсальна модель для тракторів і причепів. Стійка до проколів, економічна в експлуатації.',
            'Радіальна конструкція: менший опір коченню, рівномірне зношування, комфорт на трасі.',
            'Підсилена боковина та глибокий малюнок - для роботи на вологих і важких ґрунтах.',
            'Надійне рішення для щоденної експлуатації. Добре тримає тиск і самоочищається.',
        ];

        foreach (Product::get() as $idx => $product) {
            $changes = [];
            if (! $product->expert_note && $idx % 2 === 0) {
                $changes['expert_note'] = $notes[$idx % count($notes)];
            }
            if (! $product->description) {
                $changes['description'] = $descriptions[$idx % count($descriptions)];
            }
            if ($changes) {
                $product->update($changes);
            }
        }
    }

    // ── Блог ─────────────────────────────────────────────────────────────
    private function seedPosts(): void
    {
        $posts = [
            ['Як обрати шину для трактора', 'Розбираємо типорозміри, індекси та малюнок протектора.'],
            ['Тиск у сільгоспшинах: чому це важливо', 'Правильний тиск подовжує ресурс і економить пальне.'],
            ['Радіальні чи діагональні шини?', 'Порівнюємо конструкції та сфери застосування.'],
            ['Догляд за шинами в міжсезоння', 'Зберігання, перевірка та продовження строку служби.'],
            ['ТОП-5 шин для комбайнів 2026', 'Огляд популярних моделей під різні умови.'],
        ];

        foreach ($posts as $i => [$title, $excerpt]) {
            $post = Post::updateOrCreate(
                ['slug' => Str::slug(Translit::uk($title))],
                [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => "<p>{$excerpt}</p><p>Детальніше про вибір та експлуатацію сільгоспшин - у статті від експертів «Велика Шина».</p>",
                    'is_published' => true,
                    'published_at' => now()->subDays(($i + 1) * 3),
                    'seo_title' => $title.' - Велика Шина',
                ],
            );

            if (! $post->getFirstMedia('image')) {
                $img = $this->makePhoto('БЛОГ', mb_substr($title, 0, 18), $this->color($i + 1));
                $post->addMedia($img)->toMediaCollection('image');
            }
        }
    }

    // ── Заявки (приклади) ────────────────────────────────────────────────
    private function seedLeads(): void
    {
        if (Lead::where('source', 'demo')->exists()) {
            return;
        }

        $products = Product::where('is_active', true)->inRandomOrder()->take(12)->get();
        $samples = [
            ['Олександр Коваль', '+380671112233', 'Telegram', 'Київ', 'Нова Пошта', '№5', 'Накладений платіж', 'new'],
            ['Ірина Мельник', '+380501234567', 'Дзвінок', 'Львів', 'Нова Пошта', '№12', 'Оплата за реквізитами', 'processing'],
            ['Петро Сидоренко', '+380931112200', 'Viber', 'Одеса', 'САТ', 'вул. Польова, 3', 'Накладений платіж', 'confirmed'],
            ['Микола Бондар', '+380681239988', 'Telegram', 'Дніпро', 'Самовивіз зі складу', '-', 'Оплата за реквізитами', 'new'],
            ['Сергій Ткаченко', '+380991115544', 'Дзвінок', 'Харків', 'Нова Пошта', '№3', 'Накладений платіж', 'canceled'],
            ['Андрій Шевченко', '+380632220011', 'Viber', 'Полтава', 'Кур\'єр', 'вул. Садова, 17', 'Накладений платіж', 'processing'],
        ];

        foreach ($samples as $s => [$name, $phone, $contact, $city, $delivery, $addr, $pay, $status]) {
            $lead = Lead::create([
                'customer_name' => $name,
                'phone' => $phone,
                'contact_method' => $contact,
                'city' => $city,
                'delivery_method' => $delivery,
                'delivery_address' => $addr,
                'payment_method' => $pay,
                'customer_comment' => 'Тестова заявка для демо.',
                'status' => $status,
                'source' => 'demo',
            ]);

            foreach ($products->slice($s * 2, 2) as $p) {
                $lead->items()->create([
                    'product_id' => $p->id,
                    'qty' => 1 + ($s % 4),
                    'price_at_request' => $p->effectivePrice(),
                ]);
            }
        }
    }

    // ── Генерація зображень ──────────────────────────────────────────────
    private function color(int $i): array
    {
        $palette = [
            [33, 78, 122], [183, 28, 28], [27, 94, 32], [74, 20, 140],
            [191, 54, 12], [38, 50, 56], [0, 96, 100], [120, 80, 20],
        ];

        return $palette[$i % count($palette)];
    }

    private function makePhoto(string $line1, string $line2, array $rgb): string
    {
        $w = $h = 600;
        $img = imagecreatetruecolor($w, $h);
        imagefilledrectangle($img, 0, 0, $w, $h, imagecolorallocate($img, 240, 242, 245));

        $tyre = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledellipse($img, $w / 2, $h / 2, 460, 460, $tyre);
        imagefilledellipse($img, $w / 2, $h / 2, 230, 230, imagecolorallocate($img, 224, 226, 230));

        $white = imagecolorallocate($img, 255, 255, 255);
        $dark = imagecolorallocate($img, 40, 44, 52);
        $this->centerText($img, 5, $line1, $h / 2 - 28, $white, $w);
        $this->centerText($img, 5, $line2, $h / 2 - 6, $white, $w);
        $this->centerText($img, 3, 'VELYKA SHYNA', $h - 40, $dark, $w);

        $path = storage_path('app/seed-' . uniqid() . '.jpg');
        imagejpeg($img, $path, 88);
        imagedestroy($img);

        return $path;
    }

    private function centerText($img, int $font, string $text, int $y, int $color, int $width): void
    {
        $ascii = preg_replace('/[^\x20-\x7E]/', '', $text) ?: $text;
        $tw = imagefontwidth($font) * strlen($ascii);
        imagestring($img, $font, (int) (($width - $tw) / 2), (int) $y, $ascii, $color);
    }
}
