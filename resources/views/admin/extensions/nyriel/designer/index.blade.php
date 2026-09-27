<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nyriel Designer</title>
<style>
:root {
  --bg:#05070c; --panel:#0c121c; --panel2:#131b28; --panel3:#1a2434;
  --line:rgba(120,165,255,.14); --line2:rgba(120,165,255,.28);
  --text:#e8f0fb; --dim:#8ea3c4; --dim2:#5f7396;
  --accent:#3b82f6; --accent2:#06b6d4; --ok:#34d399; --err:#f87171;
}
* { box-sizing: border-box; margin: 0; }
body { font:13px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
  background:var(--bg); color:var(--text); height:100vh; display:flex; flex-direction:column; overflow:hidden; }

header { display:flex; align-items:center; gap:12px; padding:10px 16px;
  background:var(--panel); border-bottom:1px solid var(--line); flex:0 0 auto; }
header h1 { font-size:15px; font-weight:700; letter-spacing:.3px; }
header h1 span { color:var(--accent); }
.spacer { flex:1; }
#status { font-size:12px; color:var(--dim); display:flex; align-items:center; gap:6px; }
#status.ok { color:var(--ok); } #status.err { color:var(--err); }
#status .dot { width:7px; height:7px; border-radius:50%; background:currentColor; }
#search { background:var(--panel2); border:1px solid var(--line); border-radius:8px;
  padding:7px 11px; color:var(--text); font:inherit; font-size:12px; width:190px; }
#search::placeholder { color:var(--dim2); }
#search:focus { outline:none; border-color:var(--accent); }

.btn { font:inherit; font-size:12px; font-weight:600; cursor:pointer; border:1px solid transparent;
  border-radius:9px; padding:8px 16px; color:#fff; background:linear-gradient(135deg,var(--accent),var(--accent2));
  display:inline-flex; align-items:center; gap:7px; }
.btn.ghost { background:var(--panel2); border-color:var(--line); color:var(--dim); }
.btn.ghost:hover { color:var(--text); border-color:var(--line2); }
.btn:disabled { opacity:.5; cursor:not-allowed; }
.btn svg { width:14px; height:14px; }
.btn.danger { background:linear-gradient(135deg,#dc2626,#f87171); }

main { flex:1; display:grid; grid-template-columns:380px 1fr; min-height:0; }
aside { background:var(--panel); border-right:1px solid var(--line); overflow-y:auto; padding:12px; min-width:0; }
aside::-webkit-scrollbar { width:8px; } aside::-webkit-scrollbar-thumb { background:var(--line2); border-radius:8px; }

.snav { display:flex; flex-direction:column; gap:3px; margin-bottom:16px; }
.snav button { display:flex; align-items:center; gap:10px; width:100%; text-align:left;
  background:transparent; border:0; border-radius:10px; padding:9px 12px;
  color:var(--dim); font:inherit; font-size:12.5px; font-weight:600; cursor:pointer; }
.snav button .n { margin-left:auto; font-size:10px; opacity:.5; }
.snav button:hover { background:var(--panel2); color:var(--text); }
.snav button.on { background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; }

.group { display:none; } .group.on { display:block; }
.gh { display:flex; align-items:center; gap:8px; margin:18px 0 11px; }
.gh:first-child { margin-top:0; }
.gh h3 { font-size:10.5px; text-transform:uppercase; letter-spacing:.9px; color:var(--dim); font-weight:700; white-space:nowrap; }
.gh::after { content:''; flex:1; height:1px; background:var(--line); }
.ghint { font-size:10.5px; color:var(--dim2); margin:-6px 0 11px; line-height:1.5; }

.presets { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.preset { border:1px solid var(--line); border-radius:11px; padding:10px; cursor:pointer; text-align:left;
  background:var(--panel2); color:var(--text); font:inherit; font-size:11.5px; font-weight:600;
  display:flex; flex-direction:column; gap:7px; }
.preset:hover { border-color:var(--accent); transform:translateY(-1px); }
.preset.on { border-color:var(--accent); box-shadow:0 0 0 2px rgba(59,130,246,.2); }
.preset .dots { display:flex; gap:3px; }
.preset .dots b { width:15px; height:15px; border-radius:5px; display:block; }
.preset .mini { height:22px; border-radius:6px; border:1px solid var(--line); }

.toggle { display:flex; align-items:center; gap:11px; padding:9px 12px; background:var(--panel2);
  border:1px solid var(--line); border-radius:11px; cursor:pointer; margin-bottom:7px; }
.toggle:hover { border-color:var(--line2); }
.toggle .track { width:34px; height:19px; border-radius:10px; background:var(--panel3);
  position:relative; flex:0 0 auto; transition:background .2s; }
.toggle .track::after { content:''; position:absolute; width:15px; height:15px; border-radius:50%;
  background:var(--dim); top:2px; left:2px; transition:transform .2s,background .2s; }
.toggle input { display:none; }
.toggle input:checked + .track { background:linear-gradient(135deg,var(--accent),var(--accent2)); }
.toggle input:checked + .track::after { transform:translateX(15px); background:#fff; }
.toggle .tlabel { font-size:12.5px; font-weight:600; }
.toggle .thint { font-size:10.5px; color:var(--dim); display:block; margin-top:1px; }

.crow, .frow, .srow { margin-bottom:11px; }
.crow .ch, .srow .sh { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.crow label, .frow label, .srow label { font-size:11.5px; color:var(--dim); }
.crow .hexv, .srow .val { font-size:10.5px; color:var(--dim2); font-family:ui-monospace,monospace; }
.swatchrow { display:flex; gap:8px; align-items:center; }
.swatch { width:40px; height:34px; border-radius:9px; border:1px solid var(--line2); cursor:pointer;
  position:relative; overflow:hidden; flex:0 0 auto; transition:transform .12s,border-color .12s; }
.swatch:hover { transform:scale(1.07); border-color:var(--accent); }
.swatch input { position:absolute; inset:-8px; width:200%; height:200%; cursor:pointer; opacity:0; }
.swatch input::-webkit-color-swatch-wrapper { padding:0; }
.swatch input::-webkit-color-swatch { border:0; }

input[type=range] { -webkit-appearance:none; appearance:none; width:100%; height:4px;
  border-radius:4px; background:var(--panel3); outline:none; cursor:pointer; }
input[type=range]::-webkit-slider-thumb { -webkit-appearance:none; width:16px; height:16px; border-radius:50%;
  background:linear-gradient(135deg,var(--accent),var(--accent2)); border:2px solid #fff; cursor:grab;
  box-shadow:0 2px 6px rgba(0,0,0,.5); }
input[type=range]::-moz-range-thumb { width:14px; height:14px; border-radius:50%; border:2px solid #fff;
  background:var(--accent); cursor:grab; }

select, input[type=text] { width:100%; font:inherit; font-size:12px; padding:8px 11px;
  background:var(--panel2); border:1px solid var(--line); border-radius:9px; color:var(--text); cursor:pointer; }
select:focus, input[type=text]:focus { outline:none; border-color:var(--accent); }
.hint { font-size:10.5px; color:var(--dim2); margin-top:3px; }

#pane { background:#04060a; display:flex; flex-direction:column; min-width:0; }
#pbar { display:flex; align-items:center; gap:10px; padding:8px 14px; border-bottom:1px solid var(--line);
  font-size:11.5px; color:var(--dim); flex:0 0 auto; }
.vp { display:flex; gap:3px; }
.vp button { background:var(--panel2); border:1px solid var(--line); border-radius:7px; padding:5px 9px;
  color:var(--dim); cursor:pointer; font:inherit; font-size:11px; }
.vp button.on { background:var(--accent); color:#fff; border-color:var(--accent); }
#stage { flex:1; overflow:auto; display:flex; justify-content:center; padding:16px; }
#frame { border:0; border-radius:10px; background:#05070c; width:100%; height:100%;
  box-shadow:0 8px 40px rgba(0,0,0,.6); transition:width .25s ease; }
.nores { padding:20px; text-align:center; color:var(--dim2); font-size:11.5px; }
</style>
</head>
<body>

<header>
  <h1>Nyriel <span>Designer</span></h1>
  <input type="text" id="search" placeholder="Search settings…" autocomplete="off">
  <div class="spacer"></div>
  <div id="status"><span class="dot"></span><span class="txt">Loading…</span></div>
  <div class="spacer"></div>
  <button class="btn ghost" id="resetAll">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
    Factory reset
  </button>
  <button class="btn" id="save">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
    Save
  </button>
  <a class="btn ghost" href="/admin/extensions" style="text-decoration:none;">← Back</a>
</header>

<main>
  <aside>
    <nav class="snav" id="snav"></nav>
    <div id="panes"></div>
  </aside>

  <div id="pane">
    <div id="pbar">
      <span>Live preview</span>
      <div class="vp">
        <button data-w="100%" class="on">Fit</button>
        <button data-w="1280px">1280</button>
        <button data-w="820px">820</button>
        <button data-w="390px">390</button>
      </div>
      <select id="page" style="width:auto;padding:5px 9px;font-size:11.5px;">
        <option value="/">Dashboard</option>
        <option value="/servers">Servers</option>
        <option value="/account">Account</option>
        <option value="/account/api">API keys</option>
        <option value="/account/ssh">SSH keys</option>
      </select>
      <div class="spacer"></div>
      <label class="toggle" style="margin:0;padding:4px 10px;">
        <input type="checkbox" id="reload" checked><span class="track" style="width:30px;height:17px"></span>
        <span class="tlabel" style="font-size:11px;">Live</span>
      </label>
    </div>
    <div id="stage"><iframe id="frame" title="Nyriel preview"></iframe></div>
  </div>
</main>

<script>
const csrf   = @json(csrf_token());
const GROUPS = @json($groups);

const PRESETS = {
  'Midnight': { bg:'#0a0f1a', bg_alt:'#111a2b', sidebar_bg:'#0d1424', accent:'#3b82f6', accent2:'#06b6d4', text:'#e2e8f0', text_dim:'#94a3b8' },
  'Ember':    { bg:'#14090a', bg_alt:'#1f1012', sidebar_bg:'#1a0c0d', accent:'#f97316', accent2:'#fbbf24', text:'#fdeae0', text_dim:'#c4a294' },
  'Forest':   { bg:'#08130f', bg_alt:'#0e1f18', sidebar_bg:'#0b1813', accent:'#10b981', accent2:'#84cc16', text:'#e2f5ec', text_dim:'#94bfa8' },
  'Royal':    { bg:'#0b0718', bg_alt:'#140d28', sidebar_bg:'#100a1f', accent:'#8b5cf6', accent2:'#d946ef', text:'#f0e9fb', text_dim:'#b3a0cf' },
  'Slate':    { bg:'#101214', bg_alt:'#181b1e', sidebar_bg:'#15181b', accent:'#64748b', accent2:'#94a3b8', text:'#e8eaed', text_dim:'#98a2b3' },
  'Solar':    { bg:'#1a1005', bg_alt:'#271809', sidebar_bg:'#1f1407', accent:'#eab308', accent2:'#f59e0b', text:'#fdf3d7', text_dim:'#bda678' },
};

let state = {};
let defaults = {};
let timer = null;
let activePreset = null;
let activeGroup = 0;
let previewPage = '/';

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const isHex = v => /^#[0-9a-fA-F]{6}$/.test(String(v));

// Laravel returns errors as objects ({message}) or strings depending on the
// failure. Stringifying them blindly yields "[object Object]", which tells the
// admin nothing about what to fix.
function fmtErrors(json) {
  const out = [];
  const push = v => {
    if (v == null) return;
    if (typeof v === 'string') out.push(v);
    else if (Array.isArray(v)) v.forEach(push);
    else if (typeof v === 'object') {
      // {field: [msgs]} from a 422, or {message|detail|code} from a 500.
      for (const k of Object.keys(v)) push(v[k]);
    } else out.push(String(v));
  };
  push(json.errors);
  push(json.message);
  return out.join(' · ') || 'Unknown error';
}

function status(msg, kind = '') {
  const el = $('#status');
  el.className = kind;
  $('.txt', el).textContent = msg;
}

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

/* ── build ─────────────────────────────────────────────────────────── */

function buildNav() {
  const nav = $('#snav');
  nav.innerHTML = '';
  GROUPS.forEach((g, i) => {
    const b = document.createElement('button');
    b.innerHTML = `${esc(g.label)}<span class="n">${g.fields.length}</span>`;
    b.className = i === 0 ? 'on' : '';
    b.onclick = () => {
      activeGroup = i;
      $$('#snav button').forEach((x, j) => x.classList.toggle('on', j === i));
      $$('.group').forEach((x, j) => x.classList.toggle('on', j === i));
    };
    nav.appendChild(b);
  });
}

function fieldHtml(f) {
  const k = esc(f.key), lbl = esc(f.label || f.key), hint = f.hint ? `<div class="hint">${esc(f.hint)}</div>` : '';

  // An action is a one-shot command, not a stored value. Rendering it as a
  // toggle would write the flag into the DB on every save.
  if (f.type === 'action') {
    return `<div class="crow"><button type="button" class="btn danger" data-action="${k}" style="width:100%;justify-content:center;">
      ${esc(f.label || k)}</button>${hint}</div>`;
  }

  if (f.type === 'bool') {
    return `<label class="toggle"><input type="checkbox" data-key="${k}"><span class="track"></span>
      <span><span class="tlabel">${lbl}</span><span class="thint">${esc(f.hint || '')}</span></span></label>`;
  }

  if (f.type === 'color') {
    return `<div class="crow" data-search="${lbl.toLowerCase()}">
      <div class="ch"><label>${lbl}</label><span class="hexv" data-hex="${k}"></span></div>
      <div class="swatchrow">
        <div class="swatch"><input type="color" data-key="${k}"></div>
        <input type="text" data-key="${k}" data-mirror>
      </div>${hint}</div>`;
  }

  if (f.type === 'number') {
    const step = f.step ?? 1;
    return `<div class="srow" data-slider="${k}" data-unit="">
      <div class="sh"><label>${lbl}</label><span class="val"></span></div>
      <input type="range" data-key="${k}" min="${f.min}" max="${f.max}" step="${step}">${hint}</div>`;
  }

  if (f.type === 'select') {
    const opts = Object.entries(f.options || {})
      .map(([v, t]) => `<option value="${esc(v)}">${esc(t)}</option>`).join('');
    return `<div class="frow" data-search="${lbl.toLowerCase()}">
      <label>${lbl}</label><select data-key="${k}">${opts}</select>${hint}</div>`;
  }

  // An icon_ setting holds a CSS class, not a word. Typing it by hand means
  // guessing a family's prefix, so offer the real list instead.
  if (k.startsWith('icon_')) {
    return `<div class="frow" data-search="${lbl.toLowerCase()}">
      <label>${lbl}</label>
      <div class="iconpick-row">
        <input type="text" data-key="${k}" placeholder="${esc(f.default ?? 'ti-home')}">
        <button type="button" class="btn ghost" data-pick="${k}" title="Browse icons">Browse</button>
      </div>
      <div class="iconprev" data-prev="${k}"></div>${hint}</div>`;
  }

  return `<div class="frow" data-search="${lbl.toLowerCase()}">
    <label>${lbl}</label><input type="text" data-key="${k}" placeholder="${esc(f.default ?? '')}">${hint}</div>`;
}

/* ── Icon picker ──────────────────────────────────────────────────
   The list is read from the icon family stylesheet the designer already
   links, so every icon the family ships is offered without hardcoding a
   single name. Fetch it, parse the rules, and index by class name. */
const ICON_SETS = {};

function familyHref() {
  const sel = document.querySelector('select[data-key=icon_fallback]');
  return sel ? sel.value : 'tabler';
}

/**
 * Icon sources.
 *
 * Most families ship a stylesheet whose rules declare the icons, so the names
 * are read straight out of it. Three do not: feather, octicons and akar publish
 * their glyphs as SVG/JS only, so for those the name list is built from the
 * package's own icon index instead. Either way no icon name is hardcoded here.
 */
// Sources per family. The keys here are the icon_fallback select's option
// values, so familyHref() always finds a match.
const FAMILIES = {
  'bootstrap':       { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css' },
  'feather':         { kind: 'json', url: 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/icons.json', prefix: 'feather feather-' },
  'material':         { kind: 'list', url: 'https://raw.githubusercontent.com/google/material-design-icons/master/font/MaterialIcons-Regular.codepoints', prefix: '' },
  'material-light':   { kind: 'list', url: 'https://raw.githubusercontent.com/google/material-design-icons/master/font/MaterialIcons-Regular.codepoints', prefix: '' },
  'lucide':          { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/lucide-static@0.462.0/font/lucide.css', prefix: 'icon-' },
  'fontawesome':     { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css', prefix: 'fa-solid fa-' },
  'tabler':          { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css', prefix: 'ti-' },
  'remix-outline':   { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css', prefix: 'ri-' },
  'remix-solid':     { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css', prefix: 'ri-' },
  'octicons':        { kind: 'json', url: 'https://cdn.jsdelivr.net/npm/@primer/octicons@19.11.0/build/data.json', prefix: 'octicon octicon-' },
  'eva-outline':     { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/eva-icons@1.1.3/style/eva-icons.css', prefix: 'eva eva-' },
  'eva-solid':       { kind: 'css',  url: 'https://cdn.jsdelivr.net/npm/eva-icons@1.1.3/style/eva-icons.css', prefix: 'eva eva-' },
};

/** Pull icon class names out of a stylesheet's ::before / :before rules. */
function parseCssIcons(text, prefix) {
  const names = new Set();
  const re = /\.((?:[a-z0-9_-]+:)*[a-z][a-z0-9_-]+)::?before\s*\{([^}]*)\}/gi;
  let m;
  while ((m = re.exec(text))) {
    // Without a content declaration it is a layout rule, not an icon.
    if (!/content\s*:/i.test(m[2])) continue;
    names.add(m[1]);
  }
  // Some families put every icon in one selector list; take only single
  // class-looking selectors from it.
  if (!names.size) {
    const listRe = /([^{}]+)\{([^}]*content\s*:[^}]*)\}/gi;
    while ((m = listRe.exec(text))) {
      m[1].split(',').forEach(sel => {
        const name = sel.trim().replace(/^\./, '').replace(/::?before$/i, '');
        if (/^[a-z][a-z0-9_-]{1,40}$/i.test(name) && name.includes('-')) names.add(name);
      });
    }
  }

  // Keep only this family's own icons: a family sheet also styles buttons,
  // badges and form controls. Families that need two classes (".eva eva-home")
  // still declare a single class, so compare on the wanted prefix's last token.
  const want = (prefix || '').trim().split(/\s+/).pop();
  const out = want ? [...names].filter(n => n.split(' ').pop().startsWith(want)) : [...names];
  return [...new Set(out)].sort();
}

/** Pull icon names out of a package's JSON/JS index for SVG-only families. */
/** A plain newline-delimited name list, one per line. */
function parseNameList(text, def) {
  return text.split(/\r?\n/)
    .map(l => l.trim().split(/\s+/)[0])
    .filter(n => /^[a-z][a-z0-9_]{1,40}$/i.test(n))
    .map(n => (def.prefix ? def.prefix + n : n));
}

function parseJsonIcons(text, def) {
  const out = new Set();
  if (def.match) {
    // e.g. a JS bundle that references each glyph as "ai-airplay"
    const re = new RegExp(def.match.source, def.match.flags);
    let m;
    while ((m = re.exec(text))) out.add(def.prefix + m[1]);
  } else {
    // Feather: {"airplay": {...}} — one key per icon.
    const keys = Object.keys(JSON.parse(text));
    keys.forEach(k => out.add(def.prefix + k));
  }
  return [...out].sort();
}

async function loadIconSet(family) {
  if (ICON_SETS[family]) return ICON_SETS[family];
  const def = FAMILIES[family];
  if (!def) return (ICON_SETS[family] = []);

  let body;
  try {
    // No credentials; the response is only read for icon names.
    body = await fetch(def.url, { credentials: 'omit' }).then(r => r.text());
  } catch (e) {
    return (ICON_SETS[family] = []);
  }

  let list = [];
  try {
    list = def.kind === 'css'   ? parseCssIcons(body, def.prefix)
          : def.kind === 'json' ? parseJsonIcons(body, def)
          :                       parseNameList(body, def);
  } catch (e) {
    list = [];
  }
  ICON_SETS[family] = list;
  return list;
}

function iconPickerFor(key) {
  const cur = state[key] || '';
  const family = familyHref();

  const overlay = document.createElement('div');
  overlay.className = 'picker-overlay';
  overlay.innerHTML = `
    <div class="picker" role="dialog" aria-label="Choose an icon">
      <header>
        <input type="search" placeholder="Search ${esc(family)} icons…" autocomplete="off">
        <span class="meta">loading…</span>
        <button type="button" class="x" aria-label="Close">&times;</button>
      </header>
      <div class="grid"></div>
    </div>`;

  const search = overlay.querySelector('input');
  const meta = overlay.querySelector('.meta');
  const grid = overlay.querySelector('.grid');
  let all = [];

  const paint = () => {
    const q = search.value.trim().toLowerCase();
    const list = q ? all.filter(n => n.includes(q)) : all;
    meta.textContent = list.length + (q ? ` of ${all.length}` : '') + ' icons';
    // Cap the first paint: a family ships thousands of rules and painting them
    // all at once locks the tab.
    const shown = list.slice(0, 600);
    grid.innerHTML = shown.map(n =>
      `<button type="button" class="cell" data-name="${esc(n)}" title="${esc(n)}">
         <i class="${esc(n)}"></i><span>${esc(n)}</span>
       </button>`).join('')
      + (list.length > shown.length
        ? `<div class="more">${list.length - shown.length} more — refine the search</div>` : '');
  };

  overlay.querySelector('.x').onclick = () => overlay.remove();
  overlay.onclick = e => { if (e.target === overlay) overlay.remove(); };
  search.oninput = paint;

  grid.onclick = e => {
    const btn = e.target.closest('.cell');
    if (!btn) return;
    const input = document.querySelector(`[data-key="${CSS.escape(key)}"]`);
    if (input) input.value = btn.dataset.name;   // through the DOM, so the
    state[key] = btn.dataset.name;               // change handler sees it
    activePreset = null;
    sync(); paintIconPreviews(); draw();
    overlay.remove();
  };

  document.body.appendChild(overlay);
  search.focus();

  loadIconSet(family).then(list => {
    all = list;
    if (!all.length) {
      meta.textContent = 'could not read the icon set — type a class name instead';
      grid.innerHTML = '';
      return;
    }
    paint();
  });
}

function buildPanes() {
  const box = $('#panes');
  box.innerHTML = '';

  // Presets live above the schema groups, not inside one of them.
  const pre = document.createElement('div');
  pre.className = 'group' + (GROUPS.length ? '' : ' on');
  pre.innerHTML = `<div class="gh"><h3>Presets</h3></div><div class="presets" id="presets"></div>`;
  box.appendChild(pre);

  GROUPS.forEach((g, i) => {
    const el = document.createElement('div');
    el.className = 'group' + (i === 0 ? ' on' : '');
    el.innerHTML =
      `<div class="gh"><h3>${esc(g.label)}</h3></div>` +
      (g.hint ? `<div class="ghint">${esc(g.hint)}</div>` : '') +
      g.fields.filter(f => !f.hidden).map(fieldHtml).join('');
    box.appendChild(el);
  });

  buildPresets();
}

function buildPresets() {
  const box = $('#presets');
  box.innerHTML = '';
  for (const [name, c] of Object.entries(PRESETS)) {
    const b = document.createElement('button');
    b.className = 'preset';
    b.dataset.preset = name;
    b.innerHTML =
      `<span class="dots">${[c.accent,c.accent2,c.bg_alt,c.bg].map(x=>`<b style="background:${x}"></b>`).join('')}</span>
       <span class="mini" style="background:linear-gradient(135deg,${c.accent},${c.accent2})"></span>${name}`;
    b.onclick = () => { Object.assign(state, c); activePreset = name; schedule(); };
    box.appendChild(b);
  }
}

/* ── sync ─────────────────────────────────────────────────────────── */

function sync() {
  $$('[data-key]').forEach(el => {
    const k = el.dataset.key;
    if (!(k in state)) return;
    const v = state[k];
    if (el.type === 'checkbox') el.checked = String(v) === '1';
    else if (el.type === 'color') { if (isHex(v)) el.value = v; }
    else el.value = v;
  });
  $$('[data-hex]').forEach(el => { el.textContent = state[el.dataset.hex] ?? ''; });
  $$('.srow').forEach(row => {
    const k = row.dataset.slider;
    $('.val', row).textContent = state[k] ?? '';
  });
  $$('.preset').forEach(b => b.classList.toggle('on', b.dataset.preset === activePreset));
}

function draw() {
  // The preview frames the real client panel, so the admin sees the actual
  // dashboard rather than a mock-up. The settings ride along as query params
  // and are read by the wrapper, so nothing is written on preview.
  const p = new URLSearchParams();
  p.set('page', previewPage);
  for (const [k, v] of Object.entries(state)) p.set(k, v);
  $('#frame').src = '/extensions/nyriel/preview?' + p.toString();
}

function schedule() {
  sync();
  if ($('#reload').checked) { clearTimeout(timer); timer = setTimeout(draw, 180); }
}

function paintIconPreviews() {
  $$('[data-prev]').forEach(el => {
    const v = (state[el.dataset.prev] || '').trim();
    el.innerHTML = v
      ? '<i class="' + esc(v) + '"></i> ' + esc(v)
      : '<span style="opacity:.5">no icon set</span>';
  });
}

function touched() { activePreset = null; paintIconPreviews(); schedule(); }

/* ── events ───────────────────────────────────────────────────────── */

document.addEventListener('input', e => {
  const el = e.target.closest('[data-key]');
  if (!el) return;
  state[el.dataset.key] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
  if (el.type === 'color') {
    const t = $(`input[type=text][data-key="${el.dataset.key}"][data-mirror]`);
    if (t) t.value = el.value;
  }
  if (el.dataset.mirror && isHex(el.value)) {
    const sw = $(`input[type=color][data-key="${el.dataset.key}"]`);
    if (sw) sw.value = el.value;
  }
  touched();
});
document.addEventListener('change', e => {
  const el = e.target.closest('[data-key]');
  if (!el) return;
  state[el.dataset.key] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
  touched();
});

document.addEventListener('click', ev => {
  const btn = ev.target.closest('[data-pick]');
  if (btn) iconPickerFor(btn.dataset.pick);
});

$('#search').addEventListener('input', e => {
  const q = e.target.value.trim().toLowerCase();
  if (!q) {
    $$('[data-search]').forEach(el => { el.style.display = ''; });
    return;
  }
  $$('[data-search]').forEach(el => {
    el.style.display = el.dataset.search.includes(q) ? '' : 'none';
  });
  // Jump to the first group that has a visible match.
  const first = $$('.group').find(g => [...g.querySelectorAll('[data-search]')].some(x => x.style.display !== 'none'));
  if (first) {
    const idx = $$('.group').indexOf(first);
    $$('#snav button').forEach((x, j) => x.classList.toggle('on', j === idx));
    first.classList.add('on');
  }
});

$('#page').onchange = e => { previewPage = e.target.value; draw(); };

$$('.vp button').forEach(b => b.onclick = () => {
  $$('.vp button').forEach(x => x.classList.toggle('on', x === b));
  $('#frame').style.width = b.dataset.w;
});

async function save() {
  const btn = $('#save');
  btn.disabled = true;
  status('Saving…');
  try {
    // Form-encoded with _token in the body: Laravel's VerifyCsrfToken reads it
    // there directly, avoiding the JSON/header negotiation entirely.
    const form = new URLSearchParams({ _token: csrf });
    for (const [k, v] of Object.entries(state)) form.append(k, v);

    const res = await fetch('/admin/extensions/nyriel/designer/save', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: form,
    });
    const json = await res.json().catch(() => ({}));
    if (json.ok) {
      status(`Saved ✓ (${json.count ?? Object.keys(state).length} settings)`, 'ok');
      draw();
    } else if (res.status === 419) {
      status('Session expired — reload the page and save again', 'err');
    } else if (res.status === 422) {
      status('Rejected: ' + fmtErrors(json), 'err');
    } else {
      status('Server error (' + res.status + ') — check the panel log', 'err');
    }
  } catch (e) {
    status('Save failed: ' + e.message, 'err');
  }
  btn.disabled = false;
}

// One-shot actions post their own flag and nothing else.
document.addEventListener('click', async ev => {
  const btn = ev.target.closest('[data-action]');
  if (!btn) return;
  if (!confirm(`Run "${btn.textContent.trim()}"? This cannot be undone.`)) return;

  const key = btn.dataset.action;
  btn.disabled = true;
  try {
    const res = await fetch('/admin/extensions/nyriel/designer/save', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ _token: csrf, [key]: '1' }),
    });
    const json = await res.json().catch(() => ({}));
    if (json.ok) {
      status('Factory reset ✓', 'ok');
      state = { ...defaults };
      sync(); draw();
    } else {
      status('Failed (' + res.status + '): ' + fmtErrors(json), 'err');
    }
  } catch (e) {
    status('Failed: ' + e.message, 'err');
  }
  btn.disabled = false;
});

$('#save').onclick = save;

$('#resetAll').onclick = async () => {
  if (!confirm('Reset every Nyriel setting to its default? This cannot be undone.')) return;
  const btn = $('#resetAll');
  btn.disabled = true;
  try {
    const form = new URLSearchParams({ _token: csrf, reset: '1' });
    const res = await fetch('/admin/extensions/nyriel/designer/save', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: form,
    });
    const json = await res.json();
    if (json.ok) { state = { ...defaults }; activePreset = 'Midnight'; status('Factory reset ✓', 'ok'); sync(); draw(); }
    else { status('Reset failed', 'err'); }
  } catch (e) { status('Reset failed: ' + e.message, 'err'); }
  btn.disabled = false;
};

/* ── init ─────────────────────────────────────────────────────────── */

(async function init() {
  buildNav();
  buildPanes();

  defaults = {};
  GROUPS.forEach(g => g.fields.forEach(f => {
    if (f.type !== 'action') defaults[f.key] = f.default;
  }));
  // An action's flag is posted on demand only.
  GROUPS.forEach(g => g.fields.filter(f => f.type === 'action').forEach(f => { delete state[f.key]; }));

  try {
    const res = await fetch('/admin/extensions/nyriel/designer/load', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const json = await res.json();
    if (!json.ok) { status(json.error || 'Could not load settings', 'err'); state = { ...defaults }; }
    else { state = { ...defaults, ...json.values }; status('Loaded from database', 'ok'); }
  } catch (e) {
    status('Load failed: ' + e.message, 'err');
    state = { ...defaults };
  }

  sync();
  paintIconPreviews();
  draw();
})();
</script>
</body>
</html>
