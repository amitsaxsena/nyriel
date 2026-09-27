<?php
/**
 * Nyriel — extension card body.
 *
 * The card is a pointer, not a settings form: every control lives in the
 * designer, so this only needs to say what the extension is and where to go.
 */
?>
<div class="row">
    <div class="col-md-8">
        <div class="box" style="border-radius:14px;">
            <div class="box-body">
                <h3 style="margin:0 0 6px;font-size:16px;">Nyriel</h3>
                <p class="text-muted" style="margin-bottom:14px;">
                    Dark client theme with an icon sidebar, glass surfaces, a
                    {{ count($groups) }}-group palette editor and a live preview.
                    {{ array_sum(array_map(fn($g) => count($g['fields']), $groups)) }} settings,
                    every one editable with a real control.
                </p>
                <a href="{{ route('admin.extensions.nyriel.designer') }}">
                    <button type="button" class="btn btn-primary">
                        <i class="fa fa-paint-brush"></i> Open designer
                    </button>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box" style="border-radius:14px;">
            <div class="box-header with-border"><h3 class="box-title">Groups</h3></div>
            <ul class="list-unstyled" style="margin:0;padding:12px 16px;">
                @foreach($groups as $g)
                    <li style="padding:3px 0;font-size:12.5px;">
                        <span style="opacity:.8;">{{ $g['label'] }}</span>
                        <span class="pull-right badge" style="background:#3b82f6;">
                            {{ count($g['fields']) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
