@use('App\Models\PageContent')
@use('App\Support\Navigation')
@php
    $logo = PageContent::url(data_get($site, 'logo')) ?? asset('images/logo-blue.png');
    $menu = Navigation::menu();
    $activeSection = $currentSection ?? null;
    $here = '/' . trim(request()->path(), '/');
    $cur = fn (string $href) => $href === $here ? ' aria-current="page"' : '';
    $chevron = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#01ADB6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>';
    $chevronLg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#01ADB6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>';
    $phoneIcon = '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"></path>';
@endphp
<!-- Header -->
<header class="nav" style="background:#ffffff;border-bottom:1px solid #E1EAF0;position:relative;z-index:20">
<div class="wrap" style="display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:104px">
<a href="{{ url('/') }}" style="display:flex;align-items:center;gap:16px;text-decoration:none">
<img class="site-logo" src="{{ $logo }}" alt="{{ data_get($site, 'school_name', 'Ellenbrook Primary School') }} home" style="height:80px;width:auto;display:block">
</a>
<nav aria-label="Main">
<ul class="nav-links" style="display:flex;align-items:center;gap:14px">
@foreach ($menu as $item)
@php $on = $activeSection === $item['section'] ? ' on' : ''; @endphp
@if (! empty($item['children']))
<li class="dd"><a class="nl{{ $on }}" href="{{ url($item['href']) }}" aria-haspopup="true">{{ $item['label'] }} {!! $chevron !!}</a>
<div class="dd-panel">
@foreach ($item['children'] as $child)
<a href="{{ url($child['href']) }}"{!! $cur($child['href']) !!}>{{ $child['label'] }}</a>
@endforeach
</div></li>
@elseif (! empty($item['mega']))
<li class="has-mega"><a class="nl{{ $on }}" href="{{ url($item['href']) }}" aria-haspopup="true">{{ $item['label'] }} {!! $chevron !!}</a>
<div class="mega-panel">
<div class="wrap" style="display:grid;grid-template-columns:repeat({{ count($item['mega']) }},minmax(0,1fr)) minmax(0,1fr);gap:20px;padding-top:28px;padding-bottom:30px">
@foreach ($item['mega'] as $group => $links)
<div>
<p class="mega-head">{{ $group }}</p>
@foreach ($links as $link)
<a class="ml" href="{{ url($link['href']) }}"{!! $cur($link['href']) !!}>@if ($link['icon'])<img src="{{ $link['icon'] }}" alt="">@endif{{ $link['label'] }}</a>
@endforeach
</div>
@endforeach
<div style="background:#032855;color:#ffffff;border-radius:18px;padding:24px 22px;display:flex;flex-direction:column;gap:10px;align-self:start">
<p style="margin:0;font-weight:600;font-size:18px;line-height:1.3">Need to speak with the office?</p>
@if ($hoursLine = data_get($site, 'hours_line'))<p style="margin:0;font-size:15px;color:#DCE8F0">{{ $hoursLine }}</p>@endif
<a class="btn btn-teal" href="{{ $tel }}" style="align-self:flex-start;margin-top:6px;white-space:nowrap;padding:0 18px">{{ $phone }}</a>
<a href="{{ url($item['href']) }}" style="color:#7FE3E8;font-size:15px;margin-top:6px">See all {{ $item['label'] }} pages</a>
</div>
</div>
</div></li>
@else
<li><a class="nl{{ $on }}" href="{{ url($item['href']) }}"{!! $cur($item['href']) !!}>{{ $item['label'] }}</a></li>
@endif
@endforeach
<li style="margin-left:4px"><a class="nav-phone" href="{{ $tel }}" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:#032855;font-weight:600;font-size:18px;white-space:nowrap">
<span style="width:38px;height:38px;border-radius:50%;background:#01ADB6;display:inline-flex;align-items:center;justify-content:center"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#032855" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $phoneIcon !!}</svg></span>
{{ $phone }}</a></li>
</ul>
</nav>
<div class="m-actions" style="align-items:center;gap:10px">
<a class="m-call" href="{{ $tel }}" aria-label="Call the office on {{ $phone }}" style="width:48px;height:48px;border-radius:50%;background:#01ADB6;align-items:center;justify-content:center"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#032855" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $phoneIcon !!}</svg></a>
<button id="menu-btn" class="menu-btn btn btn-line" type="button" aria-expanded="false" aria-controls="mobile-menu" style="font:inherit;font-weight:500;cursor:pointer">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
Menu</button>
</div>
</div>
<nav id="mobile-menu" class="m-menu" aria-label="Mobile" style="background:#ffffff;border-top:4px solid #01ADB6;padding:8px 20px 28px;box-shadow:0 24px 40px rgba(3,40,85,.18)">
<a class="m-link" href="{{ url('/') }}">Home</a>
@foreach ($menu as $item)
@if (! empty($item['children']))
<details @if ($activeSection === $item['section']) open @endif><summary>{{ $item['label'] }}{!! $chevronLg !!}</summary>
<div class="m-sub" style="padding-bottom:12px">@foreach ($item['children'] as $child)<a href="{{ url($child['href']) }}"{!! $cur($child['href']) !!}>{{ $child['label'] }}</a>@endforeach</div></details>
@elseif (! empty($item['mega']))
<details @if ($activeSection === $item['section']) open @endif><summary>{{ $item['label'] }}{!! $chevronLg !!}</summary>
<div class="m-sub" style="padding-bottom:12px"><a href="{{ url($item['href']) }}">All {{ $item['label'] }} pages</a>@foreach ($item['mega'] as $group => $links)<p>{{ $group }}</p>@foreach ($links as $link)<a href="{{ url($link['href']) }}"{!! $cur($link['href']) !!}>{{ $link['label'] }}</a>@endforeach @endforeach</div></details>
@else
<a class="m-link" href="{{ url($item['href']) }}">{{ $item['label'] }}</a>
@endif
@endforeach
<div style="display:flex;flex-direction:column;gap:12px;margin-top:22px">
<a class="btn btn-line" href="{{ $tel }}" style="justify-content:center">Call {{ $phone }}</a>
</div>
</nav>
</header>
