<?php

namespace App\Livewire\Admin\Products;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductForm extends Component
{
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

    public bool $is_active = true;

    // ── категорії (мультивибір) ──────────────────────────────
    /** @var array<int> */
    public array $categoryIds = [];

    public function mount(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $product = Product::with('categories')->findOrFail($id);

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
        $this->is_active        = $product->is_active;
        $this->categoryIds      = $product->categories->pluck('id')->toArray();
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

            'categoryIds'   => ['array'],
            'categoryIds.*' => ['exists:categories,id'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        // у режимі "Уточнюйте ціну" ціна не зберігається
        if ($this->price_mode === 'inquiry') {
            $data['price'] = null;
        }

        $product = $this->productId
            ? Product::findOrFail($this->productId)
            : new Product();

        $product->fill(collect($data)->except('categoryIds')->toArray());
        $product->merchant_enabled = $this->merchant_enabled;
        $product->is_active = $this->is_active;
        $product->save();

        $product->categories()->sync($this->categoryIds);

        session()->flash('success', 'Товар збережено');

        return $this->redirectRoute('admin.products.index', navigate: true);
    }

    public function render()
    {
        return view('admin.products.product-form', [
            'productTypes' => ProductType::orderBy('name')->get(),
            'brands'       => Brand::orderBy('name')->get(),
            'categories'   => Category::orderBy('level')->orderBy('name')->get(),
        ])->layout('admin.layouts.admin');
    }
}
