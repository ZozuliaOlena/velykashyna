<?php

namespace App\Livewire\Admin\Categories;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $parent_id = null;
    public string $name = '';
    public int $sort_order = 0;
    public bool $is_active = true;
    public ?string $seo_title = null;
    public ?string $seo_description = null;
    public ?string $seo_h1 = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'parent_id'       => ['nullable', 'exists:categories,id'],
            'name'            => ['required', 'string', 'max:255'],
            'sort_order'      => ['integer', 'min:0'],
            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_h1'          => ['nullable', 'string', 'max:255'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('parent_id', 'name', 'sort_order', 'is_active', 'seo_title', 'seo_description', 'seo_h1', 'editingId');
        $this->is_active = true;
        $this->showModal = true;
    }

    /**
     * Авто-генерація SEO з назви категорії. Заповнює лише порожні поля.
     */
    public function generateSeo(): void
    {
        if (blank($this->name)) {
            session()->flash('error', 'Спершу вкажіть назву категорії');
            return;
        }

        $seo = (new Category(['name' => $this->name]))->defaultSeo();

        if (blank($this->seo_title))       { $this->seo_title = $seo['title']; }
        if (blank($this->seo_description)) { $this->seo_description = $seo['description']; }
        if (blank($this->seo_h1))          { $this->seo_h1 = $seo['h1']; }

        session()->flash('success', 'SEO-поля згенеровано (порожні заповнено)');
    }

    public function openEdit(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editingId       = $id;
        $this->parent_id       = $cat->parent_id;
        $this->name            = $cat->name;
        $this->sort_order      = $cat->sort_order;
        $this->is_active       = $cat->is_active;
        $this->seo_title       = $cat->seo_title;
        $this->seo_description = $cat->seo_description;
        $this->seo_h1          = $cat->seo_h1;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // рівень визначаємо від батька (корінь = 1), максимум 4
        $level = 1;
        if ($this->parent_id) {
            $parent = Category::findOrFail($this->parent_id);
            $level = $parent->level + 1;

            if ($level > 4) {
                $this->addError('parent_id', 'Максимальна глибина — 4 рівні');
                return;
            }
        }

        // не можна зробити категорію нащадком самої себе
        if ($this->editingId && in_array($this->parent_id, $this->descendantIds($this->editingId), true)) {
            $this->addError('parent_id', 'Категорія не може бути власним нащадком');
            return;
        }

        $cat = $this->editingId ? Category::findOrFail($this->editingId) : new Category();
        $cat->fill([
            'parent_id'       => $this->parent_id ?: null,
            'level'           => $level,
            'name'            => $this->name,
            'sort_order'      => $this->sort_order,
            'is_active'       => $this->is_active,
            'seo_title'       => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_h1'          => $this->seo_h1,
        ]);
        $cat->save();

        $this->showModal = false;
        session()->flash('success', 'Збережено');
    }

    public function toggleActive(int $id): void
    {
        $cat = Category::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    /**
     * Перевпорядкувати сусідні категорії (drag-and-drop).
     * $orderedIds — id прямих дітей одного батька в новому порядку.
     */
    public function reorder(array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $index => $id) {
            Category::where('id', (int) $id)->update(['sort_order' => $index]);
        }
    }

    public function delete(int $id): void
    {
        $cat = Category::withCount('children')->findOrFail($id);

        if ($cat->children_count > 0) {
            session()->flash('error', 'Спочатку видаліть або перенесіть підкатегорії');
            return;
        }

        $cat->delete();
        session()->flash('success', 'Видалено');
    }

    /** Усі нащадки категорії (id), щоб не утворити цикл. */
    private function descendantIds(int $id): array
    {
        $ids = [$id];
        $children = Category::where('parent_id', $id)->pluck('id');
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->descendantIds($childId));
        }
        return $ids;
    }

    public function render()
    {
        // Пошук — плаский список (дерево має сенс лише цілком);
        // без пошуку — згортуване вкладене дерево з drag-and-drop.
        $searching = $this->search !== '';

        if ($searching) {
            $tree = Category::query()
                ->withCount(['children', 'products'])
                ->where('name', 'like', "%{$this->search}%")
                ->orderBy('name')
                ->get();
        } else {
            $tree = Category::nestedTree(fn ($q) => $q->withCount(['children', 'products']));
        }

        // для випадаючого списку батьків (рівень < 4, щоб дитина не вийшла за 4)
        $parents = Category::treeOrdered(fn ($q) => $q->where('level', '<', 4));

        return view('admin.categories.category-index', compact('tree', 'parents', 'searching'))
            ->layout('admin.layouts.admin');
    }
}
