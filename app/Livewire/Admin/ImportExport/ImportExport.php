<?php

namespace App\Livewire\Admin\ImportExport;

use App\Exports\CatalogExport;
use App\Imports\CatalogImport;
use App\Services\ProductPhotoArchive;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportExport extends Component
{
    use WithFileUploads;

    public $importFile = null;
    public $photoArchive = null;

    public array $importReport = [];
    public array $photoReport = [];

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

    public function render()
    {
        return view('admin.import-export.index')->layout('admin.layouts.admin');
    }
}
