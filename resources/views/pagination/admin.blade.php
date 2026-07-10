@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Пагінація" class="adm-pg">
        <span class="adm-pg__info">
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} із {{ $paginator->total() }}
        </span>

        <div class="adm-pg__list">
            @if ($paginator->onFirstPage())
                <span class="adm-pg__btn is-disabled" aria-hidden="true">‹</span>
            @else
                <button type="button" class="adm-pg__btn" rel="prev"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" aria-label="Попередня">‹</button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="adm-pg__dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="adm-pg__btn is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <button type="button" class="adm-pg__btn"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" class="adm-pg__btn" rel="next"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')" aria-label="Наступна">›</button>
            @else
                <span class="adm-pg__btn is-disabled" aria-hidden="true">›</span>
            @endif
        </div>
    </nav>
@endif
