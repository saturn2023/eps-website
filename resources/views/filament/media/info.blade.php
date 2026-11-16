@php($m = $getRecord())
@if ($m)
@php($used = $m->usages())
<dl style="display:grid;grid-template-columns:max-content 1fr;gap:4px 12px;font-size:13px;line-height:1.5;padding-bottom:14px;border-bottom:1px solid rgba(120,120,140,.25)">
    <dt style="font-weight:600">Uploaded on:</dt><dd>{{ $m->created_at->timezone(config('app.timezone'))->format('j F Y') }}</dd>
    <dt style="font-weight:600">Uploaded by:</dt><dd>{{ $m->uploader?->name ?? 'Website setup' }}</dd>
    <dt style="font-weight:600">Used on:</dt><dd>{{ $used ? implode(', ', $used) : 'Not used on any page' }}</dd>
    <dt style="font-weight:600">File name:</dt><dd style="word-break:break-all">{{ $m->name }}</dd>
    <dt style="font-weight:600">File type:</dt><dd>{{ $m->mime ?? $m->extension() }}</dd>
    <dt style="font-weight:600">File size:</dt><dd>{{ $m->humanSize() }}</dd>
    @if ($m->dimensions())
        <dt style="font-weight:600">Dimensions:</dt><dd>{{ $m->dimensions() }}</dd>
    @endif
</dl>
@endif
