<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MachineryBrand;
use App\Models\MachineryModel;
use App\Models\MachineryType;
use App\Models\Product;
use App\Models\ProductMachineryCompatibility;
use App\Models\ProductType;
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
            ->with(['brand', 'catalogImage', 'productType', 'machineryCompatibility.machineryType']);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request->query('sort'));

        $products = $query->paginate(12)->withQueryString()
            ->through(fn (Product $p) => $p->toCard());

        return view('web.catalog', [
            'products' => $products,
            'total' => $products->total(),
            'tabs' => $this->tabs($request),
            'brands' => $this->brandFacet($request),
            'sizes' => $this->sizeFacet($request),
            'diameters' => $this->diameterFacet($request),
            'machineryNames' => MachineryType::orderBy('name')->pluck('name')->all(),
            'machineryBrands' => $this->machineryBrands($request),
            'machineryModels' => $this->machineryModels($request),
            'productTypes' => ProductType::orderBy('id')->get(['code', 'name']),
            'selected' => $this->selected($request),
            'activeFilters' => $this->activeFilters($request),
            // Власний CTA-блок у каталозі — стандартний банер футера вимикаємо.
            'showFooterCta' => false,
        ]);
    }

    /** Активні фільтри як «чипи» з посиланнями на зняття. */
    private function activeFilters(Request $request): array
    {
        $sel = $this->selected($request);
        $chips = [];

        $rm = fn (array $params) => $request->fullUrlWithQuery($params + ['page' => null]);

        $typeNames = ProductType::pluck('name', 'code');
        $mBrandNames = MachineryBrand::pluck('name', 'id');
        $mModelNames = MachineryModel::pluck('name', 'id');

        // Багатозначні поля: знімаємо одне значення зі списку.
        $multi = [
            'type' => fn ($v) => $typeNames[$v] ?? $v,
            'machinery' => fn ($v) => $v,
            'mbrand' => fn ($v) => 'Техніка: ' . ($mBrandNames[$v] ?? $v),
            'mmodel' => fn ($v) => 'Модель: ' . ($mModelNames[$v] ?? $v),
            'brand' => fn ($v) => $v,
            'size' => fn ($v) => $v,
            'constr' => fn ($v) => $v === 'TL' ? 'Безкамерна (TL)' : ($v === 'TT' ? 'Камерна (TT)' : $v),
        ];

        if ($sel['q'] !== '') {
            $chips[] = ['label' => 'Пошук: ' . $sel['q'], 'url' => $rm(['q' => null])];
        }
        if ($sel['category'] !== '') {
            $catName = Category::where('slug', $sel['category'])->orWhere('name', $sel['category'])->value('name');
            $chips[] = ['label' => $catName ?? $sel['category'], 'url' => $rm(['category' => null])];
        }
        foreach ($multi as $key => $labeller) {
            foreach ($sel[$key] as $value) {
                $rest = array_values(array_diff($sel[$key], [$value]));
                $chips[] = ['label' => $labeller($value), 'url' => $rm([$key => $rest ?: null])];
            }
        }

        if ($sel['diameter'] !== '') {
            $chips[] = ['label' => $sel['diameter'], 'url' => $rm(['diameter' => null])];
        }
        if ($sel['in_stock']) {
            $chips[] = ['label' => 'В наявності', 'url' => $rm(['in_stock' => null])];
        }

        return $chips;
    }

    /** Кількість товарів за поточним (ще не застосованим) вибором фільтрів. */
    public function count(Request $request)
    {
        $query = Product::query()->where('is_active', true);
        $this->applyFilters($query, $request);

        return response()->json(['count' => $query->count()]);
    }

    /**
     * ID категорії разом з усіма нащадками (рекурсивно, одним запитом).
     * Потрібно, щоб фільтр за батьківською категорією включав товари підкатегорій.
     *
     * @return array<int>
     */
    private function categoryWithDescendantIds(int $rootId): array
    {
        $byParent = Category::get(['id', 'parent_id'])->groupBy('parent_id');

        $ids = [];
        $stack = [$rootId];
        while ($stack) {
            $id = array_pop($stack);
            $ids[] = $id;
            foreach ($byParent[$id] ?? [] as $child) {
                $stack[] = $child->id;
            }
        }

        return $ids;
    }

    /**
     * Застосовує фільтри запиту. $except — поля, які пропустити
     * (для фасетних списків: опції поля рахуються без урахування його самого).
     */
    private function applyFilters($query, Request $request, array $except = []): void
    {
        $on = fn (string $field) => ! in_array($field, $except, true);

        if ($on('q') && ($search = trim((string) $request->query('q', '')))) {
            $query->search($search);
        }

        if ($on('type') && ($types = array_filter((array) $request->query('type', [])))) {
            $query->whereHas('productType', fn ($x) => $x->whereIn('code', $types));
        }

        if ($on('machinery') && ($machinery = array_filter((array) $request->query('machinery', [])))) {
            $typeIds = MachineryType::whereIn('name', $machinery)->pluck('id');
            $query->whereHas('machineryCompatibility', fn ($x) => $x->whereIn('machinery_type_id', $typeIds));
        }

        // Каскад «за технікою»: марка → модель (мультивибір). Частковий вибір =
        // ширша видача (обрав лише марку → усі шини на техніку цієї марки).
        if ($on('mbrand') && ($mbrand = array_filter((array) $request->query('mbrand', [])))) {
            $query->whereHas('machineryCompatibility', fn ($x) => $x->whereIn('machinery_brand_id', $mbrand));
        }

        if ($on('mmodel') && ($mmodel = array_filter((array) $request->query('mmodel', [])))) {
            $query->whereHas('machineryCompatibility', fn ($x) => $x->whereIn('machinery_model_id', $mmodel));
        }

        if ($on('category') && ($category = $request->query('category'))) {
            // Приймаємо і slug (з фільтра/чипів), і назву. Товари прив'язані до
            // листових категорій, тож для батьківської («Агрошина») включаємо
            // всі її підкатегорії, інакше нічого б не знаходилось.
            $root = Category::where('slug', $category)->orWhere('name', $category)->first();
            if ($root) {
                $ids = $this->categoryWithDescendantIds($root->id);
                $query->whereHas('categories', fn ($x) => $x->whereIn('categories.id', $ids));
            } else {
                $query->whereRaw('1 = 0'); // невідома категорія → порожній результат
            }
        }

        if ($on('brand') && ($brands = array_filter((array) $request->query('brand', [])))) {
            $brandIds = Brand::whereIn('name', $brands)->pluck('id');
            $query->whereIn('brand_id', $brandIds);
        }

        if ($on('size') && ($sizes = array_filter((array) $request->query('size', [])))) {
            $query->whereIn('size_raw', $sizes);
        }

        if ($on('constr') && ($constr = array_filter((array) $request->query('constr', [])))) {
            $query->whereIn('tube_type', $constr);
        }

        if ($on('diameter') && ($diameter = $request->query('diameter'))) {
            $num = (float) preg_replace('/[^0-9.]/', '', $diameter);
            if ($num > 0) {
                $query->where('rim_diameter', $num);
            }
        }

        if ($on('in_stock') && $request->query('in_stock')) {
            $query->where('stock_status', 'in_stock');
        }
    }

    /** Product-запит із усіма фільтрами, окрім $except. */
    private function facetBase(Request $request, string $except)
    {
        $q = Product::query()->where('is_active', true);
        $this->applyFilters($q, $request, [$except]);

        return $q;
    }

    /** Доступні бренди з урахуванням решти фільтрів (фасет). */
    private function brandFacet(Request $request): array
    {
        $brandIds = $this->facetBase($request, 'brand')
            ->whereNotNull('brand_id')->distinct()->pluck('brand_id');

        return Brand::where('is_active', true)
            ->whereIn('id', $brandIds)
            ->orderBy('name')->pluck('name')->all();
    }

    /** Доступні розміри з урахуванням решти фільтрів (фасет). */
    private function sizeFacet(Request $request): array
    {
        return $this->facetBase($request, 'size')
            ->whereNotNull('size_raw')->distinct()->orderBy('size_raw')
            ->pluck('size_raw')->all();
    }

    /** Доступні діаметри з урахуванням решти фільтрів (фасет). */
    private function diameterFacet(Request $request): array
    {
        return $this->facetBase($request, 'diameter')
            ->whereNotNull('rim_diameter')->distinct()->orderBy('rim_diameter')
            ->pluck('rim_diameter')
            ->map(fn ($d) => 'R' . (int) $d)
            ->unique()->values()->all();
    }

    /**
     * Записи сумісності для товарів, що відповідають поточним фільтрам
     * (без machinery-марки/моделі), звужені типом техніки (вкладки) і — за
     * потреби — обраною маркою. База для каскадних фасетів марки/моделі.
     */
    private function machineryCompat(Request $request, bool $withBrand)
    {
        $base = Product::query()->where('is_active', true);
        $this->applyFilters($base, $request, ['mbrand', 'mmodel']);

        $q = ProductMachineryCompatibility::query()->whereIn('product_id', $base->select('id'));

        if ($mNames = array_filter((array) $request->query('machinery', []))) {
            $q->whereIn('machinery_type_id', MachineryType::whereIn('name', $mNames)->pluck('id'));
        }
        if ($withBrand && ($mb = array_filter((array) $request->query('mbrand', [])))) {
            $q->whereIn('machinery_brand_id', $mb);
        }

        return $q;
    }

    /** Доступні марки техніки (з урахуванням типу техніки та фільтрів). */
    private function machineryBrands(Request $request): array
    {
        $ids = $this->machineryCompat($request, false)
            ->whereNotNull('machinery_brand_id')->distinct()->pluck('machinery_brand_id');

        return MachineryBrand::whereIn('id', $ids)->orderBy('name')->get(['id', 'name'])
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->all();
    }

    /** Доступні моделі техніки (звужені обраною маркою, якщо є). */
    private function machineryModels(Request $request): array
    {
        $ids = $this->machineryCompat($request, true)
            ->whereNotNull('machinery_model_id')->distinct()->pluck('machinery_model_id');

        return MachineryModel::whereIn('id', $ids)->orderBy('name')->get(['id', 'name'])
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->all();
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
            'mbrand' => array_filter((array) $request->query('mbrand', [])),
            'mmodel' => array_filter((array) $request->query('mmodel', [])),
            'type' => array_filter((array) $request->query('type', [])),
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
