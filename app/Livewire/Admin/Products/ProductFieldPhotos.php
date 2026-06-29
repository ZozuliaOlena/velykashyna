<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\MachineryModel;
use App\Models\MachineryType;
use App\Models\ProductFieldPhoto;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Керування фото товару «в роботі»: встановлені/інспектовані шини на техніці
 * з підписом. Вбудовується в картку товару (потрібен існуючий productId).
 */
class ProductFieldPhotos extends Component
{
    use WithFileUploads;
    use WithAdminToast;

    public int $productId;

    public $photo = null;
    public ?int $machinery_type_id = null;
    public ?int $machinery_model_id = null;
    public ?string $caption = null;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    protected function rules(): array
    {
        return [
            'photo'              => ['required', 'image', 'max:8192'],
            'machinery_type_id'  => ['nullable', 'exists:machinery_types,id'],
            'machinery_model_id' => ['nullable', 'exists:machinery_models,id'],
            'caption'            => ['nullable', 'string', 'max:255'],
        ];
    }

    public function add(): void
    {
        $this->validate();

        // Бренд і тип беремо з обраної моделі (тип можна перевизначити вручну).
        $model = $this->machinery_model_id ? MachineryModel::find($this->machinery_model_id) : null;

        $fieldPhoto = ProductFieldPhoto::create([
            'product_id'         => $this->productId,
            'machinery_type_id'  => $this->machinery_type_id ?: $model?->machinery_type_id,
            'machinery_brand_id' => $model?->machinery_brand_id,
            'machinery_model_id' => $this->machinery_model_id,
            'caption'            => $this->caption,
        ]);

        $fieldPhoto->addMedia($this->photo->getRealPath())
            ->usingFileName(Str::random(24) . '.' . $this->photo->getClientOriginalExtension())
            ->toMediaCollection('photo');

        $this->reset('photo', 'machinery_type_id', 'machinery_model_id', 'caption');
        session()->flash('success', 'Фото «в роботі» додано');
    }

    public function delete(int $id): void
    {
        ProductFieldPhoto::where('product_id', $this->productId)->where('id', $id)->first()?->delete();
        session()->flash('success', 'Фото видалено');
    }

    public function render()
    {
        $photos = ProductFieldPhoto::with(['machineryType', 'machineryBrand', 'machineryModel', 'media'])
            ->where('product_id', $this->productId)
            ->orderBy('sort_order')->orderByDesc('id')
            ->get();

        $types = MachineryType::orderBy('name')->get(['id', 'name']);
        $models = MachineryModel::with('brand')->orderBy('name')->get();

        return view('admin.products.product-field-photos', [
            'photos' => $photos,
            'typeOptions' => $types->map(fn ($t) => ['value' => $t->id, 'label' => $t->name])->all(),
            'modelOptions' => $models->map(fn ($m) => [
                'value' => $m->id,
                'label' => trim(($m->brand?->name ? $m->brand->name . ' ' : '') . $m->name),
            ])->all(),
        ]);
    }
}
