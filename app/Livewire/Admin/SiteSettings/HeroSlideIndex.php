<?php

namespace App\Livewire\Admin\SiteSettings;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\HeroSlide;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * «Налаштування сайту» → hero-слайдер головної сторінки.
 * Керування слайдами: заголовок, опис, фон (фото / відео / YouTube), порядок.
 */
class HeroSlideIndex extends Component
{
    use WithFileUploads;
    use WithAdminToast;

    public const BG_TYPES = [
        'image'   => 'Фото',
        'video'   => 'Відео (файл)',
        'youtube' => 'Відео з YouTube',
    ];

    public bool $showModal = false;
    public ?int $editingId = null;

    // Поля форми.
    public ?string $title = null;
    public ?string $subtitle = null;
    public string $bg_type = 'image';
    public ?string $youtube_url = null;
    public int $sort_order = 0;
    public bool $is_active = true;

    public $bg = null;                 // новий файл фону (фото/відео)
    public ?string $currentBg = null;  // збережений шлях (для прев'ю)

    protected function rules(): array
    {
        return [
            'title'       => ['nullable', 'string', 'max:255'],
            'subtitle'    => ['nullable', 'string', 'max:500'],
            'bg_type'     => ['required', 'in:image,video,youtube'],
            'youtube_url' => ['nullable', 'string', 'max:255', 'required_if:bg_type,youtube'],
            'sort_order'  => ['integer', 'min:0'],
            'is_active'   => ['boolean'],
            'bg'          => [
                'nullable',
                'file',
                'max:65536',
                $this->bg_type === 'video' ? 'mimes:mp4,webm,ogg,mov' : 'mimes:jpg,jpeg,png,webp,gif',
            ],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'title', 'subtitle', 'youtube_url', 'bg', 'currentBg');
        $this->bg_type = 'image';
        $this->is_active = true;
        $this->sort_order = (int) (HeroSlide::max('sort_order') + 1);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $slide = HeroSlide::findOrFail($id);
        $this->editingId   = $id;
        $this->title       = $slide->title;
        $this->subtitle    = $slide->subtitle;
        $this->bg_type     = $slide->bg_type;
        $this->youtube_url = $slide->youtube_url;
        $this->sort_order  = $slide->sort_order;
        $this->is_active   = $slide->is_active;
        $this->bg          = null;
        $this->currentBg   = $slide->bg_path;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $slide = $this->editingId ? HeroSlide::findOrFail($this->editingId) : new HeroSlide();

        $slide->title       = $this->title;
        $slide->subtitle    = $this->subtitle;
        $slide->bg_type     = $this->bg_type;
        $slide->youtube_url = $this->bg_type === 'youtube' ? $this->youtube_url : null;
        $slide->sort_order  = $this->sort_order;
        $slide->is_active   = $this->is_active;

        // Завантаження нового фото/відео фону.
        if ($this->bg_type !== 'youtube' && $this->bg) {
            if ($slide->bg_path) {
                Storage::disk('public')->delete($slide->bg_path);
            }
            $slide->bg_path = $this->bg->store('hero-slides', 'public');
        }

        $slide->save();

        $this->showModal = false;
        $this->reset('bg', 'currentBg');
        session()->flash('success', 'Слайд збережено');
    }

    public function deleteBg(int $id): void
    {
        $slide = HeroSlide::findOrFail($id);
        if ($slide->bg_path) {
            Storage::disk('public')->delete($slide->bg_path);
            $slide->update(['bg_path' => null]);
        }
        $this->currentBg = null;
        session()->flash('success', 'Фон видалено');
    }

    public function toggleActive(int $id): void
    {
        $slide = HeroSlide::findOrFail($id);
        $slide->update(['is_active' => ! $slide->is_active]);
    }

    /** Змінити порядок: посунути слайд вгору/вниз (обмін sort_order з сусідом). */
    public function move(int $id, string $dir): void
    {
        $slide = HeroSlide::findOrFail($id);

        $neighbor = HeroSlide::query()
            ->when($dir === 'up',
                fn ($q) => $q->where('sort_order', '<', $slide->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $slide->sort_order)->orderBy('sort_order'))
            ->first();

        if (! $neighbor) {
            return;
        }

        [$slide->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $slide->sort_order];
        $slide->save();
        $neighbor->save();
    }

    public function delete(int $id): void
    {
        $slide = HeroSlide::findOrFail($id);
        if ($slide->bg_path) {
            Storage::disk('public')->delete($slide->bg_path);
        }
        $slide->delete();
        session()->flash('success', 'Слайд видалено');
    }

    public function render()
    {
        return view('admin.site-settings.hero-slides', [
            'slides'   => HeroSlide::ordered()->get(),
            'bgTypes'  => self::BG_TYPES,
        ])->layout('admin.layouts.admin');
    }
}
