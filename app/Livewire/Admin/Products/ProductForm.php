<?php

namespace App\Livewire\Admin\Products;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductType;
use App\Livewire\Concerns\WithAdminToast;
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

    // ── основне ──────────────────────────────────────────────
    public string $sku = '';
    public ?int $product_type_id = null;
    public string $name = '';
    public ?int $brand_id = null;
    public ?string $model = null;

    // ── типорозмір ───────────────────────────────────────────
    public ?string $size_raw = null;
    public ?string $size_width = null;
    public ?string $size_profile = null;
    public ?string $rim_diameter = null;
    public ?string $rd_type = null;          // R / D
    public ?string $tube_type = null;        // TT / TL
    public ?string $ply_rating = null;       // PR
    public ?string $load_speed_index = null; // LI/SS
    public ?string $specification = null;

    // ── наявність та ціна ────────────────────────────────────
    public string $stock_status = 'inquiry';
    public string $price_mode = 'inquiry';
    public ?string $price = null;
    public string $currency = 'UAH';
    public ?string $exchange_rate = null;
    public ?string $discount_value = null;
    public ?string $discount_type = null;

    public bool $merchant_enabled = false;

    // ── SEO ──────────────────────────────────────────────────
    public ?string $seo_title = null;
    public ?string $seo_description = null;
    public ?string $seo_h1 = null;
    public ?string $slug = null;

    public bool $is_active = true;

    // ── категорії (мультивибір) ──────────────────────────────
    /** @var array<int> */
    public array $categoryIds = [];

    // ── супутні товари ───────────────────────────────────────
    /** @var array<int> */
    public array $relatedIds = [];

    // ── гнучкі характеристики (EAV), ключ = attribute_id ─────
    /** @var array<int, mixed> */
    public array $attrValues = [];

    // ── фото ─────────────────────────────────────────────────
    public $mainPhoto = null;        // одне основне
    public array $galleryPhotos = []; // кілька додаткових

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
        $this->stock_status     = $product->stock_status;
        $this->price_mode       = $product->price_mode;
        $this->price            = $product->price;
        $this->currency         = $product->currency;
        $this->exchange_rate    = $product->exchange_rate;
        $this->discount_value   = $product->discount_value;
        $this->discount_type    = $product->discount_type;
        $this->merchant_enabled = $product->merchant_enabled;
        $this->seo_title        = $product->seo_title;
        $this->seo_description  = $product->seo_description;
        $this->seo_h1           = $product->seo_h1;
        $this->slug             = $product->slug;
        $this->is_active        = $product->is_active;
        $this->categoryIds      = $product->categories->pluck('id')->toArray();
        $this->relatedIds       = $product->relatedProducts->pluck('id')->toArray();

        foreach ($product->attributeValues as $v) {
            $this->attrValues[$v->attribute_id] = match ($v->attribute?->data_type) {
                'select'  => $v->option_id,
                'number'  => $v->value_number !== null ? (string) $v->value_number : null,
                'boolean' => $v->value_text === '1',
                default   => $v->value_text,
            };
        }
    }

    /** Характеристики, доступні для обраного типу товару (власні + спільні). */
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

            'stock_status'   => ['required', 'in:in_stock,on_order,inquiry'],
            'price_mode'     => ['required', 'in:fixed,from,inquiry'],
            // ціна обов'язкова лише коли режим не "уточнюйте"
            'price'          => ['nullable', 'required_unless:price_mode,inquiry', 'numeric', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
            'exchange_rate'  => ['nullable', 'numeric', 'min:0'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_type'  => ['nullable', 'in:percent,amount'],

            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_h1'          => ['nullable', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],

            'categoryIds'   => ['array'],
            'categoryIds.*' => ['exists:categories,id'],

            'relatedIds'   => ['array'],
            'relatedIds.*' => ['exists:products,id'],

            'mainPhoto'        => ['nullable', 'image', 'max:5120'],
            'galleryPhotos'    => ['nullable', 'array'],
            'galleryPhotos.*'  => ['image', 'max:5120'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        // у режимі "Уточнюйте ціну" ціна не зберігається
        if ($this->price_mode === 'inquiry') {
            $data['price'] = null;
        }

        // валідація числових характеристик до збереження
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
            ->except(['categoryIds', 'relatedIds', 'mainPhoto', 'galleryPhotos', 'slug'])
            ->toArray();

        $product->fill($scalar);
        $product->merchant_enabled = $this->merchant_enabled;
        $product->is_active = $this->is_active;
        // ручний slug; порожній → з назви. Завжди унікалізуємо самі.
        $product->slug = $this->buildUniqueSlug($this->slug ?: $this->name, $this->productId);
        $product->save();

        $product->categories()->sync($this->categoryIds);
        $product->relatedProducts()->sync($this->relatedIds);

        // гнучкі характеристики (EAV)
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
                default: // text
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

        // фото
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

        session()->flash('success', 'Товар збережено');

        // повертаємось до таблиці товарів
        return $this->redirectRoute('admin.products.index', navigate: true);
    }

    public function deleteMedia(int $mediaId): void
    {
        if (! $this->productId) {
            return;
        }

        Product::findOrFail($this->productId)->deleteMedia($mediaId);
        session()->flash('success', 'Фото видалено');
    }

    private function uploadName($file): string
    {
        return Str::random(24) . '.' . $file->getClientOriginalExtension();
    }

    /** Унікальний slug (укр. транслітерація), з суфіксом -2, -3… при колізії. */
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
            ? Product::with('media')->find($this->productId)
            : null;

        return view('admin.products.product-form', [
            'productTypes' => ProductType::orderBy('name')->get(),
            'brands'       => Brand::orderBy('name')->get(),
            'categories'   => Category::orderBy('level')->orderBy('name')->get(),
            'allProducts'  => Product::when($this->productId, fn ($q) => $q->whereKeyNot($this->productId))
                ->orderBy('name')->get(['id', 'sku', 'name']),
            'mainMedia'    => $product?->getFirstMedia('main'),
            'galleryMedia' => $product ? $product->getMedia('gallery') : collect(),
            'attributes'   => $this->attributesForType(),
        ])->layout('admin.layouts.admin');
    }
}
