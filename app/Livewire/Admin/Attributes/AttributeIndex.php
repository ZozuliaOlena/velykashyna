<?php

namespace App\Livewire\Admin\Attributes;

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductType;
use App\Livewire\Concerns\GuardsDeletion;
use App\Livewire\Concerns\WithAdminToast;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AttributeIndex extends Component
{
    use WithPagination;
    use WithAdminToast;
    use GuardsDeletion;

    public string $search = '';
    public string $filterType = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $product_type_id = null;
    public string $code = '';
    public string $name = '';
    public string $data_type = 'text';
    public ?string $unit = null;
    public bool $is_filterable = false;
    public int $sort_order = 0;

    public string $newOption = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filterType'])) {
            $this->resetPage();
        }
    }

    protected function rules(): array
    {
        return [
            'product_type_id' => ['nullable', 'exists:product_types,id'],
            'code'            => [
                'required', 'string', 'max:255',
                Rule::unique('attributes', 'code')
                    ->where(fn ($q) => $q->where('product_type_id', $this->product_type_id ?: null))
                    ->ignore($this->editingId),
            ],
            'name'        => ['required', 'string', 'max:255'],
            'data_type'   => ['required', 'in:text,number,select,boolean'],
            'unit'        => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['integer', 'min:0'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('product_type_id', 'code', 'name', 'data_type', 'unit', 'is_filterable', 'sort_order', 'editingId', 'newOption');
        $this->data_type = 'text';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $attr = Attribute::findOrFail($id);
        $this->editingId       = $id;
        $this->product_type_id = $attr->product_type_id;
        $this->code            = $attr->code;
        $this->name            = $attr->name;
        $this->data_type       = $attr->data_type;
        $this->unit            = $attr->unit;
        $this->is_filterable   = $attr->is_filterable;
        $this->sort_order      = $attr->sort_order;
        $this->newOption       = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['is_filterable'] = $this->is_filterable;

        $attr = Attribute::updateOrCreate(['id' => $this->editingId], $data);
        $this->editingId = $attr->id;

        if ($this->data_type !== 'select') {
            $this->showModal = false;
        }

        session()->flash('success', 'Збережено');
    }

    public function addOption(): void
    {
        $this->validate(['newOption' => ['required', 'string', 'max:255']]);

        AttributeOption::create([
            'attribute_id' => $this->editingId,
            'value'        => $this->newOption,
            'sort_order'   => 0,
        ]);

        $this->newOption = '';
    }

    public function deleteOption(int $optionId): void
    {
        AttributeOption::where('id', $optionId)
            ->where('attribute_id', $this->editingId)
            ->delete();
    }

    public function delete(int $id): void
    {
        $attr = Attribute::findOrFail($id);

        if ($this->blockIfUsed($attr->name, [
            'товари' => Product::whereHas('attributeValues', fn ($q) => $q->where('attribute_id', $id)),
        ])) {
            return;
        }

        $attr->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $attributes = Attribute::query()
            ->with('productType')
            ->withCount('options')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn ($q) => $q->where('product_type_id', $this->filterType))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(25);

        $options = $this->editingId
            ? AttributeOption::where('attribute_id', $this->editingId)->orderBy('sort_order')->orderBy('value')->get()
            : collect();

        return view('admin.attributes.attribute-index', [
            'attrs'        => $attributes,
            'productTypes' => ProductType::orderBy('name')->get(),
            'options'      => $options,
        ])->layout('admin.layouts.admin');
    }
}
