{{-- resources/views/web/cart.blade.php — кошик (стан у localStorage) --}}
@extends('layouts.app')

@section('title', 'Кошик — Велика Шина')

@section('content')
<section class="section cart-page" x-data="cartCheckout('{{ route('api.leads.store') }}')">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Кошик</span>
        </nav>

        <h1 class="page-title">Кошик</h1>

        {{-- Успіх --}}
        <div class="cart-done" x-show="sent" x-cloak x-transition>
            <span class="cart-done__ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </span>
            <h2>Дякуємо за замовлення!</h2>
            <p>Номер вашого замовлення: <b>№&nbsp;<span x-text="orderId"></span></b>.<br>
                Ми зв'яжемося, щоб підтвердити деталі та узгодити доставку.</p>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Повернутись до каталогу</a>
        </div>

        {{-- Порожній кошик --}}
        <div class="cart-empty" x-show="!sent && $store.cart.items.length === 0" x-cloak>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
            </svg>
            <p>Ваш кошик порожній.</p>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти до каталогу</a>
        </div>

        {{-- Вміст кошика --}}
        <div class="cart" x-show="!sent && $store.cart.items.length > 0" x-cloak>
            {{-- 1. Товари --}}
            <section class="cart-section">
                <h2 class="cart-section__title">Товари в кошику
                    <span class="cart-section__count" x-text="$store.cart.count"></span>
                </h2>
                <div class="cart-items">
                    <template x-for="i in $store.cart.items" :key="i.id">
                        <div class="cart-item">
                            <a :href="i.url" class="cart-item__img">
                                <img :src="i.img || '/images/svg/tehnics/wheel.svg'" :alt="i.brand" />
                            </a>
                            <div class="cart-item__info">
                                <a :href="i.url" class="cart-item__name" x-text="i.size"></a>
                                <span class="cart-item__sub" x-text="(i.brand + ' ' + i.model).trim()"></span>
                            </div>
                            <div class="cart-item__qty qty">
                                <button type="button" @click="$store.cart.setQty(i.id, i.qty - 1)">−</button>
                                <input type="text" :value="i.qty" readonly />
                                <button type="button" @click="$store.cart.setQty(i.id, i.qty + 1)">+</button>
                            </div>
                            <div class="cart-item__price">
                                <template x-if="i.price && i.price_mode !== 'inquiry'">
                                    <span><span x-text="(i.price * i.qty).toLocaleString('uk-UA')"></span> <small
                                            x-text="i.cur"></small></span>
                                </template>
                                <template x-if="!i.price || i.price_mode === 'inquiry'">
                                    <span class="muted">Уточнюйте</span>
                                </template>
                            </div>
                            <button type="button" class="cart-item__remove" @click="askRemove(i)"
                                aria-label="Видалити">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <a href="{{ route('catalog') }}" class="cart-continue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Продовжити покупки
                </a>
            </section>

            {{-- 2. Оформлення замовлення --}}
            <form @submit.prevent="submit()" class="cart-checkout">
                <div class="cart-form-card">
                    <h2 class="cart-card__title">Оформлення замовлення</h2>
                    <div class="cart-form">
                        <div class="cart-field-group">
                            <label class="cart-field">
                                <span>Ваше ім'я</span>
                                <input type="text" x-model="form.name" placeholder="Як до вас звертатися" required />
                            </label>
                            <label class="cart-field">
                                <span>Телефон</span>
                                <input type="tel" x-model="form.phone" placeholder="+38 (0__) ___ __ __" required />
                            </label>
                        </div>

                        <label class="cart-field">
                            <span>Спосіб доставки</span>
                            <div class="select">
                                <select x-model="form.delivery">
                                    <option>Нова Пошта</option>
                                    <option>САТ</option>
                                    <option>Кур'єр</option>
                                    <option>Самовивіз зі складу</option>
                                </select>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                        </label>

                        <template x-if="!isPickup">
                            <div class="cart-field-group">
                                <label class="cart-field">
                                    <span>Місто</span>
                                    <input type="text" x-model="form.city" placeholder="Напр. Київ"
                                        :required="!isPickup" />
                                </label>
                                <label class="cart-field">
                                    <span>Відділення / адреса</span>
                                    <input type="text" x-model="form.address" placeholder="№ відділення або адреса"
                                        :required="!isPickup" />
                                </label>
                            </div>
                        </template>

                        <label class="cart-field">
                            <span>Спосіб оплати</span>
                            <div class="select">
                                <select x-model="form.payment">
                                    <option>Накладений платіж (при отриманні)</option>
                                    <option>Оплата за реквізитами (IBAN)</option>
                                </select>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                        </label>

                        <label class="cart-field">
                            <span>Коментар до замовлення</span>
                            <textarea x-model="form.comment" rows="3"
                                placeholder="Необов'язково: побажання, уточнення…"></textarea>
                        </label>
                    </div>
                </div>

                <aside class="cart-summary-card">
                    <h2 class="cart-card__title">Ваше замовлення</h2>
                    <div class="cart-totals">
                        <div class="cart-summary__row">
                            <span>Товари (<span x-text="$store.cart.count"></span>)</span>
                            <b><span x-text="$store.cart.total.toLocaleString('uk-UA')"></span> грн</b>
                        </div>
                        <div class="cart-summary__row">
                            <span>Доставка</span>
                            <b x-text="deliveryCost"></b>
                        </div>
                        <div class="cart-summary__row cart-summary__total">
                            <span>Разом</span>
                            <b><span x-text="$store.cart.total.toLocaleString('uk-UA')"></span> грн</b>
                        </div>
                        <p class="cart-summary__note" x-show="$store.cart.hasInquiry" x-cloak>
                            Деякі позиції — за запитом; точну ціну підтвердимо при оформленні.
                        </p>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block" :disabled="loading">
                        <span x-show="!loading">Підтвердити замовлення</span>
                        <span x-show="loading" x-cloak>Оформлюємо…</span>
                    </button>
                    <p class="product-order__err" x-show="error" x-cloak x-text="error"></p>
                    <p class="cart-summary__hint">Без онлайн-оплати. Вартість доставки — за тарифами перевізника,
                        самовивіз безкоштовний.</p>
                </aside>
            </form>
        </div>
    </div>

    {{-- Підтвердження видалення --}}
    <div class="modal" x-show="confirm.open" x-cloak x-transition.opacity.duration.200ms
        @keydown.escape.window="cancelRemove()">
        <div class="modal__backdrop" @click="cancelRemove()"></div>
        <div class="modal__box" x-transition>
            <h3 class="modal__title">Видалити товар?</h3>
            <p class="modal__text">Прибрати «<span x-text="confirm.name"></span>» з кошика?</p>
            <div class="modal__actions">
                <button type="button" class="btn btn--outline" @click="cancelRemove()">Скасувати</button>
                <button type="button" class="btn btn--primary" @click="doRemove()">Видалити</button>
            </div>
        </div>
    </div>
</section>
@endsection
