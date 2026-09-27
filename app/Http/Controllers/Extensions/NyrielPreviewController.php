<?php

namespace Pterodactyl\Http\Controllers\Extensions;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;

/**
 * Nyriel designer — live preview.
 *
 * Frames the panel's own client routes so the admin sees the real dashboard
 * rather than a mock-up. Unsaved values ride in the query string and are read by
 * the theme wrapper itself, so previewing never writes to the database.
 */
class NyrielPreviewController extends Controller
{
    /** Only these client paths may be previewed. */
    private const ALLOWED = ['/', '/servers', '/account', '/account/api', '/account/ssh'];

    private const PAGES = [
        '/'            => 'Dashboard',
        '/servers'     => 'Servers',
        '/account'     => 'Account',
        '/account/api' => 'API keys',
        '/account/ssh' => 'SSH keys',
    ];

    /** Page list, shared with the view for its page switcher. */
    public static function pages(): array
    {
        return self::PAGES;
    }

    public function __invoke(Request $request): View
    {
        $page = (string) $request->query('page', '/');
        if (! in_array($page, self::ALLOWED, true)) {
            $page = '/';
        }

        // Everything except the page selector is forwarded to the panel, which
        // is how unsaved settings reach the wrapper.
        $settings = $request->except(['page']);

        return view('extensions.nyriel.preview', [
            'page'     => $page,
            'title'    => self::PAGES[$page] ?? 'Panel',
            'settings' => $settings,
            'url'      => $page . ($settings ? '?' . http_build_query($settings) : ''),
        ]);
    }
}
