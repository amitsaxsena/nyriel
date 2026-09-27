# Nyriel — Pterodactyl theme extension (Blueprint)

A dark client theme with an independent admin theme, a schema-driven designer, a
live preview that frames the real panel, and an icon picker covering 12 icon
families (~17,000 icons).

## Layout

A Blueprint extension is not one directory — it also needs generated files under
the panel's own tree, so the package spans three roots.

```
.blueprint/extensions/nyriel/          the extension itself
  private/.store/conf.yml              Blueprint metadata
  private/schema.php                   SINGLE SOURCE OF TRUTH: 174 settings
  wrappers/dashboard.blade.php         client shell (nav, alert, context menu)
  wrappers/admin.blade.php             admin shell (stylesheet link, watermark)
  views/admin/controller.php           install-time template for the controller
  views/admin/view.blade.php           install-time template for the card

app/BlueprintFramework/Libraries/ExtensionLibrary/Client/
  NyrielStylesheet.php                 builds the CSS from the schema
  NyrielStylesheetRouteAction.php      serves it with an ETag

app/Http/Controllers/Admin/Extensions/nyriel/
  nyrielExtensionController.php        card, designer, load, save, factory reset

app/Http/Controllers/Extensions/
  NyrielPreviewController.php          the live preview route

resources/views/admin/extensions/nyriel/
  card.blade.php                       extension card
  designer/index.blade.php             the designer (search, presets, icon picker)

resources/views/extensions/nyriel/
  preview.blade.php                    preview shell (frames the real panel)

public/extensions/nyriel/nyriel.js     shortcuts, nav, menus, counters
```

## Install

1. Copy the trees above into the panel, preserving paths.
2. Create the two wrapper symlinks (see `fragments/SETUP.txt`).
3. Insert the two route blocks into `routes/base.php` **before** the SPA
   catch-all `Route::get('/{react}', ...)` — otherwise the catch-all swallows
   `/themes/...` and `/extensions/nyriel/preview`:
   - `fragments/routes-base-nyriel-css.txt`
   - `fragments/routes-base-nyriel-preview.txt`
4. Add `nyriel` to `.blueprint/extensions/blueprint/private/db/installed_extensions`
   as a plain comma-separated identifier. **Do not put `|` in it.** `|` is
   Blueprint's field separator: a stray one makes the registry parse `nyriel` as
   an extension with no `conf.yml`, and that throws on every route load, taking
   the whole panel down with it.
5. `php artisan bp:cache`
6. Clear caches **as `www-data`**, never as root:
   ```
   sudo -u www-data php artisan optimize:clear
   ```

## The one core patch this relies on

`fragments/core/BlueprintBaseLibrary.php.bak` is the unmodified file. The change
is in `extensionsConfigs()`:

```php
$conf = Yaml::parse(...);
// An empty or scalar conf.yml parses to null, and array_filter(null) is a
// TypeError. A single malformed entry must not take the whole panel's route
// registration down with it, so skip it and keep going.
if (!is_array($conf)) {
    continue;
}
$collection->push(array_filter($conf, $fn));
```

Without this, one bad `conf.yml` anywhere on the panel makes every artisan
command and every page fail with
`array_filter(): Argument #1 ($array) must be of type array, null given`.
Re-apply it after any Blueprint upgrade, which rewrites that library.

## Settings

All settings live in `private/schema.php`. The controller, the designer UI, the
validation rules, the sanitiser and the admin card all read that one array, so a
new field appears everywhere at once. Use `type => 'action'` for a one-shot
command — actions are excluded from stored state, so a later save cannot
re-trigger them.

Groups: client panel, sidebar, nav items, icon overrides, icon family, palette,
server status, dashboard, backgrounds, alerts & links, shortcuts, context menus,
alert rendering, palette slots, admin panel.

## Notes

- The stylesheet is served at `/themes/pterodactyl/css/nyriel.css`, the same
  path Pterodactyl uses for its own client assets. Its `ETag` is a hash of every
  setting, so saving busts the browser cache automatically.
- The preview frames the panel's own routes with the unsaved values in the query
  string; the wrapper reads them from there, so previewing never writes to the
  database.
- Icon families with no parseable stylesheet (Feather, Octicons) read their names
  from the package's own JSON index; Material reads Google's codepoints list.
  No icon name is hardcoded anywhere.
- The sidebar's nav buttons proxy the panel's own `<a>` links rather than
  setting `location.href`, so navigation stays inside the React router and the
  app is not torn down on every click.
