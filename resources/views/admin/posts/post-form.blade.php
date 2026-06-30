<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>{{ $postId ? 'Редагувати статтю' : 'Нова стаття' }}</h1>
        <a href="{{ route('admin.posts.index') }}" wire:navigate>← До списку</a>
    </div>

    <form wire:submit="save" style="max-width:760px" data-dirty-guard>
        {{-- ── Основне ─────────────────────────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>Основне</strong></legend>

            <div class="is-full">
                <label>Заголовок *</label>
                <input wire:model="title" type="text" style="width:100%">
                @error('title') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div class="is-full">
                <label>URL (slug)</label>
                <input wire:model="slug" type="text" style="width:100%" placeholder="Згенерується із заголовка">
                @error('slug') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div class="is-full">
                <label>Короткий опис (для списку)</label>
                <textarea wire:model="excerpt" rows="2" style="width:100%"></textarea>
                @error('excerpt') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </fieldset>

        {{-- ── Головне фото ────────────────────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>Головне фото</strong></legend>

            @if($currentImage)
                <div class="photo-thumb">
                    <img src="{{ $currentImage }}" alt="">
                    <button type="button" class="photo-del" wire:click="deleteImage"
                        data-confirm="Ви дійсно хочете видалити фото?">×</button>
                </div>
            @endif

            <x-admin.image-upload model="image" />
            @error('image') <span style="color:red">{{ $message }}</span> @enderror
            @unless($postId)
                <p style="color:#666">Фото завантажиться разом зі збереженням статті.</p>
            @endunless
        </fieldset>

        {{-- ── Текст статті (редактор Trix) ────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>Текст статті</strong></legend>
            <div class="is-full"
                 wire:ignore
                 x-data
                 x-init="
                    $nextTick(() => {
                        const editor = $refs.trix;
                        editor.addEventListener('trix-change', () => {
                            $wire.set('content', editor.value, false);
                        });
                    })
                 ">
                {{-- Початковий вміст (HTML) для редактора. --}}
                <input id="post-content" type="hidden" value="{{ $content }}">
                <trix-editor x-ref="trix" input="post-content"
                    data-upload-url="{{ route('admin.posts.upload-image') }}"
                    class="trix-content"></trix-editor>
                <small style="color:#666">
                    Жирний, курсив, заголовки, списки, посилання та фото — на панелі редактора.
                    Щоб вставити фото — натисніть значок скріпки або перетягніть зображення в текст.
                </small>
            </div>
            @error('content') <span style="color:red">{{ $message }}</span> @enderror
        </fieldset>

        {{-- ── Публікація ──────────────────────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>Публікація</strong></legend>
            <div style="display:flex; gap:1.5rem; flex-wrap:wrap; align-items:flex-end">
                <label style="display:flex; align-items:center; gap:8px; margin:0">
                    <input wire:model="is_published" type="checkbox"> Опубліковано
                </label>
                <div>
                    <label>Дата публікації</label><br>
                    <input wire:model="published_at" type="datetime-local">
                </div>
            </div>
            <small style="color:#666; display:block; margin-top:.5rem">Якщо дату не вказано — підставиться момент першої публікації.</small>
        </fieldset>

        {{-- ── SEO ─────────────────────────────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>SEO</strong></legend>
            <div class="is-full">
                <label>SEO Title</label>
                <input wire:model="seo_title" type="text" style="width:100%">
            </div>
            <div class="is-full">
                <label>SEO Description</label>
                <textarea wire:model="seo_description" rows="2" style="width:100%"></textarea>
            </div>
        </fieldset>

        <div style="margin:1.5rem 0">
            <button type="submit" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <a href="{{ route('admin.posts.index') }}" wire:navigate>Скасувати</a>
        </div>
    </form>
</div>
