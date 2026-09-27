<?php
/**
 * Nyriel — admin settings page for the theme.
 *
 * Blueprint renders this from admin/view.blade.php; POSTs land in
 * admin/controller.php which persists via $blueprint->dbSet().
 */
?>
<form method="POST" action="{{ route('admin.extensions.nyriel') }}" id="nyriel-form">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Nyriel</h3>
        </div>
        <div class="box-body">
            <p class="text-muted">
                Dark client theme: icon sidebar, CSS-variable palette, optional wallpaper.
                Changes apply immediately to every user.
            </p>

            <div class="form-group">
                <label class="control-label">Enabled</label>
                <select name="enabled" class="form-control">
                    <option value="1" @if($ae('enabled', '1') === '1') selected @endif>Yes</option>
                    <option value="0" @if($ae('enabled', '1') === '0') selected @endif>No</option>
                </select>
            </div>

            <hr>

            <h4>Palette</h4>
            <div class="row">
                @foreach([
                    'bg'         => 'Page background',
                    'bg_alt'     => 'Card / surface',
                    'sidebar_bg' => 'Sidebar background',
                    'accent'     => 'Accent',
                    'accent2'    => 'Accent (gradient end)',
                    'text'       => 'Text',
                    'text_dim'   => 'Text (dim)',
                ] as $key => $label)
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">{{ $label }}</label>
                            <input type="text" name="{{ $key }}" value="{{ $ae($key) }}" class="form-control">
                        </div>
                    </div>
                @endforeach
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Border</label>
                        <input type="text" name="border" value="{{ $ae('border', 'rgba(148, 163, 184, 0.15)') }}" class="form-control">
                    </div>
                </div>
            </div>

            <hr>

            <h4>Layout</h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Corner radius (px)</label>
                        <input type="number" name="radius" value="{{ $ae('radius', 12) }}" min="0" max="40" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Sidebar width (px)</label>
                        <input type="number" name="side_width" value="{{ $ae('side_width', 72) }}" min="48" max="240" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Blur (px)</label>
                        <input type="number" name="blur" value="{{ $ae('blur', 16) }}" min="0" max="40" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Hide sidebar</label>
                        <select name="hide_sidebar" class="form-control">
                            <option value="0" @if($ae('hide_sidebar', '0') === '0') selected @endif>No</option>
                            <option value="1" @if($ae('hide_sidebar', '0') === '1') selected @endif>Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>

            <h4>Wallpaper</h4>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="control-label">Image URL (blank = solid background)</label>
                        <input type="text" name="wallpaper" value="{{ $ae('wallpaper', '') }}" class="form-control"
                               placeholder="https://.../wallpaper.jpg">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Dim overlay (%)</label>
                        <input type="number" name="wallpaper_dim" value="{{ $ae('wallpaper_dim', 30) }}" min="0" max="90" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>
