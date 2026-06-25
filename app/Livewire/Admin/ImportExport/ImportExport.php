<?php

namespace App\Livewire\Admin\ImportExport;

use App\Exports\CatalogExport;
use App\Imports\CatalogImport;
use App\Models\CatalogImage;
use App\Services\ProductPhotoArchive;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportExport extends Component
{
    use WithFileUploads;

    public $importFile = null;
    public $photoArchive = null;
    public array $catalogImages = [];

    public array $importReport = [];
    public array $photoReport = [];
    public array $catalogImageReport = [];

    public function export()
    {
        return Excel::download(new CatalogExport(), 'velykashyna-catalog.xlsx');
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
        ]);

        $import = new CatalogImport();
        Excel::import($import, $this->importFile->getRealPath());

        $this->importReport = $import->report;
        $this->reset('importFile');
    }

    public function uploadPhotos(): void
    {
        $this->validate([
            'photoArchive' => ['required', 'file', 'mimes:zip', 'max:204800'],
        ]);

        $this->photoReport = app(ProductPhotoArchive::class)->import($this->photoArchive->getRealPath());
        $this->reset('photoArchive');
    }

    /**
     * Масова загрузка каталожних (стокових) фото за іменем файлу.
     * Імʼя файлу = значення колонки «Каталожне фото» в імпорті.
     * Один файл прив'язується до всіх товарів, що на нього посилаються.
     */
    public function uploadCatalogImages(): void
    {
        $this->validate([
            'catalogImages'   => ['required', 'array'],
            'catalogImages.*' => ['file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $uploaded = 0;
        $created = 0;
        $linked = 0;

        foreach ($this->catalogImages as $file) {
            $name = $file->getClientOriginalName();

            $ci = CatalogImage::firstOrNew(['filename' => $name]);
            if (! $ci->exists) {
                $ci->label = $name;
                $ci->save();
                $created++;
            }

            // замінюємо зображення (одне на запис)
            $ci->clearMediaCollection('image');
            $ci->addMedia($file->getRealPath())
                ->usingFileName(Str::random(24) . '.' . strtolower($file->getClientOriginalExtension()))
                ->toMediaCollection('image');

            $uploaded++;
            $linked += $ci->products()->count();
        }

        $this->catalogImageReport = compact('uploaded', 'created', 'linked');
        $this->reset('catalogImages');
    }

    public function render()
    {
        return view('admin.import-export.index')->layout('admin.layouts.admin');
    }
}
