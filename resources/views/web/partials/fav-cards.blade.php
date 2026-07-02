{{-- Картки обраного — реальні каталожні картки (partials/product-card).
     Кожну обгортаємо x-show, щоб при знятті «сердечка» вона зникала зі списку. --}}
@foreach ($cards as $card)
<div class="fav-item" x-show="$store.fav.has({{ $card['id'] }})" x-transition.opacity>
    @include('partials.product-card', ['p' => $card, 'showCountry' => false, 'favConfirm' => true])
</div>
@endforeach
