<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Слайдер головної сторінки</h1>
        <button wire:click="openCreate">+ Додати слайд</button>
    </div>

    <p style="color:#666; margin:.25rem 0 1rem">
        Слайдер у шапці головної сторінки. Для кожного слайда - заголовок, опис і фон
        (фото, завантажене відео або відео з YouTube). Порядок задається стрілками.
    </p>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr>
                <th style="width:90px">Порядок</th>
                <th style="width:120px">Фон</th>
                <th>Заголовок</th>
                <th>Опис</th>
                <th style="width:110px">Тип фону</th>
                <th style="width:90px">Активний</th>
                <th style="width:110px">Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($slides as $slide)
            <tr wire:key="slide-{{ $slide->id }}">
                <td data-label="Порядок">
                    <div style="display:flex; align-items:center; gap:6px">
                        <button class="icon-btn" wire:click="move({{ $slide->id }}, 'up')" title="Вгору" aria-label="Вгору">↑</button>
                        <button class="icon-btn" wire:click="move({{ $slide->id }}, 'down')" title="Вниз" aria-label="Вниз">↓</button>
                    </div>
                </td>
                <td data-label="Фон">
                    @if($slide->bg_type === 'youtube' && $slide->youtubeId())
                        <img src="https://img.youtube.com/vi/{{ $slide->youtubeId() }}/hqdefault.jpg"
                            data-zoom data-zoom-src="https://img.youtube.com/vi/{{ $slide->youtubeId() }}/maxresdefault.jpg"
                            alt="" style="width:100px; height:56px; object-fit:cover; border-radius:6px">
                    @elseif($slide->bg_type === 'video' && $slide->bgUrl())
                        <video src="{{ $slide->bgUrl() }}" muted preload="metadata" style="width:100px; height:56px; object-fit:cover; border-radius:6px"></video>
                    @elseif($slide->bgUrl())
                        <img src="{{ $slide->bgUrl() }}" alt="" data-zoom style="width:100px; height:56px; object-fit:cover; border-radius:6px">
                    @else
                        <span style="color:#bbb">-</span>
                    @endif
                </td>
                <td data-label="Заголовок"><strong>{{ $slide->title ?: '-' }}</strong></td>
                <td data-label="Опис">{{ $slide->subtitle ?: '-' }}</td>
                <td data-label="Тип фону">{{ $bgTypes[$slide->bg_type] ?? $slide->bg_type }}</td>
                <td data-label="Активний">
                    <button wire:click="toggleActive({{ $slide->id }})"
                        class="row-toggle {{ $slide->is_active ? 'is-on' : 'is-off' }}">
                        {{ $slide->is_active ? 'Так' : 'Ні' }}
                    </button>
                </td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit({{ $slide->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $slide->id }})"
                        data-confirm="Ви дійсно хочете видалити слайд?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center">Слайдів ще немає. Додайте перший.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати слайд' : 'Новий слайд'">
        <div class="is-full">
            <label>Заголовок</label>
            <input wire:model="title" type="text" style="width:100%" placeholder="Напр.: ВЕЛИКА ДОВІРА">
            @error('title') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div class="is-full">
            <label>Опис</label>
            <input wire:model="subtitle" type="text" style="width:100%" placeholder="Напр.: 20 років досвіду">
            @error('subtitle') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Тип фону</label>
            <select wire:model.live="bg_type" style="width:100%">
                @foreach($bgTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Порядок</label>
            <input wire:model="sort_order" type="number" min="0" style="width:100%">
        </div>

        @if($bg_type === 'youtube')
            <div class="is-full">
                <label>Посилання на YouTube</label>
                <input wire:model="youtube_url" type="text" style="width:100%"
                    placeholder="https://www.youtube.com/watch?v=...">
                @error('youtube_url') <span style="color:red">{{ $message }}</span> @enderror
                <small style="color:#666">Відео відтворюється у фоні без звуку, зациклено.</small>
            </div>
        @else
            <div class="is-full">
                <label>{{ $bg_type === 'video' ? 'Відео-файл (mp4/webm)' : 'Фото' }}</label><br>

                @if($currentBg)
                    <div class="photo-thumb" style="width:160px; height:90px">
                        @if($bg_type === 'video')
                            <video src="/storage/{{ ltrim($currentBg, '/') }}" muted controls preload="metadata" style="width:100%; height:100%; object-fit:cover"></video>
                        @else
                            <img src="/storage/{{ ltrim($currentBg, '/') }}" alt="" style="width:100%; height:100%; object-fit:cover">
                        @endif
                        <button type="button" class="photo-del" wire:click="deleteBg({{ $editingId }})"
                            data-confirm="Видалити фон?">×</button>
                    </div>
                @endif

                <input wire:model="bg" type="file"
                    accept="{{ $bg_type === 'video' ? 'video/*' : 'image/*' }}">
                <div wire:loading wire:target="bg" style="color:#666">Завантаження…</div>
                @error('bg') <span style="color:red">{{ $message }}</span> @enderror
                <small style="color:#666; display:block">До 64 МБ. Для довгих відео краще YouTube.</small>
            </div>
        @endif

        <div class="is-full" style="display:flex; align-items:center">
            <label style="margin:0"><input wire:model="is_active" type="checkbox"> Активний</label>
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Зберегти слайд?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
