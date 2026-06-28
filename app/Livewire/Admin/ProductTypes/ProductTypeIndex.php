<?php

namespace App\Livewire\Admin\ProductTypes;

use App\Models\ProductType;
use App\Livewire\Concerns\WithAdminToast;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ProductTypeIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $code = '';
    public string $name = '';
    public ?string $google_category = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('product_types', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'google_category' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('code', 'name', 'google_category', 'editingId');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $type = ProductType::findOrFail($id);
        $this->editingId = $id;
        $this->code = $type->code;
        $this->name = $type->name;
        $this->google_category = $type->google_category;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        ProductType::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        $this->reset('code', 'name', 'google_category', 'editingId');
        session()->flash('success', 'Збережено');
    }

    public function delete(int $id): void
    {
        $type = ProductType::withCount('products')->findOrFail($id);

        if ($type->products_count > 0) {
            session()->flash('error', 'Неможливо видалити: є товари цього типу');
            return;
        }

        $type->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $types = ProductType::query()
            ->withCount('products')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.product-types.product-type-index', compact('types'))
            ->layout('admin.layouts.admin');
    }
}
