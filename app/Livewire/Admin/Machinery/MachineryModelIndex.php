<?php

namespace App\Livewire\Admin\Machinery;

use App\Models\MachineryBrand;
use App\Models\MachineryModel;
use App\Models\MachineryType;
use Livewire\Component;
use Livewire\WithPagination;

class MachineryModelIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterBrand = '';
    public string $filterType = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $machinery_brand_id = null;
    public ?int $machinery_type_id = null;
    public string $name = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filterBrand', 'filterType'])) {
            $this->resetPage();
        }
    }

    protected function rules(): array
    {
        return [
            'machinery_brand_id' => ['required', 'exists:machinery_brands,id'],
            'machinery_type_id'  => ['required', 'exists:machinery_types,id'],
            'name'               => ['required', 'string', 'max:255'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('machinery_brand_id', 'machinery_type_id', 'name', 'editingId');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = MachineryModel::findOrFail($id);
        $this->editingId = $id;
        $this->machinery_brand_id = $item->machinery_brand_id;
        $this->machinery_type_id = $item->machinery_type_id;
        $this->name = $item->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        MachineryModel::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->reset('machinery_brand_id', 'machinery_type_id', 'name', 'editingId');
        session()->flash('success', 'Збережено');
    }

    public function delete(int $id): void
    {
        MachineryModel::findOrFail($id)->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $items = MachineryModel::query()
            ->with(['brand', 'type'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterBrand, fn ($q) => $q->where('machinery_brand_id', $this->filterBrand))
            ->when($this->filterType, fn ($q) => $q->where('machinery_type_id', $this->filterType))
            ->orderBy('name')
            ->paginate(25);

        return view('admin.machinery.machinery-model-index', [
            'items'  => $items,
            'brands' => MachineryBrand::orderBy('name')->get(),
            'types'  => MachineryType::orderBy('name')->get(),
        ])->layout('admin.layouts.admin');
    }
}
