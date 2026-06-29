<div>
    <h1>Фото «в роботі» (за технікою)</h1>

    <div class="admin-filters">
        <x-admin.select model="filterType" placeholder="— Тип техніки —" :options="$typeOptions" />
        <x-admin.select model="filterModel" placeholder="— Модель техніки —" :options="$modelOptions" />
        <button wire:click="resetFilters">Скинути</button>
    </div>

    @if($photos->count())
        <div class="field-photos">
            @foreach($photos as $fp)
                <div class="field-photo" wire:key="fp-{{ $fp->id }}">
                    @php($thumb = $fp->imageUrl('thumb'))
                    @if($thumb)
                        <a href="{{ $fp->imageUrl('large') }}" target="_blank" rel="noopener">
                            <img src="{{ $thumb }}" alt="">
                        </a>
                    @endif
                    <div class="field-photo__meta">
                        @if($fp->machineryLabel())
                            <strong>{{ $fp->machineryLabel() }}</strong>
                        @endif
                        @if($fp->caption)
                            <span>{{ $fp->caption }}</span>
                        @endif
                        @if($fp->product)
                            <a href="{{ route('admin.products.edit', $fp->product_id) }}" wire:navigate
                               style="font-size:12px; color:#d32f2f; text-decoration:none">
                                {{ $fp->product->sku ? $fp->product->sku . ' — ' : '' }}{{ \Illuminate\Support\Str::limit($fp->product->name, 40) }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:1rem">{{ $photos->links('pagination.admin') }}</div>
    @else
        <p style="color:#666; margin-top:1rem">Фото не знайдено. Додайте фото «в роботі» у картці товару (вкладка редагування).</p>
    @endif
</div>
