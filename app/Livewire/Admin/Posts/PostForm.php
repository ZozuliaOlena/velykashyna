<?php

namespace App\Livewire\Admin\Posts;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Post;
use App\Support\Translit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostForm extends Component
{
    use WithFileUploads;
    use WithAdminToast;

    public ?int $postId = null;

    public string $title = '';
    public ?string $slug = null;
    public ?string $excerpt = null;
    public ?string $content = null;

    public bool $is_published = false;
    public ?string $published_at = null;   // 'Y-m-d\TH:i' (datetime-local)

    public ?string $seo_title = null;
    public ?string $seo_description = null;

    public $image = null;   // нове головне фото

    public function mount(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $post = Post::findOrFail($id);

        $this->postId          = $post->id;
        $this->title           = $post->title;
        $this->slug            = $post->slug;
        $this->excerpt         = $post->excerpt;
        $this->content         = $post->content;
        $this->is_published    = $post->is_published;
        $this->published_at    = $post->published_at?->format('Y-m-d\TH:i');
        $this->seo_title       = $post->seo_title;
        $this->seo_description = $post->seo_description;
    }

    protected function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'excerpt'         => ['nullable', 'string', 'max:500'],
            'content'         => ['nullable', 'string'],
            'is_published'    => ['boolean'],
            'published_at'    => ['nullable', 'date'],
            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'image'           => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        $post = $this->postId ? Post::findOrFail($this->postId) : new Post();

        $post->title           = $data['title'];
        $post->excerpt         = $data['excerpt'];
        $post->content         = $data['content'];
        $post->is_published    = $this->is_published;
        $post->seo_title       = $data['seo_title'];
        $post->seo_description = $data['seo_description'];

        // Дата публікації: задана вручну, інакше при першій публікації — зараз.
        $post->published_at = $this->published_at
            ? Carbon::parse($this->published_at)
            : ($this->is_published ? ($post->published_at ?? now()) : $post->published_at);

        // Ручний slug; порожній → з заголовка. Завжди унікалізуємо.
        $post->slug = $this->buildUniqueSlug($this->slug ?: $this->title, $this->postId);
        $post->save();

        if ($this->image) {
            $post->addMedia($this->image->getRealPath())
                ->usingFileName($this->uploadName($this->image))
                ->toMediaCollection('image');
        }

        $this->reset('image');
        session()->flash('success', 'Статтю збережено');

        return $this->redirectRoute('admin.posts.index', navigate: true);
    }

    public function deleteImage(): void
    {
        if (! $this->postId) {
            return;
        }

        Post::findOrFail($this->postId)->clearMediaCollection('image');
        session()->flash('success', 'Фото видалено');
    }

    private function uploadName($file): string
    {
        return Str::random(24) . '.' . $file->getClientOriginalExtension();
    }

    /** Унікальний slug (укр. транслітерація), із суфіксом -2, -3… при колізії. */
    private function buildUniqueSlug(string $source, ?int $ignoreId): string
    {
        $base = Str::slug(Translit::uk($source)) ?: 'stattya';
        $slug = $base;
        $i = 1;

        while (
            Post::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    public function render()
    {
        $post = $this->postId ? Post::find($this->postId) : null;

        return view('admin.posts.post-form', [
            'currentImage' => $post?->imageUrl('thumb'),
        ])->layout('admin.layouts.admin');
    }
}
