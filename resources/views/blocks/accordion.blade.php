{{-- Block: Accordion — click-to-open sections --}}
<div class="blk">
@if (! empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
<div class="acc">
@foreach ($data['items'] ?? [] as $item)
<details><summary>{{ $item['title'] ?? '' }}<span class="pm" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#032855" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg></span></summary><div class="acc-body">{!! str($item['body'] ?? '')->sanitizeHtml() !!}</div></details>
@endforeach
</div>
</div>
