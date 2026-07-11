@extends('layouts.app')

@php($showFooterCta = false)

@section('title', '404 - Сторінку не знайдено | ВЕЛИКА ШИНА')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="section">
    <div class="container" style="max-width:640px; margin:0 auto; text-align:center">
        <div style="font-family:'Montserrat',sans-serif; font-weight:800; line-height:1;
                    font-size:clamp(84px,20vw,150px); color:#d32f2f; letter-spacing:-2px">404</div>

        <h1 class="section-title" style="margin-top:6px">Такої сторінки не знайдено</h1>

        <p style="color:#6b7280; font-size:16px; line-height:1.6; margin:14px auto 26px; max-width:470px">
            Можливо, товар знято з продажу, або адресу введено з помилкою.
            Спробуйте знайти потрібну шину через пошук - за розміром чи артикулом.
        </p>

        <form action="{{ route('catalog') }}" method="GET"
              style="display:flex; gap:8px; max-width:440px; margin:0 auto 26px">
            <input type="text" name="q" placeholder="Розмір або артикул шини" autocomplete="off"
                   style="flex:1; min-width:0; padding:13px 16px; border:1px solid #e0e0e0;
                          border-radius:10px; font-size:15px" />
            <button type="submit" class="btn btn--primary">Шукати</button>
        </form>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap">
            <a href="{{ route('home') }}" class="btn btn--outline">На головну</a>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти в каталог</a>
        </div>
    </div>
</section>
@endsection
