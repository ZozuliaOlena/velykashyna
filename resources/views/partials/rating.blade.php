{{-- resources/views/partials/rating.blade.php — оцінка клієнтів (зірки) --}}
@php($r = config('site.rating'))
<div class="rating">
    <div class="rating-stars" aria-label="{{ $r['score'] }} з {{ $r['max'] }}">
        @for ($i = 0; $i < (int) $r['max']; $i++)
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l2.95 6.36 6.99.77-5.2 4.7 1.43 6.87L12 17.9l-6.17 2.8 1.43-6.87-5.2-4.7 6.99-.77z" />
            </svg>
        @endfor
    </div>
    <span class="rating-text"><b>{{ $r['score'] }} з {{ $r['max'] }}</b> — оцінка клієнтів</span>
</div>
