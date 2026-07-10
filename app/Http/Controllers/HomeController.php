<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MachineryType;
use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /** Резервні SVG-іконки техніки за назвою (коли тип не має власної). */
    private const MACHINERY_ICONS = [
        'трактор' => 'tractor.svg',
        'комбайн' => 'combine.svg',
        'обприскувач' => 'sprayer.svg',
        'навантажувач' => 'loaders.svg',
        'вантаж' => 'truck.svg',
        'причіп' => 'wheel.svg',
    ];

    public function index()
    {
        return view('web.home', [
            'showFooterCta' => false,
            'transparentHeader' => true,
            'dbProducts' => $this->popularProducts(),
            'dbCategories' => $this->topCategories(),
            'dbMachinery' => $this->machineryTypes(),
            'filters' => $this->filterData(),
            'heroSlides' => \App\Models\HeroSlide::query()
                ->where('is_active', true)->ordered()->get()
                ->map(fn ($s) => $s->toHeroArray())->all(),
        ]);
    }

    /**
     * Фасетний фільтр: за поточним вибором (будь-яка комбінація полів)
     * повертає доступні опції для КОЖНОГО поля. Опції поля рахуються з
     * урахуванням усіх ІНШИХ полів, тож фільтр працює в будь-який бік -
     * можна почати з бренду, з розміру або з типу техніки.
     */
    public function filterOptions(Request $request)
    {
        return response()->json($this->facetOptions([
            'machinery' => $request->query('machinery') ?: null,
            'diameter' => $request->query('diameter') ?: null,
            'brand' => $request->query('brand') ?: null,
            'size' => $request->query('size') ?: null,
        ]));
    }

    /** Початкові дані фільтра: повні списки всіх полів + ендпоінт. */
    private function filterData(): array
    {
        $options = $this->facetOptions(['machinery' => null, 'diameter' => null, 'brand' => null, 'size' => null]);

        if (empty($options['machinery']) && empty($options['brands']) && empty($options['sizes'])) {
            $mk = fn (array $v) => collect($v)->map(fn ($n) => ['value' => $n, 'label' => $n])->all();
            $options = [
                'machinery' => $mk(['Трактори', 'Комбайни', 'Обприскувачі', 'Навантажувачі', 'Вантажівки']),
                'diameters' => $mk(['32', '38', '42']),
                'brands' => $mk(['BKT', 'Michelin', 'Mitas', 'Continental']),
                'sizes' => $mk(['710/70R38', '800/65R32', '520/85R42']),
            ];
        }

        return ['endpoint' => route('home.filters')] + $options;
    }

    /**
     * Опції всіх чотирьох полів для заданого вибору.
     * Для кожного поля застосовуються фільтри ВСІХ полів, окрім нього самого
     * (інакше поле «схлопнулось» би до єдиного обраного значення).
     */
    private function facetOptions(array $sel): array
    {
        return [
            'machinery' => $this->machineryFacet($sel),
            'diameters' => $this->diameterFacet($sel),
            'brands' => $this->brandFacet($sel),
            'sizes' => $this->sizeFacet($sel),
        ];
    }

    /** Застосувати до Product-запиту всі обрані фільтри, крім поля $except. */
    private function applyFilters($q, array $sel, string $except): void
    {
        if ($except !== 'machinery' && ! empty($sel['machinery'])) {
            $q->whereHas('machineryCompatibility.machineryType', fn ($x) => $x->where('name', $sel['machinery']));
        }
        if ($except !== 'diameter' && ! empty($sel['diameter'])) {
            
            $num = (float) preg_replace('/[^0-9.]/', '', (string) $sel['diameter']);
            if ($num > 0) {
                $q->where('rim_diameter', $num);
            }
        }
        if ($except !== 'brand' && ! empty($sel['brand'])) {
            $q->whereHas('brand', fn ($x) => $x->where('name', $sel['brand']));
        }
        if ($except !== 'size' && ! empty($sel['size'])) {
            $q->where('size_raw', $sel['size']);
        }
    }

    private function machineryFacet(array $sel): array
    {
        return MachineryType::query()
            ->whereHas('compatibility.product', function ($q) use ($sel) {
                $q->where('is_active', true);
                $this->applyFilters($q, $sel, 'machinery');
            })
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($n) => ['value' => $n, 'label' => $n])
            ->all();
    }

    private function diameterFacet(array $sel): array
    {
        $q = Product::query()->where('is_active', true)->whereNotNull('rim_diameter');
        $this->applyFilters($q, $sel, 'diameter');

        
        
        return $q->distinct()->orderBy('rim_diameter')->pluck('rim_diameter')
            ->map(fn ($d) => (string) (int) $d)
            ->unique()->values()
            ->map(fn ($label) => ['value' => $label, 'label' => $label])
            ->all();
    }

    private function brandFacet(array $sel): array
    {
        return Brand::query()
            ->where('is_active', true)
            ->whereHas('products', function ($q) use ($sel) {
                $q->where('is_active', true);
                $this->applyFilters($q, $sel, 'brand');
            })
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($n) => ['value' => $n, 'label' => $n])
            ->all();
    }

    private function sizeFacet(array $sel): array
    {
        $q = Product::query()->where('is_active', true)->whereNotNull('size_raw');
        $this->applyFilters($q, $sel, 'size');

        return $q->distinct()->orderBy('size_raw')->pluck('size_raw')
            ->map(fn ($s) => ['value' => $s, 'label' => $s])
            ->all();
    }

    /** Популярні моделі: акційні спершу, далі найновіші. */
    private function popularProducts(): array
    {
        return Product::query()
            ->where('is_active', true)
            ->with(['brand', 'catalogImage', 'productType', 'machineryCompatibility.machineryType'])
            ->orderByRaw('promo_badge is not null desc')
            ->latest()
            ->take(8)
            ->get()
            ->map->toCard()
            ->all();
    }

    /** Кореневі категорії з кількістю товарів та зображенням. */
    private function topCategories(): array
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')->orderBy('name')
            ->take(6)
            ->get()
            ->map(fn (Category $c) => [
                'name' => $c->name,
                'count' => $c->products_count . ' ' . $this->plural($c->products_count, 'позиція', 'позиції', 'позицій'),
                'img' => $this->categoryImage($c->image),
                'url' => route('catalog') . '?category=' . $c->slug,
            ])
            ->all();
    }

    /** Типи техніки для стрічки підбору. */
    private function machineryTypes(): array
    {
        return MachineryType::orderBy('name')
            ->get()
            ->map(fn (MachineryType $m) => [
                'name' => $m->name,
                'icon' => $m->iconUrl() ?? '/images/svg/tehnics/' . $this->machineryIcon($m->name),
                'url' => route('catalog') . '?machinery=' . urlencode($m->name),
            ])
            ->all();
    }

    /** URL зображення категорії (storage-шлях/абсолютний/корене-відносний) або null. */
    private function categoryImage(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (str_starts_with($image, 'http') || str_starts_with($image, '/')) {
            return $image;
        }

        return MediaUrl::rel(Storage::disk('public')->url($image));
    }

    private function machineryIcon(string $name): string
    {
        $name = mb_strtolower($name);
        foreach (self::MACHINERY_ICONS as $needle => $icon) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }

        return 'wheel.svg';
    }

    /** Український відмінок іменника за числом (1 позиція / 2 позиції / 5 позицій). */
    private function plural(int $n, string $one, string $few, string $many): string
    {
        $mod10 = $n % 10;
        $mod100 = $n % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            return $one;
        }
        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
            return $few;
        }

        return $many;
    }
}
