@foreach ($cards as $card)
<div class="fav-item" x-show="$store.fav.has({{ $card['id'] }})" x-transition.opacity>
    @include('partials.product-card', ['p' => $card, 'showCountry' => false, 'favConfirm' => true])
</div>
@endforeach
