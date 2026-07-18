<?php

namespace App\Livewire\Admin\Machinery;

use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\WithAdminToast;
use App\Models\MachineryPosition;
use App\Models\ProductMachineryCompatibility;
use Livewire\Component;
use Livewire\WithPagination;

class MachineryPositionIndex extends Component
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
        $item = MachineryPosition::findOrFail($id);
        $this->editingId = $id;
        $this->name = $item->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        MachineryPosition::updateOrCreate(['id' => $this->editingId], $data);
        $this->showModal = false;
        $this->reset('name', 'editingId');
        session()->flash('success', 'Збережено');
    }

    public function delete(int $id): void
    {
        MachineryPosition::findOrFail($id)->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $items = MachineryPosition::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        // Скільки товарів мають цю позицію в сумісності (один запит).
        $usage = ProductMachineryCompatibility::query()->whereNotNull('position_id')
            ->selectRaw('position_id, COUNT(DISTINCT product_id) c')->groupBy('position_id')
            ->pluck('c', 'position_id');

        $confirm = $items->mapWithKeys(fn ($p) => [$p->id => $this->confirmText(
            $p->name,
            ($n = (int) ($usage[$p->id] ?? 0)) > 0
                ? "вживається в сумісності товарів: {$n} (цей зв'язок буде очищено)"
                : null,
        )]);

        return view('admin.machinery.simple-index', [
            'items'    => $items,
            'title'    => 'Позиції на техніці',
            'addLabel' => '+ Додати позицію',
            'confirmMessages' => $confirm,
        ])->layout('admin.layouts.admin');
    }
}
