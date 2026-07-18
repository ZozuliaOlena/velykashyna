<?php

namespace App\Livewire\Admin\Products;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MachineryBrand;
use App\Models\MachineryModel;
use App\Models\MachineryPosition;
use App\Models\MachineryType;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductMachineryCompatibility;
use App\Models\ProductType;
use App\Livewire\Concerns\WithAdminToast;
use App\Support\SizeSlug;
use App\Support\Translit;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;
    use WithAdminToast;

    public ?int $productId = null;

    public string $sku = '';
    public ?int $product_type_id = null;
    public string $name = '';
    public ?int $brand_id = null;
    public ?string $model = null;

    public ?string $size_raw = null;
    public ?string $size_width = null;
    public ?string $size_profile = null;
    public ?string $rim_diameter = null;
    public ?string $rd_type = null;
    public ?string $tube_type = null;
    public ?string $ply_rating = null;
    public ?string $load_speed_index = null;
    public ?string $specification = null;
    public ?string $descrIntro = null;
    public ?string $descrAdvantages = null;
    public ?string $descrVsCompetitors = null;
    public ?string $descrFeatures = null;
    public ?string $descrWhyBuy = null;

    public ?string $expert_note = null;

    public string $stock_status = 'inquiry';
    public string $price_mode = 'inquiry';
    public ?string $price = null;
    public string $currency = 'UAH';
    public ?string $exchange_rate = null;
    public ?string $discount_value = null;
    public ?string $discount_type = null;

    public bool $merchant_enabled = false;
    public ?string $promo_badge = null;
    public ?string $shipping_badge = null;
    public string $condition = 'new';

    public ?string $seo_title = null;
    public ?string $seo_description = null;
    public ?string $seo_h1 = null;
    public ?string $slug = null;

    public bool $is_active = true;

    public array $categoryIds = [];

    /** Сумісність з технікою: рядки [тип, виробник, модель, позиція]. */
    public array $compat = [];

    public array $relatedIds = [];

    /** Пошук у підборі супутніх товарів (за назвою/артикулом). */
    public string $relatedSearch = '';

    public array $attrValues = [];

    public $mainPhoto = null;
    public array $galleryPhotos = [];

    public const GALLERY_MAX = 8;

    public function mount(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $product = Product::with(['categories', 'relatedProducts', 'attributeValues.attribute'])->findOrFail($id);

        $this->productId        = $product->id;
        $this->sku              = $product->sku;
        $this->product_type_id  = $product->product_type_id;
        $this->name             = $product->name;
        $this->brand_id         = $product->brand_id;
        $this->model            = $product->model;
        $this->size_raw         = $product->size_raw;
        $this->size_width       = $product->size_width;
        $this->size_profile     = $product->size_profile;
        $this->rim_diameter     = $product->rim_diameter;
        $this->rd_type          = $product->rd_type;
        $this->tube_type        = $product->tube_type;
        $this->ply_rating       = $product->ply_rating;
        $this->load_speed_index = $product->load_speed_index;
        $this->specification    = $product->specification;
        $this->expert_note      = $product->expert_note;

        $blocks = $product->description_blocks ?? [];
        $this->descrIntro         = $blocks['intro'] ?? (empty($blocks) ? trim(strip_tags((string) $product->description)) : null) ?: null;
        $this->descrAdvantages    = isset($blocks['advantages']) ? implode("\n", $blocks['advantages']) : null;
        $this->descrVsCompetitors = isset($blocks['vs_competitors']) ? implode("\n", $blocks['vs_competitors']) : null;
        $this->descrFeatures      = $blocks['features'] ?? null;
        $this->descrWhyBuy        = $blocks['why_buy'] ?? null;
        $this->stock_status     = $product->stock_status;
        $this->price_mode       = $product->price_mode;
        $this->price            = $product->price;
        $this->currency         = $product->currency;
        $this->exchange_rate    = $product->exchange_rate;
        $this->discount_value   = $product->discount_value;
        $this->discount_type    = $product->discount_type;
        $this->merchant_enabled = $product->merchant_enabled;
        $this->promo_badge      = $product->promo_badge;
        $this->shipping_badge   = $product->shipping_badge;
        $this->condition        = $product->condition ?: 'new';
        $this->seo_title        = $product->seo_title;
        $this->seo_description  = $product->seo_description;
        $this->seo_h1           = $product->seo_h1;
        $this->slug             = $product->slug;
        $this->is_active        = $product->is_active;
        $this->categoryIds      = $product->categories->pluck('id')->toArray();
        $this->relatedIds       = $product->relatedProducts->pluck('id')->toArray();

        $this->compat = $product->machineryCompatibility->map(fn ($c) => [
            'machinery_type_id'  => $c->machinery_type_id,
            'machinery_brand_id' => $c->machinery_brand_id,
            'machinery_model_id' => $c->machinery_model_id,
            'position_id'        => $c->position_id,
        ])->values()->toArray();

        foreach ($product->attributeValues as $v) {
            $this->attrValues[$v->attribute_id] = match ($v->attribute?->data_type) {
                'select'  => $v->option_id,
                'number'  => $v->value_number !== null ? (string) $v->value_number : null,
                'boolean' => $v->value_text === '1',
                default   => $v->value_text,
            };
        }
    }

    public function updatedCurrency(): void
    {
        if ($this->currency === 'UAH') {
            $this->exchange_rate = null;
        }
    }

    public function updatedDiscountType($value): void
    {
        // Прибрали тип знижки → одразу очищаємо і числове поле,
        // щоб не лишалася «висяча» цифра без типу.
        if ($value === '' || $value === null) {
            $this->discount_value = null;
        }
    }

    public function addCompat(): void
    {
        $this->compat[] = [
            'machinery_type_id'  => null,
            'machinery_brand_id' => null,
            'machinery_model_id' => null,
            'position_id'        => null,
        ];
    }

    public function removeCompat(int $i): void
    {
        unset($this->compat[$i]);
        $this->compat = array_values($this->compat);
    }

    /** Каскад у рядку сумісності: зміна типу скидає виробника й модель,
     *  зміна виробника - модель (щоб не лишалися неможливі комбінації). */
    public function updatedCompat($value, $key): void
    {
        $i = (int) explode('.', $key)[0];
        if (! isset($this->compat[$i])) {
            return;
        }

        if (str_ends_with($key, '.machinery_type_id')) {
            $this->compat[$i]['machinery_brand_id'] = null;
            $this->compat[$i]['machinery_model_id'] = null;
        } elseif (str_ends_with($key, '.machinery_brand_id')) {
            $this->compat[$i]['machinery_model_id'] = null;
        }
    }

    // ── Супутні товари (ручний підбір) ─────────────────────────────────
    public function addRelated(int $id): void
    {
        if ($id !== $this->productId && ! in_array($id, $this->relatedIds, true)) {
            $this->relatedIds[] = $id;
        }
        $this->relatedSearch = '';
    }

    public function removeRelated(int $id): void
    {
        $this->relatedIds = array_values(array_filter(
            $this->relatedIds,
            fn ($x) => (int) $x !== $id,
        ));
    }

    public function generateSeo(): void
    {
        $draft = new Product([
            'name'             => $this->name,
            'model'            => $this->model,
            'size_raw'         => $this->size_raw,
            'load_speed_index' => $this->load_speed_index,
            'ply_rating'       => $this->ply_rating,
            'specification'    => $this->specification,
            'tube_type'        => $this->tube_type,
        ]);
        $draft->setRelation('brand', $this->brand_id ? Brand::find($this->brand_id) : null);
        $draft->setRelation('productType', $this->product_type_id ? ProductType::find($this->product_type_id) : null);

        // Сумісність беремо з ПОТОЧНОГО стану форми (щоб контекст «для обприскувачів»
        // працював одразу, ще до збереження товару).
        $compatModels = collect($this->compat)
            ->filter(fn ($r) => ! empty($r['machinery_type_id']))
            ->map(function ($r) {
                $c = new ProductMachineryCompatibility(['machinery_type_id' => $r['machinery_type_id']]);
                $c->setRelation('machineryType', MachineryType::find($r['machinery_type_id']));
                return $c;
            })->values();
        $draft->setRelation('machineryCompatibility', $compatModels);

        $seo = $draft->defaultSeo();

        if (blank($this->seo_title))       { $this->seo_title = $seo['title']; }
        if (blank($this->seo_description)) { $this->seo_description = $seo['description']; }
        if (blank($this->seo_h1))          { $this->seo_h1 = $seo['h1']; }

        session()->flash('success', 'SEO-поля згенеровано (порожні заповнено)');
    }

    private function attributesForType(): Collection
    {
        if (! $this->product_type_id) {
            return collect();
        }

        return Attribute::query()
            ->where(fn ($q) => $q->whereNull('product_type_id')
                ->orWhere('product_type_id', $this->product_type_id))
            ->with('options')
            ->orderBy('sort_order')->orderBy('name')
            ->get();
    }

    protected function rules(): array
    {
        return [
            'sku' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
            'product_type_id'  => ['required', 'exists:product_types,id'],
            'name'             => ['required', 'string', 'max:255'],
            'brand_id'         => ['nullable', 'exists:brands,id'],
            'model'            => ['nullable', 'string', 'max:255'],

            'size_raw'         => ['nullable', 'string', 'max:255'],
            'size_width'       => ['nullable', 'numeric'],
            'size_profile'     => ['nullable', 'numeric'],
            'rim_diameter'     => ['nullable', 'numeric'],
            'rd_type'          => ['nullable', 'in:R,D'],
            'tube_type'        => ['nullable', 'in:TT,TL'],
            'ply_rating'       => ['nullable', 'string', 'max:10'],
            'load_speed_index' => ['nullable', 'string', 'max:30'],
            'specification'    => ['nullable', 'string', 'max:255'],
            'descrIntro'          => ['nullable', 'string', 'max:5000'],
            'descrAdvantages'     => ['nullable', 'string', 'max:5000'],
            'descrVsCompetitors'  => ['nullable', 'string', 'max:5000'],
            'descrFeatures'       => ['nullable', 'string', 'max:5000'],
            'descrWhyBuy'         => ['nullable', 'string', 'max:5000'],
            'expert_note'      => ['nullable', 'string', 'max:5000'],

            'stock_status'   => ['required', 'in:in_stock,on_order,inquiry'],
            'price_mode'     => ['required', 'in:fixed,from,inquiry'],
            'price'          => ['nullable', 'required_unless:price_mode,inquiry', 'numeric', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
            'exchange_rate'  => ['nullable', 'numeric', 'min:0'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_type'  => ['nullable', 'in:percent,amount'],
            'promo_badge'    => ['nullable', Rule::in(Product::PROMO_BADGES)],
            'shipping_badge' => ['nullable', Rule::in(Product::SHIPPING_BADGES)],
            'condition'      => ['required', 'in:new,used,refurbished'],

            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_h1'          => ['nullable', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],

            'categoryIds'   => ['array'],
            'categoryIds.*' => ['exists:categories,id'],

            'relatedIds'   => ['array'],
            'relatedIds.*' => ['exists:products,id'],

            'mainPhoto'        => ['nullable', 'image', 'max:5120'],
            'galleryPhotos'    => ['nullable', 'array', 'max:' . self::GALLERY_MAX],
            'galleryPhotos.*'  => ['image', 'max:5120'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->price_mode === 'inquiry') {
            $data['price'] = null;
        }

        // Знижка: порожній тип неприпустимий для enum-колонки (MySQL кидає
        // «Data truncated»). Без валідного типу або додатного значення -
        // повністю обнуляємо знижку (це ж і дозволяє її прибрати без помилки).
        $hasDiscount = in_array($data['discount_type'] ?? null, ['percent', 'amount'], true)
            && ($data['discount_value'] ?? null) !== null
            && (float) $data['discount_value'] > 0;
        if (! $hasDiscount) {
            $data['discount_type'] = null;
            $data['discount_value'] = null;
        }

        $attributes = $this->attributesForType();
        foreach ($attributes as $attr) {
            if ($attr->data_type === 'number') {
                $raw = $this->attrValues[$attr->id] ?? null;
                if ($raw !== null && $raw !== '' && ! is_numeric($raw)) {
                    $this->addError("attrValues.{$attr->id}", 'Має бути числом');
                }
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $product = $this->productId
            ? Product::findOrFail($this->productId)
            : new Product();

        $scalar = collect($data)
            ->except([
                'categoryIds', 'relatedIds', 'mainPhoto', 'galleryPhotos', 'slug',
                'descrIntro', 'descrAdvantages', 'descrVsCompetitors', 'descrFeatures', 'descrWhyBuy',
            ])
            ->toArray();

        $product->fill($scalar);

        [$product->description_blocks, $product->description] = $this->buildDescription();

        $product->merchant_enabled = $this->merchant_enabled;
        $product->promo_badge = $this->promo_badge ?: null;
        $product->shipping_badge = $this->shipping_badge ?: null;
        $product->is_active = $this->is_active;
        // Явно заданий URL лишаємо як є; за автогенерацією з назви - розділяємо
        // типорозмір дефісами (23.5R25 → 23-5r25), інакше Str::slug його «склеїть».
        $slugSource = filled($this->slug)
            ? $this->slug
            : SizeSlug::inSource($this->name, $this->size_raw);
        $product->slug = $this->buildUniqueSlug($slugSource, $this->productId);

        try {
            $product->save();

            $product->categories()->sync($this->categoryIds);
            $product->relatedProducts()->sync($this->relatedIds);

            // Сумісність з технікою: повністю перезаписуємо за станом форми
            // (лишаємо тільки рядки з обраним типом техніки).
            $product->machineryCompatibility()->delete();
            foreach ($this->compat as $row) {
                if (empty($row['machinery_type_id'])) {
                    continue;
                }
                ProductMachineryCompatibility::create([
                    'product_id'         => $product->id,
                    'machinery_type_id'  => $row['machinery_type_id'],
                    'machinery_brand_id' => $row['machinery_brand_id'] ?: null,
                    'machinery_model_id' => $row['machinery_model_id'] ?: null,
                    'position_id'        => $row['position_id'] ?: null,
                ]);
            }

        foreach ($attributes as $attr) {
            $raw = $this->attrValues[$attr->id] ?? null;
            $payload = ['value_text' => null, 'value_number' => null, 'option_id' => null];
            $empty = false;

            switch ($attr->data_type) {
                case 'number':
                    if ($raw === null || $raw === '') {
                        $empty = true;
                    } else {
                        $payload['value_number'] = $raw;
                    }
                    break;
                case 'select':
                    if (empty($raw)) {
                        $empty = true;
                    } else {
                        $payload['option_id'] = $raw;
                    }
                    break;
                case 'boolean':
                    if (! empty($raw)) {
                        $payload['value_text'] = '1';
                    } else {
                        $empty = true;
                    }
                    break;
                default:
                    if ($raw === null || $raw === '') {
                        $empty = true;
                    } else {
                        $payload['value_text'] = $raw;
                    }
            }

            if ($empty) {
                ProductAttributeValue::where('product_id', $product->id)
                    ->where('attribute_id', $attr->id)->delete();
            } else {
                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $attr->id],
                    $payload
                );
            }
        }

        if ($this->mainPhoto) {
            $product->addMedia($this->mainPhoto->getRealPath())
                ->usingFileName($this->uploadName($this->mainPhoto))
                ->toMediaCollection('main');
        }

        foreach ($this->galleryPhotos as $photo) {
            $product->addMedia($photo->getRealPath())
                ->usingFileName($this->uploadName($photo))
                ->toMediaCollection('gallery');
        }

            $this->reset('mainPhoto', 'galleryPhotos');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Не вдалося зберегти товар. Перевірте дані та спробуйте ще раз.');
            return;
        }

        session()->flash('success', 'Товар збережено');

        return $this->redirectRoute('admin.products.index', navigate: true);
    }

    public function updatedGalleryPhotos(): void
    {
        if (! $this->productId || empty($this->galleryPhotos)) {
            return;
        }

        $this->validate(['galleryPhotos.*' => ['image', 'max:5120']]);

        $product = Product::find($this->productId);
        if (! $product) {
            $this->galleryPhotos = [];
            return;
        }

        $current = $product->getMedia('gallery')->pluck('id')->all();
        $room = self::GALLERY_MAX - count($current);

        $newIds = [];
        foreach (array_slice($this->galleryPhotos, 0, max(0, $room)) as $photo) {
            $media = $product->addMedia($photo->getRealPath())
                ->usingFileName($this->uploadName($photo))
                ->toMediaCollection('gallery');
            $newIds[] = $media->id;
        }

        if ($newIds) {
            \Spatie\MediaLibrary\MediaCollections\Models\Media::setNewOrder(array_merge($newIds, $current));
        }

        $this->galleryPhotos = [];
    }

    public function deleteMedia(int $mediaId): void
    {
        if (! $this->productId) {
            return;
        }

        Product::findOrFail($this->productId)->deleteMedia($mediaId);
        session()->flash('success', 'Фото видалено');
    }

    public function reorderGallery(array $ids): void
    {
        if (! $this->productId) {
            return;
        }

        $valid = Product::find($this->productId)?->getMedia('gallery')->pluck('id')->all() ?? [];
        $ordered = array_values(array_filter(
            array_map('intval', $ids),
            fn ($id) => in_array($id, $valid, true),
        ));

        if ($ordered) {
            \Spatie\MediaLibrary\MediaCollections\Models\Media::setNewOrder($ordered);
        }
    }

    private function uploadName($file): string
    {
        return Str::random(24) . '.' . $file->getClientOriginalExtension();
    }

    private function buildDescription(): array
    {
        $toLines = fn ($t) => array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', (string) $t)),
            fn ($l) => $l !== '',
        ));

        $intro      = trim((string) $this->descrIntro);
        $advantages = $toLines($this->descrAdvantages);
        $vs         = $toLines($this->descrVsCompetitors);
        $features   = trim((string) $this->descrFeatures);
        $whyBuy     = trim((string) $this->descrWhyBuy);

        $blocks = array_filter([
            'intro'          => $intro ?: null,
            'advantages'     => $advantages ?: null,
            'vs_competitors' => $vs ?: null,
            'features'       => $features ?: null,
            'why_buy'        => $whyBuy ?: null,
        ]);

        if (empty($blocks)) {
            return [null, null];
        }

        $paras = fn ($t) => collect($toLines($t))->map(fn ($p) => '<p>'.e($p).'</p>')->implode('');
        $list = fn ($items) => '<ul>'.collect($items)->map(fn ($i) => '<li>'.e($i).'</li>')->implode('').'</ul>';

        $html = '';
        if ($intro) {
            $html .= $paras($intro);
        }
        if ($advantages) {
            $html .= '<h2>Ключові переваги</h2>'.$list($advantages);
        }
        if ($vs) {
            $html .= '<h2>Переваги над конкурентами</h2>'.$list($vs);
        }
        if ($features) {
            $html .= '<h2>Особливості експлуатації</h2>'.$paras($features);
        }
        if ($whyBuy) {
            $html .= '<h2>Чому варто придбати</h2>'.$paras($whyBuy);
        }

        return [$blocks, $html];
    }

    private function buildUniqueSlug(string $source, ?int $ignoreId): string
    {
        $base = Str::slug(Translit::uk($source)) ?: 'tovar';
        $slug = $base;
        $i = 1;

        while (
            Product::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    public function render()
    {
        $product = $this->productId
            ? Product::with(['media', 'catalogImage.media'])->find($this->productId)
            : null;

        $allProducts = Product::when($this->productId, fn ($q) => $q->whereKeyNot($this->productId))
            ->orderBy('name')->get(['id', 'sku', 'name'])->keyBy('id');

        // Обрані супутні товари - у порядку додавання.
        $relatedSelected = collect($this->relatedIds)
            ->map(fn ($id) => $allProducts->get((int) $id))
            ->filter()
            ->values();

        // Кандидати для додавання: за пошуком, без уже обраних, максимум 15.
        $term = trim(mb_strtolower($this->relatedSearch));
        $relatedCandidates = $allProducts
            ->reject(fn ($p) => in_array($p->id, $this->relatedIds, true))
            ->when($term !== '', fn ($c) => $c->filter(
                fn ($p) => str_contains(mb_strtolower($p->name.' '.$p->sku), $term)
            ))
            ->take(15)
            ->values();

        return view('admin.products.product-form', [
            'productTypes' => ProductType::orderBy('name')->get(),
            'brands'       => Brand::orderBy('name')->get(),
            'categories'   => Category::treeOrdered(),
            'allProducts'  => $allProducts,
            'relatedSelected'   => $relatedSelected,
            'relatedCandidates' => $relatedCandidates,
            'mainMedia'    => $product?->getFirstMedia('main'),
            'galleryMedia' => $product ? $product->getMedia('gallery') : collect(),
            'galleryMax'   => self::GALLERY_MAX,
            'catalogImageUrl' => $product?->catalogImage?->imageUrl('thumb'),
            'typeAttributes' => $this->attributesForType(),
            'machineryTypes'     => MachineryType::orderBy('name')->get(['id', 'name']),
            'machineryBrands'    => MachineryBrand::orderBy('name')->get(['id', 'name']),
            'machineryModels'    => MachineryModel::orderBy('name')->get(['id', 'name', 'machinery_brand_id', 'machinery_type_id']),
            'machineryPositions' => MachineryPosition::orderBy('name')->get(['id', 'name']),
        ])->layout('admin.layouts.admin');
    }
}
