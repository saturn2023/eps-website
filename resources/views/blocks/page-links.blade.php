{{-- Block: Page links grid — icon tiles for every published page in each Parent Hub group (automatic) --}}
@use('App\Support\Navigation')
<div class="blk">
@foreach (Navigation::GROUPS as $groupKey => $groupLabel)
@php $links = Navigation::groupPages($groupKey); @endphp
@if ($links->isNotEmpty())
<div class="hub-group"><h2>{{ $groupLabel }}</h2><ul class="hub-grid">
@foreach ($links as $link)
<li><a href="{{ $link->url() }}">@if ($link->iconUrl())<img src="{{ $link->iconUrl() }}" alt="">@endif{{ $link->title }}</a></li>
@endforeach
</ul></div>
@endif
@endforeach
</div>
