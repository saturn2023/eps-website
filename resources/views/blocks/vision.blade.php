{{-- Block: Statement with image — small label, large statement (bold words show in teal), image beside --}}
@use('App\Models\PageContent')
@php $image = PageContent::url($data['image'] ?? null); @endphp
<section style="padding:80px 0 72px">
<div class="wrap two-col" style="display:grid;grid-template-columns:{{ $image ? 'minmax(0,7fr) minmax(0,5fr)' : 'minmax(0,1fr)' }};gap:64px;align-items:center">
<div>
@if (! empty($data['label']))
<h2 style="margin:0 0 18px;font-size:16px;font-weight:600;color:#00666D">{{ $data['label'] }}</h2>
@endif
<div class="lead-big" style="margin:0;font-size:28px;line-height:1.45;font-weight:400;color:#032855">{!! str($data['statement'] ?? '')->sanitizeHtml() !!}</div>
</div>
@if ($image)
<img src="{{ $image }}" alt="{{ $data['image_alt'] ?? '' }}" style="width:100%;max-width:560px;height:auto;justify-self:center">
@endif
</div>
</section>
