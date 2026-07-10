@php($galMin = \App\Livewire\Admin\SiteSettings\SiteContent::GALLERY_MIN)
@php($galMax = \App\Livewire\Admin\SiteSettings\SiteContent::GALLERY_MAX)
<div>
    <h1>Контент сайту</h1>
    <p style="color:#666; margin:.25rem 0 1.25rem">
        Тут керуєте блоками сайту: галерея «ВЕЛИКА ШИНА в роботі», часті запитання (FAQ),
        а також способи доставки та оплати у кошику.
    </p>

    <fieldset style="margin:0 0 1.5rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>ВЕЛИКА ШИНА в роботі (галерея)</strong></legend>
        <p style="color:#666; margin:0 0 1rem">
            Від {{ $galMin }} до {{ $galMax }} фото. Перетягувати не потрібно -
            порядок міняйте стрілками. Підпис (alt) корисний для SEO.
        </p>

        <div class="photo-grid" style="margin-bottom:1rem">
            @foreach($gallery as $i => $g)
                <div wire:key="gal-{{ $i }}" style="border:1px solid #e3e6ec; border-radius:8px; padding:8px; width:200px">
                    <div class="photo-thumb" style="width:100%; height:120px">
                        <img src="{{ $g['img'] }}" alt="" data-zoom-src="{{ $g['img'] }}">
                    </div>
                    <input type="text" wire:model="gallery.{{ $i }}.cap" placeholder="Підпис (alt)"
                        style="width:100%; margin-top:6px; font-size:13px">
                    <div style="display:flex; gap:4px; margin-top:6px">
                        <button type="button" wire:click="moveGallery({{ $i }}, 'up')" @disabled($i === 0) title="Вгору">↑</button>
                        <button type="button" wire:click="moveGallery({{ $i }}, 'down')" @disabled($i === count($gallery) - 1) title="Вниз">↓</button>
                        <button type="button" wire:click="removeGalleryItem({{ $i }})"
                            data-confirm="Прибрати це фото з галереї?" style="margin-left:auto">✕</button>
                    </div>
                </div>
            @endforeach
        </div>

        @if(count($gallery) < $galMax)
            <label class="upload-btn">
                <input type="file" wire:model="galleryUpload" accept="image/*" hidden>
                <span>+ Додати фото</span>
            </label>
            <div wire:loading wire:target="galleryUpload" style="color:#666; font-size:13px; margin-top:4px">Завантаження…</div>
        @else
            <p style="color:#888; font-size:13px">Досягнуто максимуму {{ $galMax }} фото.</p>
        @endif
        @error('galleryUpload') <span style="color:red">{{ $message }}</span> @enderror
        <p style="color:#888; font-size:13px; margin-top:.5rem">Зараз фото: <strong>{{ count($gallery) }}</strong></p>

        <div style="margin-top:1rem">
            <button wire:click="saveGallery" class="btn-primary" data-confirm="Зберегти галерею?">Зберегти галерею</button>
        </div>
    </fieldset>

    <fieldset style="margin:0 0 1.5rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Часті запитання (FAQ)</strong></legend>

        @forelse($faqs as $i => $f)
            <div wire:key="faq-{{ $i }}" style="border-bottom:1px dashed #eee; padding-bottom:.75rem; margin-bottom:.75rem">
                <div style="display:flex; gap:8px; align-items:flex-start">
                    <div style="flex:1">
                        <input type="text" wire:model="faqs.{{ $i }}.q" placeholder="Запитання" style="width:100%; font-weight:600">
                        <textarea wire:model="faqs.{{ $i }}.a" rows="2" placeholder="Відповідь" style="width:100%; margin-top:6px"></textarea>
                    </div>
                    <button type="button" wire:click="removeFaq({{ $i }})" data-confirm="Видалити це запитання?">✕</button>
                </div>
            </div>
        @empty
            <p style="color:#999">Питань поки немає.</p>
        @endforelse

        <button type="button" wire:click="addFaq">+ Додати запитання</button>

        <div style="margin-top:1rem">
            <button wire:click="saveFaqs" class="btn-primary" data-confirm="Зберегти часті запитання?">Зберегти запитання</button>
        </div>
    </fieldset>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem">
        <fieldset style="padding:16px; border:1px solid #e3e6ec; border-radius:10px">
            <legend style="padding:0 8px"><strong>Способи доставки</strong></legend>
            <p style="color:#666; margin:0 0 .75rem; font-size:13px">Випадний список у кошику.</p>

            @foreach($delivery as $i => $d)
                <div wire:key="del-{{ $i }}" style="display:flex; gap:6px; margin-bottom:6px">
                    <input type="text" wire:model="delivery.{{ $i }}" style="flex:1">
                    <button type="button" wire:click="removeDelivery({{ $i }})" data-confirm="Прибрати спосіб доставки?">✕</button>
                </div>
            @endforeach
            <button type="button" wire:click="addDelivery" style="margin-top:.25rem">+ Додати</button>

            <div style="margin-top:1rem">
                <button wire:click="saveDelivery" class="btn-primary" data-confirm="Зберегти способи доставки?">Зберегти доставку</button>
            </div>
        </fieldset>

        <fieldset style="padding:16px; border:1px solid #e3e6ec; border-radius:10px">
            <legend style="padding:0 8px"><strong>Способи оплати</strong></legend>
            <p style="color:#666; margin:0 0 .75rem; font-size:13px">Випадний список у кошику.</p>

            @foreach($payment as $i => $p)
                <div wire:key="pay-{{ $i }}" style="display:flex; gap:6px; margin-bottom:6px">
                    <input type="text" wire:model="payment.{{ $i }}" style="flex:1">
                    <button type="button" wire:click="removePayment({{ $i }})" data-confirm="Прибрати спосіб оплати?">✕</button>
                </div>
            @endforeach
            <button type="button" wire:click="addPayment" style="margin-top:.25rem">+ Додати</button>

            <div style="margin-top:1rem">
                <button wire:click="savePayment" class="btn-primary" data-confirm="Зберегти способи оплати?">Зберегти оплату</button>
            </div>
        </fieldset>
    </div>
</div>
