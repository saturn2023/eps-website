{{-- Block: Calendar — month view and list, from the events under Calendar events in the admin --}}
@use('App\Models\Event')
@php $events = Event::orderBy('starts_on')->get()->map->toCalendarArray()->values(); @endphp
<div class="blk">
<div class="cal-head"><h2 id="cal-title" aria-live="polite">Calendar</h2><div class="cal-btns"><button id="cal-prev" type="button" aria-label="Previous month">‹ Prev</button><button id="cal-today" type="button">Today</button><button id="cal-next" type="button" aria-label="Next month">Next ›</button></div></div>
<div id="calendar" class="cal"></div>
<div class="cal-legend"><span><i class="hol"></i>School holidays</span><span><i class="sd"></i>School Development Day, no students</span><span><i></i>School event</span></div>
<h2>{{ $data['list_heading'] ?? 'Events this month' }}</h2>
<ul id="cal-list" class="cal-list"></ul>
<script>window.EPS_EVENTS = @json($events);</script>
</div>
