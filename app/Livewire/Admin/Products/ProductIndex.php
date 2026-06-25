<?php

namespace App\Livewire\Admin\Products;

use App\Models\Brand;
use App\Models\CatalogImage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Livewire\Concerns\WithAdminToast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithAdminToast;

    // фільтри (зберігаються в URL — зручно ділитись/повертатись)
    #[Url(as: 'q')]
    public string $search = '';
    #[Url]
    public string $type = '';
    #[Url]
    public string $size = '';
    #[Url]
    public string $brand = '';
    #[Url]
    public string $category = '';
    #[Url]
    public string $stock = '';

    // масовий вибір
    /** @var array<int> */
    public array $selected = [];
    public bool $selectPage = false;

    // поля масових дій
    public string $bulkStock = '';
    public string $bulkPriceMode = '';
    public ?string $bulkPrice = null;
    public ?string $bulkPricePercent = null;
    public $bulkCatalogPhoto = null; // каталожне фото для масового призначення

    protected function queryString(): array
    {
        return [];
    }

    public function updating($name): void
    {
        // будь-яка зміна фільтра/пошуку скидає пагінацію та вибір
        if (in_array($name, ['search', 'type', 'size', 'brand', 'category', 'stock'])) {
            $this->resetPage();
            $this->reset('selected', 'selectPage');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'type', 'size', 'brand', 'category', 'stock');
        $this->resetPage();
    }

    // вибрати/зняти всі рядки на поточній сторінці
    public function updatedSelectPage(bool $value): void
    {
        $this->selected = $value
            ? $this->productsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : [];
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function toggleMerchant(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['merchant_enabled' => ! $product->merchant_enabled]);
    }

    public function togglePromo(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_promo' => ! $product->is_promo]);
    }

    public function toggleFreeShipping(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['free_shipping' => ! $product->free_shipping]);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        session()->flash('success', 'Товар видалено');
    }

    // ── масові дії ───────────────────────────────────────────────
    public function bulkSetActive(bool $active): void
    {
        if (empty($this->selected)) {
            return;
        }
        Product::whereIn('id', $this->selected)->update(['is_active' => $active]);
        $this->afterBulk();
    }

    public function bulkSetMerchant(bool $enabled): void
    {
        if (empty($this->selected)) {
            return;
        }
        Product::whereIn('id', $this->selected)->update(['merchant_enabled' => $enabled]);
        $this->afterBulk();
    }

    public function bulkSetPromo(bool $on): void
    {
        if (empty($this->selected)) {
            return;
        }
        Product::whereIn('id', $this->selected)->update(['is_promo' => $on]);
        $this->afterBulk();
    }

    public function bulkSetFreeShipping(bool $on): void
    {
        if (empty($this->selected)) {
            return;
        }
        Product::whereIn('id', $this->selected)->update(['free_shipping' => $on]);
        $this->afterBulk();
    }

    public function bulkSetStock(): void
    {
        if (empty($this->selected)) {
            return;
        }
        if (! in_array($this->bulkStock, ['in_stock', 'on_order', 'inquiry'], true)) {
            session()->flash('error', 'Оберіть наявність для масової зміни');
            return;
        }

        Product::whereIn('id', $this->selected)->update(['stock_status' => $this->bulkStock]);
        $this->afterBulk();
        $this->reset('bulkStock');
        session()->flash('success', 'Наявність оновлено');
    }

    public function bulkSetPrice(): void
    {
        if (empty($this->selected)) {
            return;
        }
        if (! in_array($this->bulkPriceMode, ['fixed', 'from', 'inquiry'], true)) {
            session()->flash('error', 'Оберіть режим ціни');
            return;
        }

        $data = ['price_mode' => $this->bulkPriceMode];

        if ($this->bulkPriceMode === 'inquiry') {
            // "Уточнюйте" — ціна не зберігається
            $data['price'] = null;
        } else {
            if (! is_numeric($this->bulkPrice) || (float) $this->bulkPrice < 0) {
                session()->flash('error', 'Вкажіть коректну ціну');
                return;
            }
            $data['price'] = $this->bulkPrice;
        }

        Product::whereIn('id', $this->selected)->update($data);
        $this->afterBulk();
        $this->reset('bulkPrice', 'bulkPriceMode');
        session()->flash('success', 'Ціни оновлено');
    }

    /** Масово підняти ціну на відсоток (наприклад, +10%). */
    public function bulkRaisePrice(): void
    {
        if (empty($this->selected)) {
            return;
        }
        if (! is_numeric($this->bulkPricePercent) || (float) $this->bulkPricePercent == 0.0) {
            session()->flash('error', 'Вкажіть відсоток (наприклад, 10)');
            return;
        }

        $percent = (float) $this->bulkPricePercent;
        $factor  = 1 + $percent / 100;
        if ($factor < 0) {
            session()->flash('error', 'Відсоток некоректний — ціна стала б від’ємною');
            return;
        }

        // лише товари з реальною ціною; "Уточнюйте" (inquiry) пропускаємо
        $factorSql = number_format($factor, 6, '.', '');
        $affected  = Product::whereIn('id', $this->selected)
            ->whereNotNull('price')
            ->where('price_mode', '!=', 'inquiry')
            ->update(['price' => DB::raw("ROUND(price * {$factorSql}, 2)")]);

        $this->afterBulk();
        $this->reset('bulkPricePercent');
        session()->flash('success', "Ціни змінено на {$percent}% (оновлено товарів: {$affected})");
    }

    /** Масово призначити одне каталожне (стокове) фото вибраним товарам. */
    public function bulkSetCatalogPhoto(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->validate(
            ['bulkCatalogPhoto' => ['required', 'image', 'max:5120']],
            [],
            ['bulkCatalogPhoto' => 'фото']
        );

        $catalogImage = CatalogImage::create(['label' => 'Каталожне фото']);
        $catalogImage->addMedia($this->bulkCatalogPhoto->getRealPath())
            ->usingFileName(Str::random(24) . '.' . $this->bulkCatalogPhoto->getClientOriginalExtension())
            ->toMediaCollection('image');

        $count = count($this->selected);
        Product::whereIn('id', $this->selected)->update(['catalog_image_id' => $catalogImage->id]);

        $this->afterBulk();
        $this->reset('bulkCatalogPhoto');
        session()->flash('success', "Каталожне фото застосовано до товарів: {$count}");
    }

    public function bulkDelete(): void
    {
        $count = count($this->selected ?? []);
        Product::whereIn('id', $this->selected)->delete();
        $this->afterBulk();
        session()->flash('success', 'Видалено товарів: ' . $count);
    }

    private function afterBulk(): void
    {
        $this->reset('selected', 'selectPage');
    }

    // спільний білдер для рендера та "вибрати всі"
    private function productsQuery()
    {
        return Product::query()
            ->with(['brand', 'productType', 'media', 'catalogImage.media'])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(fn ($sub) => $sub
                    ->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('size_raw', 'like', $term));
            })
            ->when($this->type !== '', fn ($q) => $q->where('product_type_id', $this->type))
            ->when($this->size !== '', fn ($q) => $q->where('size_raw', $this->size))
            ->when($this->brand !== '', fn ($q) => $q->where('brand_id', $this->brand))
            ->when($this->stock !== '', fn ($q) => $q->where('stock_status', $this->stock))
            ->when($this->category !== '', fn ($q) => $q->whereHas(
                'categories',
                fn ($c) => $c->where('categories.id', $this->category)
            ))
            ->orderByDesc('id');
    }

    public function render()
    {
        return view('admin.products.product-index', [
            'products'     => $this->productsQuery()->paginate(25),
            'productTypes' => ProductType::orderBy('name')->get(),
            'brands'       => Brand::orderBy('name')->get(),
            'categories'   => Category::treeOrdered(),
            'sizes'        => Product::whereNotNull('size_raw')->where('size_raw', '!=', '')
                ->distinct()->orderBy('size_raw')->pluck('size_raw'),
        ])->layout('admin.layouts.admin');
    }
}
