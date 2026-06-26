<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\MachineryType;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /** Резервні SVG-іконки техніки за назвою. */
    private const MACHINERY_ICONS = [
        'трактор' => 'tractor.svg',
        'комбайн' => 'combine.svg',
        'обприскувач' => 'sprayer.svg',
        'навантажувач' => 'loaders.svg',
        'вантаж' => 'truck.svg',
        'причіп' => 'wheel.svg',
    ];

    public function index(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'catalogImage', 'machineryCompatibility.machineryType']);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request->query('sort'));

        $products = $query->paginate(12)->withQueryString()
            ->through(fn (Product $p) => $p->toCard());

        return view('web.catalog', [
            'products' => $products,
            'total' => $products->total(),
            'tabs' => $this->tabs($request),
            'brands' => Brand::where('is_active', true)->orderBy('name')->pluck('name')->all(),
            'sizes' => $this->sizeList(),
            'diameters' => $this->diameterList(),
            'machineryNames' => MachineryType::orderBy('name')->pluck('name')->all(),
            'selected' => $this->selected($request),
        ]);
    }

    /** Кількість товарів за поточним (ще не застосованим) вибором фільтрів. */
    public function count(Request $request)
    {
        $query = Product::query()->where('is_active', true);
        $this->applyFilters($query, $request);

        return response()->json(['count' => $query->count()]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($w) use ($search) {
                $w->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('size_raw', 'like', "%{$search}%");
            });
        }

        if ($machinery = array_filter((array) $request->query('machinery', []))) {
            // Резолвимо назви → id один раз і фільтруємо одним рівнем EXISTS (швидше).
            $typeIds = MachineryType::whereIn('name', $machinery)->pluck('id');
            $query->whereHas('machineryCompatibility', fn ($x) => $x->whereIn('machinery_type_id', $typeIds));
        }

        if ($category = $request->query('category')) {
            // приймаємо і slug (з фільтра головної), і назву (з чипів у шапці)
            $query->whereHas('categories', fn ($x) => $x->where('slug', $category)->orWhere('name', $category));
        }

        if ($brands = array_filter((array) $request->query('brand', []))) {
            // Фільтр по brand_id замість EXISTS-підзапиту по назві.
            $brandIds = Brand::whereIn('name', $brands)->pluck('id');
            $query->whereIn('brand_id', $brandIds);
        }

        if ($sizes = array_filter((array) $request->query('size', []))) {
            $query->whereIn('size_raw', $sizes);
        }

        if ($constr = array_filter((array) $request->query('constr', []))) {
            $query->whereIn('tube_type', $constr);
        }

        if ($diameter = $request->query('diameter')) {
            $num = (float) preg_replace('/[^0-9.]/', '', $diameter);
            if ($num > 0) {
                $query->where('rim_diameter', $num);
            }
        }

        if ($request->query('in_stock')) {
            $query->where('stock_status', 'in_stock');
        }
    }

    private function applySort($query, ?string $sort): void
    {
        match ($sort) {
            'cheap' => $query->orderByRaw('price IS NULL, price ASC'),
            'expensive' => $query->orderByRaw('price IS NULL, price DESC'),
            'new' => $query->latest(),
            default => $query->orderByDesc('is_promo')->orderByDesc('id'), // популярні
        };
    }

    /** Вкладки за технікою (перша — «Всі шини»). */
    private function tabs(Request $request): array
    {
        $selected = array_filter((array) $request->query('machinery', []));

        $tabs = [[
            'label' => 'Всі шини',
            'icon' => '/images/svg/tehnics/wheel.svg',
            'active' => empty($selected),
            'url' => $request->fullUrlWithQuery(['machinery' => null, 'page' => null]),
        ]];

        foreach (MachineryType::orderBy('name')->get() as $m) {
            $tabs[] = [
                'label' => $m->name,
                'icon' => $m->iconUrl() ?? '/images/svg/tehnics/' . $this->iconFor($m->name),
                'active' => in_array($m->name, $selected, true),
                'url' => $request->fullUrlWithQuery(['machinery' => $m->name, 'page' => null]),
            ];
        }

        return $tabs;
    }

    private function selected(Request $request): array
    {
        return [
            'machinery' => array_filter((array) $request->query('machinery', [])),
            'category' => (string) $request->query('category', ''),
            'brand' => array_filter((array) $request->query('brand', [])),
            'size' => array_filter((array) $request->query('size', [])),
            'constr' => array_filter((array) $request->query('constr', [])),
            'diameter' => (string) $request->query('diameter', ''),
            'in_stock' => (bool) $request->query('in_stock'),
            'q' => (string) $request->query('q', ''),
            'sort' => (string) $request->query('sort', 'popular'),
        ];
    }

    private function sizeList(): array
    {
        return Product::where('is_active', true)
            ->whereNotNull('size_raw')
            ->distinct()->orderBy('size_raw')
            ->pluck('size_raw')->all();
    }

    private function diameterList(): array
    {
        return Product::where('is_active', true)
            ->whereNotNull('rim_diameter')
            ->distinct()->orderBy('rim_diameter')
            ->pluck('rim_diameter')
            ->map(fn ($d) => 'R' . (int) $d)
            ->unique()->values()->all();
    }

    private function iconFor(string $name): string
    {
        $name = mb_strtolower($name);
        foreach (self::MACHINERY_ICONS as $needle => $icon) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }

        return 'wheel.svg';
    }
}
