<?php

namespace App\Livewire\Admin\Machinery;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\WithAdminToast;
use App\Models\MachineryBrand;
use App\Models\MachinerySeries;
use Livewire\Component;
use Livewire\WithPagination;

class MachinerySeriesIndex extends Component
{
    use WithPagination;
    use WithAdminToast;
    use ConfirmsDeletion;

    public string $search = '';
    public string $filterBrand = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $machinery_brand_id = null;
    public string $name = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filterBrand'])) {
            $this->resetPage();
        }
    }

    protected function rules(): array
    {
        return [
            'machinery_brand_id' => ['required', 'exists:machinery_brands,id'],
            'name'               => ['required', 'string', 'max:255'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('machinery_brand_id', 'name', 'editingId');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = MachinerySeries::findOrFail($id);
        $this->editingId = $id;
        $this->machinery_brand_id = $item->machinery_brand_id;
        $this->name = $item->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        MachinerySeries::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->reset('machinery_brand_id', 'name', 'editingId');
        session()->flash('success', 'Збережено');
    }

    public function delete(int $id): void
    {
        MachinerySeries::findOrFail($id)->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $items = MachinerySeries::query()
            ->with('brand')
            ->withCount('models')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterBrand, fn ($q) => $q->where('machinery_brand_id', $this->filterBrand))
            ->orderBy('name')
            ->paginate(25);

        $confirm = $items->mapWithKeys(fn ($s) => [$s->id => $this->confirmText(
            $s->name,
            $s->models_count > 0
                ? "має моделей: {$s->models_count} (вони лишаться без серії)"
                : null,
        )]);

        return view('admin.machinery.machinery-series-index', [
            'items'  => $items,
            'brands' => MachineryBrand::orderBy('name')->get(),
            'confirm' => $confirm,
        ])->layout('admin.layouts.admin');
    }
}
