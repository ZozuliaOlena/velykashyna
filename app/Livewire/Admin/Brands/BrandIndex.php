<?php

// app/Livewire/Admin/Brands/BrandIndex.php
namespace App\Livewire\Admin\Brands;

use App\Models\Brand;
use App\Support\Translit;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class BrandIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // поля форми
    public string $name = '';
    public string $country = '';
    public bool $is_active = true;

    protected $rules = [
        'name'    => 'required|string|max:255',
        'country' => 'nullable|string|max:255',
    ];

    // скидаємо пагінацію при пошуку
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'country', 'is_active', 'editingId']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $brand->name;
        $this->country   = $brand->country ?? '';
        $this->is_active = $brand->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Brand::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name'      => $this->name,
                'slug'      => Str::slug(Translit::uk($this->name)),
                'country'   => $this->country ?: null,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->reset(['name', 'country', 'editingId']);
        session()->flash('success', 'Збережено');
    }

    public function toggleActive(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['is_active' => ! $brand->is_active]);
    }

    public function delete(int $id): void
    {
        Brand::findOrFail($id)->delete();
    }

    public function render()
    {
        $brands = Brand::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('name')
            ->paginate(20);

        return view('admin.brands.brand-index', compact('brands'))
            ->layout('admin.layouts.admin');
    }
}
