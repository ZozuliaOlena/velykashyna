@extends('layouts.app')

@section('title', 'Шини в роботі - ' . $label . ' | ВЕЛИКА ШИНА')
@section('meta_description', 'Реальні фото встановлених шин на ' . $label . ' - приклади застосування з полів та інспекцій.')

@section('content')
<section class="section in-work" x-data="{ open: false, src: '', cap: '' }">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <a href="{{ route('catalog', ['machinery' => $model->type?->name]) }}">{{ $model->type?->name ?: 'Каталог' }}</a>
            <span class="sep">/</span>
            <span class="current">{{ $label }} - в роботі</span>
        </nav>

        <div class="in-work__head">
            <span class="about-kicker">Реальні приклади</span>
            <h1 class="section-title">Шини в роботі - <span>{{ $label }}</span></h1>
            <p class="about-sub">Фото встановлених шин на цій техніці - з полів, складу та інспекцій. Натисніть фото, щоб збільшити.</p>
        </div>

        @if ($photos->isNotEmpty())
        <div class="inwork-grid">
            @foreach ($photos as $fp)
            <article class="inwork-card">
                <button type="button" class="inwork-card__photo"
                    @click="src = '{{ $fp->imageUrl('large') }}'; cap = @js(trim($fp->caption ?? '')); open = true">
                    <img src="{{ $fp->imageUrl('thumb') ?: $fp->imageUrl('large') }}" alt="{{ $label }}" loading="lazy" />
                </button>
                <div class="inwork-card__body">
                    @if ($fp->caption)
                    <p class="inwork-card__note">{{ $fp->caption }}</p>
                    @endif
                    @if ($fp->product)
                    <a href="{{ route('product', $fp->product->slug) }}" class="inwork-card__product">
                        @php($thumb = $fp->product->thumbUrl())
                        <span class="inwork-card__thumb">
                            <img src="{{ $thumb ?: '/images/svg/tehnics/wheel.svg' }}" alt="{{ $fp->product->model }}" loading="lazy" />
                        </span>
                        <span class="inwork-card__pname">
                            <b>{{ trim(($fp->product->productType?->name ? $fp->product->productType->name . ' ' : '') . $fp->product->size_raw) }}</b>
                            {{ trim(($fp->product->brand?->name ?? '') . ' ' . ($fp->product->model ?? '')) }}
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" />
                        </svg>
                    </a>
                    @endif
                </div>
            </article>
            @endforeach
        </div>

        <div class="about-lightbox" x-show="open" x-cloak x-transition.opacity @click="open = false"
            @keydown.escape.window="open = false">
            <button type="button" class="about-lightbox__close" aria-label="Закрити">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <figure class="field-lightbox__fig" @click.stop>
                <img :src="src" alt="" />
                <figcaption x-show="cap" x-text="cap"></figcaption>
            </figure>
        </div>
        @else
        <div class="cart-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <path d="M21 15l-5-5L5 21" />
            </svg>
            <p>Поки немає фото шин у роботі на «{{ $label }}».</p>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти до каталогу</a>
        </div>
        @endif
    </div>
</section>
@endsection
