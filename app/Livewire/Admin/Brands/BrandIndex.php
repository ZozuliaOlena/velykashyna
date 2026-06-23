<?php

// app/Livewire/Admin/Brands/BrandIndex.php
namespace App\Livewire\Admin\Brands;

use App\Models\Brand;
use App\Support\Translit;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class BrandIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // поля форми
    public string $name = '';
    public string $country = '';
    public bool $is_active = true;
    public $logo = null; // завантаження логотипу

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo'    => 'nullable|image|max:5120',
        ];
    }

    // скидаємо пагінацію при пошуку
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'country', 'is_active', 'editingId', 'logo']);
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
        $this->logo      = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $brand = Brand::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name'      => $this->name,
                'slug'      => Str::slug(Translit::uk($this->name)),
                'country'   => $this->country ?: null,
                'is_active' => $this->is_active,
            ]
        );

        if ($this->logo) {
            $brand->addMedia($this->logo->getRealPath())
                ->usingFileName(Str::random(20) . '.' . $this->logo->getClientOriginalExtension())
                ->toMediaCollection('logo');
        }

        $this->showModal = false;
        $this->reset(['name', 'country', 'editingId', 'logo']);
        session()->flash('success', 'Збережено');
    }

    public function deleteLogo(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $brand->clearMediaCollection('logo');
        session()->flash('success', 'Логотип видалено');
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
            ->with('media')
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('name')
            ->paginate(20);

        $editingBrand = $this->editingId ? Brand::with('media')->find($this->editingId) : null;

        return view('admin.brands.brand-index', compact('brands', 'editingBrand'))
            ->layout('admin.layouts.admin');
    }
}
