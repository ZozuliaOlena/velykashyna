<?php

// app/Livewire/Admin/Brands/BrandIndex.php
namespace App\Livewire\Admin\Brands;

use App\Models\Brand;
use App\Support\Translit;
use Illuminate\Support\Facades\Storage;
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
    public $logo = null;            // новий файл логотипа (завантаження)
    public ?string $currentLogo = null; // вже збережений шлях (для прев'ю)

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
        $this->reset(['name', 'country', 'is_active', 'editingId', 'logo', 'currentLogo']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $brand->name;
        $this->country     = $brand->country ?? '';
        $this->is_active   = $brand->is_active;
        $this->logo        = null;
        $this->currentLogo = $brand->logo;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $brand = $this->editingId ? Brand::findOrFail($this->editingId) : new Brand();

        $brand->name      = $this->name;
        $brand->slug      = Str::slug(Translit::uk($this->name));
        $brand->country   = $this->country ?: null;
        $brand->is_active = $this->is_active;

        // новий логотип → кладемо у storage/app/public/brands, шлях у БД
        if ($this->logo) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $brand->logo = $this->logo->store('brands', 'public');
        }

        $brand->save();

        $this->showModal = false;
        $this->reset(['name', 'country', 'editingId', 'logo', 'currentLogo']);
        session()->flash('success', 'Збережено');
    }

    public function deleteLogo(int $id): void
    {
        $brand = Brand::findOrFail($id);

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
            $brand->update(['logo' => null]);
        }

        $this->currentLogo = null;
        session()->flash('success', 'Логотип видалено');
    }

    public function toggleActive(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['is_active' => ! $brand->is_active]);
    }

    public function delete(int $id): void
    {
        $brand = Brand::findOrFail($id);

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();
    }

    public function render()
    {
        $brands = Brand::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('name')
            ->paginate(20);

        return view('admin.brands.brand-index', compact('brands'))
            ->layout('admin.layouts.admin');
    }
}
