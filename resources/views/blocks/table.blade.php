{{-- Block: Table — caption, optional header row, rows whose first cell is the row label --}}
@php $headers = array_values(array_filter($data['headers'] ?? [], 'filled')); @endphp
<div class="blk"><div class="tbl-wrap"><table class="tbl">
@if (! empty($data['caption']))<caption>{{ $data['caption'] }}</caption>@endif
@if ($headers)
<thead><tr>@foreach ($headers as $h)<th scope="col">{{ $h }}</th>@endforeach</tr></thead>
@endif
<tbody>
@foreach ($data['rows'] ?? [] as $row)
@php $cells = array_values(is_array($row) && array_key_exists('cells', $row) ? ($row['cells'] ?? []) : (array) $row); @endphp
<tr>@foreach ($cells as $i => $cell)@if ($i === 0)<th scope="row">{{ $cell }}</th>@else<td>{{ $cell }}</td>@endif @endforeach</tr>
@endforeach
</tbody>
</table></div></div>
