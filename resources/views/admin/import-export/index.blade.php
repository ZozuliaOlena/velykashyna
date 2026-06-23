<div>
    <h1>Імпорт / Експорт</h1>

    {{-- ── Експорт ─────────────────────────────────────────── --}}
    <fieldset style="margin-top:1rem">
        <legend><strong>Експорт</strong></legend>
        <p style="color:#666">Повне вивантаження всіх товарів з характеристиками, категоріями та сумісністю (3 листи Excel).</p>
        <button wire:click="export" class="btn-primary">Завантажити Excel</button>
    </fieldset>

    {{-- ── Імпорт товарів ──────────────────────────────────── --}}
    <fieldset style="margin-top:1rem">
        <legend><strong>Імпорт каталогу</strong></legend>
        <p style="color:#666">
            Файл Excel/CSV з листами: <strong>Товари</strong> / <strong>Категорії</strong> / <strong>Сумісність</strong>.
            Оновлюються лише ті колонки, що присутні у файлі (зручно для масової зміни цін або SEO).
            Прив'язка за артикулом.
        </p>

        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv">
        <div wire:loading wire:target="importFile" style="color:#666">Завантаження…</div>
        @error('importFile') <span style="color:red">{{ $message }}</span> @enderror

        <div style="margin-top:.5rem">
            <button wire:click="import" wire:loading.attr="disabled" wire:target="import">Імпортувати</button>
            <span wire:loading wire:target="import" style="color:#666">Обробка…</span>
        </div>

        @if($importReport)
            <div style="margin-top:1rem; background:#f8f9fa; padding:12px; border-radius:8px">
                <strong>Результат імпорту:</strong>
                <ul>
                    <li>Створено товарів: {{ $importReport['products_created'] ?? 0 }}</li>
                    <li>Оновлено товарів: {{ $importReport['products_updated'] ?? 0 }}</li>
                    <li>Категорій оброблено: {{ $importReport['categories_touched'] ?? 0 }}</li>
                    <li>Записів сумісності: {{ $importReport['compat_created'] ?? 0 }}</li>
                </ul>
                @if(!empty($importReport['errors']))
                    <strong style="color:#d32f2f">Помилки ({{ count($importReport['errors']) }}):</strong>
                    <ul>
                        @foreach(array_slice($importReport['errors'], 0, 50) as $err)
                            <li style="color:#d32f2f">{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </fieldset>

    {{-- ── Фото архівом ────────────────────────────────────── --}}
    <fieldset style="margin-top:1rem">
        <legend><strong>Фото архівом (ZIP)</strong></legend>
        <p style="color:#666">
            Імена файлів = артикул: <code>036898.jpg</code> — основне фото,
            <code>036898_2.jpg</code> — додаткове. Розмір/пропорція уніфікуються автоматично.
        </p>

        <input type="file" wire:model="photoArchive" accept=".zip">
        <div wire:loading wire:target="photoArchive" style="color:#666">Завантаження…</div>
        @error('photoArchive') <span style="color:red">{{ $message }}</span> @enderror

        <div style="margin-top:.5rem">
            <button wire:click="uploadPhotos" wire:loading.attr="disabled" wire:target="uploadPhotos">Завантажити фото</button>
            <span wire:loading wire:target="uploadPhotos" style="color:#666">Обробка…</span>
        </div>

        @if($photoReport)
            <div style="margin-top:1rem; background:#f8f9fa; padding:12px; border-radius:8px">
                <strong>Результат:</strong>
                <ul>
                    <li>Основних фото: {{ $photoReport['main'] ?? 0 }}</li>
                    <li>Додаткових фото: {{ $photoReport['gallery'] ?? 0 }}</li>
                    <li>Пропущено (немає товару): {{ $photoReport['skipped'] ?? 0 }}</li>
                </ul>
                @if(!empty($photoReport['errors']))
                    <ul>
                        @foreach($photoReport['errors'] as $err)
                            <li style="color:#d32f2f">{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </fieldset>
</div>
