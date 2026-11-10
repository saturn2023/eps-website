@use('App\Models\PageContent')
@php
    $schoolName = data_get($site, 'school_name', 'Ellenbrook Primary School');
    $artwork = PageContent::url(data_get($site, 'footer_artwork'));
    $footerLogo = PageContent::url(data_get($site, 'footer_logo'));
@endphp
<!-- Footer -->
<footer class="foot" style="background:#032855;color:#DCE8F0">
@if ($artwork)
<img src="{{ $artwork }}" alt="" style="display:block;width:100%;height:auto">
@endif
<div class="wrap" style="padding-top:24px;padding-bottom:40px">
<div class="foot-grid" style="display:grid;grid-template-columns:minmax(0,1.2fr) repeat(3,minmax(0,1fr));gap:40px">
<div>
@if ($footerLogo)
<img src="{{ $footerLogo }}" alt="{{ $schoolName }} logo" style="width:150px;height:auto;display:block;margin-bottom:18px">
@endif
<p style="margin:0;font-weight:500;color:#ffffff;font-size:18px">{{ $schoolName }}</p>
@if ($motto = data_get($site, 'motto'))
<p class="serif" style="margin:4px 0 0;color:#7FE3E8;font-size:15px">{{ $motto }}</p>
@endif
</div>
<div>
<h2 style="margin:0 0 14px;font-size:17px;font-weight:600;color:#ffffff">Contact</h2>
<p style="margin:0 0 10px"><a href="{{ $tel }}" style="font-weight:500;font-size:20px">Call {{ $phone }}</a></p>
@if ($email)
<p style="margin:0 0 10px;word-break:break-word"><a href="mailto:{{ $email }}">{{ $email }}</a></p>
@endif
@if ($address = data_get($site, 'address'))
<p style="margin:0">{!! nl2br(e($address)) !!}</p>
@endif
@if ($directions = data_get($site, 'directions_url'))
<p style="margin:8px 0 0"><a href="{{ $directions }}" style="color:#7FE3E8" target="_blank" rel="noopener">Get directions</a></p>
@endif
</div>
<div id="hours">
<h2 style="margin:0 0 14px;font-size:17px;font-weight:600;color:#ffffff">School hours</h2>
<div class="rich">{!! str(data_get($site, 'footer_hours', ''))->sanitizeHtml() !!}</div>
</div>
<div>
<h2 style="margin:0 0 14px;font-size:17px;font-weight:600;color:#ffffff">Quick links</h2>
<ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px">
@foreach (data_get($site, 'footer_links', []) as $link)
<li><a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a></li>
@endforeach
</ul>
</div>
</div>
<div class="dotline-dark" style="margin:48px 0 28px;opacity:.85"></div>
@if ($ack = data_get($site, 'acknowledgement'))
<div class="rich" style="margin:0 0 24px;max-width:860px;font-size:15px;line-height:1.7">{!! str($ack)->sanitizeHtml() !!}</div>
@endif
<div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;font-size:14px;color:#B9CBDA">
<span>&copy; {{ $schoolName }} {{ date('Y') }}</span>
<span><a href="#privacy">Privacy</a> &#160;&#160; Web by <a href="https://www.twomoonsconsulting.com.au">Two Moons</a></span>
</div>
</div>
</footer>
