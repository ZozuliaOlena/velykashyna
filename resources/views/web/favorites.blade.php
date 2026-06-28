{{-- resources/views/web/favorites.blade.php — обране (стан у localStorage) --}}
@extends('layouts.app')

@section('title', 'Обране — Велика Шина')

@section('content')
<section class="section fav-page" x-data="removeConfirm('fav')">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Обране</span>
        </nav>

        <h1 class="page-title">Обране</h1>

        {{-- Порожньо --}}
        <div class="cart-empty" x-show="$store.fav.items.length === 0" x-cloak>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            <p>У вас ще немає обраних товарів.</p>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти до каталогу</a>
        </div>

        {{-- Список обраного --}}
        <div class="fav-grid" x-show="$store.fav.items.length > 0" x-cloak>
            <template x-for="i in $store.fav.items" :key="i.id">
                <div class="fav-card">
                    <button type="button" class="fav-card__remove" @click="askRemove(i)"
                        aria-label="Прибрати з обраного">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                    <a :href="i.url" class="fav-card__img">
                        <img :src="i.img || '/images/svg/tehnics/wheel.svg'" :alt="i.brand" />
                    </a>
                    <a :href="i.url" class="fav-card__name" x-text="i.size"></a>
                    <span class="fav-card__sub" x-text="(i.brand + ' ' + i.model).trim()"></span>
                    <div class="fav-card__price">
                        <template x-if="i.price && i.price_mode !== 'inquiry'">
                            <span><span x-text="i.price.toLocaleString('uk-UA')"></span> <small
                                    x-text="i.cur"></small></span>
                        </template>
                        <template x-if="!i.price || i.price_mode === 'inquiry'">
                            <span class="muted">Уточнюйте</span>
                        </template>
                    </div>
                    <button type="button" class="btn btn--primary btn--block" @click="$store.cart.add(i)">
                        До кошика
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Підтвердження видалення --}}
    <div class="modal" x-show="confirm.open" x-cloak x-transition.opacity.duration.200ms
        @keydown.escape.window="cancelRemove()">
        <div class="modal__backdrop" @click="cancelRemove()"></div>
        <div class="modal__box" x-transition>
            <h3 class="modal__title">Прибрати з обраного?</h3>
            <p class="modal__text">Видалити «<span x-text="confirm.name"></span>» зі списку обраного?</p>
            <div class="modal__actions">
                <button type="button" class="btn btn--outline" @click="cancelRemove()">Скасувати</button>
                <button type="button" class="btn btn--primary" @click="doRemove()">Видалити</button>
            </div>
        </div>
    </div>
</section>
@endsection
