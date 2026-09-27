/**
 * Nyriel — client behaviour.
 *
 * Everything here is progressive enhancement: if a piece can't find its target
 * it returns quietly rather than throwing, so a Pterodactyl markup change
 * degrades to "feature silently absent" instead of a broken panel.
 *
 * Loaded with `defer`; config comes from the `#nyriel-cfg` JSON island.
 */
(function () {
  'use strict';

  const cfgEl = document.getElementById('nyriel-cfg');
  if (!cfgEl) return;

  let cfg;
  try {
    cfg = JSON.parse(cfgEl.textContent);
  } catch (e) {
    return;
  }

  const $  = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.prototype.slice.call((r || document).querySelectorAll(s));

  const escapeHtml = s => String(s).replace(/[&<>"']/g, c =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  /* ── alert ─────────────────────────────────────────────────────── */

  function renderAlert() {
    const box = $('#nyriel-alert-body');
    if (!box) return;
    const srcEl = document.getElementById('nyriel-alert-src');
    // A <textarea> carries the value verbatim; textContent would be HTML-decoded
    // and the JSON quotes would survive as literal characters.
    let raw = srcEl ? (srcEl.value !== undefined ? srcEl.value : srcEl.textContent) : '';
    if (!raw) return;
    // The island holds a JSON string literal, so unwrap it.
    try { raw = JSON.parse(raw); } catch (e) { /* already a plain string */ }

    // A deliberately small subset: bold, italic, inline code, links, line
    // breaks and unordered lists. Input is escaped first, so an alert can
    // never inject markup regardless of what follows.
    let html = escapeHtml(raw);
    if (cfg.alert && cfg.alert.md) {
      html = html
        .replace(/`([^`\n]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>')
        .replace(/\[([^\]\n]+)\]\((https?:\/\/[^\s)]+)\)/g,
                 '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
    }

    const out = [];
    let inList = false;
    for (const line of html.split(/\r?\n/)) {
      const li = /^\s*[-*]\s+(.*)$/.exec(line);
      if (li) {
        if (!inList) { out.push('<ul>'); inList = true; }
        out.push('<li>' + li[1] + '</li>');
      } else {
        if (inList) { out.push('</ul>'); inList = false; }
        out.push(line.trim() === '' ? '' : '<p>' + line + '</p>');
      }
    }
    if (inList) out.push('</ul>');

    box.innerHTML = out.join('');

    // Belt and braces: even if the link regex ever loosened, only http(s)
    // survives.
    $$('a', box).forEach(a => {
      if (/^https?:\/\//i.test(a.getAttribute('href') || '')) a.setAttribute('rel', 'noopener noreferrer');
      else a.removeAttribute('href');
    });
  }

  function wireAlert() {
    const bar = $('.nyriel-alert');
    if (!bar) return;
    const x = $('.x', bar);
    if (x) x.addEventListener('click', () => bar.remove());
    if (cfg.alert && cfg.alert.timeout > 0) setTimeout(() => bar.remove(), cfg.alert.timeout * 1000);
  }

  /* ── keyboard shortcuts ────────────────────────────────────────── */

/* ──────────────────────────────────────────────────────────────── */

  /**
   * Sidebar navigation.
   *
   * Two rules the theme cannot get wrong:
   *
   *  1. Clicking a button must navigate the *SPA*. Pterodactyl's client panel is
   *     React; setting location.href would tear the whole app down and lose its
   *     state. So the button finds the panel's own <a> for that route and calls
   *     .click() on it, which is exactly what the stock navigation does.
   *
   *  2. A button is only shown when the panel actually renders a link the
   *     signed-in user may follow. Permissions live in the panel's navigation,
   *     not in the theme, so we mirror that instead of guessing.
   */
  function navWire() {
    const rail = $('#nyriel-rail');
    if (!rail) return;

    // Current server id, taken from the URL rather than guessed.
    const serverId = (location.pathname.match(/^\/server\/([^/]+)/) || [])[1] || '';

    /** The panel's real link for a route, or null when it is not rendered. */
    const panelLink = route => {
      if (!route) return null;
      const want = route.replace('%s', serverId);
      if (want.includes('%s')) return null;              // no server context
      const links = Array.from(document.querySelectorAll('a[href]'));
      // Exact match first, then a prefix match so /server/<id> covers its
      // sub-pages, the way the panel's own tab bar does.
      return links.find(a => a.getAttribute('href') === want)
          || links.find(a => (a.getAttribute('href') || '').startsWith(want + '/'))
          || null;
    };

    // Server items only make sense where the panel is showing a server.
    const onServerPage = serverId !== '';

    $$('#nyriel-rail .nyriel-nav').forEach(btn => {
      const route = btn.dataset.navRoute || '';
      const isServer = btn.dataset.navCtx === 'server';
      const link = panelLink(route);

      if (isServer && !onServerPage) { btn.hidden = true; return; }
      if (!isServer && !link) { btn.hidden = true; return; }

      // Once a real link exists the button is a proxy for it.
      btn._link = link;
      if (!btn.dataset.nyrielBound) {
        btn.dataset.nyrielBound = '1';
        btn.addEventListener('click', ev => {
          ev.preventDefault();
          if (btn._link) { btn._link.click(); markActive(); }
        });
      }
    });

    // Hide a category heading once every button under it is gone.
    $$('#nyriel-rail .cat').forEach(cat => {
      const id = cat.dataset.cat;
      const siblings = $$('#nyriel-rail .nyriel-nav').filter(b => {
        const route = b.dataset.navRoute || '';
        const bcat = b.dataset.navCtx === 'server' ? 'server'
          : route.startsWith('/account') ? 'account'
          : route.startsWith('/admin') ? 'account'
          : 'general';
        return bcat === id;
      });
      if (!siblings.some(b => !b.hidden)) cat.hidden = true;
    });

    // A separator with nothing after it looks like a mistake; drop it.
    $$('#nyriel-rail .sep').forEach(sep => {
      let n = sep.nextElementSibling;
      while (n && n.classList.contains('sep')) n = n.nextElementSibling;
      if (!n || n.hidden) sep.hidden = true;
    });

    markActive();
    showKeyHints();
  }

  /**
   * Show each button's shortcut as a small badge in the rail.
   *
   * j/k move through the *visible* buttons, so those two are hinted on the
   * first and last visible entry rather than on a fixed pair. A key no button
   * answers to is simply not hinted.
   */
  function showKeyHints() {
    if (!cfg.keyHints) return;
    const keys = cfg.keys || {};
    const visible = $$('#nyriel-rail .nyriel-nav:not([hidden])');
    if (!visible.length) return;

    $$('#nyriel-rail .nyriel-nav .keyhint').forEach(el => el.remove());

    const label = new Map();
    const prev = String(keys.prev || '').toUpperCase();
    const next = String(keys.next || '').toUpperCase();
    if (prev) label.set(visible[0], prev);
    if (next) label.set(visible[visible.length - 1], next);

    visible.forEach(btn => {
      const text = label.get(btn);
      if (!text) return;
      const span = document.createElement('span');
      span.className = 'keyhint';
      span.textContent = text;
      btn.appendChild(span);
    });
  }

  /** Highlight the button whose route matches the current URL. */
  function markActive() {
    const now = location.pathname;
    const serverId = (now.match(/^\/server\/([^/]+)/) || [])[1] || '';

    // Longest route wins: /account is the parent of /account/api, so a plain
    // prefix test would light up every ancestor at once.
    const matches = $$('#nyriel-rail .nyriel-nav')
      .map(btn => {
        const want = (btn.dataset.navRoute || '').replace('%s', serverId);
        if (!want || want.includes('%s')) return null;
        return now === want || now.startsWith(want + '/') ? { btn, len: want.length } : null;
      })
      .filter(Boolean)
      .sort((a, b) => b.len - a.len);

    const best = matches.length ? matches[0].btn : null;
    $$('#nyriel-rail .nyriel-nav').forEach(btn => {
      btn.classList.toggle('active', btn === best);
    });
  }

  /** Re-evaluate visibility after every client-side navigation. */
  function watchNav() {
    let last = location.pathname;
    const tick = () => {
      if (location.pathname === last) return;
      last = location.pathname;
      navWire();
      // The router swapped the content, so anything the new view rendered has
      // to be tagged again.
      setTimeout(() => { applyAnimations(); }, 40);
    };
    // The panel is a SPA: it patches history and re-renders in place, so there
    // is no navigation event to listen for. Observing <body> is the cheapest
    // reliable signal that the tree changed.
    new MutationObserver(tick).observe(document.body, { childList: true, subtree: true });
    window.addEventListener('popstate', tick);
  }

  function wireShortcuts() {
    if (!cfg.shortcuts) return;
    const keys = cfg.keys || {};
    const rail = $('#nyriel-rail');
    const items = () => $$('#nyriel-rail .nyriel-nav:not([hidden])');

    const typing = ev => {
      const t = ev.target;
      return t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable);
    };
    const match = (key, want) => want && String(key || '').toLowerCase() === String(want).toLowerCase();

    document.addEventListener('keydown', ev => {
      if (match(ev.key, keys.escape)) {
        const menu = $('#nyriel-ctx');
        if (menu && menu.classList.contains('open')) { hideMenu(); return; }
      }

      if (typing(ev)) return;                 // never hijack while typing
      if (ev.ctrlKey || ev.metaKey || ev.altKey) return;

      const k = ev.key || '';

      if (match(k, keys.search)) {
        const box = $('input[type=search], input[placeholder*="Search" i]');
        if (box) { ev.preventDefault(); box.focus(); }
        return;
      }
      if (match(k, keys.console)) {
        const box = $('textarea, input[placeholder*="command" i]');
        if (box) { ev.preventDefault(); box.focus(); }
        return;
      }
      if (match(k, keys.sidebar) && rail) {
        ev.preventDefault();
        const collapsed = rail.classList.toggle('collapsed');
        document.body.classList.toggle('nyriel-rail-collapsed', collapsed);
        return;
      }

      const list = items();
      if (!list.length) return;
      let idx = list.findIndex(a => a.classList.contains('active'));
      if (idx === -1) idx = 0;

      if (match(k, keys.next)) {
        ev.preventDefault();
        list[Math.min(list.length - 1, idx + 1)].focus();
      } else if (match(k, keys.prev)) {
        ev.preventDefault();
        list[Math.max(0, idx - 1)].focus();
      }
    });
  }

  /* ── context menus ─────────────────────────────────────────────── */

  let menuEl = null;

  function hideMenu() {
    if (menuEl) menuEl.classList.remove('open');
  }

  function showMenu(x, y, entries) {
    if (!menuEl) return;
    menuEl.innerHTML = '';

    for (const it of entries) {
      if (it === '-') { menuEl.appendChild(document.createElement('hr')); continue; }
      const b = document.createElement('button');
      b.type = 'button';
      b.innerHTML = it.key
        ? escapeHtml(it.label) + '<span class="k">' + escapeHtml(it.key) + '</span>'
        : escapeHtml(it.label);
      b.addEventListener('click', () => {
        hideMenu();
        if (it.run) { try { it.run(); } catch (e) { /* target absent */ } }
      });
      menuEl.appendChild(b);
    }

    menuEl.classList.add('open');

    // Clamp inside the viewport — a menu that opens off-screen is unusable.
    const r = menuEl.getBoundingClientRect();
    const px = cfg.ctx && cfg.ctx.pos === 'element' ? x : Math.min(x, innerWidth - r.width - 8);
    menuEl.style.left = Math.max(8, px) + 'px';
    menuEl.style.top  = Math.max(8, Math.min(y, innerHeight - r.height - 8)) + 'px';
  }

  function wireContextMenus() {
    menuEl = $('#nyriel-ctx');
    if (!menuEl) return;

    document.addEventListener('click', hideMenu);
    document.addEventListener('scroll', hideMenu, true);
    window.addEventListener('blur', hideMenu);

    if (cfg.ctx && cfg.ctx.sidebar) {
      const rail = $('#nyriel-rail');
      if (!rail) return;
      rail.addEventListener('contextmenu', ev => {
        const a = ev.target.closest('a');
        if (!a) return;
        ev.preventDefault();
        const r = rail.getBoundingClientRect();
        showMenu(r.right + 4, a.getBoundingClientRect().top, [
          { label: 'Copy link', run: () => navigator.clipboard && navigator.clipboard.writeText(a.href) },
          '-',
          { label: 'Pin to top', run: () => rail.prepend(a) },
        ]);
      });
    }

    if (cfg.ctx && cfg.ctx.servers) {
      document.addEventListener('contextmenu', ev => {
        const card = ev.target.closest('a[href*="/servers/"]');
        if (!card) return;
        ev.preventDefault();
        const href = card.getAttribute('href') || '';
        showMenu(ev.clientX, ev.clientY, [
          { label: 'Open', run: () => { location.href = href; } },
          { label: 'Copy link', run: () => {
              try { navigator.clipboard.writeText(new URL(href, location.origin).href); } catch (e) {}
            } },
        ]);
      });
    }

    if (cfg.ctx && cfg.ctx.files) {
      document.addEventListener('contextmenu', ev => {
        const row = ev.target.closest('[data-object="file"], tr');
        if (!row) return;
        ev.preventDefault();
        const name = (row.getAttribute('title') || row.textContent || '').trim().slice(0, 40);
        showMenu(ev.clientX, ev.clientY, [
          { label: 'Copy name', run: () => navigator.clipboard && navigator.clipboard.writeText(name) },
          '-',
          { label: 'Select', run: () => { try { row.click(); } catch (e) {} } },
        ]);
      });
    }
  }

  /* ── file view + graphs ────────────────────────────────────────── */

  function wireViewPrefs() {
    // cfg.fileView comes from file_view; file_grid is the same choice exposed as
    // a toggle, so it only decides the value when file_view is untouched.
    let view = cfg.fileView || (cfg.fileGrid ? 'grid' : 'list');
    try {
      const saved = localStorage.getItem('nyriel:fileView');
      if (saved === 'grid' || saved === 'list') view = saved;
    } catch (e) { /* private mode */ }

    document.documentElement.setAttribute('data-nyriel-fileview', view);

    // Alt+V flips the view; the choice is remembered per browser.
    document.addEventListener('keydown', ev => {
      if (!ev.altKey || (ev.key || '').toLowerCase() !== 'v') return;
      const t = ev.target;
      if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA')) return;
      ev.preventDefault();
      view = view === 'grid' ? 'list' : 'grid';
      document.documentElement.setAttribute('data-nyriel-fileview', view);
      try { localStorage.setItem('nyriel:fileView', view); } catch (e) {}
    });

    if (cfg.graphs && cfg.graphs !== 'off') {
      document.documentElement.setAttribute('data-nyriel-graphs', cfg.graphs);
    }
  }

  /* ── animated counters ─────────────────────────────────────────── */

  function animateCounters() {
    if (!(cfg.stats && cfg.stats.animate)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const seen = new WeakSet();
    const io = new IntersectionObserver(entries => {
      for (const en of entries) {
        if (!en.isIntersecting) continue;
        const el = en.target;
        if (seen.has(el)) continue;
        seen.add(el);

        const full = el.textContent.trim();
        const m = /^([^\d]*)([\d.]+)(.*)$/.exec(full);
        if (!m) continue;
        const target = parseFloat(m[2]);
        if (!isFinite(target) || target === 0) continue;

        const t0 = performance.now();
        const step = now => {
          const p = Math.min(1, (now - t0) / 550);
          const eased = 1 - Math.pow(1 - p, 3);
          el.textContent = m[1] + (Math.round(target * eased * 10) / 10) + m[3];
          if (p < 1) requestAnimationFrame(step);
          else el.textContent = full;
        };
        requestAnimationFrame(step);
      }
    }, { threshold: 0.4 });

    $$('span, strong, b, h3, h4').forEach(el => {
      if (el.children.length === 0 && /\d/.test(el.textContent)) io.observe(el);
    });
  }

  /* ── boot ──────────────────────────────────────────────────────── */

/* ── Animations ─────────────────────────────────────────────────────
   The stylesheet defines the motion; this decides what gets it. Classes are
   applied to the panel's own rows, cards and status dots, and only when the
   corresponding setting is on — so a disabled effect costs nothing.

   All of this degrades to nothing under prefers-reduced-motion, which the
   stylesheet enforces. */

  /** Which entry animation a container's children get. */
  const ENTRY_CLASSES = [
    ['fx_fade',  'ny-fade-in'],
    ['fx_rise',  'ny-rise-in'],
    ['fx_drop',  'ny-drop-in'],
    ['fx_pop',   'ny-pop-in'],
    ['fx_swing', 'ny-swing-in'],
    ['fx_blur',  'ny-blur-in'],
  ];

  // Selectors for the panel's repeated rows. These are the stable class names
  // and data attributes the panel renders, not its styled-components hashes.
  const ROW_SELECTORS = [
    '[class*="ServerCard"]',            // dashboard server cards
    '[class*="ServerRow"]',             // server list rows
    'table tbody tr',                   // backups, schedules, subusers
    '[class*="ScheduleRow"]',
    '[class*="ActivityLog"]',
    '[class*="DatabaseRow"]',
    '[class*="UserRow"]',
    // The panel has no <main>; these are the wrappers it actually renders, so
    // a page with no servers still animates something.
    '[class*="PageContentBlock"] > div',
    '[class*="ContentBox"] > div',
  ];

  const CARD_SELECTORS = [
    '.box',                              // admin panel
    '[class*="ContentBox"]',             // client panel content cards
    '[class*="PageContentBlock"]',
  ];

  // The config island mixes camelCase and snake_case (fxStagger vs fx_fade).
  // Read through one normaliser so a rename in the wrapper cannot silently
  // disable half the motion.
  const flag = name => cfg[name] ?? cfg[name.replace(/_([a-z])/g, (m, c) => c.toUpperCase())];

  function applyAnimations() {
    if (!flag('animations')) return;

    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced) return;                 // stylesheet already neutralises them

    // One entry effect for the whole page, so rows do not each pick their own.
    const entry = (ENTRY_CLASSES.find(([k]) => flag(k)) || [])[1];

    // Tag each repeated row group as a stagger container, then let the
    // stylesheet's nth-child rules do the sequencing. Re-tagging on every call
    // is fine: the class is the same, and setAttribute is idempotent.
    if (flag('fx_stagger') && entry) {
      ROW_SELECTORS.forEach(sel => {
        $$(sel).forEach(row => {
          if (row.closest('.ny-stagger')) return;   // already inside one
          const group = row.parentElement;
          if (!group) return;
          group.classList.add('ny-stagger');
        });
      });
    }

    if (entry) {
      // Animate the page's own content wrapper once, not every node in it.
      const root = $('[class*="App___StyledDiv"]') || $('[class*="ContentBox"]');
      if (root) root.classList.add(entry);
      CARD_SELECTORS.forEach(sel => {
        $$(sel).forEach((el, i) => {
          if (el.classList.contains('ny-fade-in')) return;
          // A card that is already on screen when the page loads does not need
          // to fly in; only the ones the router swapped in do.
          if (el.classList.contains('ny-stagger') || el.classList.contains(entry)) return;
          el.classList.add(entry);
        });
      });
    }

    if (flag('fx_hover_lift')) {
      $$('.box, [class*="ServerCard"], .small-box').forEach(el => {
        el.classList.add('ny-lift');
      });
    }

    // Status dots: a breathing dot reads as "live" without a number next to it.
    if (flag('fx_status_breathe') || flag('fx_status_pulse')) {
      const dotClass = flag('fx_status_pulse') ? 'ny-pulse' : 'ny-breathe';
      $$('.status-bar, [class*="ServerCard"] .status-bar').forEach(el => {
        el.classList.add(dotClass);
      });
    }
  }

  /** Remove every animation class, for the Live toggle in the designer. */
  function clearAnimations() {
    $$('.ny-stagger').forEach(el => el.classList.remove('ny-stagger'));
    $$('.ny-lift, .ny-pulse, .ny-breathe').forEach(el => {
      el.classList.remove('ny-lift', 'ny-pulse', 'ny-breathe');
    });
    const all = ENTRY_CLASSES.map(([, c]) => c);
    all.forEach(c => $$(`.${c}`).forEach(el => el.classList.remove(c)));
  }

/* ── Multitasking ──────────────────────────────────────────────────
   Nebula's floating windows: a page opens in a draggable, resizable frame
   instead of replacing the whole app, so the terminal stays visible while a
   settings page is open. Implemented independently against the panel's own
   links; nothing here depends on generated class hashes. */

  const WIN_MIN_W = 480;
  const WIN_MIN_H = 240;

  function wireMultitasking() {
    if (!flag('multitasking')) return;

    // A modifier-click opens in a frame instead of navigating, which is the
    // same gesture a browser uses for "open in new tab".
    const openInFrame = url => {
      const existing = document.querySelector('.ny-frame[data-ny-src="' + cssEscape(url) + '"]');
      if (existing) { focusFrame(existing); return; }

      const wrap = document.createElement('div');
      wrap.className = 'ny-frame';
      wrap.dataset.nySrc = url;
      wrap.innerHTML =
        '<div class="ny-frame-bar">' +
          '<span class="ny-frame-title">' + escapeHtml(url.replace(location.origin, '')) + '</span>' +
          '<button type="button" class="ny-frame-x" aria-label="Close">&times;</button>' +
        '</div>' +
        '<div class="ny-frame-load"><span class="ny-frame-spin"></span></div>' +
        '<iframe class="ny-frame-body" src="' + escapeHtml(url) + '" title=""></iframe>';

      document.body.appendChild(wrap);
      wireFrame(wrap);
      return wrap;
    };

    // A safe CSS.escape, because the value goes into an attribute selector.
    const cssEscape = s => (window.CSS && CSS.escape ? CSS.escape(s) : s.replace(/["\\]/g, '\\$&'));

    const focusFrame = frame => {
      document.querySelectorAll('.ny-frame').forEach(f => {
        const on = f === frame;
        f.style.zIndex = on ? 1000 : 990;
        f.classList.toggle('is-focused', on);
      });
    };

    function wireFrame(frame) {
      const bar = frame.querySelector('.ny-frame-bar');
      const body = frame.querySelector('.ny-frame-body');
      const loader = frame.querySelector('.ny-frame-load');

      frame.querySelector('.ny-frame-x').onclick = e => { e.stopPropagation(); close(); };
      function close() {
        frame.style.opacity = '0';
        frame.style.transform = 'scale(.96)';
        setTimeout(() => frame.remove(), 180);
      }

      // The iframe is same-origin, so its own load event is observable.
      body.addEventListener('load', () => {
        loader.style.opacity = '0';
        body.style.opacity = '1';
        setTimeout(() => loader.remove(), 220);
      }, { once: true });

      frame.addEventListener('mousedown', () => focusFrame(frame), true);

      // Drag by the bar.
      let drag = null;
      bar.addEventListener('mousedown', e => {
        if (e.target.closest('.ny-frame-x')) return;
        e.preventDefault();
        focusFrame(frame);
        const r = frame.getBoundingClientRect();
        drag = { dx: e.clientX - r.left, dy: e.clientY - r.top };
        bar.setPointerCapture?.(e.pointerId);
      });
      bar.addEventListener('pointermove', e => {
        if (!drag) return;
        const w = frame.offsetWidth, h = frame.offsetHeight;
        frame.style.left = Math.max(0, Math.min(e.clientX - drag.dx, innerWidth - 60)) + 'px';
        frame.style.top = Math.max(0, Math.min(e.clientY - drag.dy, innerHeight - 40)) + 'px';
        frame.style.right = 'auto';
        frame.style.bottom = 'auto';
      });
      bar.addEventListener('pointerup', () => { drag = null; });

      // Resize from the bottom-right corner.
      const grip = document.createElement('div');
      grip.className = 'ny-frame-grip';
      frame.appendChild(grip);
      let size = null;
      grip.addEventListener('mousedown', e => {
        e.preventDefault(); e.stopPropagation();
        const r = frame.getBoundingClientRect();
        size = { x: e.clientX, y: e.clientY, w: r.width, h: r.height };
        grip.setPointerCapture?.(e.pointerId);
      });
      grip.addEventListener('pointermove', e => {
        if (!size) return;
        frame.style.width = Math.max(WIN_MIN_W, size.w + (e.clientX - size.x)) + 'px';
        frame.style.height = Math.max(WIN_MIN_H, size.h + (e.clientY - size.y)) + 'px';
        frame.style.right = 'auto';
        frame.style.bottom = 'auto';
      });
      grip.addEventListener('pointerup', () => { size = null; });
    }

    document.addEventListener('click', e => {
      if (e.defaultPrevented || e.button !== 0) return;
      if (!(e.altKey || e.metaKey || e.ctrlKey)) return;
      const link = e.target.closest('a[href]');
      if (!link) return;
      const href = link.getAttribute('href');
      if (!href || href.startsWith('http') || href.startsWith('#')) return;
      e.preventDefault();
      openInFrame(href);
    });

    // Escape closes the topmost frame, the same key the shortcuts use for
    // every other overlay.
    document.addEventListener('keydown', e => {
      if (e.key !== 'Escape') return;
      const frames = document.querySelectorAll('.ny-frame');
      if (!frames.length) return;
      const top = frames[frames.length - 1];
      const x = top.querySelector('.ny-frame-x');
      if (x) x.click();
    });
  }

  /* ── Middle-click a sidebar button ────────────────────────────────
     Opens the page in a real browser tab, which is what a middle click means
     everywhere else. Nebula does this too; here it is a data attribute on the
     button rather than a hardcoded list of ids. */

  function wireMiddleClick() {
    if (!flag('middleClick')) return;
    $('#nyriel-rail')?.addEventListener('auxclick', e => {
      if (e.button !== 1 && !(e.ctrlKey || e.metaKey)) return;
      const btn = e.target.closest('.aether-nav');
      if (!btn || !btn._link) return;
      e.preventDefault();
      window.open(btn._link.href, '_blank', 'noopener');
    });
  }

  /* ── Status orb ───────────────────────────────────────────────────
     A fixed dot that follows the current server's status. The panel only
     renders a status bar on the server overview, so the orb reads that and
     fades itself out when there is nothing to report. */

  function wireStatusOrb() {
    if (!flag('statusOrb')) return;
    if ($('#ny-orb')) return;

    const orb = document.createElement('div');
    orb.id = 'ny-orb';
    orb.hidden = true;
    document.body.appendChild(orb);

    const paint = () => {
      // Prefer the panel's own bar; its --ActiveColor is the live status.
      const bar = document.querySelector('[class*="ServerCard"] .status-bar, .status-bar');
      if (!bar) { orb.hidden = true; return; }
      const colour = getComputedStyle(bar).getPropertyValue('--ActiveColor').trim();
      orb.style.background = colour || 'var(--ny-st-offline)';
      orb.hidden = !colour;
    };

    paint();
    new MutationObserver(paint).observe(document.body, { childList: true, subtree: true });
  }

  /* ── Keybind reference ────────────────────────────────────────────
     Nebula opens a modal listing every shortcut. Same idea, built from the
     keymap the panel already has rather than a second copy of it. */

  function wireKeybindHelp() {
    if (!flag('keybindHelp')) return;
    if ($('#ny-keys')) return;

    const rows = Object.entries(cfg.keys || {})
      .map(([action, key]) =>
        '<div class="ny-keys-row"><span>' + escapeHtml(action) + '</span>' +
        '<kbd>' + escapeHtml(String(key).toUpperCase()) + '</kbd></div>')
      .join('');

    const box = document.createElement('div');
    box.id = 'ny-keys';
    box.hidden = true;
    box.innerHTML =
      '<div class="ny-keys-inner">' +
        '<header><b>Keyboard shortcuts</b><button type="button" class="ny-keys-x" aria-label="Close">&times;</button></header>' +
        rows +
        '<p class="ny-keys-note">Alt+V toggles the file list/grid view. Escape closes this.</p>' +
      '</div>';
    document.body.appendChild(box);

    const show = () => { box.hidden = false; };
    const hide = () => { box.hidden = true; };
    box.querySelector('.ny-keys-x').onclick = hide;
    box.addEventListener('click', e => { if (e.target === box) hide(); });

    // The help key defaults to "?" so it does not collide with the six the
    // panel already binds.
    document.addEventListener('keydown', e => {
      if (typing(e)) return;
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (e.key === '?') { e.preventDefault(); box.hidden ? show() : hide(); }
      if (e.key === 'Escape' && !box.hidden) hide();
    });
  }

  /* ── Mobile navigation ────────────────────────────────────────────
     The rail is a fixed column, which is unusable below 768px. Collapse it to
     a bottom bar there, and give it a toggle so it can be put away. */

  function wireMobileNav() {
    const rail = $('#nyriel-rail');
    if (!rail) return;

    const mq = matchMedia('(max-width: 768px)');
    const apply = () => {
      document.documentElement.classList.toggle('ny-mobile', mq.matches);
      if (mq.matches) {
        if (!$('#ny-mobiletoggle')) {
          const t = document.createElement('button');
          t.id = 'ny-mobiletoggle';
          t.type = 'button';
          t.setAttribute('aria-label', 'Toggle navigation');
          t.textContent = '☰';
          t.onclick = () => document.body.classList.toggle('ny-rail-open');
          document.body.appendChild(t);
        }
      } else {
        document.body.classList.remove('ny-rail-open');
        $('#ny-mobiletoggle')?.remove();
      }
    };

    apply();
    mq.addEventListener('change', apply);
  }

  function boot() {
    renderAlert();
    wireAlert();
    wireShortcuts();
    wireContextMenus();
    wireViewPrefs();
    animateCounters();
    navWire();
    showKeyHints();
    applyAnimations();
    wireMultitasking();
    wireMiddleClick();
    wireStatusOrb();
    wireKeybindHelp();
    wireMobileNav();
    watchNav();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
