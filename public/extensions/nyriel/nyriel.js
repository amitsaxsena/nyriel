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

  function boot() {
    renderAlert();
    wireAlert();
    wireShortcuts();
    wireContextMenus();
    wireViewPrefs();
    animateCounters();
    navWire();
    showKeyHints();
    watchNav();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
