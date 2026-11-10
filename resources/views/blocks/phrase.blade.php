{{-- Block: Stand-out phrase — navy band with artwork and a large italic line --}}
@use('App\Models\PageContent')
@php $art = PageContent::url($data['artwork'] ?? null); @endphp
<section aria-label="Key message" style="background:#032855;color:#ffffff;position:relative;overflow:hidden">
<div class="wrap" style="display:flex;align-items:center;gap:56px;min-height:240px;padding-top:48px;padding-bottom:56px;flex-wrap:wrap">
@if ($art)<img src="{{ $art }}" alt="" style="width:120px;height:auto;flex:0 0 auto">@endif
<p class="phrase serif" style="margin:0;flex:1 1 520px;font-size:34px;line-height:1.35;color:#ffffff;max-width:860px">{{ $data['phrase'] ?? '' }}</p>
</div>
<div class="dotline-dark" style="position:absolute;left:0;right:0;bottom:20px;opacity:.9"></div>
</section>
