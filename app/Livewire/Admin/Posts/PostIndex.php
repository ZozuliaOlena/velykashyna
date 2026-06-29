<?php

namespace App\Livewire\Admin\Posts;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class PostIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function togglePublished(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->is_published = ! $post->is_published;
        // При першій публікації фіксуємо дату, якщо її ще немає.
        if ($post->is_published && ! $post->published_at) {
            $post->published_at = now();
        }
        $post->save();
    }

    public function delete(int $id): void
    {
        Post::findOrFail($id)->delete();
        session()->flash('success', 'Статтю видалено');
    }

    public function render()
    {
        $posts = Post::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.posts.post-index', compact('posts'))
            ->layout('admin.layouts.admin');
    }
}
