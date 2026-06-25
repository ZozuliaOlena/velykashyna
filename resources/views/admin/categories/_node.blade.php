{{-- Рекурсивний вузол дерева категорій (згортуваний + drag-and-drop) --}}
<li class="cat-node" wire:key="cat-{{ $cat->id }}" data-id="{{ $cat->id }}" x-data="{ open: true }">
    <div class="cat-row {{ $cat->is_active ? '' : 'is-inactive' }}">
        <span class="cat-handle" title="Перетягнути для сортування">⠿</span>

        @if($cat->children->isNotEmpty())
            <button type="button" class="cat-caret" :class="{ 'is-open': open }" x-on:click="open = !open">▶</button>
        @else
            <span class="cat-caret cat-caret--leaf">▶</span>
        @endif

        <span class="cat-name {{ $cat->children->isNotEmpty() ? 'is-branch' : 'is-leaf' }}">{{ $cat->name }}</span>

        @if($cat->children->isNotEmpty())
            <span class="cat-badge" title="Підкатегорій">{{ $cat->children->count() }}</span>
        @endif
        @if($cat->products_count)
            <span class="cat-badge cat-badge--muted" title="Товарів">{{ $cat->products_count }} тов.</span>
        @endif

        <span class="cat-spacer"></span>

        <button class="row-toggle {{ $cat->is_active ? 'is-on' : 'is-off' }}"
            wire:click="toggleActive({{ $cat->id }})" title="Активність">
            {{ $cat->is_active ? 'Так' : 'Ні' }}
        </button>
        <button class="icon-btn" wire:click="openEdit({{ $cat->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
        <button class="icon-btn" wire:click="delete({{ $cat->id }})" wire:confirm="Видалити категорію?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
    </div>

    @if($cat->children->isNotEmpty())
        <ul class="cat-children cat-sortable" data-parent="{{ $cat->id }}" x-show="open">
            @foreach($cat->children as $child)
                @include('admin.categories._node', ['cat' => $child])
            @endforeach
        </ul>
    @endif
</li>
