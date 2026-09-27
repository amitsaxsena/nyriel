<?php
/**
 * Nyriel — admin save handler.
 *
 * Blueprint calls this on POST. Persists every whitelisted key; unknown keys
 * are ignored rather than trusted.
 */

if (! auth()->check() || ! auth()->user()->root_admin) {
    abort(403);
}

$defaults = [
    'enabled'      => '1',
    'bg'           => '#0a0f1a',
    'bg_alt'       => '#111a2b',
    'sidebar_bg'   => '#0d1424',
    'accent'       => '#3b82f6',
    'accent2'      => '#06b6d4',
    'text'         => '#e2e8f0',
    'text_dim'     => '#94a3b8',
    'border'       => 'rgba(148, 163, 184, 0.15)',
    'radius'       => 12,
    'side_width'   => 72,
    'blur'         => 16,
    'hide_sidebar' => '0',
    'wallpaper'    => '',
    'wallpaper_dim'=> 30,
];

foreach (array_keys($defaults) as $key) {
    if (! array_key_exists($key, request()->all())) {
        continue;
    }

    $value = request()->input($key);

    // Numeric settings are clamped so a fat-fingered save can't collapse the
    // layout (radius 4000px, or a negative width that hides the whole rail).
    if (in_array($key, ['radius', 'side_width', 'blur', 'wallpaper_dim'], true)) {
        $bounds = [
            'radius'        => [0, 40],
            'side_width'    => [48, 240],
            'blur'          => [0, 40],
            'wallpaper_dim' => [0, 90],
        ];
        [$min, $max] = $bounds[$key];
        $value = max($min, min($max, (int) $value));
    }

    // Text settings are rendered straight into a <style> block, so strip tags.
    if (in_array($key, ['bg', 'bg_alt', 'sidebar_bg', 'accent', 'accent2', 'text', 'text_dim'], true)) {
        $value = substr((string) preg_replace('/[^#a-zA-Z0-9(),.%\- ]/', '', (string) $value), 0, 40);
    }

    $blueprint->dbSet('nyriel', $key, $value);
}

$blueprint->refreshCache();

return redirect()->back()->with('success', 'Nyriel settings saved.');
