#!/usr/bin/env bash
# Nyriel installer.
#
# Run from the panel root as root:  sudo bash install.sh
#
# What it does, and why each step has to happen:
#   1. copy the source trees into place
#   2. create the two wrapper symlinks Blueprint resolves through
#   3. insert the two route blocks *before* the SPA catch-all
#   4. register the extension in Blueprint's registry
#   5. clear caches as www-data, because a root-run artisan leaves root-owned
#      cache files that www-data cannot overwrite — which 500s the whole panel
set -e
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

echo "== 1. copy source trees"
for d in \
  .blueprint/extensions/nyriel \
  app/Http/Controllers/Admin/Extensions/nyriel \
  app/Http/Controllers/Extensions/NyrielPreviewController.php \
  app/BlueprintFramework/Libraries/ExtensionLibrary/Client \
  resources/views/admin/extensions/nyriel \
  resources/views/extensions/nyriel \
  public/extensions/nyriel
do
  mkdir -p "$(dirname "$d")"
  cp -a "$d" "$(dirname "$d")/"
  echo "   $d"
done

echo "== 2. wrapper symlinks"
ln -sfn ../../../../../.blueprint/extensions/nyriel/wrappers/dashboard.blade.php \
        resources/views/blueprint/dashboard/wrappers/nyriel.blade.php
ln -sfn ../../../../../.blueprint/extensions/nyriel/wrappers/admin.blade.php \
        resources/views/blueprint/admin/wrappers/nyriel.blade.php

echo "== 3. routes (before the SPA catch-all)"
python3 - <<'PY'
import re
p = 'routes/base.php'
s = open(p).read()
if "nyriel.css" in s:
    print("   already present")
else:
    block = (
        "// Nyriel stylesheet. Served without auth.session so the login page can load it\n"
        "// too; the output is panel-wide theming, never user data.\n"
        "if (class_exists(\\Pterodactyl\\BlueprintFramework\\Libraries\\ExtensionLibrary\\Client\\NyrielStylesheetRouteAction::class)) {\n"
        "    Route::get('/themes/pterodactyl/css/nyriel.css', [\n"
        "        \\Pterodactyl\\BlueprintFramework\\Libraries\\ExtensionLibrary\\Client\\NyrielStylesheetRouteAction::class,\n"
        "        '__invoke',\n"
        "    ])->name('nyriel.css');\n"
        "}\n\n"
        "// Nyriel designer preview: frames the real client panel with unsaved settings.\n"
        "if (class_exists(\\Pterodactyl\\Http\\Controllers\\Extensions\\NyrielPreviewController::class)) {\n"
        "    Route::get('/extensions/nyriel/preview', [\n"
        "        \\Pterodactyl\\Http\\Controllers\\Extensions\\NyrielPreviewController::class,\n"
        "        '__invoke',\n"
        "    ])->name('nyriel.preview');\n"
        "}\n\n"
    )
    anchor = "Route::get('/{react}', [Base\\IndexController::class, 'index'])"
    if anchor not in s:
        raise SystemExit("   catch-all anchor not found — insert the blocks by hand")
    open(p, 'w').write(s.replace(anchor, block + anchor, 1))
    print("   inserted")
PY
php -l routes/base.php

echo "== 4. registry"
python3 - <<'PY'
p = '.blueprint/extensions/blueprint/private/db/installed_extensions'
ids = [x.strip().lstrip('|').strip() for x in open(p).read().split(',')]
seen, out = set(), []
for x in ids:
    if not x or x in seen:
        continue
    seen.add(x)
    out.append(x)
if 'nyriel' not in out:
    out.append('nyriel')
open(p, 'w').write(','.join(out))
print('   registered:', len(out))
PY

echo "== 5. patch Blueprint's config loader"
# One malformed conf.yml anywhere must not abort route registration.
python3 - <<'PY'
p = 'app/BlueprintFramework/Libraries/ExtensionLibrary/BlueprintBaseLibrary.php'
s = open(p).read()
if 'if (!is_array($conf))' in s:
    print('   already patched')
else:
    old = "        $collection->push(array_filter($conf, fn($k) => !!$k));"
    new = ("        // An empty or scalar conf.yml parses to null, and array_filter(null) is a\n"
           "        // TypeError. Skip it rather than taking every route down with it.\n"
           "        if (!is_array($conf)) {\n"
           "          continue;\n"
           "        }\n\n"
           "        $collection->push(array_filter($conf, fn($k) => !!$k));")
    if old not in s:
        raise SystemExit("   anchor not found — patch extensionsConfigs() by hand")
    open(p, 'w').write(s.replace(old, new, 1))
    print('   patched')
PY
php -l app/BlueprintFramework/Libraries/ExtensionLibrary/BlueprintBaseLibrary.php

echo "== 6. ownership + caches"
chown -R www-data:www-data .blueprint/extensions/nyriel \
  app/Http/Controllers/Admin/Extensions/nyriel resources/views/admin/extensions/nyriel \
  resources/views/extensions/nyriel public/extensions/nyriel
sudo -u www-data php artisan bp:cache
sudo -u www-data php artisan optimize:clear

echo
echo "Done. Open Admin -> Extensions -> Nyriel."
