<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Блог</h1>
        <a href="{{ route('admin.posts.create') }}">
            <button class="btn-primary">+ Додати статтю</button>
        </a>
    </div>


    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук за заголовком...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr>
                <th>Фото</th>
                <th>Заголовок</th>
                <th>Опубліковано</th>
                <th>Дата</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            <tr wire:key="post-{{ $post->id }}">
                <td data-label="Фото">
                    @php($thumb = $post->imageUrl('thumb'))
                    @if($thumb)
                        <img src="{{ $thumb }}" alt="" class="product-thumb">
                    @else
                        <span class="product-thumb product-thumb--empty">—</span>
                    @endif
                </td>
                <td data-label="Заголовок">{{ $post->title }}</td>
                <td data-label="Опубліковано">
                    <button wire:click="togglePublished({{ $post->id }})"
                        class="row-toggle {{ $post->is_published ? 'is-on' : 'is-off' }}">
                        {{ $post->is_published ? 'Так' : 'Ні' }}
                    </button>
                </td>
                <td data-label="Дата">{{ $post->published_at?->format('d.m.Y') ?? '—' }}</td>
                <td class="cell-actions">
                    <a class="icon-btn" href="{{ route('admin.posts.edit', $post->id) }}" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></a>
                    <button class="icon-btn" wire:click="delete({{ $post->id }})"
                        data-confirm="Ви дійсно хочете видалити статтю?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Статей не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $posts->links('pagination.admin') }}</div>
</div>
