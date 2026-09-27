<?php
/**
 * Nyriel — settings schema.
 *
 * SINGLE SOURCE OF TRUTH. The admin controller, the designer UI, the client
 * wrapper and the preview mock all read from this array. Adding a setting means
 * adding one entry here; nothing else needs to change.
 *
 * Field types:
 *   color  → hex swatch + text field
 *   number → slider, bounded by min/max
 *   text   → plain input
 *   select → fixed set of choices
 *   bool   → toggle switch
 *   url    → text input, must be http(s)
 */

return [

    /* ── meta ─────────────────────────────────────────────────────── */
    'meta' => [
        'init' => ['type' => 'text', 'default' => '1.0.0', 'hidden' => true],
    ],

    /* ── client: master toggles ───────────────────────────────────── */
    'client' => [
        'label' => 'Client panel',
        'fields' => [
            'enabled'          => ['type' => 'bool',  'default' => '1', 'label' => 'Enable client theme', 'hint' => 'Off = stock Pterodactyl'],
            'page_indexing'    => ['type' => 'bool',  'default' => '0', 'label' => 'Allow search indexing', 'hint' => 'Adds a noindex meta tag when off'],
            'dashboard_transparency' => ['type' => 'bool', 'default' => '1', 'label' => 'Dashboard transparency', 'hint' => 'Blur cards against the background'],
            'animations'       => ['type' => 'bool',  'default' => '1', 'label' => 'UI animations', 'hint' => 'Hover lifts, fades, transitions'],
            'reset'            => ['type' => 'action', 'default' => '0', 'label' => 'Factory reset', 'hint' => 'Wipe every Nyriel setting back to its default'],
        ],
    ],

    /* ── client: sidebar ──────────────────────────────────────────── */
    'sidebar' => [
        'label' => 'Client sidebar',
        'fields' => [
            'sidebar_full'      => ['type' => 'bool',  'default' => '0', 'label' => 'Wide sidebar', 'hint' => 'Icon rail vs full width with labels'],
            'sidebar_mode'      => ['type' => 'select', 'default' => 'rail', 'label' => 'Sidebar style',
                                   'options' => ['rail' => 'Icon rail — compact', 'full' => 'Full width — with labels']],
            'sidebar_background'=> ['type' => 'select', 'default' => 'default', 'label' => 'Sidebar background',
                                   'options' => ['default' => 'Solid', 'blurred' => 'Blurred glass']],
            'sidebar_hover'     => ['type' => 'select', 'default' => 'popout', 'label' => 'Hover effect',
                                   'options' => ['none' => 'None', 'popout' => 'Slide out', 'expand' => 'Expand']],
            'sidebar_buttonstyle' => ['type' => 'select', 'default' => '1', 'label' => 'Active indicator',
                                   'options' => ['1' => 'Left bar', '2' => 'Full border', '0' => 'Fill only']],
            'sidebar_separators'=> ['type' => 'bool',  'default' => '1', 'label' => 'Section separators'],
            'sidebar_hover_tooltip' => ['type' => 'bool', 'default' => '1', 'label' => 'Hover tooltips', 'hint' => 'Only in icon-rail mode'],
            'sidebar_always_visible_buttons' => ['type' => 'bool', 'default' => '0', 'label' => 'Always show labels'],
            'hide_sidebar'     => ['type' => 'bool',  'default' => '0', 'label' => 'Hide sidebar entirely'],
            'sidebar_border_radius' => ['type' => 'number', 'default' => 12, 'min' => 0, 'max' => 30, 'label' => 'Sidebar corner radius'],
            'side_width'        => ['type' => 'number', 'default' => 72, 'min' => 48, 'max' => 240, 'label' => 'Rail width'],
            'sidebar_customlogo'=> ['type' => 'url',   'default' => '', 'label' => 'Custom logo URL', 'hint' => 'Wide sidebar only'],
            'sidebar_bg'        => ['type' => 'color', 'default' => '#0d1424', 'label' => 'Sidebar background colour'],
            'sidebar_bg_hover'  => ['type' => 'color', 'default' => '#16203a', 'label' => 'Sidebar hover colour'],
            'sidebar_bg_active' => ['type' => 'color', 'default' => '#1f2c4d', 'label' => 'Sidebar active colour'],
        ],
    ],

    /* ── client: per-item icon visibility ─────────────────────────── */
    'nav_items' => [
        'label' => 'Sidebar items',
        'fields' => [
            'sidebar_home'          => ['type' => 'bool', 'default' => '1', 'label' => 'Home'],
            'sidebar_admin'         => ['type' => 'bool', 'default' => '1', 'label' => 'Admin'],
            'sidebar_account'       => ['type' => 'bool', 'default' => '1', 'label' => 'Account'],
            'sidebar_logout'        => ['type' => 'bool', 'default' => '1', 'label' => 'Logout'],
            'sidebar_server_terminal'   => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Console'],
            'sidebar_server_files'      => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Files'],
            'sidebar_server_databases'  => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Databases'],
            'sidebar_server_schedules'  => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Schedules'],
            'sidebar_server_users'      => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Subusers'],
            'sidebar_server_backups'    => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Backups'],
            'sidebar_server_network'    => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Network'],
            'sidebar_server_startup'    => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Startup'],
            'sidebar_server_settings'   => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Settings'],
            'sidebar_server_activity'   => ['type' => 'bool', 'default' => '1', 'label' => 'Server → Activity'],
            'sidebar_server_more'       => ['type' => 'bool', 'default' => '1', 'label' => 'Server → More'],
            'sidebar_account_account'   => ['type' => 'bool', 'default' => '1', 'label' => 'Account → Profile'],
            'sidebar_account_api'       => ['type' => 'bool', 'default' => '1', 'label' => 'Account → API keys'],
            'sidebar_account_ssh'       => ['type' => 'bool', 'default' => '1', 'label' => 'Account → SSH keys'],
            'sidebar_account_activity'  => ['type' => 'bool', 'default' => '1', 'label' => 'Account → Activity'],
            'sidebar_account_more'      => ['type' => 'bool', 'default' => '1', 'label' => 'Account → More'],
        ],
    ],

    /* ── client: per-item icon overrides ──────────────────────────── */
    'icon_overrides' => [
        'label' => 'Icon overrides',
        'hint'  => 'Per-item icon class, overriding the chosen family. Leave blank to use the family default.',
        'fields' => [
            'icon_home'         => ['type' => 'text', 'default' => '', 'label' => 'Home icon'],
            'icon_account'       => ['type' => 'text', 'default' => '', 'label' => 'Account icon'],
            'icon_settings'      => ['type' => 'text', 'default' => '', 'label' => 'Settings icon'],
            'icon_accountAccount' => ['type' => 'text', 'default' => '', 'label' => 'Account icon', 'placeholder' => 'ti-home'],
            'icon_accountApi' => ['type' => 'text', 'default' => '', 'label' => 'API keys icon', 'placeholder' => 'ti-home'],
            'icon_accountSsh' => ['type' => 'text', 'default' => '', 'label' => 'SSH keys icon', 'placeholder' => 'ti-home'],
            'icon_accountActivity' => ['type' => 'text', 'default' => '', 'label' => 'Account activity icon', 'placeholder' => 'ti-home'],
            'icon_accountMore' => ['type' => 'text', 'default' => '', 'label' => 'Account more icon', 'placeholder' => 'ti-home'],
            'icon_admin' => ['type' => 'text', 'default' => '', 'label' => 'Admin icon', 'placeholder' => 'ti-home'],
            'icon_logout'        => ['type' => 'text', 'default' => '', 'label' => 'Logout icon'],
            'icon_serverTerminal'       => ['type' => 'text', 'default' => '', 'label' => 'Server → Console icon'],
            'icon_serverFiles'         => ['type' => 'text', 'default' => '', 'label' => 'Server → Files icon'],
            'icon_serverDatabases'     => ['type' => 'text', 'default' => '', 'label' => 'Server → Databases icon'],
            'icon_serverSchedules'      => ['type' => 'text', 'default' => '', 'label' => 'Server → Schedules icon'],
            'icon_serverUsers'      => ['type' => 'text', 'default' => '', 'label' => 'Server → Subusers icon'],
            'icon_serverBackups'       => ['type' => 'text', 'default' => '', 'label' => 'Server → Backups icon'],
            'icon_serverNetwork'       => ['type' => 'text', 'default' => '', 'label' => 'Server → Network icon'],
            'icon_serverStartup'       => ['type' => 'text', 'default' => '', 'label' => 'Server → Startup icon'],
            'icon_serverSettings'    => ['type' => 'text', 'default' => '', 'label' => 'Server → Settings icon'],
            'icon_serverActivity'      => ['type' => 'text', 'default' => '', 'label' => 'Server → Activity icon'],
            'icon_serverMore'          => ['type' => 'text', 'default' => '', 'label' => 'Server → More icon'],
        ],
    ],

    /* ── client: icon set ─────────────────────────────────────────── */
    'icons' => [
        'label' => 'Icons',
        'fields' => [
            'icon_fallback' => ['type' => 'select', 'default' => 'bootstrap', 'label' => 'Icon family',
                'options' => [
                    'bootstrap'      => 'Bootstrap Icons',
                    'feather'        => 'Feather',
                    'lucide'         => 'Lucide',
                    'material'       => 'Material Design',
                    'material-light' => 'Material Light',
                    'fontawesome'    => 'Font Awesome 6',
                    'tabler'         => 'Tabler Icons',
                    'remix-outline'  => 'Remix (outline)',
                    'remix-solid'    => 'Remix (fill)',
                    'octicons'       => 'Octicons',
                    'eva-outline'    => 'Eva (outline)',
                    'eva-solid'      => 'Eva (fill)',
                    ]],
            'icon_scale'      => ['type' => 'number', 'default' => 1.0, 'min' => 0.6, 'max' => 1.8, 'step' => 0.05, 'label' => 'Icon scale'],
            'icon_anim'        => ['type' => 'bool', 'default' => '1', 'label' => 'Animate on hover'],
            'icon_weight'      => ['type' => 'select', 'default' => 'normal', 'label' => 'Icon weight (outline families)',
                'options' => ['thin' => 'Thin', 'light' => 'Light', 'normal' => 'Regular', 'bold' => 'Bold']],
            'icon_cdn'         => ['type' => 'select', 'default' => 'jsdelivr', 'label' => 'Icon CDN',
                'options' => ['jsdelivr' => 'jsDelivr', 'unpkg' => 'unpkg', 'local' => 'Inline SVG (no CDN)']],
        ],
    ],

    /* ── client: palette ──────────────────────────────────────────── */
    'palette' => [
        'label' => 'Palette',
        'fields' => [
            'bg'            => ['type' => 'color', 'default' => '#0a0f1a', 'label' => 'Page background'],
            'bg_alt'        => ['type' => 'color', 'default' => '#111a2b', 'label' => 'Card / surface'],
            'bg_hover'      => ['type' => 'color', 'default' => '#18233a', 'label' => 'Card hover'],
            'content_background' => ['type' => 'color', 'default' => '#0d1424', 'label' => 'Content background'],
            'accent'        => ['type' => 'color', 'default' => '#3b82f6', 'label' => 'Accent'],
            'accent2'       => ['type' => 'color', 'default' => '#06b6d4', 'label' => 'Accent gradient end'],
            'accent_hover'  => ['type' => 'color', 'default' => '#60a5fa', 'label' => 'Accent hover'],
            'text'          => ['type' => 'color', 'default' => '#e2e8f0', 'label' => 'Text'],
            'text_dim'      => ['type' => 'color', 'default' => '#94a3b8', 'label' => 'Text (dim)'],
            'border'        => ['type' => 'text', 'default' => 'rgba(148, 163, 184, 0.15)', 'label' => 'Border', 'hint' => 'Any CSS colour'],
            'border_radius' => ['type' => 'number', 'default' => 12, 'min' => 0, 'max' => 40, 'label' => 'Corner radius'],
            'radius'        => ['type' => 'number', 'default' => 12, 'min' => 0, 'max' => 40, 'label' => 'Corner radius (alias)'],
            'blur'          => ['type' => 'number', 'default' => 16, 'min' => 0, 'max' => 40, 'label' => 'Glass blur'],
            'font_family'   => ['type' => 'text', 'default' => '', 'label' => 'Font family', 'hint' => 'Blank = system default. e.g. Inter, sans-serif'],
            'font_size'     => ['type' => 'number', 'default' => 14, 'min' => 11, 'max' => 20, 'label' => 'Base font size'],
        ],
    ],

    /* ── client: status colours ───────────────────────────────────── */
    'status' => [
        'label' => 'Server status',
        'fields' => [
            'palette_status_online'   => ['type' => 'color', 'default' => '#34d399', 'label' => 'Online'],
            'palette_status_offline'  => ['type' => 'color', 'default' => '#f87171', 'label' => 'Offline'],
            'palette_status_starting' => ['type' => 'color', 'default' => '#fbbf24', 'label' => 'Starting'],
            'palette_status_error'    => ['type' => 'color', 'default' => '#fb7185', 'label' => 'Error'],
            'statusgradient_style'    => ['type' => 'select', 'default' => 'default', 'label' => 'Status pill',
                'options' => ['default' => 'Solid dot', 'flat' => 'Flat chip', 'gradient' => 'Gradient glow']],
        ],
    ],

    /* ── client: dashboard widgets ────────────────────────────────── */
    'dashboard' => [
        'label' => 'Dashboard',
        'fields' => [
            'server_list'         => ['type' => 'select', 'default' => 'cards', 'label' => 'Server list layout',
                'options' => ['cards' => 'Cards', 'list' => 'List', 'compact' => 'Compact']],
            'dashboard_transparency' => ['type' => 'bool', 'default' => '1', 'label' => 'Card transparency'],
            'server_overview_graphs' => ['type' => 'bool', 'default' => '1', 'label' => 'CPU / memory graphs'],
            'server_colored_power' => ['type' => 'bool', 'default' => '1', 'label' => 'Colour power buttons'],
            'watermark'           => ['type' => 'bool', 'default' => '0', 'label' => 'Footer watermark', 'hint' => '"Powered by" line on client pages'],
            'file_grid'           => ['type' => 'bool', 'default' => '1', 'label' => 'File manager grid view'],
            'file_view'           => ['type' => 'select', 'default' => 'list', 'label' => 'Default file view',
                'options' => ['list' => 'List', 'grid' => 'Grid']],
            'graph_style'         => ['type' => 'select', 'default' => 'area', 'label' => 'Resource graphs',
                'options' => ['area' => 'Area', 'line' => 'Line', 'bar' => 'Bar', 'off' => 'Hidden']],
            'stat_animation'      => ['type' => 'bool', 'default' => '1', 'label' => 'Animate stat numbers'],
        ],
    ],

    /* ── client: wallpapers ───────────────────────────────────────── */
    'background' => [
        'label' => 'Backgrounds',
        'fields' => [
            'background_image'         => ['type' => 'url', 'default' => '', 'label' => 'Client wallpaper URL'],
            'background_appearance'    => ['type' => 'select', 'default' => '0', 'label' => 'Wallpaper filter',
                'options' => ['0' => 'None', '1' => 'Blur', '2' => 'Dim', '3' => 'Blur + dim']],
            'background_magic'         => ['type' => 'select', 'default' => '', 'label' => 'Pattern overlay',
                'options' => ['' => 'None', 'dots' => 'Dots', 'grid' => 'Grid', 'waves' => 'Waves', 'noise' => 'Noise', 'hex' => 'Hexagons', 'diag' => 'Diagonal']],
            'background_magicsize'     => ['type' => 'number', 'default' => 24, 'min' => 4, 'max' => 80, 'label' => 'Pattern size'],
            'background_magicopacity'  => ['type' => 'number', 'default' => 30, 'min' => 0, 'max' => 100, 'label' => 'Pattern opacity'],
            'wallpaper_dim'            => ['type' => 'number', 'default' => 30, 'min' => 0, 'max' => 90, 'label' => 'Wallpaper dim'],
            'auth_background_image'    => ['type' => 'url', 'default' => '', 'label' => 'Login page wallpaper'],
            'auth_background_appearance' => ['type' => 'select', 'default' => '0', 'label' => 'Login filter',
                'options' => ['0' => 'None', '1' => 'Blur', '2' => 'Dim', '3' => 'Blur + dim']],
            'auth_background_magic'    => ['type' => 'select', 'default' => '', 'label' => 'Login pattern',
                'options' => ['' => 'None', 'dots' => 'Dots', 'grid' => 'Grid', 'waves' => 'Waves', 'noise' => 'Noise']],
            'auth_background_magicsize' => ['type' => 'number', 'default' => 24, 'min' => 4, 'max' => 80, 'label' => 'Login pattern size'],
            'auth_customlogo'          => ['type' => 'url', 'default' => '', 'label' => 'Login page logo'],
            'watermark_auth'           => ['type' => 'bool', 'default' => '1', 'label' => 'Login watermark'],
        ],
    ],

    /* ── client: alerts + links ───────────────────────────────────── */
    'extras' => [
        'label' => 'Alerts & links',
        'fields' => [
            'alert'          => ['type' => 'bool', 'default' => '0', 'label' => 'Show a panel-wide alert'],
            'alert_text'     => ['type' => 'text', 'default' => '', 'label' => 'Alert message', 'hint' => 'Markdown supported'],
            'alert_icon'     => ['type' => 'text', 'default' => 'bi-info-circle', 'label' => 'Alert icon class'],
            'alert_position' => ['type' => 'select', 'default' => 'top', 'label' => 'Alert position',
                'options' => ['top' => 'Top', 'bottom' => 'Bottom', 'floating' => 'Floating toast']],
            'alert_dismiss'  => ['type' => 'bool', 'default' => '1', 'label' => 'Dismissible alert'],
            'website_links'  => ['type' => 'bool', 'default' => '0', 'label' => 'Custom links in sidebar'],
            'website_links_align' => ['type' => 'select', 'default' => 'left', 'label' => 'Links alignment',
                'options' => ['left' => 'Left', 'center' => 'Centre']],
            'weblink_support' => ['type' => 'text', 'default' => '', 'label' => 'Support link'],
            'weblink_billing' => ['type' => 'text', 'default' => '', 'label' => 'Billing link'],
            'weblink_status'  => ['type' => 'text', 'default' => '', 'label' => 'Status page link'],
            'weblink_social_discord' => ['type' => 'text', 'default' => '', 'label' => 'Discord link'],
            'weblink_social_github'  => ['type' => 'text', 'default' => '', 'label' => 'GitHub link'],
        ],
    ],

    /* ── client: keyboard shortcuts ───────────────────────────────── */
    'shortcuts' => [
        'label' => 'Shortcuts',
        'fields' => [
            'keyboard_shortcuts' => ['type' => 'bool', 'default' => '0', 'label' => 'Keyboard shortcuts'],
            'keybind_icons'      => ['type' => 'bool', 'default' => '1', 'label' => 'Show key hints in sidebar'],
            'keybinds'           => ['type' => 'text', 'default' => '', 'label' => 'Custom keymap (JSON)', 'hint' => 'Leave blank for defaults'],
            'keybind_nav_prev'   => ['type' => 'text', 'default' => 'k', 'label' => 'Navigate up', 'hint' => 'Single key, ignores modifiers'],
            'keybind_nav_next'   => ['type' => 'text', 'default' => 'j', 'label' => 'Navigate down'],
            'keybind_sidebar'    => ['type' => 'text', 'default' => 'b', 'label' => 'Toggle sidebar'],
            'keybind_search'     => ['type' => 'text', 'default' => '/', 'label' => 'Focus search'],
            'keybind_console'    => ['type' => 'text', 'default' => 'c', 'label' => 'Focus console'],
            'keybind_escape'     => ['type' => 'text', 'default' => 'Escape', 'label' => 'Close overlays'],
        ],
    ],

    /* ── client: context menus ────────────────────────────────────── */
    'context_menu' => [
        'label' => 'Context menus',
        'fields' => [
            'contextmenu_files'    => ['type' => 'bool', 'default' => '1', 'label' => 'Right-click file menu'],
            'contextmenu_servers'  => ['type' => 'bool', 'default' => '1', 'label' => 'Right-click server menu'],
            'contextmenu_sidebar'  => ['type' => 'bool', 'default' => '1', 'label' => 'Right-click sidebar menu'],
            'contextmenu_pos'      => ['type' => 'select', 'default' => 'cursor', 'label' => 'Menu position',
                'options' => ['cursor' => 'At cursor', 'element' => 'On element']],
        ],
    ],

    /* ── client: alert rendering ──────────────────────────────────── */
    'alert_md' => [
        'label' => 'Alert rendering',
        'fields' => [
            'alert_markdown'   => ['type' => 'bool', 'default' => '1', 'label' => 'Markdown in alerts', 'hint' => 'Bold, italic, links, code, lists'],
            'alert_timeout'    => ['type' => 'number', 'default' => 0, 'min' => 0, 'max' => 60, 'label' => 'Auto-dismiss after (s)', 'hint' => '0 = never'],
        ],
    ],

    /* ── client: extra palettes (nebula parity + more) ────────────── */
    'palettes_extra' => [
        'label' => 'Palette slots',
        'hint'  => 'Nine dashboard + eight sidebar colour slots, used by per-server card accents.',
        'fields' => [
            'palette_dashboard_1' => ['type' => 'color', 'default' => '#3b82f6', 'label' => 'Dashboard slot 1'],
            'palette_dashboard_2' => ['type' => 'color', 'default' => '#06b6d4', 'label' => 'Dashboard slot 2'],
            'palette_dashboard_3' => ['type' => 'color', 'default' => '#8b5cf6', 'label' => 'Dashboard slot 3'],
            'palette_dashboard_4' => ['type' => 'color', 'default' => '#f59e0b', 'label' => 'Dashboard slot 4'],
            'palette_dashboard_5' => ['type' => 'color', 'default' => '#10b981', 'label' => 'Dashboard slot 5'],
            'palette_dashboard_6' => ['type' => 'color', 'default' => '#f43f5e', 'label' => 'Dashboard slot 6'],
            'palette_dashboard_7' => ['type' => 'color', 'default' => '#0ea5e9', 'label' => 'Dashboard slot 7'],
            'palette_dashboard_8' => ['type' => 'color', 'default' => '#a855f7', 'label' => 'Dashboard slot 8'],
            'palette_dashboard_9' => ['type' => 'color', 'default' => '#eab308', 'label' => 'Dashboard slot 9'],
            'palette_sidebar_1'   => ['type' => 'color', 'default' => '#0d1424', 'label' => 'Sidebar slot 1'],
            'palette_sidebar_2'   => ['type' => 'color', 'default' => '#16203a', 'label' => 'Sidebar slot 2'],
            'palette_sidebar_3'   => ['type' => 'color', 'default' => '#1f2c4d', 'label' => 'Sidebar slot 3'],
            'palette_sidebar_4'   => ['type' => 'color', 'default' => '#2a3a63', 'label' => 'Sidebar slot 4'],
            'palette_sidebar_5'   => ['type' => 'color', 'default' => '#1a2338', 'label' => 'Sidebar slot 5'],
            'palette_sidebar_6'   => ['type' => 'color', 'default' => '#22304f', 'label' => 'Sidebar slot 6'],
            'palette_sidebar_7'   => ['type' => 'color', 'default' => '#2d3f66', 'label' => 'Sidebar slot 7'],
            'palette_sidebar_8'   => ['type' => 'color', 'default' => '#0f1830', 'label' => 'Sidebar slot 8'],
            'palette_auth_1'      => ['type' => 'color', 'default' => '#3b82f6', 'label' => 'Auth slot 1'],
            'palette_auth_2'      => ['type' => 'color', 'default' => '#06b6d4', 'label' => 'Auth slot 2'],
            'palette_auth_3'      => ['type' => 'color', 'default' => '#8b5cf6', 'label' => 'Auth slot 3'],
            'palette_auth_4'      => ['type' => 'color', 'default' => '#f59e0b', 'label' => 'Auth slot 4'],
            'palette_auth_5'      => ['type' => 'color', 'default' => '#10b981', 'label' => 'Auth slot 5'],
            'palette_auth_6'      => ['type' => 'color', 'default' => '#f43f5e', 'label' => 'Auth slot 6'],
            'palette_auth_7'      => ['type' => 'color', 'default' => '#0ea5e9', 'label' => 'Auth slot 7'],
            'palette_auth_8'      => ['type' => 'color', 'default' => '#a855f7', 'label' => 'Auth slot 8'],
        ],
    ],

    /* ── admin panel ──────────────────────────────────────────────── */
    'admin' => [
        'label' => 'Admin panel',
        'fields' => [
            'admin_enabled'      => ['type' => 'bool',  'default' => '1', 'label' => 'Enable admin theme', 'hint' => 'Independent of client'],
            'admin_sidebar'      => ['type' => 'select', 'default' => 'glass', 'label' => 'Admin sidebar',
                'options' => ['glass' => 'Glass (blurred)', 'dark' => 'Solid dark', 'hidden' => 'Hidden']],
            'admin_animated'     => ['type' => 'bool',  'default' => '1', 'label' => 'Admin animations'],
            'admin_compact'      => ['type' => 'bool',  'default' => '0', 'label' => 'Compact tables'],
            'admin_sticky_sidebar' => ['type' => 'bool', 'default' => '1', 'label' => 'Sticky sidebar'],
            'admin_bg'           => ['type' => 'color', 'default' => '#05070d', 'label' => 'Admin background'],
            'admin_bg_alt'       => ['type' => 'color', 'default' => '#0d1420', 'label' => 'Admin card / surface'],
            'admin_sidebar_bg'   => ['type' => 'color', 'default' => '#0a0f18', 'label' => 'Admin sidebar background'],
            'admin_accent'       => ['type' => 'color', 'default' => '#3b82f6', 'label' => 'Admin accent'],
            'admin_accent2'      => ['type' => 'color', 'default' => '#60a5fa', 'label' => 'Admin accent gradient end'],
            'admin_text'         => ['type' => 'color', 'default' => '#e8f0ff', 'label' => 'Admin text'],
            'admin_text_dim'     => ['type' => 'color', 'default' => '#a8c0e8', 'label' => 'Admin text (dim)'],
            'admin_side_width'   => ['type' => 'number', 'default' => 240, 'min' => 180, 'max' => 320, 'label' => 'Admin sidebar width'],
            'admin_blur'         => ['type' => 'number', 'default' => 20, 'min' => 0, 'max' => 40, 'label' => 'Admin glass blur'],
            'admin_radius'       => ['type' => 'number', 'default' => 14, 'min' => 0, 'max' => 40, 'label' => 'Admin corner radius'],
            'admin_watermark'    => ['type' => 'bool',  'default' => '0', 'label' => 'Admin footer watermark'],
        ],
    ],

];
