<?php

namespace Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Client;

/**
 * Nyriel — client stylesheet builder.
 *
 * Produces the panel's theme CSS from the extension schema and serves it on a
 * real route that the panel links with Theme::css() — Pterodactyl's own
 * supported extension point, rather than a <style> tag injected at runtime.
 *
 * The stock Pterodactyl theme files load under /themes/pterodactyl/, so a
 * theme normally replaces those files. A Blueprint extension cannot do that
 * (it must not touch the panel's source), so the stylesheet is generated on
 * demand and linked alongside the originals. That keeps the extension
 * self-contained while still using the same loading mechanism.
 *
 * An ETag derived from the settings makes a save bust the browser cache.
 */
class NyrielStylesheet
{
    public function __construct(private BlueprintClientLibrary $blueprint) {}

    /** Setting key => default, flattened out of the schema. */
    public function defaults(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $cached = [];
        foreach (require base_path('.blueprint/extensions/nyriel/private/schema.php') as $group) {
            if (isset($group['fields'])) {
                foreach ($group['fields'] as $key => $field) {
                    $cached[$key] = $field['default'];
                }
            }
        }
        return $cached;
    }

    public function get(string $key, $default = '')
    {
        $schema = $this->defaults();
        return $this->blueprint->dbGet('nyriel', $key, $default ?? ($schema[$key] ?? ''));
    }

    private function on(string $key, bool $default = false): bool
    {
        return ((string) $this->get($key, $default ? '1' : '0')) === '1';
    }

    public function etag(): string
    {
        return substr(md5(implode('|', array_map(
            fn ($k) => (string) $this->get($k, '~'),
            array_keys($this->defaults())
        ))), 0, 16);
    }

    public function css(): string
    {
        $get = fn (string $k, $d = '') => (string) $this->get($k, $d);

        $bg        = $get('bg', '#0a0f1a');
        $bgAlt     = $get('bg_alt', '#111a2b');
        $bgHover   = $get('bg_hover', '#18233a');
        $sidebarBg = $get('sidebar_bg', '#0d1424');
        $accent    = $get('accent', '#3b82f6');
        $accent2   = $get('accent2', '#06b6d4');
        $text      = $get('text', '#e2e8f0');
        $textDim   = $get('text_dim', '#94a3b8');
        $border    = $get('border', 'rgba(148, 163, 184, 0.15)');

        $radius    = (int) $get('radius', 12);
        $blur      = (int) $get('blur', 16);
        $sideWidth = (int) $get('side_width', 72);
        $sideRad   = (int) $get('sidebar_border_radius', 12);
        $wide      = $this->on('sidebar_full') || $get('sidebar_mode', 'rail') === 'full';
        $hideBar   = $this->on('hide_sidebar');
        $railW     = $wide ? 200 : $sideWidth;
        $railLeft  = $hideBar ? '0px' : $railW . 'px';

        $fontFamily = trim($get('font_family', ''));
        $fontStack  = $fontFamily !== ''
            ? $fontFamily
            : 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
        $fontSize = (int) $get('font_size', 14);

        $transparency = $this->on('dashboard_transparency', true);
        $animations   = $this->on('animations', true);

        $css = "/* Nyriel — generated from the extension schema. Do not edit. */\n";
        $css .= ":root {\n";
        foreach ([
            '--ny-bg'          => $bg,
            '--ny-bg-alt'      => $bgAlt,
            '--ny-bg-hover'    => $bgHover,
            '--ny-sidebar'     => $sidebarBg,
            '--ny-accent'      => $accent,
            '--ny-accent2'     => $accent2,
            '--ny-text'        => $text,
            '--ny-dim'         => $textDim,
            '--ny-border'      => $border,
            '--ny-radius'      => $radius . 'px',
            '--ny-side-radius' => $sideRad . 'px',
            '--ny-blur'        => $blur . 'px',
            '--ny-rail'        => $railLeft,
            '--ny-rail-w'      => $railW . 'px',
            '--ny-side-hover'  => $get('sidebar_bg_hover', '#16203a'),
            '--ny-side-active' => $get('sidebar_bg_active', '#1f2c4d'),
            '--ny-st-online'   => $get('palette_status_online', '#34d399'),
            '--ny-st-offline'  => $get('palette_status_offline', '#f87171'),
            '--ny-st-start'    => $get('palette_status_starting', '#fbbf24'),
            '--ny-icon-scale'  => (float) $get('icon_scale', 1.0),
            '--ny-pattern'     => (int) $get('background_magicsize', 24) . 'px',
            '--ny-pattern-op'  => round((int) $get('background_magicopacity', 30) / 100, 2),
            '--ny-font'        => $fontStack,
        ] as $k => $v) {
            $css .= "  {$k}: {$v};\n";
        }
        // ── Palette slots ──────────────────────────────────────────────
        // 9 dashboard / 8 sidebar / 8 auth colour slots. Each is an override,
        // not a new colour: an unset slot inherits, so setting one colour never
        // blanks the others.
        $slotFallback = [
            'dashboard' => ['bg' => $bg, 'alt' => $bgAlt, 'text' => $text, 'dim' => $textDim, 'accent' => $accent],
            'sidebar'   => ['bg' => $sidebarBg, 'text' => $text, 'dim' => $textDim, 'accent' => $accent, 'active' => $get('sidebar_bg_active', '#1f2c4d')],
            'auth'      => ['bg' => $bg, 'text' => $text, 'accent' => $accent],
        ];
        foreach (['dashboard' => 9, 'sidebar' => 8, 'auth' => 8] as $which => $count) {
            for ($i = 1; $i <= $count; $i++) {
                foreach ($slotFallback[$which] as $prop => $base) {
                    $override = (string) $this->get("palette_{$which}_{$i}", '');
                    $css .= "  --ny-{$which}-{$i}-{$prop}: " . ($override !== '' ? $override : $base) . ";\n";
                }
            }
        }

        // ── Admin panel, independent of the client theme ───────────────
        $adminOn = $this->on('admin_enabled', true);
        foreach ([
            '--ad-bg'      => $get('admin_bg', $bg),
            '--ad-bg-alt'  => $get('admin_bg_alt', $bgAlt),
            '--ad-side'    => $get('admin_sidebar_bg', $sidebarBg),
            '--ad-accent'  => $get('admin_accent', $accent),
            '--ad-accent2' => $get('admin_accent2', $accent2),
            '--ad-text'    => $get('admin_text', $text),
            '--ad-dim'     => $get('admin_text_dim', $textDim),
            '--ad-radius'  => (int) $get('admin_radius', $radius) . 'px',
            '--ad-blur'    => (int) $get('admin_blur', $blur) . 'px',
            '--ad-width'   => (int) $get('admin_side_width', 250) . 'px',
        ] as $k => $v) {
            $css .= "  {$k}: {$v};\n";
        }

        $css .= "}\n\n";

        // Admin rules ship only when the admin theme is on, so the switch
        // reverts to stock AdminLTE rather than half-theming it.
        if ($adminOn) {
            $ar = function (string $sel, string $body) use (&$css) {
                $css .= $sel . " {\n  " . $body . "\n}\n";
            };
            $ar('html body.adminlte, html body.skin-nyriel',
                'background-color: var(--ad-bg) !important; color: var(--ad-text) !important;');
            $ar('.main-sidebar, .sidebar',
                'background: var(--ad-side) !important; width: var(--ad-width) !important;'
                . "\n  border-right: 1px solid var(--ny-border) !important; border-radius: 0 !important;");
            $ar('.content-wrapper, .content, .main-content', 'background: var(--ad-bg) !important;');
            $ar('.content-wrapper .box, .content-wrapper .small-box, .content-wrapper .card',
                'background: color-mix(in srgb, var(--ad-bg-alt) 88%, transparent) !important;'
                . "\n  border: 1px solid var(--ny-border) !important; border-radius: var(--ad-radius) !important;");
            $ar('.content-wrapper .box-header, .content-wrapper .box-footer',
                'background: transparent !important; border-color: var(--ny-border) !important; color: var(--ad-text) !important;');
            $ar('.main-sidebar .nav-sidebar > li > a, .sidebar-menu a',
                'color: var(--ad-dim) !important; border-radius: var(--ad-radius) !important;');
            $ar('.main-sidebar .nav-sidebar > li.active > a, .sidebar-menu .active a',
                'background: linear-gradient(135deg, var(--ad-accent), var(--ad-accent2)) !important; color: #fff !important;');
            $ar('a, .content-wrapper a:not(.btn)', 'color: var(--ad-accent) !important;');
            $ar('.btn-primary, .btn-flat-primary',
                'background: var(--ad-accent) !important; border-color: var(--ad-accent) !important; color: #fff !important;');
            $ar('.btn-default, .btn-flat',
                'background: var(--ad-bg-alt) !important; border-color: var(--ny-border) !important; color: var(--ad-text) !important;');
            $ar('.content-wrapper input, .content-wrapper select, .content-wrapper textarea',
                'background: var(--ad-bg-alt) !important; border-color: var(--ny-border) !important;'
                . ' color: var(--ad-text) !important; border-radius: calc(var(--ad-radius) - 2px) !important;');
            $ar('.content-wrapper table thead th, .content-wrapper .table-bordered > thead > tr > th',
                'background: var(--ad-bg-alt) !important; border-color: var(--ny-border) !important; color: var(--ad-dim) !important;');
            $ar('.content-wrapper table td, .content-wrapper table tbody tr',
                'border-color: var(--ny-border) !important; color: var(--ad-text) !important;');

            // Compact tables: a line, not a second layout.
            if ($this->on('admin_compact')) {
                $ar('.content-wrapper table td, .content-wrapper table th',
                    'padding-top: 6px !important; padding-bottom: 6px !important;');
            }
            if ($this->on('admin_sticky_sidebar')) {
                $ar('.main-sidebar, .sidebar',
                    'position: sticky !important; top: 0 !important; height: 100vh !important; overflow-y: auto !important;');
            }
        }

        // Emit a rule only when its feature is on, so one stylesheet covers
        // every toggle instead of shipping near-duplicate files.
        $rule = function (string $selector, string $body, bool $enabled = true) use (&$css) {
            if ($enabled) {
                $css .= $selector . " {\n  " . $body . "\n}\n";
            }
        };

        /* Base. Tailwind's .bg-neutral-* utilities share body-level specificity,
           so the selector has to out-specify a class in order to win. */
        $rule(
            'html body, body, body.bg-neutral-800, body.bg-neutral-900, html body.bg-neutral-800, html body.bg-neutral-900',
            "background-color: var(--ny-bg) !important;\n  color: var(--ny-text) !important;\n"
            . "  font-family: var(--ny-font) !important;\n  font-size: {$fontSize}px;"
        );
        $rule('html:not([multitasking])', 'background: var(--ny-bg) !important;');

        /* Panel chrome the stock theme paints white. */
        $rule('.bg-neutral-900, .bg-gray-900', 'background-color: var(--ny-bg) !important;');
        $rule('.bg-neutral-800', 'background-color: var(--ny-bg-alt) !important;');
        $rule('.bg-neutral-700', 'background-color: var(--ny-bg-hover) !important;');
        $rule('input, textarea, select, .form-input',
            'background: var(--ny-bg-alt) !important; border: 1px solid var(--ny-border) !important;'
            . ' color: var(--ny-text) !important; border-radius: calc(var(--ny-radius) - 2px) !important;');
        $rule('input:focus, textarea:focus, select:focus',
            'border-color: var(--ny-accent) !important; outline: none !important;'
            . ' box-shadow: 0 0 0 3px color-mix(in srgb, var(--ny-accent) 20%, transparent) !important;');
        $rule('::placeholder', 'color: var(--ny-dim) !important;');
        $rule('::selection', 'background: var(--ny-accent); color: #fff;');

        /* Surfaces. */
        $rule('.bg-nerix, [class*="___StyledDiv"]',
            'background: color-mix(in srgb, var(--ny-bg-alt) ' . ($transparency ? '82' : '100') . '%, transparent) !important;'
            . "\n  border: 1px solid var(--ny-border) !important;\n  border-radius: var(--ny-radius) !important;");
        $rule('.bg-nerix, [class*="___StyledDiv"]',
            "backdrop-filter: blur(var(--ny-blur));\n  -webkit-backdrop-filter: blur(var(--ny-blur));",
            $transparency);

        /* Rail. */
        $rule('#nyriel-rail',
            'background: color-mix(in srgb, var(--ny-sidebar) 88%, transparent) !important;'
            . "\n  backdrop-filter: blur(var(--ny-blur));"
            . "\n  -webkit-backdrop-filter: blur(var(--ny-blur));"
            . "\n  border-right: 1px solid var(--ny-border) !important;"
            . "\n  border-radius: 0 var(--ny-side-radius) var(--ny-side-radius) 0 !important;"
            . "\n  width: var(--ny-rail) !important;"
            . "\n  display: " . ($hideBar ? 'none' : 'flex') . ' !important;');
        // The nav is <button>, not <a>: each button proxies the panel's own link
        // so the SPA router handles navigation. A button needs the reset an
        // anchor gets for free.
        $rule('#nyriel-rail .nyriel-nav',
            'appearance: none; border: 0; background: none; cursor: pointer; font: inherit;'
            . "\n  display: flex; align-items: center; gap: 10px; width: 100%;"
            . "\n  padding: 10px; text-align: left; text-decoration: none;"
            . "\n  color: var(--ny-dim) !important; border-radius: var(--ny-radius) !important;");
        $rule('#nyriel-rail .nyriel-nav:hover', 'background: var(--ny-side-hover) !important; color: var(--ny-text) !important; transform: translateX(2px);');
        $rule('#nyriel-rail .nyriel-nav.active',
            'background: linear-gradient(135deg, var(--ny-accent), var(--ny-accent2)) !important; color: #fff !important;'
            . ' box-shadow: 0 4px 14px color-mix(in srgb, var(--ny-accent) 45%, transparent) !important;');
        $rule('#nyriel-rail .nyriel-nav[hidden], #nyriel-rail .cat[hidden], #nyriel-rail .sep[hidden]',
            'display: none !important;');
        // Key hint badge. Hidden in the collapsed rail, where there is no room.
        $rule('#nyriel-rail .nyriel-nav .keyhint',
            'margin-left: auto; font-size: 10px; font-weight: 700; letter-spacing: .04em;'
            . "\n  padding: 1px 6px; border-radius: 5px; opacity: .55;"
            . "\n  background: color-mix(in srgb, currentColor 14%, transparent);"
            . "\n  pointer-events: none;");
        $rule('#nyriel-rail:not(.wide) .nyriel-nav .keyhint', 'display: none;');
        $rule('#nyriel-rail .nyriel-nav.active .keyhint', 'opacity: .9;');
        $rule('#nyriel-rail .cat',
            'padding: 14px 12px 6px; font-size: 10.5px; font-weight: 700; letter-spacing: .08em;'
            . "\n  text-transform: uppercase; color: var(--ny-dim); opacity: .55;");
        $rule('#nyriel-rail .nyriel-nav .ic',
            "width: 20px;\n  height: 20px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n"
            . '  font-size: calc(20px * var(--ny-icon-scale));');
        $rule('body, body.bg-neutral-800, body.bg-neutral-900', 'padding-left: var(--ny-rail) !important;', ! $hideBar);
        $rule('div[class*="ProgressBar"]', 'left: var(--ny-rail) !important; width: calc(100% - var(--ny-rail)) !important;', ! $hideBar);

        /* Wallpaper. The URL is validated server-side and re-checked here, so a
           malformed value cannot break out of the url() token. */
        $wallpaper = (string) $this->get('background_image', '');
        if ($wallpaper !== '' && preg_match('#^https?://[^\s"\'<>]+$#', $wallpaper)) {
            $dim = (int) $this->get('wallpaper_dim', 40);
            $css .= "html body::before {\n"
                . "  content: ''; position: fixed; inset: 0; z-index: -1;\n"
                . '  background: url(' . $wallpaper . ') center/cover no-repeat;\n'
                . "  filter: blur(" . (int) $this->get('wallpaper_filter', 0) . "px);\n"
                . "  opacity: " . round($dim / 100, 2) . ";\n"
                . "  pointer-events: none;\n"
                . "}\n";

            // One appearance switch instead of separate blur/dim paths.
            if ($get('background_appearance', 'dim') === 'dim') {
                $rule('html body', 'background-color: color-mix(in srgb, var(--ny-bg) 70%, transparent) !important;');
            }
        }

        // The auth screen gets its own wallpaper, independent of the client one.
        $authWall = (string) $this->get('auth_background_image', '');
        if ($authWall !== '' && preg_match('#^https?://[^\s"\'<>]+$#', $authWall)) {
            $css .= "html body.auth {\n"
                . "  background: url(" . $authWall . ") center/cover no-repeat !important;\n"
                . "  position: relative;\n"
                . "}\n"
                . "html body.auth::before {\n"
                . "  content: ''; position: fixed; inset: 0; z-index: -1;\n"
                . "  background: rgba(0,0,0," . round((int) $this->get('auth_wallpaper_dim', 60) / 100, 2) . ");\n"
                . "  pointer-events: none;\n"
                . "}\n";
        }

        /* Content background: some users want cards on flat colour, not glass. */
        if ($get('content_background', 'solid') === 'flat') {
            $rule('.bg-nerix, [class*="___StyledDiv"]', 'backdrop-filter: none !important; -webkit-backdrop-filter: none !important;');
        }

        /* Accent hover: only emitted when it differs, so the default is free. */
        $accentHover = (string) $this->get('accent_hover', '');
        if ($accentHover !== '') {
            $rule(':root', "--ny-accent-hover: {$accentHover};");
        }

        /* Error status colour, used by the status orb and server cards. */
        $stError = (string) $this->get('palette_status_error', '');
        if ($stError !== '') {
            $rule(':root', "--ny-st-error: {$stError};");
        }

        /* Server overview graphs and coloured power buttons. */
        if (! $this->on('server_overview_graphs', true)) {
            $rule('[class*="ServerCard"] svg, [class*="ServerCard"] canvas', 'display: none !important;');
        }
        if ($this->on('server_colored_power', true)) {
            $rule('#nyriel-rail .nyriel-nav.power-on, .power-btn-on',
                'color: var(--ny-st-online) !important;');
        }

        /* Sidebar polish. */
        $style = (string) $get('sidebar_background', 'default');
        if ($style === 'solid') {
            $rule('#nyriel-rail', 'backdrop-filter: none !important; -webkit-backdrop-filter: none !important;');
        }
        $hoverFx = (string) $get('sidebar_hover', 'popout');
        if ($hoverFx === 'slide') {
            $rule('#nyriel-rail .nyriel-nav:hover', 'transform: translateX(6px);');
        } elseif ($hoverFx === 'none') {
            $rule('#nyriel-rail .nyriel-nav:hover', 'transform: none; background: none;');
        }
        if (! $this->on('sidebar_hover_tooltip', true)) {
            $rule('#nyriel-rail .nyriel-nav', 'title: none;');
        }
        if ($this->on('sidebar_always_visible_buttons')) {
            $rule('#nyriel-rail', '--ny-rail: ' . ($wide ? '200px' : $railW) . ' !important;');
        }

        /* Auth page pattern overlay, independent of the client one. */
        $authPattern = (string) $get('auth_background_appearance', 'none');
        if ($authPattern !== 'none' && $authPattern !== '') {
            $size = (int) $this->get('auth_background_magicsize', 24);
            $layer = match ($authPattern) {
                'grid'  => "repeating-linear-gradient(90deg, currentColor 0 1px, transparent 1px {$size}px),"
                         . "repeating-linear-gradient(0deg, currentColor 0 1px, transparent 1px {$size}px)",
                'diag'  => "repeating-linear-gradient(45deg, currentColor 0 1px, transparent 1px {$size}px)",
                'dots'  => 'radial-gradient(currentColor 1px, transparent 1px)',
                default => null,
            };
            if ($layer !== null) {
                $css .= "html body.auth::after {\n"
                    . "  content: ''; position: fixed; inset: 0; z-index: -1; pointer-events: none;\n"
                    . "  background-image: {$layer};\n"
                    . '  background-size: ' . ($authPattern === 'dots' ? "{$size}px {$size}px" : 'auto') . ";\n"
                    . "  color: color-mix(in srgb, var(--ny-text) 8%, transparent);\n"
                    . "}\n";
            }
        }

        /* Admin sidebar style, motion and watermark. */
        $adminSide = (string) $get('admin_sidebar', 'dark');
        if ($adminSide === 'hidden') {
            $rule('.main-sidebar, .sidebar', 'display: none !important;');
        } elseif ($adminSide === 'glass') {
            $rule('.main-sidebar, .sidebar',
                'backdrop-filter: blur(var(--ad-blur)) !important;'
                . ' -webkit-backdrop-filter: blur(var(--ad-blur)) !important;'
                . "\n  background: color-mix(in srgb, var(--ad-side) 72%, transparent) !important;");
        }
        if ($this->on('admin_animated', true)) {
            $rule('.content-wrapper .box, .main-sidebar .nav-sidebar > li > a',
                'transition: background-color .2s ease, color .2s ease, transform .2s ease;');
        }
        if (trim((string) $this->get('admin_watermark', '')) !== '') {
            // Rendered through attr(), so the value can never become markup.
            $rule('html body.adminlte::after',
                'content: attr(data-nyriel-mark); position: fixed; right: 14px; bottom: 10px;'
                . "\n  font-size: 11px; letter-spacing: .06em; color: var(--ad-dim); opacity: .5;"
                . "\n  pointer-events: none; z-index: 5;");
        }

        /* Sidebar link alignment. */
        if ($get('website_links_align', 'left') === 'center') {
            $rule('#nyriel-rail .nyriel-link', 'justify-content: center;');
        }

        /* Corner-radius alias: a second name for the same knob, applied after
           the main one so an admin who only sets this still sees a change. */
        $radiusAlias = (int) $this->get('border_radius', 0);
        if ($radiusAlias > 0) {
            $rule(':root', "--ny-radius: {$radiusAlias}px;");
        }

        /* Status dots. */
        $rule('.status-bar, [class*="ServerCard"] .status-bar', '--ActiveColor: var(--ny-st-offline);');

        /* Motion. */
        $rule('.nyriel-lift', 'transition: transform .2s ease, box-shadow .2s ease;', $animations);
        $rule('.nyriel-lift:hover', 'transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,.35);', $animations);
        if ($animations) {
            $css .= "@media (prefers-reduced-motion: reduce) {\n  .nyriel-lift { animation: none !important; transition: none !important; }\n}\n";
        }

        $css .= "::-webkit-scrollbar { width: 9px; height: 9px; }\n";
        $css .= "::-webkit-scrollbar-track { background: transparent; }\n";
        $css .= "::-webkit-scrollbar-thumb { background: var(--ny-border); border-radius: 9px; }\n";

        return $css;
    }
}
