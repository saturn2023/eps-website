@php($m = $getRecord())
@if ($m)
<div style="display:flex;align-items:center;justify-content:center;min-height:320px;height:100%;border-radius:10px;background:rgba(120,120,140,.1);padding:16px">
    @switch($m->kind())
        @case('image')
            <img src="{{ $m->url() }}" alt="{{ $m->alt }}" style="max-width:100%;max-height:68vh;height:auto;display:block;border-radius:4px">
            @break
        @case('video')
            <video src="{{ $m->url() }}" controls preload="metadata" style="max-width:100%;max-height:68vh;display:block"></video>
            @break
        @default
            @if ($m->extension() === 'PDF')
                <iframe src="{{ $m->url() }}" title="{{ $m->name }}" style="width:100%;height:68vh;border:0;border-radius:4px;background:#fff"></iframe>
            @else
                <div style="text-align:center">
                    <span style="font-size:22px;font-weight:700;padding:6px 18px;border-radius:6px;background:#032855;color:#fff">{{ $m->extension() }}</span>
                    <p style="margin-top:14px">{{ $m->name }}</p>
                </div>
            @endif
    @endswitch
</div>
@endif
