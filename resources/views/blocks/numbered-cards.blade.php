{{-- Block: Numbered cards — e.g. "Our school givens" --}}
<section style="padding:88px 0 24px">
<div class="wrap">
@if (! empty($data['heading']))
<h2 style="margin:0 0 10px;font-weight:600;font-size:36px;line-height:1.15;letter-spacing:-0.01em;color:#032855">{{ $data['heading'] }}</h2>
@endif
@if (! empty($data['intro']))
<p style="margin:0 0 36px;max-width:720px">{{ $data['intro'] }}</p>
@endif
<ol class="givens-grid" style="list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px">
@foreach ($data['items'] ?? [] as $i => $item)
<li class="given">
<div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">@include('blocks.partials.ring', ['number' => $loop->iteration])<h3 style="margin:0;font-weight:600;font-size:21px;line-height:1.25;color:#032855">{{ $item['title'] ?? '' }}</h3></div>
@if (! empty($item['summary']))<p style="margin:0 0 12px;font-weight:400;color:#10233F">{{ $item['summary'] }}</p>@endif
@if (! empty($item['points']))
<ul>@foreach ($item['points'] as $point)<li>{{ is_array($point) ? ($point['text'] ?? '') : $point }}</li>@endforeach</ul>
@endif
</li>
@endforeach
</ol>
</div>
</section>
