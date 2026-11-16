@php($m = $getRecord())
<div style="aspect-ratio:1;width:100%;border-radius:8px;overflow:hidden;background:rgba(120,120,140,.1);border:1px solid rgba(120,120,140,.2)">
    @if ($m->isImage())
        <img src="{{ $m->url() }}" alt="{{ $m->alt }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block">
    @else
        <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:10px;text-align:center">
            <span style="font-size:13px;font-weight:700;padding:3px 10px;border-radius:4px;background:{{ $m->kind() === 'video' ? '#01ADB6' : '#032855' }};color:#fff">{{ $m->extension() }}</span>
            <span style="font-size:11px;line-height:1.3;opacity:.8;word-break:break-word">{{ Str::limit($m->title ?: $m->name, 50) }}</span>
        </div>
    @endif
</div>
