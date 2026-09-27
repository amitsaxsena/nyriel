<?php

namespace Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Client;

use Illuminate\Http\Response;

/**
 * Serves Nyriel's generated stylesheet.
 *
 * Lives under /themes/pterodactyl/css/ so the panel can link it with the same
 * Theme::css() helper it uses for its own files. Conditional GET is honoured,
 * which matters here because the CSS is rebuilt on every request and a theme
 * save should not re-download it.
 */
class NyrielStylesheetRouteAction
{
    public function __invoke(NyrielStylesheet $sheet): Response
    {
        $etag = '"' . $sheet->etag() . '"';

        $response = response($sheet->css(), 200, [
            'Content-Type'  => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
            'ETag'          => $etag,
        ]);

        // 304 only when the client's copy is still current. Comparing the raw
        // If-None-Match keeps a multi-value header from missing the match.
        $ifNone = trim((string) request()->header('If-None-Match', ''));
        if ($ifNone !== '' && in_array($etag, array_map('trim', explode(',', $ifNone)), true)) {
            return response('', 304, [
                'Cache-Control' => 'public, max-age=3600',
                'ETag'          => $etag,
            ]);
        }

        return $response;
    }
}
