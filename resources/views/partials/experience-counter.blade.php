@php($years = now()->year - config('site.founded_year'))
<section class="exp-counter">
    <div class="container">
        <div class="exp-counter__grid" x-data="experienceStats('{{ config('site.founded_date') }}')"
            data-aos="fade-up">
            <div class="exp-item">
                <span class="exp-num"><span x-text="years">{{ $years }}</span> років</span>
                <span class="exp-cap">Підбираємо правильні шини</span>
            </div>
            <div class="exp-item">
                <span class="exp-num"><span x-text="days">0</span> днів</span>
                <span class="exp-cap">Допомагаємо клієнтам</span>
            </div>
            <div class="exp-item">
                <span class="exp-num"><span x-text="hours">0</span> годин</span>
                <span class="exp-cap">Знаходимо правильні рішення</span>
            </div>
        </div>
    </div>
</section>
