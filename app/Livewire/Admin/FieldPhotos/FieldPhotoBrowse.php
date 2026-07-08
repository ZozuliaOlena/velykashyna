<?php

namespace App\Livewire\Admin\FieldPhotos;

use App\Models\MachineryModel;
use App\Models\MachineryType;
use App\Models\ProductFieldPhoto;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FieldPhotoBrowse extends Component
{
    use WithPagination;

    #[Url]
    public ?int $filterType = null;

    #[Url]
    public ?int $filterModel = null;

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterModel(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('filterType', 'filterModel');
        $this->resetPage();
    }

    public function render()
    {
        $photos = ProductFieldPhoto::query()
            ->with(['product', 'machineryType', 'machineryBrand', 'machineryModel', 'media'])
            ->when($this->filterType, fn ($q) => $q->where('machinery_type_id', $this->filterType))
            ->when($this->filterModel, fn ($q) => $q->where('machinery_model_id', $this->filterModel))
            ->orderByDesc('id')
            ->paginate(24);

        $types = MachineryType::orderBy('name')->get(['id', 'name']);
        $models = MachineryModel::with('brand')->orderBy('name')->get();

        return view('admin.field-photos.field-photo-browse', [
            'photos' => $photos,
            'typeOptions' => $types->map(fn ($t) => ['value' => $t->id, 'label' => $t->name])->all(),
            'modelOptions' => $models->map(fn ($m) => [
                'value' => $m->id,
                'label' => trim(($m->brand?->name ? $m->brand->name . ' ' : '') . $m->name),
            ])->all(),
        ])->layout('admin.layouts.admin');
    }
}
