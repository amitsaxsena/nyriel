<?php
/**
 * Nyriel — client theme wrapper.
 *
 * Renders the theme shell, the icon-family loader, pattern overlays, the
 * alert bar, and the client-side behaviour (keyboard shortcuts, context
 * menus, file-view toggle, animated counters). Every value is read through the
 * schema so this file never hardcodes a default that the designer can change.
 */

$a = fn (string $k, $d = '') => $blueprint->dbGet('nyriel', $k, $d);
$on = fn (string $k, bool $d = false) => ((string) $a($k, $d ? '1' : '0')) === '1';

// Signed-out visitors get the bare auth screen; the rail and body padding only
// make sense once there's an account to navigate to.
$signedIn = auth()->check();

$enabled      = $on('enabled', true);
$adminEnabled = $on('admin_enabled', true);
$hideSidebar  = $on('hide_sidebar');
$animations   = $on('animations', true);
$pageIndexing = $on('page_indexing');

$bg        = $a('bg', '#0a0f1a');
$bgAlt     = $a('bg_alt', '#111a2b');
$bgHover   = $a('bg_hover', '#18233a');
$sidebarBg = $a('sidebar_bg', '#0d1424');
$accent    = $a('accent', '#3b82f6');
$accent2   = $a('accent2', '#06b6d4');
$text      = $a('text', '#e2e8f0');
$textDim   = $a('text_dim', '#94a3b8');
$border    = $a('border', 'rgba(148, 163, 184, 0.15)');

$radius      = (int) $a('radius', 12);
$blur        = (int) $a('blur', 16);
$sideWidth   = (int) $a('side_width', 72);
$sideRadius  = (int) $a('sidebar_border_radius', 12);
$wide        = $on('sidebar_full') || $a('sidebar_mode', 'rail') === 'full';
$railWidth   = $wide ? 200 : $sideWidth;
$off         = $hideSidebar ? '0px' : $railWidth . 'px';

$fontFamily = trim($a('font_family', ''));
$fontStack  = $fontFamily !== ''
    ? $fontFamily
    : 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
$fontSize = (int) $a('font_size', 14);

// Icon family. Each entry is [stylesheet URL, prefix class]; families that
// ship no stylesheet use inline SVG instead (see $iconSvg below).
// Every family below is referenced by its full class name in $iconNames, so the
// table carries only the stylesheet URL. (Bootstrap's `bi bi-house` and Eva's
// `eva eva-home` are the two that genuinely need two classes; those glyph
// names in $iconNames already spell out both.)
$families = [
    'bootstrap'       => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'feather'         => 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.css',
    'lucide'          => 'https://cdn.jsdelivr.net/npm/lucide-static@0.462.0/font/lucide.css',
    'material'        => 'https://fonts.googleapis.com/icon?family=Material+Icons',
    'material-light'  => 'https://fonts.googleapis.com/icon?family=Material+Icons+Light',
    'fontawesome'     => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css',
    'tabler'          => 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css',
    'remix-outline'   => 'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css',
    'remix-solid'     => 'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css',
    'octicons'        => 'https://cdn.jsdelivr.net/npm/@primer/octicons@19.11.0/build/index.css',
    'eva-outline'     => 'https://cdn.jsdelivr.net/npm/eva-icons@1.1.3/style/eva-icons.css',
    'eva-solid'       => 'https://cdn.jsdelivr.net/npm/eva-icons@1.1.3/style/eva-icons.css',
];
$family      = array_key_exists($a('icon_fallback', 'bootstrap'), $families)
    ? $a('icon_fallback', 'bootstrap')
    : 'bootstrap';
$iconCdn     = $a('icon_cdn', 'jsdelivr');
$iconWeight  = $a('icon_weight', 'normal');
$iconScale   = (float) $a('icon_scale', 1.0);

if ($iconCdn === 'unpkg') {
    $families[$family] = str_replace('cdn.jsdelivr.net/npm', 'unpkg.com', $families[$family]);
} elseif ($iconCdn === 'local') {
    $families[$family] = null; // signals inline SVG
}
$iconCss = $families[$family] ?? null;

// Default glyph per item, per family. Blank entries fall back to the inline SVG
// set below, which is why the CDN is optional.
$iconNames = [
    // One full class name per family, in $families order:
    // bootstrap, feather, lucide, material, material-light, fontawesome,
    // tabler, remix-outline, remix-solid, octicons, eva-outline, eva-solid.
    // No prefix is added at render time; each name is the complete class.
    'home'      => ['bi bi-house', 'feather feather-home', 'icon-house', 'home', 'home-light', 'fa-solid fa-house', 'ti-home', 'ri-home-line', 'ri-home-fill-line', 'octicon octicon-home-fill-24', 'eva eva-home', 'eva eva-home'],
    'account'   => ['bi bi-person', 'feather feather-user', 'icon-user', 'person', 'person-light', 'fa-solid fa-user', 'ti-user', 'ri-user-line', 'ri-user-fill-line', 'octicon octicon-person-24', 'eva eva-person', 'eva eva-person'],
    'servers'   => ['bi bi-hdd-stack', 'feather feather-server', 'icon-server', 'dns', 'dns-light', 'fa-solid fa-server', 'ti-server', 'ri-server-line', 'ri-server-fill-line', 'octicon octicon-server-24', 'eva eva-cube', 'eva eva-cube'],
    'databases' => ['bi bi-database', 'feather feather-database', 'icon-database', 'storage', 'storage-light', 'fa-solid fa-database', 'ti-database', 'ri-database-2-line', 'ri-database-2-fill-line', 'octicon octicon-database-24', 'eva eva-hard-drive', 'eva eva-hard-drive'],
    'settings'  => ['bi bi-sliders', 'feather feather-sliders', 'icon-sliders-horizontal', 'settings', 'settings-light', 'fa-solid fa-sliders', 'ti-settings', 'ri-equalizer-3-line', 'ri-equalizer-3-fill-line', 'octicon octicon-gear-24', 'eva eva-options', 'eva eva-options'],
    'logout'    => ['bi bi-box-arrow-right', 'feather feather-log-out', 'icon-log-out', 'logout', 'logout-light', 'fa-solid fa-right-from-bracket', 'ti-logout', 'ri-logout-box-r-line', 'ri-logout-box-r-fill-line', 'octicon octicon-sign-out-24', 'eva eva-logout', 'eva eva-logout'],
];
// Inline SVG fallbacks, used when the icon CDN is disabled or a family has no
// stylesheet. One path set per item, stroked to match the icon look.
$nyrielSvg = [
    'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
    'account' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
    'servers' => '<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="14" width="18" height="7" rx="2"/>',
    'databases' => '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
    'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2 2 2 0 1 1-4 0 1.7 1.7 0 0 0-2.9-1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 3 15a2 2 0 1 1 0-4 1.7 1.7 0 0 0 1.2-2.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.7 1.7 0 0 0 9 4.6 2 2 0 1 1 13 4.6a1.7 1.7 0 0 0 2.9 1.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A1.7 1.7 0 0 0 21 11a2 2 0 1 1 0 4z"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
];

$order = array_keys($iconNames);
$famIndex = (int) array_search($family, array_keys($families), true);
$famIndex = $famIndex === false ? 0 : $famIndex;

// Nav structure. `route` is a path template the client JS matches against the
// panel's own links; %s is filled with the current server identifier, or the
// template is matched as a prefix. Visibility is decided in two stages: the
// admin's toggle here, and whether the panel renders a link for this user there.
$navGroups = [
    [
        'id'    => 'general',
        'label' => 'General',
        'items' => [
            ['key' => 'home',    'label' => 'Home',   'toggle' => 'sidebar_home',    'icon' => 'home',      'route' => '/'],
        ],
    ],
    [
        'id'    => 'server',
        'label' => 'Server Management',
        'items' => [
            ['key' => 'serverTerminal',   'label' => 'Console',   'toggle' => 'sidebar_server_terminal',   'icon' => 'servers',   'route' => '/server/%s'],
            ['key' => 'serverFiles',      'label' => 'Files',     'toggle' => 'sidebar_server_files',      'icon' => 'servers',   'route' => '/server/%s/files'],
            ['key' => 'serverDatabases',  'label' => 'Databases', 'toggle' => 'sidebar_server_databases',  'icon' => 'databases', 'route' => '/server/%s/databases'],
            ['key' => 'serverSchedules',  'label' => 'Schedules', 'toggle' => 'sidebar_server_schedules',  'icon' => 'settings',  'route' => '/server/%s/schedules'],
            ['key' => 'serverUsers',      'label' => 'Subusers',  'toggle' => 'sidebar_server_users',      'icon' => 'servers',   'route' => '/server/%s/users'],
            ['key' => 'serverBackups',    'label' => 'Backups',   'toggle' => 'sidebar_server_backups',    'icon' => 'databases', 'route' => '/server/%s/backups'],
            ['key' => 'serverNetwork',    'label' => 'Network',   'toggle' => 'sidebar_server_network',    'icon' => 'settings',  'route' => '/server/%s/network'],
            ['key' => 'serverStartup',    'label' => 'Startup',   'toggle' => 'sidebar_server_startup',    'icon' => 'settings',  'route' => '/server/%s/startup'],
            ['key' => 'serverSettings',   'label' => 'Settings',  'toggle' => 'sidebar_server_settings',   'icon' => 'settings',  'route' => '/server/%s/settings'],
            ['key' => 'serverActivity',   'label' => 'Activity',  'toggle' => 'sidebar_server_activity',   'icon' => 'servers',   'route' => '/server/%s/activity'],
            ['key' => 'serverMore',       'label' => 'More',      'toggle' => 'sidebar_server_more',       'icon' => 'settings',  'route' => '/server/%s'],
        ],
    ],
    [
        'id'    => 'account',
        'label' => 'Account',
        'items' => [
            ['key' => 'accountAccount',  'label' => 'Profile',  'toggle' => 'sidebar_account',          'icon' => 'account',  'route' => '/account'],
            ['key' => 'accountApi',      'label' => 'API keys', 'toggle' => 'sidebar_account_api',      'icon' => 'settings', 'route' => '/account/api'],
            ['key' => 'accountSsh',      'label' => 'SSH keys', 'toggle' => 'sidebar_account_ssh',      'icon' => 'settings', 'route' => '/account/ssh'],
            ['key' => 'accountActivity', 'label' => 'Activity', 'toggle' => 'sidebar_account_activity', 'icon' => 'servers',  'route' => '/account/activity'],
            ['key' => 'accountMore',     'label' => 'More',     'toggle' => 'sidebar_account_more',     'icon' => 'settings', 'route' => '/account'],
            ['key' => 'admin',           'label' => 'Admin',    'toggle' => 'sidebar_admin',            'icon' => 'settings', 'route' => '/admin'],
        ],
    ],
];
$separators = $on('sidebar_separators', true);

// Flatten for the config island and the keyboard handler.
$nav = array_merge(...array_column($navGroups, 'items'));

// Patterns are pure CSS gradients — no asset request, no CDN dependency.
$patterns = [
    'dots'  => 'radial-gradient(currentColor 1px, transparent 1px)',
    'grid'  => 'linear-gradient(currentColor 1px, transparent 1px), linear-gradient(90deg, currentColor 1px, transparent 1px)',
    'diag'  => 'repeating-linear-gradient(45deg, currentColor 0 1px, transparent 1px 8px)',
    'waves' => 'repeating-radial-gradient(circle at 50% 50%, currentColor 0 1px, transparent 1px 10px)',
    'noise' => 'repeating-conic-gradient(currentColor 0 25%, transparent 0 50%)',
    'hex'   => 'repeating-linear-gradient(60deg, currentColor 0 1px, transparent 1px 12px), repeating-linear-gradient(-60deg, currentColor 0 1px, transparent 1px 12px)',
];
$pattern      = $a('background_magic', '');
$patternSize  = (int) $a('background_magicsize', 24);
$patternAlpha = (int) $a('background_magicopacity', 30);

$authPattern      = $a('auth_background_magic', '');
$authPatternAlpha = $patternAlpha;

// Alerts
$showAlert   = $on('alert');
$alertText   = $a('alert_text', '');
$alertIcon   = $a('alert_icon', '');
$alertPos    = $a('alert_position', 'top');
$alertMd     = $on('alert_markdown', true);
$alertTmo    = (int) $a('alert_timeout', 0);
$alertDismis = $on('alert_dismiss');

$showWatermark = $on('watermark');
$showAuthMark  = $on('watermark_auth', true);
$authLogo      = trim($a('auth_customlogo', ''));
$customLogo    = trim($a('sidebar_customlogo', ''));

$shortcuts = $on('keyboard_shortcuts');
$keybinds  = [
    'prev'   => $a('keybind_nav_prev', 'k'),
    'next'   => $a('keybind_nav_next', 'j'),
    'sidebar'=> $a('keybind_sidebar', 'b'),
    'search' => $a('keybind_search', '/'),
    'console'=> $a('keybind_console', 'c'),
    'escape' => $a('keybind_escape', 'Escape'),
];

$ctxFiles   = $on('contextmenu_files', true);
$ctxServers = $on('contextmenu_servers', true);
$ctxSidebar = $on('contextmenu_sidebar', true);
$ctxPos     = $a('contextmenu_pos', 'cursor');

$cfg = [
    'nav'    => $nav,
    'family' => $family, 'weight' => $iconWeight,
    'scale' => $iconScale, 'wide' => $wide, 'animate' => $animations,
    'transparency' => $on('dashboard_transparency', true),
    'graphs' => $a('graph_style', 'area'),
    'stats' => $on('stat_animation', true),
    'fileView' => $a('file_view', ''),
    'fileGrid' => $on('file_grid'),
    'serverList' => $a('server_list', 'cards'),
    'status' => [
        'online'  => $a('palette_status_online', '#34d399'),
        'offline' => $a('palette_status_offline', '#f87171'),
        'starting'=> $a('palette_status_starting', '#fbbf24'),
    ],
    'pill' => $a('statusgradient_style', 'default'),
    'keys' => $keybinds, 'shortcuts' => $shortcuts,
    'keyHints' => $on('keybind_icons'),
    'ctx' => ['files'=>$ctxFiles,'servers'=>$ctxServers,'sidebar'=>$ctxSidebar,'pos'=>$ctxPos],
    'alert' => ['md'=>$alertMd,'timeout'=>$alertTmo,'dismiss'=>$alertDismis,'pos'=>$alertPos],
];
?>
@if($enabled)
@if($iconCss)
<link rel="stylesheet" href="{!! $iconCss !!}" crossorigin="anonymous">
@endif
<link rel="stylesheet" href="{{ route('nyriel.css') }}">
@if(!$pageIndexing)
<meta name="robots" content="noindex, nofollow">
@endif
<!-- Styling lives in /themes/pterodactyl/css/nyriel.css, generated from the same schema. -->

@if($signedIn && !$hideSidebar)
  <nav id="nyriel-rail" @if($ctxSidebar) data-nyriel-ctx="sidebar" @endif>
    @if($wide)
      @if($customLogo !== '')
        <img class="nyriel-brand" src="{{ $customLogo }}" alt="" style="height:34px;width:auto;object-fit:contain;">
      @else
        <span class="nyriel-brand">{{ config('app.name', 'Nyriel') }}</span>
      @endif
    @endif

    {{-- One button per entry. `data-nav-route` is matched against the panel's own
         links at runtime (nyriel.js), so a button only appears when the panel
         actually renders a link this user may follow — the theme never
         advertises a page the user cannot open. --}}
    @foreach($navGroups as $group)
      @if($group['items'] === [])
        @continue
      @endif

      <div class="cat" data-cat="{{ $group['id'] }}">{{ $group['label'] }}</div>

      @foreach($group['items'] as $item)
        @continue($item['toggle'] && !$on($item['toggle'], true))
        <button type="button"
                class="nyriel-nav"
                data-nyriel-nav="{{ $item['key'] }}"
                data-nav-route="{{ $item['route'] }}"
                data-nav-icon="{{ $item['icon'] }}"
                data-nav-label="{{ $item['label'] }}"
                title="{{ $item['label'] }}"
                data-nav-ctx="{{ $group['id'] }}" @if($ctxSidebar) data-nyriel-ctx="{{ $group['id'] === 'server' ? 'server' : 'sidebar' }}" @endif>
          <span class="ic">
            @php
              $override = trim($a('icon_' . $item['key'], ''));
              $glyph = $iconNames[$item['icon']][$famIndex] ?? $iconNames[$item['icon']][0] ?? '';
            @endphp
            @if($override !== '')
              <i class="{{ $override }}"></i>
            @elseif($iconCss && $glyph !== '')
              <i class="{{ $glyph }}"></i>
            @else
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $nyrielSvg[$item['icon']] ?? $nyrielSvg['settings'] !!}</svg>
            @endif
          </span>
          @if($wide)<span>{{ $item['label'] }}</span>@endif
        </button>
      @endforeach

      @if($separators && $group['id'] !== 'general')<div class="sep"></div>@endif
    @endforeach

    {{-- Custom links. Every URL is matched against an http(s) allowlist, so a
         stored value can never become a javascript: or data: link. --}}
    @php
      $linkDefs = [
          'weblink_support'        => 'Support',
          'weblink_billing'        => 'Billing',
          'weblink_status'         => 'Status',
          'weblink_social_discord' => 'Discord',
          'weblink_social_github'  => 'GitHub',
      ];
      $links = [];
      if ($on('website_links', true)) {
          foreach ($linkDefs as $key => $label) {
              $url = trim($a($key, ''));
              if ($url !== '' && preg_match('#^https?://[^\s"\'<>]+$#', $url)) {
                  $links[] = ['label' => $label, 'url' => $url];
              }
          }
      }
    @endphp

    @if($links !== [])
      <div class="cat" data-cat="links">Links</div>
      @foreach($links as $link)
        <a class="nyriel-nav nyriel-link"
           href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
           title="{{ $link['label'] }}"
           @if($ctxSidebar) data-nyriel-ctx="sidebar" @endif>
          <span class="ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
          </span>
          @if($wide)<span>{{ $link['label'] }}</span>@endif
        </a>
      @endforeach
    @endif
  </nav>
@endif

@if($showAlert && trim($alertText) !== '')
  <div class="nyriel-alert" data-pos="{{ $alertPos }}" role="status">
    @if($alertIcon !== '')
      <span class="ic"><i class="{{ $alertIcon }}"></i></span>
    @endif
    <div class="body" id="nyriel-alert-body" data-md="{{ $alertMd ? '1' : '0' }}"></div>
    @if($alertDismis)
      <button class="x" type="button" aria-label="Dismiss">&times;</button>
    @endif
  </div>
@endif

<div id="nyriel-ctx" role="menu"></div>

<script id="nyriel-cfg" type="application/json">@json($cfg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
<textarea id="nyriel-alert-src" hidden>@json($alertText, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</textarea>
<script src="/extensions/nyriel/nyriel.js" defer></script>
@endif
