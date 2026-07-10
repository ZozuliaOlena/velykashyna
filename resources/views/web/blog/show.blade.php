@extends('layouts.app')

@section('title', ($post->seo_title ?: $post->title) . ' - Блог | ВЕЛИКА ШИНА')
@section('meta_description', $post->seo_description ?: $post->teaser(32))

@push('head')
    @if ($post->imageUrl('large'))
        <meta property="og:image" content="{{ url($post->imageUrl('large')) }}" />
    @endif
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $post->seo_title ?: $post->title }}" />

    @php
        $articleLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->seo_title ?: $post->title,
            'description' => $post->seo_description ?: $post->teaser(32),
            'image' => url($post->imageUrl('large') ?: '/images/og-default.png'),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => 'ВЕЛИКА ШИНА'],
            'publisher' => ['@type' => 'Organization', 'name' => 'ВЕЛИКА ШИНА', 'logo' => ['@type' => 'ImageObject', 'url' => url('/images/logo.png')]],
            'mainEntityOfPage' => route('blog.show', $post->slug),
        ]);
        $breadcrumbLd = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Головна', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Блог', 'item' => route('blog.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => route('blog.show', $post->slug)],
        ]];
    @endphp
    <script type="application/ld+json">{!! json_encode($articleLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<article class="section blog-post">
    <div class="container container--narrow">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <a href="{{ route('blog.index') }}">Блог</a>
            <span class="sep">/</span>
            <span class="current">{{ $post->title }}</span>
        </nav>

        <header class="blog-post__head">
            <div class="blog-card__meta">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->formattedDate() }}</time>
                    <span class="dot">•</span>
                @endif
                <span>{{ $post->readingTime() }} хв читання</span>
            </div>
            <h1 class="blog-post__title">{{ $post->title }}</h1>
            @if ($post->excerpt)
                <p class="blog-post__lead">{{ $post->excerpt }}</p>
            @endif
        </header>

        @if ($post->imageUrl('large'))
        <figure class="blog-post__cover">
            <img src="{{ $post->imageUrl('large') }}" alt="{{ $post->title }}">
        </figure>
        @endif

        <div class="blog-post__body">
            {!! $post->content !!}
        </div>

        <div class="blog-post__foot">
            <a href="{{ route('blog.index') }}" class="blog-card__more">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Усі статті
            </a>
        </div>
    </div>

    @if ($related->count())
    <div class="container">
        <div class="blog-related">
            <h2 class="section-title">Читайте <span>також</span></h2>
            <div class="blog-grid">
                @foreach ($related as $rp)
                <article class="blog-card">
                    <a href="{{ route('blog.show', $rp->slug) }}" class="blog-card__media">
                        @if ($rp->imageUrl())
                            <img src="{{ $rp->imageUrl() }}" alt="{{ $rp->title }}" loading="lazy">
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
                            @if ($rp->published_at)
                                <time datetime="{{ $rp->published_at->toDateString() }}">{{ $rp->formattedDate() }}</time>
                            @endif
                        </div>
                        <h3 class="blog-card__title">
                            <a href="{{ route('blog.show', $rp->slug) }}">{{ $rp->title }}</a>
                        </h3>
                        <p class="blog-card__excerpt">{{ $rp->teaser(18) }}</p>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</article>
@endsection
