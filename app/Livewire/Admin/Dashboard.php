<?php

namespace App\Livewire\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Product;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('admin.dashboard', [
            'productsCount'   => Product::count(),
            'categoriesCount' => Category::count(),
            'brandsCount'     => Brand::count(),
            'newLeadsCount'   => Lead::where('status', 'new')->count(),
        ])->layout('admin.layouts.admin');
    }
}
