<?php

namespace App\Livewire\Admin\Machinery;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\MachineryBrand;
use Livewire\Component;
use Livewire\WithPagination;

class MachineryBrandIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }

    public function openCreate(): void
    {
        $this->reset('name', 'editingId');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = MachineryBrand::findOrFail($id);
        $this->editingId = $id;
        $this->name = $item->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        MachineryBrand::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->reset('name', 'editingId');
        session()->flash('success', 'Збережено');
    }

    public function delete(int $id): void
    {
        MachineryBrand::findOrFail($id)->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $items = MachineryBrand::query()
            ->withCount('models')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.machinery.simple-index', [
            'items'      => $items,
            'title'      => 'Виробники техніки',
            'addLabel'   => '+ Додати виробника',
            'countKey'   => 'models_count',
            'countLabel' => 'Моделей',
        ])->layout('admin.layouts.admin');
    }
}
