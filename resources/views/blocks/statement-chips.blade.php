{{-- Block: Centred statement with chips (e.g. "Our goal") --}}
<section style="background:#EEF6F8;padding:80px 0">
<div class="wrap" style="max-width:1000px;text-align:center">
@if (! empty($data['heading']))
<h2 style="margin:0 0 18px;font-weight:600;font-size:36px;line-height:1.15;letter-spacing:-0.01em;color:#032855">{{ $data['heading'] }}</h2>
@endif
@if (! empty($data['text']))
<p style="margin:0 auto 32px;max-width:820px;font-size:19px">{{ $data['text'] }}</p>
@endif
@if (! empty($data['chips']))
<ul style="list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;justify-content:center;gap:12px">
@foreach ($data['chips'] as $chip)<li class="chip"><i></i>{{ $chip }}</li>@endforeach
</ul>
@endif
</div>
</section>
