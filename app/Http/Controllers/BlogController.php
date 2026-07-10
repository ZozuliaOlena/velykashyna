<?php

namespace App\Http\Controllers;

use App\Models\Post;

class BlogController extends Controller
{
    /** Список опублікованих статей (пагінація). */
    public function index()
    {
        $posts = Post::published()->paginate(9);

        return view('web.blog.index', [
            'posts' => $posts,
            'showFooterCta' => false,
        ]);
    }

    /** Детальна сторінка статті за ЧПУ-slug. */
    public function show(Post $post)
    {
        // Чернетки/майбутні публікації - лише прихований, не публічний доступ.
        abort_unless($post->is_published
            && (! $post->published_at || $post->published_at->isPast()), 404);

        $related = Post::published()
            ->whereKeyNot($post->id)
            ->limit(3)
            ->get();

        return view('web.blog.show', [
            'post' => $post,
            'related' => $related,
            'showFooterCta' => false,
        ]);
    }
}
