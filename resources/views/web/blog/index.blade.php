@extends('layouts.app')

@section('body_class', 'page-blog')

@section('title', 'Блог - ВЕЛИКА ШИНА | Корисні статті про шини для агро та спецтехніки')
@section('meta_description', 'Блог компанії ВЕЛИКА ШИНА: підбір і експлуатація шин для сільгосп-, спец- та вантажної техніки, поради, новини та корисні матеріали.')

@section('content')
<section class="section blog">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Блог</span>
        </nav>

        <div class="blog__head">
            <span class="about-kicker">Корисне</span>
            <h1 class="section-title">Блог <span>ВЕЛИКА ШИНА</span></h1>
            <p class="about-sub">Поради щодо підбору й експлуатації шин, новини компанії та корисні матеріали для власників техніки.</p>
        </div>

        @if ($posts->count())
        <div class="blog-grid">
            @foreach ($posts as $post)
            <article class="blog-card">
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card__media">
                    @if ($post->imageUrl())
                        <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy">
                    @else
                        <span class="blog-card__ph">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="M21 15l-5-5L5 21"/>
                            </svg>
                        </span>
                    @endif
                </a>
                <div class="blog-card__body">
                    <div class="blog-card__meta">
                        @if ($post->published_at)
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->formattedDate() }}</time>
                            <span class="dot">•</span>
                        @endif
                        <span>{{ $post->readingTime() }} хв читання</span>
                    </div>
                    <h2 class="blog-card__title">
                        <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="blog-card__excerpt">{{ $post->teaser() }}</p>
                    <a href="{{ route('blog.show', $post->slug) }}" class="blog-card__more">
                        Читати далі
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        @if ($posts->hasPages())
        <nav class="pagination" aria-label="Сторінки">
            @if ($posts->onFirstPage())
            <span class="pg-arrow is-disabled" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </span>
            @else
            <a href="{{ $posts->previousPageUrl() }}" class="pg-arrow" aria-label="Назад">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            @endif

            @php($last = $posts->lastPage())
            @php($cur = $posts->currentPage())
            @php($pages = collect(range(1, $last))->filter(fn ($pg) => $pg == 1 || $pg == $last || ($pg >= $cur - 1 && $pg <= $cur + 1)))
            @php($prev = 0)
            @foreach ($pages as $pg)
            @if ($prev && $pg - $prev > 1)<span class="pg-dots">…</span>@endif
            <a href="{{ $posts->url($pg) }}" class="pg-num {{ $pg == $cur ? 'is-active' : '' }}">{{ $pg }}</a>
            @php($prev = $pg)
            @endforeach

            @if ($posts->hasMorePages())
            <a href="{{ $posts->nextPageUrl() }}" class="pg-arrow" aria-label="Вперед">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @else
            <span class="pg-arrow is-disabled" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
            @endif
        </nav>
        @endif
        @else
        <div class="blog-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/>
            </svg>
            <p>Статей поки немає - незабаром тут з'являться корисні матеріали.</p>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти в каталог</a>
        </div>
        @endif
    </div>
</section>
@endsection
