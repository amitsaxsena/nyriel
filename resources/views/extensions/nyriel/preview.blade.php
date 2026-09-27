{{--
    Nyriel designer — live preview shell.

    Frames the real client panel so the designer shows the actual dashboard.
    The settings in the iframe's src are the *unsaved* form values, which the
    theme wrapper reads directly, so nothing is written to the database.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nyriel preview — {{ $title }}</title>
<style>
  html, body { margin: 0; height: 100%; background: #05070c; }

  .bar { display: flex; align-items: center; gap: 8px; height: 26px; padding: 0 10px;
         background: #0c121c; color: #8ea3c4; font: 11px system-ui, -apple-system, sans-serif;
         border-bottom: 1px solid rgba(120,165,255,.18); }
  .bar b { color: #e6edf8; font-weight: 600; }
  .bar .sep { margin-left: auto; opacity: .7; }
  .bar select { background: #131b28; color: #8ea3c4; border: 1px solid rgba(120,165,255,.2);
                 border-radius: 6px; font: inherit; padding: 2px 6px; }
  iframe { display: block; width: 100%; height: calc(100% - 26px); border: 0; background: #05070c; }
</style>
</head>
<body>
  <div class="bar">
    <b>Live preview</b>
    <span>— unsaved settings, nothing written to the database</span>
    <span class="sep">
      <select id="jump" aria-label="Preview page">
        @foreach(\Pterodactyl\Http\Controllers\Extensions\NyrielPreviewController::pages() as $path => $label)
          <option value="{{ $path }}" @selected($path === $page)>{{ $label }}</option>
        @endforeach
      </select>
    </span>
  </div>
  <iframe id="p" src="{{ $url }}" title="Panel preview"></iframe>

  <script>
    // Jumping between client pages re-frames the same panel with the same
    // unsaved settings, rather than reloading the whole designer.
    const base = new URL(location.href);
    document.getElementById('jump').addEventListener('change', e => {
      base.searchParams.set('page', e.target.value);
      location.href = base.toString();
    });
  </script>
</body>
</html>
