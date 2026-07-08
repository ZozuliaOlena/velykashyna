{{-- resources/views/web/pages/delivery.blade.php --}}
@extends('layouts.app')

@section('title', 'Доставка й оплата — ВЕЛИКА ШИНА')
@section('meta_description', 'Способи доставки (Нова Пошта, САТ, самовивіз) та оплати (післяплата, оплата за реквізитами) у компанії ВЕЛИКА ШИНА.')

@php($c = config('site.contacts'))

@section('content')
<section class="section info-page">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Доставка й оплата</span>
        </nav>

        <div class="info-page__wrap">
            <h1 class="info-page__title">Доставка й оплата</h1>

            <div class="info-page__body">
                <h2>Способи доставки</h2>
                <ul>
                    <li><b>Нова Пошта</b> — по всій Україні, за тарифами перевізника.</li>
                    <li><b>САТ</b> — доставка вантажів і великогабаритних шин, за тарифами перевізника.</li>
                    <li><b>Самовивіз</b> — <span class="info-free">безкоштовно</span> зі складу в Києві.</li>
                </ul>

                <h2>Способи оплати</h2>
                <ul>
                    <li><b>Післяплата</b> — оплата при отриманні товару.</li>
                    <li><b>Оплата за реквізитами</b> — безготівковий розрахунок за рахунком.</li>
                </ul>

                <p>Точні терміни, вартість доставки та зручний спосіб оплати уточнюйте у менеджера:
                    <a href="tel:{{ $c['phone_href'] }}">{{ $c['phone'] }}</a>,
                    <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
