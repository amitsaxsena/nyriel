<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\nyriel;

use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;

class nyrielExtensionController extends Controller
{
    /**
     * Every Nyriel setting, straight from the extension's schema. The schema is
     * the single source of truth — the designer, the form and the wrapper all
     * read it, so adding a setting means editing one file.
     */
    public static function schema(): array
    {
        static $cached = null;
        if ($cached === null) {
            $cached = require base_path('.blueprint/extensions/nyriel/private/schema.php');
        }
        return $cached;
    }

    /**
     * Flat map: setting key => field definition.
     *
     * Actions are deliberately excluded — they are commands, not settings, so
     * they never get seeded into the designer's state, never rendered as a
     * value, and never written to the table. The designer posts an action
     * flag explicitly when the button is pressed.
     */
    public static function fields(): array
    {
        static $cached = null;
        if ($cached === null) {
            $cached = [];
            foreach (self::schema() as $group) {
                if (! isset($group['fields'])) {
                    continue;
                }
                foreach ($group['fields'] as $key => $field) {
                    if (($field['type'] ?? '') === 'action') {
                        continue;
                    }
                    $field['key'] = $key;
                    $field['group'] = $group['label'] ?? 'Other';
                    $cached[$key] = $field;
                }
            }
        }
        return $cached;
    }

    /** Action field definitions, kept separate from stored settings. */
    public static function actions(): array
    {
        $out = [];
        foreach (self::schema() as $group) {
            if (! isset($group['fields'])) {
                continue;
            }
            foreach ($group['fields'] as $key => $field) {
                if (($field['type'] ?? '') === 'action') {
                    $field['key'] = $key;
                    $out[] = $field;
                }
            }
        }
        return $out;
    }

    /** Groups as the designer wants them: label, hint, ordered field list. */
    public static function groups(): array
    {
        $out = [];
        foreach (self::schema() as $group) {
            if (! isset($group['fields'])) {
                continue;
            }
            $list = [];
            $acts = [];
            foreach ($group['fields'] as $key => $field) {
                $field['key']    = $key;
                $field['hidden'] = $field['hidden'] ?? false;
                if (($field['type'] ?? '') === 'action') {
                    $acts[] = $field;
                    continue;
                }
                $list[] = $field;
            }
            $out[] = [
                'label'   => $group['label'] ?? 'Other',
                'hint'    => $group['hint']  ?? null,
                'fields'  => $list,
                'actions' => $acts,
            ];
        }
        return $out;
    }

    /**
     * Clamp / sanitise one value against its schema entry.
     *
     * Colours and URLs land inside a <style> block or a url(), so an unchecked
     * value could break out of its context. Numbers are clamped so a bad save
     * can't collapse the layout. Selects must match an allowed option.
     */
    public static function sanitize(string $key, $value)
    {
        $field = self::fields()[$key] ?? null;
        if ($field === null) {
            return null;
        }

        $value = is_scalar($value) ? (string) $value : '';

        switch ($field['type']) {
            case 'action':
                // Only ever '1' (run it) or absent; never persisted as a value.
                return in_array($value, ['1', 'true', 'on'], true) ? '1' : null;

            case 'bool':
                return in_array($value, ['0', '1', 'true', 'false', 'on'], true) ? '1' : '0';

            case 'number':
                $min = $field['min'] ?? 0;
                $max = $field['max'] ?? 100;
                return (string) max($min, min($max, (int) $value));

            case 'select':
                $options = array_keys($field['options'] ?? []);
                return in_array($value, $options, true) ? $value : ($field['default'] ?? '');

            case 'color':
                // hex, or rgba()/rgb()/hsl() with a character allowlist.
                $clean = substr(preg_replace('/[^#a-zA-Z0-9(),.%\- ]/', '', $value), 0, 40);
                return $clean === '' ? ($field['default'] ?? '') : $clean;

            case 'url':
                $clean = substr(preg_replace('/[^a-zA-Z0-9:\/?#\[\]@!$&\'()*+,;=%.\-~ ]/', '', $value), 0, 500);
                return $clean === '' ? '' : $clean;

            case 'text':
            default:
                return substr(strip_tags($value), 0, ($field['maxlen'] ?? 2000));
        }
    }

    /** Whitelisted rule set, derived from the schema, for form requests. */
    public static function validationRules(): array
    {
        $rules = [];
        foreach (self::fields() as $key => $field) {
            if ($field['hidden'] ?? false) {
                continue;
            }
            switch ($field['type']) {
                case 'action':
                    // Fire-and-forget; only validated when actually sent.
                    $rules[$key] = 'nullable|in:0,1';
                    break;
                case 'bool':
                    $rules[$key] = 'nullable|boolean';
                    break;
                case 'number':
                    $rules[$key] = "nullable|integer|min:{$field['min']}|max:{$field['max']}";
                    break;
                case 'color':
                    $rules[$key] = 'nullable|string|max:60';
                    break;
                case 'url':
                    $rules[$key] = 'nullable|string|max:500';
                    break;
                case 'text':
                    $rules[$key] = 'nullable|string|max:' . ($field['maxlen'] ?? 2000);
                    break;
                case 'select':
                    $rules[$key] = 'nullable|string|in:' . implode(',', array_keys($field['options'] ?? []));
                    break;
            }
        }
        return $rules;
    }

    public function __construct(
        private ViewFactory $view,
        private BlueprintExtensionLibrary $blueprint,
        private ConfigRepository $config,
        private SettingsRepositoryInterface $settings,
    ) {}

    /**
     * Designer UI. Lives in Blade rather than public/ so the CSRF token and the
     * admin gate are the framework's, not a hand-rolled session check.
     */
    public function designer(): View
    {
        return $this->view->make('admin.extensions.nyriel.designer.index', [
            'groups' => self::groups(),
        ]);
    }

    /** Current values, shaped for the designer. */
    public function load(): JsonResponse
    {
        $values = [];
        foreach (self::fields() as $key => $field) {
            $values[$key] = $this->blueprint->dbGet('nyriel', $key, $field['default']);
        }

        return response()->json(['ok' => true, 'values' => $values]);
    }

    /** Persist designer changes. Same sanitiser the form uses. */
    public function post(nyrielDesignerRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $saved = [];

        // Factory reset: wipe every Nyriel setting back to its default. The
        // flag is consumed here and never written to the table, so a later save
        // can't trigger it again by accident.
        if (($payload['reset'] ?? '0') === '1') {
            foreach (array_keys(self::fields()) as $key) {
                $saved[$key] = $this->blueprint->dbGet('nyriel', $key);
                $this->blueprint->dbSet('nyriel', $key, self::fields()[$key]['default']);
            }

            return response()->json(['ok' => true, 'saved' => $saved, 'reset' => true]);
        }

        foreach ($payload as $key => $value) {
            $clean = self::sanitize($key, $value);
            if ($clean === null) {
                continue; // unknown key, or an action flag — never stored
            }
            $this->blueprint->dbSet('nyriel', $key, $clean);
            $saved[$key] = $clean;
        }

        return response()->json(['ok' => true, 'saved' => $saved, 'count' => count($saved)]);
    }

    /**
     * Extension card entry point. The designer already renders every schema
     * group with real controls, so landing here would just be a worse second
     * copy of it — send the admin straight to the designer instead.
     */
    public function index(): View
    {
        return $this->view->make('admin.extensions.nyriel.card', [
            'groups' => self::groups(),
        ]);
    }

}

class nyrielDesignerRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return nyrielExtensionController::validationRules();
    }
}

