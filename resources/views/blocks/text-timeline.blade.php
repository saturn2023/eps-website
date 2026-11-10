{{-- Block: Text with timeline — rich text on the left, vertical timeline card on the right --}}
<section aria-label="{{ $data['timeline_heading'] ?? 'Timeline' }}" style="padding:80px 0">
<div class="wrap two-col" style="display:grid;grid-template-columns:minmax(0,7fr) minmax(0,5fr);gap:72px;align-items:start">
<div class="prose">{!! str($data['body'] ?? '')->sanitizeHtml() !!}</div>
@if (! empty($data['milestones']))
<aside style="background:#EEF6F8;border-radius:24px;padding:36px 32px">
@if (! empty($data['timeline_heading']))
<h2 style="margin:0 0 26px;font-weight:600;font-size:22px;color:#032855">{{ $data['timeline_heading'] }}</h2>
@endif
<ol class="vt">
@foreach ($data['milestones'] as $m)
<li>@include('blocks.partials.ring', ['teal' => ! empty($m['highlight'])])<div style="padding-top:6px"><p style="margin:0 0 4px;font-weight:600;font-size:24px;color:#032855;line-height:1.1">{{ $m['year'] ?? '' }}</p><p style="margin:0">{{ $m['text'] ?? '' }}</p></div></li>
@endforeach
</ol>
</aside>
@endif
</div>
</section>
