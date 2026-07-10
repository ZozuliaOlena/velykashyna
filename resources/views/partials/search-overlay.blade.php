<div class="search-overlay" x-data x-cloak x-show="$store.ui.search" x-transition.opacity
    @keydown.escape.window="$store.ui.closeSearch()" @click.self="$store.ui.closeSearch()">
    <div class="search-box" x-data="liveSearch('{{ route('search.suggest') }}', '{{ route('catalog') }}')">
        <div class="search-head">
            <span>Пошук по каталогу</span>
            <button type="button" aria-label="Закрити" @click="$store.ui.closeSearch()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <div class="search-field">
            <form action="{{ route('catalog') }}" method="GET">
                <input type="text" name="q" placeholder="Типорозмір або артикул, напр. 800/65R32"
                    x-ref="searchInput" x-model="q" @input="onInput()"
                    x-effect="$store.ui.search && $nextTick(() => $refs.searchInput.focus())" />
                <button type="submit" aria-label="Шукати">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </button>
            </form>
            @include('partials.search-results')
        </div>

        <p class="search-hint" x-show="!open">Введіть розмір (наприклад 710/70R38) або артикул товару.</p>
    </div>
</div>
