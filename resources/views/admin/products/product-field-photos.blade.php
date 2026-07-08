<div>
    <details class="collapse-block" wire:ignore.self>
        <summary>
            Фото «в роботі» (застосування)
            @if($photos->count())<span class="collapse-block__count">{{ $photos->count() }}</span>@endif
        </summary>
        <div class="collapse-block__body">
        <p style="color:#666">
            Реальні фото встановлених/інспектованих шин на техніці. Прив'яжіть до моделі
            (напр. CASE 310) і додайте підпис — потім їх можна переглянути за технікою.
        </p>

        @if($photos->count())
            <div class="field-photos">
                @foreach($photos as $fp)
                    <div class="field-photo" wire:key="fp-{{ $fp->id }}">
                        @php($thumb = $fp->imageUrl('thumb'))
                        @if($thumb)<img src="{{ $thumb }}" alt="">@endif
                        <button type="button" class="photo-del" wire:click="delete({{ $fp->id }})"
                            data-confirm="Ви дійсно хочете видалити це фото?">×</button>
                        <div class="field-photo__meta">
                            @if($fp->machineryLabel())
                                <strong>{{ $fp->machineryLabel() }}</strong>
                            @endif
                            @if($fp->caption)
                                <span>{{ $fp->caption }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="field-photo-add">
            <div class="is-full">
                <label>Фото *</label>
                <x-admin.image-upload model="photo" />
                @error('photo') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:.5rem">
                <div style="flex:1 1 200px">
                    <label>Тип техніки</label>
                    <x-admin.select model="machinery_type_id" :live="true" placeholder="— Тип техніки —" :options="$typeOptions" />
                </div>
                <div style="flex:1 1 200px">
                    <label>Модель техніки</label>
                    <x-admin.select model="machinery_model_id" :live="false"
                        wire:key="fp-model-{{ $machinery_type_id ?? 0 }}"
                        placeholder="— Модель (напр. CASE 310) —" :options="$modelOptions" />
                </div>
            </div>

            <div class="is-full" style="margin-top:.5rem">
                <label>Підпис</label>
                <input wire:model="caption" type="text" style="width:100%"
                    placeholder="Напр.: 2 роки в роботі, 3000 мотогодин">
                @error('caption') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top:.75rem">
                <button type="button" class="btn-primary" wire:click="add"
                    wire:loading.attr="disabled" wire:target="add,photo">Додати фото</button>
                <span wire:loading wire:target="add" style="color:#666">Збереження…</span>
            </div>
        </div>
        </div>
    </details>
</div>
