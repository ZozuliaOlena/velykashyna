<?php

namespace App\Livewire\Admin\Machinery;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\WithAdminToast;
use App\Models\MachineryBrand;
use Livewire\Component;
use Livewire\WithPagination;

class MachineryBrandIndex extends Component
{
    use WithPagination;
    use WithAdminToast;
    use ConfirmsDeletion;

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

        // Видалення виробника КАСКАДНО видаляє його моделі - попереджаємо про це.
        $confirm = $items->mapWithKeys(fn ($b) => [$b->id => $this->confirmText(
            $b->name,
            $b->models_count > 0
                ? "має моделей: {$b->models_count} - їх буде видалено разом із виробником"
                : null,
        )]);

        return view('admin.machinery.simple-index', [
            'items'      => $items,
            'title'      => 'Виробники техніки',
            'addLabel'   => '+ Додати виробника',
            'countKey'   => 'models_count',
            'countLabel' => 'Моделей',
            'confirmMessages' => $confirm,
        ])->layout('admin.layouts.admin');
    }
}
