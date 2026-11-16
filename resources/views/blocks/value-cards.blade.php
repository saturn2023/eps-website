{{-- Block: Value cards — coloured cards with a big letter, character image, title and line of text --}}
@use('App\Models\PageContent')
@php
    // Colour the card's letters inside its title, in order: "AL" in "ACTIVE LEARNER" -> A and L.
    $highlight = function (string $title, string $letters, string $colour): string {
        $out = '';
        $pos = 0;
        foreach (mb_str_split(mb_strtoupper($letters)) as $letter) {
            $found = mb_stripos($title, $letter, $pos);
            if ($found === false) continue;
            $out .= e(mb_substr($title, $pos, $found - $pos))
                . '<span style="color:' . e($colour) . '">' . e(mb_substr($title, $found, 1)) . '</span>';
            $pos = $found + 1;
        }
        return $out . e(mb_substr($title, $pos));
    };
    $cards = $data['cards'] ?? [];
@endphp
<section style="background:#EEF6F8;padding:80px 0 88px">
<div class="wrap">
@if (! empty($data['heading']))
<h2 style="margin:0 0 10px;font-weight:600;font-size:36px;line-height:1.15;letter-spacing:-0.01em;color:#032855">{{ $data['heading'] }}</h2>
@endif
@if (! empty($data['intro']))
<p style="margin:0 0 36px;max-width:640px">{{ $data['intro'] }}</p>
@endif
<ul class="values-grid" style="list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat({{ max(1, min(count($cards), 3)) }},minmax(0,1fr));gap:28px">
@foreach ($cards as $card)
@php $colour = $card['colour'] ?? '#032855'; $img = PageContent::url($card['image'] ?? null); @endphp
<li class="value" style="border-top:8px solid {{ $colour }}">
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
<span aria-hidden="true" style="font-weight:700;font-size:64px;line-height:1;color:{{ $colour }}">{{ $card['letters'] ?? '' }}</span>
@if ($img)<img src="{{ $img }}" alt="{{ ($card['image_alt'] ?? null) ?: \App\Models\Media::altFor($card['image'] ?? null) }}" style="width:110px;height:auto">@endif
</div>
<h3 style="margin:0;font-weight:600;font-size:26px;letter-spacing:.02em;color:#032855">{!! $highlight(mb_strtoupper($card['title'] ?? ''), $card['letters'] ?? '', $colour) !!}</h3>
@if (! empty($card['text']))<p style="margin:0;font-size:18px;font-weight:400">{{ $card['text'] }}</p>@endif
</li>
@endforeach
</ul>
</div>
</section>
