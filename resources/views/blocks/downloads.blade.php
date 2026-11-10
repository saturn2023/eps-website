{{-- Block: Downloads — a list of files (PDFs etc) or web links --}}
<div class="blk">
@if (! empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
@if (! empty($data['intro']))<p>{{ $data['intro'] }}</p>@endif
@include('blocks.partials.download-list', ['files' => $data['files'] ?? [], 'grid' => ! empty($data['two_columns'])])
</div>
