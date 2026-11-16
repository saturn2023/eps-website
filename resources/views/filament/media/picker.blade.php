@php
    use App\Models\Media;

    $items = Media::ofKind($kind ?? null)->latest()->latest('id')->get()->map(fn (Media $m) => [
        'id' => $m->id,
        'url' => $m->url(),
        'kind' => $m->kind(),
        'ext' => $m->extension(),
        'name' => $m->title ?: $m->name,
        'file' => $m->name,
        'meta' => collect([$m->width ? "{$m->width} × {$m->height}" : null, $m->humanSize()])->filter()->join(' · '),
        'alt' => $m->alt,
        'search' => Str::lower(implode(' ', [$m->name, $m->title, $m->alt, $m->caption])),
    ])->values();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: $wire.$entangle(@js($getStatePath())),
            items: @js($items),
            q: '',
            get shown() {
                const q = this.q.trim().toLowerCase();
                return q ? this.items.filter(i => i.search.includes(q)) : this.items;
            },
            get chosen() { return this.items.find(i => i.id == this.state) },
        }"
        class="eps-picker"
    >
        <style>
            .eps-picker-top{display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:12px}
            .eps-picker-top input{min-width:260px;flex:0 1 340px;padding:8px 12px;border-radius:8px;border:1px solid rgba(120,120,140,.35);background:transparent;color:inherit}
            .eps-picker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;max-height:58vh;overflow:auto;padding:4px}
            .eps-picker-item{position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid rgba(120,120,140,.25);background:rgba(120,120,140,.08);padding:0;display:block;width:100%}
            .eps-picker-item img{width:100%;height:100%;object-fit:cover;display:block}
            .eps-picker-item[aria-pressed=true]{outline:4px solid #01ADB6;outline-offset:-4px}
            .eps-picker-item .tick{position:absolute;top:6px;right:6px;width:24px;height:24px;border-radius:50%;background:#01ADB6;color:#fff;display:grid;place-items:center;font-size:14px;font-weight:700}
            .eps-picker-file{height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:8px;text-align:center;font-size:12px;line-height:1.3;word-break:break-word}
            .eps-picker-file b{font-size:13px;padding:2px 8px;border-radius:4px;background:#032855;color:#fff}
            .eps-picker-chosen{margin-top:12px;font-size:13px;opacity:.85}
        </style>

        <div class="eps-picker-top">
            <input type="search" x-model="q" placeholder="Search by name, title or alt text" aria-label="Search the media library">
            <span style="font-size:13px;opacity:.75" x-text="shown.length + ' of ' + items.length + ' files'"></span>
        </div>

        <template x-if="items.length === 0">
            <p style="padding:24px 0;opacity:.8">The library has no files of this type yet. Close this and upload a file instead.</p>
        </template>

        <div class="eps-picker-grid" role="listbox" aria-label="Media Library files">
            <template x-for="item in shown" :key="item.id">
                <button type="button" class="eps-picker-item" :aria-pressed="state == item.id" :title="item.file"
                        @click="state = item.id" @dblclick="state = item.id; $nextTick(() => $el.closest('form')?.requestSubmit())">
                    <template x-if="item.kind === 'image'">
                        <img :src="item.url" :alt="item.alt || ''" loading="lazy">
                    </template>
                    <template x-if="item.kind !== 'image'">
                        <span class="eps-picker-file"><b x-text="item.ext"></b><span x-text="item.name"></span></span>
                    </template>
                    <span class="tick" x-show="state == item.id">✓</span>
                </button>
            </template>
        </div>

        <p class="eps-picker-chosen" x-show="chosen">
            Selected: <strong x-text="chosen?.file"></strong> <span x-text="chosen ? '(' + chosen.meta + ')' : ''"></span>.
            Double-click a file to use it straight away.
        </p>
    </div>
</x-dynamic-component>
