<?php

namespace App\Livewire\Admin\Machinery;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\MachineryType;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MachineryTypeIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';

    public $icon = null;
    public ?string $currentIcon = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'file', 'mimes:svg', 'max:1024'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('name', 'editingId', 'icon', 'currentIcon');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = MachineryType::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $item->name;
        $this->currentIcon = $item->icon;
        $this->icon        = null;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $type = $this->editingId ? MachineryType::findOrFail($this->editingId) : new MachineryType();
        $type->name = $this->name;

        if ($this->icon) {
            if ($type->icon) {
                Storage::disk('public')->delete($type->icon);
            }
            $type->icon = \App\Support\ImageOptimizer::toWebp($this->icon, 'machinery-types');
        }

        $type->save();

        $this->showModal = false;
        $this->reset('name', 'editingId', 'icon', 'currentIcon');
        session()->flash('success', 'Збережено');
    }

    public function deleteIcon(int $id): void
    {
        $type = MachineryType::findOrFail($id);
        if ($type->icon) {
            Storage::disk('public')->delete($type->icon);
            $type->update(['icon' => null]);
        }
        $this->currentIcon = null;
        session()->flash('success', 'Іконку видалено');
    }

    public function delete(int $id): void
    {
        $type = MachineryType::findOrFail($id);
        if ($type->icon) {
            Storage::disk('public')->delete($type->icon);
        }
        $type->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $items = MachineryType::query()
            ->withCount('models')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.machinery.simple-index', [
            'items'      => $items,
            'title'      => 'Типи техніки',
            'addLabel'   => '+ Додати тип',
            'countKey'   => 'models_count',
            'countLabel' => 'Моделей',
            'withIcon'   => true,
        ])->layout('admin.layouts.admin');
    }
}
