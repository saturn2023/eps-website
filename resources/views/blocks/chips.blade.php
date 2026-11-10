{{-- Block: Tags — a row of rounded tags, e.g. specialist programs --}}
<div class="blk">
<ul class="chips-list">
@foreach ($data['chips'] ?? [] as $chip)<li class="chip"><i></i>{{ $chip }}</li>@endforeach
</ul>
</div>
