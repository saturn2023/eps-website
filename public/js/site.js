/* Ellenbrook Primary School - review build scripts */
(function () {
  'use strict';

  function toggle(btn, panel, useClass) {
    if (!btn || !panel) return;
    btn.addEventListener('click', function () {
      var open = useClass ? !panel.classList.contains('open') : panel.hidden;
      if (useClass) panel.classList.toggle('open', open); else panel.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  toggle(document.getElementById('tr-btn'), document.getElementById('translate-panel'), false);
  toggle(document.getElementById('menu-btn'), document.getElementById('mobile-menu'), true);


  // Desktop menus: open on hover with a short close delay so the cursor can travel to the panel
  document.querySelectorAll('.nav-links > li.has-mega, .nav-links > li.dd').forEach(function (li) {
    var t = null;
    var link = li.querySelector('a.nl');
    function open() {
      clearTimeout(t);
      document.querySelectorAll('.nav-links > li.open').forEach(function (o) { if (o !== li) o.classList.remove('open'); });
      li.classList.add('open');
      if (link) link.setAttribute('aria-expanded', 'true');
    }
    function close() {
      t = setTimeout(function () { li.classList.remove('open'); if (link) link.setAttribute('aria-expanded', 'false'); }, 350);
    }
    li.addEventListener('mouseenter', open);
    li.addEventListener('mouseleave', close);
    li.addEventListener('focusin', open);
    li.addEventListener('focusout', function (e) { if (!li.contains(e.relatedTarget)) { clearTimeout(t); li.classList.remove('open'); if (link) link.setAttribute('aria-expanded', 'false'); } });
    // Touch screens wide enough for the desktop menu (e.g. iPad landscape): first tap opens, second tap follows the link
    if (link) link.addEventListener('click', function (e) {
      if (window.matchMedia('(hover: none)').matches && !li.classList.contains('open')) { e.preventDefault(); open(); }
    });
  });
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.nav-links > li.open').forEach(function (o) { if (!o.contains(e.target)) o.classList.remove('open'); });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.nav-links > li.open').forEach(function (o) { o.classList.remove('open'); var a = o.querySelector('a.nl'); if (a) { a.setAttribute('aria-expanded', 'false'); a.focus(); } });
  });

  // Translate: Google Translate reads the "googtrans" cookie (/en/<code>) on each page.
  // Its script is only loaded once a visitor has picked a language.
  (function () {
    var select = document.getElementById('tr-lang');
    if (!select) return;
    var match = document.cookie.match(/(?:^|;\s*)googtrans=\/en\/([^;]+)/);
    var current = match ? decodeURIComponent(match[1]) : 'en';
    if (Array.prototype.some.call(select.options, function (o) { return o.value === current; })) select.value = current;

    function setCookie(value) {
      var host = location.hostname, expires = value ? '' : '; expires=Thu, 01 Jan 1970 00:00:00 GMT';
      var v = value ? '/en/' + value : '';
      document.cookie = 'googtrans=' + v + '; path=/' + expires;
      if (host.indexOf('.') > -1 && !/^\d+(\.\d+){3}$/.test(host)) {
        document.cookie = 'googtrans=' + v + '; path=/; domain=.' + host.replace(/^www\./, '') + expires;
      }
    }

    select.addEventListener('change', function () {
      setCookie(select.value === 'en' ? null : select.value);
      location.reload();
    });

    if (current !== 'en') {
      window.googleTranslateElementInit = function () {
        new google.translate.TranslateElement({ pageLanguage: 'en', autoDisplay: false }, 'google_translate_element');
      };
      var s = document.createElement('script');
      s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
      s.async = true;
      document.body.appendChild(s);
    }
  })();

  // Escape closes the translate panel and mobile menu
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var tp = document.getElementById('translate-panel'), tb = document.getElementById('tr-btn');
    if (tp && !tp.hidden) { tp.hidden = true; tb.setAttribute('aria-expanded', 'false'); tb.focus(); }
    var mm = document.getElementById('mobile-menu'), mb = document.getElementById('menu-btn');
    if (mm && mm.classList.contains('open')) { mm.classList.remove('open'); mb.setAttribute('aria-expanded', 'false'); mb.focus(); }
  });

  // Hero video: pause control and reduced motion
  var vid = document.getElementById('hero-video'), vb = document.getElementById('hero-pause');
  if (vid && vb) {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var setBtn = function () {
      var paused = vid.paused;
      vb.setAttribute('aria-label', paused ? 'Play background video' : 'Pause background video');
      vb.innerHTML = paused
        ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="#ffffff" aria-hidden="true"><path d="M8 5v14l11-7z"></path></svg>'
        : '<svg width="18" height="18" viewBox="0 0 24 24" fill="#ffffff" aria-hidden="true"><path d="M7 5h3.5v14H7z M13.5 5H17v14h-3.5z"></path></svg>';
    };
    if (reduce) { vid.removeAttribute('autoplay'); vid.pause(); }
    else { var p = vid.play(); if (p && p.catch) p.catch(function () { setBtn(); }); }
    vb.addEventListener('click', function () { if (vid.paused) vid.play(); else vid.pause(); });
    vid.addEventListener('play', setBtn); vid.addEventListener('pause', setBtn);
    setBtn();
  }

  // Upload boxes: validate and list files in the browser, then send them with the form.
  // MAX_TOTAL_MB stays under PHP's post_max_size (40 MB on XAMPP).
  var MAX_MB = 10, MAX_FILES = 10, MAX_TOTAL_MB = 35, OK = ['pdf', 'jpg', 'jpeg', 'png'];
  document.querySelectorAll('form[data-upload]').forEach(function (form) {
    var input = form.querySelector('input[type=file]');
    var drop = form.querySelector('.drop');
    var list = form.querySelector('.files');
    var status = form.querySelector('.status');
    var files = [];

    function render() {
      list.innerHTML = '';
      files.forEach(function (f, i) {
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        var bad = OK.indexOf(ext) === -1 ? 'File type not accepted' : (f.size > MAX_MB * 1048576 ? 'Larger than ' + MAX_MB + ' MB' : '');
        var li = document.createElement('li');
        if (bad) li.className = 'bad';
        var label = document.createElement('span');
        label.textContent = f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)' + (bad ? ', ' + bad : '');
        var rm = document.createElement('button');
        rm.type = 'button'; rm.textContent = 'Remove';
        rm.setAttribute('aria-label', 'Remove ' + f.name);
        rm.addEventListener('click', function () { files.splice(i, 1); render(); });
        li.appendChild(label); li.appendChild(rm); list.appendChild(li);
      });
    }
    function add(fl) {
      for (var i = 0; i < fl.length && files.length < MAX_FILES; i++) files.push(fl[i]);
      render();
    }
    input.addEventListener('change', function () { add(input.files); input.value = ''; });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('over'); }); });
    drop.addEventListener('drop', function (e) { add(e.dataTransfer.files); });

    form.addEventListener('submit', function (e) {
      status.className = 'status';
      var fail = function (msg, el) { e.preventDefault(); status.className = 'status err'; status.textContent = msg; if (el) el.focus(); };
      var missing = Array.prototype.filter.call(form.querySelectorAll('[required]'), function (el) {
        return el.type === 'checkbox' ? !el.checked : !el.value.trim();
      });
      if (missing.length) return fail('Please complete the required fields marked with *.', missing[0]);
      if (!files.length) return fail('Please add at least one document.');
      if (list.querySelector('.bad')) return fail('Please remove the files marked in red.');
      var total = files.reduce(function (sum, f) { return sum + f.size; }, 0);
      if (total > MAX_TOTAL_MB * 1048576) return fail('These files add up to more than ' + MAX_TOTAL_MB + ' MB. Please send them in two goes.');
      // Put the chosen files into the real file input so they are sent with the form.
      try {
        var dt = new DataTransfer();
        files.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
      } catch (err) { return fail('Your browser could not attach the files. Please try another browser.'); }
      status.className = 'status';
      status.textContent = 'Sending your documents…';
      form.querySelector('button[type=submit]').disabled = true;
    });
  });

  // Calendar
  var cal = document.getElementById('calendar');
  if (cal) {
    var EVENTS = window.EPS_EVENTS || [];
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var today = new Date(); today.setHours(0, 0, 0, 0);
    var view = new Date(today.getFullYear(), today.getMonth(), 1);
    var title = document.getElementById('cal-title');
    var listEl = document.getElementById('cal-list');
    var parse = function (s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); };
    var dayMs = 86400000;
    var fmt = function (d) { return d.getDate() + ' ' + MONTHS[d.getMonth()].slice(0, 3); };

    function draw() {
      title.textContent = MONTHS[view.getMonth()] + ' ' + view.getFullYear();
      var start = new Date(view); start.setDate(1 - start.getDay());
      var html = '<div class="cal-dow" aria-hidden="true"><div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div></div>';
      for (var w = 0; w < 6; w++) {
        var ws = new Date(start.getTime() + w * 7 * dayMs);
        if (w > 3 && ws.getMonth() !== view.getMonth()) break;
        var we = new Date(ws.getTime() + 6 * dayMs);
        var segs = [];
        EVENTS.forEach(function (ev) {
          var s = parse(ev.start), e = parse(ev.end || ev.start);
          if (e < ws || s > we) return;
          var a = s < ws ? 0 : Math.round((s - ws) / dayMs), b = e > we ? 6 : Math.round((e - ws) / dayMs);
          segs.push({ a: a, b: b, ev: ev });
        });
        segs.sort(function (x, y) { return x.a - y.a || (y.b - y.a) - (x.b - x.a); });
        var lanes = [];
        segs.forEach(function (sg) {
          var l = 0; while (lanes[l] && lanes[l] >= sg.a) l++;
          lanes[l] = sg.b; sg.l = l;
        });
        var minH = Math.max(118, 40 + lanes.length * 28);
        html += '<div class="cal-week" style="min-height:' + minH + 'px">';
        for (var d = 0; d < 7; d++) {
          var day = new Date(ws.getTime() + d * dayMs);
          var cls = 'cal-day' + (day.getMonth() !== view.getMonth() ? ' out' : '') + (day.getTime() === today.getTime() ? ' today' : '');
          html += '<div class="' + cls + '"><span class="n">' + day.getDate() + '</span></div>';
        }
        segs.forEach(function (sg) {
          var left = (sg.a / 7 * 100), width = ((sg.b - sg.a + 1) / 7 * 100);
          html += '<div class="cal-ev ' + (sg.ev.type || '') + '" style="left:calc(' + left + '% + 4px);width:calc(' + width + '% - 8px);top:' + (36 + sg.l * 28) + 'px" title="' + sg.ev.title + '">' + (sg.ev.time ? sg.ev.time + ' ' : '') + sg.ev.title + '</div>';
        });
        html += '</div>';
      }
      cal.innerHTML = html;

      // accessible list of this month's events
      var mStart = new Date(view), mEnd = new Date(view.getFullYear(), view.getMonth() + 1, 0);
      var items = EVENTS.filter(function (ev) { return parse(ev.end || ev.start) >= mStart && parse(ev.start) <= mEnd; })
        .sort(function (x, y) { return parse(x.start) - parse(y.start); });
      listEl.innerHTML = items.length ? items.map(function (ev) {
        var s = parse(ev.start), e = parse(ev.end || ev.start);
        var when = s.getTime() === e.getTime() ? fmt(s) : fmt(s) + ' to ' + fmt(e);
        return '<li><span class="d">' + when + '</span><span>' + (ev.time ? ev.time + ', ' : '') + ev.title + '</span></li>';
      }).join('') : '<li><span class="d">No events</span><span>Nothing is listed for this month yet.</span></li>';
    }
    document.getElementById('cal-prev').addEventListener('click', function () { view.setMonth(view.getMonth() - 1); draw(); });
    document.getElementById('cal-next').addEventListener('click', function () { view.setMonth(view.getMonth() + 1); draw(); });
    document.getElementById('cal-today').addEventListener('click', function () { view = new Date(today.getFullYear(), today.getMonth(), 1); draw(); });
    draw();
  }
})();
